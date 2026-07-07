<?php

namespace App\Controllers;

class Contactos extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['tipos'] = $db->table('tipos_contacto')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('contactos/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.*, cl.razon_social, tc.nombre as tipo_nombre
            FROM contactos_cliente c
            JOIN clientes cl ON cl.id = c.cliente_id
            JOIN tipos_contacto tc ON tc.id = c.tipo_contacto_id
            WHERE c.activo = 1
            ORDER BY cl.razon_social, c.es_principal DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $principal = $r->es_principal ? '<span class="badge badge-soft-warning">Principal</span>' : '';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-contacto" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-contacto" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                esc($r->tipo_nombre),
                esc($r->valor),
                esc($r->contacto_nombre ?: '—'),
                esc($r->cargo ?: '—'),
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
        $row = $db->table('contactos_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Contacto no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['es_principal'] = $this->request->getPost('es_principal') ? 1 : 0;

        if ($data['es_principal']) {
            $db->table('contactos_cliente')
                ->where('cliente_id', $data['cliente_id'])
                ->where('activo', 1)
                ->update(['es_principal' => 0]);
        }

        $db->table('contactos_cliente')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contacto guardado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('contactos_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contacto no encontrado.']);
        }

        $data = $this->request->getPost();
        $data['es_principal'] = $this->request->getPost('es_principal') ? 1 : 0;

        if ($data['es_principal']) {
            $db->table('contactos_cliente')
                ->where('cliente_id', $row->cliente_id)
                ->where('id !=', $id)
                ->where('activo', 1)
                ->update(['es_principal' => 0]);
        }

        $db->table('contactos_cliente')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contacto actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('contactos_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contacto no encontrado.']);
        }

        $db->table('contactos_cliente')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contacto eliminado correctamente.',
        ]);
    }
}
