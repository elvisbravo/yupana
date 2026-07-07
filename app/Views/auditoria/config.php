<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div>
            <h4 class="fw-bold">Configuración de Auditoría</h4>
            <p class="text-muted mb-0">Resumen de tablas auditadas y estado del registro de cambios</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary rounded-3 fs-3">
                                <i data-lucide="database" style="width:24px;height:24px;"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 fs-xxs">Total de Tablas</p>
                        <h4 class="mb-0 fw-bold"><?= count($tablas) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-success rounded-3 fs-3">
                                <i data-lucide="check-circle" style="width:24px;height:24px;"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 fs-xxs">Tablas con Registros</p>
                        <h4 class="mb-0 fw-bold"><?= $total_audited ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-warning rounded-3 fs-3">
                                <i data-lucide="activity" style="width:24px;height:24px;"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 fs-xxs">Total de Registros</p>
                        <h4 class="mb-0 fw-bold"><?= $total_logs ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tablas del Sistema</h5>
                <span class="badge bg-info">Auditoría vía aplicación</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tabla</th>
                                <th>Filas</th>
                                <th>Motor</th>
                                <th>Auditada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tablas as $t): ?>
                            <tr>
                                <td><code><?= $t->TABLE_NAME ?></code></td>
                                <td><?= number_format($t->TABLE_ROWS) ?></td>
                                <td><?= $t->ENGINE ?></td>
                                <td>
                                    <?php if (isset($auditedMap[$t->TABLE_NAME])): ?>
                                        <span class="badge bg-success"><i data-lucide="check" style="width:12px;height:12px;"></i> Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información</h5>
            </div>
            <div class="card-body">
                <p class="mb-1">El módulo de auditoría registra automáticamente las operaciones <code>INSERT</code>, <code>UPDATE</code> y <code>DELETE</code> realizadas en el sistema a través de los controladores de la aplicación.</p>
                <p class="mb-0">Los datos anteriores y nuevos se almacenan en formato JSON para permitir la trazabilidad completa de los cambios.</p>
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
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
<?= $this->endSection() ?>
