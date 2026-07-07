<?php

namespace App\Controllers;

class ReporteMorosidad extends BaseController
{
    public function index()
    {
        $data['titulo'] = 'Reporte de Morosidad';
        $data['subtitulo'] = 'Cobros pendientes, parciales y vencidos';
        $data['listarUrl'] = current_url() . '/listar';
        return view('reporte_morosidad/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT c.id, c.cliente_id, cl.razon_social,
                   c.periodo, c.fecha_emision, c.fecha_vencimiento,
                   c.monto, c.monto_pagado, (c.monto - c.monto_pagado) as saldo,
                   c.moneda, c.estado,
                   CASE
                       WHEN c.fecha_vencimiento < CURDATE()
                            AND c.estado NOT IN ('pagado','anulado')
                       THEN DATEDIFF(CURDATE(), c.fecha_vencimiento)
                       ELSE 0
                   END as dias_mora
            FROM cobros_mensuales c
            JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.estado IN ('pendiente', 'parcial', 'vencido')
              AND c.activo = 1
            ORDER BY dias_mora DESC, cl.razon_social
        ")->getResult();

        $badges = [
            'pendiente' => 'badge-soft-warning',
            'parcial'   => 'badge-soft-info',
            'vencido'   => 'badge-soft-danger',
        ];

        $data = [];
        foreach ($rows as $r) {
            $saldo = $r->monto - $r->monto_pagado;
            $badge = '<span class="badge ' . ($badges[$r->estado] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado) . '</span>';

            $mora = $r->dias_mora > 0
                ? '<span class="text-danger fw-bold">' . $r->dias_mora . ' días</span>'
                : '<span class="text-muted">' . $r->dias_mora . '</span>';

            $data[] = [
                esc($r->razon_social),
                $r->periodo,
                $r->fecha_emision,
                $r->fecha_vencimiento,
                number_format($r->monto, 2),
                number_format($r->monto_pagado, 2),
                number_format($saldo, 2),
                $r->moneda,
                $badge,
                $mora,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
