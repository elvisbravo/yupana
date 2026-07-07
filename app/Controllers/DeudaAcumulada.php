<?php

namespace App\Controllers;

class DeudaAcumulada extends BaseController
{
    public function index()
    {
        return view('deuda_acumulada/index');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT
                cl.id,
                cl.razon_social,
                tm.monto as tarifa,
                tm.moneda,
                GREATEST(tm.fecha_inicio, cl.fecha_alta) as inicio_referencia,
                PERIOD_DIFF(
                    DATE_FORMAT(CURDATE(), '%Y%m'),
                    DATE_FORMAT(GREATEST(tm.fecha_inicio, cl.fecha_alta), '%Y%m')
                ) as meses_transcurridos,
                PERIOD_DIFF(
                    DATE_FORMAT(CURDATE(), '%Y%m'),
                    DATE_FORMAT(GREATEST(tm.fecha_inicio, cl.fecha_alta), '%Y%m')
                ) * tm.monto as total_esperado,
                COALESCE(cob.sum_monto, 0) as total_cobrado,
                COALESCE(pag.sum_pagado, 0) as total_pagado,
                COALESCE(cob.cantidad, 0) as meses_con_cobro
            FROM clientes cl
            JOIN tarifas_mensuales tm ON tm.cliente_id = cl.id AND tm.fecha_fin IS NULL AND tm.activo = 1
            LEFT JOIN (
                SELECT cliente_id,
                       SUM(monto) as sum_monto,
                       SUM(monto_pagado) as sum_pagado,
                       COUNT(*) as cantidad
                FROM cobros_mensuales
                WHERE activo = 1
                GROUP BY cliente_id
            ) cob ON cob.cliente_id = cl.id
            LEFT JOIN (
                SELECT cm.cliente_id, SUM(p.monto) as sum_pagado
                FROM pagos p
                JOIN cobros_mensuales cm ON cm.id = p.cobro_id AND cm.activo = 1
                GROUP BY cm.cliente_id
            ) pag ON pag.cliente_id = cl.id
            WHERE cl.estado = 'activo'
            ORDER BY cl.razon_social
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $saldo = $r->total_esperado - $r->total_pagado;
            $inicio = $r->inicio_referencia;
            $sin_cobro = max(0, $r->meses_transcurridos - $r->meses_con_cobro);

            $alerta = '';
            if ($saldo > 0) {
                $alerta = '<span class="badge bg-danger">Deudor</span>';
            } elseif ($r->meses_con_cobro < $r->meses_transcurridos) {
                $alerta = '<span class="badge bg-warning text-dark">Incompleto</span>';
            } else {
                $alerta = '<span class="badge bg-success">Al día</span>';
            }

            $data[] = [
                esc($r->razon_social),
                number_format($r->tarifa, 2),
                $r->moneda,
                $inicio,
                $r->meses_transcurridos,
                number_format($r->total_esperado, 2),
                number_format($r->total_cobrado, 2),
                number_format($r->total_pagado, 2),
                number_format($saldo, 2),
                $r->meses_con_cobro . ' / ' . $sin_cobro . ' sin',
                $alerta,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
