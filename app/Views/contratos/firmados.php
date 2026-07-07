<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Contratos Firmados</h4>
            <p class="text-muted mb-0">Gestión de contratos firmados por cliente</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contratoModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Contrato
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaContratos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>N° Contrato</th>
                            <th>Tipo</th>
                            <th>Firma</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Obs.</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contratoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="contratoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-8">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="cliente_id" id="f_cliente">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c->id ?>"><?= esc($c->razon_social) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">N° Contrato <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="numero_contrato" id="f_numero" maxlength="40">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="tipo" id="f_tipo">
                                <option value="servicios">Servicios</option>
                                <option value="confidencialidad">Confidencialidad</option>
                                <option value="honorarios">Honorarios</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Firma <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_firma" id="f_firma">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="f_inicio">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fin</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_fin" id="f_fin">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Monto Total</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="monto_total" id="f_monto">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Moneda</label>
                            <select class="form-select form-select-sm" name="moneda" id="f_moneda">
                                <option value="PEN">PEN</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Estado</label>
                            <select class="form-select form-select-sm" name="estado" id="f_estado">
                                <option value="borrador">Borrador</option>
                                <option value="activo" selected>Activo</option>
                                <option value="vencido">Vencido</option>
                                <option value="renovado">Renovado</option>
                                <option value="rescindido">Rescindido</option>
                            </select>
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
    <script src="<?= base_url() ?>js/contratos_firmados.js"></script>
<?= $this->endSection() ?>
