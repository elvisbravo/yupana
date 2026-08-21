<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Verificar Comprobante | <?= esc($empresa->nombre_comercial ?? 'Group Yupana') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="<?= base_url() ?>img/yupana-icon.png">

    <script src="<?= base_url() ?>assets/js/config.js"></script>
    <link href="<?= base_url() ?>assets/css/vendors.min.css" rel="stylesheet" type="text/css">
    <link href="<?= base_url() ?>assets/css/app.min.css" rel="stylesheet" type="text/css">
    <script src="<?= base_url() ?>assets/plugins/lucide/lucide.min.js"></script>

    <style>
        .auth-brand img { max-height: 60px; width: auto; }
        .card { border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); }
        .auth-box { min-height: 100vh; }
        #resultado .badge { font-size: 0.85rem; }
    </style>
</head>

<body>

<div class="auth-box overflow-hidden align-items-center d-flex">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-5 col-md-7 col-sm-9">
                <div class="card">
                    <div class="card-body p-4 p-sm-5">
                        <div class="auth-brand text-center mb-4">
                            <?php if (!empty($empresa->logo_url)): ?>
                            <img src="<?= base_url($empresa->logo_url) ?>" alt="Logo"><br>
                            <?php endif; ?>
                            <h5 class="fw-bold mt-2 mb-0"><?= esc($empresa->nombre_comercial ?? $empresa->razon_social ?? 'Group Yupana') ?></h5>
                            <p class="text-muted mt-2 mb-0">Verifica la autenticidad de tu comprobante ingresando sus datos exactos.</p>
                        </div>

                        <form id="verificarForm">
                            <div id="verificarAlert"></div>

                            <div class="mb-3">
                                <label class="form-label">RUC del emisor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ruc" maxlength="11" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Comprobante <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tipo_comprobante_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t->id ?>"><?= esc($t->nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Serie <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="serie" placeholder="F001" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Número <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="numero" placeholder="123" required>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_emision" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Monto Total <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="monto" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-semibold" id="btnVerificar">
                                    <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                                    Verificar
                                </button>
                            </div>
                        </form>

                        <div id="resultado" class="mt-4 d-none">
                            <hr>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <p class="mb-1"><strong id="r_tipo"></strong></p>
                                    <p class="mb-1 text-muted" id="r_serie_numero"></p>
                                    <p class="mb-1 text-muted">Emisión: <span id="r_fecha"></span></p>
                                    <p class="mb-0">Total: <strong id="r_total"></strong></p>
                                </div>
                                <span class="badge badge-soft-success">Aceptado</span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mt-3" id="r_descargas"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/vendors.min.js"></script>
<script src="<?= base_url() ?>assets/js/app.js"></script>
<script src="<?= base_url() ?>js/verificar.js"></script>

</body>

</html>
