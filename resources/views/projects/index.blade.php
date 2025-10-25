@extends('layouts.app')

@section('title', 'Listado de Proyectos')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Listado de Proyectos</h1>
                <!-- CORRECCIÓN: Botón "Crear" apunta a la ruta 'create' -->
                <a href="{{ route('projects.create') }}" class="btn btn-primary" id="create-project-btn">
                    Crear Nuevo Proyecto
                </a>
            </div>

            <div id="projects-table-container">
                <p class="text-center text-muted">Cargando proyectos...</p>
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
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
                $('#create-project-btn').hide();
            }
        });
    </script>
@endsection
