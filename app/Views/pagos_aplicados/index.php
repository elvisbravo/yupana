<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Pagos Aplicados</h4>
            <p class="text-muted mb-0">Registro de pagos aplicados a cobros</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pagoModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Pago
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaPagos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Período</th>
                            <th>Método</th>
                            <th>Fecha Pago</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Operación</th>
                            <th>Banco</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pagoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="pagoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>

                    <div class="mb-2">
                        <label class="form-label">Cobro <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="cobro_id" id="f_cobro">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($cobros as $co): ?>
                            <option value="<?= $co->id ?>" data-saldo="<?= $co->saldo ?>">
                                <?= esc($co->razon_social) ?> - <?= $co->periodo ?> (S/ <?= number_format($co->saldo, 2) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="metodo_pago_id" id="f_metodo">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($metodos as $m): ?>
                                <option value="<?= $m->id ?>"><?= esc($m->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_pago" id="f_fecha">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Moneda</label>
                            <select class="form-select form-select-sm" name="moneda" id="f_moneda">
                                <option value="PEN">PEN</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Monto <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="monto" id="f_monto">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">N° Operación</label>
                            <input type="text" class="form-control form-control-sm" name="numero_operacion" id="f_operacion" maxlength="80">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Banco</label>
                            <input type="text" class="form-control form-control-sm" name="banco" id="f_banco" maxlength="80">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control form-control-sm" name="observaciones" id="f_observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>js/pagos_aplicados.js"></script>
<?= $this->endSection() ?>
