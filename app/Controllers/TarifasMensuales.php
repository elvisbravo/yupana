<?php

namespace App\Controllers;

class TarifasMensuales extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('tarifas_mensuales/index', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT t.*, cl.razon_social,
                tf.monto AS futura_monto, tf.fecha_inicio AS futura_fecha_inicio, tf.motivo_cambio AS futura_motivo
            FROM tarifas_mensuales t
            JOIN clientes cl ON cl.id = t.cliente_id
            LEFT JOIN tarifas_mensuales tf ON tf.cliente_id = t.cliente_id AND tf.activo = 1
                AND tf.fecha_inicio = (
                    SELECT MIN(t3.fecha_inicio)
                    FROM tarifas_mensuales t3
                    WHERE t3.cliente_id = t.cliente_id AND t3.activo = 1
                        AND t3.fecha_inicio > CURDATE()
                )
            WHERE t.activo = 1
                AND t.fecha_inicio = (
                    SELECT MAX(t2.fecha_inicio)
                    FROM tarifas_mensuales t2
                    WHERE t2.cliente_id = t.cliente_id AND t2.activo = 1
                        AND t2.fecha_inicio <= CURDATE()
                        AND (t2.fecha_fin IS NULL OR t2.fecha_fin >= CURDATE())
                )
            ORDER BY cl.razon_social
        ")->getResult();

        $hoy = date('Y-m-d');

        $data = [];
        foreach ($rows as $r) {
            $periodoInicio = $this->periodoTexto($r->fecha_inicio);
            $periodoFin = $this->periodoTexto($r->fecha_fin);
            $motivoHtml = $r->motivo_cambio ? esc($r->motivo_cambio) : '';
            $montoHtml = number_format($r->monto, 2);
            [, $estadoHtml] = $this->estadoTarifa($r, $hoy);

            if ($r->futura_fecha_inicio) {
                $periodoFutura = $this->periodoTexto($r->futura_fecha_inicio);
                $periodoInicio .= '<br><span class="badge badge-soft-warning fs-xxs mt-1">Próx: ' . $periodoFutura . '</span>';
                $montoHtml .= '<br><span class="badge badge-soft-warning fs-xxs mt-1">' . number_format($r->futura_monto, 2) . '</span>';
                if ($r->futura_motivo) {
                    $motivoHtml .= '<br><span class="badge badge-soft-warning fs-xxs mt-1">' . esc($r->futura_motivo) . '</span>';
                }
            }

            $acciones = '<button class="btn btn-sm btn-soft-info ver-tarifas-cliente" data-cliente-id="' . $r->cliente_id . '" title="Ver / editar tarifas">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->razon_social),
                $montoHtml,
                $r->moneda,
                $periodoInicio,
                $periodoFin,
                $motivoHtml,
                $estadoHtml,
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function porCliente($clienteId)
    {
        $db = \Config\Database::connect();
        $cliente = $db->table('clientes')->where('id', $clienteId)->get()->getRow();
        if (!$cliente) {
            return $this->response->setJSON(['error' => 'Cliente no encontrado.']);
        }

        $rows = $db->table('tarifas_mensuales')
            ->where('cliente_id', $clienteId)
            ->where('activo', 1)
            ->orderBy('fecha_inicio', 'DESC')
            ->get()->getResult();

        $hoy = date('Y-m-d');
        $tarifas = [];
        foreach ($rows as $r) {
            [$estado, $estadoHtml] = $this->estadoTarifa($r, $hoy);
            $tarifas[] = [
                'id' => $r->id,
                'monto' => number_format($r->monto, 2),
                'moneda' => $r->moneda,
                'periodo_inicio' => $this->periodoTexto($r->fecha_inicio),
                'periodo_fin' => $this->periodoTexto($r->fecha_fin),
                'motivo' => $r->motivo_cambio ? esc($r->motivo_cambio) : '',
                'estado' => $estado,
                'estado_html' => $estadoHtml,
                'editable' => $this->esEditable($r, $hoy),
            ];
        }

        return $this->response->setJSON([
            'cliente' => esc($cliente->razon_social),
            'tarifas' => $tarifas,
        ]);
    }

    private function estadoTarifa($row, string $hoy): array
    {
        if ($row->fecha_inicio > $hoy) {
            return ['proxima', '<span class="badge badge-soft-warning">Próxima</span>'];
        }
        if ($row->fecha_fin && $row->fecha_fin < $hoy) {
            return ['vencida', '<span class="badge badge-soft-secondary">Vencida</span>'];
        }
        return ['vigente', '<span class="badge badge-soft-success">Vigente</span>'];
    }

    private function periodoTexto(?string $fecha): string
    {
        if (!$fecha) {
            return '—';
        }
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return $meses[(int) date('n', strtotime($fecha))] . ' ' . date('Y', strtotime($fecha));
    }

    private function esEditable($row, string $hoy): bool
    {
        [$estado] = $this->estadoTarifa($row, $hoy);
        if ($estado === 'proxima') {
            return true;
        }
        if ($estado === 'vencida') {
            return false;
        }
        return !$this->corteFacturacionPasado($row);
    }

    private function corteFacturacionPasado($row): bool
    {
        $db = \Config\Database::connect();
        $cfg = $db->table('configuracion_facturacion_automatica')->where('id', 1)->get()->getRow();
        if (!$cfg || !$cfg->activo) {
            return false;
        }

        $dias = array_map('intval', explode(',', $cfg->dias_generacion));
        sort($dias);
        $dia = $dias[0] ?? 1;

        $inicio = new \DateTime($row->fecha_inicio);
        $diasEnMes = (int) $inicio->format('t');
        $dia = min($dia, $diasEnMes);

        $corte = $inicio->format('Y-m-') . sprintf('%02d', $dia) . ' ' . $cfg->hora_generacion;
        $ahora = new \DateTime('now', new \DateTimeZone('America/Lima'));
        return $ahora->format('Y-m-d H:i:s') >= $corte;
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['error' => 'Tarifa no encontrada.']);
        }
        return $this->response->setJSON($row);
    }

    public function guardar()
    {
        if (!$this->validate($this->tarifaRules())) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $db = \Config\Database::connect();
        $data = $this->request->getPost();
        $data['fecha_inicio'] = $this->mesToFecha($data['fecha_inicio']);
        $data['fecha_fin'] = $this->mesToFecha($data['fecha_fin'] ?? null);
        $data['motivo_cambio'] = $data['motivo_cambio'] ?? null;
        $data['usuario_id'] = session('user_id');

        $abierta = $db->table('tarifas_mensuales')
            ->where('cliente_id', $data['cliente_id'])
            ->where('activo', 1)
            ->where('fecha_fin', null)
            ->get()->getRow();

        if ($abierta && $abierta->fecha_inicio >= $data['fecha_inicio']) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['fecha_inicio' => 'Ya existe una tarifa vigente que inicia en ' . $abierta->fecha_inicio . ' o después. Edítala en vez de crear una nueva.'],
            ]);
        }

        if ($abierta) {
            $fechaCierre = date('Y-m-d', strtotime($data['fecha_inicio'] . ' -1 day'));
            $db->table('tarifas_mensuales')->where('id', $abierta->id)->update(['fecha_fin' => $fechaCierre]);
        }

        $db->table('tarifas_mensuales')->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa mensual guardada correctamente.',
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tarifa no encontrada.']);
        }

        if (!$this->esEditable($row, date('Y-m-d'))) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede modificar una tarifa vigente o vencida.']);
        }

        if (!$this->validate($this->tarifaRules())) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $data = $this->request->getPost();
        $data['fecha_inicio'] = $this->mesToFecha($data['fecha_inicio']);
        $data['fecha_fin'] = $this->mesToFecha($data['fecha_fin'] ?? null);
        $data['motivo_cambio'] = $data['motivo_cambio'] ?? null;

        $db->table('tarifas_mensuales')->where('id', $id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa mensual actualizada correctamente.',
        ]);
    }

    private function tarifaRules(): array
    {
        return [
            'cliente_id'   => 'required|integer',
            'monto'        => 'required|numeric',
            'moneda'       => 'required|in_list[PEN,USD]',
            'fecha_inicio' => 'required|regex_match[/^\d{4}-\d{2}$/]',
            'fecha_fin'    => 'permit_empty|regex_match[/^\d{4}-\d{2}$/]',
        ];
    }

    private function mesToFecha(?string $mes): ?string
    {
        if (!$mes) {
            return null;
        }
        return $mes . '-01';
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('tarifas_mensuales')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tarifa no encontrada.']);
        }

        if (!$this->esEditable($row, date('Y-m-d'))) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede modificar una tarifa vigente o vencida.']);
        }

        $db->table('tarifas_mensuales')->where('id', $id)->update(['activo' => 0]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tarifa mensual eliminada correctamente.',
        ]);
    }
}
