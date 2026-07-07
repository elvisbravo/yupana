(function () {
    'use strict';

    var tabla = new DataTable('#tablaMensajes', {
        ajax: '/mensajes/listar',
        responsive: true,
        order: [[1, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5, orderable: false },
        ],
        columnDefs: [{ targets: 5, className: 'text-end' }],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    var form = document.getElementById('mensajeForm');
    var modal = document.getElementById('mensajeModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    // Edit
    document.querySelector('#tablaMensajes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-mensaje');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/mensajes/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return alert(data.error);
                    limpiarForm();
                    document.getElementById('f_titulo').value = data.mensaje.titulo || '';
                    document.getElementById('f_contenido').value = data.mensaje.contenido || '';
                    document.getElementById('f_id').value = data.mensaje.id;
                    modalTitle.textContent = 'Editar Mensaje';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    // View envios
    document.querySelector('#tablaMensajes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.ver-envios');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/mensajes/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return alert(data.error);
                    var html = '<table class="table table-sm table-bordered mb-0">' +
                        '<thead class="table-light"><tr><th>Contacto</th><th>Teléfono</th><th>Cliente</th><th>RUC</th><th>Estado</th><th>Intentos</th><th>Fecha Envío</th></tr></thead><tbody>';
                    (data.envios || []).forEach(function (e) {
                        var estadoBadge = 'badge-soft-warning';
                        if (e.estado === 'enviado') estadoBadge = 'badge-soft-success';
                        else if (e.estado === 'rechazado') estadoBadge = 'badge-soft-danger';
                        var fecha = e.fecha_envio ? new Date(e.fecha_envio).toLocaleString('es-PE') : '—';
                        html += '<tr>' +
                            '<td>' + (e.nombre_contacto || '—') + '</td>' +
                            '<td>' + (e.numero_whatsapp || '—') + '</td>' +
                            '<td>' + (e.razon_social || '—') + '</td>' +
                            '<td>' + (e.ruc || '—') + '</td>' +
                            '<td><span class="badge ' + estadoBadge + '">' + (e.estado || '—') + '</span></td>' +
                            '<td>' + (e.intentos || 0) + '</td>' +
                            '<td>' + fecha + '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                    document.getElementById('enviosContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('enviosModal')).show();
                });
        }
    });

    // Delete
    document.querySelector('#tablaMensajes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-mensaje');
        if (!btn) return;
        if (!confirm('¿Desactivar este mensaje?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/mensajes/eliminar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    tabla.ajax.reload();
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    alert(res.message);
                }
            });
    });

    // Form submit (edit)
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');

        var mode = form.dataset.mode || 'editar';
        var url = '/mensajes/actualizar/' + form.dataset.editId;
        var fd = new FormData(form);

        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    bsModal.hide();
                    tabla.ajax.reload(null, false);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    mostrarAlerta(res.message || 'Error al guardar.', 'danger');
                    btnGuardar.disabled = false;
                    spinner.classList.add('d-none');
                }
            })
            .catch(function () {
                mostrarAlerta('Error de conexión.', 'danger');
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
            });
    });

    modal.addEventListener('hidden.bs.modal', function () { limpiarForm(); });
    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });

    function limpiarForm() {
        form.reset();
        alertBox.innerHTML = '';
        btnGuardar.disabled = false;
        spinner.classList.add('d-none');
        delete form.dataset.mode;
        delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
