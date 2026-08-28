(function () {
    'use strict';

    var filtroPeriodo = document.getElementById('filtroPeriodoCron');
    var titulo = document.getElementById('tituloGenerado');
    var montoTotalEl = document.getElementById('montoTotalGenerado');

    function actualizarTitulo() {
        var periodo = filtroPeriodo.value;
        var label = (typeof PERIODOS_LABELS !== 'undefined' && PERIODOS_LABELS[periodo]) ? PERIODOS_LABELS[periodo] : periodo;
        titulo.textContent = 'Facturas Generadas — Período: ' + label;
    }

    var SIMBOLOS_MONEDA = { PEN: 'S/', USD: '$' };

    function actualizarMontoTotal(rows) {
        var totales = {};
        (rows || []).forEach(function (row) {
            var moneda = row[9] || 'PEN';
            var monto = parseFloat(String(row[8]).replace(/,/g, '')) || 0;
            totales[moneda] = (totales[moneda] || 0) + monto;
        });

        var monedas = Object.keys(totales);
        if (monedas.length === 0) {
            montoTotalEl.textContent = 'S/ 0.00';
            return;
        }
        montoTotalEl.textContent = monedas.map(function (m) {
            var simbolo = SIMBOLOS_MONEDA[m] || m;
            return simbolo + ' ' + totales[m].toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }).join(' + ');
    }

    var tabla = new DataTable('#tablaGeneradas', {
        ajax: function (data, cb) {
            var params = new URLSearchParams({ origen: 'cron' });
            if (filtroPeriodo.value) params.set('periodo', filtroPeriodo.value);
            fetch('/comprobantes/ventas/listar?' + params.toString())
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    actualizarMontoTotal(res.data);
                    cb(res);
                })
                .catch(function () {
                    actualizarMontoTotal([]);
                    cb({ data: [] });
                });
        },
        responsive: true,
        order: [[2, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 },
            { data: 2, render: function (data, type, row) {
                if (type !== 'display' || !data) return data;
                var p = data.split('-');
                return p.length === 3 ? (p[2] + '-' + p[1] + '-' + p[0]) : data;
            } },
            { data: 3, render: function (data, type, row) {
                if (type !== 'display' || !data) return data;
                var p = data.split('-');
                return p.length === 2 ? (p[1] + '-' + p[0]) : data;
            } },
            { data: 4, render: function (data, type, row) {
                if (type !== 'display') return data;
                return data + '<br><small class="text-muted">' + (row[5] || '') + '</small>';
            } },
            { data: 8, className: 'text-end fw-bold' },
            { data: 13, orderable: false, className: 'text-end' },
        ],
        columnDefs: [
            { responsivePriority: 1, targets: [0, 5] },
            { responsivePriority: 2, targets: [4] },
        ],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    filtroPeriodo.addEventListener('change', function () {
        actualizarTitulo();
        tabla.ajax.reload();
    });

    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });

    document.querySelector('#tablaGeneradas tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.enviar-sunat');
        if (!btn) return;
        btn.disabled = true;
        fetch('/comprobantes/enviar-sunat/' + btn.getAttribute('data-id'), { method: 'POST' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                alert(res.mensaje || (res.success ? 'Enviado correctamente.' : 'No se pudo enviar.'));
                tabla.ajax.reload(null, false);
            })
            .catch(function () {
                alert('Error de conexión al enviar a SUNAT.');
                btn.disabled = false;
            });
    });

    actualizarTitulo();
})();
