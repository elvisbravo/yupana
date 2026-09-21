(function () {
    var contactos = JSON.parse(document.getElementById('chatContactosData').textContent || '[]');
    var conversaciones = JSON.parse(document.getElementById('chatConversacionesData').textContent || '{}');

    var contactosById = {};
    contactos.forEach(function (c) { contactosById[c.id] = c; });

    var activeId = contactos.length ? contactos[0].id : null;

    var elSidebar = document.getElementById('chatSidebar');
    var elMain = document.getElementById('chatMain');
    var elList = document.getElementById('chatContactList');
    var elMessages = document.getElementById('chatMessages');
    var elHeaderAvatar = document.getElementById('chatHeaderAvatar');
    var elHeaderName = document.getElementById('chatHeaderName');
    var elHeaderStatus = document.getElementById('chatHeaderStatus');
    var elInput = document.getElementById('chatMessageInput');
    var elSendBtn = document.getElementById('chatSendBtn');
    var elBackBtn = document.getElementById('chatBackBtn');
    var elSearch = document.getElementById('chatSearch');

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function bubbleHtml(msg) {
        var isOut = msg.tipo === 'saliente';
        var ticks = '';
        if (isOut) {
            var color = msg.leido ? 'color:#4fc3f7;' : '';
            ticks = '<i data-lucide="check-check" class="ms-1" style="width:14px;height:14px;vertical-align:-2px;' + color + '"></i>';
        }
        return '' +
            '<div class="d-flex ' + (isOut ? 'justify-content-end' : 'justify-content-start') + '">' +
                '<div class="chat-bubble ' + (isOut ? 'outgoing bg-primary-subtle' : 'incoming bg-light') + ' px-3 py-2">' +
                    '<div>' + escapeHtml(msg.texto) + '</div>' +
                    '<div class="fs-xxs text-muted text-end mt-1">' + escapeHtml(msg.hora) + ticks + '</div>' +
                '</div>' +
            '</div>';
    }

    function scrollToBottom() {
        var wrapper = elMessages.closest('[data-simplebar]');
        if (!wrapper) return;
        var scrollEl = wrapper.SimpleBar ? wrapper.SimpleBar.getScrollElement() : wrapper;
        if (scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight;
    }

    function renderMessages(id) {
        var msgs = conversaciones[id] || [];

        if (msgs.length === 0) {
            elMessages.className = 'h-100 d-flex flex-column align-items-center justify-content-center text-muted p-3';
            elMessages.innerHTML =
                '<i data-lucide="message-circle" style="width:42px;height:42px;" class="mb-2 opacity-50"></i>' +
                '<span class="fs-sm">No hay mensajes aún, escribe el primero</span>';
        } else {
            elMessages.className = 'p-3 d-flex flex-column gap-2';
            elMessages.innerHTML = msgs.map(bubbleHtml).join('');
        }

        if (window.lucide) window.lucide.createIcons();
        scrollToBottom();
    }

    function renderHeader(contact) {
        elHeaderAvatar.textContent = contact.iniciales;
        elHeaderAvatar.className = 'avatar-title bg-' + contact.color + '-subtle text-' + contact.color + ' rounded-circle fw-semibold';
        elHeaderName.textContent = contact.nombre;
        elHeaderStatus.textContent = contact.estado || contact.ultimaConexion || '';
    }

    function selectContact(id, userInitiated) {
        var contact = contactosById[id];
        if (!contact) return;

        activeId = contact.id;

        elList.querySelectorAll('.chat-contact-item').forEach(function (item) {
            item.classList.toggle('active', String(item.dataset.contactId) === String(id));
        });

        renderHeader(contact);
        renderMessages(activeId);

        if (userInitiated && window.matchMedia('(max-width: 767.98px)').matches) {
            elSidebar.classList.add('chat-hide-mobile');
            elMain.classList.remove('chat-hide-mobile');
        }
    }

    function sendMessage() {
        var text = elInput.value.trim();
        if (!text || activeId === null) return;

        if (!conversaciones[activeId]) conversaciones[activeId] = [];

        var now = new Date();
        var hora = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        conversaciones[activeId].push({ tipo: 'saliente', texto: text, hora: hora, leido: false });

        elInput.value = '';
        renderMessages(activeId);
    }

    elList.addEventListener('click', function (e) {
        var item = e.target.closest('.chat-contact-item');
        if (!item) return;
        selectContact(item.dataset.contactId, true);
    });

    elBackBtn.addEventListener('click', function () {
        elSidebar.classList.remove('chat-hide-mobile');
        elMain.classList.add('chat-hide-mobile');
    });

    elSendBtn.addEventListener('click', sendMessage);
    elInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });

    elSearch.addEventListener('input', function () {
        var term = elSearch.value.trim().toLowerCase();
        elList.querySelectorAll('.chat-contact-item').forEach(function (item) {
            var nameEl = item.querySelector('.fw-semibold');
            var name = nameEl ? nameEl.textContent.toLowerCase() : '';
            item.classList.toggle('d-none', term !== '' && name.indexOf(term) === -1);
        });
    });

    if (activeId !== null) {
        selectContact(activeId, false);
    }
})();
