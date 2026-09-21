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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-12">
        <h4 class="fw-bold mb-0">Chat WhatsApp</h4>
        <p class="text-muted mb-0">Conversaciones con clientes y contactos</p>
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
                            <div class="position-relative">
                                <i data-lucide="search" class="position-absolute top-50 translate-middle-y text-muted" style="width:16px;height:16px;left:12px;"></i>
                                <input type="text" id="chatSearch" class="form-control form-control-sm" style="padding-left:34px;" placeholder="Buscar o empezar un chat nuevo">
                            </div>
                        </div>
                        <div class="flex-grow-1" data-simplebar>
                            <div class="list-group list-group-flush" id="chatContactList">
                                <?php foreach ($contactos as $i => $c): ?>
                                    <?php $ultimo = !empty($conversaciones[$c['id']]) ? end($conversaciones[$c['id']]) : null; ?>
                                    <a href="javascript:void(0);"
                                       class="list-group-item list-group-item-action chat-contact-item d-flex align-items-center gap-2 py-3<?= $i === 0 ? ' active' : '' ?>"
                                       data-contact-id="<?= $c['id'] ?>">
                                        <span class="avatar-lg flex-shrink-0">
                                            <span class="avatar-title bg-<?= esc($c['color']) ?>-subtle text-<?= esc($c['color']) ?> rounded-circle fw-semibold">
                                                <?= esc($c['iniciales']) ?>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1 overflow-hidden">
                                            <span class="d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold text-body text-truncate"><?= esc($c['nombre']) ?></span>
                                                <span class="fs-xxs text-muted flex-shrink-0 ms-1"><?= $ultimo ? esc($ultimo['hora']) : '' ?></span>
                                            </span>
                                            <span class="d-flex justify-content-between align-items-center">
                                                <span class="fs-xs text-muted text-truncate"><?= $ultimo ? esc($ultimo['texto']) : 'No hay mensajes aún' ?></span>
                                                <?php if ($c['noLeidos'] > 0): ?>
                                                    <span class="badge rounded-pill bg-success flex-shrink-0 ms-1"><?= (int) $c['noLeidos'] ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panel principal: conversación -->
                    <div class="chat-main d-flex flex-column flex-grow-1 chat-hide-mobile" id="chatMain">
                        <div class="d-flex align-items-center gap-2 p-2 px-3 border-bottom">
                            <button class="btn btn-icon btn-sm btn-soft-secondary chat-back-btn" id="chatBackBtn" type="button">
                                <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                            </button>
                            <span class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fw-semibold" id="chatHeaderAvatar">--</span>
                            </span>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-body text-truncate" id="chatHeaderName">&nbsp;</div>
                                <div class="fs-xxs text-muted" id="chatHeaderStatus">&nbsp;</div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-icon btn-sm btn-soft-secondary" type="button"><i data-lucide="phone" style="width:16px;height:16px;"></i></button>
                                <button class="btn btn-icon btn-sm btn-soft-secondary" type="button"><i data-lucide="video" style="width:16px;height:16px;"></i></button>
                                <button class="btn btn-icon btn-sm btn-soft-secondary" type="button"><i data-lucide="more-vertical" style="width:16px;height:16px;"></i></button>
                            </div>
                        </div>

                        <div class="flex-grow-1 chat-messages-body" data-simplebar>
                            <div class="p-3 d-flex flex-column gap-2" id="chatMessages"></div>
                        </div>

                        <div class="d-flex align-items-center gap-2 p-2 px-3 border-top">
                            <button class="btn btn-icon btn-sm btn-soft-secondary" type="button"><i data-lucide="paperclip" style="width:18px;height:18px;"></i></button>
                            <button class="btn btn-icon btn-sm btn-soft-secondary" type="button"><i data-lucide="smile" style="width:18px;height:18px;"></i></button>
                            <input type="text" class="form-control form-control-sm" id="chatMessageInput" placeholder="Escribe un mensaje">
                            <button class="btn btn-primary btn-icon btn-sm rounded-circle flex-shrink-0" type="button" id="chatSendBtn">
                                <i data-lucide="send" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="chatContactosData"><?= json_encode($contactos) ?></script>
<script type="application/json" id="chatConversacionesData"><?= json_encode($conversaciones) ?></script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>js/chat_web_whatsapp.js"></script>
<?= $this->endSection() ?>
