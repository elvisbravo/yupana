(function () {
    'use strict';

    var filtro = (document.getElementById('filtroTareas') || {}).value || 'todas';
    var listarMap = {
        'todas': '/tareas/mias/listar',
        'asignadas': '/tareas/asignadas/listar',
        'creadas': '/tareas/creadas/listar',
    };

    var tabla = new DataTable('#tablaTareas', {
        ajax: listarMap[filtro] || '/tareas/mias/listar',
        responsive: true,
        order: [[6, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 },
            { data: 8, orderable: false },
        ],
        columnDefs: [{ targets: 8, className: 'text-end' }],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    var form = document.getElementById('tareaForm');
    var modal = document.getElementById('tareaModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    document.querySelector('[data-bs-target="#tareaModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nueva Tarea';
        form.dataset.mode = 'crear';
    });

    document.querySelector('#tablaTareas tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-tarea');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/tareas/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Tarea';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaTareas tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.completar-tarea');
        if (!btn) return;
        if (!confirm('¿Marcar esta tarea como completada?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/tareas/completar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { alert(res.message); }
            });
    });

    document.querySelector('#tablaTareas tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-tarea');
        if (!btn) return;
        if (!confirm('¿Cancelar esta tarea?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/tareas/eliminar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { alert(res.message); }
            });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');
        var mode = form.dataset.mode || 'crear';
        var url = mode === 'editar' ? '/tareas/actualizar/' + form.dataset.editId : '/tareas/guardar';
        var fd = new FormData(form);
        if (!fd.get('cliente_id')) fd.delete('cliente_id');
        if (!fd.get('asignado_a_id')) fd.delete('asignado_a_id');
        if (!fd.get('fecha_vencimiento')) fd.delete('fecha_vencimiento');
        if (!fd.get('descripcion')) fd.delete('descripcion');
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
        document.getElementById('f_prioridad').value = data.prioridad || 'media';
        document.getElementById('f_asignado').value = data.asignado_a_id || '';
        document.getElementById('f_vencimiento').value = data.fecha_vencimiento || '';
        document.getElementById('f_descripcion').value = data.descripcion || '';
    }

    function limpiarForm() {
        form.reset(); alertBox.innerHTML = ''; btnGuardar.disabled = false; spinner.classList.add('d-none');
        document.getElementById('f_prioridad').value = 'media';
        delete form.dataset.mode; delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
