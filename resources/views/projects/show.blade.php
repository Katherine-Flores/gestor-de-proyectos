@extends('layouts.app')

@section('title', $project->nombre)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ $project->nombre }}</h1>
        <div>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning">Editar</a>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Volver al listado</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-3">
            <h5 class="mb-0">Información General</h5>
        </div>
        <div class="card-body p-4">
            <p class="text-muted">{{ $project->descripcion }}</p>
            <div class="row">
                <div class="col-md-4"><strong>Tipo:</strong> {{ $project->tipo }}</div>
                <div class="col-md-4"><strong>Categoría:</strong> {{ $project->categoria }}</div>
                <div class="col-md-4"><strong>Estado:</strong> <span class="badge bg-info text-dark">{{ $project->estado }}</span></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6"><strong>Fecha Inicio:</strong> {{ $project->fecha_inicio }}</div>
                <div class="col-md-6"><strong>Fecha Fin Estimada:</strong> {{ $project->fecha_fin_estimada }}</div>
            </div>
            <h5 class="mt-4">Avance del Proyecto</h5>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $project->porcentaje_avance }}%;" aria-valuenow="{{ $project->porcentaje_avance }}">{{ $project->porcentaje_avance }}%</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><strong>Integrantes y Clientes</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse ($project->users as $user)
                        <li class="list-group-item">{{ $user->name }} <span class="badge bg-secondary float-end">{{ $user->role->name ?? 'Usuario' }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No hay usuarios asignados.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><strong>Recursos Asignados</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse ($project->resources as $recurso)
                        <li class="list-group-item">{{ $recurso->descripcion }} <span class="badge bg-secondary float-end">Cant: {{ $recurso->cantidad }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No hay recursos asignados.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
