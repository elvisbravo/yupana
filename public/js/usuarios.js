(function () {
    'use strict';

    var tabla = new DataTable('#tablaUsuarios', {
        ajax: '/usuarios/listar',
        responsive: true,
        order: [[0, 'asc']],
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
            { data: 6, orderable: false },
        ],
        columnDefs: [
            { targets: 6, className: 'text-end' },
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

    var form = document.getElementById('usuarioForm');
    var modal = document.getElementById('usuarioModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);
    var pwdLabel = document.getElementById('pwdRequired');
    var pwdHelp = document.getElementById('pwdHelp');

    document.querySelector('[data-bs-target="#usuarioModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nuevo Usuario';
        form.dataset.mode = 'crear';
        pwdLabel.style.display = 'inline';
        pwdHelp.style.display = 'none';
        document.getElementById('f_password').required = true;
    });

    document.querySelector('#tablaUsuarios tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-usuario');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/usuarios/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Usuario';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    pwdLabel.style.display = 'none';
                    pwdHelp.style.display = 'inline';
                    document.getElementById('f_password').required = false;
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaUsuarios tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-usuario');
        if (!btn) return;
        if (!confirm('¿Eliminar este usuario?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/usuarios/eliminar/' + id)
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
        var url = mode === 'editar' ? '/usuarios/actualizar/' + form.dataset.editId : '/usuarios/guardar';
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
        document.getElementById('usuarioId').value = data.id;
        document.getElementById('f_nombres').value = data.nombres || '';
        document.getElementById('f_apellidos').value = data.apellidos || '';
        document.getElementById('f_email').value = data.email || '';
        document.getElementById('f_dni').value = data.dni || '';
        document.getElementById('f_rol').value = data.rol_id || '';
        document.getElementById('f_telefono').value = data.telefono || '';
        document.getElementById('f_estado').value = data.estado || 'activo';
    }

    function limpiarForm() {
        form.reset();
        document.getElementById('f_estado').value = 'activo';
        alertBox.innerHTML = '';
        btnGuardar.disabled = false;
        spinner.classList.add('d-none');
        delete form.dataset.mode;
        delete form.dataset.editId;
        pwdLabel.style.display = 'inline';
        pwdHelp.style.display = 'none';
        document.getElementById('f_password').required = true;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
