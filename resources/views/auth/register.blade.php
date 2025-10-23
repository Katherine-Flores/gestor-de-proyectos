<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - GestiónPro</title>

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="login-container">
    <header class="header">
        <a href="/" class="logo">GestiónPro</a>
    </header>

    <div class="card">
        <h1>Crear Cuenta</h1>
        <p class="subtitle">Únete a nuestra plataforma</p>

        <form id="registerForm" class="register-form">
            {{-- Los campos 'name' y 'lastname' se usarán para construir el 'nombre' completo --}}
            <div class="name-fields">
                <div class="form-group half-width">
                    <label for="name">Nombre *</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="name" name="name" placeholder="Tu nombre" required>
                    </div>
                </div>

                <div class="form-group half-width">
                    <label for="lastname">Apellido *</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="lastname" name="lastname" placeholder="Tu apellido" required>
                    </div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="email">Correo Electrónico *</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="password">Contraseña *</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                    <i class="fas fa-eye toggle-password"></i>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="password_confirmation">Confirmar Contraseña *</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite tu contraseña" required>
                    <i class="fas fa-eye toggle-password"></i>
                </div>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a></label>
            </div>

            <button type="submit" class="btn-primary">
                Crear Cuenta
            </button>
        </form>

        <div class="footer-links">
            <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia Sesión</a></p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        crossorigin="anonymous"></script>

<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
