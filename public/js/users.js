const ROLE_NAME_TO_ID = {
    'Lider': 1,
    'Integrante': 2,
    'Cliente': 3,
};
const ROLE_ID_TO_NAME = {
    1: 'Lider',
    2: 'Integrante',
    3: 'Cliente',
};
const ALL_ROLE_NAMES = ['Lider', 'Integrante', 'Cliente'];

const token = getCookie('token');
const userRole = localStorage.getItem('user_role');
let authUserId = null;


(function() {
    if (userRole !== 'Lider') {
        $('#users-table-container').html('<div class="alert alert-danger">Acceso Denegado: Solo los Líderes pueden gestionar usuarios.</div>');
        return;
    }

    if (!token) {
        window.location.href = '/login';
        return;
    }

    function fetchUsers() {
        const $container = $('#users-table-container');
        $container.html('<p class="text-center"><div class="spinner-border text-primary" role="status"></div> Cargando usuarios...</p>');

        $.ajax({
            url: `${API_BASE_URL}/users`,
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                // Si la API devuelve 'Lider', 'Integrante', 'Cliente' en user.role, ¡perfecto!
                renderUsersTable(response.users);
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al cargar usuarios. Verifica los permisos API.';
                $container.html(`<div class="alert alert-danger">Error: ${message}</div>`);
            }
        });
    }

    function renderUsersTable(users) {
        let rows = '';

        if (!users || users.length === 0) {
            $('#users-table-container').html('<div class="alert alert-info">No se encontraron usuarios en el sistema.</div>');
            return;
        }

        users.forEach(user => {
            const currentRoleId = user.role_id;
            const currentRoleName = user.role; // Nombre del rol devuelto por la API

            const roleOptions = ALL_ROLE_NAMES.map(roleName => {
                const roleId = ROLE_NAME_TO_ID[roleName]; // ID correspondiente
                const isSelected = user.role_id === roleId ? 'selected' : '';
                const isDisabled = user.id === authUserId ? 'disabled' : '';

                return `<option value="${roleId}" ${isSelected} ${isDisabled}>${roleName}</option>`;
            }).join('');

            const isDisabled = user.id === authUserId ? 'disabled' : '';

            rows += `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.nombre}</td>
                    <td>${user.email}</td>
                    <td>
                        <select class="form-select user-role-select" data-user-id="${user.id}" data-user-name="${user.nombre}" data-old-role-id="${currentRoleId}" ${isDisabled}>
                            ${roleOptions}
                        </select>
                        ${user.id === authUserId ? '<span class="badge bg-secondary ms-2">Tú</span>' : ''}
                    </td>
                </tr>
            `;
        });

        const tableHtml = `
            <table class="table table-striped table-hover" id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        `;

        $('#users-table-container').html(tableHtml);
    }

    $('#users-table-container').on('change', '.user-role-select', function() {
        const $select = $(this);
        const userId = $select.data('user-id');
        const userName = $select.data('user-name');

        const newRoleId = $select.val();
        const newRoleName = $select.find('option:selected').text();
        const oldRoleId = $select.data('old-role-id');

        if (userId === authUserId) {
            $select.val(oldRoleId);
            return;
        }

        $('#modal-user-name').text(userName);
        $('#modal-new-role-text').text(newRoleName);

        $('#confirm-role-change-btn').data('user-id', userId)
            .data('new-role-id', newRoleId)
            .data('new-role-name', newRoleName)
            .data('select-element', $select[0]);

        const roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
        roleModal.show();
    });

    $('#confirm-role-change-btn').on('click', function() {
        const $btn = $(this);
        const userId = $btn.data('user-id');
        const newRoleId = $btn.data('new-role-id');
        const newRoleName = $btn.data('new-role-name');
        const selectElement = $btn.data('select-element');
        const $select = $(selectElement);
        const oldRoleId = $select.data('old-role-id');


        $btn.prop('disabled', true).text('Cambiando...');

        $.ajax({
            url: `${API_BASE_URL}/users/${userId}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ role_id: newRoleId }),
            headers: { 'Authorization': 'Bearer ' + token },
            success: function() {
                alert(`Rol actualizado a ${newRoleName} con éxito.`);
                $select.data('old-role-id', parseInt(newRoleId));
                fetchUsers();
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error desconocido al actualizar el rol.';
                alert(`Fallo al actualizar rol: ${message}`);
                $select.val(oldRoleId);
            },
            complete: function() {
                $btn.prop('disabled', false).text('Confirmar Cambio');
                bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            }
        });
    });

    fetchUsers();

})();
