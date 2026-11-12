@extends('layouts.mantis')
@php $isAdmin = auth()->user()->hasRole('SuperAdmin'); $rp = $isAdmin ? 'admin-sistema.' : 'director.'; @endphp

@section('title', 'Horario escolar | ' . ($isAdmin ? 'SuperAdmin' : 'Director'))
@section('page-title', 'Horario escolar')
@section('body-class', $isAdmin ? 'role-superadmin' : 'role-director')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @endif
    <style>
        .jp-horario-hero {
            border: none;
            border-radius: 28px;
            background: linear-gradient(120deg, #121a51 0%, #192572 55%, #162061 100%);
            color: #fff;
            box-shadow: 0 18px 36px rgba(18, 26, 80, 0.28);
            position: relative;
            overflow: hidden;
        }
        .jp-horario-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 12% 18%, rgba(177, 187, 255, 0.35), transparent 45%),
                radial-gradient(circle at 85% 70%, rgba(255, 255, 255, 0.18), transparent 50%);
            pointer-events: none;
        }
        .jp-horario-hero .card-body {
            position: relative;
            z-index: 1;
        }
        .jp-horario-hero__layout {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            align-items: center;
            justify-content: space-between;
        }
        .jp-horario-hero__content {
            max-width: 520px;
        }
        .jp-horario-hero__tag {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .85rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.12);
            font-weight: 700;
            font-size: .85rem;
            letter-spacing: .02em;
        }
        .jp-horario-hero__stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: .75rem;
            min-width: 260px;
        }
        .jp-horario-hero__stat {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            padding: .75rem .9rem;
        }
        .jp-horario-hero__stat span {
            display: block;
            font-size: 1.1rem;
            font-weight: 800;
        }
        .jp-horario-hero__stat small {
            display: block;
            opacity: .85;
        }
        .jp-horario-hero__badge {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 999px;
            padding: .5rem 1rem;
            font-weight: 700;
        }
        .jp-horario-levels {
            border-radius: 26px;
            background: #fff;
            padding: 2rem;
            border: 1px solid #d4e1f1;
            box-shadow: 0 16px 30px rgba(18, 27, 82, 0.08);
        }
        .jp-horario-levels__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .jp-horario-levels__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .2rem .7rem;
            border-radius: 999px;
            background: #eaf2fd;
            color: #162061;
            font-weight: 700;
            font-size: .78rem;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .jp-horario-chooser {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
        }
        .jp-horario-level {
            border-radius: 24px;
            border: 1px solid #d4e1f1;
            background: #fff;
            padding: 1.6rem;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 12px 24px rgba(18, 27, 82, 0.12);
            position: relative;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .jp-horario-level::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(130deg, rgba(22, 32, 97, 0.12), transparent 58%);
            pointer-events: none;
        }
        .jp-horario-level:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 30px rgba(18, 27, 82, 0.18);
        }
        .jp-horario-level__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .jp-horario-level__badge {
            display: inline-flex;
            align-items: center;
            padding: .35rem .75rem;
            border-radius: 999px;
            font-weight: 800;
            color: #162061;
            background: #e8f1fd;
        }
        .jp-horario-level__icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #151f5f;
            background: #d6f2ff;
            box-shadow: inset 0 0 0 1px #c6ccf1;
        }
        .jp-horario-level[data-level="Inicial"] .jp-horario-level__icon {
            background: #e1f3f1;
            color: #0f766e;
            box-shadow: inset 0 0 0 1px #c5e7e3;
        }
        .jp-horario-level[data-level="Primaria"] .jp-horario-level__icon {
            background: #d6f2ff;
            color: #17226a;
        }
        .jp-horario-level[data-level="Secundaria"] .jp-horario-level__icon {
            background: #e8efff;
            color: #1f2e8d;
            box-shadow: inset 0 0 0 1px #c9d8ff;
        }
        .jp-horario-level__time {
            font-size: 1.3rem;
            font-weight: 800;
            color: #111427;
        }
        .jp-horario-level__time small {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: #6b7280;
            margin-top: .2rem;
        }
        .jp-horario-level__meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: .4rem 1rem;
            color: #4b5563;
            font-size: .9rem;
        }
        .jp-horario-level__meta strong {
            color: #111427;
        }
        .jp-horario-level__footer {
            margin-top: auto;
        }
        .jp-horario-btn {
            border: none;
            border-radius: 999px;
            padding: .55rem 1.3rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(120deg, #192471, #162061);
            box-shadow: 0 12px 20px rgba(18, 27, 82, 0.22);
        }
        .jp-horario-btn:hover {
            color: #fff;
            transform: translateY(-1px);
        }
        .jp-horario-grid th,
        .jp-horario-grid td {
            text-align: center;
            vertical-align: middle;
        }
        .jp-horario-grid thead th {
            background: #f5f9ff;
            color: #162061;
            border-bottom: 1px solid #d2e0f2;
            font-weight: 700;
        }
        .jp-horario-grid .time-cell {
            text-align: left;
            min-width: 160px;
            font-weight: 700;
            color: #111427;
        }
        .jp-horario-grid .time-cell small {
            display: block;
            font-weight: 500;
            color: #6b7280;
        }
        .jp-horario-grid td {
            height: 46px;
        }
        .jp-horario-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .15rem .6rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            background: #e8f1fd;
            color: #18246e;
        }
        .wizard-steps--horario {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .wizard-top.jp-soft-card {
            border-radius: 18px;
        }
        .wizard-top .card-body {
            padding: 18px 22px;
        }
        .print-only {
            display: none;
        }
        @media print {
            body {
                background: #fff;
            }
            .no-print,
            .sidebar,
            .pc-sidebar,
            .pc-header,
            .navbar,
            .footer,
            .jp-horario-hero,
            .wizard-top,
            .jp-horario-levels {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .jp-horario-grid th,
            .jp-horario-grid td {
                font-size: 12px;
                padding: 6px;
            }
        }
    </style>
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('director.partials.sidebar')
    @endif
@endsection

@section('content')
    
    @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Revisa la informaci&oacute;n:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (empty($nivel))
        <div class="jp-horario-levels mt-4">
            <div class="jp-horario-levels__header">
                <div>
                    <div class="jp-horario-levels__eyebrow">Niveles acad&eacute;micos</div>
                    <h6 class="mb-1">Selecciona el nivel</h6>
                    <p class="mb-0 text-muted">Accede r&aacute;pido a la configuraci&oacute;n de cada jornada.</p>
                </div>
            </div>
            <div class="jp-horario-chooser">
                @forelse ($horarios as $horario)
                    @php
                        $inicio = \Illuminate\Support\Carbon::parse($horario->hora_inicio);
                        $duracion = (int) $horario->duracion_clase_min;
                        $recreo = (int) $horario->recreo_min;
                        $clases = (int) $horario->clases_por_dia;
                        $recreoDespues = (int) ($horario->recreo_despues_de ?? 0);
                        $fin = $inicio->copy();

                        for ($i = 1; $i <= $clases; $i++) {
                            $fin->addMinutes($duracion);
                            if ($recreo > 0 && $recreoDespues === $i) {
                                $fin->addMinutes($recreo);
                            }
                        }
                        $nivelInicial = strtoupper(substr($horario->nivel, 0, 1));
                    @endphp
                    <article class="jp-horario-level" data-level="{{ $horario->nivel }}">
                        <div class="jp-horario-level__head">
                            <span class="jp-horario-level__badge">{{ $horario->nivel }}</span>
                            <div class="jp-horario-level__icon">{{ $nivelInicial }}</div>
                        </div>
                        <div class="jp-horario-level__time">
                            {{ $inicio->format('H:i') }} - {{ $fin->format('H:i') }}
                            <small>{{ $clases }} clases &bull; {{ $duracion }} min c/u</small>
                        </div>
                        <div class="jp-horario-level__meta">
                            <span>Recreo: <strong>{{ $recreo }} min</strong></span>
                            @if ($recreo > 0 && $recreoDespues)
                                <span>Recreo tras clase <strong>{{ $recreoDespues }}</strong></span>
                            @endif
                        </div>
                        <div class="jp-horario-level__footer">
                            <a class="btn jp-horario-btn"
                                href="{{ route($rp . 'horarios.index', ['nivel' => $horario->nivel]) }}">
                                Configurar horario
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="text-muted">No hay niveles configurados.</div>
                @endforelse
            </div>
        </div>
    @else
        <div class="d-flex justify-content-end mt-3">
            <a href="{{ route($rp . 'horarios.index') }}" class="btn btn-outline-secondary">
                Volver a niveles
            </a>
        </div>
        <div class="card jp-soft-card wizard-top mt-3">
            <div class="card-body">
                <div class="wizard-steps wizard-steps--horario">
                    <div class="wizard-progress">
                        <span style="width: {{ $paso === 1 ? '50%' : '100%' }}"></span>
                    </div>
                    <div class="wizard-step {{ $paso === 1 ? 'is-active' : '' }}">
                        <span class="wizard-step__circle">1</span>
                        <span>Configuraci&oacute;n</span>
                    </div>
                    <div class="wizard-step {{ $paso === 2 ? 'is-active' : '' }}">
                        <span class="wizard-step__circle">2</span>
                        <span>Programaci&oacute;n</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-1">
            @forelse ($horarios as $horario)
            @php
                $inicio = \Illuminate\Support\Carbon::parse($horario->hora_inicio);
                $duracion = (int) $horario->duracion_clase_min;
                $recreo = (int) $horario->recreo_min;
                $clases = (int) $horario->clases_por_dia;
                $recreoDespues = (int) ($horario->recreo_despues_de ?? 0);
                $slots = [];
                $cursor = $inicio->copy();

                for ($i = 1; $i <= $clases; $i++) {
                    $finClase = $cursor->copy()->addMinutes($duracion);
                    $slots[] = [
                        'tipo' => 'Clase ' . $i,
                        'inicio' => $cursor->format('H:i'),
                        'fin' => $finClase->format('H:i'),
                    ];
                    $cursor = $finClase;

                    if ($recreo > 0 && $recreoDespues === $i) {
                        $finRecreo = $cursor->copy()->addMinutes($recreo);
                        $slots[] = [
                            'tipo' => 'Recreo',
                            'inicio' => $cursor->format('H:i'),
                            'fin' => $finRecreo->format('H:i'),
                        ];
                        $cursor = $finRecreo;
                    }
                }
                $finJornada = $cursor->format('H:i');
                $dias = ['Lunes', 'Martes', 'Mi&eacute;rcoles', 'Jueves', 'Viernes'];
                $diasProgramacion = [
                    ['key' => 'Lunes', 'label' => 'Lunes'],
                    ['key' => 'Martes', 'label' => 'Martes'],
                    ['key' => 'Miercoles', 'label' => 'Mi&eacute;rcoles'],
                    ['key' => 'Jueves', 'label' => 'Jueves'],
                    ['key' => 'Viernes', 'label' => 'Viernes'],
                ];
            @endphp
            @if ($paso === 1)
                <div class="col-12">
                    <div class="card jp-soft-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $horario->nivel }}</h6>
                                <small class="text-muted">Jornada: {{ $inicio->format('H:i') }} - {{ $finJornada }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $clases }} clases</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route($rp . 'horarios.update', $horario) }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Hora inicio</label>
                                        <input type="time" name="hora_inicio" class="form-control"
                                            value="{{ old('hora_inicio', \Illuminate\Support\Carbon::parse($horario->hora_inicio)->format('H:i')) }}"
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Duraci&oacute;n clase (min)</label>
                                        <input type="number" name="duracion_clase_min" class="form-control" min="30" max="180"
                                            value="{{ old('duracion_clase_min', $horario->duracion_clase_min) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Clases por d&iacute;a</label>
                                        <input type="number" name="clases_por_dia" class="form-control" min="1" max="8"
                                            value="{{ old('clases_por_dia', $horario->clases_por_dia) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Recreo (min)</label>
                                        <input type="number" name="recreo_min" class="form-control" min="0" max="120"
                                            value="{{ old('recreo_min', $horario->recreo_min) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Recreo despu&eacute;s de</label>
                                        <input type="number" name="recreo_despues_de" class="form-control" min="1" max="7"
                                            value="{{ old('recreo_despues_de', $horario->recreo_despues_de) }}"
                                            placeholder="Ej: 2">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn jp-btn-search w-100">
                                            Guardar cambios
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-4">
                                <div class="fw-bold mb-2">Vista previa del horario</div>
                                <div class="table-responsive">
                                    <table class="table table-sm jp-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Bloque</th>
                                                <th>Inicio</th>
                                                <th>Fin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($slots as $slot)
                                                <tr>
                                                    <td>{{ $slot['tipo'] }}</td>
                                                    <td>{{ $slot['inicio'] }}</td>
                                                    <td>{{ $slot['fin'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="fw-bold mb-2">Vista semanal</div>
                                <div class="table-responsive">
                                    <table class="table table-sm jp-table jp-horario-grid mb-0">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                @foreach ($dias as $dia)
                                                    <th>{!! $dia !!}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($slots as $slot)
                                                <tr>
                                                    <td class="time-cell">
                                                        {{ $slot['inicio'] }} - {{ $slot['fin'] }}
                                                        <small>{{ $slot['tipo'] }}</small>
                                                    </td>
                                                    @foreach ($dias as $dia)
                                                        <td>
                                                            @if ($slot['tipo'] === 'Recreo')
                                                                <span class="jp-horario-chip">Recreo</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <a href="{{ route($rp . 'horarios.index', ['nivel' => $nivel, 'paso' => 2]) }}"
                                    class="btn btn-outline-primary">
                                    Continuar a programaci&oacute;n
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($paso === 2)
                <div class="col-12">
                    <div class="card jp-soft-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="mb-0">Programaci&oacute;n de cursos</h6>
                            <small class="text-muted">Selecciona cursos por bloque y d&iacute;a.</small>
                        </div>
                        <a href="{{ route($rp . 'horarios.index', ['nivel' => $nivel, 'paso' => 1]) }}"
                            class="btn btn-sm btn-outline-secondary">
                            Volver a configuraci&oacute;n
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route($rp . 'horarios.index') }}"
                            class="row g-3 align-items-end">
                            <input type="hidden" name="nivel" value="{{ $nivel }}">
                            <input type="hidden" name="paso" value="2">
                            <div class="col-md-4">
                                <label class="form-label">A&ntilde;o escolar</label>
                                <select name="anio_escolar_id" class="form-select">
                                    @foreach ($anios as $anioItem)
                                        <option value="{{ $anioItem->id }}" @selected($anio?->id === $anioItem->id)>
                                            {{ $anioItem->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Periodo acad&eacute;mico</label>
                                <select name="periodo_academico_id" class="form-select">
                                    @forelse ($periodos as $periodoItem)
                                        <option value="{{ $periodoItem->id }}" @selected($periodo?->id === $periodoItem->id)>
                                            {{ $periodoItem->codigo }} - {{ $periodoItem->nombre }}
                                        </option>
                                    @empty
                                        <option value="">Sin periodos</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Secci&oacute;n</label>
                                <select name="seccion_id" class="form-select">
                                    <option value="">Seleccione una secci&oacute;n</option>
                                    @foreach ($secciones as $seccionItem)
                                        <option value="{{ $seccionItem->id }}" @selected($seccion?->id === $seccionItem->id)>
                                            {{ $seccionItem->grado?->nombre ?? '-' }} - {{ $seccionItem->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-primary">Cargar horario</button>
                            </div>
                        </form>

                        @if (!$anio)
                            <div class="alert alert-info mt-3 mb-0">
                                No hay a&ntilde;os escolares disponibles.
                            </div>
                        @elseif (!$periodo)
                            <div class="alert alert-warning mt-3 mb-0">
                                No hay periodos acad&eacute;micos configurados para este a&ntilde;o.
                            </div>
                        @elseif (!$seccion)
                            <div class="alert alert-info mt-3 mb-0">
                                Selecciona una secci&oacute;n para comenzar a programar el horario.
                            </div>
                        @elseif ($cursos->isEmpty())
                            <div class="alert alert-warning mt-3 mb-0">
                                Esta secci&oacute;n no tiene cursos asignados para el periodo seleccionado.
                            </div>
                        @else
                            <form method="POST" action="{{ route($rp . 'horarios.programar') }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="nivel" value="{{ $nivel }}">
                                <input type="hidden" name="anio_escolar_id" value="{{ $anio->id }}">
                                <input type="hidden" name="periodo_academico_id" value="{{ $periodo->id }}">
                                <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">

                                <div class="table-responsive">
                                    <table class="table table-sm jp-table jp-horario-grid mb-0">
                                        <thead>
                                            <tr>
                                                <th>Bloque</th>
                                                @foreach ($diasProgramacion as $dia)
                                                    <th>{!! $dia['label'] !!}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($slots as $slot)
                                                <tr>
                                                    <td class="time-cell">
                                                        {{ $slot['inicio'] }} - {{ $slot['fin'] }}
                                                        <small>{{ $slot['tipo'] }}</small>
                                                    </td>
                                                    @foreach ($diasProgramacion as $dia)
                                                        <td>
                                                            @if ($slot['tipo'] === 'Recreo')
                                                                <span class="jp-horario-chip">Recreo</span>
                                                            @else
                                                                @php
                                                                    $slotKey = $dia['key'] . '|' . $slot['inicio'];
                                                                    $horarioAsignado = $horariosAsignados->get($slotKey);
                                                                @endphp
                                                                <div class="d-grid gap-1">
                                                                    <select name="horarios[{{ $dia['key'] }}][{{ $slot['inicio'] }}]"
                                                                        class="form-select form-select-sm">
                                                                        <option value="">Libre</option>
                                                                        @foreach ($cursos as $curso)
                                                                            <option value="{{ $curso->id }}"
                                                                                @selected($horarioAsignado?->curso_id == $curso->id)>
                                                                                {{ $curso->nombre }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="docentes[{{ $dia['key'] }}][{{ $slot['inicio'] }}]"
                                                                        class="form-select form-select-sm">
                                                                        <option value="">Docente (autom&aacute;tico)</option>
                                                                        @foreach ($docentes as $docente)
                                                                            <option value="{{ $docente->id }}"
                                                                                @selected($horarioAsignado?->docente_id == $docente->id)>
                                                                                {{ $docente->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                    <small class="text-muted">
                                        Los docentes se toman de la asignaci&oacute;n de cursos del periodo.
                                    </small>
                                    <div class="d-flex gap-2 no-print">
                                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                            Imprimir horario
                                        </button>
                                        <button type="submit" class="btn jp-btn-search">Guardar horario</button>
                                    </div>
                                </div>
                            </form>

                            <div class="print-only mt-4">
                                <div class="mb-2">
                                    <strong>{{ config('app.name', 'R.A.S') }}</strong>
                                    <div class="text-muted">
                                        Horario: {{ $nivel }} |
                                        A&ntilde;o {{ $anio->nombre }} |
                                        Periodo {{ $periodo->codigo }} - {{ $periodo->nombre }} |
                                        Secci&oacute;n {{ $seccion->grado?->nombre ?? '-' }} - {{ $seccion->nombre }}
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm jp-table jp-horario-grid mb-0">
                                        <thead>
                                            <tr>
                                                <th>Bloque</th>
                                                @foreach ($diasProgramacion as $dia)
                                                    <th>{!! $dia['label'] !!}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($slots as $slot)
                                                <tr>
                                                    <td class="time-cell">
                                                        {{ $slot['inicio'] }} - {{ $slot['fin'] }}
                                                        <small>{{ $slot['tipo'] }}</small>
                                                    </td>
                                                    @foreach ($diasProgramacion as $dia)
                                                        <td>
                                                            @if ($slot['tipo'] === 'Recreo')
                                                                Recreo
                                                            @else
                                                                @php
                                                                    $slotKey = $dia['key'] . '|' . $slot['inicio'];
                                                                    $horarioAsignado = $horariosAsignados->get($slotKey);
                                                                @endphp
                                                                {{ $horarioAsignado?->curso?->nombre ?? 'Libre' }}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                </div>
            @endif
        @empty
            <div class="col-12">
                <div class="alert alert-warning">
                    No hay horarios configurados. Ejecuta las migraciones para cargar valores iniciales.
                </div>
            </div>
        @endforelse
        </div>
    @endif
@endsection

@push('scripts')
    @if ($paso === 2 && isset($cursos) && $cursos->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const cursoDocenteMap = @json(
                    $cursos->mapWithKeys(function ($curso) {
                        return [$curso->id => $curso->pivot?->docente_id];
                    })
                );

                const courseSelects = document.querySelectorAll('select[name^="horarios["]');
                courseSelects.forEach(function (cursoSelect) {
                    const container = cursoSelect.closest('.d-grid') || cursoSelect.parentElement;
                    const docenteSelect = container ? container.querySelector('select[name^="docentes["]') : null;
                    if (!docenteSelect) {
                        return;
                    }

                    const syncDocente = function () {
                        const cursoId = cursoSelect.value;
                        if (!cursoId) {
                            docenteSelect.value = '';
                            return;
                        }
                        const docenteId = cursoDocenteMap[cursoId];
                        docenteSelect.value = docenteId ? String(docenteId) : '';
                    };

                    // Auto-fill on load only if no docente selected
                    if (!docenteSelect.value && cursoSelect.value) {
                        const docenteId = cursoDocenteMap[cursoSelect.value];
                        if (docenteId) {
                            docenteSelect.value = String(docenteId);
                        }
                    }

                    cursoSelect.addEventListener('change', syncDocente);
                });
            });
        </script>
    @endif
@endpush
