@extends('layouts.mantis')

@section('title', 'Nuevo Apoderado | Secretaria')
@section('page-title', 'Nuevo Apoderado')
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
            <h5 class="mb-0">Registrar apoderado</h5>
            <a href="{{ route('secretaria.apoderados.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('secretaria.apoderados.store') }}">
                @csrf
                <div class="row g-3">
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
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" value="{{ old('dni') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tel&eacute;fono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direcci&oacute;n</label>
                        <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Estudiantes asociados</label>
                        <select name="estudiante_ids[]" class="form-select" multiple>
                            @foreach ($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}" @selected(collect(old('estudiante_ids'))->contains($estudiante->id))>
                                    {{ $estudiante->apellidos }} {{ $estudiante->nombres }} - {{ $estudiante->dni ?? 'Sin DNI' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Puedes seleccionar m&aacute;s de un estudiante.</small>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('secretaria.apoderados.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn jp-btn-incidencia">Guardar apoderado</button>
                </div>
            </form>
        </div>
    </div>
@endsection
