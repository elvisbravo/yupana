<?php

namespace App\Controllers;

class Empresa extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['empresa'] = $db->table('empresa')->where('id', 1)->get()->getRow();
        $data['sedes'] = $db->table('sedes')->where('empresa_id', 1)->where('activo', 1)->orderBy('nombre')->get()->getResult();
        $data['comprobantes'] = $db->table('tipos_comprobante')->orderBy('nombre')->get()->getResult();
        return view('empresa/index', $data);
    }

    public function guardarEmpresa()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        unset($data['id']);

        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = $logo->getRandomName();
            $logo->move('uploads/empresa', $newName);
            $data['logo_url'] = 'uploads/empresa/' . $newName;
        }

        $db->table('empresa')->where('id', 1)->update($data);

        // Auto-create/update main sede (anexo 0000)
        $sedeData = [
            'empresa_id' => 1,
            'nombre'     => 'Principal',
            'direccion'  => $data['direccion_fiscal'] ?? '',
            'anexo'      => '0000',
            'telefono'   => $data['telefono'] ?? '',
            'correo'     => $data['correo'] ?? '',
        ];
        $existing = $db->table('sedes')->where('empresa_id', 1)->where('anexo', '0000')->where('activo', 1)->get()->getRow();
        if ($existing) {
            $db->table('sedes')->where('id', $existing->id)->update($sedeData);
        } else {
            $db->table('sedes')->insert($sedeData);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Datos de la empresa actualizados correctamente.',
        ]);
    }

    public function listarSedes()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('sedes')->where('empresa_id', 1)->where('activo', 1)->orderBy('nombre')->get()->getResult();

        $data = [];
        foreach ($rows as $r) {
            $acciones = '<button class="btn btn-sm btn-soft-info editar-sede" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-sede" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $envio = $r->tipo_envio == 'produccion'
                ? '<span class="badge badge-soft-success">Producción</span>'
                : '<span class="badge badge-soft-warning">Prueba</span>';

            $data[] = [
                esc($r->nombre),
                esc($r->direccion ?: '—'),
                esc($r->anexo ?: '—'),
                esc($r->telefono ?: '—'),
                esc($r->correo ?: '—'),
                $envio,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtenerSede($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('sedes')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Sede no encontrada.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardarSede()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        unset($data['id']);
        $data['empresa_id'] = 1;
        $db->table('sedes')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sede guardada correctamente.',
        ]);
    }

    public function actualizarSede($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('sedes')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sede no encontrada.']);
        }

        $data = $this->request->getPost();
        unset($data['id']);
        $db->table('sedes')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sede actualizada correctamente.',
        ]);
    }

    public function eliminarSede($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('sedes')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sede no encontrada.']);
        }

        $db->table('sedes')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sede eliminada correctamente.',
        ]);
    }

    public function listarCorrelativos()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.*, s.nombre as sede_nombre, tc.nombre as comprobante_nombre, tc.abreviatura
            FROM correlativos c
            JOIN sedes s ON s.id = c.sede_id
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            WHERE c.activo = 1
            ORDER BY s.nombre, tc.nombre
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $envio = $r->tipo_envio == 'prueba'
                ? '<span class="badge badge-soft-warning">Prueba</span>'
                : '<span class="badge badge-soft-success">Producción</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-correlativo" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-correlativo" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->sede_nombre),
                esc($r->comprobante_nombre),
                esc($r->serie),
                $r->correlativo_inicio,
                $r->correlativo_actual,
                $envio,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtenerCorrelativo($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('correlativos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Correlativo no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardarCorrelativo()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        unset($data['id']);

        $existing = $db->table('correlativos')
            ->where('sede_id', $data['sede_id'])
            ->where('serie', $data['serie'])
            ->where('activo', 1)
            ->get()->getRow();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe un correlativo con esa serie para esta sede.',
            ]);
        }

        $db->table('correlativos')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Correlativo guardado correctamente.',
        ]);
    }

    public function actualizarCorrelativo($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('correlativos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Correlativo no encontrado.']);
        }

        $data = $this->request->getPost();
        unset($data['id']);

        $existing = $db->table('correlativos')
            ->where('sede_id', $data['sede_id'])
            ->where('serie', $data['serie'])
            ->where('id !=', $id)
            ->where('activo', 1)
            ->get()->getRow();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe otro correlativo con esa serie para esta sede.',
            ]);
        }

        $db->table('correlativos')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Correlativo actualizado correctamente.',
        ]);
    }

    public function eliminarCorrelativo($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('correlativos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Correlativo no encontrado.']);
        }

        $db->table('correlativos')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Correlativo eliminado correctamente.',
        ]);
    }
}
