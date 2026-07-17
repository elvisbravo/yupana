(function () {
    'use strict';

    var tabla = new DataTable('#tablaSolicitudes', {
        ajax: '/solicitudes/listar',
        responsive: true,
        order: [[4, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 },
            { data: 6, orderable: false },
        ],
        columnDefs: [
            { targets: 4, render: function (data) { if (!data) return '—'; var d = new Date(data); return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); } },
            { targets: 6, className: 'text-end' },
        ],
        language: {
            paginate: { first: '<i class="ti ti-chevrons-left"></i>', previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>', last: '<i class="ti ti-chevrons-right"></i>' },
            search: 'Buscar:', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Mostrando 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)', lengthMenu: 'Mostrar _MENU_ registros',
            emptyTable: 'No hay datos disponibles', zeroRecords: 'No se encontraron registros'
        }
    });

    var modal = document.getElementById('solicitudModal');
    var bsModal = new bootstrap.Modal(modal);

    document.querySelector('#tablaSolicitudes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.ver-solicitud');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        fetch('/solicitudes/obtener/' + id)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) return alert(data.error);
                document.getElementById('detalle_nombres').textContent = data.nombres || '';
                document.getElementById('detalle_apellidos').textContent = data.apellidos || '';
                document.getElementById('detalle_email').textContent = data.email || '';
                document.getElementById('detalle_telefono').textContent = data.telefono || '—';
                document.getElementById('detalle_servicio').textContent = data.servicio || '—';
                document.getElementById('detalle_mensaje').textContent = data.mensaje || '';
                document.getElementById('detalle_fecha').textContent = data.created_at || '';
                bsModal.show();
                tabla.ajax.reload(null, false);
            });
    });

    document.querySelector('#tablaSolicitudes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-solicitud');
        if (!btn) return;
        if (!confirm('¿Eliminar esta solicitud?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/solicitudes/eliminar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) { tabla.ajax.reload(); if (typeof lucide !== 'undefined') lucide.createIcons(); }
                else { alert(res.message); }
            });
    });

    tabla.on('draw', function () { if (typeof lucide !== 'undefined') lucide.createIcons(); });
})();
