<?php

namespace App\Controllers;

class TarifasMensuales extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['servicios'] = $db->table('servicios_contratados')->where('activo', 1)->orderBy('id')->get()->getResult();
        return view('tarifas_mensuales/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT t.*, cl.razon_social, s.tipo_servicio_id, ts.nombre as servicio_nombre
            FROM tarifas_mensuales t
            JOIN clientes cl ON cl.id = t.cliente_id
            LEFT JOIN servicios_contratados s ON s.id = t.servicio_contratado_id
            LEFT JOIN tipos_servicio ts ON ts.id = s.tipo_servicio_id
            WHERE t.activo = 1
            ORDER BY cl.razon_social, t.fecha_inicio DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $servicio = $r->servicio_nombre ?: '—';
            $fecha_fin = $r->fecha_fin ?: '—';
            $motivo = $r->motivo_cambio ?: '—';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-tarifa" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-tarifa" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                esc($servicio),
                number_format($r->monto, 2),
                $r->moneda,
                $r->fecha_inicio,
                $fecha_fin,
                esc($motivo),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Tarifa no encontrada.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['servicio_contratado_id'] = $data['servicio_contratado_id'] ?: null;
        $data['fecha_fin'] = $data['fecha_fin'] ?: null;
        $data['motivo_cambio'] = $data['motivo_cambio'] ?? null;
        $data['usuario_id'] = session('user_id');

        $db->table('tarifas_mensuales')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa mensual guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tarifa no encontrada.']);
        }

        $data = $this->request->getPost();
        $data['servicio_contratado_id'] = $data['servicio_contratado_id'] ?: null;
        $data['fecha_fin'] = $data['fecha_fin'] ?: null;
        $data['motivo_cambio'] = $data['motivo_cambio'] ?? null;

        $db->table('tarifas_mensuales')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa mensual actualizada correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tarifa no encontrada.']);
        }

        $db->table('tarifas_mensuales')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa mensual eliminada correctamente.',
        ]);
    }
}
