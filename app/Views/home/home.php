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
                    <h3 class="fw-bold mb-0">248</h3>
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
                    <h3 class="fw-bold mb-0">186</h3>
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
                    <h3 class="fw-bold mb-0">S/ 32,580</h3>
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
                    <h3 class="fw-bold mb-0">S/ 18,420</h3>
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
                <a href="#" class="btn btn-sm btn-soft-primary">Ver Todos</a>
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
                            <tr>
                                <td><a href="#" class="text-body fw-semibold">Corporación Los Andes S.A.C.</a></td>
                                <td>20123456789</td>
                                <td>General</td>
                                <td><span class="badge bg-success-subtle text-success">Activo</span></td>
                                <td>15/06/2026</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="text-body fw-semibold">Inversiones del Sur E.I.R.L.</a></td>
                                <td>20456789123</td>
                                <td>Mype</td>
                                <td><span class="badge bg-success-subtle text-success">Activo</span></td>
                                <td>12/06/2026</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="text-body fw-semibold">D&M Consultores Asociados S.A.C.</a></td>
                                <td>20567893456</td>
                                <td>General</td>
                                <td><span class="badge bg-success-subtle text-success">Activo</span></td>
                                <td>10/06/2026</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="text-body fw-semibold">Comercial La Victoria E.I.R.L.</a></td>
                                <td>20789123456</td>
                                <td>RUS</td>
                                <td><span class="badge bg-warning-subtle text-warning">Pendiente</span></td>
                                <td>08/06/2026</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="text-body fw-semibold">Grupo Tecnológico del Perú S.A.C.</a></td>
                                <td>20891234567</td>
                                <td>General</td>
                                <td><span class="badge bg-success-subtle text-success">Activo</span></td>
                                <td>05/06/2026</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="text-body fw-semibold">Transportes Unidos del Norte S.A.</a></td>
                                <td>20178934512</td>
                                <td>Mype</td>
                                <td><span class="badge bg-success-subtle text-success">Activo</span></td>
                                <td>03/06/2026</td>
                            </tr>
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
                    <span class="badge bg-danger rounded-pill mt-1">3</span>
                    <div>
                        <p class="mb-0 fw-semibold">Contratos por Vencer</p>
                        <p class="text-muted fs-sm mb-0">5 contratos vencen en los próximos 7 días</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="badge bg-warning rounded-pill mt-1">2</span>
                    <div>
                        <p class="mb-0 fw-semibold">Cobros Vencidos</p>
                        <p class="text-muted fs-sm mb-0">S/ 4,280 en cobros con más de 30 días de atraso</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="badge bg-info rounded-pill mt-1">1</span>
                    <div>
                        <p class="mb-0 fw-semibold">Comprobantes por Emitir</p>
                        <p class="text-muted fs-sm mb-0">12 comprobantes del período pendientes de emisión</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-secondary rounded-pill mt-1">0</span>
                    <div>
                        <p class="mb-0 fw-semibold">Tareas Pendientes</p>
                        <p class="text-muted fs-sm mb-0">8 tareas asignadas sin completar</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Clientes por Régimen</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>General</span>
                    <span class="fw-semibold">142</span>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: 57%"></div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Mype</span>
                    <span class="fw-semibold">68</span>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: 27%"></div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>RUS</span>
                    <span class="fw-semibold">23</span>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 9%"></div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Otros</span>
                    <span class="fw-semibold">15</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-secondary" style="width: 6%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>