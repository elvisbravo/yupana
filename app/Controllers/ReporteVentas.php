<?php

namespace App\Controllers;

class ReporteVentas extends BaseController
{
    public function index()
    {
        return view('reporte_ventas/index');
    }

    public function listar()
    {
        $db = \Config\Database::connect();

        $rows = $db->query("
            SELECT
                c.id, c.serie, c.numero, c.fecha_emision, c.subtotal, c.igv, c.total, c.moneda, c.estado_sunat,
                tc.nombre AS tipo_nombre, tc.codigo AS tipo_codigo,
                cl.razon_social, cl.ruc, cl.direccion,
                u.departamento, u.provincia, u.distrito
            FROM comprobantes_emitidos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            LEFT JOIN ubigeos u ON u.id = cl.ubigeo_id
            ORDER BY c.fecha_emision ASC, c.serie ASC, CAST(c.numero AS UNSIGNED) ASC
        ")->getResult();

        $badges = [
            'aceptado'  => 'badge-soft-success',
            'pendiente' => 'badge-soft-warning',
            'rechazado' => 'badge-soft-danger',
            'anulado'   => 'badge-soft-secondary',
            'enviado'   => 'badge-soft-info',
            'baja'      => 'badge-soft-dark',
        ];

        $data = [];
        foreach ($rows as $r) {
            $ubigeoTexto = trim(implode(' - ', array_filter([$r->departamento, $r->provincia, $r->distrito])));
            $estadoBadge = '<span class="badge ' . ($badges[$r->estado_sunat] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado_sunat) . '</span>';

            $data[] = [
                $r->serie . '-' . $r->numero,
                esc($r->tipo_nombre),
                $r->fecha_emision,
                esc($r->razon_social ?? '—'),
                $r->ruc ?? '—',
                esc($r->direccion ?? '—'),
                $ubigeoTexto !== '' ? esc($ubigeoTexto) : '—',
                number_format($r->subtotal, 2),
                number_format($r->igv, 2),
                number_format($r->total, 2) . ' ' . esc($r->moneda),
                $estadoBadge,
                $r->tipo_codigo,                 // [11] TD code (01, 03, 07…)
                number_format($r->total, 2),     // [12] Total numérico puro
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
