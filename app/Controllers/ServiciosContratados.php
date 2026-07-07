<?php

namespace App\Controllers;

class ServiciosContratados extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['tipos'] = $db->table('tipos_servicio')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('servicios_contratados/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT s.*, cl.razon_social, ts.nombre as tipo_servicio_nombre
            FROM servicios_contratados s
            JOIN clientes cl ON cl.id = s.cliente_id
            JOIN tipos_servicio ts ON ts.id = s.tipo_servicio_id
            WHERE s.activo = 1
            ORDER BY cl.razon_social, s.fecha_inicio DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $fecha_fin = $r->fecha_fin ?: '—';
            $acciones = '<button class="btn btn-sm btn-soft-info editar-servicio" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-servicio" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                esc($r->tipo_servicio_nombre),
                $r->fecha_inicio,
                $fecha_fin,
                esc($r->descripcion ?: '—'),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('servicios_contratados')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Servicio no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_fin'] = $data['fecha_fin'] ?: null;
        $data['descripcion'] = $data['descripcion'] ?? null;

        $db->table('servicios_contratados')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Servicio contratado guardado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('servicios_contratados')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Servicio no encontrado.']);
        }

        $data = $this->request->getPost();
        $data['fecha_fin'] = $data['fecha_fin'] ?: null;
        $data['descripcion'] = $data['descripcion'] ?? null;

        $db->table('servicios_contratados')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Servicio contratado actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('servicios_contratados')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Servicio no encontrado.']);
        }

        $db->table('servicios_contratados')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Servicio contratado eliminado correctamente.',
        ]);
    }
}
