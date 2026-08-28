(function () {
    'use strict';

    var filtroPeriodo = document.getElementById('filtroPeriodoWhatsapp');
    var titulo = document.getElementById('tituloRecordatorios');

    function actualizarTitulo() {
        var periodo = filtroPeriodo.value;
        var label = (typeof PERIODOS_LABELS_WHATSAPP !== 'undefined' && PERIODOS_LABELS_WHATSAPP[periodo]) ? PERIODOS_LABELS_WHATSAPP[periodo] : periodo;
        titulo.textContent = 'Recordatorios de Pago por WhatsApp — Período: ' + label;
    }

    var tabla = new DataTable('#tablaRecordatoriosWhatsapp', {
        ajax: function (data, cb) {
            var params = new URLSearchParams();
            if (filtroPeriodo.value) params.set('periodo', filtroPeriodo.value);
            fetch('/whatsapp-recordatorio-pagos/listar?' + params.toString())
                .then(function (r) { return r.json(); })
                .then(function (res) { cb(res); })
                .catch(function () { cb({ data: [] }); });
        },
        responsive: true,
        order: [[7, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 },
            { data: 1, render: function (data, type, row) {
                if (type !== 'display' || !data) return data;
                var p = data.split('-');
                return p.length === 3 ? (p[2] + '-' + p[1] + '-' + p[0]) : data;
            } },
            { data: 2 },
            { data: 3 },
            { data: 4, className: 'text-end' },
            { data: 5 },
            { data: 6, className: 'text-center', orderable: false },
            { data: 7 },
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

    actualizarTitulo();
})();
