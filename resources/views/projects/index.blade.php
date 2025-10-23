{{-- Esta línea le dice a Blade que use tu nuevo layout --}}
@extends('layouts.app')

{{-- El título de la pestaña del navegador --}}
@section('title', 'Listado de Proyectos')

{{-- Todo tu contenido va aquí --}}
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Listado de Proyectos</h1>
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
                {{-- @forelse ($projects as $project) ... @empty ... @endforelse --}}

                <tr>
                    <td>Sistema de Inventario</td>
                    <td>Software</td>
                    <td><span class="badge bg-info text-dark">Planificado</span></td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 10%;">10%</div>
                        </div>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                        <a href="#" class="btn btn-sm btn-warning">Editar</a>
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
    </div>
@endsection
