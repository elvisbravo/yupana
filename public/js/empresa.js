(function () {
    'use strict';

    // ===== Empresa Form =====
    var empresaForm = document.getElementById('empresaForm');
    var btnEmpresa = document.getElementById('btnGuardarEmpresa');
    var empresaSpinner = btnEmpresa.querySelector('.spinner-border');
    var empresaAlert = document.getElementById('empresaAlert');

    // Search RUC via API
    document.getElementById('btnBuscarRucEmpresa').addEventListener('click', function () {
        var ruc = document.getElementById('f_empresa_ruc').value.trim();
        if (ruc.length !== 11) {
            empresaAlert.innerHTML = '<div class="alert alert-warning alert-dismissible fade show py-2" role="alert">Ingrese un RUC de 11 dígitos.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
            return;
        }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Buscando...';

        fetch('/consultas/api/ruc/' + ruc)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.encontrado && res.data) {
                    var d = res.data;
                    document.getElementById('f_empresa_razon_social').value = d.razon_social || d.nombre || '';
                    document.getElementById('f_empresa_nombre_comercial').value = d.nombre_comercial || '';
                    document.getElementById('f_empresa_direccion').value = d.direccion || '';
                    empresaAlert.innerHTML = '<div class="alert alert-success alert-dismissible fade show py-2" role="alert">RUC encontrado.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                } else {
                    empresaAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">' + (res.mensaje || 'No se encontraron datos.') + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                }
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="search" style="width:14px;height:14px;"></i>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(function () {
                empresaAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">Error de conexión con la API.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="search" style="width:14px;height:14px;"></i>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    });

    empresaForm.addEventListener('submit', function (e) {
        e.preventDefault();
        btnEmpresa.disabled = true;
        empresaSpinner.classList.remove('d-none');

        var fd = new FormData(empresaForm);

        fetch('/empresa/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    empresaAlert.innerHTML = '<div class="alert alert-success alert-dismissible fade show py-2" role="alert">'
                        + res.message + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                } else {
                    empresaAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">'
                        + (res.message || 'Error al guardar.') + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                }
                btnEmpresa.disabled = false;
                empresaSpinner.classList.add('d-none');
            })
            .catch(function () {
                empresaAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">Error de conexión.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                btnEmpresa.disabled = false;
                empresaSpinner.classList.add('d-none');
            });
    });

    // ===== Sedes DataTable =====
    var tablaSedes = new DataTable('#tablaSedes', {
        ajax: '/empresa/sedes/listar',
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, orderable: false },
        ],
        columnDefs: [
            { targets: 6, className: 'text-end' },
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

    // ===== Sede Modal =====
    var sedeForm = document.getElementById('sedeForm');
    var sedeModal = document.getElementById('sedeModal');
    var sedeModalTitle = sedeModal.querySelector('.modal-title');
    var btnSede = document.getElementById('btnGuardarSede');
    var sedeSpinner = btnSede.querySelector('.spinner-border');
    var sedeAlert = document.getElementById('sedeAlert');
    var bsSedeModal = new bootstrap.Modal(sedeModal);

    document.querySelector('[data-bs-target="#sedeModal"]').addEventListener('click', function () {
        sedeForm.reset();
        sedeAlert.innerHTML = '';
        sedeModalTitle.textContent = 'Nueva Sede';
        delete sedeForm.dataset.editId;
    });

    document.querySelector('#tablaSedes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-sede');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/empresa/sedes/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return;
                    sedeForm.reset();
                    sedeAlert.innerHTML = '';
                    sedeModalTitle.textContent = 'Editar Sede';
                    sedeForm.dataset.editId = id;
                    document.getElementById('sedeId').value = data.id;
                    document.getElementById('f_sede_nombre').value = data.nombre || '';
                    document.getElementById('f_sede_direccion').value = data.direccion || '';
                    document.getElementById('f_sede_anexo').value = data.anexo || '';
                    document.getElementById('f_sede_telefono').value = data.telefono || '';
                    document.getElementById('f_sede_correo').value = data.correo || '';
                    document.getElementById('f_sede_envio').value = data.tipo_envio || 'prueba';
                    bsSedeModal.show();
                });
        }
    });

    document.querySelector('#tablaSedes tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-sede');
        if (!btn) return;
        if (!confirm('¿Eliminar esta sede?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/empresa/sedes/eliminar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    tablaSedes.ajax.reload();
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
    });

    sedeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSede.disabled = true;
        sedeSpinner.classList.remove('d-none');

        var mode = sedeForm.dataset.editId ? 'editar' : 'crear';
        var url = mode === 'editar' ? '/empresa/sedes/actualizar/' + sedeForm.dataset.editId : '/empresa/sedes/guardar';
        var fd = new FormData(sedeForm);

        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    bsSedeModal.hide();
                    tablaSedes.ajax.reload(null, false);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    sedeAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">'
                        + (res.message || 'Error al guardar.') + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                    btnSede.disabled = false;
                    sedeSpinner.classList.add('d-none');
                }
            })
            .catch(function () {
                sedeAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">Error de conexión.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                btnSede.disabled = false;
                sedeSpinner.classList.add('d-none');
            });
    });

    sedeModal.addEventListener('hidden.bs.modal', function () {
        sedeForm.reset();
        sedeAlert.innerHTML = '';
        btnSede.disabled = false;
        sedeSpinner.classList.add('d-none');
        delete sedeForm.dataset.editId;
    });

    tablaSedes.on('draw', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // ===== Correlativos DataTable =====
    var tablaCorrelativos = new DataTable('#tablaCorrelativos', {
        ajax: '/empresa/correlativos/listar',
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'d-md-flex justify-content-between align-items-center my-2'lf>rt<'d-md-flex justify-content-between align-items-center mt-2'ip>",
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, orderable: false },
        ],
        columnDefs: [
            { targets: 6, className: 'text-end' },
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

    // ===== Correlativo Modal =====
    var corrForm = document.getElementById('correlativoForm');
    var corrModal = document.getElementById('correlativoModal');
    var corrModalTitle = corrModal.querySelector('.modal-title');
    var btnCorr = document.getElementById('btnGuardarCorrelativo');
    var corrSpinner = btnCorr.querySelector('.spinner-border');
    var corrAlert = document.getElementById('correlativoAlert');
    var bsCorrModal = new bootstrap.Modal(corrModal);

    document.querySelector('[data-bs-target="#correlativoModal"]').addEventListener('click', function () {
        corrForm.reset();
        corrAlert.innerHTML = '';
        corrModalTitle.textContent = 'Nuevo Correlativo';
        delete corrForm.dataset.editId;
        document.getElementById('f_corr_inicio').value = 1;
        document.getElementById('f_corr_actual').value = 1;
    });

    document.querySelector('#tablaCorrelativos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.editar-correlativo');
        if (btn) {
            var id = btn.getAttribute('data-id');
            fetch('/empresa/correlativos/obtener/' + id)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return;
                    corrForm.reset();
                    corrAlert.innerHTML = '';
                    corrModalTitle.textContent = 'Editar Correlativo';
                    corrForm.dataset.editId = id;
                    document.getElementById('correlativoId').value = data.id;
                    document.getElementById('f_corr_sede').value = data.sede_id;
                    document.getElementById('f_corr_comprobante').value = data.tipo_comprobante_id;
                    document.getElementById('f_corr_serie').value = data.serie || '';
                    document.getElementById('f_corr_inicio').value = data.correlativo_inicio;
                    document.getElementById('f_corr_actual').value = data.correlativo_actual;
                    document.getElementById('f_corr_envio').value = data.tipo_envio || 'produccion';
                    bsCorrModal.show();
                });
        }
    });

    document.querySelector('#tablaCorrelativos tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.eliminar-correlativo');
        if (!btn) return;
        if (!confirm('¿Eliminar este correlativo?')) return;
        var id = btn.getAttribute('data-id');
        fetch('/empresa/correlativos/eliminar/' + id)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    tablaCorrelativos.ajax.reload();
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
    });

    corrForm.addEventListener('submit', function (e) {
        e.preventDefault();
        btnCorr.disabled = true;
        corrSpinner.classList.remove('d-none');

        var mode = corrForm.dataset.editId ? 'editar' : 'crear';
        var url = mode === 'editar' ? '/empresa/correlativos/actualizar/' + corrForm.dataset.editId : '/empresa/correlativos/guardar';
        var fd = new FormData(corrForm);

        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    bsCorrModal.hide();
                    tablaCorrelativos.ajax.reload(null, false);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    corrAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">'
                        + (res.message || 'Error al guardar.') + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                    btnCorr.disabled = false;
                    corrSpinner.classList.add('d-none');
                }
            })
            .catch(function () {
                corrAlert.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">Error de conexión.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                btnCorr.disabled = false;
                corrSpinner.classList.add('d-none');
            });
    });

    corrModal.addEventListener('hidden.bs.modal', function () {
        corrForm.reset();
        corrAlert.innerHTML = '';
        btnCorr.disabled = false;
        corrSpinner.classList.add('d-none');
        delete corrForm.dataset.editId;
    });

    tablaCorrelativos.on('draw', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
})();
