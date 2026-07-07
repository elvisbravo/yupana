<?php

namespace App\Controllers;

class Comprobantes extends BaseController
{
    public function index()
    {
        return redirect()->to('/comprobantes/ventas');
    }

    public function ventas()
    {
        $db = \Config\Database::connect();
        $data['tipos'] = $db->query("SELECT id, nombre, abreviatura FROM tipos_comprobante WHERE activo = 1 ORDER BY id")->getResult();
        $data['anios'] = $db->query("SELECT DISTINCT YEAR(fecha_emision) as anio FROM comprobantes_emitidos ORDER BY anio DESC")->getResult();
        $data['periodos'] = $db->query("SELECT DISTINCT periodo FROM comprobantes_emitidos ORDER BY periodo DESC")->getResult();
        return view('comprobantes/ventas', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $tipo = $this->request->getGet('tipo');
        $periodo = $this->request->getGet('periodo');
        $anio = $this->request->getGet('anio');

        $sql = "
            SELECT c.*, tc.nombre as tipo_nombre, tc.abreviatura, cl.razon_social, cl.ruc
            FROM comprobantes_emitidos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            WHERE 1=1
        ";
        $params = [];
        if ($tipo) { $sql .= " AND c.tipo_comprobante_id = ?"; $params[] = $tipo; }
        if ($periodo) { $sql .= " AND c.periodo = ?"; $params[] = $periodo; }
        if ($anio) { $sql .= " AND YEAR(c.fecha_emision) = ?"; $params[] = $anio; }
        $sql .= " ORDER BY c.fecha_emision DESC, c.id DESC LIMIT 1000";

        $rows = $db->query($sql, $params)->getResult();

        $badges = [
            'aceptado'  => 'badge-soft-success',
            'pendiente' => 'badge-soft-warning',
            'rechazado' => 'badge-soft-danger',
            'anulado'   => 'badge-soft-secondary',
            'enviado'   => 'badge-soft-info',
            'baja'      => 'badge-soft-dark',
        ];
        $pagoBadges = [
            'pagado'    => 'badge-soft-success',
            'parcial'   => 'badge-soft-warning',
            'no_pagado' => 'badge-soft-danger',
        ];

        $data = [];
        foreach ($rows as $r) {
            $sunatBadge = '<span class="badge ' . ($badges[$r->estado_sunat] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado_sunat) . '</span>';
            $pagoBadge = '<span class="badge ' . ($pagoBadges[$r->estado_pago] ?? 'badge-soft-secondary') . '">' . ucfirst(str_replace('_', ' ', $r->estado_pago)) . '</span>';
            $data[] = [
                $r->serie . '-' . $r->numero,
                esc($r->tipo_nombre),
                $r->fecha_emision,
                $r->periodo,
                esc($r->razon_social ?? '—'),
                $r->ruc ?? '—',
                number_format($r->subtotal, 2),
                number_format($r->igv, 2),
                number_format($r->total, 2),
                $r->moneda,
                $sunatBadge,
                $pagoBadge,
                esc($r->observaciones ?? '—'),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function crear()
    {
        $db = \Config\Database::connect();
        $data['tipos'] = $db->query("SELECT id, nombre, abreviatura FROM tipos_comprobante WHERE activo = 1 ORDER BY id")->getResult();
        $data['clientes'] = $db->query("SELECT id, ruc, razon_social FROM clientes WHERE estado = 'activo' ORDER BY razon_social")->getResult();
        $data['monedas'] = ['PEN', 'USD'];
        $data['sedes'] = $db->table('sedes')->where('empresa_id', 1)->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('comprobantes/crear', $data);
    }

    public function obtenerCliente()
    {
        $id = $this->request->getGet('id');
        if (!$id) return $this->response->setJSON(['error' => 'ID requerido']);

        $db = \Config\Database::connect();
        $cliente = $db->query("
            SELECT c.*, rt.nombre as regimen_nombre
            FROM clientes c
            LEFT JOIN regimenes_tributarios rt ON rt.id = c.regimen_actual_id
            WHERE c.id = ?
        ", [$id])->getRow();
        if (!$cliente) return $this->response->setJSON(['error' => 'Cliente no encontrado']);

        $servicios = $db->query("
            SELECT sc.id, ts.nombre as servicio, ts.id as tipo_servicio_id,
                   COALESCE(tm.monto, 0) as monto,
                   COALESCE(tm.moneda, 'PEN') as moneda
            FROM servicios_contratados sc
            JOIN tipos_servicio ts ON ts.id = sc.tipo_servicio_id
            LEFT JOIN tarifas_mensuales tm ON tm.cliente_id = sc.cliente_id
                AND tm.servicio_contratado_id = sc.id
                AND tm.fecha_fin IS NULL AND tm.activo = 1
            WHERE sc.cliente_id = ? AND sc.activo = 1
        ", [$id])->getResult();

        return $this->response->setJSON([
            'cliente' => $cliente,
            'servicios' => $servicios,
        ]);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $rules = [
            'tipo_comprobante_id' => 'required|integer',
            'cliente_id' => 'required|integer',
            'serie' => 'required|max_length[4]',
            'numero' => 'required|max_length[20]',
            'fecha_emision' => 'required|valid_date',
            'subtotal' => 'required|numeric',
            'igv' => 'required|numeric',
            'total' => 'required|numeric',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $insert = [
            'tipo_comprobante_id' => $data['tipo_comprobante_id'],
            'cliente_id' => $data['cliente_id'],
            'serie' => $data['serie'],
            'numero' => $data['numero'],
            'fecha_emision' => $data['fecha_emision'],
            'periodo' => date('Y-m', strtotime($data['fecha_emision'])),
            'moneda' => $data['moneda'] ?? 'PEN',
            'subtotal' => $data['subtotal'],
            'igv' => $data['igv'],
            'total' => $data['total'],
            'estado_sunat' => 'pendiente',
            'estado_pago' => $data['estado_pago'] ?? 'no_pagado',
            'observaciones' => $data['observaciones'] ?? null,
            'detalle' => $data['detalle'] ?? null,
            'usuario_id' => session('usuario_id'),
        ];

        if ($insert['detalle']) {
            $items = json_decode($insert['detalle'], true);
            $insert['detalle'] = json_encode($items);
        }

        $db->table('comprobantes_emitidos')->insert($insert);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Comprobante creado correctamente.',
        ]);
    }

    public function guardarClienteRapido()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        if (empty($data['ruc']) || empty($data['razon_social'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'RUC y Razón Social son requeridos.']);
        }

        $existe = $db->table('clientes')->where('ruc', $data['ruc'])->get()->getRow();
        if ($existe) {
            return $this->response->setJSON([
                'success' => true,
                'cliente' => ['id' => $existe->id, 'razon_social' => $existe->razon_social, 'ruc' => $existe->ruc],
            ]);
        }

        $insert = [
            'ruc' => $data['ruc'],
            'razon_social' => $data['razon_social'],
            'email' => $data['email'] ?? null,
            'fecha_alta' => date('Y-m-d'),
            'estado' => 'activo',
            'usuario_registro_id' => session('usuario_id'),
        ];

        $db->table('clientes')->insert($insert);
        $id = $db->insertID();

        return $this->response->setJSON([
            'success' => true,
            'cliente' => [
                'id' => $id,
                'razon_social' => $data['razon_social'],
                'ruc' => $data['ruc'],
            ],
        ]);
    }

    public function tiposPorSede()
    {
        $sedeId = $this->request->getGet('sede_id');
        if (!$sedeId) return $this->response->setJSON([]);

        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT DISTINCT tc.id, tc.nombre, tc.abreviatura
            FROM correlativos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            WHERE c.sede_id = ? AND c.activo = 1
            ORDER BY tc.id
        ", [$sedeId])->getResult();

        return $this->response->setJSON($rows);
    }

    public function obtenerCorrelativo()
    {
        $sedeId = $this->request->getGet('sede_id');
        $tipoId = $this->request->getGet('tipo_comprobante_id');

        if (!$sedeId || !$tipoId) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $rows = $db->table('correlativos')
            ->where('sede_id', $sedeId)
            ->where('tipo_comprobante_id', $tipoId)
            ->where('activo', 1)
            ->orderBy('serie')
            ->get()->getResult();

        return $this->response->setJSON($rows);
    }
}
