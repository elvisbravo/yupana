(function () {
    'use strict';

    var tabla = new DataTable('#tablaReporteVentas', {
        ajax: '/reporte-ventas/listar',
        responsive: true,
        order: [[2, 'asc'], [0, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'Blf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar Excel',
                className: 'btn btn-sm btn-soft-success',
                title: null,
                exportOptions: { columns: [0] },
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    var headers = [
                        'Fecha', 'Ruc', 'Razon Social', 'TD', 'Nro Documento', 'Num Hasta',
                        'Monto Exportacion', 'Monto Afecto', 'Monto Exonerado', 'Monto Inafecto',
                        'IGV Exportacion', 'IGV Afecto', 'ISC', 'ICBPER', 'Otros', 'Total', 'TC',
                        'IdCuenta', 'Glosa', 'Fecha Ref NotCred', 'TD Ref NotCred',
                        'Nro Documento Ref NotCred', 'IdCentroCosto', 'IdMoneda'
                    ];

                    function xmlEsc(s) {
                        return (s || '').toString()
                            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    }

                    function makeRow(cells, rowNum) {
                        var xml = '<row r="' + rowNum + '">';
                        cells.forEach(function (val, i) {
                            var col = String.fromCharCode(65 + i);
                            xml += '<c r="' + col + rowNum + '" t="inlineStr"><is><t>' + xmlEsc(val) + '</t></is></c>';
                        });
                        return xml + '</row>';
                    }

                    $('sheetData row', sheet).remove();

                    var xml = makeRow(headers, 1);
                    tabla.rows().data().toArray().forEach(function (row, i) {
                        var f = (row[2] || '').split('-');
                        var fecha = f.length === 3 ? f[2] + '/' + f[1] + '/' + f[0] : (row[2] || '');
                        xml += makeRow([
                            fecha, row[4], row[3], row[11], row[0],
                            '',
                            '0.00', '0.00',
                            row[12],
                            '0.00', '0.00', '0.00', '0.00', '0.00', '0.00',
                            row[12],
                            '', '', '', '', '', '', '', ''
                        ], i + 2);
                    });

                    $('sheetData', sheet).append(xml);
                },
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
            { data: 11, visible: false },
            { data: 12, visible: false },
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
