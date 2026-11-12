@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp

@section('title', 'Incidencias | Secretaria')
@section('page-title', 'Incidencias')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
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
        $gravedadClasses = [
            'Leve' => 'jp-incidencia-pill--leve',
            'Moderada' => 'jp-incidencia-pill--moderada',
            'Grave' => 'jp-incidencia-pill--grave',
            'Critica' => 'jp-incidencia-pill--critica',
        ];
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card jp-soft-card jp-incidencia-hero">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1">Incidencias</h4>
                        <p class="mb-0 text-muted">Registro y seguimiento de incidencias reportadas.</p>
                    </div>
                    <button type="button" class="btn jp-btn-incidencia" data-bs-toggle="modal" data-bs-target="#jpIncidenciaModal">
                        <i data-feather="plus-circle" class="me-1"></i>
                        Registrar incidencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card jp-incidencia-filter">
                <div class="card-body">
                    <form method="GET" action="{{ route($rp . 'incidencias.index') }}" class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Alumno (DNI o nombre)</label>
                            <div class="input-group jp-incidencia-input">
                                <span class="input-group-text"><i data-feather="search"></i></span>
                                <input type="text" name="q" class="form-control"
                                    placeholder="Buscar por DNI o nombre" value="{{ request('q') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>
                                        {{ $tipo === 'Academica' ? 'Acad&eacute;mica' : $tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label">Gravedad</label>
                            <select name="gravedad" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($gravedades as $gravedad)
                                    <option value="{{ $gravedad }}" @selected(request('gravedad') === $gravedad)>
                                        {{ $gravedad === 'Critica' ? 'Cr&iacute;tica' : $gravedad }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>
                                        {{ ucfirst($estado) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <button type="submit" class="btn jp-btn-incidencia w-100">
                                <i data-feather="search" class="me-1"></i>
                                Buscar incidencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3 jp-incidencia-alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="row mt-3">
        <div class="col-12">
            <div class="card jp-soft-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table jp-table jp-students-table mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>DNI</th>
                                    <th>Alumno</th>
                                    <th>Tipo</th>
                                    <th>Gravedad</th>
                                    <th>Estado</th>
                                    <th>Reportado por</th>
                                    <th>Descripcion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($incidencias as $incidencia)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($incidencia->fecha_reporte)->format('d/m/Y') }}</td>
                                        <td>{{ $incidencia->estudiante->dni ?? '-' }}</td>
                                        <td>{{ $incidencia->estudiante->apellidos }}, {{ $incidencia->estudiante->nombres }}</td>
                                        <td>{{ $incidencia->tipo ?? '-' }}</td>
                                        <td>
                                            @php
                                                $gravedad = $incidencia->gravedad ?? '-';
                                                $gravedadClass = $gravedadClasses[$gravedad] ?? 'jp-incidencia-pill--neutral';
                                            @endphp
                                            <span class="jp-incidencia-pill {{ $gravedadClass }}">
                                                {{ $gravedad }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $estado = strtolower($incidencia->estado ?? 'abierta');
                                                $estadoClass = match ($estado) {
                                                    'abierta' => 'jp-incidencia-pill--abierta',
                                                    'cerrada', 'resuelta' => 'jp-incidencia-pill--cerrada',
                                                    default => 'jp-incidencia-pill--neutral',
                                                };
                                            @endphp
                                            <span class="jp-incidencia-pill {{ $estadoClass }}">
                                                {{ ucfirst($estado) }}
                                            </span>
                                        </td>
                                        <td>{{ data_get($incidencia, 'reportadoPor.name', '-') }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($incidencia->descripcion, 60) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay incidencias registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $incidencias->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Registrar Incidencia --}}
    <div class="modal fade" id="jpIncidenciaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable jp-inc-dialog">
            <div class="modal-content jp-inc-modal">

                {{-- Header --}}
                <div class="jp-inc-header">
                    <div class="jp-inc-header-icon">
                        <i data-feather="alert-triangle"></i>
                    </div>
                    <div class="jp-inc-header-text">
                        <h5>Registrar Incidencia</h5>
                        <small>Ingrese el DNI del alumno para continuar</small>
                    </div>
                    <button type="button" class="jp-inc-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i data-feather="x"></i>
                    </button>
                </div>

                <div class="jp-inc-body">
                    @if (!$anioActivoId)
                        <div class="alert alert-warning mb-3">
                            No hay un a&ntilde;o escolar registrado.
                        </div>
                    @endif

                    <form method="POST" action="{{ route($rp . 'incidencias.store') }}" id="jpIncidenciaForm">
                        @csrf
                        <input type="hidden" name="anio_escolar_id" value="{{ $anioActivoId }}">
                        <input type="hidden" name="fecha_reporte" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="estudiante_id" id="jpEstudianteIdHidden">

                        {{-- Datos de todos los estudiantes en JSON para búsqueda client-side --}}
                        @php
                            $estudiantesJson = $estudiantes->map(function($e) {
                                return ['id' => $e->id, 'dni' => $e->dni, 'nombres' => $e->nombres, 'apellidos' => $e->apellidos];
                            });
                        @endphp
                        <script id="jpEstudiantesData" type="application/json">
                            @json($estudiantesJson)
                        </script>

                        {{-- Paso 1: Búsqueda por DNI --}}
                        <div class="jp-inc-step-box">
                            <div class="jp-inc-step-title">
                                <span class="jp-inc-step-badge">1</span> Identificar Alumno
                            </div>
                                <div class="jp-inc-dni-row">
                                    <div class="jp-inc-dni-input-wrap">
                                        <i data-feather="credit-card" class="jp-inc-input-icon"></i>
                                        <input type="text" id="jpDniInput" class="jp-inc-dni-input"
                                            placeholder="N&uacute;mero de DNI (8 d&iacute;gitos)"
                                            maxlength="8" inputmode="numeric" autocomplete="off">
                                    </div>
                                    <button type="button" id="jpBuscarDniBtn" class="jp-inc-search-btn">
                                        <i data-feather="search"></i>
                                        Buscar
                                    </button>
                                </div>
                                <div id="jpDniError" class="jp-inc-feedback jp-inc-feedback--error d-none">
                                    <i data-feather="alert-circle"></i>
                                    <span id="jpDniErrorMsg"></span>
                                </div>
                                <div id="jpStudentCard" class="jp-inc-student-card d-none">
                                    <div class="jp-inc-student-avatar">
                                        <i data-feather="user"></i>
                                    </div>
                                    <div class="jp-inc-student-info">
                                        <div class="jp-inc-student-name" id="jpStudentName"></div>
                                        <div class="jp-inc-student-dni" id="jpStudentDniLabel"></div>
                                    </div>
                                    <div class="jp-inc-student-ok">
                                        <i data-feather="check-circle"></i>
                                    </div>
                                </div>
                        </div>

                        {{-- Separador --}}
                        <div class="jp-inc-divider" id="jpDivider" style="display:none;">
                            <span>completar formulario</span>
                        </div>

                        {{-- Paso 2: Datos de la incidencia (oculto hasta encontrar alumno) --}}
                        <div class="jp-inc-step-box" id="jpPaso2" style="display:none;">
                            <div class="jp-inc-step-title">
                                <span class="jp-inc-step-badge">2</span> Datos de la Incidencia
                            </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="jp-inc-label">
                                            <i data-feather="tag"></i> Tipo *
                                        </label>
                                        <div class="jp-inc-tipo-grid">
                                            @foreach ($tipos as $tipo)
                                                @php
                                                    $tipoIcon = match($tipo) {
                                                        'Disciplinaria' => 'shield',
                                                        'Conducta'      => 'frown',
                                                        'Academica'     => 'book-open',
                                                        'Convivencia'   => 'users',
                                                        default         => 'more-horizontal',
                                                    };
                                                @endphp
                                                <label class="jp-inc-tipo-option">
                                                    <input type="radio" name="tipo" value="{{ $tipo }}" required>
                                                    <span>
                                                        <i data-feather="{{ $tipoIcon }}"></i>
                                                        {{ $tipo === 'Academica' ? 'Académica' : $tipo }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="jp-inc-label">
                                            <i data-feather="thermometer"></i> Gravedad *
                                        </label>
                                        <div class="jp-inc-gravedad-list">
                                            @foreach ($gravedades as $gravedad)
                                                @php
                                                    $gClass = match($gravedad) {
                                                        'Leve'     => 'jp-inc-g--leve',
                                                        'Moderada' => 'jp-inc-g--moderada',
                                                        'Grave'    => 'jp-inc-g--grave',
                                                        'Critica'  => 'jp-inc-g--critica',
                                                        default    => '',
                                                    };
                                                @endphp
                                                <label class="jp-inc-gravedad-option {{ $gClass }}">
                                                    <input type="radio" name="gravedad" value="{{ $gravedad }}" required>
                                                    <span>{{ $gravedad === 'Critica' ? 'Crítica' : $gravedad }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="jp-inc-label">
                                            <i data-feather="file-text"></i> Descripci&oacute;n *
                                        </label>
                                        <textarea name="descripcion" class="jp-inc-textarea" rows="3"
                                            placeholder="Describa detalladamente la incidencia..." required></textarea>
                                    </div>
                                </div>
                        </div>

                        {{-- Botón submit --}}
                        <div id="jpSubmitWrap" style="display:none; margin-top: 1.25rem;">
                            <button type="submit" class="jp-inc-submit-btn" @disabled(!$anioActivoId)>
                                <i data-feather="save"></i>
                                Registrar Incidencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* ============================================
   JP Incidencia Modal — Diseño refinado
   ============================================ */

/* Animaciones */
@keyframes jpSlideUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes jpPulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(29, 42, 129,.4); }
    50%      { box-shadow: 0 0 0 6px rgba(29, 42, 129,0); }
}

/* Contenedor */
.jp-inc-dialog { max-width: 560px; margin: .75rem auto; }
@media (max-width: 576px) { .jp-inc-dialog { max-width: 96vw; margin: .5rem auto; } }

.jp-inc-modal {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(0,0,0,.22), 0 8px 20px rgba(0,0,0,.12);
}

/* ── Header ── */
.jp-inc-header {
    background: linear-gradient(135deg, #111a4f 0%, #172167 45%, #1d2a81 100%);
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    position: relative;
    overflow: hidden;
}
@media (min-width: 480px) { .jp-inc-header { padding: 1.4rem 1.6rem; gap: 1rem; } }
.jp-inc-header::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.jp-inc-header-icon {
    width: 50px; height: 50px; flex-shrink: 0;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
}
.jp-inc-header-icon svg { width:22px; height:22px; stroke:#fff; }
.jp-inc-header-text { flex: 1; }
.jp-inc-header-text h5 {
    margin: 0;
    color: #fff;
    font-weight: 700;
    font-size: .98rem;
    letter-spacing: -.01em;
}
.jp-inc-header-text small {
    color: rgba(255,255,255,.65);
    font-size: .73rem;
    letter-spacing: .02em;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}
@media (min-width: 480px) {
    .jp-inc-header-text h5  { font-size: 1.1rem; }
    .jp-inc-header-text small { max-width: none; white-space: normal; }
}
.jp-inc-close {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 10px;
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .2s, transform .15s;
    flex-shrink: 0;
}
.jp-inc-close:hover { background: rgba(255,255,255,.25); transform: scale(1.08); }
.jp-inc-close svg { width: 15px; height: 15px; stroke: #fff; }

/* ── Body ── */
.jp-inc-modal { display: flex; flex-direction: column; max-height: calc(100vh - 80px); }
.jp-inc-body {
    padding: 1.25rem;
    background: #f8f9fa;
    overflow-y: auto;
    flex: 1;
}
@media (min-width: 480px) { .jp-inc-body { padding: 1.6rem; } }

/* ── Steps ── */
.jp-inc-step-box {
    width: 100%;
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 14px;
    padding: .9rem 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    margin-bottom: 1rem;
    animation: jpSlideUp .3s ease both;
    box-sizing: border-box;
}
.jp-inc-step-title {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-weight: 700;
    font-size: .72rem;
    color: #999;
    margin-bottom: .85rem;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.jp-inc-step-badge {
    width: 22px; height: 22px; flex-shrink: 0;
    background: linear-gradient(135deg, #172167, #1d2a81);
    color: #fff; font-weight: 800; font-size: .7rem;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 6px rgba(29, 41, 116,.4);
}

/* ── DNI Search ── */
.jp-inc-dni-row { display: flex; gap: .6rem; flex-wrap: wrap; }
.jp-inc-dni-row .jp-inc-dni-input-wrap { min-width: 0; }
@media (max-width: 360px) {
    .jp-inc-dni-row { flex-direction: column; }
    .jp-inc-search-btn { width: 100%; justify-content: center; }
}
.jp-inc-dni-input-wrap { flex: 1; position: relative; }
.jp-inc-input-icon {
    position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px; stroke: #aaa; pointer-events: none;
}
.jp-inc-dni-input {
    width: 100%;
    padding: .75rem .9rem .75rem 2.5rem;
    border: 1.5px solid #e0e0e0;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 500;
    outline: none;
    background: #fafafa;
    transition: border-color .2s, box-shadow .2s;
    letter-spacing: .05em;
}
.jp-inc-dni-input:focus {
    border-color: #1d2a81;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(29, 42, 129,.1);
}
.jp-inc-search-btn {
    padding: .75rem 1.25rem;
    background: linear-gradient(135deg, #172167, #1d2a81);
    color: #fff; border: none; border-radius: 12px;
    font-size: .88rem; font-weight: 700;
    display: flex; align-items: center; gap: .45rem;
    cursor: pointer; white-space: nowrap;
    box-shadow: 0 4px 12px rgba(29, 41, 116,.35);
    transition: transform .15s, box-shadow .15s;
}
.jp-inc-search-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(29, 41, 116,.45);
}
.jp-inc-search-btn:active { transform: translateY(0); }
.jp-inc-search-btn svg { width: 15px; height: 15px; stroke: #fff; }

/* ── Feedback Error ── */
.jp-inc-feedback {
    display: flex; align-items: center; gap: .5rem;
    margin-top: .65rem;
    font-size: .83rem; border-radius: 10px;
    padding: .5rem .85rem;
}
.jp-inc-feedback svg { width: 14px; height: 14px; flex-shrink: 0; }
.jp-inc-feedback--error {
    background: #fff2f2;
    border: 1px solid #fcc;
    color: #c0392b;
}
.jp-inc-feedback--error svg { stroke: #c0392b; }

/* ── Student Card ── */
.jp-inc-student-card {
    margin-top: .75rem;
    background: linear-gradient(135deg, #f8fbff, #fff);
    border: 1.5px solid #c8d9f0;
    border-left: 4px solid #1d2a81;
    border-radius: 12px;
    padding: .85rem 1rem;
    display: flex; align-items: center; gap: .9rem;
    animation: jpSlideUp .3s ease;
}
.jp-inc-student-avatar {
    width: 46px; height: 46px; flex-shrink: 0;
    background: linear-gradient(135deg, #161f5f, #1d2a81);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 10px rgba(29, 41, 116,.35);
}
.jp-inc-student-avatar svg { width: 22px; height: 22px; stroke: #fff; }
.jp-inc-student-info { flex: 1; min-width: 0; }
.jp-inc-student-name {
    font-weight: 800; color: #1a1a1a;
    font-size: .95rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.jp-inc-student-dni {
    display: inline-flex; align-items: center; gap: .3rem;
    margin-top: .25rem;
    background: #e8ebf0; color: #172167;
    font-size: .72rem; font-weight: 700;
    padding: .15rem .5rem; border-radius: 20px;
    letter-spacing: .05em;
}
.jp-inc-student-ok {
    width: 32px; height: 32px; flex-shrink: 0;
    background: #e8f8ee;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.jp-inc-student-ok svg { width: 18px; height: 18px; stroke: #27ae60; }

/* ── Labels ── */
.jp-inc-label {
    display: flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 700; color: #888;
    margin-bottom: .6rem;
    text-transform: uppercase; letter-spacing: .08em;
}
.jp-inc-label svg { width: 12px; height: 12px; }

/* ── Tipo: pills responsivas ── */
.jp-inc-tipo-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .45rem;
}
@media (max-width: 480px) {
    .jp-inc-tipo-grid { grid-template-columns: repeat(3, 1fr); }
}
.jp-inc-tipo-option input { display: none; }
.jp-inc-tipo-option span {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: .3rem;
    padding: .6rem .4rem;
    border: 1.5px solid #e8e8e8;
    border-radius: 12px;
    font-size: .75rem; font-weight: 700; color: #777;
    cursor: pointer;
    background: #fafafa;
    transition: all .18s;
    text-align: center;
    line-height: 1.2;
}
.jp-inc-tipo-option span svg { width: 16px; height: 16px; }
.jp-inc-tipo-option span:hover {
    border-color: #1d2a81;
    color: #172167;
    background: #f5f9ff;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(29, 41, 116,.12);
}
.jp-inc-tipo-option input:checked + span {
    background: linear-gradient(135deg, #172167, #1d2a81);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 12px rgba(29, 41, 116,.4);
    transform: translateY(-1px);
}
.jp-inc-tipo-option input:checked + span svg { stroke: #fff; }

/* ── Gravedad: fila horizontal ── */
.jp-inc-gravedad-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .5rem;
}
@media (max-width: 380px) {
    .jp-inc-gravedad-list { grid-template-columns: repeat(2, 1fr); }
}
.jp-inc-gravedad-option input { display: none; }
.jp-inc-gravedad-option span {
    display: flex;
    align-items: center; justify-content: center;
    padding: .65rem .3rem;
    border-radius: 10px;
    border: 2px solid transparent;
    font-size: .82rem; font-weight: 800;
    cursor: pointer;
    transition: all .18s;
    text-align: center;
}
.jp-inc-g--leve span     { background: #f0faf1; color: #2d6a4f; border-color: #b7e4c7; }
.jp-inc-g--moderada span { background: #ebfaff; color: #17226a; border-color: #b8c1fd; }
.jp-inc-g--grave span    { background: #f1f7ff; color: #192471; border-color: #c8cffd; }
.jp-inc-g--critica span  {
    background: linear-gradient(135deg, #06081a, #111a4f);
    color: #d5e7ff; border-color: #161f5f;
}
.jp-inc-gravedad-option span:hover { transform: translateY(-2px); filter: brightness(1.05); }
.jp-inc-g--leve     input:checked + span { background: #2d6a4f; color: #fff; border-color: #1b4332; box-shadow: 0 3px 10px rgba(45,106,79,.4); }
.jp-inc-g--moderada input:checked + span { background: #17226a; color: #fff; border-color: #141e5d; box-shadow: 0 3px 10px rgba(23, 34, 106,.4); }
.jp-inc-g--grave    input:checked + span { background: #192471; color: #fff; border-color: #162165; box-shadow: 0 3px 10px rgba(25, 36, 113,.4); }
.jp-inc-g--critica  input:checked + span { background: #06081a; color: #d5e7ff; border-color: #2b3ba1; box-shadow: 0 3px 10px rgba(29, 43, 133,.5); animation: jpPulse 1.5s infinite; }
.jp-inc-gravedad-option input:checked + span { transform: translateY(-2px) scale(1.04); }

/* ── Textarea ── */
.jp-inc-textarea {
    width: 100%;
    padding: .75rem .9rem;
    border: 1.5px solid #e0e0e0;
    border-radius: 12px;
    font-size: .9rem;
    resize: vertical; outline: none;
    background: #fafafa;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
    min-height: 90px;
}
.jp-inc-textarea:focus {
    border-color: #1d2a81;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(29, 42, 129,.1);
}

/* ── Separador entre pasos ── */
.jp-inc-divider {
    display: flex; align-items: center; gap: .75rem;
    margin: .4rem 0;
    color: #ccc; font-size: .72rem;
}
.jp-inc-divider::before, .jp-inc-divider::after {
    content: ''; flex: 1; height: 1px; background: #ececec;
}

/* ── Submit ── */
.jp-inc-submit-btn {
    width: 100%; padding: .95rem;
    background: linear-gradient(135deg, #131c57 0%, #1d2974 50%, #1d2a81 100%);
    color: #fff; border: none; border-radius: 14px;
    font-size: 1rem; font-weight: 800; letter-spacing: .02em;
    display: flex; align-items: center; justify-content: center; gap: .6rem;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(29, 41, 116,.45);
    transition: transform .15s, box-shadow .15s;
}
.jp-inc-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(29, 41, 116,.5);
}
.jp-inc-submit-btn:active { transform: translateY(0); }
.jp-inc-submit-btn svg { width: 18px; height: 18px; stroke: #fff; }
.jp-inc-submit-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }
</style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const estudiantes = JSON.parse(document.getElementById('jpEstudiantesData').textContent);

            const dniInput       = document.getElementById('jpDniInput');
            const buscarBtn      = document.getElementById('jpBuscarDniBtn');
            const errorBox       = document.getElementById('jpDniError');
            const errorMsg       = document.getElementById('jpDniErrorMsg');
            const studentCard    = document.getElementById('jpStudentCard');
            const studentName    = document.getElementById('jpStudentName');
            const studentDniLbl  = document.getElementById('jpStudentDniLabel');
            const estudianteHidden = document.getElementById('jpEstudianteIdHidden');
            const paso2          = document.getElementById('jpPaso2');
            const submitWrap     = document.getElementById('jpSubmitWrap');
            const divider        = document.getElementById('jpDivider');

            function resetLookup() {
                errorBox.classList.add('d-none');
                studentCard.classList.add('d-none');
                paso2.style.display = 'none';
                submitWrap.style.display = 'none';
                divider.style.display = 'none';
                estudianteHidden.value = '';
            }

            function showError(msg) {
                errorMsg.textContent = msg;
                errorBox.classList.remove('d-none');
                studentCard.classList.add('d-none');
                paso2.style.display = 'none';
                submitWrap.style.display = 'none';
                divider.style.display = 'none';
                estudianteHidden.value = '';
                feather.replace();
            }

            function showStudent(est) {
                studentName.textContent = est.apellidos + ', ' + est.nombres;
                studentDniLbl.textContent = 'DNI: ' + est.dni;
                estudianteHidden.value = est.id;
                errorBox.classList.add('d-none');
                studentCard.classList.remove('d-none');
                divider.style.display = 'flex';
                paso2.style.display = 'block';
                submitWrap.style.display = 'block';
                feather.replace();
            }

            function buscar() {
                const dni = dniInput.value.trim();
                if (!/^\d{8}$/.test(dni)) {
                    showError('Ingrese exactamente 8 d\u00edgitos num\u00e9ricos.');
                    return;
                }
                const found = estudiantes.find(e => e.dni === dni);
                if (!found) {
                    showError('No se encontr\u00f3 ning\u00fan alumno con DNI ' + dni + '.');
                    return;
                }
                showStudent(found);
            }

            buscarBtn.addEventListener('click', buscar);
            dniInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); buscar(); }
            });
            dniInput.addEventListener('input', resetLookup);

            // Reset modal on close
            document.getElementById('jpIncidenciaModal').addEventListener('hidden.bs.modal', function () {
                dniInput.value = '';
                resetLookup();
                document.getElementById('jpIncidenciaForm').reset();
            });
        });
    </script>
@endpush
