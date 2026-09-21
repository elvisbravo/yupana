(function () {
    'use strict';

    var tabla = new DataTable('#tablaTarifas', {
        ajax: '/tarifas/mensuales/listar',
        responsive: true,
        order: [[0, 'asc']],
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

    var form = document.getElementById('tarifaForm');
    var modal = document.getElementById('tarifaModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    var clienteSelect = document.getElementById('f_cliente');
    var clienteChoices;

    function initChoices(val, bloqueado) {
        if (clienteChoices) clienteChoices.destroy();
        clienteChoices = new Choices(clienteSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar cliente...',
            placeholder: true,
            noResultsText: 'Sin resultados',
            noChoicesText: 'Sin opciones',
            itemSelectText: '',
            position: 'bottom',
        });
        if (val) clienteChoices.setChoiceByValue(String(val));
        if (bloqueado) clienteChoices.disable(); else clienteChoices.enable();
    }

    initChoices();

    document.querySelector('[data-bs-target="#tarifaModal"]').addEventListener('click', function () {
        limpiarForm();
        modalTitle.textContent = 'Nueva Tarifa Mensual';
        form.dataset.mode = 'crear';
        initChoices();
    });

    var clienteModal = document.getElementById('tarifasClienteModal');
    var bsClienteModal = new bootstrap.Modal(clienteModal);
    var tcBody = document.getElementById('tc_tabla_body');
    var tcNombre = document.getElementById('tc_cliente_nombre');

    function cargarTarifasCliente(clienteId) {
        fetch('/tarifas/mensuales/cliente/' + clienteId)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.error) return alert(res.error);
                clienteModal.dataset.clienteId = clienteId;
                tcNombre.textContent = res.cliente;
                tcBody.innerHTML = res.tarifas.map(function (t) {
                    var acciones = '';
                    if (t.editable) {
                        acciones = '<button class="btn btn-sm btn-soft-info editar-tarifa" data-id="' + t.id + '" title="Editar">'
                            + '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                            + '<button class="btn btn-sm btn-soft-danger eliminar-tarifa" data-id="' + t.id + '" title="Eliminar">'
                            + '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
                    }
                    return '<tr>'
                        + '<td>' + t.periodo_inicio + '</td>'
                        + '<td>' + t.periodo_fin + '</td>'
                        + '<td>' + t.monto + '</td>'
                        + '<td>' + t.moneda + '</td>'
                        + '<td>' + t.motivo + '</td>'
                        + '<td>' + t.estado_html + '</td>'
                        + '<td class="text-end">' + acciones + '</td>'
                        + '</tr>';
                }).join('');
                bsClienteModal.show();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    document.querySelector('#tablaTarifas tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.ver-tarifas-cliente');
        if (!btn) return;
        cargarTarifasCliente(btn.getAttribute('data-cliente-id'));
    });

    tcBody.addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-tarifa');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/tarifas/mensuales/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    modalTitle.textContent = 'Editar Tarifa Mensual';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    initChoices(data.cliente_id, true);
                    bsClienteModal.hide();
                    bsModal.show();
                });
            return;
        }

        var delBtn = e.target.closest('.eliminar-tarifa');
        if (!delBtn) return;
        if (!confirm('¿Eliminar esta tarifa mensual?')) return;
        var delId = delBtn.getAttribute('data-id');
        fetch('/tarifas/mensuales/eliminar/' + delId)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    tabla.ajax.reload(null, false);
                    cargarTarifasCliente(clienteModal.dataset.clienteId);
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
        var url = mode === 'editar' ? '/tarifas/mensuales/actualizar/' + form.dataset.editId : '/tarifas/mensuales/guardar';
        var fd = new FormData(form);
        if (!fd.has('cliente_id')) fd.set('cliente_id', clienteSelect.value);
        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { bsModal.hide(); tabla.ajax.reload(null, false); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { var msg = res.message || 'Error al guardar.'; if (res.errors) msg = Object.values(res.errors).join('<br>'); mostrarAlerta(msg, 'danger'); btnGuardar.disabled = false; spinner.classList.add('d-none'); }
            })
            .catch(function () { mostrarAlerta('Error de conexión.', 'danger'); btnGuardar.disabled = false; spinner.classList.add('d-none'); });
    });

    modal.addEventListener('hidden.bs.modal', function () {
        var volverALista = form.dataset.mode === 'editar' && clienteModal.dataset.clienteId;
        var clienteId = clienteModal.dataset.clienteId;
        limpiarForm();
        if (volverALista) cargarTarifasCliente(clienteId);
    });
    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });

    function llenarForm(data) {
        document.getElementById('f_cliente').value = data.cliente_id || '';
        document.getElementById('f_monto').value = data.monto || '';
        document.getElementById('f_moneda').value = data.moneda || 'PEN';
        document.getElementById('f_fecha_inicio').value = (data.fecha_inicio || '').slice(0, 7);
        document.getElementById('f_fecha_fin').value = (data.fecha_fin || '').slice(0, 7);
        document.getElementById('f_motivo').value = data.motivo_cambio || '';
    }

    function limpiarForm() {
        if (clienteChoices) clienteChoices.destroy();
        form.reset(); alertBox.innerHTML = ''; btnGuardar.disabled = false; spinner.classList.add('d-none');
        document.getElementById('f_moneda').value = 'PEN';
        delete form.dataset.mode; delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
