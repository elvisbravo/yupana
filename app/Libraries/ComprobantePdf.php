<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class ComprobantePdf
{
    public function generar(int $comprobanteId): array
    {
        $db = \Config\Database::connect();

        $comprobante = $db->query('SELECT * FROM comprobantes_emitidos WHERE id = ?', [$comprobanteId])->getRow();
        if (!$comprobante) {
            return ['success' => false, 'mensaje' => 'Comprobante no encontrado.'];
        }

        $tipo = $db->table('tipos_comprobante')->where('id', $comprobante->tipo_comprobante_id)->get()->getRow();
        $cliente = $db->table('clientes')->where('id', $comprobante->cliente_id)->get()->getRow();
        $empresa = $db->table('empresa')->where('id', 1)->get()->getRow();

        $sede = null;
        if ($comprobante->sede_id) {
            $sede = $db->table('sedes')->where('id', $comprobante->sede_id)->get()->getRow();
        }

        $items = json_decode($comprobante->detalle ?? '[]', true) ?: [];

        $exonerada = 0.0;
        $gravada = 0.0;
        $tasaIgv = (float) $comprobante->subtotal > 0 ? round(((float) $comprobante->igv / (float) $comprobante->subtotal) * 100, 2) : 0.0;

        foreach ($items as &$item) {
            $monto = (float) ($item['total'] ?? ((float) $item['cantidad'] * (float) $item['precio_unitario']));
            if (($item['tipo_afectacion'] ?? '20') === '10') {
                $gravada += $monto;
                $item['precio_con_igv'] = round((float) $item['precio_unitario'] * (1 + $tasaIgv / 100), 2);
            } else {
                $exonerada += $monto;
                $item['precio_con_igv'] = (float) $item['precio_unitario'];
            }
        }
        unset($item);

        $qrTexto = implode('|', [
            $empresa->ruc ?? '',
            $tipo->codigo ?? '',
            $comprobante->serie,
            $comprobante->numero,
            number_format((float) $comprobante->igv, 2, '.', ''),
            number_format((float) $comprobante->total, 2, '.', ''),
            $comprobante->fecha_emision,
            '6',
            $cliente->ruc ?? '',
        ]);

        $qrDataUri = (new Builder(writer: new PngWriter(), data: $qrTexto, size: 130, margin: 4))->build()->getDataUri();

        $logoDataUri = null;
        if (!empty($empresa->logo_url)) {
            $logoPath = FCPATH . $empresa->logo_url;
            if (is_file($logoPath)) {
                $mime = mime_content_type($logoPath) ?: 'image/png';
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $numeroALetras = new NumeroALetras();

        $cuentas = $db->table('cuentas_bancarias')
            ->where('empresa_id', 1)
            ->where('activo', 1)
            ->where('moneda', $comprobante->moneda)
            ->orderBy('orden')
            ->orderBy('banco')
            ->get()->getResult();

        $html = view('comprobantes/pdf_template', [
            'comprobante' => $comprobante,
            'tipo' => $tipo,
            'cliente' => $cliente,
            'empresa' => $empresa,
            'sede' => $sede,
            'items' => $items,
            'exonerada' => $exonerada,
            'gravada' => $gravada,
            'totalLetras' => $numeroALetras->convertir((float) $comprobante->total, $comprobante->moneda),
            'qrDataUri' => $qrDataUri,
            'logoDataUri' => $logoDataUri,
            'cuentas' => $cuentas,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (!is_dir(FCPATH . 'uploads/comprobantes')) {
            mkdir(FCPATH . 'uploads/comprobantes', 0755, true);
        }

        $archivoBase = preg_replace('/[^A-Za-z0-9\-]/', '', $comprobante->serie . '-' . $comprobante->numero);
        $pdfPath = 'uploads/comprobantes/' . $archivoBase . '.pdf';
        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        $db->table('comprobantes_emitidos')->where('id', $comprobante->id)->update(['pdf_url' => $pdfPath]);

        return ['success' => true, 'pdf_url' => $pdfPath];
    }
}
