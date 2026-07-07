<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Archivos del Cliente</h4>
            <p class="text-muted mb-0">Gestión de documentos asociados a clientes</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#documentoModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Documento
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaDocumentos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Fecha Doc.</th>
                            <th>Vencimiento</th>
                            <th>Tamaño</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="documentoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="documentoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Documento</h5>
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

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="tipo_documento_id" id="f_tipo">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t->id ?>"><?= esc($t->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_documento" id="f_nombre" maxlength="255">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <input type="text" class="form-control form-control-sm" name="descripcion" id="f_descripcion" maxlength="255">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Fecha Documento</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_documento" id="f_fecha_doc">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Vencimiento</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_vencimiento" id="f_vencimiento">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">URL del Archivo</label>
                        <input type="text" class="form-control form-control-sm" name="archivo_url" id="f_url" maxlength="500" placeholder="https://...">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Archivo</label>
                            <input type="text" class="form-control form-control-sm" name="archivo_nombre" id="f_archivo_nombre" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tamaño (bytes)</label>
                            <input type="number" min="0" class="form-control form-control-sm" name="archivo_size" id="f_size">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hash SHA-256</label>
                            <input type="text" class="form-control form-control-sm" name="archivo_hash" id="f_hash" maxlength="64">
                        </div>
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
    <script src="<?= base_url() ?>js/documentos_archivos.js"></script>
<?= $this->endSection() ?>
