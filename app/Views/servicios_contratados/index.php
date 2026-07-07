<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Servicios Contratados</h4>
            <p class="text-muted mb-0">Gestión de servicios contratados por cliente</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#servicioModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Servicio
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaServicios" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo Servicio</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Descripción</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="servicioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="servicioForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Servicio</h5>
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
                        <label class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="tipo_servicio_id" id="f_tipo">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t->id ?>"><?= esc($t->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="f_fecha_inicio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_fin" id="f_fecha_fin">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control form-control-sm" name="descripcion" id="f_descripcion" rows="2"></textarea>
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
    <script src="<?= base_url() ?>js/servicios_contratados.js"></script>
<?= $this->endSection() ?>
