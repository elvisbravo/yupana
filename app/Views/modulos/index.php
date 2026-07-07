<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Módulos del Sistema</h4>
            <p class="text-muted mb-0">Gestión de módulos del sistema</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#moduloModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Módulo
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaModulos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Icono</th>
                            <th>Ruta</th>
                            <th>Orden</th>
                            <th>Módulo Padre</th>
                            <th>Activo</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Módulo -->
<div class="modal fade" id="moduloModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="moduloForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="moduloId">
                    <div id="formAlert"></div>

                    <div class="mb-2">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="codigo" id="f_codigo" placeholder="ej. roles.modulos">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="nombre" id="f_nombre" placeholder="ej. Módulos del Sistema">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Icono (Lucide)</label>
                            <input type="text" class="form-control form-control-sm" name="icono" id="f_icono" placeholder="ej. grid">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Orden</label>
                            <input type="number" class="form-control form-control-sm" name="orden" id="f_orden" min="0">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Ruta</label>
                        <input type="text" class="form-control form-control-sm" name="ruta" id="f_ruta" placeholder="ej. /roles/modulos">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Módulo Padre</label>
                        <select class="form-select form-select-sm" name="padre_id" id="f_padre">
                            <option value="">Ninguno (es padre)</option>
                            <?php foreach ($padres as $p): ?>
                            <?php if ($p->codigo !== 'roles.modulos'): ?>
                            <option value="<?= $p->id ?>"><?= esc($p->nombre) ?> (<?= esc($p->codigo) ?>)</option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control form-control-sm" name="descripcion" id="f_descripcion" rows="2"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="f_activo" value="1" checked>
                        <label class="form-check-label" for="f_activo">Activo</label>
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
    <script src="<?= base_url() ?>js/modulos.js"></script>
<?= $this->endSection() ?>
