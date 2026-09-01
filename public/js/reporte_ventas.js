(function () {
    'use strict';

    var tabla = new DataTable('#tablaReporteVentas', {
        ajax: '/reporte-ventas/listar',
        responsive: true,
        order: [[2, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'Blf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar Excel',
                className: 'btn btn-sm btn-soft-success',
                title: 'Reporte de Ventas',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] },
            },
        ],
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2, render: function (data, type, row) {
                if (type !== 'display' || !data) return data;
                var p = data.split('-');
                return p.length === 3 ? (p[2] + '-' + p[1] + '-' + p[0]) : data;
            } },
            { data: 3 },
            { data: 4, visible: false },
            { data: 5, visible: false },
            { data: 6, visible: false },
            { data: 7, visible: false, className: 'text-end' },
            { data: 8, visible: false, className: 'text-end' },
            { data: 9, className: 'text-end fw-bold' },
            { data: 10, className: 'text-center' },
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

    tabla.on('draw', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
})();
