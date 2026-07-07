<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold"><?= esc($titulo) ?></h4>
            <p class="text-muted mb-0">Gestión de tareas y pendientes</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tareaModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nueva Tarea
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaTareas" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Asignado</th>
                            <th>Creador</th>
                            <th>Vencimiento</th>
                            <th>Descripción</th>
                            <th style="width: 120px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tareaModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="tareaForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>

                    <input type="hidden" id="filtroTareas" value="<?= $filtro ?>">

                    <div class="mb-2">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="titulo" id="f_titulo" maxlength="180">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select class="form-select form-select-sm" name="cliente_id" id="f_cliente">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c->id ?>"><?= esc($c->razon_social) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="prioridad" id="f_prioridad">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asignar a</label>
                            <select class="form-select form-select-sm" name="asignado_a_id" id="f_asignado">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u->id ?>"><?= esc($u->nombres . ' ' . $u->apellidos) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Vencimiento</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_vencimiento" id="f_vencimiento">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control form-control-sm" name="descripcion" id="f_descripcion" rows="3"></textarea>
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
    <script src="<?= base_url() ?>js/tareas.js"></script>
<?= $this->endSection() ?>
