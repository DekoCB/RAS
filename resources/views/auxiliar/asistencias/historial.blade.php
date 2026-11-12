@extends('layouts.mantis')

@section('title', 'Historial Asistencias | Auxiliar')
@section('page-title', 'Historial de Asistencias')
@section('body-class', 'role-auxiliar')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/auxiliar-dashboard.css') }}?v={{ time() }}">
@endpush

@section('sidebar')
    @include('auxiliar.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-search-card mt-2">
        <div class="card-body">
            <form method="GET" action="{{ route('auxiliar.asistencias.historial') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Secci&oacute;n</label>
                        <select name="seccion_id" class="form-select">
                            <option value="">Todas las secciones</option>
                            @foreach ($seccionesAsignadas as $sec)
                                <option value="{{ $sec->id }}" @selected((string) $seccionId === (string) $sec->id)>
                                    {{ $sec->grado?->nombre }} - {{ $sec->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Fecha</label>
                        <select name="fecha" class="form-select">
                            <option value="">Selecciona fecha</option>
                            @foreach ($fechasDisponibles as $f)
                                <option value="{{ $f }}" @selected($fecha === $f)>
                                    {{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button class="btn jp-btn-search" type="submit">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Secci&oacute;n</th>
                            <th>Estudiante</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($asistencias as $a)
                            @php
                                $estado = strtolower($a->estado ?? '');
                                $pill = match($estado) {
                                    'presente' => 'aux-pill--ok',
                                    'tarde'    => 'aux-pill--warn',
                                    'falta', 'ausente' => 'aux-pill--danger',
                                    default    => 'aux-pill--neutral',
                                };
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $a->seccion?->grado?->nombre }} - {{ $a->seccion?->nombre }}</td>
                                <td class="fw-bold">{{ $a->estudiante?->apellidos }}, {{ $a->estudiante?->nombres }}</td>
                                <td class="text-center">
                                    <span class="aux-pill {{ $pill }}">{{ ucfirst($estado) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    @if ($fecha)
                                        No hay registros para la fecha seleccionada.
                                    @else
                                        Selecciona una fecha para ver el historial.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($asistencias->hasPages())
        <div class="mt-3">{{ $asistencias->links() }}</div>
    @endif
@endsection
