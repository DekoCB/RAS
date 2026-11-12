@extends('layouts.mantis')

@section('title', 'Horario | Docente')
@section('page-title', 'Horario')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
    <style>
        .print-only {
            display: none;
        }
        .jp-schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
        }
        .jp-schedule-col {
            border: 1px solid #e0eaef;
            border-radius: 16px;
            padding: 12px;
            background: #fff;
            min-height: 240px;
        }
        .jp-schedule-day {
            font-weight: 800;
            color: #11194a;
            margin-bottom: 8px;
            text-align: center;
        }
        .jp-schedule-event {
            border: 1px dashed #cbd5f5;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 0.85rem;
            line-height: 1.35;
            background: #f9fdff;
        }
        .jp-schedule-event strong {
            display: block;
            font-weight: 800;
            color: #111427;
        }
        .jp-schedule-event small {
            display: block;
            color: #4b5563;
        }
        .jp-schedule-event:last-child {
            margin-bottom: 0;
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
            .page-header,
            .pc-footer {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .jp-table th,
            .jp-table td {
                font-size: 12px;
                padding: 6px;
            }
            .jp-schedule-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 8px;
            }
            .jp-schedule-col {
                border-color: #9ca3af;
                padding: 8px;
            }
            .jp-schedule-event {
                page-break-inside: avoid;
                border-color: #9ca3af;
            }
        }
    </style>
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-docente-filter mt-2 no-print">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Horario del docente</h5>
                <p class="jp-docente-filter-sub">
                    Consulta tu horario por d&iacute;a y secci&oacute;n.
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
                    <span class="jp-docente-filter-pill">
                        <i data-feather="sun"></i>
                        Turno Ma&ntilde;ana
                    </span>
                </div>
            </div>
            @if ($horarios->isNotEmpty())
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="jp-docente-history-btn" onclick="window.print()">
                        <i data-feather="printer"></i>
                        Imprimir horario
                    </button>
                    <a class="jp-docente-history-btn is-success"
                        href="{{ route('docente.horarios.excel', request()->query()) }}">
                        <i data-feather="download"></i>
                        Descargar Excel
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="card jp-search-card mt-3 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('docente.horarios.index') }}" class="row g-3 align-items-end">
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
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn jp-btn-search w-100">Cargar horario</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            @if ($horarios->isNotEmpty())
                <div class="print-only mb-3">
                    <div style="font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 6px;">
                        HORARIO DEL DOCENTE
                    </div>
                    <div style="text-align: center;">
                        <strong>{{ auth()->user()->name ?? 'Docente' }}</strong>
                        <div class="text-muted">
                            A&ntilde;o {{ $anio?->nombre ?? date('Y') }}
                            @if ($periodo)
                                | Periodo {{ $periodo->codigo }} - {{ $periodo->nombre }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @php
                $diasOrden = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
                $diasLabel = [
                    'Lunes' => 'Lunes',
                    'Martes' => 'Martes',
                    'Miercoles' => 'Mi&eacute;rcoles',
                    'Jueves' => 'Jueves',
                    'Viernes' => 'Viernes',
                ];
            @endphp
            @if ($horarios->isEmpty())
                <div class="text-muted">No hay horarios asignados para este docente.</div>
            @else
                <div class="jp-schedule-grid">
                    @foreach ($diasOrden as $diaKey)
                        @php
                            $items = $horarios->get($diaKey, collect());
                        @endphp
                        <div class="jp-schedule-col">
                            <div class="jp-schedule-day">{!! $diasLabel[$diaKey] !!}</div>
                            @forelse ($items as $horario)
                                <div class="jp-schedule-event">
                                    <strong>{{ $horario->curso?->nombre ?? '-' }}</strong>
                                    <small>
                                        {{ \Illuminate\Support\Carbon::parse($horario->hora_inicio)->format('g:ia') }}
                                        -
                                        {{ \Illuminate\Support\Carbon::parse($horario->hora_fin)->format('g:ia') }}
                                    </small>
                                    <small>
                                        {{ $horario->seccion?->grado?->nivel ?? '-' }}
                                        {{ $horario->seccion?->grado?->nombre ?? '-' }}
                                        - {{ $horario->seccion?->nombre ?? '-' }}
                                    </small>
                                </div>
                            @empty
                                <div class="text-muted" style="text-align:center; font-size:0.85rem;">
                                    Sin clases
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
