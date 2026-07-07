<?php

namespace App\Controllers;

class TiposServicio extends BaseController
{
    public function index()
    {
        return view('tipos_servicio/index');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tipos_servicio')->where('activo', 1)->orderBy('codigo')->get()->getResult();

        $data = [];
        foreach ($rows as $r) {
            $acciones = '<button class="btn btn-sm btn-soft-info editar-tipo" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-tipo" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->codigo),
                esc($r->nombre),
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
        $row = $db->table('tipos_servicio')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Tipo de servicio no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $existing = $db->table('tipos_servicio')->where('codigo', $data['codigo'])->where('activo', 1)->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe un tipo de servicio con ese código.']);
        }

        $db->table('tipos_servicio')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tipo de servicio guardado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tipos_servicio')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tipo de servicio no encontrado.']);
        }

        $data = $this->request->getPost();

        $existing = $db->table('tipos_servicio')
            ->where('codigo', $data['codigo'])
            ->where('id !=', $id)
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe otro tipo de servicio con ese código.']);
        }

        $db->table('tipos_servicio')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tipo de servicio actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tipos_servicio')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tipo de servicio no encontrado.']);
        }

        $db->table('tipos_servicio')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tipo de servicio eliminado correctamente.',
        ]);
    }
}
