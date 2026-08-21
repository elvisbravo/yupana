<?php $diasActivos = array_map('intval', explode(',', $config->dias_generacion ?? '1')); ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold">Facturación Automática</h4>
        <p class="text-muted mb-0">Horario y parámetros que usará el cron externo para generar comprobantes</p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Configuración de Generación Automática</h5>
            </div>
            <div class="card-body">
                <div id="cfaAlert"></div>
                <form id="cfaForm">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="activo" id="f_cfa_activo" <?= !empty($config->activo) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="f_cfa_activo">Facturación automática activa</label>
                    </div>

                    <label class="form-label">Día(s) del mes en que se generan las facturas</label>
                    <div class="row row-cols-lg-8 row-cols-4 g-2 mb-3">
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dias[]" value="<?= $d ?>" id="f_dia_<?= $d ?>" <?= in_array($d, $diasActivos, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="f_dia_<?= $d ?>"><?= $d ?></label>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hora de generación</label>
                            <input type="time" class="form-control form-control-sm" name="hora_generacion" value="<?= esc(substr($config->hora_generacion ?? '06:00:00', 0, 5)) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sede</label>
                            <select class="form-select form-select-sm" name="sede_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($sedes as $s): ?>
                                <option value="<?= $s->id ?>" <?= ($config->sede_id ?? null) == $s->id ? 'selected' : '' ?>><?= esc($s->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Comprobante</label>
                            <select class="form-select form-select-sm" name="tipo_comprobante_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t->id ?>" <?= ($config->tipo_comprobante_id ?? null) == $t->id ? 'selected' : '' ?>><?= esc($t->nombre) ?> (<?= esc($t->abreviatura) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Moneda</label>
                            <select class="form-select form-select-sm" name="moneda">
                                <?php foreach ($monedas as $m): ?>
                                <option value="<?= $m ?>" <?= ($config->moneda ?? 'PEN') === $m ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tasa IGV (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" name="tasa_igv" value="<?= esc($config->tasa_igv ?? '18.00') ?>">
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarCfa">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Guardar
                        </button>
                        <a href="<?= base_url('facturacion-automatica/clientes') ?>" class="btn btn-sm btn-soft-secondary">
                            Elegir clientes excluidos →
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actividad del Cron</h5>
            </div>
            <div class="card-body">
                <p class="mb-1 fs-xxs text-muted">Último período generado</p>
                <p class="fw-bold mb-3"><?= esc($config->ultimo_periodo_generado ?? '—') ?></p>
                <p class="mb-1 fs-xxs text-muted">Última ejecución</p>
                <p class="fw-bold mb-0"><?= esc($config->ultima_ejecucion ?? '—') ?></p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Integración</h5>
            </div>
            <div class="card-body fs-xxs">
                <p class="mb-2">El cron externo (Node.js) debe llamar, con el header <code>X-Api-Token</code>:</p>
                <p class="mb-1"><code>GET /api/facturacion/configuracion</code></p>
                <p class="mb-0"><code>POST /api/facturacion/generar</code></p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/facturacion_automatica_configuracion.js"></script>
<?= $this->endSection() ?>
