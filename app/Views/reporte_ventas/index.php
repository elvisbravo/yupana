<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="<?= base_url() ?>assets/plugins/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold">Reporte de Ventas</h4>
        <p class="text-muted mb-0">Todas las ventas generadas (comprobantes emitidos)</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaReporteVentas" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Comprobante</th>
                            <th>Tipo</th>
                            <th>Emisión</th>
                            <th>Cliente</th>
                            <th>RUC</th>
                            <th>Dirección</th>
                            <th>Ubigeo</th>
                            <th>Subtotal</th>
                            <th>IGV</th>
                            <th>Total</th>
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
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.buttons.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/buttons.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/jszip.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/buttons.html5.min.js"></script>
    <script src="<?= base_url() ?>js/reporte_ventas.js"></script>
<?= $this->endSection() ?>
