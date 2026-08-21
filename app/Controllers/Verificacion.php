<?php

namespace App\Controllers;

class Verificacion extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data['tipos'] = $db->table('tipos_comprobante')
            ->whereIn('codigo', ['01', '03', '07', '08'])
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get()->getResult();
        $data['empresa'] = $db->table('empresa')->where('id', 1)->get()->getRow();

        return view('publico/verificar', $data);
    }

    public function consultar()
    {
        $db = \Config\Database::connect();

        $ruc = trim((string) $this->request->getPost('ruc'));
        $tipoId = $this->request->getPost('tipo_comprobante_id');
        $serie = trim((string) $this->request->getPost('serie'));
        $numeroInput = trim((string) $this->request->getPost('numero'));
        $monto = $this->request->getPost('monto');
        $fecha = trim((string) $this->request->getPost('fecha_emision'));

        $noEncontrado = [
            'success' => false,
            'message' => 'No se encontró ningún comprobante aceptado con esos datos.',
        ];

        $empresa = $db->table('empresa')->where('id', 1)->get()->getRow();
        if (!$empresa || !$ruc || $empresa->ruc !== $ruc) {
            return $this->response->setJSON($noEncontrado);
        }

        if (!$tipoId || !$serie || !$numeroInput || !$fecha || $monto === null || $monto === '') {
            return $this->response->setJSON($noEncontrado);
        }

        $numeroPadded = str_pad(ltrim($numeroInput, '0') ?: '0', 8, '0', STR_PAD_LEFT);

        $comprobante = $db->query("
            SELECT c.*, tc.nombre as tipo_nombre
            FROM comprobantes_emitidos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            WHERE c.tipo_comprobante_id = ?
              AND c.serie = ?
              AND (c.numero = ? OR c.numero = ?)
              AND c.total = ?
              AND c.fecha_emision = ?
              AND c.estado_sunat = 'aceptado'
            LIMIT 1
        ", [$tipoId, $serie, $numeroInput, $numeroPadded, $monto, $fecha])->getRow();

        if (!$comprobante) {
            return $this->response->setJSON($noEncontrado);
        }

        return $this->response->setJSON([
            'success' => true,
            'tipo' => $comprobante->tipo_nombre,
            'serie_numero' => $comprobante->serie . '-' . $comprobante->numero,
            'fecha_emision' => $comprobante->fecha_emision,
            'total' => $comprobante->total,
            'moneda' => $comprobante->moneda,
            'pdf_url' => $comprobante->pdf_url ? base_url($comprobante->pdf_url) : null,
            'xml_url' => $comprobante->xml_url ? base_url($comprobante->xml_url) : null,
            'cdr_url' => $comprobante->cdr_url ? base_url($comprobante->cdr_url) : null,
        ]);
    }
}
