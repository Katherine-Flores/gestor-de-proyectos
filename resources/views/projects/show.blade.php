@extends('layouts.app')

@section('title', 'Detalle del Proyecto')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0" id="project-name-display">Cargando Proyecto...</h1>
        <div id="project-actions">
            <a href="#" id="edit-link" class="btn btn-warning d-none">Editar</a>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Volver al listado</a>
        </div>
    </div>

    <div id="project-info-container">
        <p class="text-center text-muted">Cargando información...</p>
        <div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-3">
            <h5 class="mb-0">Recursos Asignados</h5>
        </div>
        <div class="card-body p-4">
            <ul class="list-group list-group-flush" id="resources-display-container">
                <li class="list-group-item text-muted">Cargando recursos...</li>
            </ul>
        </div>
    </div>

    <hr class="mt-4">

    <div class="row mt-4" id="seccion-registrar-actualización">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">Registrar Nueva Actualización</div>
                <div class="card-body">
                    <form id="updateForm">
                        <input type="hidden" id="project_id" name="project_id" value="">

                        <div class="mb-3">
                            <label for="contenido" class="form-label">Contenido de la Actualización</label>
                            <textarea class="form-control" id="contenido" name="contenido" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="porcentaje_avance" class="form-label">Avance (%)</label>
                                <input type="number" class="form-control" id="porcentaje_avance" name="porcentaje_avance" min="0" max="100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado_actualizado" class="form-label">Nuevo Estado</label>
                                <select class="form-select" id="estado_actualizado" name="estado_actualizado" required>
                                    <option value="">Selecciona...</option>
                                    <option value="Planificado">Planificado</option>
                                    <option value="En Ejecución">En Ejecución</option>
                                    <option value="En Auditoría">En Auditoría</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="resultado_final" class="form-label">Resultado Final</label>
                            <select class="form-select" id="resultado_final" name="resultado_final">
                                <option value="">Seleccione...</option>
                                <option value="Completo">Completo</option>
                                <option value="Incompleto">Incompleto</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar Avance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            crossorigin="anonymous"></script>

    <script src="{{ asset('js/projects.js') }}"></script>

    <script>
        $(document).ready(function() {
            if (localStorage.getItem('user_role') !== 'Lider') {
                $('#edit-link').hide();
                $('#seccion-registrar-actualización').hide();
            }
        });
    </script>
@endsection
