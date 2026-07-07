<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Actividades CIIU</h4>
            <p class="text-muted mb-0">Asignación de actividades económicas a clientes</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#actividadModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Asignar Actividad
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaActividades" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Código</th>
                            <th>Actividad</th>
                            <th>Fecha Inicio</th>
                            <th>Principal</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="actividadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="actividadForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Asignar Actividad</h5>
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
                        <label class="form-label">Actividad Económica <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="actividad_id" id="f_actividad">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($actividades as $a): ?>
                            <option value="<?= $a->id ?>"><?= esc($a->codigo) ?> - <?= esc($a->descripcion) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="f_fecha_inicio">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_principal" id="f_principal" value="1">
                        <label class="form-check-label" for="f_principal">Actividad Principal</label>
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
    <script src="<?= base_url() ?>js/actividades.js"></script>
<?= $this->endSection() ?>
