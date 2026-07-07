<?php

namespace App\Controllers;

class Regimenes extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('regimenes/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT rh.*, cl.razon_social, rt.nombre as regimen_nombre, u.nombres as usuario_nombre
            FROM cliente_regimen_historial rh
            JOIN clientes cl ON cl.id = rh.cliente_id
            JOIN regimenes_tributarios rt ON rt.id = rh.regimen_id
            LEFT JOIN usuarios u ON u.id = rh.usuario_id
            ORDER BY cl.razon_social, rh.fecha_inicio DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $fechaFin = $r->fecha_fin
                ? date('d/m/Y', strtotime($r->fecha_fin))
                : '<span class="badge badge-soft-success">Vigente</span>';

            $data[] = [
                esc($r->razon_social),
                esc($r->regimen_nombre),
                date('d/m/Y', strtotime($r->fecha_inicio)),
                $fechaFin,
                esc($r->motivo ?: '—'),
                esc($r->usuario_nombre ?: '—'),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
