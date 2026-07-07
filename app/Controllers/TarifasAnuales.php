<?php

namespace App\Controllers;

class TarifasAnuales extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('tarifas_anuales/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT t.*, cl.razon_social
            FROM tarifas_anuales t
            JOIN clientes cl ON cl.id = t.cliente_id
            WHERE t.activo = 1
            ORDER BY cl.razon_social, t.anio DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $acciones = '<button class="btn btn-sm btn-soft-info editar-tarifa" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-tarifa" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                $r->anio,
                number_format($r->monto, 2),
                $r->moneda,
                esc($r->concepto ?: '—'),
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
        $row = $db->table('tarifas_anuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Tarifa no encontrada.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['concepto'] = $data['concepto'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;
        $data['usuario_id'] = session('user_id');

        $existing = $db->table('tarifas_anuales')
            ->where('cliente_id', $data['cliente_id'])
            ->where('anio', $data['anio'])
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe una tarifa anual para este cliente y año.']);
        }

        $db->table('tarifas_anuales')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa anual guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_anuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tarifa no encontrada.']);
        }

        $data = $this->request->getPost();
        $data['concepto'] = $data['concepto'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;

        $existing = $db->table('tarifas_anuales')
            ->where('cliente_id', $data['cliente_id'])
            ->where('anio', $data['anio'])
            ->where('id !=', $id)
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe otra tarifa anual para este cliente y año.']);
        }

        $db->table('tarifas_anuales')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa anual actualizada correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_anuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tarifa no encontrada.']);
        }

        $db->table('tarifas_anuales')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa anual eliminada correctamente.',
        ]);
    }
}
