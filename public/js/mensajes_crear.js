(function () {
    'use strict';

    var form = document.getElementById('mensajeForm');
    var btnGuardar = document.getElementById('btnGuardar');
    var spinner = btnGuardar.querySelector('.spinner-border');
    var alertBox = document.getElementById('formAlert');

    // Select all / deselect all
    document.getElementById('checkAll').addEventListener('change', function () {
        var checks = document.querySelectorAll('.check-contacto');
        checks.forEach(function (c) { c.checked = this.checked; }, this);
    });

    // Filter contacts table
    document.getElementById('filtroContactos').addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('#tablaContactos tbody tr');
        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = (!q || text.indexOf(q) !== -1) ? '' : 'none';
        });
    });

    // Form submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnGuardar.disabled = true;
        spinner.classList.remove('d-none');

        var selected = document.querySelectorAll('.check-contacto:checked');
        if (selected.length === 0) {
            mostrarAlerta('Seleccione al menos un contacto.', 'danger');
            btnGuardar.disabled = false;
            spinner.classList.add('d-none');
            return;
        }

        var fd = new FormData(form);
        fetch('/mensajes/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    window.location.href = '/mensajes/listado';
                } else {
                    mostrarAlerta(res.message || 'Error al guardar.', 'danger');
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

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }
})();
