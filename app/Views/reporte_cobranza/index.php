<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Cobranza Mensual</h4>
            <p class="text-muted mb-0">Resumen de cobros por cliente y período</p>
        </div>
        <div style="width: 200px;">
            <select class="form-select form-select-sm" id="filtroPeriodo">
                <option value="">Todos los períodos</option>
                <?php foreach ($periodos as $p): ?>
                <option value="<?= $p->periodo ?>"><?= $p->periodo ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaCobranza" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Período</th>
                            <th>Cobros</th>
                            <th>Monto Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Cobrado</th>
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
    <script src="<?= base_url() ?>js/reporte_cobranza.js"></script>
<?= $this->endSection() ?>
