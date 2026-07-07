(function () {
    'use strict';

    var form = document.getElementById('comprobanteForm');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');
    var itemsBody = document.getElementById('itemsBody');

    // Create initial item row
    agregarItem('', 1, 0);

    // Sede change: enable tipo dropdown, load correlativos info
    document.getElementById('f_sede').addEventListener('change', function () {
        var sedeId = this.value;
        var fTipo = document.getElementById('f_tipo');

        document.getElementById('f_serie').value = '';
        document.getElementById('f_numero').value = '';

        if (!sedeId) {
            fTipo.disabled = true;
            fTipo.innerHTML = '<option value="">Primero seleccione sede</option>';
            document.getElementById('f_tipo_envio').value = '';
            document.getElementById('f_tipo_envio_hidden').value = '';
            return;
        }

        var opt = this.options[this.selectedIndex];
        var envio = opt.getAttribute('data-envio') || 'prueba';
        document.getElementById('f_tipo_envio').value = envio.charAt(0).toUpperCase() + envio.slice(1);
        document.getElementById('f_tipo_envio_hidden').value = envio;

        fTipo.disabled = true;
        fTipo.innerHTML = '<option value="">Cargando...</option>';

        fetch('/comprobantes/tipos-por-sede?sede_id=' + sedeId)
            .then(function (r) { return r.json(); })
            .then(function (tipos) {
                fTipo.innerHTML = '<option value="">Seleccionar...</option>';
                if (tipos.length === 0) {
                    fTipo.innerHTML += '<option value="" disabled>No hay correlativos configurados</option>';
                    fTipo.disabled = true;
                    return;
                }
                tipos.forEach(function (t) {
                    fTipo.innerHTML += '<option value="' + t.id + '" data-abrev="' + t.abreviatura + '">' + t.abreviatura + ' - ' + t.nombre + '</option>';
                });
                fTipo.disabled = false;
            })
            .catch(function () {
                fTipo.innerHTML = '<option value="">Error al cargar</option>';
            });
    });

    // Tipo change: load correlativo
    document.getElementById('f_tipo').addEventListener('change', function () {
        var tipoId = this.value;
        var sedeId = document.getElementById('f_sede').value;
        document.getElementById('f_serie').value = '';
        document.getElementById('f_numero').value = '';
        if (!tipoId || !sedeId) return;

        fetch('/comprobantes/obtener-correlativo?sede_id=' + sedeId + '&tipo_comprobante_id=' + tipoId)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.length === 0) {
                    document.getElementById('f_serie').value = 'SIN CORRELATIVO';
                    return;
                }
                // Use the first correlativo (or could add a series dropdown)
                var corr = data[0];
                document.getElementById('f_serie').value = corr.serie || '';
                document.getElementById('f_numero').value = corr.correlativo_actual || 1;
            });
    });

    // Client search with dropdown
    var clienteTexto = document.getElementById('f_cliente_text');
    var clienteHidden = document.getElementById('f_cliente');
    var clienteInfo = document.getElementById('clienteInfo');
    var clienteDropdown = document.getElementById('clienteDropdown');

    clienteTexto.addEventListener('input', function () {
        var val = this.value.trim().toLowerCase();
        clienteHidden.value = '';

        if (!val) {
            clienteDropdown.style.display = 'none';
            clienteInfo.textContent = 'Escribe para buscar o agrega uno nuevo';
            document.getElementById('serviciosContainer').style.display = 'none';
            return;
        }

        var matches = CLIENTES.filter(function (c) {
            return c.razon_social.toLowerCase().indexOf(val) !== -1 || (c.ruc && c.ruc.indexOf(val) !== -1);
        });

        if (matches.length) {
            clienteDropdown.innerHTML = '';
            matches.forEach(function (c) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action py-1 px-2';
                item.innerHTML = '<strong>' + escHtml(c.razon_social) + '</strong> <small class="text-muted">' + (c.ruc || '') + '</small>';
                item.addEventListener('click', function () {
                    seleccionarCliente(c);
                });
                clienteDropdown.appendChild(item);
            });
            clienteDropdown.style.display = 'block';
        } else {
            clienteDropdown.style.display = 'none';
            clienteInfo.textContent = 'Cliente externo (no registrado)';
        }
    });

    function seleccionarCliente(c) {
        clienteTexto.value = c.razon_social;
        clienteHidden.value = c.id;
        clienteInfo.textContent = c.ruc ? 'RUC: ' + c.ruc : 'Cliente seleccionado';
        clienteDropdown.style.display = 'none';
        cargarServicios(c.id);
    }

    // Hide dropdown on click outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#f_cliente_text') && !e.target.closest('#clienteDropdown')) {
            clienteDropdown.style.display = 'none';
        }
    });

    // Enter key to select first match
    clienteTexto.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !clienteHidden.value) {
            e.preventDefault();
            var first = clienteDropdown.querySelector('.list-group-item');
            if (first) first.click();
        }
    });

    function cargarServicios(clienteId) {
        var container = document.getElementById('serviciosContainer');
        var lista = document.getElementById('serviciosLista');
        lista.innerHTML = '';
        if (!clienteId) { container.style.display = 'none'; return; }

        fetch('/comprobantes/obtener-cliente?id=' + clienteId)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) return;
                if (data.servicios && data.servicios.length) {
                    container.style.display = 'block';
                    data.servicios.forEach(function (s) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-sm btn-outline-primary';
                        btn.textContent = s.servicio + ' (S/ ' + (parseFloat(s.monto) || 0).toFixed(2) + ')';
                        btn.addEventListener('click', function () {
                            agregarItem(s.servicio, 1, parseFloat(s.monto) || 0);
                        });
                        lista.appendChild(btn);
                    });
                } else {
                    container.style.display = 'none';
                }
            });
    }

    // Quick-create client
    var nuevoClienteBtn = document.getElementById('btnNuevoCliente');
    var nuevoClienteModal = new bootstrap.Modal(document.getElementById('nuevoClienteModal'));
    var nuevoClienteForm = document.getElementById('nuevoClienteForm');
    var btnGuardarCliente = document.getElementById('btnGuardarCliente');
    var spinnerCliente = btnGuardarCliente.querySelector('.spinner-border');

    nuevoClienteBtn.addEventListener('click', function () {
        document.getElementById('clienteAlert').innerHTML = '';
        nuevoClienteForm.reset();
        nuevoClienteModal.show();
    });

    nuevoClienteForm.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardarCliente.disabled = true;
        spinnerCliente.classList.remove('d-none');

        var fd = new FormData(nuevoClienteForm);
        fetch('/comprobantes/guardar-cliente', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success && res.cliente) {
                    CLIENTES.push({ id: String(res.cliente.id), razon_social: res.cliente.razon_social, ruc: res.cliente.ruc || '' });
                    clienteTexto.value = res.cliente.razon_social;
                    clienteHidden.value = res.cliente.id;
                    clienteInfo.textContent = res.cliente.ruc ? 'RUC: ' + res.cliente.ruc : 'Cliente creado';
                    nuevoClienteModal.hide();
                    cargarServicios(res.cliente.id);
                } else {
                    var msg = res.message || 'Error al guardar.';
                    document.getElementById('clienteAlert').innerHTML = '<div class="alert alert-danger py-1 fs-xxs">' + msg + '</div>';
                }
                btnGuardarCliente.disabled = false;
                spinnerCliente.classList.add('d-none');
            })
            .catch(function () {
                document.getElementById('clienteAlert').innerHTML = '<div class="alert alert-danger py-1 fs-xxs">Error de conexión.</div>';
                btnGuardarCliente.disabled = false;
                spinnerCliente.classList.add('d-none');
            });
    });

    // Add item row
    document.getElementById('agregarItem').addEventListener('click', function () {
        agregarItem('', 1, 0);
    });

    function agregarItem(desc, cant, precio) {
        var tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td><input type="text" class="form-control form-control-sm item-desc" value="' + escHtml(desc) + '" placeholder="Descripción"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-cant" value="' + cant + '" min="1" step="1"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-precio" value="' + precio + '" min="0" step="0.01"></td>' +
            '<td class="item-total text-end fw-bold align-middle">' + (cant * precio).toFixed(2) + '</td>' +
            '<td class="text-center align-middle">' +
            '<button type="button" class="btn btn-sm btn-soft-danger eliminar-item"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>' +
            '</td>';
        itemsBody.appendChild(tr);
        calcular();
        bindItemEvents(tr);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function bindItemEvents(row) {
        var inputs = row.querySelectorAll('.item-cant, .item-precio');
        inputs.forEach(function (inp) {
            inp.addEventListener('input', function () { calcular(); });
        });
        row.querySelector('.eliminar-item').addEventListener('click', function () {
            row.remove();
            calcular();
        });
    }

    function calcular() {
        var subtotal = 0;
        var rows = itemsBody.querySelectorAll('.item-row');
        rows.forEach(function (row) {
            var cant = parseFloat(row.querySelector('.item-cant').value) || 0;
            var precio = parseFloat(row.querySelector('.item-precio').value) || 0;
            var total = cant * precio;
            row.querySelector('.item-total').textContent = total.toFixed(2);
            subtotal += total;
        });
        var tasaIgv = parseFloat(document.getElementById('f_tipo_igv').value) || 0;
        var igv = subtotal * (tasaIgv / 100);
        var total = subtotal + igv;
        document.getElementById('igvLabel').textContent = tasaIgv;
        document.getElementById('resSubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('resIgv').textContent = igv.toFixed(2);
        document.getElementById('resTotal').textContent = total.toFixed(2);
        document.getElementById('f_subtotal').value = subtotal.toFixed(2);
        document.getElementById('f_igv').value = igv.toFixed(2);
        document.getElementById('f_total').value = total.toFixed(2);
    }

    // Recalculate on IGV type change
    document.getElementById('f_tipo_igv').addEventListener('change', calcular);

    // Bind first row
    bindItemEvents(itemsBody.querySelector('.item-row'));

    // Form submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');

        // Build detalle JSON
        var items = [];
        itemsBody.querySelectorAll('.item-row').forEach(function (row) {
            var desc = row.querySelector('.item-desc').value.trim();
            var cant = parseFloat(row.querySelector('.item-cant').value) || 0;
            var precio = parseFloat(row.querySelector('.item-precio').value) || 0;
            if (desc && cant > 0) {
                items.push({ descripcion: desc, cantidad: cant, precio_unitario: precio, total: cant * precio });
            }
        });
        document.getElementById('f_detalle').value = items.length ? JSON.stringify(items) : '';

        calcular();

        var fd = new FormData(form);
        fetch('/comprobantes/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    mostrarAlerta('Comprobante creado correctamente.', 'success');
                    limpiarForm();
                } else {
                    var msg = res.message || 'Error al guardar.';
                    if (res.errors) msg = Object.values(res.errors).join('<br>');
                    mostrarAlerta(msg, 'danger');
                    btnGuardar.disabled = false;
                    spinner.classList.add('d-none');
                }
            })
            .catch(function () {
                mostrarAlerta('Error de conexión.', 'danger');
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
            });
    });

    document.getElementById('btnLimpiar').addEventListener('click', function () {
        limpiarForm();
    });

    function limpiarForm() {
        form.reset();
        alertBox.innerHTML = '';
        btnGuardar.disabled = false;
        spinner.classList.add('d-none');
        document.getElementById('f_fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('f_serie').value = '';
        document.getElementById('f_numero').value = '';
        document.getElementById('f_tipo_envio').value = '';
        document.getElementById('f_tipo_envio_hidden').value = '';
        document.getElementById('f_tipo').disabled = true;
        document.getElementById('f_tipo').innerHTML = '<option value="">Primero seleccione sede</option>';
        document.getElementById('serviciosContainer').style.display = 'none';
        document.getElementById('serviciosLista').innerHTML = '';
        clienteTexto.value = '';
        clienteHidden.value = '';
        clienteInfo.textContent = 'Escribe para buscar o agrega uno nuevo';
        document.getElementById('f_tipo_igv').value = '0';
        // Reset items to one empty row
        itemsBody.innerHTML = '';
        agregarItem('', 1, 0);
        calcular();
    }

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }

    function escHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Init
    if (typeof lucide !== 'undefined') lucide.createIcons();
})();
