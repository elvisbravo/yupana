<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold">Clientes Excluidos</h4>
        <p class="text-muted mb-0">Marca los clientes que NO deben facturarse automáticamente. Los que no marques se facturan con normalidad.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="clientesForm">
                    <div id="clientesAlert"></div>

                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" id="filtroClientes" placeholder="Filtrar por RUC o razón social..." style="max-width:300px;">
                    </div>

                    <div style="max-height:500px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;">
                        <table class="table table-sm table-hover mb-0" id="tablaClientesExcl">
                            <thead class="table-light fs-xxs">
                                <tr>
                                    <th style="width:80px;">Excluir <input type="checkbox" id="checkAll" class="ms-1"></th>
                                    <th>RUC</th>
                                    <th>Razón Social</th>
                                    <th>Servicios Activos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td><input type="checkbox" class="check-cliente" name="excluidos[]" value="<?= $c->id ?>" <?= $c->excluido ? 'checked' : '' ?>></td>
                                    <td><?= esc($c->ruc ?: '—') ?></td>
                                    <td><?= esc($c->razon_social) ?></td>
                                    <td><?= (int) $c->servicios_activos ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarClientesExcl">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Guardar
                        </button>
                        <a href="<?= base_url('facturacion-automatica/configuracion') ?>" class="btn btn-sm btn-soft-secondary">Volver a Configuración</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/facturacion_automatica_clientes.js"></script>
<?= $this->endSection() ?>
