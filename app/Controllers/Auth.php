<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login()
    {
        $session = service('session');
        if ($session->get('logged_in')) {
            return redirect()->to('/home');
        }
        return view('auth/login');
    }

    public function loginPost()
    {
        $json = $this->request->getJSON(true);
        $email = $json['email'] ?? '';
        $password = $json['password'] ?? '';

        if (!$email || !$password) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Correo y contraseña son requeridos.',
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('usuarios');
        $user = $builder->where('email', $email)->get()->getRow();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Credenciales inválidas.',
            ]);
        }

        if ($user->estado !== 'activo') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cuenta bloqueada o inactiva. Contacte al administrador.',
            ]);
        }

        if (!password_verify($password, $user->password_hash)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Credenciales inválidas.',
            ]);
        }

        $rol = $db->table('roles')->where('id', $user->rol_id)->get()->getRow();

        $session = service('session');
        $session->set([
            'user_id'    => $user->id,
            'rol_id'     => $user->rol_id,
            'rol_nombre' => $rol->nombre ?? '',
            'nombres'    => $user->nombres,
            'apellidos'  => $user->apellidos,
            'email'      => $user->email,
            'logged_in'  => true,
        ]);

        // Update last access
        $builder->where('id', $user->id)->update(['ultimo_acceso' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'user' => [
                'nombres'   => $user->nombres,
                'apellidos' => $user->apellidos,
                'email'     => $user->email,
            ],
        ]);
    }

    public function logout()
    {
        $session = service('session');
        $session->destroy();
        return redirect()->to('/login');
    }
}