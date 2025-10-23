@extends('layouts.app')

@section('title', 'Listado de Proyectos')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Listado de Proyectos</h1>
                <!-- CORRECCIÓN: Botón "Crear" apunta a la ruta 'create' -->
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    Crear Nuevo Proyecto
                </a>
            </div>
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Avance</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td>{{ $project->nombre }}</td>
                        <td>{{ $project->tipo }}</td>
                        <td><span class="badge bg-info text-dark">{{ $project->estado }}</span></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $project->porcentaje_avance ?? 0 }}%;">{{ $project->porcentaje_avance ?? 0 }}%</div>
                            </div>
                        </td>
                        <td>
                            <!-- CORRECCIÓN: Botón "Ver" apunta a 'show' -->
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info">Ver</a>

                            <!-- CORRECCIÓN: Botón "Editar" apunta a 'edit' -->
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-warning">Editar</a>

                            <!-- CORRECCIÓN: Botón "Eliminar" es un FORMULARIO -->
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar este proyecto?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay proyectos para mostrar.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
