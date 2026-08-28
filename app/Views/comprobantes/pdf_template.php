<?php
$monedaSimbolo = $comprobante->moneda === 'USD' ? '$' : 'S/';
$nombreTipo = mb_strtoupper($tipo->nombre ?? 'COMPROBANTE', 'UTF-8');
$esCron = $comprobante->origen === 'cron';
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; }
    table { border-collapse: collapse; width: 100%; }
    .box { border: 1px solid #d5dbe1; border-radius: 3px; padding: 8px 10px; }
    .section { margin-bottom: 12px; }

    .header-table td { vertical-align: middle; }
    .empresa-nombre { font-size: 14px; font-weight: bold; color: #2c3e50; }
    .empresa-detalle { color: #555; margin-top: 2px; }

    .doc-box { text-align: center; border: 1.5px solid #2c3e50; border-radius: 4px; padding: 10px; }
    .doc-box .ruc { font-size: 10.5px; font-weight: bold; color: #2c3e50; }
    .doc-box .tipo { font-size: 13px; font-weight: bold; color: #2c3e50; margin: 5px 0; letter-spacing: 0.5px; }
    .doc-box .numero { font-size: 12px; font-weight: bold; background: #2c3e50; color: #fff; padding: 3px 0; border-radius: 3px; }

    .info-header { background: #eef1f4; color: #2c3e50; font-weight: bold; padding: 4px 8px; border: 1px solid #d5dbe1; border-bottom: none; border-radius: 3px 3px 0 0; }
    .info-body { border: 1px solid #d5dbe1; border-top: none; border-radius: 0 0 3px 3px; padding: 8px 10px; }
    .info-table td { vertical-align: top; padding: 1px 0; }
    .info-label { font-weight: bold; width: 85px; color: #444; }

    .items-table th { background: #2c3e50; color: #fff; padding: 6px 8px; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; }
    .items-table td { padding: 5px 8px; font-size: 8.5px; border-bottom: 1px solid #e7eaee; }
    .items-table tr.par td { background: #f7f9fa; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .totales-table { width: 45%; margin-left: 55%; }
    .totales-table td { padding: 3px 8px; }
    .totales-table .label { text-align: right; color: #555; }
    .totales-table .valor { text-align: right; width: 90px; }
    .totales-table .total-row td { border-top: 1.5px solid #2c3e50; font-size: 11px; font-weight: bold; color: #2c3e50; padding-top: 6px; }

    .footer-table td { vertical-align: middle; }
    .rep-impresa { color: #555; line-height: 1.4; }

    .cuentas-table { margin-top: 6px; }
    .cuentas-table th { background: #2c3e50; color: #fff; padding: 5px 8px; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; }
    .cuentas-table td { padding: 4px 8px; font-size: 8.5px; color: #444; border-bottom: 1px solid #e7eaee; }
    .cuentas-table tr.par td { background: #f7f9fa; }

    .leyenda { text-align: center; color: #666; font-style: italic; margin-top: 14px; }

    .small { font-size: 8px; }
</style>
</head>
<body>

<table class="section header-table">
    <tr>
        <td style="width:62%;">
            <?php if ($logoDataUri): ?>
            <img src="<?= $logoDataUri ?>" style="max-height:50px;max-width:180px;"><br>
            <?php endif; ?>
            
            <div class="empresa-detalle"><?= esc($empresa->razon_social) ?></div>
            <div class="empresa-detalle"><?= esc($sede->direccion ?? $empresa->direccion_fiscal ?? '') ?></div>
        </td>
        <td style="width:38%;">
            <div class="doc-box">
                <div class="ruc">RUC <?= esc($empresa->ruc ?? '') ?></div>
                <div class="tipo"><?= esc($nombreTipo) ?></div>
                <div class="numero"><?= esc($comprobante->serie . '-' . $comprobante->numero) ?></div>
            </div>
        </td>
    </tr>
</table>

<table class="section header-table">
    <tr>
        <td style="width:63%;padding-right:10px;">
            <div class="info-header">CLIENTE</div>
            <div class="info-body">
                <table class="info-table">
                    <tr><td class="info-label">RUC</td><td>: <?= esc($cliente->ruc ?? '—') ?></td></tr>
                    <tr><td class="info-label">Denominación</td><td>: <?= esc($cliente->razon_social ?? '—') ?></td></tr>
                    <tr><td class="info-label">Dirección</td><td>: <?= esc($cliente->direccion ?? '—') ?></td></tr>
                </table>
            </div>
        </td>
        <td style="width:37%;">
            <div class="info-header">COMPROBANTE</div>
            <div class="info-body">
                <table class="info-table">
                    <tr><td class="info-label">Emisión</td><td>: <?= esc(date('d/m/Y', strtotime($comprobante->fecha_emision))) ?></td></tr>
                    <tr><td class="info-label">Vencimiento</td><td>: <?= esc(date('d/m/Y', strtotime($comprobante->fecha_vencimiento ?: $comprobante->fecha_emision))) ?></td></tr>
                    <tr><td class="info-label">Moneda</td><td>: <?= $comprobante->moneda === 'USD' ? 'Dólares' : 'Soles' ?></td></tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<table class="section items-table">
    <thead>
        <tr>
            <th style="width:8%;">Cant.</th>
            <th>Descripción</th>
            <th style="width:16%;" class="text-right">P.U.</th>
            <th style="width:16%;" class="text-right">Importe</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $i => $item): ?>
        <tr class="<?= $i % 2 === 1 ? 'par' : '' ?>">
            <td class="text-center"><?= (int) $item['cantidad'] ?></td>
            <td><?= esc($item['descripcion']) ?></td>
            <td class="text-right"><?= number_format((float) ($item['precio_con_igv'] ?? $item['precio_unitario']), 2) ?></td>
            <td class="text-right"><?= number_format((float) ($item['total'] ?? ($item['cantidad'] * $item['precio_unitario'])), 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="section">
    <tr>
        <td>
            <table class="totales-table">
                <tr><td class="label">Exonerada</td><td class="valor"><?= $monedaSimbolo ?> <?= number_format($exonerada, 2) ?></td></tr>
                <tr><td class="label">Gravada</td><td class="valor"><?= $monedaSimbolo ?> <?= number_format($gravada, 2) ?></td></tr>
                <tr><td class="label">IGV</td><td class="valor"><?= $monedaSimbolo ?> <?= number_format((float) $comprobante->igv, 2) ?></td></tr>
                <tr class="total-row"><td class="label">TOTAL</td><td class="valor"><?= $monedaSimbolo ?> <?= number_format((float) $comprobante->total, 2) ?></td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="section">
    <strong>IMPORTE EN LETRAS:</strong> <?= esc($totalLetras) ?>
</div>

<?php if (!$esCron): ?>
<div class="section">
    <strong>Forma de pago:</strong> <?= esc($comprobante->forma_pago) ?> — <?= $monedaSimbolo ?> <?= number_format((float) $comprobante->total, 2) ?>
</div>
<?php endif; ?>

<?php if (!empty($cuentas)): ?>
<div class="section">
    <strong>Cuentas para depósito / transferencia (<?= esc($comprobante->moneda) ?>)</strong>
    <table class="cuentas-table">
        <thead>
            <tr>
                <th>Banco</th>
                <th>Tipo</th>
                <th>N° de Cuenta</th>
                <th>CCI</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cuentas as $i => $c): ?>
            <tr class="<?= $i % 2 === 1 ? 'par' : '' ?>">
                <td><?= esc($c->banco) ?></td>
                <td><?= ucfirst($c->tipo_cuenta) ?></td>
                <td><?= esc($c->numero_cuenta) ?></td>
                <td><?= esc($c->numero_cci ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<table class="section footer-table">
    <tr>
        <td style="width:78%;" class="small rep-impresa">
            Representación impresa de la <?= esc($nombreTipo) ?>.<br>
            Para verificar este comprobante, visite <?= esc(base_url('verificar')) ?> e ingrese RUC, tipo, serie, número y monto.
        </td>
        <td style="width:22%;" class="text-center">
            <img src="<?= $qrDataUri ?>" style="width:95px;height:95px;">
        </td>
    </tr>
</table>

<?php if (!empty($empresa->leyenda_pdf)): ?>
<div class="leyenda small">
    <?= nl2br(esc($empresa->leyenda_pdf)) ?>
</div>
<?php endif; ?>

</body>
</html>
