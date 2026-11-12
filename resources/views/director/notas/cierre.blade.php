@extends('layouts.mantis')

@php $isAdmin = auth()->user()->hasRole('SuperAdmin'); $rp = $isAdmin ? 'admin-sistema.' : 'director.'; @endphp
@section('title', 'Cierre de Notas | ' . ($isAdmin ? 'SuperAdmin' : 'Director'))
@section('page-title', 'Cierre de Notas')
@section('body-class', $isAdmin ? 'role-superadmin' : 'role-director')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @endif
    <style>
        /* ── Variables institución ──────────────────────────── */
        :root {
            --cn-primario:  #1c297e;
            --cn-primario2: #213196;
            --cn-primario3: #23339e;
            --cn-grad:      linear-gradient(135deg, #1c297e 0%, #213196 60%, #23339e 100%);
            --cn-soft-bg:   #f5faff;
            --cn-border:    #d4ecfd;
            --cn-ink:       #1c1917;
            --cn-muted:     #78716c;
            --cn-shadow:    0 2px 12px rgba(28, 41, 126,.10);
            --cn-verde:     #2e7d32;
            --cn-amarillo:  #22329c;
            --cn-blue-sem:  #c62828;
        }

        /* ── Layout principal ──────────────────────────────── */
        .cn-wrapper { display: flex; flex-direction: column; gap: 1.25rem; }

        /* ── Selector de contexto ──────────────────────────── */
        .cn-selector {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--cn-shadow);
            border: 1.5px solid var(--cn-border);
            overflow: hidden;
        }
        .cn-selector-header {
            background: var(--cn-grad);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            color: #fff;
        }
        .cn-selector-header .cn-icon-wrap {
            width: 38px; height: 38px;
            background: rgba(255,255,255,.18);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .cn-selector-body { padding: 1.25rem 1.5rem; }

        /* ── Paso indicador ────────────────────────────────── */
        .cn-steps {
            display: flex;
            gap: 0;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            overflow: hidden;
            border: 1.5px solid var(--cn-border);
        }
        .cn-step {
            flex: 1;
            padding: .55rem .75rem;
            background: var(--cn-soft-bg);
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            color: #a8a29e;
            border-right: 1.5px solid var(--cn-border);
            transition: background .2s;
        }
        .cn-step:last-child { border-right: none; }
        .cn-step.active { background: #ecf6ff; color: var(--cn-primario); font-weight: 600; }
        .cn-step.done   { background: #e8f5e9; color: var(--cn-verde); }
        .cn-step-num {
            width: 22px; height: 22px;
            border-radius: 50%;
            background: #a8c0d6;
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .cn-step.active .cn-step-num { background: var(--cn-primario); }
        .cn-step.done   .cn-step-num { background: var(--cn-verde); }

        /* ── Semáforo global ───────────────────────────────── */
        .cn-semaforo-global {
            border-radius: 14px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            background: #fff;
            border: 1.5px solid var(--cn-border);
            box-shadow: var(--cn-shadow);
        }
        .cn-semaforo-dot {
            width: 56px; height: 56px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
        }
        .cn-semaforo-dot.verde    { background: #e8f5e9; }
        .cn-semaforo-dot.amarillo { background: #e1f6ff; }
        .cn-semaforo-dot.rojo     { background: #ebf2ff; }
        .cn-semaforo-info { flex: 1; min-width: 180px; }
        .cn-semaforo-pct {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }
        .cn-semaforo-pct.verde    { color: var(--cn-verde); }
        .cn-semaforo-pct.amarillo { color: var(--cn-amarillo); }
        .cn-semaforo-pct.rojo     { color: var(--cn-blue-sem); }
        .cn-progress-bar {
            height: 10px;
            border-radius: 8px;
            background: var(--cn-border);
            margin-top: .5rem;
            overflow: hidden;
        }
        .cn-progress-fill {
            height: 100%;
            border-radius: 8px;
            transition: width .6s ease;
        }
        .cn-progress-fill.verde    { background: linear-gradient(90deg,#43a047,#66bb6a); }
        .cn-progress-fill.amarillo { background: linear-gradient(90deg,#23339e,#2334a0); }
        .cn-progress-fill.rojo     { background: linear-gradient(90deg,#223194,#334199); }

        /* ── Tarjetas KPI ──────────────────────────────────── */
        .cn-kpi-row { display: flex; gap: .75rem; flex-wrap: wrap; }
        .cn-kpi {
            flex: 1;
            min-width: 110px;
            background: #fff;
            border-radius: 12px;
            border: 1.5px solid var(--cn-border);
            padding: .9rem 1rem;
            text-align: center;
            box-shadow: 0 1px 6px rgba(28, 41, 126,.06);
        }
        .cn-kpi-num { font-size: 1.7rem; font-weight: 800; line-height: 1; }
        .cn-kpi-lbl { font-size: .72rem; color: var(--cn-muted); margin-top: .2rem; text-transform: uppercase; letter-spacing: .04em; }
        .cn-kpi.kpi-esp  .cn-kpi-num { color: var(--cn-primario); }
        .cn-kpi.kpi-reg  .cn-kpi-num { color: var(--cn-verde); }
        .cn-kpi.kpi-falt .cn-kpi-num { color: var(--cn-blue-sem); }

        /* ── Tabla de detalle ──────────────────────────────── */
        .cn-table-wrap {
            background: #fff;
            border-radius: 14px;
            border: 1.5px solid var(--cn-border);
            overflow: hidden;
            box-shadow: var(--cn-shadow);
        }
        .cn-table-header {
            padding: .9rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid var(--cn-border);
            background: var(--cn-soft-bg);
        }
        .cn-table-header h6 { margin: 0; font-size: .92rem; font-weight: 700; color: var(--cn-ink); }
        .cn-table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--cn-muted); background: var(--cn-soft-bg); }
        .cn-table td { vertical-align: middle; font-size: .85rem; }

        /* ── Badges semáforo ───────────────────────────────── */
        .badge-verde    { background: #e8f5e9 !important; color: #2e7d32 !important; border: 1px solid #a5d6a7; }
        .badge-amarillo { background: #e1f6ff !important; color: #22329c !important; border: 1px solid #b4beff; }
        .badge-rojo     { background: #ebf2ff !important; color: #1d2b84 !important; border: 1px solid #bcc3ef; }
        .badge-estado { font-size: .73rem; font-weight: 600; padding: .3em .7em; border-radius: 6px; }

        /* ── Panel cerrado ─────────────────────────────────── */
        .cn-cerrado-banner {
            background: #e8f5e9;
            border: 1.5px solid #a5d6a7;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .cn-cerrado-banner.forzado {
            background: #e1f6ff;
            border-color: #b4beff;
        }
        .cn-cerrado-banner.abierto {
            background: #fff;
            border-color: var(--cn-border);
        }

        /* ── Botones de acción ─────────────────────────────── */
        .cn-actions { display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; }
        .btn-cerrar {
            background: var(--cn-grad);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .6rem 1.4rem;
            font-weight: 600;
            font-size: .87rem;
            display: flex; align-items: center; gap: .5rem;
            transition: opacity .2s, transform .1s;
            cursor: pointer;
        }
        .btn-cerrar:hover:not(:disabled) { opacity: .88; transform: translateY(-1px); color: #fff; }
        .btn-cerrar:disabled { opacity: .40; cursor: not-allowed; }
        .btn-forzar {
            background: linear-gradient(135deg,#151f5f,#1d2a82);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .6rem 1.4rem;
            font-weight: 600;
            font-size: .87rem;
            display: flex; align-items: center; gap: .5rem;
            transition: opacity .2s, transform .1s;
            cursor: pointer;
        }
        .btn-forzar:hover:not(:disabled) { opacity: .88; transform: translateY(-1px); color: #fff; }
        .btn-forzar:disabled { opacity: .40; cursor: not-allowed; }
        .btn-reabrir {
            background: #fff;
            color: var(--cn-verde);
            border: 1.5px solid var(--cn-verde);
            border-radius: 10px;
            padding: .6rem 1.4rem;
            font-weight: 600;
            font-size: .87rem;
            display: flex; align-items: center; gap: .5rem;
            transition: background .2s, transform .1s;
            cursor: pointer;
        }
        .btn-reabrir:hover:not(:disabled) { background: #e8f5e9; transform: translateY(-1px); color: var(--cn-verde); }
        .btn-reabrir:disabled { opacity: .40; cursor: not-allowed; }
        .btn-exportar {
            background: #fff;
            color: var(--cn-primario);
            border: 1.5px solid var(--cn-primario);
            border-radius: 10px;
            padding: .6rem 1.2rem;
            font-weight: 600;
            font-size: .87rem;
            display: flex; align-items: center; gap: .5rem;
            transition: background .2s, transform .1s;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-exportar:hover:not(:disabled) { background: #ecf6ff; transform: translateY(-1px); color: var(--cn-primario); }

        /* ── Loading spinner ───────────────────────────────── */
        .cn-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            padding: 2.5rem;
            color: var(--cn-muted);
            font-size: .92rem;
        }
        .cn-spinner {
            width: 30px; height: 30px;
            border: 3px solid var(--cn-border);
            border-top-color: var(--cn-primario);
            border-radius: 50%;
            animation: cn-spin .75s linear infinite;
        }
        @keyframes cn-spin { to { transform: rotate(360deg); } }

        /* ── Historial badges ──────────────────────────────── */
        .badge-cerrar        { background: #ecf6ff; color: #1c297e; border: 1px solid #b9c2fc; }
        .badge-cierre_forzado{ background: #e1f6ff; color: #1d2a82; border: 1px solid #abb6fd; }
        .badge-reabrir       { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-historial { font-size: .72rem; font-weight: 600; padding: .3em .7em; border-radius: 6px; }

        /* ── Responsive ────────────────────────────────────── */
        @media (max-width: 576px) {
            .cn-steps { display: none; }
            .cn-kpi-row { gap: .5rem; }
            .cn-kpi { min-width: 80px; }
            .cn-semaforo-global { flex-direction: column; text-align: center; }
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
<div class="cn-wrapper">

    {{-- ── Pasos de navegación ── --}}
    <div class="cn-steps" id="cnSteps">
        <div class="cn-step active" id="step1">
            <span class="cn-step-num">1</span> Seleccionar contexto
        </div>
        <div class="cn-step" id="step2">
            <span class="cn-step-num">2</span> Ver estado
        </div>
        <div class="cn-step" id="step3">
            <span class="cn-step-num">3</span> Faltantes
        </div>
        <div class="cn-step" id="step4">
            <span class="cn-step-num">4</span> Acción de cierre
        </div>
    </div>

    {{-- ── PASO 1: Selector de contexto ── --}}
    <div class="cn-selector">
        <div class="cn-selector-header">
            <div class="cn-icon-wrap">
                <i data-feather="filter" style="width:18px;height:18px;"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:.95rem;">Selector de Contexto</div>
                <div style="font-size:.78rem;opacity:.82;">Define el alcance del cierre de notas</div>
            </div>
        </div>
        <div class="cn-selector-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">
                        <i data-feather="calendar" style="width:13px;height:13px;margin-right:4px;"></i>
                        Año escolar
                    </label>
                    <select id="anioSelect" class="form-select form-select-sm">
                        <option value="">— Seleccione —</option>
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id }}">{{ $anio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">
                        <i data-feather="layers" style="width:13px;height:13px;margin-right:4px;"></i>
                        Periodo académico
                    </label>
                    <select id="periodoAcademicoSelect" class="form-select form-select-sm">
                        <option value="">— Seleccione año primero —</option>
                        @foreach ($periodosAcademicos as $pa)
                            <option value="{{ $pa->id }}" data-anio="{{ $pa->anio_escolar_id }}">
                                {{ $pa->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">
                        <i data-feather="award" style="width:13px;height:13px;margin-right:4px;"></i>
                        Evaluación
                    </label>
                    <select id="periodoSelect" class="form-select form-select-sm">
                        @foreach ($periodos as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">
                        <i data-feather="crosshair" style="width:13px;height:13px;margin-right:4px;"></i>
                        Alcance
                    </label>
                    <select id="alcanceSelect" class="form-select form-select-sm">
                        <option value="todos">Todos</option>
                        <option value="seccion">Por sección</option>
                        <option value="curso">Por curso</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 d-none" id="seccionWrap">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">
                        <i data-feather="users" style="width:13px;height:13px;margin-right:4px;"></i>
                        Sección
                    </label>
                    <select id="seccionSelect" class="form-select form-select-sm">
                        <option value="">— Todas —</option>
                        @foreach ($secciones as $s)
                            <option value="{{ $s->id }}" data-anio="{{ $s->anio_escolar_id }}">
                                {{ $s->grado?->nombre }} {{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 d-none" id="cursoWrap">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">
                        <i data-feather="book" style="width:13px;height:13px;margin-right:4px;"></i>
                        Curso
                    </label>
                    <select id="cursoSelect" class="form-select form-select-sm">
                        <option value="">— Todos —</option>
                        @foreach ($cursos as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 ms-auto">
                    <button class="btn btn-sm w-100 btn-cerrar" id="btnVerEstado"
                            style="background:linear-gradient(135deg,#1c297e,#213196);">
                        <i data-feather="search" style="width:14px;height:14px;"></i>
                        Ver estado
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PASO 2 + 4: Dashboard de estado y acciones ── --}}
    <div id="cnDashboard" class="d-none">

        {{-- Semáforo global + KPIs --}}
        <div class="cn-semaforo-global mb-3" id="cnSemaforoGlobal">
            <div class="cn-loading">
                <div class="cn-spinner"></div>
                Cargando estadísticas...
            </div>
        </div>

        {{-- Tabla de detalle por sección/curso --}}
        <div class="cn-table-wrap">
            <div class="cn-table-header">
                <h6><i data-feather="grid" style="width:15px;height:15px;margin-right:6px;"></i>Detalle por Sección / Curso</h6>
                <div class="d-flex gap-2">
                    <span class="badge badge-verde badge-estado"><span style="font-size:1em;">●</span> Completo</span>
                    <span class="badge badge-amarillo badge-estado"><span style="font-size:1em;">●</span> Casi listo</span>
                    <span class="badge badge-rojo badge-estado"><span style="font-size:1em;">●</span> Faltantes</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 cn-table" id="cnTablaDetalle">
                    <thead>
                        <tr>
                            <th>Sección</th>
                            <th>Curso</th>
                            <th>Docente</th>
                            <th class="text-center">Esperadas</th>
                            <th class="text-center">Registradas</th>
                            <th class="text-center">Faltantes</th>
                            <th class="text-center">% Completitud</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="cnTablaBody">
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <div class="cn-loading"><div class="cn-spinner"></div>Cargando...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Faltantes detallados --}}
        <div class="cn-table-wrap mt-3" id="cnFaltantesWrap" style="display:none!important;">
            <div class="cn-table-header">
                <h6>
                    <i data-feather="alert-circle" style="width:15px;height:15px;margin-right:6px;color:#1d2b84;"></i>
                    Notas Faltantes
                    <span class="badge bg-danger ms-2" id="cnFaltantesCount">0</span>
                </h6>
                <button class="btn btn-sm btn-outline-secondary" id="btnToggleFaltantes" style="font-size:.75rem;">
                    <i data-feather="eye" style="width:13px;height:13px;"></i> Ver detalle
                </button>
            </div>
            <div class="table-responsive" id="cnFaltantesTable" style="display:none;">
                <table class="table table-hover align-middle mb-0 cn-table">
                    <thead>
                        <tr>
                            <th>Sección</th>
                            <th>Curso</th>
                            <th>Alumno</th>
                            <th>Docente responsable</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="cnFaltantesBody"></tbody>
                </table>
            </div>
        </div>

        {{-- Panel de acciones --}}
        <div class="cn-table-wrap mt-3" id="cnPanelAcciones">
            <div class="cn-table-header">
                <h6><i data-feather="lock" style="width:15px;height:15px;margin-right:6px;"></i>Acciones de Cierre</h6>
            </div>
            <div class="p-3" id="cnAccionesBody">
                <div class="cn-loading"><div class="cn-spinner"></div>Cargando...</div>
            </div>
        </div>
    </div>

    {{-- ── Historial / Auditoría ── --}}
    <div class="cn-table-wrap">
        <div class="cn-table-header">
            <h6>
                <i data-feather="clock" style="width:15px;height:15px;margin-right:6px;"></i>
                Historial de Cierres y Reaperturas
            </h6>
            <button class="btn btn-sm btn-outline-primary" id="btnCargarHistorial" style="font-size:.75rem;">
                <i data-feather="refresh-cw" style="width:13px;height:13px;"></i> Cargar
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 cn-table">
                <thead>
                    <tr>
                        <th>Acción</th>
                        <th>Año</th>
                        <th>Periodo</th>
                        <th>Evaluación</th>
                        <th>Sección / Curso</th>
                        <th>Realizado por</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody id="cnHistorialBody">
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3" style="font-size:.85rem;">
                            Haz clic en "Cargar" para ver el historial
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ────────────────────────────────────────────────────────────────────────── --}}
{{-- MODALES                                                                    --}}
{{-- ────────────────────────────────────────────────────────────────────────── --}}

{{-- Modal: Confirmar cierre normal --}}
<div class="modal fade" id="modalCerrar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.2);">
            <div class="modal-header" style="background:linear-gradient(135deg,#1c297e,#213196);border-radius:14px 14px 0 0;border:none;">
                <h5 class="modal-title text-white fw-bold">
                    <i data-feather="lock" style="width:18px;height:18px;margin-right:8px;"></i>
                    Confirmar Cierre de Notas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert" style="background:#ecf6ff;border:1.5px solid #b9c2fc;border-radius:10px;">
                    <div class="fw-semibold mb-1" style="color:#1c297e;">¿Está seguro de cerrar las notas?</div>
                    <small class="text-muted">Una vez cerradas, los docentes no podrán modificar las calificaciones. Podrá reabrir el periodo si es necesario.</small>
                </div>
                <div id="cnResumenCierre" class="p-3 rounded" style="background:#f5faff;border:1.5px solid #d4ecfd;font-size:.85rem;"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-sm rounded-pill btn-cerrar" id="btnConfirmarCierre" style="background:linear-gradient(135deg,#1c297e,#213196);">
                    <i data-feather="lock" style="width:13px;height:13px;"></i> Sí, cerrar notas
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Cierre forzado --}}
<div class="modal fade" id="modalForzar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.2);">
            <div class="modal-header" style="background:linear-gradient(135deg,#213094,#223199);border-radius:14px 14px 0 0;border:none;">
                <h5 class="modal-title text-white fw-bold">
                    <i data-feather="alert-triangle" style="width:18px;height:18px;margin-right:8px;"></i>
                    Cierre Forzado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert" style="background:#e1f6ff;border:1.5px solid #b4beff;border-radius:10px;">
                    <div class="fw-semibold" style="color:#213094;">⚠️ Existen notas faltantes</div>
                    <small class="text-muted">El cierre forzado bloqueará las notas aunque no estén completas. Esta acción quedará registrada en el historial y requiere un motivo.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Motivo del cierre forzado <span class="text-danger">*</span></label>
                    <textarea id="motivoForzado" class="form-control" rows="3"
                              placeholder="Explique por qué se realiza el cierre con notas faltantes..."
                              style="border-radius:10px;font-size:.85rem;"></textarea>
                    <div class="invalid-feedback" id="motivoForzadoError">El motivo es obligatorio (mínimo 10 caracteres).</div>
                </div>
                <div id="cnFaltantesResumenForzado" class="p-2 rounded" style="background:#e1f6ff;border:1px solid #b4beff;font-size:.8rem;"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-sm rounded-pill btn-forzar" id="btnConfirmarForzado">
                    <i data-feather="alert-triangle" style="width:13px;height:13px;"></i> Confirmar cierre forzado
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Reabrir --}}
<div class="modal fade" id="modalReabrir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.2);">
            <div class="modal-header" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);border-radius:14px 14px 0 0;border:none;">
                <h5 class="modal-title text-white fw-bold">
                    <i data-feather="unlock" style="width:18px;height:18px;margin-right:8px;"></i>
                    Reabrir Periodo de Notas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert" style="background:#e8f5e9;border:1.5px solid #a5d6a7;border-radius:10px;">
                    <div class="fw-semibold" style="color:#1b5e20;">Reabrir periodo cerrado</div>
                    <small class="text-muted">Los docentes podrán volver a registrar o modificar notas. Esta acción queda registrada en el historial.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Motivo de reapertura <span class="text-danger">*</span></label>
                    <textarea id="motivoReabrir" class="form-control" rows="3"
                              placeholder="Ingrese el motivo de la reapertura (mínimo 10 caracteres)..."
                              style="border-radius:10px;font-size:.85rem;"></textarea>
                    <div class="invalid-feedback" id="motivoReabrirError">El motivo es obligatorio (mínimo 10 caracteres).</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-sm rounded-pill btn-reabrir" id="btnConfirmarReabrir" style="background:#2e7d32;color:#fff;border:none;">
                    <i data-feather="unlock" style="width:13px;height:13px;"></i> Confirmar reapertura
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast de notificaciones --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="cnToast" class="toast align-items-center border-0" role="alert" style="border-radius:12px;min-width:280px;">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="cnToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    // ── Estado global ─────────────────────────────────────────────────────────
    let currentStats    = null;
    let currentFaltantes = [];
    let isLoading       = false;

    // ── Helpers ───────────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const qs = sel => document.querySelector(sel);

    function ctx() {
        return {
            anio_escolar_id:      $('anioSelect').value,
            periodo_academico_id: $('periodoAcademicoSelect').value,
            periodo:              $('periodoSelect').value,
            alcance:              $('alcanceSelect').value,
            seccion_id:           $('seccionSelect')?.value || '',
            curso_id:             $('cursoSelect')?.value   || '',
        };
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function toast(msg, tipo = 'success') {
        const el  = $('cnToast');
        const msg_el = $('cnToastMsg');
        el.className = 'toast align-items-center border-0 text-bg-' + tipo;
        msg_el.textContent = msg;
        const t = new bootstrap.Toast(el, { delay: 4000 });
        t.show();
    }

    function spinnerHtml(txt = 'Cargando...') {
        return `<div class="cn-loading"><div class="cn-spinner"></div>${txt}</div>`;
    }

    async function fetchJson(url, method = 'GET', body = null) {
        const opts = {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        };
        if (body) opts.body = JSON.stringify(body);
        const r = await fetch(url, opts);
        const json = await r.json();
        if (!r.ok) throw json;
        return json;
    }

    // ── Filtrado de periodos académicos por año ────────────────────────────────
    function filtrarPeriodos() {
        const anioId = $('anioSelect').value;
        const opts   = $('periodoAcademicoSelect').querySelectorAll('option');
        let primero  = true;
        opts.forEach(o => {
            if (!o.value) return;
            const visible = o.dataset.anio === anioId;
            o.style.display = visible ? '' : 'none';
            if (visible && primero) { o.selected = true; primero = false; }
        });
    }

    // ── Filtrado de secciones por año ─────────────────────────────────────────
    function filtrarSecciones() {
        const anioId = $('anioSelect').value;
        const sel    = $('seccionSelect');
        if (!sel) return;
        sel.querySelectorAll('option').forEach(o => {
            if (!o.value) return;
            o.style.display = o.dataset.anio === anioId ? '' : 'none';
        });
    }

    // ── Mostrar/ocultar filtros de alcance ─────────────────────────────────────
    function actualizarAlcance() {
        const v = $('alcanceSelect').value;
        $('seccionWrap').classList.toggle('d-none', v !== 'seccion');
        $('cursoWrap').classList.toggle('d-none',   v !== 'curso');
    }

    // ── Activar paso ──────────────────────────────────────────────────────────
    function setStep(n) {
        for (let i = 1; i <= 4; i++) {
            const el = $('step' + i);
            if (!el) continue;
            el.classList.toggle('active', i === n);
            el.classList.toggle('done',   i < n);
        }
    }

    // ── Semáforo ──────────────────────────────────────────────────────────────
    function semaforoBadge(estado) {
        const map = { verde: 'verde', amarillo: 'amarillo', rojo: 'rojo' };
        const icons = { verde: '●', amarillo: '●', rojo: '●' };
        const labels = { verde: 'Completo', amarillo: 'Casi listo', rojo: 'Faltantes' };
        const e = map[estado] || 'rojo';
        return `<span class="badge badge-${e} badge-estado">${icons[e]} ${labels[e]}</span>`;
    }

    function semaforoIcon(estado) {
        return { verde: '✅', amarillo: '⚠️', rojo: '🔴' }[estado] || '🔴';
    }

    // ── Renderizar dashboard principal ────────────────────────────────────────
    function renderDashboard(stats) {
        currentStats = stats;
        $('cnDashboard').classList.remove('d-none');

        // Semáforo global
        const pct   = stats.porcentaje_completitud;
        const estado = stats.estado_global;
        const colorMap = { verde: '#2e7d32', amarillo: '#22329c', rojo: '#1d2b84' };
        const col = colorMap[estado] || '#1d2b84';

        $('cnSemaforoGlobal').innerHTML = `
            <div class="cn-semaforo-dot ${estado}">${semaforoIcon(estado)}</div>
            <div class="cn-semaforo-info">
                <div style="font-size:.78rem;color:#78909c;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Completitud global</div>
                <div class="cn-semaforo-pct ${estado}">${pct}%</div>
                <div class="cn-progress-bar mt-2">
                    <div class="cn-progress-fill ${estado}" style="width:${pct}%;"></div>
                </div>
                <div style="font-size:.78rem;color:#90a4ae;margin-top:.3rem;">${stats.total_registradas} de ${stats.total_esperadas} notas registradas</div>
            </div>
            <div class="cn-kpi-row">
                <div class="cn-kpi kpi-esp">
                    <div class="cn-kpi-num">${stats.total_esperadas}</div>
                    <div class="cn-kpi-lbl">Esperadas</div>
                </div>
                <div class="cn-kpi kpi-reg">
                    <div class="cn-kpi-num">${stats.total_registradas}</div>
                    <div class="cn-kpi-lbl">Registradas</div>
                </div>
                <div class="cn-kpi kpi-falt">
                    <div class="cn-kpi-num">${stats.total_faltantes}</div>
                    <div class="cn-kpi-lbl">Faltantes</div>
                </div>
            </div>
            ${stats.cerrado ? `
                <div style="flex-basis:100%;">
                    <div class="cn-cerrado-banner ${stats.es_forzado ? 'forzado' : ''}">
                        <span style="font-size:1.3rem;">${stats.es_forzado ? '⚠️' : '🔒'}</span>
                        <div>
                            <div class="fw-bold" style="font-size:.87rem;color:${stats.es_forzado?'#213094':'#1b5e20'};">
                                Periodo ${stats.es_forzado ? 'FORZADO' : 'CERRADO'}
                            </div>
                            <div style="font-size:.78rem;color:#78716c;">
                                Cerrado el ${stats.fecha_cierre} por <strong>${stats.cerrado_por || 'N/A'}</strong>
                                ${stats.motivo_cierre ? `— Motivo: ${stats.motivo_cierre}` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            ` : ''}
        `;

        // Tabla de detalle
        if (!stats.detalle || stats.detalle.length === 0) {
            $('cnTablaBody').innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No hay asignaciones de cursos para los filtros seleccionados.</td></tr>`;
        } else {
            $('cnTablaBody').innerHTML = stats.detalle.map(d => `
                <tr>
                    <td><span class="fw-semibold" style="font-size:.85rem;">${d.seccion}</span></td>
                    <td><span style="font-size:.84rem;">${d.curso}</span></td>
                    <td>
                        <span style="font-size:.82rem;color:#78716c;">
                            <i data-feather="user" style="width:12px;height:12px;"></i>
                            ${d.docente}
                        </span>
                    </td>
                    <td class="text-center"><span class="fw-bold" style="color:#1c297e;">${d.esperadas}</span></td>
                    <td class="text-center"><span class="fw-bold" style="color:#2e7d32;">${d.registradas}</span></td>
                    <td class="text-center">
                        ${d.faltantes > 0
                            ? `<span class="fw-bold" style="color:#1d2b84;">${d.faltantes}</span>`
                            : `<span style="color:#2e7d32;">—</span>`}
                    </td>
                    <td class="text-center">
                        <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;">
                            <div style="flex:1;max-width:80px;height:6px;background:#d4ecfd;border-radius:4px;overflow:hidden;">
                                <div style="width:${d.porcentaje}%;height:100%;background:${d.estado==='verde'?'#43a047':d.estado==='amarillo'?'#2334a0':'#223194'};border-radius:4px;"></div>
                            </div>
                            <span style="font-size:.82rem;font-weight:600;">${d.porcentaje}%</span>
                        </div>
                    </td>
                    <td class="text-center">${semaforoBadge(d.estado)}</td>
                </tr>
            `).join('');
            feather.replace();
        }

        // Panel de acciones
        renderAcciones(stats);
        setStep(stats.cerrado ? 4 : (stats.total_faltantes > 0 ? 3 : 4));
    }

    // ── Renderizar acciones ───────────────────────────────────────────────────
    function renderAcciones(stats) {
        const c = ctx();
        let html = '';

        if (stats.cerrado) {
            html = `
                <div class="cn-cerrado-banner ${stats.es_forzado ? 'forzado' : ''} mb-3">
                    <span style="font-size:1.5rem;">${stats.es_forzado ? '⚠️' : '🔒'}</span>
                    <div>
                        <div class="fw-bold" style="font-size:.95rem;">Periodo ${stats.es_forzado ? 'cerrado forzadamente' : 'cerrado correctamente'}</div>
                        <div style="font-size:.82rem;color:#78716c;">Los docentes no pueden modificar notas en este periodo.</div>
                    </div>
                </div>
                <div class="cn-actions">
                    <button class="btn-reabrir" onclick="abrirModalReabrir()">
                        <i data-feather="unlock" style="width:15px;height:15px;"></i> Reabrir periodo
                    </button>
                    <a href="${buildExportUrl(c, 'excel')}" class="btn-exportar text-decoration-none">
                        <i data-feather="file-text" style="width:15px;height:15px;"></i> Excel
                    </a>
                    <a href="${buildExportUrl(c, 'pdf')}" target="_blank" class="btn-exportar text-decoration-none">
                        <i data-feather="printer" style="width:15px;height:15px;"></i> PDF
                    </a>
                </div>
            `;
        } else if (stats.total_faltantes === 0) {
            html = `
                <div class="alert mb-3" style="background:#e8f5e9;border:1.5px solid #a5d6a7;border-radius:10px;">
                    <span style="font-size:1.1rem;">✅</span>
                    <span class="fw-semibold" style="color:#1b5e20;"> Todas las notas están registradas.</span>
                    El periodo está listo para cerrarse.
                </div>
                <div class="cn-actions">
                    <button class="btn-cerrar" onclick="abrirModalCerrar()">
                        <i data-feather="lock" style="width:15px;height:15px;"></i> Cerrar notas
                    </button>
                    <a href="${buildExportUrl(c, 'excel')}" class="btn-exportar text-decoration-none">
                        <i data-feather="file-text" style="width:15px;height:15px;"></i> Exportar Excel
                    </a>
                    <a href="${buildExportUrl(c, 'pdf')}" target="_blank" class="btn-exportar text-decoration-none">
                        <i data-feather="printer" style="width:15px;height:15px;"></i> Exportar PDF
                    </a>
                </div>
            `;
        } else {
            html = `
                <div class="alert mb-3" style="background:#e1f6ff;border:1.5px solid #b4beff;border-radius:10px;">
                    <span style="font-size:1.1rem;">⚠️</span>
                    <span class="fw-semibold" style="color:#213094;"> Hay ${stats.total_faltantes} nota(s) faltante(s).</span>
                    Puede registrarlas o proceder con un cierre forzado.
                </div>
                <div class="cn-actions">
                    <button class="btn-cerrar" disabled title="Complete todas las notas primero">
                        <i data-feather="lock" style="width:15px;height:15px;"></i> Cerrar notas
                    </button>
                    <button class="btn-forzar" onclick="abrirModalForzar()">
                        <i data-feather="alert-triangle" style="width:15px;height:15px;"></i> Cierre forzado
                    </button>
                    <a href="${buildExportUrl(c, 'excel')}" class="btn-exportar text-decoration-none">
                        <i data-feather="file-text" style="width:15px;height:15px;"></i> Exportar Excel
                    </a>
                </div>
            `;
        }

        $('cnAccionesBody').innerHTML = html;
        feather.replace();
    }

    function buildExportUrl(c, formato) {
        const p = new URLSearchParams({
            anio_escolar_id:      c.anio_escolar_id,
            periodo_academico_id: c.periodo_academico_id,
            periodo:              c.periodo,
            alcance:              c.alcance,
            seccion_id:           c.seccion_id,
            curso_id:             c.curso_id,
            formato,
        });
        return `{{ route($rp . 'notas.exportar') }}?${p.toString()}`;
    }

    // ── Cargar estadísticas ───────────────────────────────────────────────────
    async function cargarEstadisticas() {
        const c = ctx();
        if (!c.anio_escolar_id || !c.periodo_academico_id) {
            toast('Seleccione el año escolar y el periodo académico.', 'warning');
            return;
        }
        if (isLoading) return;
        isLoading = true;

        $('cnDashboard').classList.remove('d-none');
        $('cnSemaforoGlobal').innerHTML = spinnerHtml('Calculando estadísticas...');
        $('cnTablaBody').innerHTML      = `<tr><td colspan="8">${spinnerHtml()}</td></tr>`;
        $('cnAccionesBody').innerHTML   = spinnerHtml();
        $('cnFaltantesWrap').style.display = 'none';

        try {
            const stats = await fetchJson('{{ route($rp . 'notas.estadisticas') }}', 'POST', c);
            renderDashboard(stats);

            // Cargar faltantes si hay
            if (stats.total_faltantes > 0) {
                await cargarFaltantes();
            }
        } catch (e) {
            toast('Error al cargar estadísticas: ' + (e.message || 'Error desconocido'), 'danger');
            $('cnSemaforoGlobal').innerHTML = '<div class="p-3 text-danger">Error al cargar estadísticas.</div>';
        } finally {
            isLoading = false;
        }
    }

    // ── Cargar faltantes ──────────────────────────────────────────────────────
    async function cargarFaltantes() {
        const c = ctx();
        try {
            const data = await fetchJson('{{ route($rp . 'notas.faltantes') }}', 'POST', c);
            currentFaltantes = data.faltantes || [];
            renderFaltantes(currentFaltantes);
        } catch (e) {
            console.error('Error faltantes:', e);
        }
    }

    function renderFaltantes(faltantes) {
        const wrap = $('cnFaltantesWrap');
        if (!faltantes.length) {
            wrap.style.display = 'none';
            return;
        }
        wrap.style.removeProperty('display');
        $('cnFaltantesCount').textContent = faltantes.length;

        $('cnFaltantesBody').innerHTML = faltantes.map(f => `
            <tr>
                <td style="font-size:.84rem;font-weight:600;">${f.seccion}</td>
                <td style="font-size:.84rem;">${f.curso}</td>
                <td style="font-size:.84rem;">${f.estudiante}</td>
                <td style="font-size:.82rem;color:#78716c;">
                    <i data-feather="user" style="width:12px;height:12px;"></i> ${f.docente}
                </td>
                <td class="text-center">
                    <a href="{{ url('docente/notas/create') }}?seccion_id=${f.seccion_id}&curso_id=${f.curso_id}"
                       target="_blank"
                       class="btn btn-sm"
                       style="font-size:.75rem;background:#ecf6ff;color:#1c297e;border:1px solid #b9c2fc;border-radius:8px;padding:.25rem .7rem;">
                        <i data-feather="edit-2" style="width:11px;height:11px;"></i> Ir a registrar
                    </a>
                </td>
            </tr>
        `).join('');
        feather.replace();
    }

    // ── Acciones de cierre ────────────────────────────────────────────────────
    function abrirModalCerrar() {
        const c = ctx();
        const s = currentStats;
        $('cnResumenCierre').innerHTML = `
            <div class="row g-2">
                <div class="col-6"><strong>Año:</strong> ${$('anioSelect').options[$('anioSelect').selectedIndex]?.text}</div>
                <div class="col-6"><strong>Periodo:</strong> ${$('periodoAcademicoSelect').options[$('periodoAcademicoSelect').selectedIndex]?.text}</div>
                <div class="col-6"><strong>Evaluación:</strong> ${c.periodo}</div>
                <div class="col-6"><strong>Alcance:</strong> ${c.alcance}</div>
                <div class="col-12 mt-1"><strong>Notas a cerrar:</strong> ${s?.total_registradas || 0} notas</div>
            </div>
        `;
        new bootstrap.Modal($('modalCerrar')).show();
    }

    function abrirModalForzar() {
        const s = currentStats;
        $('cnFaltantesResumenForzado').innerHTML = `
            <strong>Faltantes detectados:</strong> ${s?.total_faltantes || 0} notas.<br>
            Estas notas quedarán sin valor registrado.
        `;
        $('motivoForzado').value = '';
        $('motivoForzado').classList.remove('is-invalid');
        new bootstrap.Modal($('modalForzar')).show();
    }

    function abrirModalReabrir() {
        $('motivoReabrir').value = '';
        $('motivoReabrir').classList.remove('is-invalid');
        new bootstrap.Modal($('modalReabrir')).show();
    }

    async function ejecutarCierre(esForzado = false) {
        const c      = ctx();
        const motivo = esForzado ? $('motivoForzado').value.trim() : '';

        if (esForzado) {
            if (motivo.length < 10) {
                $('motivoForzado').classList.add('is-invalid');
                return;
            }
            $('motivoForzado').classList.remove('is-invalid');
        }

        const btn = esForzado ? $('btnConfirmarForzado') : $('btnConfirmarCierre');
        btn.disabled = true;
        btn.innerHTML = '<div class="cn-spinner" style="width:16px;height:16px;border-width:2px;"></div> Procesando...';

        try {
            const res = await fetchJson('{{ route($rp . 'notas.cerrar') }}', 'POST', {
                ...c,
                es_forzado: esForzado,
                motivo: motivo || null,
            });

            bootstrap.Modal.getInstance($('modal' + (esForzado ? 'Forzar' : 'Cerrar')))?.hide();
            toast(res.message, 'success');
            await cargarEstadisticas();
        } catch (e) {
            toast(e.message || 'Error al cerrar notas.', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = esForzado
                ? '<i data-feather="alert-triangle" style="width:13px;height:13px;"></i> Confirmar cierre forzado'
                : '<i data-feather="lock" style="width:13px;height:13px;"></i> Sí, cerrar notas';
            feather.replace();
        }
    }

    async function ejecutarReabrir() {
        const motivo = $('motivoReabrir').value.trim();
        if (motivo.length < 10) {
            $('motivoReabrir').classList.add('is-invalid');
            return;
        }
        $('motivoReabrir').classList.remove('is-invalid');

        const btn = $('btnConfirmarReabrir');
        btn.disabled = true;
        btn.innerHTML = '<div class="cn-spinner" style="width:16px;height:16px;border-width:2px;"></div> Procesando...';

        try {
            const res = await fetchJson('{{ route($rp . 'notas.reabrir') }}', 'POST', {
                ...ctx(),
                motivo,
            });
            bootstrap.Modal.getInstance($('modalReabrir'))?.hide();
            toast(res.message, 'success');
            await cargarEstadisticas();
        } catch (e) {
            toast(e.message || 'Error al reabrir.', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-feather="unlock" style="width:13px;height:13px;"></i> Confirmar reapertura';
            feather.replace();
        }
    }

    // ── Historial ─────────────────────────────────────────────────────────────
    async function cargarHistorial() {
        const c = ctx();
        $('cnHistorialBody').innerHTML = `<tr><td colspan="8">${spinnerHtml()}</td></tr>`;

        try {
            const params = new URLSearchParams({
                anio_escolar_id:      c.anio_escolar_id,
                periodo_academico_id: c.periodo_academico_id,
                periodo:              c.periodo,
            });
            const data = await fetchJson(`{{ route($rp . 'notas.historial') }}?${params}`);
            const hist = data.historial || [];

            if (!hist.length) {
                $('cnHistorialBody').innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3" style="font-size:.85rem;">No hay registros de historial para los filtros seleccionados.</td></tr>`;
                return;
            }

            const badgeMap = {
                cerrar:         'cerrar',
                cierre_forzado: 'cierre_forzado',
                reabrir:        'reabrir',
            };

            $('cnHistorialBody').innerHTML = hist.map(h => `
                <tr>
                    <td>
                        <span class="badge badge-historial badge-${badgeMap[h.accion] || 'cerrar'}">
                            ${({'cierre_forzado':'⚠️','cerrar':'🔒','reabrir':'🔓'}[h.accion]||'?')}
                            ${h.accion_label}
                        </span>
                    </td>
                    <td style="font-size:.83rem;">${h.anio || '—'}</td>
                    <td style="font-size:.83rem;">${h.periodo_academico || '—'}</td>
                    <td style="font-size:.83rem;"><strong>${h.periodo}</strong></td>
                    <td style="font-size:.83rem;">${h.seccion} / ${h.curso}</td>
                    <td style="font-size:.83rem;">
                        <i data-feather="user" style="width:11px;height:11px;"></i> ${h.realizado_por}
                    </td>
                    <td style="font-size:.8rem;color:#78716c;max-width:180px;">
                        ${h.motivo ? `<span title="${h.motivo}">${h.motivo.substring(0,60)}${h.motivo.length>60?'…':''}</span>` : '<span class="text-muted">—</span>'}
                    </td>
                    <td style="font-size:.8rem;white-space:nowrap;">${h.fecha}</td>
                </tr>
            `).join('');
            feather.replace();
        } catch (e) {
            $('cnHistorialBody').innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">Error al cargar historial.</td></tr>`;
        }
    }

    // ── Toggle faltantes ──────────────────────────────────────────────────────
    function toggleFaltantes() {
        const tbl = $('cnFaltantesTable');
        const btn = $('btnToggleFaltantes');
        const visible = tbl.style.display !== 'none';
        tbl.style.display = visible ? 'none' : '';
        btn.innerHTML = visible
            ? '<i data-feather="eye" style="width:13px;height:13px;"></i> Ver detalle'
            : '<i data-feather="eye-off" style="width:13px;height:13px;"></i> Ocultar';
        feather.replace();
    }

    // ── Event listeners ───────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        feather.replace();

        $('anioSelect').addEventListener('change', () => {
            filtrarPeriodos();
            filtrarSecciones();
        });
        $('alcanceSelect').addEventListener('change', actualizarAlcance);
        $('btnVerEstado').addEventListener('click', cargarEstadisticas);
        $('btnCargarHistorial').addEventListener('click', cargarHistorial);
        $('btnToggleFaltantes')?.addEventListener('click', toggleFaltantes);
        $('btnConfirmarCierre').addEventListener('click', () => ejecutarCierre(false));
        $('btnConfirmarForzado').addEventListener('click', () => ejecutarCierre(true));
        $('btnConfirmarReabrir').addEventListener('click', ejecutarReabrir);

        // Disparar filtro inicial
        filtrarPeriodos();
        filtrarSecciones();
        actualizarAlcance();

        // Auto-seleccionar primer año si solo hay uno
        const anioOpts = $('anioSelect').querySelectorAll('option[value]');
        if (anioOpts.length === 1) {
            $('anioSelect').value = anioOpts[0].value;
            filtrarPeriodos();
            filtrarSecciones();
        }
    });

    // Exponer funciones al scope global para onclick
    window.abrirModalCerrar  = abrirModalCerrar;
    window.abrirModalForzar  = abrirModalForzar;
    window.abrirModalReabrir = abrirModalReabrir;

})();
</script>
@endpush
