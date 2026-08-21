(function () {
    'use strict';

    var form = document.getElementById('cfaForm');
    var btn = document.getElementById('btnGuardarCfa');
    var spinner = btn.querySelector('.spinner-border');
    var alertBox = document.getElementById('cfaAlert');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.disabled = true;
        spinner.classList.remove('d-none');

        var fd = new FormData(form);

        fetch('/facturacion-automatica/configuracion/guardar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    alertBox.innerHTML = '<div class="alert alert-success alert-dismissible fade show py-2" role="alert">'
                        + res.message + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
                } else {
                    var msg = res.message || 'Error al guardar.';
                    if (res.errors) msg = Object.values(res.errors).join('<br>');
                    alertBox.innerHTML = '<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">'
                        + msg + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
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
