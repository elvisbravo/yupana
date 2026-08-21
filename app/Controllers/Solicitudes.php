<?php

namespace App\Controllers;

use App\Models\SolicitudContactoModel;

class Solicitudes extends BaseController
{
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to('/login');
        }
        return view('solicitudes/index');
    }

    public function listar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['data' => []]);
        }

        $model = new SolicitudContactoModel();
        $rows = $model->orderBy('created_at', 'DESC')->findAll();

        $data = [];
        foreach ($rows as $r) {
            $leido = $r->leido ? '<span class="badge badge-soft-success">Leído</span>'
                : '<span class="badge badge-soft-warning">Nuevo</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info ver-solicitud" data-id="' . $r->id . '" title="Ver">'
                . '<i data-lucide="eye" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-solicitud" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->nombres . ' ' . $r->apellidos),
                esc($r->email),
                esc($r->telefono ?: '—'),
                esc($r->servicio ?: '—'),
                $r->created_at,
                $leido,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $model = new SolicitudContactoModel();
        $row = $model->find($id);

        if (!$row) {
            return $this->response->setJSON(['error' => 'Solicitud no encontrada.']);
        }

        if (!$row->leido) {
            $model->update($id, ['leido' => 1]);
            $row->leido = 1;
        }

        return $this->response->setJSON($row);
    }

    public function eliminar($id)
    {
        $model = new SolicitudContactoModel();
        $row = $model->find($id);

        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no encontrada.']);
        }

        $model->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Solicitud eliminada correctamente.',
        ]);
    }
}
