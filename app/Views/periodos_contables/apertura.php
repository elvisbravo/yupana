<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Apertura de Período</h4>
            <p class="text-muted mb-0">Creación y gestión de períodos contables</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#periodoModal">
            <i data-lucide="plus" class="me-1" style="width: 18px; height: 18px;"></i> Nuevo Período
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaPeriodos" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Cliente</th>
                            <th>Período</th>
                            <th>Estado</th>
                            <th>Cierre</th>
                            <th>Presentación</th>
                            <th>Operaciones</th>
                            <th>Obs.</th>
                            <th style="width: 140px;">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="periodoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <form id="periodoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Período Contable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAlert"></div>

                    <div class="mb-2">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="cliente_id" id="f_cliente">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c->id ?>"><?= esc($c->razon_social) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Año <span class="text-danger">*</span></label>
                            <input type="number" min="2000" max="2100" class="form-control form-control-sm" name="anio" id="f_anio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mes <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="mes" id="f_mes">
                                <option value="">Seleccionar...</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Setiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select form-select-sm" name="estado" id="f_estado">
                            <option value="abierto">Abierto</option>
                            <option value="en_proceso">En Proceso</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">N° Operaciones</label>
                            <input type="number" min="0" class="form-control form-control-sm" name="numero_operaciones" id="f_ops">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Cierre</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_cierre" id="f_cierre">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control form-control-sm" name="observaciones" id="f_observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>js/periodos_apertura.js"></script>
<?= $this->endSection() ?>
