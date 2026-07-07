<?php

namespace App\Controllers;

class DatosTributarios extends BaseController
{
    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('clientes')
            ->select('id, ruc, razon_social, regimen_actual_id, tipo_renta, presenta_balance')
            ->where('id', $id)
            ->where('estado', 'activo')
            ->get()->getRow();

        if (!$row) {
            return $this->response->setJSON(['error' => 'Cliente no encontrado.']);
        }

        $historial = $db->query("
            SELECT crh.fecha_inicio, crh.fecha_fin, crh.motivo, r.nombre as regimen_nombre
            FROM cliente_regimen_historial crh
            LEFT JOIN regimenes_tributarios r ON r.id = crh.regimen_id
            WHERE crh.cliente_id = ?
            ORDER BY crh.fecha_inicio DESC
        ", [$id])->getResult();

        return $this->response->setJSON([
            'cliente'   => $row,
            'historial' => $historial,
        ]);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('id');
        $regimenNuevo = $this->request->getPost('regimen_actual_id');
        $tipoRenta = $this->request->getPost('tipo_renta');
        $presentaBalance = $this->request->getPost('presenta_balance') ? 1 : 0;
        $motivo = $this->request->getPost('motivo');
        $fechaInicio = $this->request->getPost('fecha_inicio') ?: date('Y-m-d');

        $cliente = $db->table('clientes')->where('id', $id)->get()->getRow();
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado.']);
        }

        $db->transStart();

        $updateData = [
            'tipo_renta'       => $tipoRenta,
            'presenta_balance' => $presentaBalance,
        ];

        if ($regimenNuevo && $regimenNuevo != $cliente->regimen_actual_id) {
            $updateData['regimen_actual_id'] = $regimenNuevo;

            $db->table('cliente_regimen_historial')
                ->where('cliente_id', $id)
                ->where('fecha_fin', null)
                ->update(['fecha_fin' => date('Y-m-d')]);

            $db->table('cliente_regimen_historial')->insert([
                'cliente_id'  => $id,
                'regimen_id'  => $regimenNuevo,
                'fecha_inicio'=> $fechaInicio,
                'motivo'      => $motivo ?: 'Cambio desde Datos Tributarios',
                'usuario_id'  => session('user_id'),
            ]);
        }

        $db->table('clientes')->where('id', $id)->update($updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar los datos tributarios.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Datos tributarios actualizados correctamente.',
        ]);
    }
}
