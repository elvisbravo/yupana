(function () {
    'use strict';

    // ======================== TABLA CRONOGRAMA ========================
    var tabla = new DataTable('#tablaVencimientos', {
        ajax: {
            url: '/vencimientos/listar',
            data: function (d) {
                d.anio = document.getElementById('filtroAnio').value;
            }
        },
        responsive: true,
        order: [[0, 'desc'], [1, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
            { data: 4, orderable: false },
        ],
        columnDefs: [{ targets: 5, className: 'text-end' }],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    document.getElementById('filtroAnio').addEventListener('change', function () {
        tabla.ajax.reload();
    });

    tabla.on('draw', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // Delete vencimiento
    document.querySelector('#tablaVencimientos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-vencimiento');
        if (!btn) return;
        if (!confirm('¿Eliminar este registro?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/vencimientos/eliminar/' + id)
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

    // ======================== GUARDAR CRONOGRAMA ========================
    var cronogramaForm = document.getElementById('cronogramaForm');
    var btnGuardarCrono = document.getElementById('btnGuardarCronograma');
    var spinnerCrono = btnGuardarCrono.querySelector('.spinner-border');

    cronogramaForm.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardarCrono.disabled = true;
        spinnerCrono.classList.remove('d-none');

        var fd = new FormData(cronogramaForm);
        fetch('/vencimientos/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    document.getElementById('formAlertCargar').innerHTML =
                        '<div class="alert alert-success alert-dismissible fade show py-2" role="alert">' + res.message +
                        '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                    cargarDiasExistentes();
                    tabla.ajax.reload();
                    // Refresh year filter
                    var anio = document.getElementById('f_anio').value;
                    var sel = document.getElementById('filtroAnio');
                    if (!sel.querySelector('option[value="' + anio + '"]')) {
                        var opt = document.createElement('option');
                        opt.value = anio;
                        opt.textContent = anio;
                        sel.appendChild(opt);
                    }
                } else {
                    document.getElementById('formAlertCargar').innerHTML =
                        '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">' + (res.message || 'Error') +
                        '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                }
                btnGuardarCrono.disabled = false;
                spinnerCrono.classList.add('d-none');
            })
            .catch(function () {
                document.getElementById('formAlertCargar').innerHTML =
                    '<div class="alert alert-danger py-2">Error de conexión.</div>';
                btnGuardarCrono.disabled = false;
                spinnerCrono.classList.add('d-none');
            });
    });

    // Pre-fill existing data when year changes
    function cargarDiasExistentes() {
        var anio = document.getElementById('f_anio').value;
        if (!anio) return;

        // Clear all day inputs
        document.querySelectorAll('input[name^="dias["]').forEach(function (inp) {
            inp.value = '';
        });

        fetch('/vencimientos/listar?anio=' + anio)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                (res.data || []).forEach(function (row) {
                    // row: [anio, periodo, digito, fecha, acciones, id]
                    var mesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'];
                    var mesIdx = mesNombres.indexOf(row[1].split(' ')[0]);
                    if (mesIdx < 1 || mesIdx > 12) return;
                    var digito = row[2];
                    var partes = row[3].split('/');
                    var dia = parseInt(partes[0], 10);
                    if (!dia) return;
                    var input = document.querySelector('input[name="dias[' + mesIdx + '][' + digito + ']"]');
                    if (input) input.value = dia;
                });
            });
    }

    document.getElementById('f_anio').addEventListener('change', cargarDiasExistentes);

    // Also pre-fill on page load after a small delay (DataTable may need time)
    setTimeout(cargarDiasExistentes, 300);
    // ======================== CONSULTAR CLIENTE ========================
    var btnConsultar = document.getElementById('btnConsultar');
    var resultadoConsulta = document.getElementById('resultadoConsulta');
    var infoCliente = document.getElementById('infoCliente');
    var consultaBody = document.getElementById('consultaBody');

    btnConsultar.addEventListener('click', function () {
        var clienteId = document.getElementById('f_cliente_consulta').value;
        var anio = document.getElementById('f_anio_consulta').value;

        if (!clienteId) {
            alert('Seleccione un cliente.');
            return;
        }

        fetch('/vencimientos/consultar?cliente_id=' + clienteId + '&anio=' + anio)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    alert(data.error);
                    resultadoConsulta.style.display = 'none';
                    return;
                }
                infoCliente.innerHTML = '<strong>' + data.cliente + '</strong> (RUC: ' + data.ruc +
                    ') — Último dígito: <strong>' + data.ultimo_digito + '</strong> | ' +
                    'Balance: <strong>' + data.presenta_balance + '</strong>';

                consultaBody.innerHTML = '';
                if (data.vencimientos && data.vencimientos.length) {
                    data.vencimientos.forEach(function (v) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + v[0] + '</td><td>' + v[1] + '</td><td>' + v[2] + '</td>';
                        consultaBody.appendChild(tr);
                    });
                } else {
                    consultaBody.innerHTML = '<tr><td colspan="3" class="text-muted text-center">No hay vencimientos registrados para este período.</td></tr>';
                }
                resultadoConsulta.style.display = '';
            });
    });
})();
