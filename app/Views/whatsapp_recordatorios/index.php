<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold" id="tituloRecordatorios">Recordatorios de Pago por WhatsApp</h4>
            <p class="text-muted mb-0">Historial de mensajes de WhatsApp enviados por comprobante</p>
        </div>
        <div>
            <label class="form-label mb-0 fs-xxs">Período</label>
            <select class="form-select form-select-sm" id="filtroPeriodoWhatsapp" style="width:160px;">
                <?php if (empty($periodos)): ?>
                <option value="<?= esc($defaultPeriodo) ?>"><?= esc($defaultPeriodo) ?></option>
                <?php endif; ?>
                <?php foreach ($periodos as $p): ?>
                <option value="<?= esc($p->periodo) ?>" <?= $p->periodo === $defaultPeriodo ? 'selected' : '' ?>><?= esc($periodosLabels[$p->periodo]) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="tablaRecordatoriosWhatsapp" class="table table-striped dt-responsive align-middle mb-0">
                    <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th>Comprobante</th>
                            <th>Emisión</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Total</th>
                            <th>Mensaje</th>
                            <th>Enviado</th>
                            <th>Fecha Envío</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>var PERIODOS_LABELS_WHATSAPP = <?= json_encode($periodosLabels) ?>;</script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>js/whatsapp_recordatorios.js"></script>
<?= $this->endSection() ?>
