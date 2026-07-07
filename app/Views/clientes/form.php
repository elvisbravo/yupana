<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold"><?= isset($cliente) ? 'Editar Cliente' : 'Nuevo Cliente' ?></h4>
            <p class="text-muted mb-0"><?= isset($cliente) ? 'Modifica los datos del cliente' : 'Registra un nuevo cliente en el sistema' ?></p>
        </div>
        <a href="<?= base_url('clientes') ?>" class="btn btn-soft-secondary">
            <i data-lucide="arrow-left" class="me-1" style="width: 18px; height: 18px;"></i> Volver
        </a>
    </div>
</div>

<?php if (session('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
        <?php foreach (session('errors') as $error): ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= base_url(isset($cliente) ? 'clientes/actualizar/' . $cliente->id : 'clientes/guardar') ?>">
                    <?php if (isset($cliente)): ?>
                        <input type="hidden" name="id" value="<?= $cliente->id ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="codigo" value="<?= old('codigo', $codigo ?? $cliente->codigo ?? '') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha de Alta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_alta" value="<?= old('fecha_alta', $cliente->fecha_alta ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="activo" <?= old('estado', $cliente->estado ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= old('estado', $cliente->estado ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                <option value="suspendido" <?= old('estado', $cliente->estado ?? '') === 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                                <option value="baja" <?= old('estado', $cliente->estado ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3">Tipo de Persona</h6>

                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_persona" id="tipo_natural" value="natural"
                                <?= old('tipo_persona', $cliente->tipo_persona ?? '') === 'natural' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipo_natural">Persona Natural</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_persona" id="tipo_juridica" value="juridica"
                                <?= old('tipo_persona', $cliente->tipo_persona ?? '') === 'juridica' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipo_juridica">Persona Jurídica</label>
                        </div>
                    </div>

                    <div class="row g-3" id="campos-natural">
                        <div class="col-md-4">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" value="<?= old('nombres', $cliente->nombres ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" value="<?= old('apellidos', $cliente->apellidos ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" value="<?= old('fecha_nacimiento', $cliente->fecha_nacimiento ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-1" id="campos-juridica">
                        <div class="col-md-6">
                            <label class="form-label">Razón Social</label>
                            <input type="text" class="form-control" name="razon_social" value="<?= old('razon_social', $cliente->razon_social ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" class="form-control" name="nombre_comercial" value="<?= old('nombre_comercial', $cliente->nombre_comercial ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Constitución</label>
                            <input type="date" class="form-control" name="fecha_constitucion" value="<?= old('fecha_constitucion', $cliente->fecha_constitucion ?? '') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3">Documentos</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">RUC</label>
                            <input type="text" class="form-control" name="ruc" maxlength="11" value="<?= old('ruc', $cliente->ruc ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">DNI</label>
                            <input type="text" class="form-control" name="dni" maxlength="8" value="<?= old('dni', $cliente->dni ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Carné de Extranjería</label>
                            <input type="text" class="form-control" name="ce" maxlength="15" value="<?= old('ce', $cliente->ce ?? '') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3">Contacto</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= old('email', $cliente->email ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" value="<?= old('telefono', $cliente->telefono ?? '') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3">Dirección</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" value="<?= old('direccion', $cliente->direccion ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ubigeo</label>
                            <select class="form-select" name="ubigeo_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($ubigeos as $u): ?>
                                <option value="<?= $u->id ?>" <?= old('ubigeo_id', $cliente->ubigeo_id ?? '') == $u->id ? 'selected' : '' ?>>
                                    <?= esc($u->departamento) ?> / <?= esc($u->provincia) ?> / <?= esc($u->distrito) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control" name="referencia" value="<?= old('referencia', $cliente->referencia ?? '') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3">Tributario</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Régimen Tributario</label>
                            <select class="form-select" name="regimen_actual_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($regimenes as $r): ?>
                                <option value="<?= $r->id ?>" <?= old('regimen_actual_id', $cliente->regimen_actual_id ?? '') == $r->id ? 'selected' : '' ?>>
                                    <?= esc($r->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="3"><?= old('observaciones', $cliente->observaciones ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i data-lucide="save" class="me-1" style="width: 18px; height: 18px;"></i>
                            <?= isset($cliente) ? 'Actualizar' : 'Guardar' ?>
                        </button>
                        <a href="<?= base_url('clientes') ?>" class="btn btn-soft-secondary ms-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
