<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <style>
        #tarifaModal .choices__list--single .choices__item {
            background-color: transparent;
            border: none;
            padding-left: 0;
            color: var(--ins-body-color);
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Tarifas Mensuales</h4>
            <p class="text-muted mb-0">Gestión de tarifas mensuales por cliente</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tarifaModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nueva Tarifa
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaTarifas" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Periodo Inicio</th>
                            <th>Periodo Fin</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tarifaModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="tarifaForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Tarifa Mensual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>

                    <div class="mb-2">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="cliente_id" id="f_cliente" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c->id ?>"><?= esc($c->razon_social) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Monto <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="monto" id="f_monto" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Moneda <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="moneda" id="f_moneda" required>
                                <option value="PEN">PEN</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Periodo de Inicio <span class="text-danger">*</span></label>
                            <input type="month" class="form-control form-control-sm" name="fecha_inicio" id="f_fecha_inicio" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Periodo de Fin</label>
                            <input type="month" class="form-control form-control-sm" name="fecha_fin" id="f_fecha_fin">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Motivo Cambio</label>
                            <input type="text" class="form-control form-control-sm" name="motivo_cambio" id="f_motivo" maxlength="255">
                        </div>
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

<div class="modal fade" id="tarifasClienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tarifas de <span id="tc_cliente_nombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Periodo Inicio</th>
                            <th>Periodo Fin</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tc_tabla_body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/choices/choices.min.js"></script>
    <script src="<?= base_url() ?>js/tarifas_mensuales.js"></script>
<?= $this->endSection() ?>
