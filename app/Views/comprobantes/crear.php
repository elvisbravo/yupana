<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div>
            <h4 class="fw-bold">Crear Servicio</h4>
            <p class="text-muted mb-0">Registro de comprobante tipo invoice / POS</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="formAlert"></div>

                <form id="comprobanteForm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Sede <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="sede_id" id="f_sede" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($sedes as $s): ?>
                                <option value="<?= $s->id ?>" data-envio="<?= $s->tipo_envio ?>"><?= esc($s->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo Comprobante <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="tipo_comprobante_id" id="f_tipo" required disabled>
                                <option value="">Primero seleccione sede</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Serie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="serie" id="f_serie" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" name="numero" id="f_numero" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipo Envío</label>
                            <input type="text" class="form-control form-control-sm" id="f_tipo_envio" readonly>
                            <input type="hidden" name="tipo_envio" id="f_tipo_envio_hidden">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Moneda</label>
                            <select class="form-select form-select-sm" name="moneda" id="f_moneda">
                                <option value="PEN">PEN - Soles</option>
                                <option value="USD">USD - Dólares</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="f_cliente_text" placeholder="Buscar o escribir cliente..." autocomplete="off">
                                <input type="hidden" name="cliente_id" id="f_cliente">
                                <button type="button" class="btn btn-outline-primary" id="btnNuevoCliente" title="Nuevo cliente">
                                    <i data-lucide="plus" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                            <div id="clienteDropdown" class="list-group list-group-flush" style="display:none;position:absolute;z-index:1000;width:calc(100% - 40px);max-height:200px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;"></div>
                            <div class="mt-1">
                                <small class="text-muted" id="clienteInfo">Escribe para buscar o agrega uno nuevo</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Emisión <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_emision" id="f_fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado Pago</label>
                            <select class="form-select form-select-sm" name="estado_pago" id="f_estado_pago">
                                <option value="no_pagado">No Pagado</option>
                                <option value="pagado">Pagado</option>
                                <option value="parcial">Parcial</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3" id="serviciosContainer" style="display:none;">
                        <div class="col-12">
                            <label class="form-label">Servicios del Cliente</label>
                            <div id="serviciosLista" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle / Items</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:45%;">Descripción</th>
                                        <th style="width:10%;">Cant.</th>
                                        <th style="width:15%;">Precio Unit.</th>
                                        <th style="width:15%;">Total</th>
                                        <th style="width:15%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-soft-primary mt-1" id="agregarItem">
                            <i data-lucide="plus" style="width:14px;height:14px;"></i> Agregar Item
                        </button>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 ms-auto">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label fs-xxs">Tipo IGV</label>
                                    <select class="form-select form-select-sm" id="f_tipo_igv">
                                        <option value="0">Exonerado (0%)</option>
                                        <option value="18">Gravado (18%)</option>
                                        <option value="10">Gravado (10%)</option>
                                    </select>
                                </div>
                            </div>
                            <table class="table table-sm table-borderless text-end">
                                <tr>
                                    <td style="width:50%;">Subtotal:</td>
                                    <td class="fw-bold" id="resSubtotal">0.00</td>
                                </tr>
                                <tr>
                                    <td>IGV (<span id="igvLabel">0</span>%):</td>
                                    <td class="fw-bold" id="resIgv">0.00</td>
                                </tr>
                                <tr class="fs-5">
                                    <td><strong>TOTAL:</strong></td>
                                    <td><strong id="resTotal">0.00</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <input type="hidden" name="subtotal" id="f_subtotal">
                    <input type="hidden" name="igv" id="f_igv">
                    <input type="hidden" name="total" id="f_total">
                    <input type="hidden" name="detalle" id="f_detalle">

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control form-control-sm" name="observaciones" rows="2"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            <i data-lucide="save" style="width:16px;height:16px;"></i> Guardar Comprobante
                        </button>
                        <button type="button" class="btn btn-soft-secondary" id="btnLimpiar">
                            <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i> Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoClienteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-scrollable">
        <div class="modal-content">
            <form id="nuevoClienteForm">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="clienteAlert"></div>
                    <div class="mb-2">
                        <label class="form-label">RUC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="ruc" maxlength="11" placeholder="20123456789">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="razon_social" placeholder="Nombre del cliente">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email" placeholder="correo@ejemplo.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarCliente">
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
    <script>
        var CLIENTES = <?= json_encode($clientes) ?>;
    </script>
    <script src="<?= base_url() ?>js/comprobantes_crear.js"></script>
<?= $this->endSection() ?>
