(function () {
    var config = JSON.parse(document.getElementById('chatConfigData').textContent || '{}');
    var BASE = (config.baseUrl || '') + '/chat-web-whatsapp';
    var POLL_MS = 3000;
    var COLORES = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];

    var chats = [];
    var activeId = null;
    var activeChat = null;
    var ultimoMensajeId = 0;
    var filtro = 'todos';
    var busqueda = '';
    var cargandoMensajes = false;
    var cargandoChats = false;

    var elSidebar = document.getElementById('chatSidebar');
    var elMain = document.getElementById('chatMain');
    var elList = document.getElementById('chatContactList');
    var elMessages = document.getElementById('chatMessages');
    var elHeaderAvatar = document.getElementById('chatHeaderAvatar');
    var elHeaderName = document.getElementById('chatHeaderName');
    var elHeaderStatus = document.getElementById('chatHeaderStatus');
    var elHeaderActions = document.getElementById('chatHeaderActions');
    var elTransfer = document.getElementById('chatTransferSelect');
    var elCerrarBtn = document.getElementById('chatCerrarBtn');
    var elLecturasBtn = document.getElementById('chatLecturasBtn');
    var elInput = document.getElementById('chatMessageInput');
    var elSendBtn = document.getElementById('chatSendBtn');
    var elBackBtn = document.getElementById('chatBackBtn');
    var elSearch = document.getElementById('chatSearch');
    var elFilter = document.getElementById('chatFilter');
    var elAlert = document.getElementById('chatInstanciaAlert');

    // ---------------------------------------------------------------- utilidades

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function api(url, opciones) {
        opciones = opciones || {};
        opciones.headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, opciones.headers || {});
        return fetch(url, opciones).then(function (r) {
            return r.json().catch(function () { return { success: false, message: 'Respuesta inválida del servidor.' }; })
                .then(function (data) {
                    if (!r.ok || data.success === false) {
                        var err = new Error(data.message || 'Error ' + r.status);
                        err.status = r.status;
                        throw err;
                    }
                    return data;
                });
        });
    }

    function post(url, campos) {
        var fd = new FormData();
        Object.keys(campos || {}).forEach(function (k) { fd.append(k, campos[k] == null ? '' : campos[k]); });
        return api(url, { method: 'POST', body: fd });
    }

    function nombreChat(c) {
        return c.cliente_nombre || c.nombre || (c.numero ? '+' + c.numero : 'Sin nombre');
    }

    function iniciales(nombre) {
        var partes = String(nombre).replace(/[^\p{L}\p{N} ]/gu, '').trim().split(/\s+/);
        var ini = (partes[0] || '').charAt(0) + (partes.length > 1 ? partes[1].charAt(0) : '');
        return (ini || '#').toUpperCase();
    }

    function color(id) {
        return COLORES[Number(id) % COLORES.length];
    }

    function parseFecha(s) {
        return s ? new Date(String(s).replace(' ', 'T')) : null;
    }

    function hora(s) {
        var d = parseFecha(s);
        if (!d || isNaN(d)) return '';
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    function fechaCorta(s) {
        var d = parseFecha(s);
        if (!d || isNaN(d)) return '';
        var hoy = new Date();
        if (d.toDateString() === hoy.toDateString()) return hora(s);
        var ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
        if (d.toDateString() === ayer.toDateString()) return 'Ayer';
        return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
    }

    function icons() {
        if (window.lucide) window.lucide.createIcons();
    }

    function scrollToBottom() {
        var wrapper = elMessages.closest('[data-simplebar]');
        if (!wrapper) return;
        var scrollEl = wrapper.SimpleBar ? wrapper.SimpleBar.getScrollElement() : wrapper;
        if (scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight;
    }

    function estaAbajo() {
        var wrapper = elMessages.closest('[data-simplebar]');
        var scrollEl = wrapper && wrapper.SimpleBar ? wrapper.SimpleBar.getScrollElement() : wrapper;
        if (!scrollEl) return true;
        return scrollEl.scrollHeight - scrollEl.scrollTop - scrollEl.clientHeight < 80;
    }

    function toast(msg) {
        if (window.Swal) {
            window.Swal.fire({ icon: 'warning', text: msg, timer: 3500, showConfirmButton: false });
        } else {
            alert(msg);
        }
    }

    // ---------------------------------------------------------------- lista de chats

    function chatItemHtml(c) {
        var nombre = nombreChat(c);
        var col = color(c.id);
        var noLeidos = Number(c.no_leidos) || 0;
        var etiqueta = '';
        if (c.estado_chat === 'cerrado') {
            etiqueta = '<span class="badge bg-secondary-subtle text-secondary ms-1">Cerrado</span>';
        } else if (!c.agente_id) {
            etiqueta = '<span class="badge bg-warning-subtle text-warning ms-1">Sin asignar</span>';
        } else if (Number(c.agente_id) !== Number(config.usuarioId) && c.agente_nombre) {
            etiqueta = '<span class="badge bg-info-subtle text-info ms-1 text-truncate" style="max-width:90px;">' + escapeHtml(c.agente_nombre) + '</span>';
        }

        return '' +
            '<a href="javascript:void(0);" class="list-group-item list-group-item-action chat-contact-item d-flex align-items-center gap-2 py-3' +
                (String(c.id) === String(activeId) ? ' active' : '') + '" data-contact-id="' + c.id + '">' +
                '<span class="avatar-lg flex-shrink-0">' +
                    '<span class="avatar-title bg-' + col + '-subtle text-' + col + ' rounded-circle fw-semibold">' + escapeHtml(iniciales(nombre)) + '</span>' +
                '</span>' +
                '<span class="flex-grow-1 overflow-hidden">' +
                    '<span class="d-flex justify-content-between align-items-center">' +
                        '<span class="fw-semibold text-body text-truncate">' + escapeHtml(nombre) + '</span>' +
                        '<span class="fs-xxs text-muted flex-shrink-0 ms-1">' + escapeHtml(fechaCorta(c.ultimo_mensaje_at)) + '</span>' +
                    '</span>' +
                    '<span class="d-flex justify-content-between align-items-center">' +
                        '<span class="fs-xs text-muted text-truncate">' + escapeHtml(c.ultimo_mensaje || 'No hay mensajes aún') + '</span>' +
                        '<span class="d-flex align-items-center flex-shrink-0">' + etiqueta +
                            (noLeidos > 0 ? '<span class="badge rounded-pill bg-success ms-1">' + noLeidos + '</span>' : '') +
                        '</span>' +
                    '</span>' +
                '</span>' +
            '</a>';
    }

    function numeroDeBusqueda() {
        var digitos = busqueda.replace(/[\s\-+().]/g, '');
        return /^\d{9,15}$/.test(digitos) ? digitos : null;
    }

    function renderChats() {
        if (!chats.length) {
            var num = numeroDeBusqueda();
            elList.innerHTML = num
                ? '<a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3" id="chatIniciarNumero">' +
                      '<i data-lucide="message-square-plus" class="text-primary" style="width:18px;height:18px;"></i>' +
                      '<span>Iniciar chat con <strong>+' + escapeHtml(num.length === 9 ? '51' + num : num) + '</strong></span></a>'
                : '<div class="text-center text-muted fs-sm p-4">' +
                      (busqueda ? 'No se encontraron chats' : 'No hay conversaciones todavía') + '</div>';
            icons();
            return;
        }
        elList.innerHTML = chats.map(chatItemHtml).join('');
    }

    var ESTADOS_WA = {
        qr: 'esperando que se escanee el QR',
        loading: 'cargando',
        initializing: 'iniciando',
        disconnected: 'desconectado',
        auth_failure: 'error de autenticación'
    };

    function renderInstancias(instancias) {
        var caida = (instancias || []).filter(function (i) { return !i.conectada; })[0];
        elAlert.classList.toggle('d-none', !caida);
        if (!caida) return;
        var texto = !caida.latido_ok
            ? 'Sin conexión con la aplicación WhatsApp local'
            : 'WhatsApp no vinculado (' + (ESTADOS_WA[caida.estado] || caida.estado) + ')';
        document.getElementById('chatInstanciaAlertText').textContent =
            texto + ': los mensajes quedarán en cola hasta que se reconecte.';
    }

    function cargarChats() {
        if (cargandoChats) return Promise.resolve();
        cargandoChats = true;
        var params = new URLSearchParams({ filtro: filtro, q: busqueda });
        return api(BASE + '/chats?' + params.toString())
            .then(function (data) {
                chats = data.chats || [];
                renderChats();
                renderInstancias(data.instancias);
            })
            .catch(function (e) { console.error('chats', e); })
            .finally(function () { cargandoChats = false; });
    }

    // ---------------------------------------------------------------- mensajes

    function ticksHtml(m) {
        if (m.direccion !== 'saliente') return '';
        var estilo = 'width:14px;height:14px;vertical-align:-2px;';
        var ack = Number(m.ack) || 0;

        if (m.estado_envio === 'error' || ack < 0) {
            return '<i data-lucide="alert-triangle" class="ms-1 text-danger" title="' + escapeHtml(m.error || 'Error al enviar') + '" style="' + estilo + '"></i>';
        }
        if (ack >= 3) return '<i data-lucide="check-check" class="ms-1" title="Leído" style="' + estilo + 'color:#4fc3f7;"></i>';
        if (ack === 2) return '<i data-lucide="check-check" class="ms-1" title="Entregado" style="' + estilo + '"></i>';
        if (ack === 1 || m.estado_envio === 'enviado' || (!m.estado_envio && m.origen !== 'chat_web')) {
            return '<i data-lucide="check" class="ms-1" title="Enviado" style="' + estilo + '"></i>';
        }
        return '<i data-lucide="clock" class="ms-1" title="Pendiente de envío" style="' + estilo + '"></i>';
    }

    function mediaHtml(m) {
        return (m.media || []).map(function (f) {
            var url = BASE + '/media/' + f.id;
            var comoDocumento = m.tipo === 'document';
            if (m.tipo === 'sticker') {
                return '<img src="' + url + '" class="chat-sticker" alt="sticker">';
            }
            if (!comoDocumento && /^image\//.test(f.mimetype)) {
                return '<a href="' + url + '" target="_blank"><img src="' + url + '" class="chat-media-img mb-1" alt=""></a>';
            }
            if (!comoDocumento && /^audio\//.test(f.mimetype)) {
                return '<audio controls preload="none" src="' + url + '" class="mb-1" style="max-width:240px;"></audio>';
            }
            if (!comoDocumento && /^video\//.test(f.mimetype)) {
                return '<video controls preload="none" src="' + url + '" class="chat-media-img mb-1"></video>';
            }
            return '<a href="' + url + '" target="_blank" class="d-inline-flex align-items-center gap-1 mb-1">' +
                '<i data-lucide="file-text" style="width:16px;height:16px;"></i>' + escapeHtml(f.nombre_archivo || 'Archivo') + '</a>';
        }).join('');
    }

    function bubbleHtml(m) {
        var isOut = m.direccion === 'saliente';
        var autor = '';
        if (isOut && m.usuario_nombre) {
            autor = m.usuario_nombre;
        } else if (isOut && m.origen === 'celular') {
            autor = 'Desde el celular';
        } else if (!isOut && m.autor && activeChat && Number(activeChat.es_grupo) === 1) {
            autor = m.autor;
        }
        var sinTexto = !m.cuerpo && !(m.media || []).length;
        var esSticker = m.tipo === 'sticker' && (m.media || []).length > 0;

        return '' +
            '<div class="d-flex ' + (isOut ? 'justify-content-end' : 'justify-content-start') + '" data-msg-id="' + m.id + '">' +
                '<div class="chat-bubble ' + (isOut ? 'outgoing' : 'incoming') +
                    (esSticker ? ' chat-bubble-sticker' : (isOut ? ' bg-primary-subtle' : ' bg-light')) + ' px-3 py-2">' +
                    (autor ? '<div class="chat-autor">' + escapeHtml(autor) + '</div>' : '') +
                    mediaHtml(m) +
                    (m.cuerpo ? '<div style="white-space:pre-wrap;">' + escapeHtml(m.cuerpo) + '</div>' : '') +
                    (sinTexto ? '<div class="fst-italic text-muted">[' + escapeHtml(m.tipo || 'mensaje') + ']</div>' : '') +
                    '<div class="fs-xxs text-muted text-end mt-1"><span class="chat-msg-hora">' + escapeHtml(fechaCorta(m.fecha_mensaje)) + '</span>' +
                        '<span class="chat-msg-ticks">' + ticksHtml(m) + '</span></div>' +
                '</div>' +
            '</div>';
    }

    function renderVacio() {
        elMessages.className = 'h-100 d-flex flex-column align-items-center justify-content-center text-muted p-3';
        elMessages.innerHTML =
            '<i data-lucide="message-circle" style="width:42px;height:42px;" class="mb-2 opacity-50"></i>' +
            '<span class="fs-sm">No hay mensajes aún, escribe el primero</span>';
    }

    function agregarMensajes(mensajes, reemplazar) {
        var abajo = reemplazar || estaAbajo();

        if (reemplazar) {
            if (!mensajes.length) {
                renderVacio();
                icons();
                return;
            }
            elMessages.className = 'p-3 d-flex flex-column gap-2';
            elMessages.innerHTML = mensajes.map(bubbleHtml).join('');
        } else if (mensajes.length) {
            if (!elMessages.querySelector('[data-msg-id]')) {
                elMessages.className = 'p-3 d-flex flex-column gap-2';
                elMessages.innerHTML = '';
            }
            mensajes.forEach(function (m) {
                if (!elMessages.querySelector('[data-msg-id="' + m.id + '"]')) {
                    elMessages.insertAdjacentHTML('beforeend', bubbleHtml(m));
                }
            });
        }

        mensajes.forEach(function (m) { ultimoMensajeId = Math.max(ultimoMensajeId, Number(m.id)); });
        icons();
        if (abajo) scrollToBottom();
    }

    function actualizarTicks(estados) {
        (estados || []).forEach(function (e) {
            var el = elMessages.querySelector('[data-msg-id="' + e.id + '"] .chat-msg-ticks');
            if (!el) return;
            var html = ticksHtml({ direccion: 'saliente', estado_envio: e.estado_envio, error: e.error, ack: e.ack, origen: e.origen });
            if (el.dataset.html !== html) {
                el.dataset.html = html;
                el.innerHTML = html;
            }
        });
        icons();
    }

    function cargarMensajes(reemplazar) {
        if (activeId === null || (cargandoMensajes && !reemplazar)) return Promise.resolve();
        cargandoMensajes = true;
        var id = activeId;
        var url = BASE + '/chats/' + id + '/mensajes' + (reemplazar ? '' : '?desde_id=' + ultimoMensajeId);

        return api(url)
            .then(function (data) {
                if (id !== activeId) return;
                activeChat = data.chat;
                renderHeader();
                agregarMensajes(data.mensajes || [], reemplazar);
                actualizarTicks(data.estados);
                marcarLeidoEnLista(id);
            })
            .catch(function (e) {
                if (e.status === 404 && id === activeId) {
                    toast('Ya no tienes acceso a este chat.');
                    cerrarChatActivo();
                    cargarChats();
                } else {
                    console.error('mensajes', e);
                }
            })
            .finally(function () { cargandoMensajes = false; });
    }

    function marcarLeidoEnLista(id) {
        var item = elList.querySelector('.chat-contact-item[data-contact-id="' + id + '"] .badge.bg-success');
        if (item) item.remove();
        chats.forEach(function (c) { if (String(c.id) === String(id)) c.no_leidos = 0; });
    }

    // ---------------------------------------------------------------- encabezado y acciones

    function puedeEscribir() {
        if (!activeChat) return false;
        return !activeChat.agente_id || Number(activeChat.agente_id) === Number(config.usuarioId) || config.esSupervisor;
    }

    function renderHeader() {
        if (!activeChat) return;
        var nombre = nombreChat(activeChat);
        var col = color(activeChat.id);
        elHeaderAvatar.textContent = iniciales(nombre);
        elHeaderAvatar.className = 'avatar-title bg-' + col + '-subtle text-' + col + ' rounded-circle fw-semibold';
        elHeaderName.textContent = nombre;

        var partes = [];
        if (activeChat.numero) partes.push('+' + activeChat.numero);
        if (activeChat.cliente_nombre && activeChat.nombre) partes.push(activeChat.nombre);
        partes.push(activeChat.agente_nombre ? 'Atiende: ' + activeChat.agente_nombre : 'Sin asignar');
        if (activeChat.estado_chat === 'cerrado') partes.push('Cerrado');
        elHeaderStatus.textContent = partes.join(' · ');

        elHeaderActions.classList.remove('d-none');
        if (elTransfer && document.activeElement !== elTransfer) {
            elTransfer.value = activeChat.agente_id ? String(activeChat.agente_id) : '';
        }
        elCerrarBtn.title = activeChat.estado_chat === 'cerrado' ? 'Reabrir chat' : 'Cerrar chat';
        elCerrarBtn.classList.toggle('btn-soft-success', activeChat.estado_chat === 'cerrado');
        elCerrarBtn.classList.toggle('btn-soft-secondary', activeChat.estado_chat !== 'cerrado');

        var escribir = puedeEscribir();
        elInput.disabled = !escribir;
        elSendBtn.disabled = !escribir;
        elAdjuntarBtn.disabled = !escribir;
        if (!escribir) limpiarAdjuntos();
        elInput.placeholder = escribir ? 'Escribe un mensaje' : 'Este chat lo atiende ' + (activeChat.agente_nombre || 'otro usuario');
    }

    function cerrarChatActivo() {
        activeId = null;
        activeChat = null;
        elHeaderActions.classList.add('d-none');
        elHeaderName.innerHTML = '&nbsp;';
        elHeaderStatus.innerHTML = '&nbsp;';
        elHeaderAvatar.textContent = '--';
        elInput.disabled = true;
        elSendBtn.disabled = true;
        elAdjuntarBtn.disabled = true;
        limpiarAdjuntos();
        renderVacio();
        icons();
    }

    function selectChat(id, userInitiated) {
        activeId = Number(id);
        activeChat = chats.filter(function (c) { return Number(c.id) === activeId; })[0] || null;
        ultimoMensajeId = 0;

        elList.querySelectorAll('.chat-contact-item').forEach(function (item) {
            item.classList.toggle('active', String(item.dataset.contactId) === String(id));
        });

        elMessages.className = 'h-100 d-flex align-items-center justify-content-center text-muted p-3';
        elMessages.innerHTML = '<span class="fs-sm">Cargando...</span>';
        renderHeader();
        cargarMensajes(true);

        if (userInitiated && window.matchMedia('(max-width: 767.98px)').matches) {
            elSidebar.classList.add('chat-hide-mobile');
            elMain.classList.remove('chat-hide-mobile');
        }
    }

    function sendMessage() {
        var text = elInput.value.trim();
        if ((!text && !adjuntos.length) || activeId === null || elSendBtn.disabled) return;

        var id = activeId;
        var enviados = adjuntos.slice();
        var fd = new FormData();
        fd.append('mensaje', text);
        enviados.forEach(function (a) { fd.append('archivos[]', a.file, a.file.name); });
        if (enviados.length && elComoDocumento.checked) fd.append('como_documento', '1');

        elInput.value = '';
        elSendBtn.disabled = true;
        elAdjuntarBtn.disabled = true;
        if (enviados.length) elInput.placeholder = 'Subiendo ' + enviados.length + ' archivo(s)...';

        api(BASE + '/chats/' + id + '/enviar', { method: 'POST', body: fd })
            .then(function (data) {
                if (enviados.length) limpiarAdjuntos();
                if (id !== activeId) return;
                activeChat = data.chat;
                renderHeader();
                return cargarMensajes(false);
            })
            .then(cargarChats)
            .catch(function (e) {
                elInput.value = text;
                toast(e.message);
                cargarMensajes(false);
            })
            .finally(function () {
                if (id === activeId) renderHeader();
                elInput.focus();
            });
    }

    // ---------------------------------------------------------------- adjuntos

    var MAX_ADJUNTOS = 10;
    var MAX_BYTES = 16 * 1024 * 1024;
    var EXT_BLOQUEADAS = ['exe', 'bat', 'cmd', 'com', 'scr', 'msi', 'js', 'vbs', 'jar', 'ps1', 'sh', 'php', 'phtml', 'phar'];
    var adjuntos = [];

    var elAdjuntos = document.getElementById('chatAdjuntos');
    var elAdjuntosLista = document.getElementById('chatAdjuntosLista');
    var elAdjuntarBtn = document.getElementById('chatAdjuntarBtn');
    var elArchivoInput = document.getElementById('chatArchivoInput');
    var elComoDocumento = document.getElementById('chatComoDocumento');
    var elDropZone = document.getElementById('chatDropZone');

    function tamanoLegible(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return (bytes / 1024 / 1024).toFixed(1) + ' MB';
    }

    function iconoArchivo(file) {
        if (/^video\//.test(file.type)) return 'film';
        if (/^audio\//.test(file.type)) return 'music';
        if (/pdf$/.test(file.type)) return 'file-text';
        return 'file';
    }

    function agregarAdjuntos(files) {
        if (!puedeEscribir()) return;
        var errores = [];
        Array.prototype.forEach.call(files, function (file) {
            var ext = (file.name.split('.').pop() || '').toLowerCase();
            if (adjuntos.length >= MAX_ADJUNTOS) {
                errores.push('Máximo ' + MAX_ADJUNTOS + ' archivos por envío.');
            } else if (EXT_BLOQUEADAS.indexOf(ext) !== -1) {
                errores.push('No se permite enviar archivos .' + ext + '.');
            } else if (file.size > MAX_BYTES) {
                errores.push('«' + file.name + '» supera 16 MB.');
            } else if (file.size === 0) {
                errores.push('«' + file.name + '» está vacío.');
            } else {
                adjuntos.push({
                    file: file,
                    url: /^image\//.test(file.type) && file.type !== 'image/svg+xml' ? URL.createObjectURL(file) : null
                });
            }
        });
        if (errores.length) toast(errores.filter(function (v, i, a) { return a.indexOf(v) === i; }).join('\n'));
        renderAdjuntos();
        elInput.focus();
    }

    function quitarAdjunto(i) {
        var a = adjuntos.splice(i, 1)[0];
        if (a && a.url) URL.revokeObjectURL(a.url);
        renderAdjuntos();
    }

    function limpiarAdjuntos() {
        adjuntos.forEach(function (a) { if (a.url) URL.revokeObjectURL(a.url); });
        adjuntos = [];
        elComoDocumento.checked = false;
        renderAdjuntos();
    }

    function renderAdjuntos() {
        elAdjuntos.classList.toggle('d-none', adjuntos.length === 0);
        elAdjuntosLista.innerHTML = adjuntos.map(function (a, i) {
            var thumb = a.url
                ? '<img src="' + a.url + '" class="chat-adjunto-thumb" alt="">'
                : '<div class="chat-adjunto-thumb text-muted"><i data-lucide="' + iconoArchivo(a.file) + '" style="width:28px;height:28px;"></i></div>';
            return '<div class="chat-adjunto" title="' + escapeHtml(a.file.name) + '">' + thumb +
                '<button type="button" class="btn btn-danger chat-adjunto-quitar" data-quitar="' + i + '" title="Quitar">&times;</button>' +
                '<div class="fs-xxs text-truncate mt-1">' + escapeHtml(a.file.name) + '</div>' +
                '<div class="fs-xxs text-muted">' + tamanoLegible(a.file.size) + '</div>' +
            '</div>';
        }).join('');
        if (puedeEscribir()) {
            elInput.placeholder = adjuntos.length ? 'Añade un comentario (opcional)' : 'Escribe un mensaje';
        }
        icons();
    }

    elAdjuntarBtn.addEventListener('click', function () { elArchivoInput.click(); });
    elArchivoInput.addEventListener('change', function () {
        agregarAdjuntos(elArchivoInput.files);
        elArchivoInput.value = '';
    });
    elAdjuntosLista.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-quitar]');
        if (btn) quitarAdjunto(Number(btn.dataset.quitar));
    });

    // Arrastrar y soltar sobre la conversación
    var arrastres = 0;
    function conArchivos(e) {
        return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') !== -1;
    }
    elMain.addEventListener('dragenter', function (e) {
        if (!conArchivos(e) || !puedeEscribir()) return;
        e.preventDefault();
        arrastres++;
        elDropZone.classList.remove('d-none');
    });
    elMain.addEventListener('dragover', function (e) {
        if (conArchivos(e) && puedeEscribir()) e.preventDefault();
    });
    elMain.addEventListener('dragleave', function (e) {
        if (!conArchivos(e)) return;
        arrastres = Math.max(0, arrastres - 1);
        if (arrastres === 0) elDropZone.classList.add('d-none');
    });
    elMain.addEventListener('drop', function (e) {
        if (!conArchivos(e)) return;
        e.preventDefault();
        arrastres = 0;
        elDropZone.classList.add('d-none');
        agregarAdjuntos(e.dataTransfer.files);
    });

    // Pegar imágenes (Ctrl+V) en el campo de texto
    elInput.addEventListener('paste', function (e) {
        var files = (e.clipboardData && e.clipboardData.files) || [];
        if (files.length) {
            e.preventDefault();
            agregarAdjuntos(files);
        }
    });

    // ---------------------------------------------------------------- eventos

    elList.addEventListener('click', function (e) {
        if (e.target.closest('#chatIniciarNumero')) {
            abrirNuevoChat(numeroDeBusqueda());
            return;
        }
        var item = e.target.closest('.chat-contact-item');
        if (!item) return;
        selectChat(item.dataset.contactId, true);
    });

    elBackBtn.addEventListener('click', function () {
        elSidebar.classList.remove('chat-hide-mobile');
        elMain.classList.add('chat-hide-mobile');
    });

    elSendBtn.addEventListener('click', sendMessage);
    elInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    var searchTimer = null;
    elSearch.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            busqueda = elSearch.value.trim();
            cargarChats();
        }, 300);
    });

    elFilter.addEventListener('click', function (e) {
        var link = e.target.closest('[data-filtro]');
        if (!link) return;
        filtro = link.dataset.filtro;
        elFilter.querySelectorAll('[data-filtro]').forEach(function (l) { l.classList.toggle('active', l === link); });
        cargarChats();
    });

    if (elTransfer) {
        elTransfer.addEventListener('change', function () {
            if (activeId === null) return;
            var id = activeId;
            post(BASE + '/chats/' + id + '/transferir', { usuario_id: elTransfer.value })
                .then(function (data) {
                    if (id === activeId) { activeChat = data.chat; renderHeader(); }
                    cargarChats();
                })
                .catch(function (e) { toast(e.message); renderHeader(); });
        });
    }

    elCerrarBtn.addEventListener('click', function () {
        if (activeId === null) return;
        var id = activeId;
        post(BASE + '/chats/' + id + '/cerrar', {})
            .then(function (data) {
                if (id === activeId) { activeChat = data.chat; renderHeader(); }
                cargarChats();
            })
            .catch(function (e) { toast(e.message); });
    });

    elLecturasBtn.addEventListener('click', function () {
        if (activeId === null) return;
        api(BASE + '/chats/' + activeId + '/lecturas')
            .then(function (data) {
                var lista = (data.lecturas || []).map(function (l) {
                    return (l.usuario_nombre || 'Usuario ' + l.usuario_id) + ' — ' + fechaCorta(l.leido_at) + ' ' + hora(l.leido_at);
                });
                var texto = lista.length ? lista.join('\n') : 'Nadie ha abierto este chat todavía.';
                if (window.Swal) {
                    window.Swal.fire({ title: 'Visto por', html: '<div style="white-space:pre-line;text-align:left;">' + escapeHtml(texto) + '</div>' });
                } else {
                    alert('Visto por:\n' + texto);
                }
            })
            .catch(function (e) { toast(e.message); });
    });

    // ---------------------------------------------------------------- nuevo chat

    var elNuevoModal = document.getElementById('chatNuevoModal');
    var nuevoModal = window.bootstrap ? new window.bootstrap.Modal(elNuevoModal) : null;
    var elNuevoNumero = document.getElementById('chatNuevoNumero');
    var elNuevoMensaje = document.getElementById('chatNuevoMensaje');
    var elNuevoCliente = document.getElementById('chatNuevoCliente');
    var elNuevoClientes = document.getElementById('chatNuevoClientes');
    var elNuevoError = document.getElementById('chatNuevoError');
    var elNuevoEnviar = document.getElementById('chatNuevoEnviar');
    var clientesTimer = null;

    function errorNuevo(msg) {
        elNuevoError.textContent = msg || '';
        elNuevoError.classList.toggle('d-none', !msg);
    }

    function abrirNuevoChat(numero) {
        elNuevoNumero.value = numero ? (numero.length === 9 ? '51' + numero : numero) : '';
        elNuevoMensaje.value = '';
        elNuevoCliente.value = '';
        elNuevoClientes.classList.add('d-none');
        errorNuevo('');
        if (nuevoModal) nuevoModal.show();
        setTimeout(function () { (numero ? elNuevoMensaje : elNuevoNumero).focus(); }, 300);
    }

    function buscarClientesNuevo() {
        var q = elNuevoCliente.value.trim();
        if (q.length < 2) {
            elNuevoClientes.classList.add('d-none');
            return;
        }
        api(BASE + '/clientes?q=' + encodeURIComponent(q))
            .then(function (data) {
                var lista = data.clientes || [];
                elNuevoClientes.innerHTML = lista.length
                    ? lista.map(function (c) {
                        return '<a href="javascript:void(0);" class="list-group-item list-group-item-action py-2" data-telefono="' + escapeHtml(c.telefono) + '">' +
                            '<div class="fw-semibold fs-sm">' + escapeHtml(c.razon_social) + '</div>' +
                            '<div class="fs-xs text-muted">' + escapeHtml(c.ruc || '') + ' · ' + escapeHtml(c.telefono) + '</div></a>';
                    }).join('')
                    : '<div class="list-group-item fs-sm text-muted">Sin clientes con teléfono</div>';
                elNuevoClientes.classList.remove('d-none');
            })
            .catch(function (e) { console.error('clientes', e); });
    }

    function enviarNuevoChat() {
        var numero = elNuevoNumero.value.replace(/\D/g, '');
        var mensaje = elNuevoMensaje.value.trim();
        if (numero.length < 9) return errorNuevo('Ingresa un número válido.');
        if (!mensaje) return errorNuevo('Escribe el primer mensaje.');

        errorNuevo('');
        elNuevoEnviar.disabled = true;
        post(BASE + '/chats/nuevo', { numero: numero, mensaje: mensaje })
            .then(function (data) {
                if (nuevoModal) nuevoModal.hide();
                elSearch.value = '';
                busqueda = '';
                if (filtro === 'sin_asignar') {
                    filtro = 'todos';
                    elFilter.querySelectorAll('[data-filtro]').forEach(function (l) { l.classList.toggle('active', l.dataset.filtro === 'todos'); });
                }
                return cargarChats().then(function () { selectChat(data.chat_id, true); });
            })
            .catch(function (e) { errorNuevo(e.message); })
            .finally(function () { elNuevoEnviar.disabled = false; });
    }

    document.getElementById('chatNuevoBtn').addEventListener('click', function () {
        abrirNuevoChat(numeroDeBusqueda());
    });
    elNuevoEnviar.addEventListener('click', enviarNuevoChat);
    elNuevoMensaje.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarNuevoChat();
        }
    });
    elNuevoCliente.addEventListener('input', function () {
        clearTimeout(clientesTimer);
        clientesTimer = setTimeout(buscarClientesNuevo, 300);
    });
    elNuevoClientes.addEventListener('click', function (e) {
        var item = e.target.closest('[data-telefono]');
        if (!item) return;
        elNuevoNumero.value = item.dataset.telefono.replace(/\D/g, '');
        elNuevoCliente.value = item.querySelector('.fw-semibold').textContent;
        elNuevoClientes.classList.add('d-none');
        elNuevoMensaje.focus();
    });

    // ---------------------------------------------------------------- inicio y polling

    cargarChats();

    setInterval(function () {
        if (document.hidden) return;
        cargarChats();
        if (activeId !== null) cargarMensajes(false);
    }, POLL_MS);
})();
