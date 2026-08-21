<?php

namespace App\Controllers;

use App\Models\SolicitudContactoModel;

class Home extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $totalClientes = $db->table('clientes')->where('estado', 'activo')->countAllResults();

        $contratosActivos = $db->table('contratos')->where('estado', 'activo')->countAllResults();

        $cobrosPendientes = $db->table('cobros_mensuales')
            ->select('SUM(monto - monto_pagado) AS total')
            ->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
            ->where('activo', 1)
            ->get()->getRow()->total ?? 0;

        $recaudadoMes = $db->table('cobros_mensuales')
            ->select('SUM(monto_pagado) AS total')
            ->where('estado', 'pagado')
            ->where('MONTH(fecha_emision)', date('m'))
            ->where('YEAR(fecha_emision)', date('Y'))
            ->get()->getRow()->total ?? 0;

        $ultimosClientes = $db->table('clientes')
            ->select('clientes.*, regimenes_tributarios.nombre AS regimen_nombre')
            ->join('regimenes_tributarios', 'regimenes_tributarios.id = clientes.regimen_actual_id', 'left')
            ->where('clientes.estado', 'activo')
            ->orderBy('clientes.created_at', 'DESC')
            ->limit(6)
            ->get()->getResult();

        $clientesPorRegimen = $db->table('clientes')
            ->select('regimenes_tributarios.nombre, COUNT(*) AS total')
            ->join('regimenes_tributarios', 'regimenes_tributarios.id = clientes.regimen_actual_id', 'left')
            ->where('clientes.estado', 'activo')
            ->groupBy('clientes.regimen_actual_id')
            ->get()->getResult();

        $contratosPorVencer = $db->table('contratos')
            ->select('COUNT(*) AS total')
            ->where('estado', 'activo')
            ->where('fecha_fin >=', date('Y-m-d'))
            ->where('fecha_fin <=', date('Y-m-d', strtotime('+7 days')))
            ->get()->getRow()->total ?? 0;

        $cobrosVencidos = $db->table('cobros_mensuales')
            ->select('SUM(monto - monto_pagado) AS total')
            ->where('estado', 'vencido')
            ->where('activo', 1)
            ->get()->getRow()->total ?? 0;

        $pendientesEmision = $db->table('comprobantes_emitidos')->where('estado_sunat', 'pendiente')->countAllResults();

        $tareasPendientes = $db->table('tareas')
            ->whereIn('estado', ['pendiente', 'en_progreso'])
            ->countAllResults();

        return view('home/home', [
            'totalClientes'     => $totalClientes,
            'contratosActivos'  => $contratosActivos,
            'cobrosPendientes'  => number_format($cobrosPendientes, 2),
            'recaudadoMes'      => number_format($recaudadoMes, 2),
            'ultimosClientes'   => $ultimosClientes,
            'clientesPorRegimen' => $clientesPorRegimen,
            'totalRegimen'      => array_sum(array_column($clientesPorRegimen, 'total')),
            'contratosPorVencer' => $contratosPorVencer,
            'cobrosVencidos'    => number_format($cobrosVencidos, 2),
            'pendientesEmision' => $pendientesEmision,
            'tareasPendientes'  => $tareasPendientes,
        ]);
    }

    public function webPage(): string
    {
        return view('web/webpage');
    }

    public function guardarContacto()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no válida.']);
        }

        $model = new SolicitudContactoModel();

        if (!$model->save($this->request->getPost())) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar.',
                'errors'  => $model->errors(),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mensaje enviado con éxito. Te contactaremos pronto.',
        ]);
    }
}
