<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div>
            <h4 class="fw-bold">Deuda Acumulada</h4>
            <p class="text-muted mb-0">Cálculo automático de deuda basado en tarifas mensuales activas y tiempo transcurrido</p>
        </div>
    </div>
</div>

<div class="alert alert-info py-2 mb-3" role="alert">
    <i data-lucide="info" style="width:16px;height:16px;" class="me-1"></i>
    El cálculo usa la tarifa mensual activa × meses desde la fecha de referencia (inicio del cliente o tarifa).
    No requiere crear cobros por período.
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaDeuda" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Tarifa Mensual</th>
                            <th>Moneda</th>
                            <th>Desde</th>
                            <th>Meses</th>
                            <th>Esperado</th>
                            <th>Cobrado</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Cobros</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                </table>
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
    <script src="<?= base_url() ?>js/deuda_acumulada.js"></script>
<?= $this->endSection() ?>
