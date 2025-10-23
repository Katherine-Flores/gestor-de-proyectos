@extends('layouts.app')

@section('title', 'Crear Nuevo Proyecto')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Crear Nuevo Proyecto</h1>

            <!-- Mostrar Errores de Validación -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- CORRECCIÓN: 'action' apunta a 'store' y se usa 'POST' -->
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf <!-- ¡¡CRÍTICO para seguridad!! -->

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Proyecto</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="software" {{ old('tipo') == 'software' ? 'selected' : '' }}>Software</option>
                            <option value="redes" {{ old('tipo') == 'redes' ? 'selected' : '' }}>Redes</option>
                            <option value="hardware" {{ old('tipo') == 'hardware' ? 'selected' : '' }}>Hardware</option>
                            <option value="otros" {{ old('tipo') == 'otros' ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" value="{{ old('categoria') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin_estimada" class="form-label">Fecha Fin Estimada</label>
                        <input type="date" class="form-control" id="fecha_fin_estimada" name="fecha_fin_estimada" value="{{ old('fecha_fin_estimada') }}">
                    </div>
                </div>

                <!-- (Campos 'clientes' e 'integrantes' omitidos por simplicidad, los puedes añadir como <select multiple>) -->

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Proyecto</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
