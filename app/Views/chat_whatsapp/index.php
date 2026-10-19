<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .chat-app-card {
        height: calc(100vh - 220px);
        min-height: 520px;
        overflow: hidden;
    }

    .chat-sidebar {
        width: 320px;
        min-width: 320px;
        border-right: 1px solid var(--ins-border-color);
    }

    .chat-contact-item {
        cursor: pointer;
    }

    .chat-contact-item.active {
        background-color: var(--ins-light) !important;
    }

    .chat-messages-body {
        overflow-y: auto;
    }

    .chat-bubble {
        max-width: 70%;
        word-break: break-word;
    }

    .chat-bubble.incoming {
        border-radius: 0 .9rem .9rem .9rem;
    }

    .chat-bubble.outgoing {
        border-radius: .9rem 0 .9rem .9rem;
    }

    .chat-back-btn {
        display: none !important;
    }

    @media (max-width: 767.98px) {
        .chat-sidebar.chat-hide-mobile,
        .chat-main.chat-hide-mobile {
            display: none !important;
        }

        .chat-back-btn {
            display: inline-flex !important;
        }
    }

    .chat-filter .nav-link {
        padding: .25rem .6rem;
        font-size: .8rem;
    }

    .chat-bubble .chat-autor {
        font-size: .7rem;
        font-weight: 600;
        opacity: .75;
    }

    .chat-drop-zone {
        position: absolute;
        inset: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--ins-primary-rgb, 13, 110, 253), .08);
        border: 2px dashed var(--ins-primary, #0d6efd);
        color: var(--ins-primary, #0d6efd);
        pointer-events: none;
    }

    .chat-drop-zone.d-none {
        display: none !important;
    }

    .chat-adjunto {
        position: relative;
        width: 72px;
    }

    .chat-adjunto-thumb {
        width: 72px;
        height: 72px;
        border-radius: .5rem;
        object-fit: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--ins-light);
    }

    .chat-adjunto-quitar {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 20px;
        height: 20px;
        padding: 0;
        line-height: 1;
        border-radius: 50%;
    }

    .chat-sticker {
        width: 150px;
        max-width: 150px;
        max-height: 150px;
        object-fit: contain;
        display: block;
    }

    .chat-bubble-sticker {
        background: transparent !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .chat-media-img {
        max-width: 240px;
        max-height: 240px;
        border-radius: .5rem;
        display: block;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-12">
        <h4 class="fw-bold mb-0">Chat WhatsApp</h4>
        <p class="text-muted mb-0">Conversaciones con clientes y contactos</p>
    </div>
</div>

<div class="alert alert-warning py-2 d-none" id="chatInstanciaAlert" role="alert">
    <div class="d-flex align-items-center gap-2">
        <i data-lucide="wifi-off" style="width:16px;height:16px;"></i>
        <span id="chatInstanciaAlertText">WhatsApp local desconectado: los mensajes quedarán en cola hasta que se reconecte.</span>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-body p-0">
                <div class="d-flex chat-app-card">

                    <!-- Sidebar: lista de chats -->
                    <div class="chat-sidebar d-flex flex-column" id="chatSidebar">
                        <div class="p-3 border-bottom">
                            <div class="d-flex gap-2 mb-2">
                                <div class="position-relative flex-grow-1">
                                    <i data-lucide="search" class="position-absolute top-50 translate-middle-y text-muted" style="width:16px;height:16px;left:12px;"></i>
                                    <input type="text" id="chatSearch" class="form-control form-control-sm" style="padding-left:34px;" placeholder="Buscar por nombre, número o cliente">
                                </div>
                                <button class="btn btn-sm btn-primary btn-icon flex-shrink-0" type="button" id="chatNuevoBtn" title="Nuevo chat">
                                    <i data-lucide="message-square-plus" style="width:16px;height:16px;"></i>
                                </button>
                            </div>
                            <ul class="nav nav-pills chat-filter" id="chatFilter">
                                <li class="nav-item"><a href="javascript:void(0);" class="nav-link active" data-filtro="todos">Todos</a></li>
                                <li class="nav-item"><a href="javascript:void(0);" class="nav-link" data-filtro="mios">Míos</a></li>
                                <li class="nav-item"><a href="javascript:void(0);" class="nav-link" data-filtro="sin_asignar">Sin asignar</a></li>
                            </ul>
                        </div>
                        <div class="flex-grow-1" data-simplebar>
                            <div class="list-group list-group-flush" id="chatContactList">
                                <div class="text-center text-muted fs-sm p-4">Cargando chats...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel principal: conversación -->
                    <div class="chat-main d-flex flex-column flex-grow-1 chat-hide-mobile position-relative" id="chatMain">
                        <div class="chat-drop-zone d-none" id="chatDropZone">
                            <div class="text-center">
                                <i data-lucide="upload" style="width:40px;height:40px;"></i>
                                <div class="fw-semibold mt-2">Suelta los archivos para adjuntarlos</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 p-2 px-3 border-bottom">
                            <button class="btn btn-icon btn-sm btn-soft-secondary chat-back-btn" id="chatBackBtn" type="button">
                                <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                            </button>
                            <span class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fw-semibold" id="chatHeaderAvatar">--</span>
                            </span>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-body text-truncate" id="chatHeaderName">&nbsp;</div>
                                <div class="fs-xxs text-muted text-truncate" id="chatHeaderStatus">&nbsp;</div>
                            </div>
                            <div class="d-flex gap-1 align-items-center d-none" id="chatHeaderActions">
                                <?php if ($esSupervisor): ?>
                                    <select class="form-select form-select-sm" id="chatTransferSelect" style="width:auto;max-width:180px;" title="Agente asignado">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($agentes as $a): ?>
                                            <option value="<?= (int) $a->id ?>"><?= esc($a->nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                                <button class="btn btn-icon btn-sm btn-soft-secondary" type="button" id="chatLecturasBtn" title="Visto por">
                                    <i data-lucide="eye" style="width:16px;height:16px;"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-soft-secondary" type="button" id="chatCerrarBtn" title="Cerrar chat">
                                    <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex-grow-1 chat-messages-body" data-simplebar>
                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-3" id="chatMessages">
                                <i data-lucide="message-square" style="width:42px;height:42px;" class="mb-2 opacity-50"></i>
                                <span class="fs-sm">Selecciona un chat para ver la conversación</span>
                            </div>
                        </div>

                        <div class="border-top px-3 pt-2 d-none" id="chatAdjuntos">
                            <div class="d-flex flex-wrap gap-2" id="chatAdjuntosLista"></div>
                            <div class="form-check form-check-sm mt-1 mb-0">
                                <input class="form-check-input" type="checkbox" id="chatComoDocumento">
                                <label class="form-check-label fs-xs text-muted" for="chatComoDocumento">Enviar como documento (sin compresión)</label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 p-2 px-3 border-top">
                            <button class="btn btn-icon btn-sm btn-soft-secondary flex-shrink-0" type="button" id="chatAdjuntarBtn" title="Adjuntar archivos" disabled>
                                <i data-lucide="paperclip" style="width:18px;height:18px;"></i>
                            </button>
                            <input type="file" id="chatArchivoInput" multiple hidden>
                            <input type="text" class="form-control form-control-sm" id="chatMessageInput" placeholder="Escribe un mensaje" maxlength="4096" disabled>
                            <button class="btn btn-primary btn-icon btn-sm rounded-circle flex-shrink-0" type="button" id="chatSendBtn" disabled>
                                <i data-lucide="send" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: nuevo chat -->
<div class="modal fade" id="chatNuevoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 position-relative">
                    <label class="form-label" for="chatNuevoCliente">Buscar cliente <span class="text-muted fs-xs">(opcional)</span></label>
                    <input type="text" class="form-control" id="chatNuevoCliente" placeholder="RUC o razón social" autocomplete="off">
                    <div class="list-group position-absolute w-100 shadow-sm d-none" id="chatNuevoClientes" style="z-index:10;max-height:220px;overflow-y:auto;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="chatNuevoNumero">Número de WhatsApp</label>
                    <div class="input-group">
                        <span class="input-group-text">+</span>
                        <input type="tel" class="form-control" id="chatNuevoNumero" placeholder="51 987 654 321" maxlength="20" autocomplete="off">
                    </div>
                    <div class="form-text">Si escribes 9 dígitos se agrega el código de Perú (51).</div>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="chatNuevoMensaje">Mensaje</label>
                    <textarea class="form-control" id="chatNuevoMensaje" rows="3" maxlength="4096" placeholder="Escribe el primer mensaje"></textarea>
                </div>
                <div class="alert alert-danger py-2 mt-3 mb-0 d-none" id="chatNuevoError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="chatNuevoEnviar">
                    <i data-lucide="send" class="me-1" style="width:16px;height:16px;"></i>Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="chatConfigData"><?= json_encode([
    'baseUrl'      => rtrim(base_url(), '/'),
    'usuarioId'    => $usuarioId,
    'esSupervisor' => $esSupervisor,
]) ?></script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/chat_web_whatsapp.js"></script>
<?= $this->endSection() ?>
