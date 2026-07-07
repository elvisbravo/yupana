<?php

namespace App\Controllers;

class ReporteFacturacion extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['anios'] = $db->query("SELECT DISTINCT YEAR(fecha_emision) as anio FROM comprobantes_emitidos ORDER BY anio DESC")->getResult();
        return view('reporte_facturacion/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio');

        $sql = "
            SELECT cl.id as cliente_id, cl.razon_social as cliente_nombre,
                   YEAR(co.fecha_emision) as anio,
                   COUNT(*) as total_comprobantes,
                   SUM(co.total) as monto_total,
                   SUM(CASE WHEN co.estado_sunat = 'anulado' THEN co.total ELSE 0 END) as monto_anulado
            FROM clientes cl
            JOIN comprobantes_emitidos co ON co.cliente_id = cl.id
            WHERE cl.estado = 'activo'
        ";
        $params = [];
        if ($anio) {
            $sql .= " AND YEAR(co.fecha_emision) = ?";
            $params[] = $anio;
        }
        $sql .= " GROUP BY cl.id, cl.razon_social, YEAR(co.fecha_emision)
                  ORDER BY cl.razon_social, anio DESC";

        $rows = $db->query($sql, $params)->getResult();

        $data = [];
        foreach ($rows as $r) {
            $neto = $r->monto_total - $r->monto_anulado;
            $data[] = [
                esc($r->cliente_nombre),
                $r->anio,
                $r->total_comprobantes,
                number_format($r->monto_total, 2),
                number_format($r->monto_anulado, 2),
                number_format($neto, 2),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
