(function () {
    'use strict';

    var filtroPeriodo = document.getElementById('filtroPeriodo');

    var tabla = new DataTable('#tablaCobranza', {
        ajax: {
            url: '/reportes/cobranza/listar',
            data: function (d) { d.periodo = filtroPeriodo.value; }
        },
        responsive: true,
        order: [[1, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 },
            { data: 2, className: 'text-center' },
            { data: 3, className: 'text-end' },
            { data: 4, className: 'text-end' },
            { data: 5, className: 'text-end' },
            { data: 6, className: 'text-center' },
        ],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    filtroPeriodo.addEventListener('change', function () { tabla.ajax.reload(); });

    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });
})();
