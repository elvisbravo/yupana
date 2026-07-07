<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Cobros Mensuales</h4>
            <p class="text-muted mb-0">Gestión de cobros mensuales por cliente</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cobroModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Cobro
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaCobros" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Período</th>
                            <th>Emisión</th>
                            <th>Vencimiento</th>
                            <th>Monto</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Moneda</th>
                            <th>Estado</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cobroModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="cobroForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Cobro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>

                    <div class="mb-2">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="cliente_id" id="f_cliente">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c->id ?>"><?= esc($c->razon_social) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Servicio Contratado</label>
                        <select class="form-select form-select-sm" name="servicio_contratado_id" id="f_servicio">
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Período <span class="text-danger">*</span></label>
                            <input type="month" class="form-control form-control-sm" name="periodo" id="f_periodo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Emisión <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_emision" id="f_emision">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_vencimiento" id="f_vencimiento">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Monto <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="monto" id="f_monto">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Moneda</label>
                            <select class="form-select form-select-sm" name="moneda" id="f_moneda">
                                <option value="PEN">PEN</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select form-select-sm" name="estado" id="f_estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Concepto</label>
                        <input type="text" class="form-control form-control-sm" name="concepto" id="f_concepto" maxlength="255">
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
    <script src="<?= base_url() ?>js/cobros_mensuales.js"></script>
<?= $this->endSection() ?>
