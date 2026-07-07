(function () {
    'use strict';

    var tabla = new DataTable('#tablaCobros', {
        ajax: '/cobros/mensuales/listar',
        responsive: true,
        order: [[3, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 },
            { data: 5, className: 'text-end' }, { data: 6, className: 'text-end' },
            { data: 7, className: 'text-end' }, { data: 8 }, { data: 9 },
            { data: 10, orderable: false },
        ],
        columnDefs: [{ targets: 10, className: 'text-end' }],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    var form = document.getElementById('cobroForm');
    var modal = document.getElementById('cobroModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);
    var selCliente = document.getElementById('f_cliente');
    var selServicio = document.getElementById('f_servicio');

    selCliente.addEventListener('change', function () {
        var cid = this.value;
        selServicio.innerHTML = '<option value="">Seleccionar...</option>';
        if (!cid) return;
        fetch('/cobros/mensuales/obtener-servicios?cliente_id=' + cid)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                data.forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.servicio_nombre;
                    selServicio.appendChild(opt);
                });
            });
    });

    document.querySelector('[data-bs-target="#cobroModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nuevo Cobro';
        form.dataset.mode = 'crear';
        selCliente.value = '';
        selServicio.innerHTML = '<option value="">Seleccionar...</option>';
    });

    document.querySelector('#tablaCobros tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-cobro');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/cobros/mensuales/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    selCliente.value = data.cliente_id || '';
                    selCliente.dispatchEvent(new Event('change'));
                    setTimeout(function () {
                        llenarForm(data);
                    }, 200);
                    modalTitle.textContent = 'Editar Cobro';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaCobros tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-cobro');
        if (!btn) return;
        if (!confirm('¿Anular este cobro?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/cobros/mensuales/eliminar/' + id)
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
        var url = mode === 'editar' ? '/cobros/mensuales/actualizar/' + form.dataset.editId : '/cobros/mensuales/guardar';
        var fd = new FormData(form);
        if (!fd.get('servicio_contratado_id')) fd.delete('servicio_contratado_id');
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
        document.getElementById('f_servicio').value = data.servicio_contratado_id || '';
        document.getElementById('f_periodo').value = data.periodo || '';
        document.getElementById('f_emision').value = data.fecha_emision || '';
        document.getElementById('f_vencimiento').value = data.fecha_vencimiento || '';
        document.getElementById('f_monto').value = data.monto || '';
        document.getElementById('f_moneda').value = data.moneda || 'PEN';
        document.getElementById('f_estado').value = data.estado || 'pendiente';
        document.getElementById('f_concepto').value = data.concepto || '';
        document.getElementById('f_observaciones').value = data.observaciones || '';
    }

    function limpiarForm() {
        form.reset(); alertBox.innerHTML = ''; btnGuardar.disabled = false; spinner.classList.add('d-none');
        document.getElementById('f_moneda').value = 'PEN';
        document.getElementById('f_estado').value = 'pendiente';
        delete form.dataset.mode; delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
