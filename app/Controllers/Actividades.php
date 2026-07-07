<?php

namespace App\Controllers;

class Actividades extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['actividades'] = $db->table('actividades_economicas')->where('activo', 1)->orderBy('codigo')->get()->getResult();
        return view('actividades/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT ca.*, cl.razon_social, ae.codigo as act_codigo, ae.descripcion as act_descripcion
            FROM cliente_actividades ca
            JOIN clientes cl ON cl.id = ca.cliente_id
            JOIN actividades_economicas ae ON ae.id = ca.actividad_id
            WHERE ca.activo = 1
            ORDER BY cl.razon_social, ca.es_principal DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $principal = $r->es_principal ? '<span class="badge badge-soft-warning">Principal</span>' : '';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-actividad" data-id="' . $r->cliente_id . '-' . $r->actividad_id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-actividad" data-id="' . $r->cliente_id . '-' . $r->actividad_id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                esc($r->act_codigo),
                esc($r->act_descripcion),
                $r->fecha_inicio ?: '—',
                $principal,
                $acciones,
                $r->cliente_id,
                $r->actividad_id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener()
    {
        $clienteId = $this->request->getGet('cliente_id');
        $actividadId = $this->request->getGet('actividad_id');

        $db = \Config\Database::connect();
        $row = $db->table('cliente_actividades')
            ->where('cliente_id', $clienteId)
            ->where('actividad_id', $actividadId)
            ->get()->getRow();

        if (!$row) {
            return $this->response->setJSON(['error' => 'Asignación no encontrada.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['es_principal'] = $this->request->getPost('es_principal') ? 1 : 0;

        $exists = $db->table('cliente_actividades')
            ->where('cliente_id', $data['cliente_id'])
            ->where('actividad_id', $data['actividad_id'])
            ->get()->getRow();

        if ($exists) {
            if ($exists->activo) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Esta actividad ya está asignada al cliente.',
                ]);
            }
            $db->table('cliente_actividades')
                ->where('cliente_id', $data['cliente_id'])
                ->where('actividad_id', $data['actividad_id'])
                ->update(['activo' => 1, 'es_principal' => $data['es_principal'], 'fecha_inicio' => $data['fecha_inicio'] ?? null]);
            return $this->response->setJSON(['success' => true, 'message' => 'Actividad asignada correctamente.']);
        }

        if ($data['es_principal']) {
            $db->table('cliente_actividades')
                ->where('cliente_id', $data['cliente_id'])
                ->where('activo', 1)
                ->update(['es_principal' => 0]);
        }

        $db->table('cliente_actividades')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Actividad asignada correctamente.',
        ]);
    }

    public function actualizar()
    {
        $clienteId = $this->request->getPost('cliente_id');
        $actividadId = $this->request->getPost('actividad_id');

        $db = \Config\Database::connect();
        $row = $db->table('cliente_actividades')
            ->where('cliente_id', $clienteId)
            ->where('actividad_id', $actividadId)
            ->get()->getRow();

        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asignación no encontrada.']);
        }

        $data = $this->request->getPost();
        $data['es_principal'] = $this->request->getPost('es_principal') ? 1 : 0;

        if ($data['es_principal']) {
            $db->table('cliente_actividades')
                ->where('cliente_id', $clienteId)
                ->where('activo', 1)
                ->where('actividad_id !=', $actividadId)
                ->update(['es_principal' => 0]);
        }

        $db->table('cliente_actividades')
            ->where('cliente_id', $clienteId)
            ->where('actividad_id', $actividadId)
            ->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asignación actualizada correctamente.',
        ]);
    }

    public function eliminar()
    {
        $clienteId = $this->request->getGet('cliente_id');
        $actividadId = $this->request->getGet('actividad_id');

        $db = \Config\Database::connect();
        $row = $db->table('cliente_actividades')
            ->where('cliente_id', $clienteId)
            ->where('actividad_id', $actividadId)
            ->get()->getRow();

        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asignación no encontrada.']);
        }

        $db->table('cliente_actividades')
            ->where('cliente_id', $clienteId)
            ->where('actividad_id', $actividadId)
            ->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asignación eliminada correctamente.',
        ]);
    }
}
