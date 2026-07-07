<?php

namespace App\Controllers;

class Permisos extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['roles'] = $db->table('roles')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        $data['permisos'] = $db->table('permisos')->where('activo', 1)->orderBy('id')->get()->getResult();
        return view('permisos/index', $data);
    }

    public function obtener()
    {
        $rolId = $this->request->getGet('rol_id');
        if (!$rolId) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();

        $modulos = $db->table('modulos')->where('activo', 1)->orderBy('orden')->get()->getResult();

        $asignados = $db->table('roles_modulos')->where('rol_id', $rolId)->get()->getResult();
        $modulosAsignados = [];
        foreach ($asignados as $a) {
            $modulosAsignados[$a->modulo_id] = true;
        }

        $permisosAsignados = $db->table('roles_modulos_permisos')->where('rol_id', $rolId)->get()->getResult();
        $permisosPorModulo = [];
        foreach ($permisosAsignados as $p) {
            $permisosPorModulo[$p->modulo_id][$p->permiso_id] = true;
        }

        $data = [];
        foreach ($modulos as $m) {
            $data[] = [
                'id'       => $m->id,
                'codigo'   => $m->codigo,
                'nombre'   => $m->nombre,
                'padre_id' => $m->padre_id,
                'icono'    => $m->icono,
                'asignado' => isset($modulosAsignados[$m->id]),
                'permisos' => $permisosPorModulo[$m->id] ?? [],
            ];
        }

        return $this->response->setJSON($data);
    }

    public function guardar()
    {
        $rolId = $this->request->getPost('rol_id');
        $modulos = $this->request->getPost('modulos');

        if (!$rolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Seleccione un rol.',
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('roles_modulos')->where('rol_id', $rolId)->delete();
        $db->table('roles_modulos_permisos')->where('rol_id', $rolId)->delete();

        if ($modulos) {
            foreach ($modulos as $moduloId => $data) {
                $db->table('roles_modulos')->insert([
                    'rol_id'    => $rolId,
                    'modulo_id' => $moduloId,
                ]);

                if (!empty($data['permisos'])) {
                    foreach ($data['permisos'] as $permisoId) {
                        $db->table('roles_modulos_permisos')->insert([
                            'rol_id'     => $rolId,
                            'modulo_id'  => $moduloId,
                            'permiso_id' => $permisoId,
                        ]);
                    }
                }
            }
        }

        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Permisos actualizados correctamente.',
        ]);
    }
}
