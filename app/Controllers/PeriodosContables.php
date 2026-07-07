<?php

namespace App\Controllers;

class PeriodosContables extends BaseController
{
    public function apertura()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('periodos_contables/apertura', $data);
    }

    public function cierre()
    {
        return view('periodos_contables/cierre');
    }

    public function ple()
    {
        return view('periodos_contables/ple');
    }

    public function pdt()
    {
        return view('periodos_contables/pdt');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT p.*, cl.razon_social
            FROM periodos_contables p
            JOIN clientes cl ON cl.id = p.cliente_id
            WHERE p.activo = 1
            ORDER BY cl.razon_social, p.anio DESC, p.mes DESC
        ")->getResult();

        return $this->buildData($rows, true);
    }

    public function listarPendientes()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT p.*, cl.razon_social
            FROM periodos_contables p
            JOIN clientes cl ON cl.id = p.cliente_id
            WHERE p.activo = 1 AND p.estado IN ('abierto','en_proceso')
            ORDER BY cl.razon_social, p.anio DESC, p.mes DESC
        ")->getResult();

        return $this->buildData($rows, true);
    }

    public function listarCerrados()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT p.*, cl.razon_social
            FROM periodos_contables p
            JOIN clientes cl ON cl.id = p.cliente_id
            WHERE p.activo = 1 AND p.estado = 'cerrado'
            ORDER BY cl.razon_social, p.anio DESC, p.mes DESC
        ")->getResult();

        return $this->buildData($rows, true);
    }

    private function buildData($rows, $withActions)
    {
        $badges = [
            'abierto'     => 'badge-soft-success',
            'en_proceso'  => 'badge-soft-warning',
            'cerrado'     => 'badge-soft-secondary',
            'presentado'  => 'badge-soft-info',
        ];

        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'];

        $data = [];
        foreach ($rows as $r) {
            $periodo = $meses[(int)$r->mes] . ' ' . $r->anio;
            $badge = '<span class="badge ' . ($badges[$r->estado] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado) . '</span>';
            $f_cierre = $r->fecha_cierre ?: '—';
            $f_pres = $r->fecha_presentacion ?: '—';
            $ops = $r->numero_operaciones ?? '—';

            $acciones = '';
            if ($withActions) {
                $acciones = '<button class="btn btn-sm btn-soft-info editar-periodo" data-id="' . $r->id . '" title="Editar">'
                    . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';
                if ($r->estado === 'abierto' || $r->estado === 'en_proceso') {
                    $acciones .= '<button class="btn btn-sm btn-soft-warning cerrar-periodo" data-id="' . $r->id . '" title="Cerrar período">'
                        . '<i data-lucide="lock" style="width:14px;height:14px;"></i></button> ';
                }
                if ($r->estado === 'cerrado') {
                    $acciones .= '<button class="btn btn-sm btn-soft-info presentar-periodo" data-id="' . $r->id . '" title="Marcar como presentado">'
                        . '<i data-lucide="check-circle" style="width:14px;height:14px;"></i></button> ';
                }
                $acciones .= '<button class="btn btn-sm btn-soft-danger eliminar-periodo" data-id="' . $r->id . '" title="Eliminar">'
                    . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
            }

            $data[] = [
                esc($r->razon_social),
                $periodo,
                $badge,
                $f_cierre,
                $f_pres,
                $ops,
                esc($r->observaciones ?: '—'),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('periodos_contables')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['error' => 'Período no encontrado.']);
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['observaciones'] = $data['observaciones'] ?? null;
        $data['numero_operaciones'] = $data['numero_operaciones'] ?: null;

        $existing = $db->table('periodos_contables')
            ->where('cliente_id', $data['cliente_id'])
            ->where('anio', $data['anio'])
            ->where('mes', $data['mes'])
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe un período para este cliente, año y mes.']);
        }

        $db->table('periodos_contables')->insert($data);

        return $this->response->setJSON(['success' => true, 'message' => 'Período creado correctamente.']);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('periodos_contables')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Período no encontrado.']);

        $data = $this->request->getPost();
        $data['observaciones'] = $data['observaciones'] ?? null;
        $data['numero_operaciones'] = $data['numero_operaciones'] ?: null;

        if (isset($data['fecha_cierre']) && !$data['fecha_cierre']) unset($data['fecha_cierre']);
        if (isset($data['fecha_presentacion']) && !$data['fecha_presentacion']) unset($data['fecha_presentacion']);

        $existing = $db->table('periodos_contables')
            ->where('cliente_id', $data['cliente_id'])
            ->where('anio', $data['anio'])
            ->where('mes', $data['mes'])
            ->where('id !=', $id)
            ->where('activo', 1)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe otro período para este cliente, año y mes.']);
        }

        $db->table('periodos_contables')->where('id', $id)->update($data);

        return $this->response->setJSON(['success' => true, 'message' => 'Período actualizado correctamente.']);
    }

    public function cerrar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('periodos_contables')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Período no encontrado.']);
        if ($row->estado === 'cerrado' || $row->estado === 'presentado') {
            return $this->response->setJSON(['success' => false, 'message' => 'El período ya está cerrado o presentado.']);
        }

        $db->table('periodos_contables')->where('id', $id)->update([
            'estado' => 'cerrado',
            'fecha_cierre' => date('Y-m-d'),
            'usuario_cierre_id' => session('user_id'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período cerrado correctamente.']);
    }

    public function presentar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('periodos_contables')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Período no encontrado.']);
        if ($row->estado !== 'cerrado') {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se pueden presentar períodos cerrados.']);
        }

        $db->table('periodos_contables')->where('id', $id)->update([
            'estado' => 'presentado',
            'fecha_presentacion' => date('Y-m-d'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período marcado como presentado.']);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('periodos_contables')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Período no encontrado.']);

        $db->table('periodos_contables')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período eliminado correctamente.']);
    }
}
