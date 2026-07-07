<?php

namespace App\Controllers;

class Notas extends BaseController
{
    public function generales()
    {
        return $this->renderView('Notas Generales', 'general');
    }

    public function cobranza()
    {
        return $this->renderView('Notas de Cobranza', 'cobranza');
    }

    public function tributaria()
    {
        return $this->renderView('Notas Tributarias', 'tributaria');
    }

    public function laboral()
    {
        return $this->renderView('Notas Laborales', 'laboral');
    }

    private function renderView($titulo, $tipo)
    {
        $db = \Config\Database::connect();
        $data['titulo'] = $titulo;
        $data['tipo'] = $tipo;
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('notas/index', $data);
    }

    public function listar($tipo)
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT n.*, cl.razon_social, u.nombres, u.apellidos
            FROM notas_cliente n
            JOIN clientes cl ON cl.id = n.cliente_id
            JOIN usuarios u ON u.id = n.usuario_id
            WHERE n.tipo = ?
            ORDER BY n.fecha DESC
        ", [$tipo])->getResult();

        $data = [];
        foreach ($rows as $r) {
            $usuario = esc($r->nombres . ' ' . $r->apellidos);
            $acciones = '<button class="btn btn-sm btn-soft-info editar-nota" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-nota" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                $r->fecha,
                $usuario,
                nl2br(esc($r->nota)),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('notas_cliente')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['error' => 'Nota no encontrada.']);
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['usuario_id'] = session('user_id');

        $db->table('notas_cliente')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Nota guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('notas_cliente')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);

        $data = $this->request->getPost();
        unset($data['usuario_id']);

        $db->table('notas_cliente')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Nota actualizada correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('notas_cliente')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);

        $db->table('notas_cliente')->where('id', $id)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Nota eliminada correctamente.',
        ]);
    }
}
