<?php

namespace App\Controllers;

class ApiFacturacion extends BaseController
{
    private const MESES = [
        '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL',
        '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO',
        '09' => 'SETIEMBRE', '10' => 'OCTUBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE',
    ];

    public function configuracion()
    {
        $db = \Config\Database::connect();
        $cfg = $db->table('configuracion_facturacion_automatica')->where('id', 1)->get()->getRow();

        if (!$cfg) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Configuración no encontrada.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'activo' => (bool) $cfg->activo,
            'dias_generacion' => array_map('intval', explode(',', $cfg->dias_generacion)),
            'hora_generacion' => $cfg->hora_generacion,
            'sede_id' => (int) $cfg->sede_id,
            'tipo_comprobante_id' => (int) $cfg->tipo_comprobante_id,
            'moneda' => $cfg->moneda,
            'tasa_igv' => (float) $cfg->tasa_igv,
            'ultimo_periodo_generado' => $cfg->ultimo_periodo_generado,
            'ultima_ejecucion' => $cfg->ultima_ejecucion,
        ]);
    }

    public function generar()
    {
        $db = \Config\Database::connect();
        $body = $this->request->getJSON(true) ?? [];
        $periodo = $body['periodo'] ?? (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m');

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El período debe tener el formato YYYY-MM.',
            ]);
        }

        $cfg = $db->table('configuracion_facturacion_automatica')->where('id', 1)->get()->getRow();
        if (!$cfg || !$cfg->activo) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'La facturación automática no está activa.',
            ]);
        }

        $tipo = $db->table('tipos_comprobante')->where('id', $cfg->tipo_comprobante_id)->get()->getRow();
        if (!$tipo) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'El tipo de comprobante configurado ya no existe.',
            ]);
        }

        // Clientes activos con una tarifa mensual vigente a nivel de cliente (servicio_contratado_id IS NULL)
        $clientes = $db->query("
            SELECT DISTINCT c.id, c.ruc, c.razon_social, tm.monto
            FROM clientes c
            JOIN tarifas_mensuales tm ON tm.cliente_id = c.id
                AND tm.servicio_contratado_id IS NULL
                AND tm.fecha_fin IS NULL
                AND tm.activo = 1
            WHERE c.estado = 'activo'
            ORDER BY c.razon_social
        ")->getResult();

        $excluidosIds = array_column(
            $db->query('SELECT cliente_id FROM facturacion_automatica_exclusiones')->getResultArray(),
            'cliente_id'
        );

        $generados = [];
        $omitidos = [];

        foreach ($clientes as $cliente) {
            if (in_array($cliente->id, $excluidosIds)) {
                $omitidos[] = [
                    'cliente_id' => $cliente->id,
                    'razon_social' => $cliente->razon_social,
                    'motivo' => 'Cliente excluido de facturación automática.',
                ];
                continue;
            }

            if ($tipo->requiere_ruc && empty($cliente->ruc)) {
                $omitidos[] = [
                    'cliente_id' => $cliente->id,
                    'razon_social' => $cliente->razon_social,
                    'motivo' => 'El cliente no tiene RUC y el tipo de comprobante lo requiere.',
                ];
                continue;
            }

            $yaExiste = $db->query("
                SELECT id FROM comprobantes_emitidos
                WHERE cliente_id = ? AND periodo = ? AND origen = 'cron' AND estado_sunat != 'anulado'
                LIMIT 1
            ", [$cliente->id, $periodo])->getRow();
            if ($yaExiste) {
                $omitidos[] = [
                    'cliente_id' => $cliente->id,
                    'razon_social' => $cliente->razon_social,
                    'motivo' => 'Ya existe un comprobante generado por cron para este período.',
                ];
                continue;
            }

            $monto = (float) $cliente->monto;
            if ($monto <= 0) {
                $omitidos[] = [
                    'cliente_id' => $cliente->id,
                    'razon_social' => $cliente->razon_social,
                    'motivo' => 'El cliente no tiene un monto mensual vigente configurado.',
                ];
                continue;
            }

            $mesAnio = (self::MESES[substr($periodo, 5, 2)] ?? substr($periodo, 5, 2)) . ' ' . substr($periodo, 0, 4);

            $items = [[
                'codigo' => 'P001',
                'descripcion' => "SERVICIO DE ASESORIA CONTABLE EXTERNA MES DE {$mesAnio}",
                'unidad' => 'ZZ',
                'cantidad' => 1,
                'precio_unitario' => $monto,
                'tipo_afectacion' => '20',
                'total' => $monto,
            ]];
            $subtotal = $monto;

            $igv = round($subtotal * ((float) $cfg->tasa_igv / 100), 2);
            $total = round($subtotal + $igv, 2);

            $db->transStart();

            $correlativo = $db->query("
                SELECT * FROM correlativos
                WHERE sede_id = ? AND tipo_comprobante_id = ? AND activo = 1
                ORDER BY serie ASC LIMIT 1 FOR UPDATE
            ", [$cfg->sede_id, $cfg->tipo_comprobante_id])->getRow();

            if (!$correlativo) {
                $db->transComplete();
                $omitidos[] = [
                    'cliente_id' => $cliente->id,
                    'razon_social' => $cliente->razon_social,
                    'motivo' => 'No hay un correlativo activo configurado para la sede/tipo seleccionados.',
                ];
                continue;
            }

            $numero = (int) $correlativo->correlativo_actual;
            $db->query('UPDATE correlativos SET correlativo_actual = correlativo_actual + 1 WHERE id = ?', [$correlativo->id]);

            $fechaEmision = (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d');

            $db->table('comprobantes_emitidos')->insert([
                'cliente_id' => $cliente->id,
                'sede_id' => $cfg->sede_id,
                'tipo_comprobante_id' => $cfg->tipo_comprobante_id,
                'serie' => $correlativo->serie,
                'numero' => str_pad((string) $numero, 8, '0', STR_PAD_LEFT),
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $fechaEmision,
                'forma_pago' => 'CONTADO',
                'periodo' => $periodo,
                'moneda' => $cfg->moneda,
                'subtotal' => round($subtotal, 2),
                'igv' => $igv,
                'total' => $total,
                'detalle' => json_encode($items),
                'estado_sunat' => 'pendiente',
                'estado_pago' => 'no_pagado',
                'origen' => 'cron',
                'usuario_id' => null,
            ]);

            $comprobanteId = $db->insertID();

            $db->transComplete();

            if ($db->transStatus() === false) {
                $omitidos[] = [
                    'cliente_id' => $cliente->id,
                    'razon_social' => $cliente->razon_social,
                    'motivo' => 'No se pudo generar el comprobante (posible número duplicado); revisar correlativos.',
                ];
                continue;
            }

            $envio = (new \App\Libraries\SunatClient())->enviar($comprobanteId);
            (new \App\Libraries\ComprobantePdf())->generar($comprobanteId);

            $generados[] = [
                'cliente_id' => $cliente->id,
                'razon_social' => $cliente->razon_social,
                'serie' => $correlativo->serie,
                'numero' => str_pad((string) $numero, 8, '0', STR_PAD_LEFT),
                'total' => $total,
                'estado_sunat' => $envio['estado_sunat'] ?? 'pendiente',
                'sunat_mensaje' => $envio['mensaje'] ?? null,
            ];
        }

        $db->table('configuracion_facturacion_automatica')->where('id', 1)->update([
            'ultimo_periodo_generado' => $periodo,
            'ultima_ejecucion' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'periodo' => $periodo,
            'generados' => count($generados),
            'omitidos' => count($omitidos),
            'detalle_generados' => $generados,
            'detalle_omitidos' => $omitidos,
        ]);
    }

    public function pendientesWhatsapp()
    {
        $db = \Config\Database::connect();

        $rows = $db->query("
            SELECT
                c.id, c.serie, c.numero, c.fecha_emision, c.moneda, c.total,
                c.estado_sunat, c.pdf_url,
                cl.id AS cliente_id, cl.ruc AS cliente_ruc,
                cl.razon_social AS cliente_razon_social, cl.telefono AS cliente_telefono
            FROM comprobantes_emitidos c
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.enviado_whatsapp = 'NO'
            ORDER BY c.fecha_emision ASC, c.id ASC
        ")->getResult();

        return $this->response->setJSON([
            'success' => true,
            'total' => count($rows),
            'data' => $rows,
        ]);
    }

    public function marcarEnviadoWhatsapp($id)
    {
        $db = \Config\Database::connect();
        $body = $this->request->getJSON(true) ?? [];
        $valor = strtoupper(trim($body['enviado_whatsapp'] ?? ''));

        if (!in_array($valor, ['SI', 'NO'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => "El campo enviado_whatsapp debe ser 'SI' o 'NO'.",
            ]);
        }

        $comprobante = $db->table('comprobantes_emitidos')->where('id', $id)->get()->getRow();
        if (!$comprobante) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Comprobante no encontrado.',
            ]);
        }

        $db->table('comprobantes_emitidos')->where('id', $id)->update(['enviado_whatsapp' => $valor]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Estado de envío por WhatsApp actualizado.',
            'id' => (int) $id,
            'enviado_whatsapp' => $valor,
        ]);
    }
}
