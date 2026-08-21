(function () {
    'use strict';

    var form = document.getElementById('clientesForm');
    var btn = document.getElementById('btnGuardarClientesExcl');
    var spinner = btn.querySelector('.spinner-border');
    var alertBox = document.getElementById('clientesAlert');

    document.getElementById('checkAll').addEventListener('change', function () {
        var checks = document.querySelectorAll('.check-cliente');
        checks.forEach(function (c) { c.checked = this.checked; }, this);
    });

    document.getElementById('filtroClientes').addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('#tablaClientesExcl tbody tr');
        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = (!q || text.indexOf(q) !== -1) ? '' : 'none';
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.disabled = true;
        spinner.classList.remove('d-none');

        var fd = new FormData(form);
        fetch('/facturacion-automatica/clientes/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    alertBox.innerHTML = '<div class="alert alert-success alert-dismissible fade show py-2" role="alert">'
                        + res.message + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                } else {
                    alertBox.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">'
                        + (res.message || 'Error al guardar.') + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                }
                btn.disabled = false;
                spinner.classList.add('d-none');
            })
            .catch(function () {
                alertBox.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">Error de conexión.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                btn.disabled = false;
                spinner.classList.add('d-none');
            });
    });
})();
