<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold">Crear Mensaje</h4>
            <p class="text-muted mb-0">Redacta un mensaje y selecciona los contactos destinatarios</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="mensajeForm">
                    <div id="formAlert"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="titulo" id="f_titulo" maxlength="255" placeholder="Asunto del mensaje">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contenido</label>
                        <textarea class="form-control form-control-sm" name="contenido" id="f_contenido" rows="4" placeholder="Escribe el mensaje..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Archivo adjunto</label>
                        <input type="file" class="form-control form-control-sm" name="path_file" id="f_path_file">
                    </div>

                    <hr>
                    <h6 class="fw-semibold mb-2">Seleccionar Contactos</h6>
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" id="filtroContactos" placeholder="Filtrar por contacto o cliente..." style="max-width:300px;">
                    </div>
                    <div style="max-height:400px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;">
                        <table class="table table-sm table-hover mb-0" id="tablaContactos">
                            <thead class="table-light fs-xxs">
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="checkAll"></th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th>Cliente</th>
                                    <th>RUC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contactos as $c): ?>
                                <tr>
                                    <td><input type="checkbox" class="check-contacto" name="contactos[]" value="<?= $c->id ?>"></td>
                                    <td><?= esc($c->contacto_nombre) ?></td>
                                    <td><?= esc($c->telefono ?: '—') ?></td>
                                    <td><?= esc($c->razon_social) ?></td>
                                    <td><?= esc($c->ruc ?: '—') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary" id="btnGuardar">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Guardar y Enviar
                        </button>
                        <a href="<?= base_url() ?>mensajes/listado" class="btn btn-sm btn-soft-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/mensajes_crear.js"></script>
<?= $this->endSection() ?>
