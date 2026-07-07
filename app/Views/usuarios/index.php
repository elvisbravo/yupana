<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Usuarios</h4>
            <p class="text-muted mb-0">Gestión de usuarios del sistema</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Usuario
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaUsuarios" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>DNI</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último Acceso</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="usuarioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="usuarioForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="usuarioId">
                    <div id="formAlert"></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombres" id="f_nombres">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="apellidos" id="f_apellidos">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-sm" name="email" id="f_email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">DNI</label>
                            <input type="text" class="form-control form-control-sm" name="dni" id="f_dni" maxlength="8">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="rol_id" id="f_rol">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->id ?>"><?= esc($r->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="f_telefono">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Contraseña <span class="text-danger" id="pwdRequired">*</span></label>
                        <input type="password" class="form-control form-control-sm" name="password" id="f_password" autocomplete="new-password">
                        <small class="text-muted" id="pwdHelp">En edición, dejar vacío para mantener la actual.</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select form-select-sm" name="estado" id="f_estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
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
    <script src="<?= base_url() ?>js/usuarios.js"></script>
<?= $this->endSection() ?>
