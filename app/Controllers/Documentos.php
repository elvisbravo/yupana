<?php

namespace App\Controllers;

class Documentos extends BaseController
{
    public function archivos()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['tipos'] = $db->table('tipos_documento')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('documentos/archivos', $data);
    }

    public function vencidos()
    {
        return view('documentos/vencidos');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT d.*, cl.razon_social, td.nombre as tipo_nombre
            FROM documentos_cliente d
            JOIN clientes cl ON cl.id = d.cliente_id
            JOIN tipos_documento td ON td.id = d.tipo_documento_id
            WHERE d.activo = 1
            ORDER BY cl.razon_social, d.fecha_vencimiento
        ")->getResult();

        return $this->buildData($rows, true);
    }

    public function listarVencidos()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT d.*, cl.razon_social, td.nombre as tipo_nombre
            FROM documentos_cliente d
            JOIN clientes cl ON cl.id = d.cliente_id
            JOIN tipos_documento td ON td.id = d.tipo_documento_id
            WHERE d.activo = 1
              AND d.fecha_vencimiento IS NOT NULL
              AND d.fecha_vencimiento < CURDATE()
            ORDER BY d.fecha_vencimiento, cl.razon_social
        ")->getResult();

        return $this->buildData($rows, false);
    }

    private function buildData($rows, $withActions)
    {
        $data = [];
        foreach ($rows as $r) {
            $fecha_doc = $r->fecha_documento ?: '—';
            $fecha_venc = $r->fecha_vencimiento ?: '—';
            $tamano = $r->archivo_size ? $this->formatSize($r->archivo_size) : '—';

            $acciones = '';
            if ($withActions) {
                $acciones = '<button class="btn btn-sm btn-soft-info editar-documento" data-id="' . $r->id . '" title="Editar">'
                    . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                    . '<button class="btn btn-sm btn-soft-danger eliminar-documento" data-id="' . $r->id . '" title="Eliminar">'
                    . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
            }

            $data[] = [
                esc($r->razon_social),
                esc($r->tipo_nombre),
                esc($r->nombre_documento),
                esc($r->descripcion ?: '—'),
                $fecha_doc,
                $fecha_venc,
                $tamano,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('documentos_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Documento no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['archivo_url'] = $data['archivo_url'] ?? '';
        $data['archivo_nombre'] = $data['archivo_nombre'] ?? '';
        $data['archivo_size'] = $data['archivo_size'] ?: null;
        $data['archivo_hash'] = $data['archivo_hash'] ?? null;
        $data['descripcion'] = $data['descripcion'] ?? null;
        $data['fecha_documento'] = $data['fecha_documento'] ?: null;
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?: null;
        $data['usuario_id'] = session('user_id');

        $db->table('documentos_cliente')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento guardado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('documentos_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado.']);
        }

        $data = $this->request->getPost();
        $data['archivo_url'] = $data['archivo_url'] ?? '';
        $data['archivo_nombre'] = $data['archivo_nombre'] ?? '';
        $data['archivo_size'] = $data['archivo_size'] ?: null;
        $data['archivo_hash'] = $data['archivo_hash'] ?? null;
        $data['descripcion'] = $data['descripcion'] ?? null;
        $data['fecha_documento'] = $data['fecha_documento'] ?: null;
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?: null;

        $db->table('documentos_cliente')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('documentos_cliente')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento no encontrado.']);
        }

        $db->table('documentos_cliente')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento eliminado correctamente.',
        ]);
    }
}
