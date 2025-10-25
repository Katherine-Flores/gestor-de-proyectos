@extends('layouts.app')

@section('title', 'Configuración de Perfil')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Configuración de Perfil</h1>
            <p class="text-muted">Actualiza tu información personal, correo electrónico y contraseña.</p>

            <form id="profileUpdateForm" class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <h5 class="mt-4 border-bottom pb-2">Cambiar Contraseña (Opcional)</h5>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Deja vacío para no cambiar">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>

                <div class="col-12 mb-3">
                    <label for="current_password" class="form-label">Contraseña Actual (Requerida para Email/Password)</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                    <small class="text-muted">Necesitas tu contraseña actual para confirmar los cambios, especialmente el correo y la contraseña.</small>
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <div id="alert-message" class="mt-3"></div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script>
        const token = getCookie('token');

        function fetchUserProfile() {
            if (!token) {
                window.location.href = '/login';
                return;
            }

            $.ajax({
                url: `${API_BASE_URL}/user`,
                method: 'GET',
                headers: { 'Authorization': 'Bearer ' + token },
                success: function(response) {
                    $('#nombre').val(response.nombre); // Asegúrate que 'nombre' es la clave correcta en la respuesta
                    $('#email').val(response.email);
                    // Guardamos el ID del usuario para la actualización
                    $('#profileUpdateForm').data('user-id', response.id);
                },
                error: function(xhr) {
                    $('#alert-message').html('<div class="alert alert-danger">Error al cargar el perfil.</div>');
                }
            });
        }

        // 2. Manejar Envío del Formulario
        $('#profileUpdateForm').on('submit', function(e) {
            e.preventDefault();

            const userId = $(this).data('user-id');
            const newPassword = $('#password').val();
            const confirmPassword = $('#password_confirmation').val();

            if (newPassword && newPassword !== confirmPassword) {
                alert('Las nuevas contraseñas no coinciden.');
                return;
            }

            const formData = {
                nombre: $('#nombre').val(),
                email: $('#email').val(),
                current_password: $('#current_password').val(),
            };

            if (newPassword) {
                formData.password = newPassword;
                formData.password_confirmation = confirmPassword;
            }

            $.ajax({
                url: `${API_BASE_URL}/users/${userId}/update-profile`,
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                headers: { 'Authorization': 'Bearer ' + token },
                success: function(response) {
                    $('#alert-message').html('<div class="alert alert-success">Perfil actualizado con éxito!</div>');
                    // Recargar datos y limpiar campos sensibles
                    fetchUserProfile();
                    $('#password').val('');
                    $('#password_confirmation').val('');
                    $('#current_password').val('');
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error desconocido';
                    $('#alert-message').html(`<div class="alert alert-danger">Fallo al actualizar: ${message}</div>`);
                }
            });
        });

        // Inicialización
        $(document).ready(function() {
            fetchUserProfile();
        });
    </script>
@endsection
