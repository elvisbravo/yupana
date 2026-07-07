<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<style>
.dt-diff-cell pre { margin: 0; white-space: pre-wrap; word-break: break-all; }
.dt-diff-cell { max-width: 300px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold">Log de Auditoría</h4>
            <p class="text-muted mb-0">Registro de cambios, inserciones y eliminaciones en el sistema</p>
        </div>
        <div class="d-flex gap-2 align-items-end flex-wrap">
            <div>
                <label class="form-label mb-0 fs-xxs">Tabla</label>
                <select class="form-select form-select-sm" id="filtroTabla" style="width:170px;">
                    <option value="">Todas</option>
                    <?php foreach ($tablas as $t): ?>
                    <option value="<?= $t->tabla ?>"><?= $t->tabla ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label mb-0 fs-xxs">Desde</label>
                <input type="date" class="form-control form-control-sm" id="filtroDesde" style="width:150px;">
            </div>
            <div>
                <label class="form-label mb-0 fs-xxs">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="filtroHasta" style="width:150px;">
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
                <table id="tablaLogs" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Tabla</th>
                            <th>Registro</th>
                            <th>Acción</th>
                            <th>Usuario</th>
                            <th>IP</th>
                            <th>Fecha</th>
                            <th>Datos Anteriores</th>
                            <th>Datos Nuevos</th>
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
    <script src="<?= base_url() ?>js/auditoria_logs.js"></script>
<?= $this->endSection() ?>
