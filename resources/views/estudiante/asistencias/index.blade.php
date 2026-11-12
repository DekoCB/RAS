@extends('layouts.mantis')

@section('title', 'Asistencias | Estudiante')
@section('page-title', 'Asistencias')
@section('body-class', 'role-estudiante')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/estudiante-modules.css') }}">
@endpush

@section('sidebar')
    @include('estudiante.partials.sidebar')
@endsection

@section('content')
    {{-- Botón volver --}}
    <a class="est-back-link" href="{{ route('estudiante.dashboard') }}">
        <i data-feather="arrow-left"></i> Volver al panel
    </a>

    {{-- Hero --}}
    <div class="est-module-hero">
        <div class="est-module-hero__left">
            <div class="est-module-hero__icon">
                <i data-feather="calendar"></i>
            </div>
            <div>
                <h1 class="est-module-hero__title">Mis asistencias</h1>
                <p class="est-module-hero__sub">Consulta tu historial por mes y revisa el detalle por día.</p>
            </div>
        </div>
        <div class="est-module-hero__right">
            <form method="GET" class="est-filter-form">
                <div>
                    <div class="form-label">Año y mes</div>
                    <div>
                        <select name="anio" class="form-select">
                            @foreach ($aniosDisponibles as $anio)
                                <option value="{{ $anio }}" @selected((int) $anioSeleccionado === (int) $anio)>
                                    {{ $anio }}
                                </option>
                            @endforeach
                        </select>
                        <select name="mes" class="form-select">
                            @foreach ($mesesDisponibles as $mes)
                                <option value="{{ $mes['value'] }}" @selected($mesSeleccionado === $mes['value'])>
                                    {{ ucfirst($mes['label']) }}
                                </option>
                            @endforeach
                        </select>
                        <button class="est-btn-filter" type="submit">
                            <i data-feather="search" style="width:14px;height:14px;display:inline;"></i>
                            Ver
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Métricas --}}
    <div class="est-metrics">
        <div class="est-metric" style="--met-color: linear-gradient(90deg,#172167,#213095); --met-num: #213095;">
            <div class="est-metric__num">{{ $resumen['total'] }}</div>
            <div class="est-metric__label">Total del mes</div>
        </div>
        <div class="est-metric" style="--met-color: linear-gradient(90deg,#166534,#16a34a); --met-num: #16a34a;">
            <div class="est-metric__num">{{ $resumen['presente'] }}</div>
            <div class="est-metric__label">Presentes</div>
        </div>
        <div class="est-metric" style="--met-color: linear-gradient(90deg,#1f2e8e,#23339e); --met-num: #1f2e8e;">
            <div class="est-metric__num">{{ $resumen['tarde'] }}</div>
            <div class="est-metric__label">Tardanzas</div>
        </div>
        <div class="est-metric" style="--met-color: linear-gradient(90deg,#1c2a81,#23339e); --met-num: #1c2a81;">
            <div class="est-metric__num">{{ $resumen['ausente'] }}</div>
            <div class="est-metric__label">Faltas</div>
        </div>
        <div class="est-metric" style="--met-color: linear-gradient(90deg,#1f2e8d,#25359c); --met-num: #1f2e8d;">
            <div class="est-metric__num">{{ $resumen['justificado'] }}</div>
            <div class="est-metric__label">Justificadas</div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="est-section-card">
        <div class="est-section-card__header">
            <div>
                <div class="est-section-card__title">Registro del mes</div>
                <div class="est-section-card__sub">Detalle día a día de tu asistencia</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="est-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Día</th>
                        <th>Sección</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistencias as $asistencia)
                        @php
                            $fecha = \Carbon\Carbon::parse($asistencia->fecha)->locale('es');
                            $estado = strtolower($asistencia->estado ?? '');
                            $estadoNormalizado = match ($estado) {
                                'tardanza' => 'tarde',
                                'falta'    => 'ausente',
                                default    => $estado,
                            };
                            $pill = match ($estadoNormalizado) {
                                'presente'    => 'jp-attendance-pill--presente',
                                'tarde'       => 'jp-attendance-pill--tarde',
                                'ausente'     => 'jp-attendance-pill--ausente',
                                'justificado' => 'jp-attendance-pill--justificado',
                                default       => 'jp-attendance-pill--neutral',
                            };
                            $seccionLabel = $asistencia->seccion
                                ? trim(($asistencia->seccion?->grado?->nombre ?? '') . ' ' . ($asistencia->seccion?->nombre ?? ''))
                                : '-';
                        @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $fecha->translatedFormat('d M Y') }}</td>
                            <td style="color:#6b7280;">{{ ucfirst($fecha->translatedFormat('l')) }}</td>
                            <td>{{ $seccionLabel ?: '-' }}</td>
                            <td>
                                <span class="jp-attendance-pill {{ $pill }}">
                                    {{ ucfirst($estadoNormalizado ?: '-') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="est-empty"><td colspan="4">No hay asistencias registradas en este mes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
