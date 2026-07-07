<?php

namespace App\Controllers;

use App\Models\ModuloModel;

class Modulos extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['padres'] = $db->table('modulos')->where('activo', 1)->where('padre_id', null)->orderBy('orden')->get()->getResult();
        return view('modulos/index', $data);
    }

    public function listar()
    {
        $model = new ModuloModel();
        $modulos = $model->orderBy('orden', 'ASC')->findAll();

        $db = \Config\Database::connect();
        $padres = $db->table('modulos')->where('activo', 1)->get()->getResult();
        $mapa = [];
        foreach ($padres as $p) {
            $mapa[$p->id] = $p->nombre;
        }

        $data = [];
        foreach ($modulos as $m) {
            $activo = $m->activo
                ? '<span class="badge badge-soft-success">Sí</span>'
                : '<span class="badge badge-soft-secondary">No</span>';

            $acciones = '';
            $acciones .= '<button class="btn btn-sm btn-soft-info editar-modulo" data-id="' . $m->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';

            if ($m->codigo !== 'roles.modulos') {
                $acciones .= '<button class="btn btn-sm btn-soft-danger eliminar-modulo" data-id="' . $m->id . '" title="Eliminar">'
                    . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
            }

            $data[] = [
                esc($m->codigo),
                esc($m->nombre),
                esc($m->icono ?: '—'),
                esc($m->ruta ?: '—'),
                $m->orden,
                esc($mapa[$m->padre_id] ?? '<span class="text-muted">—</span>'),
                $activo,
                $acciones,
                $m->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $model = new ModuloModel();
        $modulo = $model->find($id);

        if (!$modulo) {
            return $this->response->setJSON(['error' => 'Módulo no encontrado.']);
        }

        return $this->response->setJSON($modulo);
    }

    public function guardar()
    {
        $model = new ModuloModel();

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
            'message' => 'Módulo creado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $model = new ModuloModel();

        $modulo = $model->find($id);
        if (!$modulo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Módulo no encontrado.',
            ]);
        }

        $rules = $model->validationRules;
        $rules['codigo'] = 'required|max_length[80]|is_unique[modulos.codigo,id,' . $id . ']';

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
            'message' => 'Módulo actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $model = new ModuloModel();
        $modulo = $model->find($id);

        if (!$modulo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Módulo no encontrado.',
            ]);
        }

        if ($modulo->codigo === 'roles.modulos') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar este módulo.',
            ]);
        }

        $db = \Config\Database::connect();
        $hijos = $db->table('modulos')->where('padre_id', $id)->countAllResults();
        if ($hijos > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar un módulo que tiene submódulos.',
            ]);
        }

        $model->update($id, ['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Módulo desactivado correctamente.',
        ]);
    }
}
