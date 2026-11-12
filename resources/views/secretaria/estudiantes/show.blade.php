@extends('layouts.mantis')

@section('title', 'Detalle Estudiante | Secretaria')
@section('page-title', 'Detalle Estudiante')
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
    @php
        $codigoEstudiante = trim((string) ($estudiante->codigo ?? ''));
        if ($codigoEstudiante === '' || $codigoEstudiante === '-') {
            $codigoEstudiante = $estudiante->carnet_codigo
                ? trim((string) $estudiante->carnet_codigo)
                : 'ALU-' . str_pad((string) $estudiante->id, 5, '0', STR_PAD_LEFT);
        }
    @endphp
    <div class="card jp-soft-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Detalle del estudiante</h5>
            <a href="{{ route('secretaria.estudiantes.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
        <div class="card-body">
            <p><strong>Alumno:</strong> {{ $estudiante->apellidos }} {{ $estudiante->nombres }}</p>
            <p><strong>DNI:</strong> {{ $estudiante->dni ?? '-' }}</p>
            <p><strong>Codigo:</strong> {{ $codigoEstudiante }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($estudiante->estado ?? 'activo') }}</p>
        </div>
    </div>
@endsection
