<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Solicitudes de Contacto</h4>
            <p class="text-muted mb-0">Mensajes recibidos desde la página web</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaSolicitudes" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="solicitudModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nombres</dt>
                    <dd class="col-sm-8" id="detalle_nombres"></dd>
                    <dt class="col-sm-4">Apellidos</dt>
                    <dd class="col-sm-8" id="detalle_apellidos"></dd>
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8" id="detalle_email"></dd>
                    <dt class="col-sm-4">Teléfono</dt>
                    <dd class="col-sm-8" id="detalle_telefono"></dd>
                    <dt class="col-sm-4">Servicio</dt>
                    <dd class="col-sm-8" id="detalle_servicio"></dd>
                    <dt class="col-sm-4">Mensaje</dt>
                    <dd class="col-sm-8" id="detalle_mensaje" style="white-space: pre-wrap;"></dd>
                    <dt class="col-sm-4">Fecha</dt>
                    <dd class="col-sm-8" id="detalle_fecha"></dd>
                </dl>
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
    <script src="<?= base_url() ?>js/solicitudes.js"></script>
<?= $this->endSection() ?>
