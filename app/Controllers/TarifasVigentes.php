<?php

namespace App\Controllers;

class TarifasVigentes extends BaseController
{
    public function index()
    {
        return view('tarifas_vigentes/index');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT t.id, t.cliente_id, t.monto, t.moneda, t.fecha_inicio, t.motivo_cambio,
                   cl.razon_social
            FROM tarifas_mensuales t
            JOIN clientes cl ON cl.id = t.cliente_id
            WHERE t.fecha_fin IS NULL AND t.activo = 1
            ORDER BY cl.razon_social
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                esc($r->razon_social),
                number_format($r->monto, 2),
                $r->moneda,
                $r->fecha_inicio,
                esc($r->motivo_cambio ?: '—'),
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
