<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Usuarios extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['roles'] = $db->table('roles')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('usuarios/index', $data);
    }

    public function listar()
    {
        $model = new UsuarioModel();
        $usuarios = $model->where('estado', 'activo')->orderBy('created_at', 'DESC')->findAll();

        $db = \Config\Database::connect();
        $roles = $db->table('roles')->where('activo', 1)->get()->getResult();
        $mapa = [];
        foreach ($roles as $r) {
            $mapa[$r->id] = $r->nombre;
        }

        $data = [];
        foreach ($usuarios as $u) {
            $nombreCompleto = esc($u->nombres) . ' ' . esc($u->apellidos);

            $estados = ['activo' => 'success', 'inactivo' => 'secondary', 'bloqueado' => 'danger'];
            $estadoClase = $estados[$u->estado] ?? 'secondary';
            $estadoHtml = '<span class="badge badge-soft-' . $estadoClase . '">' . ucfirst($u->estado) . '</span>';

            $ultimoAcceso = $u->ultimo_acceso
                ? date('d/m/Y H:i', strtotime($u->ultimo_acceso))
                : '<span class="text-muted">Nunca</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-usuario" data-id="' . $u->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';

            if ($u->id !== session('user_id')) {
                $acciones .= '<button class="btn btn-sm btn-soft-danger eliminar-usuario" data-id="' . $u->id . '" title="Eliminar">'
                    . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
            }

            $data[] = [
                $nombreCompleto,
                esc($u->email),
                esc($u->dni ?: '—'),
                esc($mapa[$u->rol_id] ?? '—'),
                $estadoHtml,
                $ultimoAcceso,
                $acciones,
                $u->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $model = new UsuarioModel();
        $usuario = $model->find($id);

        if (!$usuario) {
            return $this->response->setJSON(['error' => 'Usuario no encontrado.']);
        }

        unset($usuario->password_hash);

        $rol = \Config\Database::connect()->table('roles')->where('id', $usuario->rol_id)->get()->getRow();
        $usuario->rol_nombre = $rol->nombre ?? '';

        return $this->response->setJSON($usuario);
    }

    public function guardar()
    {
        $model = new UsuarioModel();

        $password = $this->request->getPost('password');
        if (!$password) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La contraseña es obligatoria.',
                'errors'  => ['password' => 'La contraseña es obligatoria.'],
            ]);
        }

        if (!$this->validate($model->validationRules, $model->validationMessages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $data = $this->request->getPost();
        $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        unset($data['password']);

        $model->save($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usuario creado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $model = new UsuarioModel();
        $usuario = $model->find($id);
        if (!$usuario) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ]);
        }

        $rules = $model->validationRules;
        $rules['email'] = 'required|valid_email|is_unique[usuarios.email,id,' . $id . ']';
        $rules['dni'] = 'permit_empty|exact_length[8]|is_unique[usuarios.dni,id,' . $id . ']';

        if (!$this->validate($rules, $model->validationMessages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $data = $this->request->getPost();
        $password = $data['password'] ?? '';
        unset($data['password']);

        if ($password) {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $model->update($id, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usuario actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $model = new UsuarioModel();
        $usuario = $model->find($id);
        if (!$usuario) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ]);
        }

        if ($usuario->id === session('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta.',
            ]);
        }

        $model->update($id, ['estado' => 'inactivo']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usuario desactivado correctamente.',
        ]);
    }
}
