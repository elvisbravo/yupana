<?php

namespace App\Controllers;

use App\Models\RolModel;

class Roles extends BaseController
{
    public function index()
    {
        return view('roles/index');
    }

    public function listar()
    {
        $model = new RolModel();
        $roles = $model->orderBy('nivel', 'DESC')->findAll();

        $data = [];
        foreach ($roles as $r) {
            $activo = $r->activo
                ? '<span class="badge badge-soft-success">Sí</span>'
                : '<span class="badge badge-soft-secondary">No</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-rol" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-rol" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->codigo),
                esc($r->nombre),
                esc($r->descripcion ?: '—'),
                $r->nivel,
                $activo,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $model = new RolModel();
        $rol = $model->find($id);
        if (!$rol) {
            return $this->response->setJSON(['error' => 'Rol no encontrado.']);
        }
        return $this->response->setJSON($rol);
    }

    public function guardar()
    {
        $model = new RolModel();

        if (!$this->validate($model->validationRules, $model->validationMessages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $data = $this->request->getPost();
        $data['activo'] = $this->request->getPost('activo') ? 1 : 0;
        $model->save($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Rol creado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $model = new RolModel();
        $rol = $model->find($id);
        if (!$rol) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rol no encontrado.',
            ]);
        }

        $rules = $model->validationRules;
        $rules['codigo'] = 'required|max_length[20]|is_unique[roles.codigo,id,' . $id . ']';

        if (!$this->validate($rules, $model->validationMessages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $data = $this->request->getPost();
        $data['activo'] = $this->request->getPost('activo') ? 1 : 0;
        $model->update($id, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Rol actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $model = new RolModel();
        $rol = $model->find($id);
        if (!$rol) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rol no encontrado.',
            ]);
        }

        if ($rol->codigo === 'ADMIN') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar el rol Administrador.',
            ]);
        }

        $db = \Config\Database::connect();
        $usuarios = $db->table('usuarios')->where('rol_id', $id)->countAllResults();
        if ($usuarios > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar un rol con usuarios asignados.',
            ]);
        }

        $db->table('roles_modulos')->where('rol_id', $id)->delete();
        $db->table('roles_modulos_permisos')->where('rol_id', $id)->delete();
        $model->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Rol eliminado correctamente.',
        ]);
    }
}
