(function () {
    'use strict';

    var tabla = new DataTable('#tablaPeriodos', {
        ajax: '/periodos/apertura/listar',
        responsive: true,
        order: [[1, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 }, { data: 6 },
            { data: 7, orderable: false },
        ],
        columnDefs: [{ targets: 7, className: 'text-end' }],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    var form = document.getElementById('periodoForm');
    var modal = document.getElementById('periodoModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    document.querySelector('[data-bs-target="#periodoModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nuevo Período Contable';
        form.dataset.mode = 'crear';
    });

    document.querySelector('#tablaPeriodos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-periodo');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/periodos/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Período';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaPeriodos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.cerrar-periodo');
        if (!btn) return;
        if (!confirm('¿Cerrar este período contable?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/periodos/cerrar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { alert(res.message); }
            });
    });

    document.querySelector('#tablaPeriodos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.presentar-periodo');
        if (!btn) return;
        if (!confirm('¿Marcar este período como presentado?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/periodos/presentar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { alert(res.message); }
            });
    });

    document.querySelector('#tablaPeriodos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-periodo');
        if (!btn) return;
        if (!confirm('¿Eliminar este período contable?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/periodos/eliminar/' + id)
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
        var url = mode === 'editar' ? '/periodos/actualizar/' + form.dataset.editId : '/periodos/guardar';
        var fd = new FormData(form);
        if (!fd.get('fecha_cierre')) fd.delete('fecha_cierre');
        if (!fd.get('numero_operaciones')) fd.delete('numero_operaciones');
        if (!fd.get('observaciones')) fd.delete('observaciones');
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
        document.getElementById('f_cliente').value = data.cliente_id || '';
        document.getElementById('f_anio').value = data.anio || '';
        document.getElementById('f_mes').value = data.mes || '';
        document.getElementById('f_estado').value = data.estado || 'abierto';
        document.getElementById('f_ops').value = data.numero_operaciones || '';
        document.getElementById('f_cierre').value = data.fecha_cierre || '';
        document.getElementById('f_observaciones').value = data.observaciones || '';
    }

    function limpiarForm() {
        form.reset(); alertBox.innerHTML = ''; btnGuardar.disabled = false; spinner.classList.add('d-none');
        document.getElementById('f_estado').value = 'abierto';
        delete form.dataset.mode; delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
