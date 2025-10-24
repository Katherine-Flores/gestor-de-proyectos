@extends('layouts.app')

@section('title', 'Editar Proyecto')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4" id="project-title">Editar Proyecto: Cargando...</h1>

            <div id="edit-form-container">
                <p class="text-center text-muted">Cargando datos del proyecto...</p>
                <div class="text-center">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                <h5 class="mt-4 border-bottom pb-2">Asignación de Recursos</h5>
                <div id="resources-container">
                    <p class="text-center text-muted">Cargando recursos...</p>
                </div>
                <button type="button" id="add-resource-btn" class="btn btn-action btn-primary mt-2">
                    <i class="fas fa-plus-circle me-1"></i> Añadir Recurso
                </button>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            crossorigin="anonymous"></script>

    <script src="{{ asset('js/projects.js') }}"></script>
@endsection
