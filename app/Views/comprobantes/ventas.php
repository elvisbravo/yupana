<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold">Servicios / Ventas</h4>
            <p class="text-muted mb-0">Listado unificado de todos los comprobantes emitidos</p>
        </div>
        <div class="d-flex gap-2 align-items-end flex-wrap">
            <div>
                <label class="form-label mb-0 fs-xxs">Tipo</label>
                <select class="form-select form-select-sm" id="filtroTipo" style="width:160px;">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t->id ?>"><?= esc($t->abreviatura) ?> - <?= esc($t->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label mb-0 fs-xxs">Período</label>
                <select class="form-select form-select-sm" id="filtroPeriodo" style="width:120px;">
                    <option value="">Todos</option>
                    <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p->periodo ?>"><?= $p->periodo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label mb-0 fs-xxs">Año</label>
                <select class="form-select form-select-sm" id="filtroAnio" style="width:100px;">
                    <option value="">Todos</option>
                    <?php foreach ($anios as $a): ?>
                    <option value="<?= $a->anio ?>"><?= $a->anio ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-sm btn-soft-primary" id="btnFiltrar">
                <i data-lucide="filter" style="width:14px;height:14px;"></i> Filtrar
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaVentas" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Comprobante</th>
                            <th>Tipo</th>
                            <th>Emisión</th>
                            <th>Período</th>
                            <th>Cliente</th>
                            <th>RUC</th>
                            <th>Subtotal</th>
                            <th>IGV</th>
                            <th>Total</th>
                            <th>Moneda</th>
                            <th>Sunat</th>
                            <th>Pago</th>
                            <th>Observaciones</th>
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
    <script src="<?= base_url() ?>js/comprobantes_ventas.js"></script>
<?= $this->endSection() ?>
