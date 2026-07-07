<?php

namespace App\Controllers;

class Contratos extends BaseController
{
    public function firmados()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('contratos/firmados', $data);
    }

    public function vigentes()
    {
        return view('contratos/vigentes');
    }

    public function vencidos()
    {
        return view('contratos/vencidos');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.*, cl.razon_social
            FROM contratos c
            JOIN clientes cl ON cl.id = c.cliente_id
            ORDER BY c.fecha_firma DESC, cl.razon_social
        ")->getResult();

        return $this->buildData($rows, true);
    }

    public function listarVigentes()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.*, cl.razon_social
            FROM contratos c
            JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.estado = 'activo'
            ORDER BY c.fecha_fin, cl.razon_social
        ")->getResult();

        return $this->buildData($rows, false);
    }

    public function listarVencidos()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.*, cl.razon_social
            FROM contratos c
            JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.estado = 'vencido'
            ORDER BY c.fecha_fin DESC, cl.razon_social
        ")->getResult();

        return $this->buildData($rows, false);
    }

    private function buildData($rows, $withActions)
    {
        $tipoLabels = [
            'servicios'       => 'Servicios',
            'confidencialidad' => 'Confidencialidad',
            'honorarios'      => 'Honorarios',
            'otro'            => 'Otro',
        ];

        $badges = [
            'borrador'   => 'badge-soft-secondary',
            'activo'     => 'badge-soft-success',
            'vencido'    => 'badge-soft-danger',
            'renovado'   => 'badge-soft-info',
            'rescindido' => 'badge-soft-dark',
        ];

        $data = [];
        foreach ($rows as $r) {
            $tipo = $tipoLabels[$r->tipo] ?? $r->tipo;
            $badge = '<span class="badge ' . ($badges[$r->estado] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado) . '</span>';
            $monto = $r->monto_total ? number_format($r->monto_total, 2) . ' ' . $r->moneda : '—';
            $fecha_fin = $r->fecha_fin ?: '—';

            $acciones = '';
            if ($withActions) {
                $acciones = '<button class="btn btn-sm btn-soft-info editar-contrato" data-id="' . $r->id . '" title="Editar">'
                    . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';
                if ($r->estado !== 'rescindido') {
                    $acciones .= '<button class="btn btn-sm btn-soft-danger eliminar-contrato" data-id="' . $r->id . '" title="Rescindir">'
                        . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
                }
            }

            $data[] = [
                esc($r->razon_social),
                esc($r->numero_contrato),
                $tipo,
                $r->fecha_firma,
                $r->fecha_inicio,
                $fecha_fin,
                $monto,
                $badge,
                esc($r->observaciones ?: '—'),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('contratos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Contrato no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['archivo_url'] = $data['archivo_url'] ?? '';
        $data['archivo_nombre'] = $data['archivo_nombre'] ?? '';
        $data['archivo_size'] = $data['archivo_size'] ?: null;
        $data['archivo_hash'] = $data['archivo_hash'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;
        $data['usuario_id'] = session('user_id');

        $existing = $db->table('contratos')->where('numero_contrato', $data['numero_contrato'])->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe un contrato con ese número.']);
        }

        $db->table('contratos')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contrato guardado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('contratos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado.']);
        }

        $data = $this->request->getPost();
        $data['archivo_url'] = $data['archivo_url'] ?? '';
        $data['archivo_nombre'] = $data['archivo_nombre'] ?? '';
        $data['archivo_size'] = $data['archivo_size'] ?: null;
        $data['archivo_hash'] = $data['archivo_hash'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;

        $existing = $db->table('contratos')
            ->where('numero_contrato', $data['numero_contrato'])
            ->where('id !=', $id)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe otro contrato con ese número.']);
        }

        $db->table('contratos')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contrato actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('contratos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado.']);
        }

        $db->table('contratos')->where('id', $id)->update(['estado' => 'rescindido']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contrato rescindido correctamente.',
        ]);
    }
}
