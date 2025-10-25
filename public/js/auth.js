const API_BASE_URL = 'http://18.216.126.104/api';

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
    return null;
}

$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: ' http://18.216.126.104/api/login',
            method: 'POST',
            data: {
                email: $('#email').val(),
                password: $('#password').val()
            },
            success: function(response) {
                // Guardar token en cookies (1 día)
                document.cookie = `token=${response.access_token}; path=/; max-age=86400`;

                // Guardar el rol del usuario en localStorage
                localStorage.setItem('user_role', response.user.role);

                //alert(`Bienvenido ${response.user.nombre}!`);
                window.location.href = '/projects';
            },
            error: function(xhr) {
                alert('Credenciales inválidas o error de conexión.');
            }
        });
    });

    $('#registerForm').on('submit', function(e) {
        e.preventDefault();

        const nombreCompleto = $('#name').val() + ' ' + $('#lastname').val();

        const data = {
            nombre: nombreCompleto,
            email: $('#email').val(),
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val()
        };

        $.ajax({
            url: ' http://18.216.126.104/api/register',
            method: 'POST',
            data: data,
            success: function(response) {
                document.cookie = `token=${response.access_token}; path=/; max-age=86400`;

                //alert(`¡Cuenta creada con éxito, ${response.user.nombre}! Redirigiendo al login.`);
                window.location.href = '/login';
            },
            error: function(xhr) {
                let errorMessage = 'Error desconocido al intentar registrar la cuenta.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = 'Error de validación: revisa que las contraseñas coincidan y que el email no esté en uso.';
                }
                //alert('Fallo en el registro: ' + errorMessage);
            }
        });
    });

    $('#logout-button-global').on('click', function(e) {
        e.preventDefault();

        const token = getCookie('token');

        if (!token) {
            localStorage.removeItem('user_role');
            window.location.href = '/login';
            return;
        }

        $.ajax({
            url: `${API_BASE_URL}/logout`,
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function() {
                document.cookie = "token=; path=/; max-age=0";
                localStorage.removeItem('user_role');
                window.location.href = '/login';
            },
            error: function(xhr) {
                console.error("Fallo al revocar token en la API. Limpiando sesión local.", xhr);
                document.cookie = "token=; path=/; max-age=0";
                localStorage.removeItem('user_role');
                window.location.href = '/login';
            }
        });
    });

    // Mostrar/ocultar contraseña
    $('.toggle-password').on('click', function() {
        const parentWrapper = $(this).closest('.input-icon-wrapper');
        const input = parentWrapper.find('input');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
});
