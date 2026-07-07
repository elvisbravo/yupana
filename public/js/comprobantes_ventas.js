(function () {
    'use strict';

    var filtroTipo = document.getElementById('filtroTipo');
    var filtroPeriodo = document.getElementById('filtroPeriodo');
    var filtroAnio = document.getElementById('filtroAnio');
    var btnFiltrar = document.getElementById('btnFiltrar');

    var tabla = new DataTable('#tablaVentas', {
        ajax: function (data, cb, s) {
            var params = new URLSearchParams();
            if (filtroTipo.value) params.set('tipo', filtroTipo.value);
            if (filtroPeriodo.value) params.set('periodo', filtroPeriodo.value);
            if (filtroAnio.value) params.set('anio', filtroAnio.value);
            fetch('/comprobantes/ventas/listar?' + params.toString())
                .then(function (r) { return r.json(); })
                .then(function (res) { cb(res); })
                .catch(function () { cb({ data: [] }); });
        },
        responsive: true,
        order: [[2, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 },
            { data: 2 }, { data: 3 },
            { data: 4 }, { data: 5 },
            { data: 6, className: 'text-end' },
            { data: 7, className: 'text-end' },
            { data: 8, className: 'text-end fw-bold' },
            { data: 9, className: 'text-center' },
            { data: 10 }, { data: 11 }, { data: 12 },
        ],
        columnDefs: [
            { responsivePriority: 1, targets: [0, 8] },
            { responsivePriority: 2, targets: [4] },
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
