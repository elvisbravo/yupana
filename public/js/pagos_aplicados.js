(function () {
    'use strict';

    var tabla = new DataTable('#tablaPagos', {
        ajax: '/cobros/pagos/listar',
        responsive: true,
        order: [[3, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
            { data: 4, className: 'text-end' }, { data: 5 }, { data: 6 }, { data: 7 },
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

    var form = document.getElementById('pagoForm');
    var modal = document.getElementById('pagoModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    document.querySelector('[data-bs-target="#pagoModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nuevo Pago';
        form.dataset.mode = 'crear';
    });

    document.querySelector('#tablaPagos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-pago');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/cobros/pagos/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Pago';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaPagos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-pago');
        if (!btn) return;
        if (!confirm('¿Eliminar este pago?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/cobros/pagos/eliminar/' + id)
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
        var url = mode === 'editar' ? '/cobros/pagos/actualizar/' + form.dataset.editId : '/cobros/pagos/guardar';
        var fd = new FormData(form);
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
        document.getElementById('f_cobro').value = data.cobro_id || '';
        document.getElementById('f_metodo').value = data.metodo_pago_id || '';
        document.getElementById('f_fecha').value = data.fecha_pago || '';
        document.getElementById('f_monto').value = data.monto || '';
        document.getElementById('f_moneda').value = data.moneda || 'PEN';
        document.getElementById('f_operacion').value = data.numero_operacion || '';
        document.getElementById('f_banco').value = data.banco || '';
        document.getElementById('f_observaciones').value = data.observaciones || '';
    }

    function limpiarForm() {
        form.reset(); alertBox.innerHTML = ''; btnGuardar.disabled = false; spinner.classList.add('d-none');
        document.getElementById('f_moneda').value = 'PEN';
        delete form.dataset.mode; delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
