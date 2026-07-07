(function () {
    'use strict';

    var filtroTabla = document.getElementById('filtroTabla');
    var filtroDesde = document.getElementById('filtroDesde');
    var filtroHasta = document.getElementById('filtroHasta');
    var btnFiltrar = document.getElementById('btnFiltrar');

    var tabla = new DataTable('#tablaLogs', {
        ajax: function (data, cb, s) {
            var params = new URLSearchParams();
            if (filtroTabla.value) params.set('tabla', filtroTabla.value);
            if (filtroDesde.value) params.set('desde', filtroDesde.value);
            if (filtroHasta.value) params.set('hasta', filtroHasta.value);
            fetch('/auditoria/logs/listar?' + params.toString())
                .then(function (r) { return r.json(); })
                .then(function (res) { cb(res); })
                .catch(function () { cb({ data: [] }); });
        },
        responsive: true,
        order: [[5, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 },
            { data: 1, className: 'text-center' },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, className: 'dt-diff-cell' },
            { data: 7, className: 'dt-diff-cell' },
        ],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    btnFiltrar.addEventListener('click', function () { tabla.ajax.reload(); });

    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });
})();
