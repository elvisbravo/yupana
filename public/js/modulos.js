(function () {
    'use strict';

    var tabla = new DataTable('#tablaModulos', {
        ajax: '/roles/modulos/listar',
        responsive: true,
        order: [[4, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6 },
            { data: 7, orderable: false },
        ],
        columnDefs: [
            { targets: 7, className: 'text-end' },
        ],
        language: {
            paginate: {
                first: '<i class="ti ti-chevrons-left"></i>',
                previous: '<i class="ti ti-chevron-left"></i>',
                next: '<i class="ti ti-chevron-right"></i>',
                last: '<i class="ti ti-chevrons-right"></i>'
            },
            search: 'Buscar:',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)',
            lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles',
            zeroRecords: 'No se encontraron registros'
        }
    });

    var form = document.getElementById('moduloForm');
    var modal = document.getElementById('moduloModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    document.querySelector('[data-bs-target="#moduloModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nuevo Módulo';
        form.dataset.mode = 'crear';
    });

    document.querySelector('#tablaModulos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-modulo');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/roles/modulos/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Módulo';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaModulos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-modulo');
        if (!btn) return;
        if (!confirm('¿Eliminar este módulo?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/roles/modulos/eliminar/' + id)
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

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');

        var mode = form.dataset.mode || 'crear';
        var url = mode === 'editar' ? '/roles/modulos/actualizar/' + form.dataset.editId : '/roles/modulos/guardar';
        var fd = new FormData(form);

        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    bsModal.hide();
                    tabla.ajax.reload(null, false);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    var msg = res.message || 'Error al guardar.';
                    if (res.errors) msg = Object.values(res.errors).join('<br>');
                    mostrarAlerta(msg, 'danger');
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

    modal.addEventListener('hidden.bs.modal', function () {
        limpiarForm();
    });

    tabla.on('draw', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    function llenarForm(data) {
        document.getElementById('moduloId').value = data.id;
        document.getElementById('f_codigo').value = data.codigo || '';
        document.getElementById('f_nombre').value = data.nombre || '';
        document.getElementById('f_icono').value = data.icono || '';
        document.getElementById('f_orden').value = data.orden || '';
        document.getElementById('f_ruta').value = data.ruta || '';
        document.getElementById('f_padre').value = data.padre_id || '';
        document.getElementById('f_descripcion').value = data.descripcion || '';
        document.getElementById('f_activo').checked = data.activo == 1;
    }

    function limpiarForm() {
        form.reset();
        document.getElementById('f_activo').checked = true;
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
