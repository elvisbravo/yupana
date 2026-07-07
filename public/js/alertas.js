(function () {
    'use strict';

    var tipo = (document.querySelector('input[name="tipo"]') || {}).value || '';
    var listarMap = {
        'vencimiento_contrato':   '/alertas/vencimiento-contrato/listar',
        'vencimiento_documento':  '/alertas/vencimiento-documento/listar',
        'cobro_vencido':          '/alertas/cobro-vencido/listar',
        'declaracion_proxima':    '/alertas/declaracion/listar',
        'cumpleanos':             '/alertas/cumpleanos/listar',
    };

    var tabla = new DataTable('#tablaAlertas', {
        ajax: listarMap[tipo] || '',
        responsive: true,
        order: [[2, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 },
            { data: 6, orderable: false },
        ],
        columnDefs: [{ targets: 6, className: 'text-end' }],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    var form = document.getElementById('alertaForm');
    var modal = document.getElementById('alertaModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    document.querySelector('[data-bs-target="#alertaModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nueva Alerta';
        form.dataset.mode = 'crear';
    });

    document.querySelector('#tablaAlertas tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-alerta');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/alertas/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Alerta';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    function setupEstadoBtn(className, urlSuffix, confirmMsg, successMsg) {
        document.querySelector('#tablaAlertas tbody').addEventListener('click', function (e) {
            var btn = e.target.closest('.' + className);
            if (!btn) return;
            if (!confirm(confirmMsg)) return;
            var id = btn.getAttribute('data-id');
            fetch('/alertas/' + urlSuffix + '/' + id)
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                    else { alert(res.message); }
                });
        });
    }

    setupEstadoBtn('vista-alerta', 'vista', '¿Marcar esta alerta como vista?');
    setupEstadoBtn('resolver-alerta', 'resolver', '¿Resolver esta alerta?');
    setupEstadoBtn('descartar-alerta', 'descartar', '¿Descartar esta alerta?');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');
        var mode = form.dataset.mode || 'crear';
        var url = mode === 'editar' ? '/alertas/actualizar/' + form.dataset.editId : '/alertas/guardar';
        var fd = new FormData(form);
        if (!fd.get('cliente_id')) fd.delete('cliente_id');
        if (!fd.get('usuario_asignado_id')) fd.delete('usuario_asignado_id');
        if (!fd.get('mensaje')) fd.delete('mensaje');
        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { bsModal.hide(); tabla.ajax.reload(null, false); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { var msg = res.message || 'Error al guardar.'; if (res.errors) msg = Object.values(res.errors).join('<br>'); mostrarAlerta(msg, 'danger'); btnGuardar.disabled = false; spinner.classList.add('d-none'); }
            })
            .catch(function () { mostrarAlerta('Error de conexión.', 'danger'); btnGuardar.disabled = false; spinner.classList.add('d-none'); });
    });

    modal.addEventListener('hidden.bs.modal', function () { limpiarForm(); });
    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });

    function llenarForm(data) {
        document.getElementById('f_titulo').value = data.titulo || '';
        document.getElementById('f_cliente').value = data.cliente_id || '';
        document.getElementById('f_asignado').value = data.usuario_asignado_id || '';
        document.getElementById('f_fecha').value = data.fecha_alerta || '';
        document.getElementById('f_estado').value = data.estado || 'pendiente';
        document.getElementById('f_mensaje').value = data.mensaje || '';
    }

    function limpiarForm() {
        form.reset(); alertBox.innerHTML = ''; btnGuardar.disabled = false; spinner.classList.add('d-none');
        document.getElementById('f_estado').value = 'pendiente';
        delete form.dataset.mode; delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
