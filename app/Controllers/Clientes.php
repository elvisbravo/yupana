<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class Clientes extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['regimenes'] = $db->table('regimenes_tributarios')->where('activo', 1)->get()->getResult();
        $data['ubigeos'] = $db->table('ubigeos')->where('activo', 1)->orderBy('departamento, provincia, distrito')->get()->getResult();

        return view('clientes/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();

        $clientes = $db->query("
            SELECT c.*,
                tm.monto AS tarifa_monto,
                tm.fecha_inicio AS tarifa_fecha_inicio,
                tm.moneda AS tarifa_moneda
            FROM clientes c
            LEFT JOIN tarifas_mensuales tm ON tm.cliente_id = c.id AND tm.activo = 1
                AND tm.fecha_inicio = (
                    SELECT MAX(tm2.fecha_inicio)
                    FROM tarifas_mensuales tm2
                    WHERE tm2.cliente_id = c.id AND tm2.activo = 1
                )
            WHERE c.estado = 'activo'
            ORDER BY c.created_at DESC
        ")->getResult();

        $regimenes = $db->table('regimenes_tributarios')->where('activo', 1)->get()->getResult();
        $mapa = [];
        foreach ($regimenes as $r) {
            $mapa[$r->id] = $r->nombre;
        }

        $data = [];
        foreach ($clientes as $c) {
            $nombre = esc($c->razon_social);
            if ($c->nombre_comercial) {
                $nombre .= '<br><small class="text-muted">' . esc($c->nombre_comercial) . '</small>';
            }

            $estados = ['activo' => 'success', 'inactivo' => 'secondary', 'suspendido' => 'warning', 'baja' => 'danger'];
            $estadoClase = $estados[$c->estado] ?? 'secondary';
            $estadoHtml = '<span class="badge badge-soft-' . $estadoClase . '">' . ucfirst($c->estado) . '</span>';

            $tarifaHtml = '—';
            if ($c->tarifa_monto) {
                $mes = strtoupper(date('M', strtotime($c->tarifa_fecha_inicio)));
                $anio = date('Y', strtotime($c->tarifa_fecha_inicio));
                $moneda = $c->tarifa_moneda ?: 'S/';
                $tarifaHtml = '<span class="badge badge-soft-success">' . $mes . ' ' . $anio . ': ' . $moneda . ' ' . number_format($c->tarifa_monto, 2) . '</span>';
            }

            $acciones = '<div class="dropdown">'
                . '<button class="btn btn-sm btn-soft-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-lucide="more-horizontal" style="width:14px;height:14px;"></i></button>'
                . '<ul class="dropdown-menu dropdown-menu-end">'
                . '<li><a class="dropdown-item editar-cliente" href="#" data-id="' . $c->id . '"><i data-lucide="pencil" class="me-2" style="width:14px;height:14px;"></i>Editar</a></li>'
                . '<li><a class="dropdown-item regimen-cliente" href="#" data-id="' . $c->id . '"><i data-lucide="shield" class="me-2" style="width:14px;height:14px;"></i>Régimen Tributario</a></li>'
                . '<li><hr class="dropdown-divider"></li>'
                . '<li><a class="dropdown-item text-danger eliminar-cliente" href="#" data-id="' . $c->id . '"><i data-lucide="trash-2" class="me-2" style="width:14px;height:14px;"></i>Eliminar</a></li>'
                . '</ul></div>';

            $data[] = [
                $c->ruc ?: '—',
                $nombre,
                esc($c->email ?: '—'),
                $tarifaHtml,
                esc($mapa[$c->regimen_actual_id] ?? '—'),
                $estadoHtml,
                $acciones,
                $c->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $model = new ClienteModel();
        $cliente = $model->find($id);

        if (!$cliente) {
            return $this->response->setJSON(['error' => 'Cliente no encontrado.']);
        }

        $db = \Config\Database::connect();
        $tarifa = $db->table('tarifas_mensuales')
            ->where('cliente_id', $id)
            ->where('fecha_fin', null)
            ->orderBy('created_at', 'DESC')
            ->get()->getRow();

        $cliente->monto_mensual = $tarifa ? (float)$tarifa->monto : null;
        $cliente->tarifa_fecha_inicio = $tarifa ? $tarifa->fecha_inicio : null;

        return $this->response->setJSON($cliente);
    }

    public function guardar()
    {
        $model = new ClienteModel();

        if (!$this->validate($model->validationRules, $model->validationMessages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $data = $this->request->getPost();
        $data['usuario_registro_id'] = session('user_id');
        $data['presenta_balance'] = $this->request->getPost('presenta_balance') ? 1 : 0;
        $model->save($data);
        $clienteId = $model->getInsertID();

        $monto = $this->request->getPost('monto_mensual');
        $fechaInicio = $this->request->getPost('tarifa_fecha_inicio') ?: $data['fecha_alta'];

        if ($monto && $clienteId) {
            $db->table('tarifas_mensuales')->insert([
                'cliente_id'  => $clienteId,
                'monto'       => $monto,
                'moneda'      => 'PEN',
                'fecha_inicio' => $fechaInicio,
                'usuario_id'  => session('user_id'),
            ]);
        }

        $regimenId = $data['regimen_actual_id'] ?? null;
        if ($regimenId && $clienteId) {
            $db->table('cliente_regimen_historial')->insert([
                'cliente_id'  => $clienteId,
                'regimen_id'  => $regimenId,
                'fecha_inicio' => $fechaInicio,
                'usuario_id'  => session('user_id'),
            ]);
        }

        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cliente registrado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $model = new ClienteModel();

        $cliente = $model->find($id);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ]);
        }

        $rules = $model->validationRules;
        $rules['ruc'] = 'permit_empty|exact_length[11]|is_unique[clientes.ruc,id,' . $id . ']';

        if (!$this->validate($rules, $model->validationMessages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $data = $this->request->getPost();
        $data['presenta_balance'] = $this->request->getPost('presenta_balance') ? 1 : 0;
        $model->update($id, $data);

        $monto = $this->request->getPost('monto_mensual');
        $fechaInicio = $this->request->getPost('tarifa_fecha_inicio');

        if ($monto && $fechaInicio) {
            $db->table('tarifas_mensuales')->where('cliente_id', $id)->where('fecha_fin', null)->update(['fecha_fin' => date('Y-m-d')]);

            $db->table('tarifas_mensuales')->insert([
                'cliente_id'  => $id,
                'monto'       => $monto,
                'moneda'      => 'PEN',
                'fecha_inicio' => $fechaInicio,
                'usuario_id'  => session('user_id'),
            ]);
        }

        $regimenId = $data['regimen_actual_id'] ?? null;
        if ($regimenId) {
            $db->table('cliente_regimen_historial')->where('cliente_id', $id)->where('fecha_fin', null)->update(['fecha_fin' => date('Y-m-d')]);

            $db->table('cliente_regimen_historial')->insert([
                'cliente_id'  => $id,
                'regimen_id'  => $regimenId,
                'fecha_inicio' => $fechaInicio ?: date('Y-m-d'),
                'usuario_id'  => session('user_id'),
            ]);
        }

        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cliente actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $model = new ClienteModel();
        $cliente = $model->find($id);

        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ]);
        }

        $model->update($id, ['estado' => 'inactivo', 'usuario_id_eliminado' => session('usuario_id')]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cliente desactivado correctamente.',
        ]);
    }
}
