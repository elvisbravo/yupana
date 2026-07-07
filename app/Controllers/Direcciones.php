<?php

namespace App\Controllers;

class Direcciones extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['tipos'] = $db->table('tipos_direccion')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        $data['ubigeos'] = $db->table('ubigeos')->where('activo', 1)->orderBy('departamento, provincia, distrito')->get()->getResult();
        return view('direcciones/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT d.*, cl.razon_social, td.nombre as tipo_nombre,
                   CONCAT(u.departamento, ' / ', u.provincia, ' / ', u.distrito) as ubigeo_nombre
            FROM direcciones_cliente d
            JOIN clientes cl ON cl.id = d.cliente_id
            JOIN tipos_direccion td ON td.id = d.tipo_direccion_id
            LEFT JOIN ubigeos u ON u.id = d.ubigeo_id
            WHERE d.activo = 1
            ORDER BY cl.razon_social, d.es_principal DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $principal = $r->es_principal ? '<span class="badge badge-soft-warning">Principal</span>' : '';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-direccion" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-direccion" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                esc($r->tipo_nombre),
                esc($r->direccion),
                esc($r->ubigeo_nombre ?: '—'),
                esc($r->referencia ?: '—'),
                $principal,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('direcciones_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Dirección no encontrada.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['es_principal'] = $this->request->getPost('es_principal') ? 1 : 0;

        if ($data['es_principal']) {
            $db->table('direcciones_cliente')
                ->where('cliente_id', $data['cliente_id'])
                ->where('activo', 1)
                ->update(['es_principal' => 0]);
        }

        $db->table('direcciones_cliente')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dirección guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('direcciones_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dirección no encontrada.']);
        }

        $data = $this->request->getPost();
        $data['es_principal'] = $this->request->getPost('es_principal') ? 1 : 0;

        if ($data['es_principal']) {
            $db->table('direcciones_cliente')
                ->where('cliente_id', $row->cliente_id)
                ->where('id !=', $id)
                ->where('activo', 1)
                ->update(['es_principal' => 0]);
        }

        $db->table('direcciones_cliente')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dirección actualizada correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('direcciones_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dirección no encontrada.']);
        }

        $db->table('direcciones_cliente')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dirección eliminada correctamente.',
        ]);
    }
}
