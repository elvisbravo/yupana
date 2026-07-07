(function () {
    'use strict';

    var form = document.getElementById('loginForm');
    var submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    var alertContainer = document.getElementById('loginAlert');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;

        clearAlert();

        if (!email || !password) {
            showAlert('Por favor ingresa tu correo y contraseña.', 'danger');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Ingresando...';

        fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password }),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(function () {
                        window.location.href = '/home';
                    }, 600);
                } else {
                    showAlert(data.message, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Iniciar Sesión';
                }
            })
            .catch(function () {
                showAlert('Error de conexión. Intenta nuevamente.', 'danger');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Iniciar Sesión';
            });
    });

    function showAlert(msg, type) {
        clearAlert();
        var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">';
        html += msg;
        html += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        html += '</div>';
        alertContainer.innerHTML = html;
    }

    function clearAlert() {
        if (alertContainer) alertContainer.innerHTML = '';
    }
})();
