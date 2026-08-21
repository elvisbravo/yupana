(function () {
    'use strict';

    var form = document.getElementById('verificarForm');
    var btn = document.getElementById('btnVerificar');
    var spinner = btn.querySelector('.spinner-border');
    var alertBox = document.getElementById('verificarAlert');
    var resultado = document.getElementById('resultado');

    function mostrarAlerta(msg, tipo) {
        alertBox.innerHTML = '<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2" role="alert">' + msg +
            '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
    }

    function botonDescarga(url, texto) {
        return '<a href="' + url + '" target="_blank" class="btn btn-sm btn-soft-primary">' + texto + '</a>';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.disabled = true;
        spinner.classList.remove('d-none');
        alertBox.innerHTML = '';
        resultado.classList.add('d-none');

        var fd = new FormData(form);

        fetch('/verificar/consultar', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    document.getElementById('r_tipo').textContent = res.tipo;
                    document.getElementById('r_serie_numero').textContent = res.serie_numero;
                    document.getElementById('r_fecha').textContent = res.fecha_emision;
                    document.getElementById('r_total').textContent = res.moneda + ' ' + parseFloat(res.total).toFixed(2);

                    var descargas = '';
                    if (res.pdf_url) descargas += botonDescarga(res.pdf_url, 'Ver PDF');
                    if (res.xml_url) descargas += botonDescarga(res.xml_url, 'Descargar XML');
                    if (res.cdr_url) descargas += botonDescarga(res.cdr_url, 'Descargar CDR');
                    document.getElementById('r_descargas').innerHTML = descargas;

                    resultado.classList.remove('d-none');
                } else {
                    mostrarAlerta(res.message || 'No se encontró ningún comprobante con esos datos.', 'danger');
                }
                btn.disabled = false;
                spinner.classList.add('d-none');
            })
            .catch(function () {
                mostrarAlerta('Error de conexión.', 'danger');
                btn.disabled = false;
                spinner.classList.add('d-none');
            });
    });
})();
