<?php

namespace App\Controllers;

class Tareas extends BaseController
{
    private function renderView($titulo, $filtro)
    {
        $db = \Config\Database::connect();
        $data['titulo'] = $titulo;
        $data['filtro'] = $filtro;
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['usuarios'] = $db->table('usuarios')->where('estado', 'activo')->orderBy('nombres')->get()->getResult();
        return view('tareas/index', $data);
    }

    public function mias()
    {
        return $this->renderView('Mis Tareas', 'todas');
    }

    public function asignadas()
    {
        return $this->renderView('Asignadas a Mí', 'asignadas');
    }

    public function creadas()
    {
        return $this->renderView('Creadas por Mí', 'creadas');
    }

    public function listar($filtro)
    {
        $db = \Config\Database::connect();
        $userId = session('user_id');

        $where = "t.estado != 'cancelada'";
        if ($filtro === 'asignadas') {
            $where = "t.asignado_a_id = $userId AND t.estado != 'cancelada'";
        } elseif ($filtro === 'creadas') {
            $where = "t.creado_por_id = $userId AND t.estado != 'cancelada'";
        }

        $rows = $db->query("
            SELECT t.*, cl.razon_social,
                   a.nombres as asignado_nombres, a.apellidos as asignado_apellidos,
                   c.nombres as creador_nombres, c.apellidos as creador_apellidos
            FROM tareas t
            LEFT JOIN clientes cl ON cl.id = t.cliente_id
            LEFT JOIN usuarios a ON a.id = t.asignado_a_id
            JOIN usuarios c ON c.id = t.creado_por_id
            WHERE $where
            ORDER BY t.fecha_vencimiento IS NULL, t.fecha_vencimiento, t.created_at DESC
        ")->getResult();

        $badges = [
            'pendiente'    => 'badge-soft-warning',
            'en_progreso'  => 'badge-soft-info',
            'completada'   => 'badge-soft-success',
        ];

        $prioridadBadges = [
            'baja'    => 'badge-soft-secondary',
            'media'   => 'badge-soft-primary',
            'alta'    => 'badge-soft-warning',
            'urgente' => 'badge-soft-danger',
        ];

        $data = [];
        foreach ($rows as $r) {
            $cliente = $r->razon_social ? esc($r->razon_social) : '—';
            $asignado = $r->asignado_nombres ? esc($r->asignado_nombres . ' ' . $r->asignado_apellidos) : '—';
            $creador = esc($r->creador_nombres . ' ' . $r->creador_apellidos);
            $vencimiento = $r->fecha_vencimiento ?: '—';
            $estadoBadge = '<span class="badge ' . ($badges[$r->estado] ?? 'badge-soft-secondary') . '">' . ucfirst(str_replace('_', ' ', $r->estado)) . '</span>';
            $prioridadBadge = '<span class="badge ' . ($prioridadBadges[$r->prioridad] ?? 'badge-soft-secondary') . '">' . ucfirst($r->prioridad) . '</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-tarea" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';
            if ($r->estado !== 'completada') {
                $acciones .= '<button class="btn btn-sm btn-soft-success completar-tarea" data-id="' . $r->id . '" title="Completar">'
                    . '<i data-lucide="check" style="width:14px;height:14px;"></i></button> ';
            }
            $acciones .= '<button class="btn btn-sm btn-soft-danger eliminar-tarea" data-id="' . $r->id . '" title="Cancelar">'
                . '<i data-lucide="x" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->titulo),
                $cliente,
                $prioridadBadge,
                $estadoBadge,
                $asignado,
                $creador,
                $vencimiento,
                esc($r->descripcion ? mb_substr($r->descripcion, 0, 80) . (mb_strlen($r->descripcion) > 80 ? '...' : '') : '—'),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tareas')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['error' => 'Tarea no encontrada.']);
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['cliente_id'] = $data['cliente_id'] ?: null;
        $data['asignado_a_id'] = $data['asignado_a_id'] ?: null;
        $data['descripcion'] = $data['descripcion'] ?? null;
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?: null;
        $data['creado_por_id'] = session('user_id');

        $db->table('tareas')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarea guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tareas')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Tarea no encontrada.']);

        $data = $this->request->getPost();
        $data['cliente_id'] = $data['cliente_id'] ?: null;
        $data['asignado_a_id'] = $data['asignado_a_id'] ?: null;
        $data['descripcion'] = $data['descripcion'] ?? null;
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?: null;

        $db->table('tareas')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarea actualizada correctamente.',
        ]);
    }

    public function completar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tareas')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Tarea no encontrada.']);
        if ($row->estado === 'completada') {
            return $this->response->setJSON(['success' => false, 'message' => 'La tarea ya está completada.']);
        }

        $db->table('tareas')->where('id', $id)->update([
            'estado' => 'completada',
            'fecha_completada' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Tarea completada correctamente.']);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tareas')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Tarea no encontrada.']);

        $db->table('tareas')->where('id', $id)->update(['estado' => 'cancelada']);

        return $this->response->setJSON(['success' => true, 'message' => 'Tarea cancelada correctamente.']);
    }
}
