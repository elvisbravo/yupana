<?php

namespace App\Libraries;

class SunatClient
{
    private const RUTAS_POR_CODIGO = [
        '01' => 'factura',
        '03' => 'boleta',
        '07' => 'nota-credito',
        '08' => 'nota-debito',
    ];

    public function enviar(int $comprobanteId): array
    {
        $db = \Config\Database::connect();

        $comprobante = $db->query('SELECT * FROM comprobantes_emitidos WHERE id = ?', [$comprobanteId])->getRow();
        if (!$comprobante) {
            return ['success' => false, 'mensaje' => 'Comprobante no encontrado.'];
        }

        $tipo = $db->table('tipos_comprobante')->where('id', $comprobante->tipo_comprobante_id)->get()->getRow();
        $ruta = self::RUTAS_POR_CODIGO[$tipo->codigo ?? null] ?? null;
        if (!$ruta) {
            return $this->fallar($db, $comprobante, 'Tipo de comprobante no soportado por facturación electrónica.');
        }

        $cliente = $db->table('clientes')->where('id', $comprobante->cliente_id)->get()->getRow();
        if (!$cliente || empty($cliente->ruc)) {
            return $this->fallar($db, $comprobante, 'El cliente no tiene RUC registrado.');
        }

        if (!$comprobante->sede_id) {
            return $this->fallar($db, $comprobante, 'El comprobante no tiene una sede asociada.');
        }

        $sede = $db->query('
            SELECT s.*, u.codigo as ubigeo_codigo, u.departamento, u.provincia, u.distrito
            FROM sedes s
            LEFT JOIN ubigeos u ON u.id = s.ubigeo_id
            WHERE s.id = ?
        ', [$comprobante->sede_id])->getRow();
        if (!$sede || !$sede->ubigeo_codigo) {
            return $this->fallar($db, $comprobante, 'La sede emisora no tiene ubigeo configurado.');
        }

        $empresa = $db->table('empresa')->where('id', 1)->get()->getRow();
        if (!$empresa || empty($empresa->usuario_sol) || empty($empresa->password_sol) || empty($empresa->password_certificate)) {
            return $this->fallar($db, $comprobante, 'Faltan credenciales SOL/certificado en la configuración de la empresa.');
        }

        $encrypter = service('encrypter');
        try {
            $passwordSol = $encrypter->decrypt(base64_decode($empresa->password_sol));
            $passwordCertificate = $encrypter->decrypt(base64_decode($empresa->password_certificate));
        } catch (\Exception $e) {
            return $this->fallar($db, $comprobante, 'No se pudo desencriptar las credenciales SUNAT: ' . $e->getMessage());
        }

        $items = json_decode($comprobante->detalle ?? '[]', true) ?: [];
        if (!$items) {
            return $this->fallar($db, $comprobante, 'El comprobante no tiene ítems de detalle.');
        }

        $numeroALetras = new NumeroALetras();

        $payload = [
            'emisor' => [
                'ruc_emisor' => $empresa->ruc,
                'usuario_sol' => $empresa->usuario_sol,
                'password_sol' => $passwordSol,
                'password_certificate' => $passwordCertificate,
                'razon_social' => $empresa->razon_social,
                'nombre_comercial' => $empresa->nombre_comercial ?: $empresa->razon_social,
                'emisor_direccion' => $sede->direccion ?: $empresa->direccion_fiscal,
                'emisor_ubigueo' => $sede->ubigeo_codigo,
                'emisor_cod_local' => $sede->anexo ?: '0000',
                'emisor_departamento' => $sede->departamento,
                'emisor_provincia' => $sede->provincia,
                'emisor_distrito' => $sede->distrito,
            ],
            'cliente' => [
                'cliente_tipo_doc' => '6',
                'cliente_num_doc' => $cliente->ruc,
                'cliente_nombre' => $cliente->razon_social,
            ],
            'detalle_comprobante' => [
                'ambiente' => $sede->tipo_envio === 'produccion' ? 'PRODUCCION' : 'PRUEBA',
                'serie' => $comprobante->serie,
                'correlativo' => $comprobante->numero,
                'fecha_emision' => $comprobante->fecha_emision,
                'fecha_vencimiento' => $comprobante->fecha_vencimiento ?: $comprobante->fecha_emision,
                'moneda' => $comprobante->moneda,
                'forma_pago' => $comprobante->forma_pago,
                'total_letras' => $numeroALetras->convertir((float) $comprobante->total, $comprobante->moneda),
            ],
            'detalle_item' => array_map(static fn ($item) => [
                'codigo' => $item['codigo'] ?? 'P001',
                'descripcion' => $item['descripcion'],
                'unidad' => $item['unidad'] ?? 'ZZ',
                'cantidad' => $item['cantidad'],
                'valor_unitario' => $item['precio_unitario'],
                'tipo_afectacion' => $item['tipo_afectacion'] ?? '20',
            ], $items),
        ];

        $baseUrl = rtrim(env('SUNAT_API_URL', 'http://localhost:9500'), '/');
        $client = \Config\Services::curlrequest(['timeout' => 120]);

        try {
            $response = $client->request('POST', $baseUrl . '/api/' . $ruta, [
                'json' => $payload,
                'http_errors' => false,
            ]);
        } catch (\Exception $e) {
            return $this->fallar($db, $comprobante, 'Error de conexión con facturación electrónica: ' . $e->getMessage());
        }

        $body = json_decode($response->getBody(), true) ?? [];

        if (empty($body['success'])) {
            $mensaje = $body['messages']['message'] ?? $body['messages']['error'] ?? $body['message'] ?? 'Error desconocido al enviar a SUNAT.';
            return $this->fallar($db, $comprobante, $mensaje);
        }

        $estado = strtoupper($body['estado'] ?? '');
        $estadoSunat = str_starts_with($estado, 'ACEPTADA') ? 'aceptado' : ($estado === 'RECHAZADA' ? 'rechazado' : 'pendiente');

        $update = [
            'estado_sunat' => $estadoSunat,
            'sunat_mensaje' => substr($body['mensaje'] ?? $estado, 0, 500),
        ];

        if (!empty($body['hash'])) {
            $update['hash_cpe'] = $body['hash'];
        }

        $archivoBase = $this->nombreArchivo($comprobante);

        if (!is_dir(FCPATH . 'uploads/comprobantes')) {
            mkdir(FCPATH . 'uploads/comprobantes', 0755, true);
        }

        if (!empty($body['xml'])) {
            $xmlPath = 'uploads/comprobantes/' . $archivoBase . '.xml';
            file_put_contents(FCPATH . $xmlPath, base64_decode($body['xml']));
            $update['xml_url'] = $xmlPath;
        }

        if (!empty($body['cdr'])) {
            $cdrPath = 'uploads/comprobantes/' . $archivoBase . '-cdr.zip';
            file_put_contents(FCPATH . $cdrPath, base64_decode($body['cdr']));
            $update['cdr_url'] = $cdrPath;
        }

        $db->table('comprobantes_emitidos')->where('id', $comprobante->id)->update($update);

        return [
            'success' => true,
            'estado_sunat' => $estadoSunat,
            'mensaje' => $update['sunat_mensaje'],
        ];
    }

    private function fallar($db, $comprobante, string $mensaje): array
    {
        $db->table('comprobantes_emitidos')->where('id', $comprobante->id)->update([
            'sunat_mensaje' => substr($mensaje, 0, 500),
        ]);

        return ['success' => false, 'estado_sunat' => $comprobante->estado_sunat, 'mensaje' => $mensaje];
    }

    private function nombreArchivo($comprobante): string
    {
        $limpio = static fn ($v) => preg_replace('/[^A-Za-z0-9\-]/', '', (string) $v);
        return $limpio($comprobante->serie) . '-' . $limpio($comprobante->numero);
    }
}
