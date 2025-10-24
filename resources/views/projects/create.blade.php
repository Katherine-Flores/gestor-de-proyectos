@extends('layouts.app')

@section('title', 'Crear Nuevo Proyecto')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Crear Nuevo Proyecto</h1>

            <form id="createProjectForm">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Proyecto</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="software">Software</option>
                            <option value="redes">Redes</option>
                            <option value="hardware">Hardware</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin_estimada" class="form-label">Fecha Fin Estimada</label>
                        <input type="date" class="form-control" id="fecha_fin_estimada" name="fecha_fin_estimada">
                    </div>
                </div>

                <h5 class="mt-4 border-bottom pb-2">Asignación de Recursos</h5>
                <div id="resources-container">
                    <div class="row mb-2 resource-item" data-index="0">
                        <div class="col-4">
                            <select class="form-select resource-type" name="recursos[0][tipo]">
                                <option value="tiempo">Tiempo</option>
                                <option value="personas">Personas</option>
                                <option value="equipos">Equipos</option>
                                <option value="servicios">Servicios</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control resource-description" name="recursos[0][descripcion]" placeholder="Descripción (ej. Meses, Laptops)">
                        </div>
                        <div class="col-2">
                            <input type="number" class="form-control resource-quantity" name="recursos[0][cantidad]" placeholder="Cant." min="0">
                        </div>
                        <div class="col-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger btn-sm remove-resource">X</button>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-resource-btn" class="btn btn-info btn-sm mt-2">Añadir Recurso</button>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Proyecto</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            crossorigin="anonymous"></script>

    <script src="{{ asset('js/projects.js') }}"></script>
@endsection
