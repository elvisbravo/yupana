<?php

namespace App\Controllers;

class FacturacionAutomatica extends BaseController
{
    private const MESES = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    ];

    public function index()
    {
        return redirect()->to('/facturacion-automatica/configuracion');
    }

    public function configuracion()
    {
        $db = \Config\Database::connect();
        $data['config'] = $db->table('configuracion_facturacion_automatica')->where('id', 1)->get()->getRow();
        $data['sedes'] = $db->table('sedes')->where('empresa_id', 1)->where('activo', 1)->orderBy('nombre')->get()->getResult();
        $data['tipos'] = $db->table('tipos_comprobante')
            ->whereIn('aplica_a', ['juridica', 'ambos'])
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get()->getResult();
        $data['monedas'] = ['PEN', 'USD'];

        return view('facturacion_automatica/configuracion', $data);
    }

    public function guardarConfiguracion()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $rules = [
            'sede_id' => 'required|integer',
            'tipo_comprobante_id' => 'required|integer',
            'hora_generacion' => 'required',
            'moneda' => 'required|max_length[3]',
            'tasa_igv' => 'required|numeric',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $tipo = $db->table('tipos_comprobante')->where('id', $data['tipo_comprobante_id'])->get()->getRow();
        if (!$tipo || !in_array($tipo->aplica_a, ['juridica', 'ambos'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El tipo de comprobante seleccionado no es válido para clientes con RUC.',
            ]);
        }

        $dias = array_filter(array_map('intval', $data['dias'] ?? []), fn ($d) => $d >= 1 && $d <= 31);
        sort($dias);

        $update = [
            'activo' => !empty($data['activo']) ? 1 : 0,
            'dias_generacion' => $dias ? implode(',', $dias) : '1',
            'hora_generacion' => $data['hora_generacion'],
            'sede_id' => $data['sede_id'],
            'tipo_comprobante_id' => $data['tipo_comprobante_id'],
            'moneda' => $data['moneda'],
            'tasa_igv' => $data['tasa_igv'],
            'usuario_id' => session('user_id'),
        ];

        $db->table('configuracion_facturacion_automatica')->where('id', 1)->update($update);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Configuración de facturación automática guardada correctamente.',
        ]);
    }

    public function generadas()
    {
        $db = \Config\Database::connect();
        $periodos = $db->query("
            SELECT DISTINCT periodo FROM comprobantes_emitidos
            WHERE origen = 'cron' AND periodo IS NOT NULL
            ORDER BY periodo DESC
        ")->getResult();

        $labels = [];
        foreach ($periodos as $p) {
            $labels[$p->periodo] = $this->nombrePeriodo($p->periodo);
        }

        $config = $db->table('configuracion_facturacion_automatica')->where('id', 1)->get()->getRow();
        $defaultPeriodo = $config->ultimo_periodo_generado ?? date('Y-m');

        $data['periodos'] = $periodos;
        $data['periodosLabels'] = $labels;
        $data['defaultPeriodo'] = $defaultPeriodo;

        return view('facturacion_automatica/generadas', $data);
    }

    public function clientes()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->query("
            SELECT c.id, c.ruc, c.razon_social,
                   (SELECT COUNT(*) FROM servicios_contratados sc WHERE sc.cliente_id = c.id AND sc.activo = 1) as servicios_activos,
                   (fae.cliente_id IS NOT NULL) as excluido
            FROM clientes c
            LEFT JOIN facturacion_automatica_exclusiones fae ON fae.cliente_id = c.id
            WHERE c.estado = 'activo'
            ORDER BY c.razon_social
        ")->getResult();

        return view('facturacion_automatica/clientes', $data);
    }

    public function guardarClientes()
    {
        $db = \Config\Database::connect();
        $excluidos = array_map('intval', $this->request->getPost('excluidos') ?? []);

        $db->transStart();
        $db->table('facturacion_automatica_exclusiones')->emptyTable();
        if ($excluidos) {
            $rows = array_map(fn ($id) => [
                'cliente_id' => $id,
                'usuario_id' => session('user_id'),
            ], $excluidos);
            $db->table('facturacion_automatica_exclusiones')->insertBatch($rows);
        }
        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Clientes excluidos de facturación automática actualizados.',
        ]);
    }

    private function nombrePeriodo(string $periodo): string
    {
        [$anio, $mes] = explode('-', $periodo);
        return (self::MESES[$mes] ?? $mes) . ' ' . $anio;
    }
}
