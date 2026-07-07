<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<ul class="nav nav-tabs mb-4" id="vencimientosTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabCargar">Cargar Cronograma</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabConsultar">Consultar por Cliente</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabListado">Cronograma Cargado</button>
    </li>
</ul>

<div class="tab-content">

    <!-- Pestaña Cargar -->
    <div class="tab-pane fade show active" id="tabCargar">
        <div class="card">
            <div class="card-body">
                <form id="cronogramaForm">
                    <div id="formAlertCargar"></div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Año <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="anio" id="f_anio">
                                <?php for ($a = 2026; $a <= date('Y') + 2; $a++): ?>
                                <option value="<?= $a ?>" <?= $a == date('Y') ? 'selected' : '' ?>><?= $a ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <p class="text-muted small">Ingresa solo el <strong>día</strong> (1-31) para cada mes y dígito. El sistema arma la fecha completa con el año y mes.</p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tablaDigitos">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Período</th>
                                    <?php for ($d = 0; $d <= 9; $d++): ?>
                                    <th class="text-center">Dígito <?= $d ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre']; ?>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <tr>
                                    <td class="fw-semibold"><?= $meses[$m - 1] ?> (se declara <?= $m == 12 ? 'Enero del siguiente año' : $meses[$m] ?>)</td>
                                    <?php for ($d = 0; $d <= 9; $d++): ?>
                                    <td><input type="number" class="form-control form-control-sm text-center" name="dias[<?= $m ?>][<?= $d ?>]" min="1" max="31" placeholder="--" style="min-width:55px;width:55px;"></td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarCronograma">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Guardar Cronograma
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pestaña Consultar -->
    <div class="tab-pane fade" id="tabConsultar">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Cliente</label>
                        <select class="form-select form-select-sm" id="f_cliente_consulta">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c->id ?>"><?= esc($c->razon_social) ?> (<?= $c->ruc ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Año</label>
                        <input type="number" class="form-control form-control-sm" id="f_anio_consulta" value="<?= date('Y') ?>" min="2020" max="2040">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-primary" id="btnConsultar">Consultar</button>
                    </div>
                </div>

                <div id="resultadoConsulta" style="display:none;">
                    <div class="alert alert-info py-2" id="infoCliente"></div>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th>Año</th><th>Mes</th><th>Fecha Vencimiento</th></tr>
                        </thead>
                        <tbody id="consultaBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestaña Listado -->
    <div class="tab-pane fade" id="tabListado">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Año</label>
                        <select class="form-select form-select-sm" id="filtroAnio">
                            <option value="">Todos</option>
                            <?php foreach ($anios as $a): ?>
                            <option value="<?= $a->anio ?>"><?= $a->anio ?></option>
                            <?php endforeach; ?>
                            <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                        </select>
                    </div>
                </div>
                <table id="tablaVencimientos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Año</th>
                            <th>Período</th>
                            <th>Dígito</th>
                            <th>Fecha Vencimiento</th>
                            <th style="width:60px;">Acción</th>
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
    <script src="<?= base_url() ?>js/vencimientos.js"></script>
<?= $this->endSection() ?>
