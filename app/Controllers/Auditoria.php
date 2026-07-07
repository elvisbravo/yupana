<?php

namespace App\Controllers;

class Auditoria extends BaseController
{
    public function index()
    {
        return redirect()->to('/auditoria/logs');
    }

    public function logs()
    {
        $db = \Config\Database::connect();
        $data['tablas'] = $db->query("SELECT DISTINCT tabla FROM log_auditoria ORDER BY tabla")->getResult();
        return view('auditoria/logs', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $tabla = $this->request->getGet('tabla');
        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');

        $sql = "
            SELECT l.*, u.nombres, u.apellidos
            FROM log_auditoria l
            LEFT JOIN usuarios u ON u.id = l.usuario_id
            WHERE 1=1
        ";
        $params = [];
        if ($tabla) { $sql .= " AND l.tabla = ?"; $params[] = $tabla; }
        if ($desde) { $sql .= " AND DATE(l.created_at) >= ?"; $params[] = $desde; }
        if ($hasta) { $sql .= " AND DATE(l.created_at) <= ?"; $params[] = $hasta; }
        $sql .= " ORDER BY l.created_at DESC LIMIT 1000";

        $rows = $db->query($sql, $params)->getResult();

        $badges = [
            'insert' => 'badge-soft-success',
            'update' => 'badge-soft-warning',
            'delete' => 'badge-soft-danger',
        ];

        $data = [];
        foreach ($rows as $r) {
            $accionBadge = '<span class="badge ' . ($badges[$r->accion] ?? 'badge-soft-secondary') . '">'
                . ucfirst($r->accion) . '</span>';
            $usuario = $r->nombres ? esc($r->nombres . ' ' . $r->apellidos) : '—';

            $anteriores = $r->datos_anteriores ? '<pre class="mb-0" style="max-height:120px;overflow:auto;font-size:11px;">'
                . esc(json_encode(json_decode($r->datos_anteriores), JSON_PRETTY_PRINT)) . '</pre>' : '—';

            $nuevos = $r->datos_nuevos ? '<pre class="mb-0" style="max-height:120px;overflow:auto;font-size:11px;">'
                . esc(json_encode(json_decode($r->datos_nuevos), JSON_PRETTY_PRINT)) . '</pre>' : '—';

            $data[] = [
                esc($r->tabla),
                $r->registro_id,
                $accionBadge,
                $usuario,
                $r->ip ?? '—',
                $r->created_at,
                $anteriores,
                $nuevos,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function config()
    {
        $db = \Config\Database::connect();

        $tables = $db->query("
            SELECT TABLE_NAME, TABLE_ROWS, ENGINE, TABLE_COMMENT
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = 'yupana_db'
              AND TABLE_TYPE = 'BASE TABLE'
              AND TABLE_NAME NOT IN ('log_auditoria')
            ORDER BY TABLE_NAME
        ")->getResult();

        $audited = $db->query("SELECT DISTINCT tabla FROM log_auditoria")->getResult();
        $auditedMap = [];
        foreach ($audited as $a) $auditedMap[$a->tabla] = true;

        $data['tablas'] = $tables;
        $data['auditedMap'] = $auditedMap;
        $data['total_logs'] = $db->query("SELECT COUNT(*) as c FROM log_auditoria")->getRow()->c;
        $data['total_audited'] = count($auditedMap);

        return view('auditoria/config', $data);
    }
}
