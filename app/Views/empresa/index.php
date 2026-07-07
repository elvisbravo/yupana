<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold">Configuración de la Empresa</h4>
        <p class="text-muted mb-0">Datos generales y sedes</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Datos Generales</h5>
            </div>
            <div class="card-body">
                <div id="empresaAlert"></div>
                <form id="empresaForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">RUC</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="ruc" id="f_empresa_ruc" value="<?= esc($empresa->ruc ?? '') ?>" maxlength="11">
                                <button type="button" class="btn btn-outline-primary" id="btnBuscarRucEmpresa" title="Buscar RUC">
                                    <i data-lucide="search" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Razón Social</label>
                            <input type="text" class="form-control form-control-sm" name="razon_social" id="f_empresa_razon_social" value="<?= esc($empresa->razon_social ?? '') ?>">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_comercial" id="f_empresa_nombre_comercial" value="<?= esc($empresa->nombre_comercial ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dirección Fiscal</label>
                            <input type="text" class="form-control form-control-sm" name="direccion_fiscal" id="f_empresa_direccion" value="<?= esc($empresa->direccion_fiscal ?? '') ?>">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" value="<?= esc($empresa->telefono ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control form-control-sm" name="correo" value="<?= esc($empresa->correo ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control form-control-sm" name="logo" accept="image/*">
                            <?php if (!empty($empresa->logo_url)): ?>
                            <div class="mt-2">
                                <img src="<?= base_url($empresa->logo_url) ?>" alt="Logo" style="max-height:60px;">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarEmpresa">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Sedes</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sedeModal">
                    <i data-lucide="plus" class="me-1" style="width:14px;height:14px;"></i> Nueva Sede
                </button>
            </div>
            <div class="card-body">
                <table id="tablaSedes" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Anexo</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Tipo Envío</th>
                            <th style="width:80px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Correlativos -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Correlativos por Sede</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#correlativoModal">
                    <i data-lucide="plus" class="me-1" style="width:14px;height:14px;"></i> Nuevo Correlativo
                </button>
            </div>
            <div class="card-body">
                <table id="tablaCorrelativos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Sede</th>
                            <th>Comprobante</th>
                            <th>Serie</th>
                            <th>Inicio</th>
                            <th>Actual</th>
                            <th>Envío</th>
                            <th style="width:80px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Correlativo -->
<div class="modal fade" id="correlativoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Correlativo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="correlativoForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="correlativoId">
                    <div id="correlativoAlert"></div>
                    <div class="mb-3">
                        <label class="form-label">Sede <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="sede_id" id="f_corr_sede" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s->id ?>"><?= esc($s->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo Comprobante <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="tipo_comprobante_id" id="f_corr_comprobante" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($comprobantes as $c): ?>
                            <option value="<?= $c->id ?>"><?= esc($c->nombre) ?> (<?= esc($c->abreviatura) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Serie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="serie" id="f_corr_serie" placeholder="Ej: F001" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Correlativo Inicio</label>
                            <input type="number" class="form-control form-control-sm" name="correlativo_inicio" id="f_corr_inicio" value="1" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correlativo Actual</label>
                            <input type="number" class="form-control form-control-sm" name="correlativo_actual" id="f_corr_actual" value="1" min="1">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tipo Envío</label>
                        <select class="form-select form-select-sm" name="tipo_envio" id="f_corr_envio">
                            <option value="produccion">Producción</option>
                            <option value="prueba">Prueba</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarCorrelativo" form="correlativoForm">
                    <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sede -->
<div class="modal fade" id="sedeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sedeForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="sedeId">
                    <div id="sedeAlert"></div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Sede <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="nombre" id="f_sede_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control form-control-sm" name="direccion" id="f_sede_direccion">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Anexo</label>
                            <input type="text" class="form-control form-control-sm" name="anexo" id="f_sede_anexo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="f_sede_telefono">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control form-control-sm" name="correo" id="f_sede_correo">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tipo Envío</label>
                        <select class="form-select form-select-sm" name="tipo_envio" id="f_sede_envio">
                            <option value="prueba">Prueba</option>
                            <option value="produccion">Producción</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarSede" form="sedeForm">
                    <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    Guardar
                </button>
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
    <script src="<?= base_url() ?>js/empresa.js"></script>
<?= $this->endSection() ?>
