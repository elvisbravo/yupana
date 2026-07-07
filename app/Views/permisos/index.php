<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Permisos por Módulo</h4>
            <p class="text-muted mb-0">Asigne permisos a los roles por cada módulo del sistema</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Seleccionar Rol <span class="text-danger">*</span></label>
                        <select class="form-select" id="selectRol">
                            <option value="">— Seleccione un rol —</option>
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r->id ?>"><?= esc($r->nombre) ?> (<?= esc($r->codigo) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="permisosContainer" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <button class="btn btn-sm btn-soft-secondary" id="btnExpandAll">Expandir Todo</button>
                            <button class="btn btn-sm btn-soft-secondary" id="btnCollapseAll">Colapsar Todo</button>
                        </div>
                        <button class="btn btn-sm btn-primary" id="btnGuardarPermisos">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Guardar Permisos
                        </button>
                    </div>
                    <div id="permisosAlert"></div>
                    <div id="permisosTree"></div>
                </div>

                <div id="permisosEmpty" class="d-none text-center text-muted py-5">
                    <i data-lucide="shield" style="width:48px;height:48px;" class="mb-2"></i>
                    <p>Seleccione un rol para gestionar sus permisos.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/permisos.js"></script>
<?= $this->endSection() ?>
