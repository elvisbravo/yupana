<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Clientes</h4>
            <p class="text-muted mb-0">Listado de clientes registrados</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Cliente
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaClientes" data-tables="basic" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>RUC</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Tarifa Actual</th>
                            <th>Régimen</th>
                            <th>Estado</th>
                            <th style="width: 70px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clienteForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="clienteId">
                    <div id="formAlert"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">RUC</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="ruc" id="f_ruc" maxlength="11" placeholder="20123456789">
                                <button type="button" class="btn btn-outline-primary" id="btnBuscarRuc" title="Buscar RUC">
                                    <i data-lucide="search" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Razón Social</label>
                            <input type="text" class="form-control form-control-sm" name="razon_social" id="f_razon_social">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_comercial" id="f_nombre_comercial">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="f_telefono">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" name="email" id="f_email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Alta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_alta" id="f_fecha_alta">
                        </div>
                    </div>

                    <div id="tarifaSection">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Monto Mensual (S/)</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="monto_mensual" id="f_monto_mensual">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Inicio Tarifa</label>
                            <input type="date" class="form-control form-control-sm" name="tarifa_fecha_inicio" id="f_tarifa_fecha_inicio">
                        </div>
                    </div>
                    </div>

                    <div id="camposTributarios">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Régimen Tributario</label>
                            <select class="form-select form-select-sm" name="regimen_actual_id" id="f_regimen">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($regimenes as $r): ?>
                                <option value="<?= $r->id ?>"><?= esc($r->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo Renta</label>
                            <select class="form-select form-select-sm" name="tipo_renta" id="f_tipo_renta">
                                <option value="general">General</option>
                                <option value="mype">MYPE</option>
                                <option value="especial">Especial</option>
                                <option value="amazonica">Amazonica</option>
                                <option value="agrario">Agrario</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-flex align-items-center gap-2" style="padding-top:8px;">
                                <input type="checkbox" name="presenta_balance" id="f_presenta_balance" value="1">
                                Presenta Balance
                            </label>
                        </div>
                    </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control form-control-sm" name="direccion" id="f_direccion">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ubigeo</label>
                            <select class="form-select form-select-sm" name="ubigeo_id" id="f_ubigeo">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($ubigeos as $u): ?>
                                <option value="<?= $u->id ?>"><?= esc($u->departamento) ?> / <?= esc($u->provincia) ?> / <?= esc($u->distrito) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control form-control-sm" name="referencia" id="f_referencia">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control form-control-sm" name="observaciones" id="f_observaciones" rows="2"></textarea>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-sm btn-primary" id="btnGuardar" form="clienteForm">
                    <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Datos Tributarios -->
<div class="modal fade" id="regimenModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Datos Tributarios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="regimenFormAlert"></div>
                <input type="hidden" name="id" id="rf_id">

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-semibold text-primary" id="rf_cliente_titulo"></h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Régimen Actual</label>
                        <p class="fw-bold mb-0" id="rf_regimen_actual"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Tipo Renta</label>
                        <p class="fw-bold mb-0" id="rf_tipo_renta_actual"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Presenta Balance</label>
                        <p class="fw-bold mb-0" id="rf_presenta_balance_actual"></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Desde</label>
                        <p class="fw-bold mb-0" id="rf_regimen_desde"></p>
                    </div>
                </div>

                <hr>

                <div class="mb-4">
                    <h6 class="fw-semibold mb-2">Historial de Regímenes</h6>
                    <div id="rf_historialContainer" style="max-height:200px;overflow-y:auto;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Régimen</th><th>Desde</th><th>Hasta</th><th>Motivo</th></tr>
                            </thead>
                            <tbody id="rf_historialBody"></tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <div>
                    <h6 class="fw-semibold mb-3">Cambiar Datos Tributarios</h6>
                    <form id="regimenForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nuevo Régimen</label>
                            <select class="form-select form-select-sm" name="regimen_actual_id" id="rf_regimen">
                                <option value="">Sin cambio...</option>
                                <?php foreach ($regimenes as $r): ?>
                                <option value="<?= $r->id ?>"><?= esc($r->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo Renta</label>
                            <select class="form-select form-select-sm" name="tipo_renta" id="rf_tipo_renta">
                                <option value="general">General</option>
                                <option value="mype">MYPE</option>
                                <option value="especial">Especial</option>
                                <option value="amazonica">Amazónica</option>
                                <option value="agrario">Agrario</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-2">
                            <label class="form-label d-flex align-items-center gap-2 mb-0">
                                <input type="checkbox" name="presenta_balance" id="rf_presenta_balance" value="1">
                                Presenta Balance
                            </label>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="rf_fecha_inicio">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Motivo del Cambio</label>
                            <input type="text" class="form-control form-control-sm" name="motivo" id="rf_motivo" placeholder="Opcional">
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarRegimen" form="regimenForm">
                    <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    Guardar Cambios
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
    <script src="<?= base_url() ?>assets/plugins/choices/choices.min.js"></script>
    <script src="<?= base_url() ?>js/clientes.js"></script>
<?= $this->endSection() ?>
