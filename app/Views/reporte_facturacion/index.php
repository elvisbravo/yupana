<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Facturación Anual</h4>
            <p class="text-muted mb-0">Resumen de comprobantes emitidos por cliente y año</p>
        </div>
        <div style="width: 200px;">
            <select class="form-select form-select-sm" id="filtroAnio">
                <option value="">Todos los años</option>
                <?php foreach ($anios as $a): ?>
                <option value="<?= $a->anio ?>"><?= $a->anio ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaFacturacion" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Año</th>
                            <th>Comprobantes</th>
                            <th>Monto Total</th>
                            <th>Anulado</th>
                            <th>Neto</th>
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
    <script src="<?= base_url() ?>js/reporte_facturacion.js"></script>
<?= $this->endSection() ?>
