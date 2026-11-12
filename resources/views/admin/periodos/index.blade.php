@extends('layouts.mantis')

@section('title', 'Periodos Académicos')
@section('page-title', 'Periodos Académicos')
@section('body-class', 'role-superadmin')

@push('styles')
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
<style>
/* ── Header ───────────────────────────────────────────── */
.per-header {
    background: linear-gradient(120deg, #162061 0%, #18246e 55%, #1c297e 100%);
    border-radius: 18px;
    padding: 1.4rem 1.8rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap;
    box-shadow: 0 12px 30px rgba(22, 32, 97,.28);
    margin-bottom: 1.5rem;
    position: relative; overflow: hidden;
}
.per-header::before {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.06); border-radius: 50%;
    pointer-events: none;
}
.per-header::after {
    content: '';
    position: absolute; right: 60px; bottom: -50px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.04); border-radius: 50%;
    pointer-events: none;
}
.per-header-left h4 {
    color: #fff; font-weight: 800; font-size: 1.2rem;
    margin: 0 0 .2rem; display: flex; align-items: center; gap: .5rem;
}
.per-header-left h4 i { width: 20px; height: 20px; }
.per-header-left p { color: rgba(255,255,255,.72); font-size: .82rem; margin: 0; }
.per-header-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(255,255,255,.16); border: 1.5px solid rgba(255,255,255,.3);
    border-radius: 999px; padding: .35rem .9rem;
    color: #fff; font-size: .8rem; font-weight: 700;
}
.per-header-pill i { width: 14px; height: 14px; }
.per-header-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .6rem 1.3rem;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.35);
    border-radius: 12px; color: #fff; font-weight: 700; font-size: .86rem;
    cursor: pointer; transition: all .2s ease; text-decoration: none;
}
.per-header-btn:hover { background: rgba(255,255,255,.28); color: #fff; transform: translateY(-1px); }
.per-header-btn i { width: 16px; height: 16px; }

/* ── Period cards grid ─────────────────────────────────── */
.per-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.2rem;
    margin-bottom: 1.5rem;
}

/* ── Period card ───────────────────────────────────────── */
.per-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e8ebf0;
    box-shadow: 0 4px 20px rgba(22, 32, 97,.07);
    overflow: hidden;
    display: flex; flex-direction: column;
    transition: transform .2s ease, box-shadow .2s ease;
}
.per-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(22, 32, 97,.13); }

.per-card-top {
    padding: 1.2rem 1.3rem 1rem;
    display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
    border-bottom: 1px solid #f2f7fe;
}
.per-card-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.per-card-icon i { width: 20px; height: 20px; }
.per-card-icon.is-vac { background: #edf8ff; color: #1f2e8e; }
.per-card-icon.is-acad { background: #f0fdf4; color: #15803d; }

.per-card-name { font-weight: 800; font-size: .95rem; color: #1e293b; margin: 0 0 .2rem; }
.per-card-code {
    display: inline-block;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 6px; padding: .1rem .5rem;
    font-size: .7rem; font-weight: 700; color: #64748b;
    font-family: monospace; letter-spacing: .03em;
}

/* ── Type badge ────────────────────────────────────────── */
.per-type-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .8rem; border-radius: 999px;
    font-size: .7rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
    white-space: nowrap;
}
.per-type-badge i { width: 11px; height: 11px; }
.per-type-badge.is-vac  { background: #edf8ff; color: #1f2e8e; border: 1px solid rgba(31, 46, 142,.25); }
.per-type-badge.is-acad { background: #f0fdf4; color: #15803d; border: 1px solid rgba(21,128,61,.25); }

/* ── Card body ─────────────────────────────────────────── */
.per-card-body { padding: 1rem 1.3rem; flex: 1; display: flex; flex-direction: column; gap: .75rem; }

.per-info-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; padding: .55rem .75rem;
    background: #f8fafc; border-radius: 10px;
}
.per-info-label {
    display: flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em;
}
.per-info-label i { width: 12px; height: 12px; }
.per-info-value { font-size: .83rem; font-weight: 700; color: #334155; text-align: right; }
.per-info-value.text-muted-sm { color: #94a3b8; font-weight: 500; font-style: italic; }

/* ── Status badge ──────────────────────────────────────── */
.per-status {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .7rem; border-radius: 999px;
    font-size: .72rem; font-weight: 800;
}
.per-status i { width: 11px; height: 11px; }
.per-status.is-active { background: #dcfce7; color: #15803d; border: 1px solid rgba(21,128,61,.2); }
.per-status.is-inactive { background: #f1f5f9; color: #64748b; border: 1px solid rgba(100,116,139,.2); }

/* ── Tarifa display ────────────────────────────────────── */
.per-tarifa-grid { display: flex; flex-direction: column; gap: .3rem; }
.per-tarifa-item {
    display: flex; align-items: center; justify-content: space-between;
    font-size: .78rem;
}
.per-tarifa-nivel { color: #64748b; }
.per-tarifa-monto { font-weight: 800; color: #1c297e; }

/* ── Card footer ───────────────────────────────────────── */
.per-card-footer {
    padding: .9rem 1.3rem;
    border-top: 1px solid #f2f7fe;
    display: flex; align-items: center; justify-content: flex-end; gap: .6rem;
}
.per-edit-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.1rem;
    background: linear-gradient(135deg, #1c297e, #1f2e8e);
    color: #fff; border: 0; border-radius: 10px;
    font-weight: 700; font-size: .82rem;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(28, 41, 126,.22);
    transition: all .18s ease;
}
.per-edit-btn:hover { opacity: .9; color: #fff; transform: translateY(-1px); }
.per-edit-btn i { width: 14px; height: 14px; }

/* ── Vacacional config btn ────────────────────────────── */
.per-config-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem 1rem;
    background: #edf8ff; border: 1.5px solid rgba(31, 46, 142,.3);
    color: #1f2e8e; border-radius: 10px;
    font-weight: 700; font-size: .78rem;
    text-decoration: none; transition: all .18s ease;
}
.per-config-btn:hover { background: #c7effe; color: #1b287b; }
.per-config-btn i { width: 13px; height: 13px; }

/* ── Bottom card ───────────────────────────────────────── */
.per-bottom-card {
    background: #fff; border-radius: 18px;
    border: 1px solid #e8ebf0;
    box-shadow: 0 4px 20px rgba(22, 32, 97,.06);
    padding: 1.2rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap;
}
.per-bottom-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #1c297e, #1f2e8e);
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
}
.per-bottom-icon i { width: 20px; height: 20px; }
.per-bottom-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1.3rem;
    background: linear-gradient(135deg, #1c297e, #1f2e8e);
    color: #fff; border: 0; border-radius: 12px;
    font-weight: 700; font-size: .86rem;
    text-decoration: none;
    box-shadow: 0 6px 16px rgba(28, 41, 126,.25);
    transition: all .18s ease;
}
.per-bottom-btn:hover { opacity: .9; color: #fff; transform: translateY(-1px); }
.per-bottom-btn i { width: 16px; height: 16px; }

/* ── Empty state ───────────────────────────────────────── */
.per-empty {
    text-align: center; padding: 3.5rem 1rem;
    color: #cbd5e1; background: #fff;
    border-radius: 18px; border: 1px solid #e8ebf0;
}
.per-empty i { width: 52px; height: 52px; opacity: .25; }
.per-empty p { margin-top: .75rem; font-size: .9rem; }
</style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $nombresMeses = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
@endphp

{{-- ── Header ──────────────────────────────────────────── --}}
<div class="per-header">
    <div class="per-header-left">
        <h4><i data-feather="calendar"></i> Periodos Académicos</h4>
        <p>Configura fechas, meses de pensiones y tarifa vacacional — Año {{ $anioActivo?->nombre ?? now()->year }}</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="per-header-pill">
            <i data-feather="layers"></i>
            {{ $periodos->count() }} periodo{{ $periodos->count() !== 1 ? 's' : '' }}
        </span>
        <a href="{{ route('admin-sistema.periodos.tarifa-vacacional') }}" class="per-header-btn">
            <i data-feather="settings"></i>
            Tarifa Vacacional
        </a>
    </div>
</div>

{{-- ── Alertas ──────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert d-flex align-items-center gap-2 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;color:#15803d;">
        <i data-feather="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert d-flex align-items-center gap-2 mb-3" style="background:#f5f9ff;border:1px solid #cae0fe;border-radius:12px;color:#1c297e;">
        <i data-feather="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
        {{ session('error') }}
    </div>
@endif

{{-- ── Cards de periodos ────────────────────────────────── --}}
@if($periodos->isEmpty())
    <div class="per-empty">
        <i data-feather="inbox"></i>
        <p>No hay periodos configurados para el año activo.</p>
    </div>
@else
    <div class="per-grid">
        @foreach($periodos as $p)
        @php
            [$mI, $mF] = $p->rangoMesesPensiones();
            [$fI, $fF] = $p->rangoFechasEfectivas();
            $usaFechasGuardadas = !empty($p->fecha_inicio) && !empty($p->fecha_fin);
            $mesesGenerables  = $p->es_vacacional ? [] : $p->mesesGenerablesPensiones();
            $mesesExcluidos   = $p->es_vacacional ? [] : $p->mesesExcluidosPensiones();
            $labelInicio = !empty($mesesGenerables) ? $nombresMeses[$mesesGenerables[0]] : $nombresMeses[$mI];
            $labelFin    = !empty($mesesGenerables) ? $nombresMeses[$mesesGenerables[count($mesesGenerables)-1]] : $nombresMeses[$mF];
            $isVac = $p->es_vacacional;
        @endphp
        <div class="per-card">

            {{-- Top --}}
            <div class="per-card-top">
                <div class="d-flex align-items-center gap-2">
                    <div class="per-card-icon {{ $isVac ? 'is-vac' : 'is-acad' }}">
                        <i data-feather="{{ $isVac ? 'sun' : 'book-open' }}"></i>
                    </div>
                    <div>
                        <p class="per-card-name">{{ $p->nombre }}</p>
                        <span class="per-card-code">{{ $p->codigo }}</span>
                    </div>
                </div>
                <span class="per-type-badge {{ $isVac ? 'is-vac' : 'is-acad' }}">
                    <i data-feather="{{ $isVac ? 'sun' : 'award' }}"></i>
                    {{ $isVac ? 'Vacacional' : 'Académico' }}
                </span>
            </div>

            {{-- Body --}}
            <div class="per-card-body">

                {{-- Fechas --}}
                <div class="per-info-row">
                    <span class="per-info-label"><i data-feather="calendar"></i> Fecha inicio</span>
                    <span class="per-info-value">
                        {{ $fI->format('d/m/Y') }}
                        @if(!$usaFechasGuardadas)
                            <span class="d-block" style="font-size:.68rem;color:#94a3b8;font-weight:500;">por defecto</span>
                        @endif
                    </span>
                </div>
                <div class="per-info-row">
                    <span class="per-info-label"><i data-feather="calendar"></i> Fecha fin</span>
                    <span class="per-info-value">
                        {{ $fF->format('d/m/Y') }}
                        @if(!$usaFechasGuardadas)
                            <span class="d-block" style="font-size:.68rem;color:#94a3b8;font-weight:500;">por defecto</span>
                        @endif
                    </span>
                </div>

                {{-- Meses pensiones --}}
                <div class="per-info-row">
                    <span class="per-info-label"><i data-feather="credit-card"></i> Meses pensión</span>
                    @if($isVac)
                        <span class="per-info-value text-muted-sm">N/A</span>
                    @else
                        <div class="text-end">
                            <span class="per-info-value">{{ $labelInicio }} → {{ $labelFin }}</span>
                            <span class="d-block" style="font-size:.7rem;color:#94a3b8;">{{ count($mesesGenerables) }} mes{{ count($mesesGenerables) !== 1 ? 'es' : '' }}</span>
                            @if(!empty($mesesExcluidos))
                                <span class="d-block" style="font-size:.68rem;color:#22329c;">
                                    Excl: {{ collect($mesesExcluidos)->map(fn($m) => $nombresMeses[$m] ?? $m)->implode(', ') }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Estado activo --}}
                <div class="per-info-row">
                    <span class="per-info-label"><i data-feather="zap"></i> Estado</span>
                    @if($p->activo)
                        <span class="per-status is-active"><i data-feather="check"></i> Activo</span>
                    @else
                        <span class="per-status is-inactive">Inactivo</span>
                    @endif
                </div>

                {{-- Tarifa vacacional (solo para vacacional) --}}
                @if($isVac)
                    <div class="per-info-row" style="flex-direction:column;align-items:flex-start;gap:.5rem;">
                        <span class="per-info-label"><i data-feather="dollar-sign"></i> Tarifa vacacional</span>
                        @if($tarifasVacacional->isNotEmpty())
                            <div class="per-tarifa-grid w-100">
                                @foreach(['Inicial','Primaria','Secundaria'] as $n)
                                    @if(isset($tarifasVacacional[$n]))
                                        <div class="per-tarifa-item">
                                            <span class="per-tarifa-nivel">{{ $n }}</span>
                                            <span class="per-tarifa-monto">S/ {{ number_format($tarifasVacacional[$n]->monto, 2) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <a href="{{ route('admin-sistema.periodos.tarifa-vacacional') }}" class="per-config-btn">
                                <i data-feather="plus"></i> Configurar tarifa
                            </a>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="per-card-footer">
                <a href="{{ route('admin-sistema.periodos.edit', $p) }}" class="per-edit-btn">
                    <i data-feather="edit-2"></i> Editar
                </a>
            </div>

        </div>
        @endforeach
    </div>
@endif

{{-- ── Bottom: Configurar tarifa --}}
<div class="per-bottom-card">
    <div class="d-flex align-items-center gap-3">
        <div class="per-bottom-icon">
            <i data-feather="settings"></i>
        </div>
        <div>
            <h6 class="fw-800 mb-1" style="color:#1e293b;font-size:.9rem;">Tarifa del Periodo Vacacional</h6>
            <p class="mb-0" style="font-size:.78rem;color:#94a3b8;">Configura el monto que se cobra por el periodo vacacional para cada nivel educativo.</p>
        </div>
    </div>
    <a href="{{ route('admin-sistema.periodos.tarifa-vacacional') }}" class="per-bottom-btn">
        <i data-feather="settings"></i> Configurar Tarifa Vacacional
    </a>
</div>

@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush
