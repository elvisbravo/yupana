<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div>
            <h4 class="fw-bold"><?= esc($titulo ?? 'Reporte de Morosidad') ?></h4>
            <p class="text-muted mb-0"><?= esc($subtitulo ?? 'Cobros pendientes, parciales y vencidos') ?></p>
        </div>
    </div>
</div>

<input type="hidden" id="listarUrl" value="<?= $listarUrl ?? '/cobros/morosidad/listar' ?>">

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaMorosidad" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Período</th>
                            <th>Emisión</th>
                            <th>Vencimiento</th>
                            <th>Monto</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Moneda</th>
                            <th>Estado</th>
                            <th>Días Mora</th>
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
    <script src="<?= base_url() ?>js/reporte_morosidad.js"></script>
<?= $this->endSection() ?>
