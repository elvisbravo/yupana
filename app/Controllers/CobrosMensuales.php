<?php

namespace App\Controllers;

class CobrosMensuales extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['servicios'] = $db->table('servicios_contratados sc')
            ->select('sc.id, sc.cliente_id, ts.nombre as servicio_nombre')
            ->join('tipos_servicio ts', 'ts.id = sc.tipo_servicio_id')
            ->where('sc.activo', 1)
            ->orderBy('sc.id')
            ->get()->getResult();
        return view('cobros_mensuales/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.*, cl.razon_social, ts.nombre as servicio_nombre
            FROM cobros_mensuales c
            JOIN clientes cl ON cl.id = c.cliente_id
            LEFT JOIN servicios_contratados sc ON sc.id = c.servicio_contratado_id
            LEFT JOIN tipos_servicio ts ON ts.id = sc.tipo_servicio_id
            WHERE c.activo = 1
            ORDER BY c.fecha_emision DESC, cl.razon_social
        ")->getResult();

        $badges = [
            'pendiente' => 'badge-soft-warning',
            'pagado'    => 'badge-soft-success',
            'parcial'   => 'badge-soft-info',
            'vencido'   => 'badge-soft-danger',
            'anulado'   => 'badge-soft-secondary',
        ];

        $data = [];
        foreach ($rows as $r) {
            $servicio = $r->servicio_nombre ?: '—';
            $saldo = $r->monto - $r->monto_pagado;
            $badge = '<span class="badge ' . ($badges[$r->estado] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado) . '</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-cobro" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';
            if ($r->estado !== 'anulado') {
                $acciones .= '<button class="btn btn-sm btn-soft-danger eliminar-cobro" data-id="' . $r->id . '" title="Anular">'
                    . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
            }

            $data[] = [
                esc($r->razon_social),
                esc($servicio),
                $r->periodo,
                $r->fecha_emision,
                $r->fecha_vencimiento,
                number_format($r->monto, 2),
                number_format($r->monto_pagado, 2),
                number_format($saldo, 2),
                $r->moneda,
                $badge,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('cobros_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Cobro no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['servicio_contratado_id'] = $data['servicio_contratado_id'] ?: null;
        $data['concepto'] = $data['concepto'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;
        $data['usuario_id'] = session('user_id');

        $existing = $db->table('cobros_mensuales')
            ->where('cliente_id', $data['cliente_id'])
            ->where('periodo', $data['periodo'])
            ->where('servicio_contratado_id', $data['servicio_contratado_id'])
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe un cobro para este cliente, periodo y servicio.']);
        }

        $db->table('cobros_mensuales')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cobro mensual guardado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('cobros_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cobro no encontrado.']);
        }
        if ($row->estado === 'anulado') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede editar un cobro anulado.']);
        }
        if ($row->monto_pagado > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede editar un cobro con pagos registrados. Anule los pagos primero.']);
        }

        $data = $this->request->getPost();
        $data['servicio_contratado_id'] = $data['servicio_contratado_id'] ?: null;
        $data['concepto'] = $data['concepto'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;

        $existing = $db->table('cobros_mensuales')
            ->where('cliente_id', $data['cliente_id'])
            ->where('periodo', $data['periodo'])
            ->where('servicio_contratado_id', $data['servicio_contratado_id'])
            ->where('id !=', $id)
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe otro cobro para este cliente, periodo y servicio.']);
        }

        $db->table('cobros_mensuales')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cobro mensual actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('cobros_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cobro no encontrado.']);
        }

        $db->table('cobros_mensuales')->where('id', $id)->update(['estado' => 'anulado', 'activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cobro anulado correctamente.',
        ]);
    }

    public function obtenerServicios()
    {
        $cliente_id = $this->request->getGet('cliente_id');
        $db = \Config\Database::connect();
        $rows = $db->table('servicios_contratados sc')
            ->select('sc.id, ts.nombre as servicio_nombre')
            ->join('tipos_servicio ts', 'ts.id = sc.tipo_servicio_id')
            ->where('sc.cliente_id', $cliente_id)
            ->where('sc.activo', 1)
            ->get()->getResult();
        return $this->response->setJSON($rows);
    }
}
