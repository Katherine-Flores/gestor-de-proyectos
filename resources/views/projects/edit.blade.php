@extends('layouts.app')

@section('title', 'Editar Proyecto')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Editar Proyecto: {{ $project->nombre }}</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT') {{-- ¡Importante para la actualización! --}}

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Proyecto</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $project->nombre) }}" required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $project->descripcion) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <input type="text" class="form-control" id="tipo" name="tipo" value="{{ old('tipo', $project->tipo) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" value="{{ old('categoria', $project->categoria) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $project->fecha_inicio) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin_estimada" class="form-label">Fecha Fin Estimada</label>
                        <input type="date" class="form-control" id="fecha_fin_estimada" name="fecha_fin_estimada" value="{{ old('fecha_fin_estimada', $project->fecha_fin_estimada) }}">
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="clientes" class="form-label">Clientes</label>
                        <select multiple class="form-control" id="clientes" name="clientes[]">
                            {{-- @foreach($all_clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ $project->clientes->contains($client->id) ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach --}}
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="integrantes" class="form-label">Integrantes</label>
                        <select multiple class="form-control" id="integrantes" name="integrantes[]">
                            {{-- @foreach($all_users as $user)
                                <option value="{{ $user->id }}"
                                    {{ $project->integrantes->contains($user->id) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach --}}
                        </select>
                    </div>
                </div>


                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Actualizar Proyecto</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
