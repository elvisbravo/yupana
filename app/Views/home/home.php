<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4 mt-4">
    <div class="col-12">
        <h4 class="fw-bold">Dashboard</h4>
        <p class="text-muted mb-0">Resumen general del sistema</p>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar-xl bg-primary-subtle rounded d-flex align-items-center justify-content-center flex-shrink-0">
                    <i data-lucide="users" class="fs-24 text-primary"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $totalClientes ?></h3>
                    <p class="text-muted mb-0">Clientes Registrados</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar-xl bg-success-subtle rounded d-flex align-items-center justify-content-center flex-shrink-0">
                    <i data-lucide="file-text" class="fs-24 text-success"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $contratosActivos ?></h3>
                    <p class="text-muted mb-0">Contratos Activos</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar-xl bg-warning-subtle rounded d-flex align-items-center justify-content-center flex-shrink-0">
                    <i data-lucide="clock" class="fs-24 text-warning"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">S/ <?= $cobrosPendientes ?></h3>
                    <p class="text-muted mb-0">Cobros Pendientes</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar-xl bg-info-subtle rounded d-flex align-items-center justify-content-center flex-shrink-0">
                    <i data-lucide="wallet" class="fs-24 text-info"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">S/ <?= $recaudadoMes ?></h3>
                    <p class="text-muted mb-0">Recaudado este Mes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Últimos Clientes Registrados</h5>
                <a href="<?= base_url('clientes') ?>" class="btn btn-sm btn-soft-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>RUC</th>
                                <th>Régimen</th>
                                <th>Estado</th>
                                <th>Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimosClientes)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No hay clientes registrados</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($ultimosClientes as $c): ?>
                            <tr>
                                <td><a href="<?= base_url('clientes') ?>" class="text-body fw-semibold"><?= esc($c->razon_social) ?></a></td>
                                <td><?= esc($c->ruc) ?></td>
                                <td><?= esc($c->regimen_nombre ?? '—') ?></td>
                                <td>
                                    <?php if ($c->estado === 'activo'): ?>
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                    <?php elseif ($c->estado === 'inactivo'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning"><?= esc(ucfirst($c->estado)) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($c->created_at)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Alertas del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="badge bg-<?= $contratosPorVencer > 0 ? 'danger' : 'secondary' ?> rounded-pill mt-1"><?= $contratosPorVencer ?></span>
                    <div>
                        <p class="mb-0 fw-semibold">Contratos por Vencer</p>
                        <p class="text-muted fs-sm mb-0"><?= $contratosPorVencer ?> contratos vencen en los próximos 7 días</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="badge bg-<?= $cobrosVencidos > 0 ? 'warning' : 'secondary' ?> rounded-pill mt-1"><?= $cobrosVencidos > 0 ? '!' : '0' ?></span>
                    <div>
                        <p class="mb-0 fw-semibold">Cobros Vencidos</p>
                        <p class="text-muted fs-sm mb-0">S/ <?= $cobrosVencidos ?> en cobros vencidos</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="badge bg-<?= $pendientesEmision > 0 ? 'info' : 'secondary' ?> rounded-pill mt-1"><?= $pendientesEmision ?></span>
                    <div>
                        <p class="mb-0 fw-semibold">Comprobantes por Emitir</p>
                        <p class="text-muted fs-sm mb-0"><?= $pendientesEmision ?> comprobantes pendientes de emisión</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-<?= $tareasPendientes > 0 ? 'info' : 'secondary' ?> rounded-pill mt-1"><?= $tareasPendientes ?></span>
                    <div>
                        <p class="mb-0 fw-semibold">Tareas Pendientes</p>
                        <p class="text-muted fs-sm mb-0"><?= $tareasPendientes ?> tareas sin completar</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Clientes por Régimen</h5>
            </div>
            <div class="card-body">
                <?php if (empty($clientesPorRegimen)): ?>
                <p class="text-muted text-center mb-0">Sin datos</p>
                <?php else: ?>
                <?php $idx = 0; ?>
                <?php foreach ($clientesPorRegimen as $r): ?>
                <?php $pct = $totalRegimen > 0 ? round($r->total / $totalRegimen * 100) : 0; $idx++; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?= esc($r->nombre ?? 'Sin régimen') ?></span>
                    <span class="fw-semibold"><?= $r->total ?></span>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-<?= $idx % 2 === 0 ? 'success' : 'primary' ?>" style="width: <?= $pct ?>%"></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
