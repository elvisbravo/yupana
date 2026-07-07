<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Iniciar Sesión | Group Yupana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Group Yupana - Inicio de sesión">
    <meta name="keywords" content="Yupana">
    <meta name="author" content="Group Yupana">

    <link rel="shortcut icon" href="img/yupana-icon.png">

    <script src="assets/js/config.js"></script>
    <link href="assets/css/vendors.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css">
    <script src="assets/plugins/lucide/lucide.min.js"></script>

    <style>
        .auth-brand img {
            max-height: 70px;
            width: auto;
        }
        .auth-brand .logo-text {
            font-size: 1.5rem;
            letter-spacing: 1px;
        }
        .card {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            border-radius: 8px;
            padding: 12px 0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.9rem;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }
        .auth-box {
            min-height: 100vh;
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .form-control {
            padding-right: 40px;
        }
        .password-wrapper .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #8b8b8b;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .password-wrapper .toggle-password:hover {
            color: #495057;
        }
        .divider {
            border-top: 1px solid #e9ecef;
            margin: 1.5rem 0;
        }
    </style>

</head>

<body>

    <div class="auth-box overflow-hidden align-items-center d-flex">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-md-6 col-sm-8">
                    <div class="card">
                        <div class="card-body p-4 p-sm-5">
                            <div class="auth-brand text-center mb-4">
                                <a href="index.html" class="d-inline-block">
                                    <span class="d-flex flex-column align-items-center gap-2">
                                        <img src="img/yupana_logo.png" alt="Group Yupana">
                                    </span>
                                </a>
                                <p class="text-muted mt-3 mb-0">Bienvenido de nuevo. Ingresa tus credenciales para continuar.</p>
                            </div>

                            <form method="POST" id="loginForm">


                                <div id="loginAlert"></div>

                                <div class="mb-3">
                                    <label for="userEmail" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="micorreo@ejemplo.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                        <button type="button" class="toggle-password" id="togglePassword">
                                            <i data-lucide="eye-off" class="eye-icon" style="width: 18px; height: 18px;"></i>
                                            <i data-lucide="eye" class="eye-icon d-none" style="width: 18px; height: 18px;"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">Recordarme</label>
                                    </div>
                                    
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary fw-semibold">Iniciar Sesión</button>
                                </div>

                            </form>
                        </div>
                    </div>
                    <p class="text-center text-muted mt-4 mb-0 small">
                        &copy; <script>document.write(new Date().getFullYear())</script> Group Yupana. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/vendors.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="js/login.js"></script>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const password = document.getElementById('password');
            const icons = this.querySelectorAll('.eye-icon');
            const isPassword = password.type === 'password';
            password.type = isPassword ? 'text' : 'password';
            icons.forEach(icon => icon.classList.toggle('d-none'));
        });
    </script>

</body>

</html>
