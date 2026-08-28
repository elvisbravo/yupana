<?php

namespace App\Controllers;

class WhatsappRecordatorios extends BaseController
{
    private const MESES = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    ];

    public function index()
    {
        $db = \Config\Database::connect();
        $periodos = $db->query("
            SELECT DISTINCT c.periodo
            FROM facturas_mensajes_whatsapp f
            INNER JOIN comprobantes_emitidos c ON c.id = f.factura_id
            WHERE c.periodo IS NOT NULL
            ORDER BY c.periodo DESC
        ")->getResult();

        $labels = [];
        foreach ($periodos as $p) {
            $labels[$p->periodo] = $this->nombrePeriodo($p->periodo);
        }

        $defaultPeriodo = $periodos[0]->periodo ?? date('Y-m');

        return view('whatsapp_recordatorios/index', [
            'periodos' => $periodos,
            'periodosLabels' => $labels,
            'defaultPeriodo' => $defaultPeriodo,
        ]);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $periodo = $this->request->getGet('periodo');

        $sql = "
            SELECT
                f.id, f.mensaje, f.enviado, f.fecha_envio,
                c.serie, c.numero, c.fecha_emision, c.total, c.moneda,
                cl.ruc, cl.razon_social, cl.telefono
            FROM facturas_mensajes_whatsapp f
            INNER JOIN comprobantes_emitidos c ON c.id = f.factura_id
            INNER JOIN clientes cl ON cl.id = f.cliente_id
            WHERE 1=1
        ";
        $params = [];
        if ($periodo) {
            $sql .= " AND c.periodo = ?";
            $params[] = $periodo;
        }
        $sql .= " ORDER BY f.fecha_envio DESC, f.id DESC";

        $rows = $db->query($sql, $params)->getResult();

        $data = [];
        foreach ($rows as $r) {
            $enviadoUp = strtoupper(trim($r->enviado));
            $enviadoBadge = $enviadoUp === 'SI'
                ? '<span class="badge badge-soft-success">' . esc($r->enviado) . '</span>'
                : '<span class="badge badge-soft-secondary">' . esc($r->enviado) . '</span>';

            $data[] = [
                $r->serie . '-' . $r->numero,
                $r->fecha_emision,
                esc($r->razon_social) . '<br><small class="text-muted">' . esc($r->ruc ?? '—') . '</small>',
                esc($r->telefono ?: '—'),
                number_format($r->total, 2) . ' ' . esc($r->moneda),
                '<div style="max-width:280px;white-space:normal;">' . esc($r->mensaje) . '</div>',
                $enviadoBadge,
                date('d/m/Y H:i', strtotime($r->fecha_envio)),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    private function nombrePeriodo(string $periodo): string
    {
        [$anio, $mes] = explode('-', $periodo);
        return (self::MESES[$mes] ?? $mes) . ' ' . $anio;
    }
}
