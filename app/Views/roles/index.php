<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Roles</h4>
            <p class="text-muted mb-0">Gestión de roles del sistema</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rolModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Rol
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaRoles" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Nivel</th>
                            <th>Activo</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rolModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="rolForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="rolId">
                    <div id="formAlert"></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="codigo" id="f_codigo" maxlength="20" placeholder="ej. ADMIN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nivel</label>
                            <input type="number" class="form-control form-control-sm" name="nivel" id="f_nivel" min="1" max="5">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="nombre" id="f_nombre" placeholder="ej. Administrador">
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
    <script src="<?= base_url() ?>js/roles.js"></script>
<?= $this->endSection() ?>
