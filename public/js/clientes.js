(function () {
    'use strict';

    var tabla = new DataTable('#tablaClientes', {
        ajax: '/clientes/listar',
        responsive: true,
        order: [[0, 'desc']],
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

    var form = document.getElementById('clienteForm');
    var modal = document.getElementById('clienteModal');
    var modalTitle = document.getElementById('modalTitle');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var bsModal = new bootstrap.Modal(modal);

    // Searchable ubigeo select
    var ubigeoSelect = document.getElementById('f_ubigeo');
    var ubigeoChoices;

    function initChoices(val) {
        if (ubigeoChoices) ubigeoChoices.destroy();
        ubigeoChoices = new Choices(ubigeoSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar ubigeo...',
            placeholder: true,
            noResultsText: 'Sin resultados',
            noChoicesText: 'Sin opciones',
            itemSelectText: '',
            position: 'bottom',
        });
        if (val) ubigeoChoices.setChoiceByValue(String(val));
    }

    initChoices();

    // Search RUC via API
    var btnBuscarRuc = document.getElementById('btnBuscarRuc');
    btnBuscarRuc.addEventListener('click', function () {
        var ruc = document.getElementById('f_ruc').value.trim();
        if (ruc.length !== 11) {
            mostrarAlerta('Ingrese un RUC de 11 dígitos.', 'warning');
            return;
        }

        btnBuscarRuc.disabled = true;
        btnBuscarRuc.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Buscando...';

        fetch('/consultas/api/ruc/' + ruc)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.encontrado && res.data) {
                    var d = res.data;
                    document.getElementById('f_razon_social').value = d.razon_social || d.nombre || '';
                    document.getElementById('f_nombre_comercial').value = d.nombre_comercial || '';
                    document.getElementById('f_direccion').value = d.direccion || '';
                    if (res.ubigeo_id) {
                        if (ubigeoChoices) ubigeoChoices.destroy();
                        document.getElementById('f_ubigeo').value = res.ubigeo_id;
                        initChoices(res.ubigeo_id);
                    }
                    mostrarAlerta('RUC encontrado.', 'success');
                } else {
                    mostrarAlerta(res.mensaje || 'No se encontraron datos.', 'danger');
                }
                btnBuscarRuc.disabled = false;
                btnBuscarRuc.innerHTML = '<i data-lucide="search" style="width:14px;height:14px;"></i>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(function () {
                mostrarAlerta('Error de conexión con la API.', 'danger');
                btnBuscarRuc.disabled = false;
                btnBuscarRuc.innerHTML = '<i data-lucide="search" style="width:14px;height:14px;"></i>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    });

    document.querySelector('[data-bs-target="#clienteModal"]').addEventListener('click', function () {
        limpiarForm();
        var hoy = new Date().toISOString().slice(0, 10);
        document.getElementById('f_fecha_alta').value = hoy;
        document.getElementById('f_tarifa_fecha_inicio').value = hoy;
        document.getElementById('tarifaSection').style.display = '';
        document.getElementById('camposTributarios').style.display = '';
        modalTitle.textContent = 'Nuevo Cliente';
        form.dataset.mode = 'crear';
        initChoices();
    });

    document.querySelector('#tablaClientes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-cliente');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/clientes/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return mostrarAlerta(data.error, 'danger');
                    limpiarForm();
                    llenarForm(data);
                    document.getElementById('tarifaSection').style.display = 'none';
                    document.getElementById('camposTributarios').style.display = 'none';
                    modalTitle.textContent = 'Editar Cliente';
                    form.dataset.mode = 'editar';
                    form.dataset.editId = id;
                    initChoices(data.ubigeo_id);
                    bsModal.show();
                });
        }
    });

    document.querySelector('#tablaClientes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-cliente');
        if (!btn) return;
        if (!confirm('¿Eliminar este cliente?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/clientes/eliminar/' + id)
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
        var url = mode === 'editar' ? '/clientes/actualizar/' + form.dataset.editId : '/clientes/guardar';
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
        document.getElementById('clienteId').value = data.id;
        document.getElementById('f_ruc').value = data.ruc || '';
        document.getElementById('f_razon_social').value = data.razon_social || '';
        document.getElementById('f_nombre_comercial').value = data.nombre_comercial || '';
        document.getElementById('f_email').value = data.email || '';
        document.getElementById('f_telefono').value = data.telefono || '';
        document.getElementById('f_direccion').value = data.direccion || '';
        document.getElementById('f_referencia').value = data.referencia || '';
        document.getElementById('f_ubigeo').value = data.ubigeo_id || '';
        document.getElementById('f_observaciones').value = data.observaciones || '';
    }

    function limpiarForm() {
        if (ubigeoChoices) ubigeoChoices.destroy();
        form.reset();
        alertBox.innerHTML = '';
        btnGuardar.disabled = false;
        spinner.classList.add('d-none');
        document.getElementById('tarifaSection').style.display = '';
        document.getElementById('camposTributarios').style.display = '';
        delete form.dataset.mode;
        delete form.dataset.editId;
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }

    // ===== Régimen Tributario Modal =====
    var regimenForm = document.getElementById('regimenForm');
    var regimenModal = document.getElementById('regimenModal');
    var btnGuardarRegimen = document.getElementById('btnGuardarRegimen');
    var regimenSpinner = btnGuardarRegimen.querySelector('.spinner-border');
    var regimenAlert = document.getElementById('regimenFormAlert');
    var bsRegimenModal = new bootstrap.Modal(regimenModal);

    function cargarHistorialRegimen(historial) {
        var tbody = document.getElementById('rf_historialBody');
        tbody.innerHTML = '';
        if (!historial || historial.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">Sin historial de cambios</td></tr>';
            return;
        }
        historial.forEach(function (h) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + (h.regimen_nombre || '—') + '</td>'
                + '<td>' + (h.fecha_inicio || '—') + '</td>'
                + '<td>' + (h.fecha_fin || '<span class="text-success">Vigente</span>') + '</td>'
                + '<td>' + (h.motivo || '—') + '</td>';
            tbody.appendChild(tr);
        });
    }

    document.querySelector('#tablaClientes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.regimen-cliente');
        if (!btn) return;
        var id = btn.getAttribute('data-id');

        fetch('/clientes/datos-tributarios/obtener/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.error) return mostrarAlertaRegimen(res.error, 'danger');

                var data = res.cliente;
                var historial = res.historial;

                regimenForm.reset();
                regimenAlert.innerHTML = '';
                regimenForm.dataset.editId = id;
                document.getElementById('rf_id').value = data.id;
                document.getElementById('rf_cliente_titulo').textContent = data.ruc + ' - ' + data.razon_social;

                var regimenSelect = document.getElementById('rf_regimen');
                var regimenNombre = '';
                for (var i = 0; i < regimenSelect.options.length; i++) {
                    if (regimenSelect.options[i].value == data.regimen_actual_id) {
                        regimenNombre = regimenSelect.options[i].text;
                        break;
                    }
                }
                document.getElementById('rf_regimen_actual').textContent = regimenNombre || '—';

                var tipos = { 'general': 'General', 'mype': 'MYPE', 'especial': 'Especial', 'amazonica': 'Amazónica', 'agrario': 'Agrario' };
                document.getElementById('rf_tipo_renta_actual').textContent = tipos[data.tipo_renta] || data.tipo_renta || '—';
                document.getElementById('rf_presenta_balance_actual').textContent = data.presenta_balance == 1 ? 'Sí' : 'No';

                if (historial && historial.length > 0) {
                    var vigente = historial.find(function (h) { return !h.fecha_fin; }) || historial[0];
                    document.getElementById('rf_regimen_desde').textContent = vigente.fecha_inicio || '—';
                } else {
                    document.getElementById('rf_regimen_desde').textContent = '—';
                }

                cargarHistorialRegimen(historial);

                document.getElementById('rf_regimen').value = data.regimen_actual_id || '';
                document.getElementById('rf_tipo_renta').value = data.tipo_renta || 'general';
                document.getElementById('rf_presenta_balance').checked = data.presenta_balance == 1;
                document.getElementById('rf_fecha_inicio').value = new Date().toISOString().slice(0, 10);

                bsRegimenModal.show();
            });
    });

    regimenForm.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardarRegimen.disabled = true;
        regimenSpinner.classList.remove('d-none');

        var fd = new FormData(regimenForm);
        fd.set('id', regimenForm.dataset.editId);

        fetch('/clientes/datos-tributarios/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    bsRegimenModal.hide();
                    tabla.ajax.reload(null, false);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    mostrarAlertaRegimen(res.message || 'Error al guardar.', 'danger');
                    btnGuardarRegimen.disabled = false;
                    regimenSpinner.classList.add('d-none');
                }
            })
            .catch(function () {
                mostrarAlertaRegimen('Error de conexión.', 'danger');
                btnGuardarRegimen.disabled = false;
                regimenSpinner.classList.add('d-none');
            });
    });

    regimenModal.addEventListener('hidden.bs.modal', function () {
        regimenForm.reset();
        regimenAlert.innerHTML = '';
        btnGuardarRegimen.disabled = false;
        regimenSpinner.classList.add('d-none');
        delete regimenForm.dataset.editId;
    });

    function mostrarAlertaRegimen(msg, tipo) {
        regimenAlert.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
