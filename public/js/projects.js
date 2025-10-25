// Local = http://127.0.0.1:8000/api
// AWS =  http://18.216.126.104/api
const API_BASE_URL = 'http://18.216.126.104/api';
let token = null;

// =============================================
// UTILIDADES GLOBALES
// =============================================
token = getCookie('token');
userRole = localStorage.getItem('user_role');

function getProjectIdFromUrl() {
    const match = window.location.pathname.match(/\/projects\/(\d+)/);
    return match ? match[1] : null;
}

// =============================================
// UTILS DE RECURSOS DINÁMICOS
// =============================================
let resourceIndex = 0; // Contador global de índice de recursos, inicializado a 0

// Función para generar la fila HTML de un recurso
function generateResourceRow(index, tipo = 'tiempo', descripcion = '', cantidad = 0) {
    return `
        <div class="row mb-2 resource-item" data-index="${index}">
            <div class="col-4">
                <select class="form-select resource-type" name="recursos[${index}][tipo]">
                    <option value="tiempo" ${tipo === 'tiempo' ? 'selected' : ''}>Tiempo</option>
                    <option value="personas" ${tipo === 'personas' ? 'selected' : ''}>Personas</option>
                    <option value="equipos" ${tipo === 'equipos' ? 'selected' : ''}>Equipos</option>
                    <option value="servicios" ${tipo === 'servicios' ? 'selected' : ''}>Servicios</option>
                </select>
            </div>
            <div class="col-5">
                <input type="text" class="form-control resource-description" name="recursos[${index}][descripcion]" placeholder="Descripción" value="${descripcion}">
            </div>
            <div class="col-2">
                <input type="number" class="form-control resource-quantity" name="recursos[${index}][cantidad]" placeholder="Cant." min="0" value="${cantidad}">
            </div>
            <div class="col-1 d-flex align-items-center justify-content-center">
                <button type="button" class="btn btn-danger btn-sm remove-resource"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>`;
};

function addResourceRow() {
    const resourcesContainer = $('#resources-container');

    // Generamos la nueva fila con el índice global actual
    resourcesContainer.append(generateResourceRow(resourceIndex));

    // Adjuntar evento de eliminar a la nueva fila
    $(`#resources-container .resource-item[data-index="${resourceIndex}"] .remove-resource`).on('click', removeResourceRow);

    resourceIndex++;
}

function removeResourceRow() {
    if ($('#resources-container .resource-item').length > 1) {
        $(this).closest('.resource-item').remove();
    } else {
        alert("Debe haber al menos un recurso.");
    }
}

token = getCookie('token');

// =============================================
// LÓGICA DE LISTADO (GET /projects)
// =============================================
function renderProjects(projects) {
    let tableBodyHtml = '';

    const isLider = userRole === 'Lider';

    if (!projects || projects.length === 0) {
        tableBodyHtml = '<tr><td colspan="5" class="text-center">No hay proyectos para mostrar.</td></tr>';
    } else {
        projects.forEach(project => {
            const avance = project.porcentaje_avance ?? 0;

            const estadoNormalizado = project.estado.toLowerCase().replace(/ /g, '-');

            const estadoBadge = `<span class="status-badge bg-${estadoNormalizado}">${project.estado}</span>`;
            const progressBarClass = `progress-bar-${estadoNormalizado}`;

            let actionsHtml = '';
            if (isLider) {
                actionsHtml = `
                    <a href="/projects/${project.id}/edit" class="btn btn-action btn-warning"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-action btn-danger delete-project-btn" data-id="${project.id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                `;
            }

            tableBodyHtml += `
                <tr>
                    <td>${project.nombre}</td>
                    <td>${project.tipo}</td>
                    <td>${estadoBadge}</td>
                    <td class="col-w-avance">
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar ${progressBarClass}" role="progressbar"
                                    style="width: ${avance}%;"
                                    aria-valuenow="${avance}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <span class="progress-text">${avance}%</span>
                        </div>
                    </td>
                    <td>
                        <a href="/projects/${project.id}" class="btn btn-action btn-info"><i class="fas fa-eye"></i></a>
                        ${actionsHtml}
                    </td>
                </tr>`;
        });
    }

    const tableHtml = `
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th class="col-w-nombre">Nombre</th>
                    <th class="col-w-data">Tipo</th>
                    <th class="col-w-data">Estado</th>
                    <th class="col-w-avance">Avance</th>
                    <th class="col-w-acciones">Acciones</th>
                </tr>
                </thead>
                <tbody>${tableBodyHtml}</tbody>
            </table>
        </div>`;

    $('#projects-table-container').html(tableHtml);
}

function fetchProjects() {
    if (!token) {
        $('#projects-table-container').html('<p class="alert alert-danger">Error: Token no encontrado. Inicia sesión.</p>');
        return;
    }

    $.ajax({
        url: `${API_BASE_URL}/projects`,
        method: 'GET',
        headers: { 'Authorization': 'Bearer ' + token },
        success: function(response) {
            let projectsArray;

            if (response && response.projects) {
                projectsArray = response.projects;
            } else {
                projectsArray = [];
            }

            renderProjects(projectsArray);
        },
        error: function(xhr) {
            $('#projects-table-container').html('<p class="alert alert-danger">Error al cargar proyectos. Inténtalo de nuevo.</p>');
        }
    });
}

function deleteProject(projectId) {
    if (!confirm('¿Seguro que deseas eliminar este proyecto?')) return;

    $.ajax({
        url: `${API_BASE_URL}/projects/${projectId}`,
        method: 'DELETE',
        headers: { 'Authorization': 'Bearer ' + token },
        success: function() {
            alert('Proyecto eliminado con éxito.');
            fetchProjects();
        },
        error: function() {
            alert('Error al eliminar el proyecto.');
        }
    });
}

// =============================================
// LÓGICA DE EDICIÓN (GET /projects/{id} & PUT /projects/{id})
// =============================================
function renderEditForm(projectData) {
    const htmlForm = `
        <form id="editProjectForm">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Proyecto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="${projectData.nombre || ''}" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3">${projectData.descripcion || ''}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select name="tipo" id="tipo" class="form-select" required>
                        <option value="software" ${projectData.tipo == 'software' ? 'selected' : ''}>Software</option>
                        <option value="redes" ${projectData.tipo == 'redes' ? 'selected' : ''}>Redes</option>
                        <option value="hardware" ${projectData.tipo == 'hardware' ? 'selected' : ''}>Hardware</option>
                        <option value="otros" ${projectData.tipo == 'otros' ? 'selected' : ''}>Otros</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" class="form-control" id="categoria" name="categoria" value="${projectData.categoria || ''}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="${projectData.fecha_inicio || ''}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_fin_estimada" class="form-label">Fecha Fin Estimada</label>
                    <input type="date" class="form-control" id="fecha_fin_estimada" name="fecha_fin_estimada" value="${projectData.fecha_fin_estimada || ''}">
                </div>
            </div>

            <h5 class="mt-4 border-bottom pb-2">Asignación de Recursos</h5>
            <div id="resources-container">
                </div>
            <button type="button" id="add-resource-btn" class="btn btn-form-action btn-add-resource mt-2">
                <i class="fas fa-plus-circle"></i> Añadir Recurso
            </button>

            <div class="mt-3 d-flex justify-content-end gap-2">
                <a href="/projects" class="btn btn-form-action btn-cancel">
                    <i class="fas fa-times-circle"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-form-action btn-primary">
                    <i class="fas fa-save"></i> Actualizar Proyecto
                </button>
            </div>
        </form>
    `;

    $('#edit-form-container').html(htmlForm);
    $('#project-title').text(`Editar Proyecto: ${projectData.nombre}`);

    const resourcesContainer = $('#resources-container');
    resourcesContainer.empty(); // Limpiar contenido previo

    resourceIndex = 0;

    if (projectData.resources && projectData.resources.length > 0) {
        projectData.resources.forEach(resource => {
            resourcesContainer.append(generateResourceRow(
                resourceIndex,
                resource.tipo,
                resource.descripcion,
                resource.cantidad
            ));
            resourceIndex++;
        });
    } else {
        resourcesContainer.append(generateResourceRow(resourceIndex));
        resourceIndex++;
    }

    resourcesContainer.find('.remove-resource').on('click', removeResourceRow);
    // Usamos .off().on() para asegurar que el evento no se duplique cada vez que renderizamos
    $('#add-resource-btn').off('click').on('click', addResourceRow);
}

function fetchProjectForEdit(projectId) {
    if (!token || !projectId) {
        $('#edit-form-container').html('<p class="alert alert-danger">Error: No se puede cargar el proyecto. Falta token o ID.</p>');
        return;
    }

    $.ajax({
        url: `${API_BASE_URL}/projects/${projectId}`,
        method: 'GET',
        headers: { 'Authorization': 'Bearer ' + token },
        success: function(response) {
            renderEditForm(response.project);
        },
        error: function(xhr) {
            $('#edit-form-container').html('<p class="alert alert-danger">No se pudo cargar el proyecto.</p>');
        }
    });
}


// =============================================
// LÓGICA DE DETALLE Y ACTUALIZACIÓN (show.blade.php & POST /updates)
// =============================================

function renderProjectDetails(project) {
    // Normalizamos el estado para la clase CSS y la clase de barra
    const estadoNormalizado = project.estado.toLowerCase().replace(/ /g, '-');
    const estadoBadge = `<span class="status-badge bg-${estadoNormalizado}">${project.estado}</span>`;
    const progressBarClass = `progress-bar-${estadoNormalizado}`;
    const avance = project.porcentaje_avance ?? 0;

    // Lógica condicional para el Resultado Final
    let resultadoFinalHtml = '';

    // Si el estado es Finalizado Y tiene un resultado_final
    if (project.estado === 'Finalizado' && project.resultado_final) {

        const resultadoClass = project.resultado_final.toLowerCase();

        resultadoFinalHtml = `
            <div class="col-md-12 mt-3">
                <p class="mb-0"><strong>Resultado Final:</strong>
                <span class="status-badge bg-${resultadoClass}">${project.resultado_final}</span>
                </p>
            </div>
        `;
    }

    const htmlInfo = `
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header">
                 <h5 class="mb-0 text-primary">Información General</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted">${project.descripcion || 'Sin descripción.'}</p>
                <div class="row g-3">
                    <div class="col-md-4"><strong>Tipo:</strong> ${project.tipo}</div>
                    <div class="col-md-4"><strong>Categoría:</strong> ${project.categoria}</div>
                    <div class="col-md-4"><strong>Estado:</strong> ${estadoBadge}</div>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6"><strong>Fecha Inicio:</strong> ${project.fecha_inicio}</div>
                    <div class="col-md-6"><strong>Fecha Fin Estimada:</strong> ${project.fecha_fin_estimada}</div>
                    ${resultadoFinalHtml}
                </div>

                <h5 class="mt-4 pt-3 border-top">Avance del Proyecto</h5>
                <div class="progress-container">
                    <div class="progress">
                        <div class="progress-bar ${progressBarClass}" role="progressbar"
                            style="width: ${avance}%;"
                            aria-valuenow="${avance}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                    </div>
                    <span class="progress-text">${avance}%</span>
                </div>
            </div>
        </div>
        `;

    $('#project-info-container').html(htmlInfo);

    let resourcesHtml = '';

    if (project.resources && project.resources.length > 0) {
        project.resources.forEach(resource => {
            const tipoCapitalized = resource.tipo.charAt(0).toUpperCase() + resource.tipo.slice(1);
            resourcesHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${resource.descripcion}
                    <span class="badge bg-primary rounded-pill">
                        ${tipoCapitalized}: ${resource.cantidad}
                    </span>
                </li>
            `;
        });
    } else {
        resourcesHtml = '<li class="list-group-item text-muted">No hay recursos asignados a este proyecto.</li>';
    }

    $('#resources-display-container').html(resourcesHtml);

    $('#project-name-display').text(project.nombre);
    $('#edit-link').attr('href', `/projects/${project.id}/edit`).removeClass('d-none');
    $('#project_id').val(project.id);
}

function fetchProjectDetails(projectId) {
    if (!token || !projectId) {
        $('#project-info-container').html('<p class="alert alert-danger">Error: Falta token o ID.</p>');
        return;
    }

    $.ajax({
        url: `${API_BASE_URL}/projects/${projectId}`,
        method: 'GET',
        headers: { 'Authorization': 'Bearer ' + token },
        success: function(response) {
            renderProjectDetails(response.project);
        },
        error: function(xhr) {
            $('#project-info-container').html('<p class="alert alert-danger">No se pudo cargar el proyecto.</p>');
        }
    });
}


// =============================================
// INICIALIZACIÓN Y MANEJO DE EVENTOS
// =============================================
$(document).ready(function() {
    token = getCookie('token');

    // ---------------------------------------------
    // INDEX (Listado)
    // ---------------------------------------------
    if (window.location.pathname === '/projects') {
        fetchProjects();

        $('#projects-table-container').on('click', '.delete-project-btn', function() {
            const projectId = $(this).data('id');
            deleteProject(projectId);
        });
    }

    // ---------------------------------------------
    // CREATE (Creación)
    // ---------------------------------------------
    if (window.location.pathname === '/projects/create') {
        // Inicializar recursos dinámicos para la vista de creación
        $('#resources-container .remove-resource').on('click', removeResourceRow);
        $('#add-resource-btn').on('click', addResourceRow);
    }

    $('#createProjectForm').on('submit', function(e) {
        e.preventDefault();

        const recursos = [];
        $('#resources-container .resource-item').each(function() {
            const cantidad = parseInt($(this).find('.resource-quantity').val());
            if (cantidad > 0) {
                recursos.push({
                    tipo: $(this).find('.resource-type').val(),
                    descripcion: $(this).find('.resource-description').val(),
                    cantidad: cantidad
                });
            }
        });

        const formData = {
            nombre: $('#nombre').val(),
            descripcion: $('#descripcion').val(),
            tipo: $('#tipo').val(),
            categoria: $('#categoria').val(),
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin_estimada: $('#fecha_fin_estimada').val(),
            estado: "Planificado",
            porcentaje_avance: 0,
            clientes: [],
            integrantes: [],
            recursos: recursos,
        };

        $.ajax({
            url: `${API_BASE_URL}/projects`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            headers: { 'Authorization': 'Bearer ' + token },
            success: function() {
                alert('Proyecto creado con éxito.');
                window.location.href = '/projects';
            },
            error: function(xhr) {
                alert('Error al crear proyecto: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            }
        });
    });

    // ---------------------------------------------
    // EDIT (Edición)
    // ---------------------------------------------
    if (window.location.pathname.includes('/edit')) {
        const projectId = getProjectIdFromUrl();
        if (projectId) {
            fetchProjectForEdit(projectId);
        }
    }

    $('#edit-form-container').on('submit', '#editProjectForm', function(e) {
        e.preventDefault();

        const projectId = getProjectIdFromUrl();

        const recursos = [];
        $('#resources-container .resource-item').each(function() {
            const cantidad = parseInt($(this).find('.resource-quantity').val());
            if (cantidad > 0) {
                recursos.push({
                    tipo: $(this).find('.resource-type').val(),
                    descripcion: $(this).find('.resource-description').val(),
                    cantidad: cantidad
                });
            }
        });

        const formData = {
            nombre: $('#nombre').val(),
            descripcion: $('#descripcion').val(),
            tipo: $('#tipo').val(),
            categoria: $('#categoria').val(),
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin_estimada: $('#fecha_fin_estimada').val(),
            recursos: recursos,
            clientes: [], // Mantener vacíos por ahora
            integrantes: [], // Mantener vacíos por ahora
        };

        $.ajax({
            url: `${API_BASE_URL}/projects/${projectId}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            headers: { 'Authorization': 'Bearer ' + token },
            success: function() {
                alert('Proyecto actualizado con éxito.');
                window.location.href = `/projects/${projectId}`;
            },
            error: function(xhr) {
                alert('Fallo en la actualización: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            }
        });
    });


    // ---------------------------------------------
    // SHOW & UPDATES (Detalle y Registro de Avance)
    // ---------------------------------------------
    if (window.location.pathname.match(/\/projects\/\d+$/)) {
        const projectId = getProjectIdFromUrl();
        const resultadoFinalInput = $('#resultado_final');
        const estadoActualizadoSelect = $('#estado_actualizado');

        estadoActualizadoSelect.on('change', function() {
            if ($(this).val() === 'Finalizado') {
                resultadoFinalInput.prop('required', true);
                resultadoFinalInput.closest('.mb-3').find('label').append('<span class="text-danger required-star">*</span>');
            } else {
                resultadoFinalInput.prop('required', false);
                resultadoFinalInput.closest('.mb-3').find('.required-star').remove();
            }
        }).trigger('change');

        if (projectId) {
            fetchProjectDetails(projectId);
        }
    }

    $('#updateForm').on('submit', function(e) {
        e.preventDefault();

        const projectId = $('#project_id').val();

        const formData = {
            project_id: projectId,
            contenido: $('#contenido').val(),
            porcentaje_avance: $('#porcentaje_avance').val(),
            estado_actualizado: $('#estado_actualizado').val(),
            resultado_final: $('#resultado_final').val()
        };

        $.ajax({
            url: `${API_BASE_URL}/updates`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            headers: { 'Authorization': 'Bearer ' + token },
            success: function() {
                alert('Actualización registrada con éxito.');
                fetchProjectDetails(projectId); // Recargar
                $('#updateForm')[0].reset();
            },
            error: function(xhr) {
                alert('Fallo en la actualización: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            }
        });
    });

    /*$('#logout-button').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: `${API_BASE_URL}/logout`,
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                localStorage.removeItem('user_role');
                window.location.href = '/login';
            },
            error: function(xhr) {
                console.error("Fallo al revocar token en la API, forzando redirección.", xhr);
                localStorage.removeItem('user_role');
                window.location.href = '/login';
            }
        });
    });*/

});
