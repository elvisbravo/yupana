<?php

namespace App\Controllers;

class Comprobantes extends BaseController
{
    public function index()
    {
        return redirect()->to('/comprobantes/ventas');
    }

    public function ventas()
    {
        $db = \Config\Database::connect();
        $data['tipos'] = $db->query("SELECT id, nombre, abreviatura FROM tipos_comprobante WHERE activo = 1 ORDER BY id")->getResult();
        $data['anios'] = $db->query("SELECT DISTINCT YEAR(fecha_emision) as anio FROM comprobantes_emitidos ORDER BY anio DESC")->getResult();
        $data['periodos'] = $db->query("SELECT DISTINCT periodo FROM comprobantes_emitidos ORDER BY periodo DESC")->getResult();
        return view('comprobantes/ventas', $data);
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $tipo = $this->request->getGet('tipo');
        $periodo = $this->request->getGet('periodo');
        $anio = $this->request->getGet('anio');
        $origen = $this->request->getGet('origen');
        $tipoEnvio = $this->request->getGet('tipo_envio');

        $sql = "
            SELECT c.*, tc.nombre as tipo_nombre, tc.abreviatura, tc.codigo as tipo_codigo, cl.razon_social, cl.ruc
            FROM comprobantes_emitidos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            WHERE 1=1
        ";
        $params = [];
        if ($tipo) { $sql .= " AND c.tipo_comprobante_id = ?"; $params[] = $tipo; }
        if ($periodo) { $sql .= " AND c.periodo = ?"; $params[] = $periodo; }
        if ($anio) { $sql .= " AND YEAR(c.fecha_emision) = ?"; $params[] = $anio; }
        if ($origen) { $sql .= " AND c.origen = ?"; $params[] = $origen; }
        if ($tipoEnvio) { $sql .= " AND c.tipo_envio_sunat = ?"; $params[] = $tipoEnvio; }
        $sql .= " ORDER BY c.fecha_emision DESC, c.id DESC LIMIT 1000";

        $rows = $db->query($sql, $params)->getResult();

        $badges = [
            'aceptado'  => 'badge-soft-success',
            'pendiente' => 'badge-soft-warning',
            'rechazado' => 'badge-soft-danger',
            'anulado'   => 'badge-soft-secondary',
            'enviado'   => 'badge-soft-info',
            'baja'      => 'badge-soft-dark',
        ];
        $pagoBadges = [
            'pagado'    => 'badge-soft-success',
            'parcial'   => 'badge-soft-warning',
            'no_pagado' => 'badge-soft-danger',
        ];

        $tiposSoportados = ['01', '03', '07', '08'];

        $data = [];
        foreach ($rows as $r) {
            $sunatBadge = '<span class="badge ' . ($badges[$r->estado_sunat] ?? 'badge-soft-secondary') . '">' . ucfirst($r->estado_sunat) . '</span>';
            $pagoBadge = '<span class="badge ' . ($pagoBadges[$r->estado_pago] ?? 'badge-soft-secondary') . '">' . ucfirst(str_replace('_', ' ', $r->estado_pago)) . '</span>';

            $archivos = '';
            if (!empty($r->pdf_url)) {
                $archivos .= '<a class="btn btn-sm btn-soft-danger" href="' . base_url($r->pdf_url) . '" target="_blank" title="Ver PDF">PDF</a> ';
            }
            if (!empty($r->xml_url)) {
                $archivos .= '<a class="btn btn-sm btn-soft-secondary" href="' . base_url($r->xml_url) . '" download title="Descargar XML">XML</a> ';
            }
            if (!empty($r->cdr_url)) {
                $archivos .= '<a class="btn btn-sm btn-soft-secondary" href="' . base_url($r->cdr_url) . '" download title="Descargar CDR">CDR</a>';
            }

            $acciones = '<div class="dropdown">'
                . '<button class="btn btn-sm btn-soft-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-lucide="more-horizontal" style="width:14px;height:14px;"></i></button>'
                . '<ul class="dropdown-menu dropdown-menu-end">';
            if (in_array($r->estado_sunat, ['pendiente', 'rechazado'], true) && in_array($r->tipo_codigo, $tiposSoportados, true)) {
                $titulo = $r->sunat_mensaje ? esc($r->sunat_mensaje, 'attr') : 'Enviar a SUNAT';
                $acciones .= '<li><a class="dropdown-item enviar-sunat" href="#" data-id="' . $r->id . '" title="' . $titulo . '">'
                    . '<i data-lucide="send" class="me-2" style="width:14px;height:14px;"></i>Enviar a SUNAT</a></li>';
            }
            if (in_array($r->tipo_codigo, ['01', '03'], true) && $r->estado_sunat === 'aceptado') {
                $acciones .= '<li><a class="dropdown-item" href="' . base_url('comprobantes/nota-credito/' . $r->id) . '">'
                    . '<i data-lucide="file-plus" class="me-2" style="width:14px;height:14px;"></i>Generar nota de crédito</a></li>';
            } else {
                $acciones .= '<li><a class="dropdown-item disabled" href="#" aria-disabled="true" tabindex="-1" title="Solo disponible para facturas/boletas aceptadas">'
                    . '<i data-lucide="file-plus" class="me-2" style="width:14px;height:14px;"></i>Generar nota de crédito</a></li>';
            }
            $acciones .= '<li><a class="dropdown-item disabled" href="#" aria-disabled="true" tabindex="-1" title="Próximamente">'
                . '<i data-lucide="ban" class="me-2" style="width:14px;height:14px;"></i>Anular comprobante</a></li>';
            $acciones .= '</ul></div>';

            $data[] = [
                $r->serie . '-' . $r->numero,
                esc($r->tipo_nombre),
                $r->fecha_emision,
                $r->periodo,
                esc($r->razon_social ?? '—'),
                $r->ruc ?? '—',
                number_format($r->subtotal, 2),
                number_format($r->igv, 2),
                number_format($r->total, 2),
                $r->moneda,
                $sunatBadge,
                $pagoBadge,
                esc($r->observaciones ?? '—'),
                $archivos ?: '—',
                $acciones,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function crear()
    {
        $db = \Config\Database::connect();
        $data['tipos'] = $db->query("SELECT id, nombre, abreviatura FROM tipos_comprobante WHERE activo = 1 ORDER BY id")->getResult();
        $data['clientes'] = $db->query("SELECT id, ruc, razon_social FROM clientes WHERE estado = 'activo' ORDER BY razon_social")->getResult();
        $data['monedas'] = ['PEN', 'USD'];
        $data['sedes'] = $db->table('sedes')->where('empresa_id', 1)->where('activo', 1)->orderBy('nombre')->get()->getResult();
        return view('comprobantes/crear', $data);
    }

    public function obtenerCliente()
    {
        $id = $this->request->getGet('id');
        if (!$id) return $this->response->setJSON(['error' => 'ID requerido']);

        $db = \Config\Database::connect();
        $cliente = $db->query("
            SELECT c.*, rt.nombre as regimen_nombre
            FROM clientes c
            LEFT JOIN regimenes_tributarios rt ON rt.id = c.regimen_actual_id
            WHERE c.id = ?
        ", [$id])->getRow();
        if (!$cliente) return $this->response->setJSON(['error' => 'Cliente no encontrado']);

        $servicios = $db->query("
            SELECT sc.id, ts.nombre as servicio, ts.id as tipo_servicio_id,
                   ts.codigo_sunat, ts.unidad_medida, ts.tipo_afectacion_igv,
                   COALESCE(tm.monto, 0) as monto,
                   COALESCE(tm.moneda, 'PEN') as moneda
            FROM servicios_contratados sc
            JOIN tipos_servicio ts ON ts.id = sc.tipo_servicio_id
            LEFT JOIN tarifas_mensuales tm ON tm.cliente_id = sc.cliente_id
                AND tm.servicio_contratado_id = sc.id
                AND tm.fecha_fin IS NULL AND tm.activo = 1
            WHERE sc.cliente_id = ? AND sc.activo = 1
        ", [$id])->getResult();

        return $this->response->setJSON([
            'cliente' => $cliente,
            'servicios' => $servicios,
        ]);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $rules = [
            'tipo_comprobante_id' => 'required|integer',
            'cliente_id' => 'required|integer',
            'serie' => 'required|max_length[4]',
            'numero' => 'required|max_length[20]',
            'fecha_emision' => 'required|valid_date',
            'subtotal' => 'required|numeric',
            'igv' => 'required|numeric',
            'total' => 'required|numeric',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $sedeId = $data['sede_id'] ?? null;
        $sede = $sedeId ? $db->table('sedes')->where('id', $sedeId)->get()->getRow() : null;

        $insert = [
            'tipo_comprobante_id' => $data['tipo_comprobante_id'],
            'cliente_id' => $data['cliente_id'],
            'sede_id' => $sedeId,
            'serie' => $data['serie'],
            'numero' => $data['numero'],
            'fecha_emision' => $data['fecha_emision'],
            'fecha_vencimiento' => $data['fecha_emision'],
            'forma_pago' => 'CONTADO',
            'periodo' => date('Y-m', strtotime($data['fecha_emision'])),
            'moneda' => $data['moneda'] ?? 'PEN',
            'subtotal' => $data['subtotal'],
            'igv' => $data['igv'],
            'total' => $data['total'],
            'estado_sunat' => 'pendiente',
            'estado_pago' => $data['estado_pago'] ?? 'no_pagado',
            'observaciones' => $data['observaciones'] ?? null,
            'detalle' => $data['detalle'] ?? null,
            'usuario_id' => session('usuario_id'),
            'tipo_envio_sunat' => $sede->tipo_envio ?? 'prueba',
        ];

        if ($insert['detalle']) {
            $items = json_decode($insert['detalle'], true);
            $insert['detalle'] = json_encode($items);
        }

        $db->table('comprobantes_emitidos')->insert($insert);
        $comprobanteId = $db->insertID();

        $envio = (new \App\Libraries\SunatClient())->enviar($comprobanteId);
        (new \App\Libraries\ComprobantePdf())->generar($comprobanteId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Comprobante creado correctamente.',
            'sunat' => $envio,
        ]);
    }

    public function enviarSunat($id)
    {
        $resultado = (new \App\Libraries\SunatClient())->enviar((int) $id);

        return $this->response->setJSON($resultado);
    }

    public function regenerarPdf($id)
    {
        try {
            $resultado = (new \App\Libraries\ComprobantePdf())->generar((int) $id);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'No se pudo generar el PDF: ' . $e->getMessage(),
            ]);
        }

        return $this->response->setJSON($resultado);
    }

    public function guardarClienteRapido()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        if (empty($data['ruc']) || empty($data['razon_social'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'RUC y Razón Social son requeridos.']);
        }

        $existe = $db->table('clientes')->where('ruc', $data['ruc'])->get()->getRow();
        if ($existe) {
            return $this->response->setJSON([
                'success' => true,
                'cliente' => ['id' => $existe->id, 'razon_social' => $existe->razon_social, 'ruc' => $existe->ruc],
            ]);
        }

        $insert = [
            'ruc' => $data['ruc'],
            'razon_social' => $data['razon_social'],
            'email' => $data['email'] ?? null,
            'fecha_alta' => date('Y-m-d'),
            'estado' => 'activo',
            'usuario_registro_id' => session('usuario_id'),
        ];

        $db->table('clientes')->insert($insert);
        $id = $db->insertID();

        return $this->response->setJSON([
            'success' => true,
            'cliente' => [
                'id' => $id,
                'razon_social' => $data['razon_social'],
                'ruc' => $data['ruc'],
            ],
        ]);
    }

    public function tiposPorSede()
    {
        $sedeId = $this->request->getGet('sede_id');
        if (!$sedeId) return $this->response->setJSON([]);

        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT DISTINCT tc.id, tc.nombre, tc.abreviatura
            FROM correlativos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            WHERE c.sede_id = ? AND c.activo = 1
            ORDER BY tc.id
        ", [$sedeId])->getResult();

        return $this->response->setJSON($rows);
    }

    public function obtenerCorrelativo()
    {
        $sedeId = $this->request->getGet('sede_id');
        $tipoId = $this->request->getGet('tipo_comprobante_id');

        if (!$sedeId || !$tipoId) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $rows = $db->table('correlativos')
            ->where('sede_id', $sedeId)
            ->where('tipo_comprobante_id', $tipoId)
            ->where('activo', 1)
            ->orderBy('serie')
            ->get()->getResult();

        return $this->response->setJSON($rows);
    }

    public function notaCredito($id)
    {
        $db = \Config\Database::connect();
        $comprobante = $db->query("
            SELECT c.*, tc.nombre as tipo_nombre, tc.codigo as tipo_codigo, cl.razon_social, cl.ruc
            FROM comprobantes_emitidos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            LEFT JOIN clientes cl ON cl.id = c.cliente_id
            WHERE c.id = ?
        ", [$id])->getRow();

        if (!$comprobante || !in_array($comprobante->tipo_codigo, ['01', '03'], true) || $comprobante->estado_sunat !== 'aceptado') {
            return redirect()->to('/comprobantes/ventas');
        }

        $data['comprobante'] = $comprobante;
        $data['items'] = json_decode($comprobante->detalle ?? '[]', true) ?: [];
        $data['motivos'] = $db->table('motivos_nota_credito')->where('activo', 1)->orderBy('codigo')->get()->getResult();

        return view('comprobantes/nota_credito', $data);
    }

    public function guardarNotaCredito()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $rules = [
            'comprobante_referencia_id' => 'required|integer',
            'motivo_nota_credito_id' => 'required|integer',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $original = $db->query("
            SELECT c.*, tc.codigo as tipo_codigo
            FROM comprobantes_emitidos c
            JOIN tipos_comprobante tc ON tc.id = c.tipo_comprobante_id
            WHERE c.id = ?
        ", [$data['comprobante_referencia_id']])->getRow();

        if (!$original || !in_array($original->tipo_codigo, ['01', '03'], true) || $original->estado_sunat !== 'aceptado') {
            return $this->response->setJSON(['success' => false, 'message' => 'El comprobante original no es válido para generar una nota de crédito.']);
        }

        $motivo = $db->table('motivos_nota_credito')->where('id', $data['motivo_nota_credito_id'])->where('activo', 1)->get()->getRow();
        if (!$motivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Motivo no válido.']);
        }

        $tipoNC = $db->table('tipos_comprobante')->where('codigo', '07')->get()->getRow();
        if (!$tipoNC) {
            return $this->response->setJSON(['success' => false, 'message' => 'No existe el tipo de comprobante "Nota de Crédito" en el catálogo.']);
        }

        $sede = $original->sede_id ? $db->table('sedes')->where('id', $original->sede_id)->get()->getRow() : null;

        $db->transStart();

        $correlativo = $db->query("
            SELECT * FROM correlativos
            WHERE sede_id = ? AND tipo_comprobante_id = ? AND activo = 1
            ORDER BY serie ASC LIMIT 1 FOR UPDATE
        ", [$original->sede_id, $tipoNC->id])->getRow();

        if (!$correlativo) {
            $db->transComplete();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay un correlativo activo configurado para Nota de Crédito en la sede del comprobante original.',
            ]);
        }

        $numero = (int) $correlativo->correlativo_actual;
        $db->query('UPDATE correlativos SET correlativo_actual = correlativo_actual + 1 WHERE id = ?', [$correlativo->id]);

        $fechaEmision = (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d');

        $db->table('comprobantes_emitidos')->insert([
            'cliente_id' => $original->cliente_id,
            'comprobante_referencia_id' => $original->id,
            'motivo_nota_credito_id' => $motivo->id,
            'sede_id' => $original->sede_id,
            'tipo_comprobante_id' => $tipoNC->id,
            'serie' => $correlativo->serie,
            'numero' => str_pad((string) $numero, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaEmision,
            'forma_pago' => 'CONTADO',
            'periodo' => date('Y-m', strtotime($fechaEmision)),
            'moneda' => $original->moneda,
            'subtotal' => $original->subtotal,
            'igv' => $original->igv,
            'total' => $original->total,
            'detalle' => $original->detalle,
            'estado_sunat' => 'pendiente',
            'estado_pago' => 'no_pagado',
            'origen' => 'manual',
            'usuario_id' => session('usuario_id'),
            'tipo_envio_sunat' => $sede->tipo_envio ?? 'prueba',
        ]);

        $comprobanteId = $db->insertID();
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo generar la nota de crédito (posible número duplicado); revisar correlativos.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Nota de crédito generada correctamente.',
            'id' => $comprobanteId,
        ]);
    }
}
