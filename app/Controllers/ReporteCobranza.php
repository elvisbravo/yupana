<?php

namespace App\Controllers;

class ReporteCobranza extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['periodos'] = $db->query("SELECT DISTINCT periodo FROM cobros_mensuales WHERE activo = 1 ORDER BY periodo DESC")->getResult();
        return view('reporte_cobranza/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $periodo = $this->request->getGet('periodo');

        $sql = "
            SELECT cl.id as cliente_id, cl.razon_social,
                   c.periodo, COUNT(*) as total_cobros,
                   SUM(c.monto) as monto_total,
                   SUM(c.monto_pagado) as total_pagado,
                   SUM(c.monto - c.monto_pagado) as saldo_pendiente
            FROM cobros_mensuales c
            JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.activo = 1
        ";
        $params = [];
        if ($periodo) {
            $sql .= " AND c.periodo = ?";
            $params[] = $periodo;
        }
        $sql .= " GROUP BY cl.id, cl.razon_social, c.periodo
                  ORDER BY c.periodo DESC, cl.razon_social";

        $rows = $db->query($sql, $params)->getResult();

        $data = [];
        foreach ($rows as $r) {
            $pct = $r->monto_total > 0 ? round(($r->total_pagado / $r->monto_total) * 100) : 0;
            $data[] = [
                esc($r->razon_social),
                $r->periodo,
                $r->total_cobros,
                number_format($r->monto_total, 2),
                number_format($r->total_pagado, 2),
                number_format($r->saldo_pendiente, 2),
                $pct . '%',
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
