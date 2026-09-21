<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div>
            <h4 class="fw-bold">Generar Nota de Crédito</h4>
            <p class="text-muted mb-0">Ref. comprobante <?= esc($comprobante->serie . '-' . $comprobante->numero) ?> (<?= esc($comprobante->tipo_nombre) ?>)</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="formAlert"></div>

                <form id="notaCreditoForm">
                    <input type="hidden" name="comprobante_referencia_id" value="<?= $comprobante->id ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control form-control-sm" value="<?= esc($comprobante->razon_social ?? '—') ?> (<?= esc($comprobante->ruc ?? '—') ?>)" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Comprobante Afectado</label>
                            <input type="text" class="form-control form-control-sm" value="<?= esc($comprobante->tipo_nombre . ' ' . $comprobante->serie . '-' . $comprobante->numero) ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Emisión Original</label>
                            <input type="text" class="form-control form-control-sm" value="<?= esc($comprobante->fecha_emision) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle / Items (igual al comprobante original)</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50%;">Descripción</th>
                                        <th style="width:15%;">Cant.</th>
                                        <th style="width:15%;">Precio Unit.</th>
                                        <th style="width:20%;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= esc($item['descripcion'] ?? '') ?></td>
                                        <td><?= esc($item['cantidad'] ?? '') ?></td>
                                        <td><?= number_format((float) ($item['precio_unitario'] ?? 0), 2) ?></td>
                                        <td><?= number_format((float) ($item['total'] ?? 0), 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 ms-auto">
                            <table class="table table-sm table-borderless text-end">
                                <tr>
                                    <td style="width:50%;">Subtotal:</td>
                                    <td class="fw-bold"><?= esc($comprobante->moneda) ?> <?= number_format($comprobante->subtotal, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>IGV:</td>
                                    <td class="fw-bold"><?= esc($comprobante->moneda) ?> <?= number_format($comprobante->igv, 2) ?></td>
                                </tr>
                                <tr class="fs-5">
                                    <td><strong>TOTAL:</strong></td>
                                    <td><strong><?= esc($comprobante->moneda) ?> <?= number_format($comprobante->total, 2) ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Motivo <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="motivo_nota_credito_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($motivos as $m): ?>
                                <option value="<?= $m->id ?>"><?= esc($m->codigo) ?> - <?= esc($m->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            <i data-lucide="save" style="width:16px;height:16px;"></i> Generar Nota de Crédito
                        </button>
                        <a href="<?= base_url('comprobantes/ventas') ?>" class="btn btn-soft-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/comprobantes_nota_credito.js"></script>
<?= $this->endSection() ?>
