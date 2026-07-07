<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Listado de Mensajes</h4>
            <p class="text-muted mb-0">Historial de mensajes enviados</p>
        </div>
        <a href="<?= base_url() ?>mensajes/crear" class="btn btn-primary">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Mensaje
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaMensajes" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Título</th>
                            <th>Fecha Creación</th>
                            <th>Creado por</th>
                            <th>Total Envíos</th>
                            <th>Estado</th>
                            <th style="width: 110px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="mensajeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="mensajeForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Editar Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>
                    <input type="hidden" name="id" id="f_id">

                    <div class="mb-2">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="titulo" id="f_titulo" maxlength="255">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contenido</label>
                        <textarea class="form-control form-control-sm" name="contenido" id="f_contenido" rows="4"></textarea>
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

<!-- Modal Ver Envíos -->
<div class="modal fade" id="enviosModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Envíos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="enviosContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    <script src="<?= base_url() ?>js/mensajes_listado.js"></script>
<?= $this->endSection() ?>
