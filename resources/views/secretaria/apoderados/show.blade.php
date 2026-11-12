@extends('layouts.mantis')

@section('title', 'Detalle Apoderado | Secretaria')
@section('page-title', 'Detalle Apoderado')
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
            <h5 class="mb-0">Detalle del apoderado</h5>
            <a href="{{ route('secretaria.apoderados.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
        <div class="card-body">
            <p><strong>Apoderado:</strong> {{ $apoderado->apellidos }} {{ $apoderado->nombres }}</p>
            <p><strong>DNI:</strong> {{ $apoderado->dni ?? '-' }}</p>
            <p><strong>Tel&eacute;fono:</strong> {{ $apoderado->telefono ?? '-' }}</p>
            <p><strong>Email:</strong> {{ $apoderado->email ?? '-' }}</p>
            <p><strong>Direcci&oacute;n:</strong> {{ $apoderado->direccion ?? '-' }}</p>
        </div>
    </div>
@endsection
