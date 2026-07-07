<?php

namespace App\Controllers;

class PagosAplicados extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['cobros'] = $db->query("
            SELECT c.id, cl.razon_social, c.periodo, c.monto, c.monto_pagado,
                   (c.monto - c.monto_pagado) as saldo, c.moneda
            FROM cobros_mensuales c
            JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.activo = 1 AND c.estado IN ('pendiente', 'parcial', 'vencido')
            ORDER BY cl.razon_social, c.periodo
        ")->getResult();
        $data['metodos'] = $db->table('metodos_pago')->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('pagos_aplicados/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT p.*, cl.razon_social, c.periodo, mp.nombre as metodo_nombre
            FROM pagos p
            JOIN cobros_mensuales c ON c.id = p.cobro_id
            JOIN clientes cl ON cl.id = c.cliente_id
            JOIN metodos_pago mp ON mp.id = p.metodo_pago_id
            WHERE p.activo = 1
            ORDER BY p.fecha_pago DESC, cl.razon_social
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $acciones = '<button class="btn btn-sm btn-soft-info editar-pago" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-pago" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                $r->periodo,
                esc($r->metodo_nombre),
                $r->fecha_pago,
                number_format($r->monto, 2),
                $r->moneda,
                esc($r->numero_operacion ?: '—'),
                esc($r->banco ?: '—'),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('pagos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Pago no encontrado.']);
        }
        return $this->response->setJSON($row);
    }

    private function recalcularCobro($db, $cobro_id)
    {
        $total = $db->table('pagos')
            ->selectSum('monto', 'total')
            ->where('cobro_id', $cobro_id)
            ->where('activo', 1)
            ->get()->getRow()->total ?? 0;

        $cobro = $db->table('cobros_mensuales')->where('id', $cobro_id)->get()->getRow();
        if (!$cobro) return;

        $nuevo_estado = 'pendiente';
        if ($total <= 0) {
            $nuevo_estado = 'pendiente';
        } elseif ($total >= $cobro->monto) {
            $nuevo_estado = 'pagado';
        } else {
            $nuevo_estado = 'parcial';
        }

        $db->table('cobros_mensuales')
            ->where('id', $cobro_id)
            ->update(['monto_pagado' => $total, 'estado' => $nuevo_estado]);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['numero_operacion'] = $data['numero_operacion'] ?? null;
        $data['banco'] = $data['banco'] ?? null;
        $data['comprobante_url'] = $data['comprobante_url'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;
        $data['usuario_id'] = session('user_id');

        $db->table('pagos')->insert($data);
        $pago_id = $db->insertID();

        $this->recalcularCobro($db, $data['cobro_id']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago registrado correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('pagos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pago no encontrado.']);
        }

        $data = $this->request->getPost();
        $data['numero_operacion'] = $data['numero_operacion'] ?? null;
        $data['banco'] = $data['banco'] ?? null;
        $data['comprobante_url'] = $data['comprobante_url'] ?? null;
        $data['observaciones'] = $data['observaciones'] ?? null;

        $db->table('pagos')->where('id', $id)->update($data);

        $this->recalcularCobro($db, $row->cobro_id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('pagos')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pago no encontrado.']);
        }

        $db->table('pagos')->where('id', $id)->update(['activo' => 0]);

        $this->recalcularCobro($db, $row->cobro_id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago eliminado correctamente.',
        ]);
    }
}
