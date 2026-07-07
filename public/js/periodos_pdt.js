(function () {
    'use strict';

    var tabla = new DataTable('#tablaPeriodos', {
        ajax: '/periodos/pdt/listar',
        responsive: true,
        order: [[1, 'desc']],
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

    document.querySelector('#tablaPeriodos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.presentar-periodo');
        if (!btn) return;
        if (!confirm('¿Marcar este período como presentado?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/periodos/presentar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { alert(res.message); }
            });
    });

    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });
})();
