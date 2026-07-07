<?php

namespace App\Controllers;

class Vencimientos extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['anios'] = $db->query("SELECT DISTINCT anio FROM vencimientos_renta ORDER BY anio DESC")->getResult();
        $data['clientes'] = $db->table('clientes')->where('estado', 'activo')->orderBy('razon_social')->get()->getResult();
        return view('vencimientos/index', $data);
    }

    public function listarCronograma()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?: date('Y');

        $rows = $db->query("
            SELECT * FROM vencimientos_renta
            WHERE anio = ?
            ORDER BY mes, digito_ruc
        ", [$anio])->getResult();

        $data = [];
        foreach ($rows as $r) {
            $acciones = '<button class="btn btn-sm btn-soft-danger eliminar-vencimiento" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'];
            $mesIdx = (int)$r->mes;
            $declLabel = $mesIdx == 12 ? 'Enero del siguiente año' : $meses[$mesIdx + 1];
            $periodoLabel = $meses[$mesIdx] . ' (se declara ' . $declLabel . ')';

            $data[] = [
                $r->anio,
                $periodoLabel,
                $r->digito_ruc,
                date('d/m/Y', strtotime($r->fecha_vencimiento)),
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function guardarCronograma()
    {
        $db = \Config\Database::connect();
        $post = $this->request->getPost();

        $anio = (int)($post['anio'] ?? 0);
        $dias = $post['dias'] ?? [];

        if (!$anio) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Indique el año.',
            ]);
        }

        $db->transBegin();
        $inserted = 0;

        foreach ($dias as $mes => $digitos) {
            $mes = (int)$mes;
            if ($mes < 1 || $mes > 12) continue;

            foreach ($digitos as $digito => $dia) {
                $digito = (int)$digito;
                if ($digito < 0 || $digito > 9) continue;

                $existe = $db->query("
                    SELECT id FROM vencimientos_renta
                    WHERE anio = ? AND mes = ? AND digito_ruc = ?
                ", [$anio, $mes, $digito])->getRow();

                $diaVal = trim((string)$dia);

                if ($diaVal === '') {
                    // Empty = delete existing record
                    if ($existe) {
                        $db->table('vencimientos_renta')->where('id', $existe->id)->delete();
                    }
                    continue;
                }

                $diaInt = (int)$diaVal;
                if ($diaInt < 1 || $diaInt > 31) continue;

                // Declaration month = mes+1 (December → January of next year)
                $declMes = $mes == 12 ? 1 : $mes + 1;
                $declAnio = $mes == 12 ? $anio + 1 : $anio;
                $fecha = sprintf('%04d-%02d-%02d', $declAnio, $declMes, $diaInt);

                if ($existe) {
                    $db->table('vencimientos_renta')->where('id', $existe->id)->update([
                        'fecha_vencimiento' => $fecha,
                    ]);
                } else {
                    $db->table('vencimientos_renta')->insert([
                        'anio' => $anio,
                        'mes' => $mes,
                        'digito_ruc' => $digito,
                        'fecha_vencimiento' => $fecha,
                    ]);
                }
                $inserted++;
            }
        }

        $db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'message' => "Cronograma guardado ($inserted registros).",
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('vencimientos_renta')->where('id', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Registro no encontrado.']);
        }

        $db->table('vencimientos_renta')->where('id', $id)->delete();

        return $this->response->setJSON(['success' => true, 'message' => 'Registro eliminado.']);
    }

    // Consultar vencimientos de un cliente
    public function consultar()
    {
        $db = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');
        $anio = $this->request->getGet('anio') ?: date('Y');

        if (!$clienteId) {
            return $this->response->setJSON(['error' => 'Seleccione un cliente.']);
        }

        $cliente = $db->table('clientes')->where('id', $clienteId)->get()->getRow();
        if (!$cliente) {
            return $this->response->setJSON(['error' => 'Cliente no encontrado.']);
        }

        $ultimoDigito = (int)substr($cliente->ruc, -1);

        $rows = $db->query("
            SELECT * FROM vencimientos_renta
            WHERE anio = ? AND digito_ruc = ?
            ORDER BY mes
        ", [$anio, $ultimoDigito])->getResult();

        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'];
        $data = [];
        foreach ($rows as $r) {
            $mesIdx = (int)$r->mes;
            $declLabel = $mesIdx == 12 ? 'Enero del siguiente año' : $meses[$mesIdx + 1];
            $periodoLabel = $meses[$mesIdx] . ' (se declara ' . $declLabel . ')';
            $data[] = [
                $r->anio,
                $periodoLabel,
                date('d/m/Y', strtotime($r->fecha_vencimiento)),
            ];
        }

        return $this->response->setJSON([
            'cliente' => $cliente->razon_social,
            'ruc' => $cliente->ruc,
            'ultimo_digito' => $ultimoDigito,
            'presenta_balance' => $cliente->presenta_balance ? 'Sí' : 'No',
            'vencimientos' => $data,
        ]);
    }
}
