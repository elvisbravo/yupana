<?php

namespace App\Controllers;

class ReporteRegimenes extends BaseController
{
    public function index()
    {
        return view('reporte_regimenes/index');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT crh.*, rt.nombre as regimen_nombre,
                   c.razon_social
            FROM cliente_regimen_historial crh
            JOIN regimenes_tributarios rt ON rt.id = crh.regimen_id
            JOIN clientes c ON c.id = crh.cliente_id
            ORDER BY c.razon_social, crh.fecha_inicio DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $fin = $r->fecha_fin ?? '<span class="text-success fw-bold">Vigente</span>';
            $data[] = [
                esc($r->razon_social),
                esc($r->regimen_nombre),
                $r->fecha_inicio,
                $fin,
                esc($r->motivo ?? '—'),
                esc($r->documento_sustento ?? '—'),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
