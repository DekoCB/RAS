@extends('layouts.mantis')

@php
    $isAdmin = auth()->user()->hasRole('SuperAdmin');
    $rp = $isAdmin ? 'admin-sistema.' : 'director.';
    $periodoLabel = $periodo ? $periodo->codigo . ' - ' . $periodo->nombre : '';
    $pageTitle = $periodoLabel ? 'Horario ' . $periodoLabel : 'Horario';
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-director')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @endif
    <style>
        .jp-search-card.compact .card-body {
            padding: 16px 18px;
        }
        .jp-search-card.compact .form-label {
            font-size: .82rem;
            margin-bottom: 4px;
        }
        .jp-search-card.compact .form-select,
        .jp-search-card.compact .form-control {
            min-height: 36px;
            padding: .35rem .65rem;
            font-size: .9rem;
        }
        .jp-search-card.compact .jp-btn-search {
            padding: .45rem .9rem;
            height: 36px;
            font-size: .92rem;
        }
        .jp-search-card.compact .form-grid {
            display: grid !important;
            gap: 12px 16px;
        }
        .jp-search-card.compact .form-grid.form-grid-4 {
            grid-template-columns: repeat(5, minmax(160px, 1fr)) !important;
            align-items: end;
        }
        .jp-search-card.compact .form-grid.form-grid-4 > div {
            min-width: 0;
        }
        @media (max-width: 1200px) {
            .jp-search-card.compact .form-grid.form-grid-4 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .jp-search-card.compact .form-grid.form-grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 576px) {
            .jp-search-card.compact .form-grid.form-grid-4 {
                grid-template-columns: 1fr;
            }
        }
        .print-only {
            display: none;
        }
        .print-schedule {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .print-schedule__day {
            font-weight: 700;
            text-align: center;
            font-size: 12px;
            color: #374151;
            margin-bottom: 6px;
        }
        .print-schedule__col {
            border: 1px solid #cbd5f5;
            min-height: 320px;
            padding: 10px;
        }
        .print-schedule__event {
            border: 1px dashed #9ca3af;
            border-radius: 8px;
            padding: 8px;
            font-size: 11px;
            margin-bottom: 8px;
            line-height: 1.35;
        }
        .print-schedule__event--recreo {
            border-style: solid;
            background: #c7effe;
            border-color: #404c9b;
        }
        .print-schedule__event:last-child {
            margin-bottom: 0;
        }
        .print-schedule__event strong {
            display: block;
            font-size: 11px;
        }
        .print-schedule__event small {
            display: block;
            color: #4b5563;
        }
        .print-section + .print-section {
            margin-top: 18px;
        }
        .print-section__title {
            font-weight: 700;
            margin-bottom: 8px;
            color: #111427;
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
            .footer {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .print-schedule {
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 8px;
            }
            .print-schedule__col {
                border-color: #9ca3af;
                padding: 8px;
            }
            .print-schedule__event {
                page-break-inside: avoid;
            }
            .jp-docente-filter,
            .jp-search-card,
            .jp-soft-card .table-responsive,
            .jp-soft-card .text-muted,
            .page-header,
            .pc-footer {
                display: none !important;
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
    <div class="card jp-docente-filter mt-2">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Horario {{ $periodoLabel ?: '' }}</h5>
                <p class="jp-docente-filter-sub">
                    Filtra por a&ntilde;o, periodo y secci&oacute;n.
                </p>
                <div class="jp-docente-filter-meta">
                    <span class="jp-docente-filter-pill">
                        <i data-feather="calendar"></i>
                        A&ntilde;o {{ $anio?->nombre ?? date('Y') }}
                    </span>
                    @if ($periodo)
                        <span class="jp-docente-filter-pill is-accent">
                            <i data-feather="bookmark"></i>
                            {{ $periodo->codigo }} - {{ $periodo->nombre }}
                        </span>
                    @endif
                    @if ($anio && $periodo)
                        <span class="jp-docente-filter-pill is-success">
                            <i data-feather="layers"></i>
                            Secciones con horario: {{ $seccionesConHorario }}
                        </span>
                        <span class="jp-docente-filter-pill is-muted">
                            <i data-feather="list"></i>
                            Registros: {{ $horarios->count() }}
                        </span>
                    @endif
                </div>
            </div>
            @if ($anio && $periodo)
                <div class="d-flex gap-2 flex-wrap no-print">
                    <button type="button" class="jp-docente-history-btn"
                        onclick="window.print()">
                        <i data-feather="printer"></i>
                        Imprimir horario
                    </button>
                    <a class="jp-docente-history-btn is-success"
                        href="{{ route($rp . 'horarios.lista.excel', request()->query()) }}">
                        <i data-feather="download"></i>
                        Descargar Excel
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="card jp-search-card compact mt-3 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route($rp . 'horarios.lista') }}" class="form-grid form-grid-4">
                <div>
                    <label class="form-label">A&ntilde;o escolar</label>
                    <select name="anio_escolar_id" class="form-select">
                        @foreach ($anios as $anioItem)
                            <option value="{{ $anioItem->id }}" @selected($anio?->id === $anioItem->id)>
                                {{ $anioItem->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
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
                <div>
                    <label class="form-label">Nivel</label>
                    <select name="nivel" class="form-select">
                        <option value="">Todos</option>
                        <option value="Inicial" @selected($nivel === 'Inicial')>Inicial</option>
                        <option value="Primaria" @selected($nivel === 'Primaria')>Primaria</option>
                        <option value="Secundaria" @selected($nivel === 'Secundaria')>Secundaria</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Secci&oacute;n</label>
                    <select name="seccion_id" class="form-select">
                        <option value="">Todas las secciones</option>
                        @foreach ($secciones as $seccionItem)
                            <option value="{{ $seccionItem->id }}" @selected($seccion?->id === $seccionItem->id)>
                                {{ $seccionItem->grado?->nivel ?? '-' }} - {{ $seccionItem->grado?->nombre ?? '-' }} -
                                {{ $seccionItem->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-end">
                    <button type="submit" class="btn jp-btn-search w-100">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            @if ($anio && $periodo)
                <div class="print-only mb-3">
                    <div style="font-size: 26px; font-weight: 800; letter-spacing: 0.08em; margin-bottom: 6px; text-align: center;">
                        HORARIO
                    </div>
                    <strong style="display: block; text-align: center;">Horario {{ $periodoLabel ?: '' }}</strong>
                    <div class="text-muted" style="text-align: center;">
                        A&ntilde;o {{ $anio->nombre }}
                        | Periodo {{ $periodo->codigo }} - {{ $periodo->nombre }}
                        @if ($seccion)
                            | Secci&oacute;n {{ $seccion->grado?->nombre ?? '-' }} - {{ $seccion->nombre }}
                        @endif
                    </div>
                </div>
            @endif
            @if ($horarios->isEmpty())
                <div class="text-muted">
                    No hay horarios registrados para los filtros seleccionados.
                </div>
            @else
                @php
                    $diasOrden = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
                    $diasLabel = [
                        'Lunes' => 'Lunes',
                        'Martes' => 'Martes',
                        'Miercoles' => 'Mi&eacute;rcoles',
                        'Jueves' => 'Jueves',
                        'Viernes' => 'Viernes',
                    ];
                    $horariosPorSeccion = $horarios->groupBy('seccion_id');
                @endphp
                @foreach ($horariosPorSeccion as $seccionId => $items)
                    @php
                        $seccionActual = $items->first()?->seccion;
                        $seccionLabel = trim(
                            ($seccionActual?->grado?->nivel ?? '-') . ' ' .
                            ($seccionActual?->grado?->nombre ?? '-') . ' - ' .
                            ($seccionActual?->nombre ?? '-')
                        );
                        $nivelHorario = $seccionActual?->grado?->nivel;
                        $horarioNivel = $nivelHorario ? $horariosEscolares->get($nivelHorario) : null;
                        $recreoSlot = null;

                        if ($horarioNivel && (int) $horarioNivel->recreo_min > 0) {
                            $inicio = \Illuminate\Support\Carbon::parse($horarioNivel->hora_inicio);
                            $duracion = (int) $horarioNivel->duracion_clase_min;
                            $recreo = (int) $horarioNivel->recreo_min;
                            $clases = (int) $horarioNivel->clases_por_dia;
                            $recreoDespues = (int) $horarioNivel->recreo_despues_de;
                            if ($recreoDespues <= 0) {
                                $recreoDespues = min(2, max($clases - 1, 1));
                            }
                            $cursor = $inicio->copy();

                            for ($i = 1; $i <= $clases; $i++) {
                                $finClase = $cursor->copy()->addMinutes($duracion);
                                if ($recreoDespues === $i) {
                                    $finRecreo = $finClase->copy()->addMinutes($recreo);
                                    $recreoSlot = [
                                        'inicio' => $finClase->format('H:i'),
                                        'fin' => $finRecreo->format('H:i'),
                                    ];
                                    break;
                                }
                                $cursor = $finClase;
                            }
                        }

                        $horariosPorDia = $items->groupBy('dia_semana');
                    @endphp
                    <div class="print-section">
                        <div class="print-section__title">Secci&oacute;n: {{ $seccionLabel }}</div>
                        <div class="print-schedule">
                            @foreach ($diasOrden as $diaKey)
                                <div>
                                    <div class="print-schedule__day">{!! $diasLabel[$diaKey] !!}</div>
                                    <div class="print-schedule__col">
                                        @php
                                            $eventos = ($horariosPorDia[$diaKey] ?? collect())
                                                ->map(fn($item) => [
                                                    'tipo' => 'Clase',
                                                    'inicio' => $item->hora_inicio,
                                                    'fin' => $item->hora_fin,
                                                    'item' => $item,
                                                ])
                                                ->values();

                                            if (!empty($recreoSlot)) {
                                                $eventos->push([
                                                    'tipo' => 'Recreo',
                                                    'inicio' => $recreoSlot['inicio'],
                                                    'fin' => $recreoSlot['fin'],
                                                ]);
                                            }

                                            $eventos = $eventos->sortBy('inicio');
                                        @endphp
                                        @foreach ($eventos as $evento)
                                            @if ($evento['tipo'] === 'Recreo')
                                                <div class="print-schedule__event print-schedule__event--recreo">
                                                    <strong>Recreo</strong>
                                                    <small>
                                                        {{ \Illuminate\Support\Carbon::parse($evento['inicio'])->format('g:ia') }}
                                                        -
                                                        {{ \Illuminate\Support\Carbon::parse($evento['fin'])->format('g:ia') }}
                                                    </small>
                                                </div>
                                            @else
                                                @php $item = $evento['item']; @endphp
                                                <div class="print-schedule__event">
                                                    <strong>{{ $item->curso?->nombre ?? '-' }}</strong>
                                                    <small>
                                                        {{ \Illuminate\Support\Carbon::parse($item->hora_inicio)->format('g:ia') }}
                                                        -
                                                        {{ \Illuminate\Support\Carbon::parse($item->hora_fin)->format('g:ia') }}
                                                    </small>
                                                    <small>{{ $item->docente?->name ?? 'Sin asignar' }}</small>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
