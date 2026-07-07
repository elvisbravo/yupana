(function () {
    'use strict';

    var selectRol = document.getElementById('selectRol');
    var container = document.getElementById('permisosContainer');
    var empty = document.getElementById('permisosEmpty');
    var tree = document.getElementById('permisosTree');
    var alertBox = document.getElementById('permisosAlert');
    var btnGuardar = document.getElementById('btnGuardarPermisos');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var btnExpandAll = document.getElementById('btnExpandAll');
    var btnCollapseAll = document.getElementById('btnCollapseAll');

    var permisosDisponibles = [];

    selectRol.addEventListener('change', function () {
        var rolId = this.value;
        if (!rolId) {
            container.classList.add('d-none');
            empty.classList.remove('d-none');
            return;
        }
        cargarPermisos(rolId);
    });

    function cargarPermisos(rolId) {
        fetch('/roles/permisos/obtener?rol_id=' + rolId)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                container.classList.remove('d-none');
                empty.classList.add('d-none');
                tree.innerHTML = '';
                alertBox.innerHTML = '';
                renderTree(data);
            });
    }

    function renderTree(data) {
        var padres = data.filter(function (m) { return !m.padre_id; });
        var hijos = {};
        data.forEach(function (m) {
            if (m.padre_id) {
                if (!hijos[m.padre_id]) hijos[m.padre_id] = [];
                hijos[m.padre_id].push(m);
            }
        });

        var html = '<div class="accordion" id="accordionPermisos">';

        padres.forEach(function (padre, idx) {
            var childs = hijos[padre.id] || [];
            var collapseId = 'collapse' + padre.id;
            var headingId = 'heading' + padre.id;

            html += '<div class="accordion-item">';
            html += '<h2 class="accordion-header" id="' + headingId + '">';
            html += '<button class="accordion-button' + (idx > 0 ? ' collapsed' : '') + '" type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '">';
            html += '<div class="d-flex align-items-center gap-2 w-100">';
            html += '<input type="checkbox" class="form-check-input modulo-checkbox" data-modulo-id="' + padre.id + '" id="mod_' + padre.id + '"' + (padre.asignado ? ' checked' : '') + '>';
            html += '<label class="form-check-label fw-semibold" for="mod_' + padre.id + '">';
            if (padre.icono) html += '<i data-lucide="' + padre.icono + '" style="width:16px;height:16px;" class="me-1"></i> ';
            html += padre.nombre + '</label>';
            html += '</div></button></h2>';

            html += '<div id="' + collapseId + '" class="accordion-collapse collapse' + (idx === 0 ? ' show' : '') + '" data-bs-parent="#accordionPermisos">';
            html += '<div class="accordion-body">';

            var todosPermisos = padre.permisos || {};

            if (childs.length > 0) {
                childs.forEach(function (child) {
                    var childPermisos = child.permisos || {};
                    html += '<div class="d-flex align-items-center gap-2 mb-1 ps-3 border-start border-2">';
                    html += '<input type="checkbox" class="form-check-input modulo-checkbox" data-modulo-id="' + child.id + '" id="mod_' + child.id + '"' + (child.asignado ? ' checked' : '') + '>';
                    html += '<label class="form-check-label fw-medium" for="mod_' + child.id + '">';
                    if (child.icono) html += '<i data-lucide="' + child.icono + '" style="width:14px;height:14px;" class="me-1"></i> ';
                    html += child.nombre + '</label>';
                    html += renderPermisosCheckboxes(child.id, childPermisos);
                    html += '</div>';
                });
            }

            html += renderPermisosCheckboxes(padre.id, todosPermisos);

            html += '</div></div></div>';
        });

        html += '</div>';
        tree.innerHTML = html;

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function renderPermisosCheckboxes(moduloId, selected) {
        var permisos = ['leer', 'crear', 'editar', 'eliminar', 'exportar', 'importar', 'aprobar', 'anular', 'cerrar', 'reimprimir'];
        var nombres = {
            leer: 'Leer', crear: 'Crear', editar: 'Editar', eliminar: 'Eliminar',
            exportar: 'Exportar', importar: 'Importar', aprobar: 'Aprobar',
            anular: 'Anular', cerrar: 'Cerrar', reimprimir: 'Reimprimir'
        };

        var permisosData = [];
        var ids = Object.keys(selected);
        ids.forEach(function (id) { permisosData.push(parseInt(id)); });

        // Map permiso IDs to codes
        var permisoMap = {
            1: 'leer', 2: 'crear', 3: 'editar', 4: 'eliminar',
            5: 'exportar', 6: 'importar', 7: 'aprobar',
            8: 'anular', 9: 'cerrar', 10: 'reimprimir'
        };

        var html = '<div class="d-flex flex-wrap gap-2 mt-1 ms-4">';
        permisos.forEach(function (p) {
            var pId = Object.keys(permisoMap).find(function (k) { return permisoMap[k] === p; });
            var checked = pId && permisosData.indexOf(parseInt(pId)) !== -1;
            html += '<div class="form-check form-check-inline">';
            html += '<input class="form-check-input permiso-checkbox" type="checkbox" data-modulo-id="' + moduloId + '" data-permiso-id="' + pId + '" id="perm_' + moduloId + '_' + p + '"' + (checked ? ' checked' : '') + '>';
            html += '<label class="form-check-label" for="perm_' + moduloId + '_' + p + '">' + nombres[p] + '</label>';
            html += '</div>';
        });
        html += '</div>';

        return html;
    }

    // Expand / Collapse all
    btnExpandAll.addEventListener('click', function () {
        document.querySelectorAll('#accordionPermisos .accordion-collapse').forEach(function (el) {
            el.classList.add('show');
        });
        document.querySelectorAll('#accordionPermisos .accordion-button').forEach(function (btn) {
            btn.classList.remove('collapsed');
        });
    });

    btnCollapseAll.addEventListener('click', function () {
        document.querySelectorAll('#accordionPermisos .accordion-collapse').forEach(function (el) {
            el.classList.remove('show');
        });
        document.querySelectorAll('#accordionPermisos .accordion-button').forEach(function (btn) {
            btn.classList.add('collapsed');
        });
    });

    // Save
    btnGuardar.addEventListener('click', function () {
        var rolId = selectRol.value;
        if (!rolId) {
            mostrarAlerta('Seleccione un rol.', 'warning');
            return;
        }

        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');
        alertBox.innerHTML = '';

        var modulos = {};
        document.querySelectorAll('.modulo-checkbox:checked').forEach(function (cb) {
            var mid = cb.getAttribute('data-modulo-id');
            modulos[mid] = { permisos: [] };
        });

        document.querySelectorAll('.permiso-checkbox:checked').forEach(function (cb) {
            var mid = cb.getAttribute('data-modulo-id');
            var pid = cb.getAttribute('data-permiso-id');
            if (modulos[mid]) {
                modulos[mid].permisos.push(pid);
            }
        });

        var fd = new FormData();
        fd.append('rol_id', rolId);
        fd.append('modulos', JSON.stringify(modulos));

        fetch('/roles/permisos/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    mostrarAlerta(res.message, 'success');
                    cargarPermisos(rolId);
                } else {
                    mostrarAlerta(res.message || 'Error al guardar.', 'danger');
                }
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
            })
            .catch(function () {
                mostrarAlerta('Error de conexión.', 'danger');
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
            });
    });

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
