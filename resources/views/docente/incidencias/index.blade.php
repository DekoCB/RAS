@extends('layouts.mantis')

@section('title', 'Historial de incidencias | Docente')
@section('page-title', 'Historial de incidencias')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-count-card mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-pill">Historial</div>
                    <h5 class="mb-1">Incidencias registradas</h5>
                    <p class="mb-0 opacity-75">
                        Revisa las incidencias reportadas por secci&oacute;n, curso y estudiante.
                    </p>
                </div>
                <a href="{{ route('docente.incidencias.create') }}" class="btn jp-btn-search">
                    Registrar incidencia
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table jp-table jp-notas-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Secci&oacute;n</th>
                            <th>Curso</th>
                            <th>Estudiante</th>
                            <th>Tipo</th>
                            <th>Gravedad</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incidencias as $incidencia)
                            @php
                                $gravedad = $incidencia->gravedad ?? '-';
                                $gravedadClass = match ($gravedad) {
                                    'Leve' => 'jp-incidencia-pill--leve',
                                    'Moderada' => 'jp-incidencia-pill--moderada',
                                    'Grave' => 'jp-incidencia-pill--grave',
                                    'Critica' => 'jp-incidencia-pill--critica',
                                    default => 'jp-incidencia-pill--neutral',
                                };
                                $estado = strtolower($incidencia->estado ?? 'abierta');
                                $estadoClass = match ($estado) {
                                    'abierta' => 'jp-incidencia-pill--abierta',
                                    'cerrada', 'resuelta' => 'jp-incidencia-pill--cerrada',
                                    default => 'jp-incidencia-pill--neutral',
                                };
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($incidencia->fecha_reporte)->format('d-m-Y') }}</td>
                                <td>
                                    {{ $incidencia->seccion?->grado?->nombre ?? '-' }} - {{ $incidencia->seccion?->nombre ?? '-' }}
                                </td>
                                <td>{{ $incidencia->curso?->nombre ?? '-' }}</td>
                                <td>
                                    {{ $incidencia->estudiante?->apellidos ?? '-' }},
                                    {{ $incidencia->estudiante?->nombres ?? '' }}
                                </td>
                                <td>{{ $incidencia->tipo ?? '-' }}</td>
                                <td><span class="jp-incidencia-pill {{ $gravedadClass }}">{{ $gravedad }}</span></td>
                                <td>
                                    <span class="jp-incidencia-pill {{ $estadoClass }}">
                                        {{ ucfirst($estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('docente.incidencias.edit', $incidencia) }}" class="text-primary me-2">Editar</a>
                                    <form method="POST" action="{{ route('docente.incidencias.destroy', $incidencia) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-danger border-0 bg-transparent p-0" onclick="return confirm('Eliminar incidencia?')">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No hay incidencias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 jp-table-pagination">
                {{ $incidencias->links() }}
            </div>
        </div>
    </div>
@endsection
