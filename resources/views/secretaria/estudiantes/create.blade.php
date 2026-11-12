@extends('layouts.mantis')

@section('title', 'Nuevo Estudiante | Secretaria')
@section('page-title', 'Nuevo Estudiante')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
    <div class="card jp-soft-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Registrar estudiante</h5>
            <a href="{{ route('secretaria.estudiantes.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('secretaria.estudiantes.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">C&oacute;digo</label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" value="{{ old('dni') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="nombres" class="form-control @error('nombres') is-invalid @enderror" value="{{ old('nombres') }}" required>
                        @error('nombres')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos') }}" required>
                        @error('apellidos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sexo</label>
                        <select name="sexo" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="Masculino" @selected(old('sexo') === 'Masculino')>Masculino</option>
                            <option value="Femenino" @selected(old('sexo') === 'Femenino')>Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="activo" @selected(old('estado', 'activo') === 'activo')>Activo</option>
                            <option value="inactivo" @selected(old('estado') === 'inactivo')>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direcci&oacute;n</label>
                        <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Apoderados asociados</label>
                        <select name="apoderado_ids[]" class="form-select" multiple>
                            @foreach ($apoderados as $apoderado)
                                <option value="{{ $apoderado->id }}" @selected(collect(old('apoderado_ids'))->contains($apoderado->id))>
                                    {{ $apoderado->apellidos }} {{ $apoderado->nombres }} - {{ $apoderado->dni ?? 'Sin DNI' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Puedes seleccionar m&aacute;s de un apoderado.</small>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('secretaria.estudiantes.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn jp-btn-incidencia">Guardar estudiante</button>
                </div>
            </form>
        </div>
    </div>
@endsection
