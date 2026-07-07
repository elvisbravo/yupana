<?php

namespace App\Controllers;

class Alertas extends BaseController
{
    private function renderView($titulo, $tipo)
    {
        $db = \Config\Database::connect();
        $data['titulo'] = $titulo;
        $data['tipo'] = $tipo;
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        $data['usuarios'] = $db->table('usuarios')->where('estado', 'activo')->orderBy('nombres')->get()->getResult();
        return view('alertas/index', $data);
    }

    public function vencimientoContrato()
    {
        return $this->renderView('Vencimiento de Contratos', 'vencimiento_contrato');
    }

    public function vencimientoDocumento()
    {
        return $this->renderView('Vencimiento de Documentos', 'vencimiento_documento');
    }

    public function cobroVencido()
    {
        return $this->renderView('Cobros Vencidos', 'cobro_vencido');
    }

    public function declaracion()
    {
        return $this->renderView('Declaraciones Próximas', 'declaracion_proxima');
    }

    public function cumpleanos()
    {
        return $this->renderView('Cumpleaños', 'cumpleanos');
    }

    public function listar($tipo)
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT a.*, cl.razon_social, u.nombres, u.apellidos
            FROM alertas_sistema a
            LEFT JOIN clientes cl ON cl.id = a.cliente_id
            LEFT JOIN usuarios u ON u.id = a.usuario_asignado_id
            WHERE a.tipo = ?
            ORDER BY a.fecha_alerta, a.created_at DESC
        ", [$tipo])->getResult();

        $badges = [
            'pendiente'  => 'badge-soft-warning',
            'vista'      => 'badge-soft-info',
            'resuelta'   => 'badge-soft-success',
            'descartada' => 'badge-soft-secondary',
        ];

        $data = [];
        foreach ($rows as $r) {
            $cliente = $r->razon_social ? esc($r->razon_social) : '—';
            $asignado = $r->nombres ? esc($r->nombres . ' ' . $r->apellidos) : '—';
            $estadoBadge = '<span class="badge ' . ($badges[$r->estado] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado) . '</span>';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-alerta" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> ';
            if ($r->estado === 'pendiente') {
                $acciones .= '<button class="btn btn-sm btn-soft-info vista-alerta" data-id="' . $r->id . '" title="Marcar como vista">'
                    . '<i data-lucide="eye" style="width:14px;height:14px;"></i></button> ';
            }
            if ($r->estado !== 'resuelta' && $r->estado !== 'descartada') {
                $acciones .= '<button class="btn btn-sm btn-soft-success resolver-alerta" data-id="' . $r->id . '" title="Resolver">'
                    . '<i data-lucide="check-circle" style="width:14px;height:14px;"></i></button> ';
            }
            $acciones .= '<button class="btn btn-sm btn-soft-danger descartar-alerta" data-id="' . $r->id . '" title="Descartar">'
                . '<i data-lucide="x" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->titulo),
                $cliente,
                $r->fecha_alerta,
                $asignado,
                esc($r->mensaje ? mb_substr($r->mensaje, 0, 100) . (mb_strlen($r->mensaje) > 100 ? '...' : '') : '—'),
                $estadoBadge,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('alertas_sistema')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['error' => 'Alerta no encontrada.']);
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['cliente_id'] = $data['cliente_id'] ?: null;
        $data['usuario_asignado_id'] = $data['usuario_asignado_id'] ?: null;
        $data['mensaje'] = $data['mensaje'] ?? null;

        $db->table('alertas_sistema')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Alerta guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('alertas_sistema')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Alerta no encontrada.']);

        $data = $this->request->getPost();
        $data['cliente_id'] = $data['cliente_id'] ?: null;
        $data['usuario_asignado_id'] = $data['usuario_asignado_id'] ?: null;
        $data['mensaje'] = $data['mensaje'] ?? null;

        $db->table('alertas_sistema')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Alerta actualizada correctamente.',
        ]);
    }

    private function cambiarEstado($id, $nuevoEstado, $mensaje)
    {
        $db = \Config\Database::connect();
        $row = $db->table('alertas_sistema')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Alerta no encontrada.']);

        $db->table('alertas_sistema')->where('id', $id)->update(['estado' => $nuevoEstado]);

        return $this->response->setJSON(['success' => true, 'message' => $mensaje]);
    }

    public function vista($id)
    {
        return $this->cambiarEstado($id, 'vista', 'Alerta marcada como vista.');
    }

    public function resolver($id)
    {
        return $this->cambiarEstado($id, 'resuelta', 'Alerta resuelta correctamente.');
    }

    public function descartar($id)
    {
        return $this->cambiarEstado($id, 'descartada', 'Alerta descartada.');
    }
}
