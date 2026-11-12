@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Registrar Pago | Secretaria')
@section('page-title', 'Registrar Pago')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/plugins/flatpickr.min.css') }}">
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <style>
        /* ════════════════════════════════════════════════
           VARIABLES JP
        ════════════════════════════════════════════════ */
        :root {
            --pago-grad:    linear-gradient(135deg, #1c297e 0%, #213196 60%, #23339e 100%);
            --pago-blue:     #1c297e;
            --pago-sky:  #213196;
            --pago-cyan:   #22329c;
            --pago-soft:    #f5faff;
            --pago-border:  #d4ecfd;
            --pago-ink:     #1c1917;
            --pago-muted:   #78716c;
        }

        /* ── Wizad panels ──────────────────────────────── */
        .wizard-panel { display: none; }
        .wizard-panel.active { display: block; }

        /* ── Búsqueda de alumno ─────────────────────────── */
        #alumnoResults {
            position: absolute; z-index: 1050; width: 100%;
            max-height: 290px; overflow-y: auto;
            border: 1.5px solid var(--pago-border); border-radius: .75rem;
            background: #fff;
            box-shadow: 0 12px 32px rgba(28, 41, 126,.14);
        }
        #alumnoResults .result-item {
            padding: .7rem 1rem; cursor: pointer;
            border-bottom: 1px solid #eaf6fe; font-size: .875rem;
            transition: background .1s;
        }
        #alumnoResults .result-item:last-child { border-bottom: none; }
        #alumnoResults .result-item:hover { background: #f5faff; }
        #alumnoResults .result-item strong { color: var(--pago-blue); }

        /* ── Card del alumno ────────────────────────────── */
        .alumno-card {
            background: linear-gradient(135deg, #f5faff, #eaf6fe);
            border: 1.5px solid var(--pago-border); border-radius: 1rem;
        }
        .alumno-avatar {
            width: 52px; height: 52px; border-radius: 50%;
            background: var(--pago-grad);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.1rem; flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(28, 41, 126,.35);
        }

        /* ── Badge de paso ──────────────────────────────── */
        .step-pill {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--pago-grad);
            color: #fff; border-radius: 2rem;
            padding: .3rem .9rem .3rem .4rem;
            font-size: .78rem; font-weight: 700;
            box-shadow: 0 3px 10px rgba(28, 41, 126,.3);
        }
        .step-pill .step-num {
            width: 22px; height: 22px; border-radius: 50%;
            background: rgba(255,255,255,.25);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .8rem;
        }

        /* ── Separador de sección ───────────────────────── */
        .section-label {
            font-size: .72rem; font-weight: 700; letter-spacing: .08em;
            color: var(--pago-muted); text-transform: uppercase;
        }

        /* ── Quick chips ────────────────────────────────── */
        .qchip {
            display: inline-flex; align-items: center; gap: .45rem;
            border: 1.5px solid var(--pago-border); border-radius: 2rem;
            padding: .38rem 1rem; font-size: .82rem; font-weight: 600;
            cursor: pointer; transition: all .18s;
            background: #fff; color: var(--pago-ink);
            box-shadow: 0 1px 4px rgba(28, 41, 126,.06);
        }
        .qchip:hover {
            border-color: var(--pago-sky);
            background: #f5faff; color: var(--pago-blue);
        }
        .qchip.active {
            background: var(--pago-grad);
            border-color: transparent; color: #fff;
            box-shadow: 0 4px 14px rgba(28, 41, 126,.3);
        }

        /* ── Tarjeta de categoría ───────────────────────── */
        .cat-card {
            border-radius: .9rem; overflow: hidden;
            border: 1.5px solid var(--pago-border);
            box-shadow: 0 2px 12px rgba(28, 41, 126,.06);
            height: 100%;
        }
        .cat-header {
            padding: .9rem 1rem;
            display: flex; align-items: center; justify-content: space-between;
        }
        /* Matrícula — rojo */
        .cat-header.matricula {
            background: linear-gradient(135deg, #f2f7fe, #e2eefe);
            border-bottom: 1.5px solid #cae0fe;
        }
        /* Mensualidades — naranja */
        .cat-header.pension {
            background: linear-gradient(135deg, #edf8ff, #d5f0ff);
            border-bottom: 1.5px solid #ccd2fe;
        }
        .cat-header.otro {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-bottom: 1.5px solid #bfdbfe;
        }
        .cat-icon {
            width: 34px; height: 34px; border-radius: .5rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .cat-icon.matricula { background: #e2eefe; }
        .cat-icon.pension   { background: #d5f0ff; }
        .cat-icon.otro      { background: #dbeafe; }
        .cat-title { font-weight: 700; font-size: .88rem; color: var(--pago-ink); }
        .cat-subtotal { font-size: .78rem; font-weight: 700; color: var(--pago-muted); }
        .cat-toggle {
            font-size: .75rem; font-weight: 700;
            color: var(--pago-sky); border: none; background: none;
            padding: 0; cursor: pointer; transition: color .15s;
        }
        .cat-toggle:hover { color: var(--pago-blue); text-decoration: underline; }

        /* ── Fila de deuda ──────────────────────────────── */
        .deuda-row {
            display: flex; align-items: center; gap: .8rem;
            padding: .65rem 1rem; border-bottom: 1px solid #eaf6fe;
            cursor: pointer; transition: background .12s; user-select: none;
        }
        .deuda-row:last-child { border-bottom: none; }
        .deuda-row:hover  { background: #f5faff; }
        .deuda-row.sel    { background: #e8f5ff; }
        .deuda-chk {
            width: 18px; height: 18px; flex-shrink: 0;
            accent-color: var(--pago-sky); cursor: pointer;
        }
        .deuda-nombre { flex: 1; font-size: .855rem; font-weight: 500; color: #374151; }
        .deuda-monto  { font-size: .855rem; font-weight: 700; color: var(--pago-ink); white-space: nowrap; }
        .badge-venc   { font-size: .65rem; padding: .15em .5em; vertical-align: middle; }
        /* ── Input monto parcial ─────────────────────────── */
        .deuda-amount-inp {
            width: 96px; text-align: right; font-weight: 700; font-size: .84rem;
            border: 1.5px solid var(--pago-sky); border-radius: .45rem;
            padding: .18rem .45rem; color: var(--pago-blue); background: #f5faff;
            flex-shrink: 0;
        }
        .deuda-amount-inp:focus {
            outline: none; border-color: var(--pago-blue);
            box-shadow: 0 0 0 2px rgba(33, 49, 150,.15);
        }
        .deuda-saldo-ref {
            font-size: .73rem; color: var(--pago-muted); white-space: nowrap;
            text-decoration: line-through; flex-shrink: 0;
        }

        /* ── Barra flotante de total ─────────────────────── */
        #barraTotal { position: sticky; bottom: 1.25rem; z-index: 100; margin-top: 1.25rem; }
        .barra-inner {
            background: var(--pago-grad);
            color: #fff; border-radius: 1.1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 32px rgba(28, 41, 126,.45);
            display: flex; align-items: center; gap: 1rem;
        }
        .barra-icon {
            width: 46px; height: 46px; border-radius: .75rem;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; flex-shrink: 0;
        }
        .barra-label { font-size: .8rem; opacity: .85; font-weight: 500; }
        .barra-monto { font-size: 1.5rem; font-weight: 800; letter-spacing: -.5px; line-height: 1.1; }
        .barra-btn {
            background: #fff; color: var(--pago-blue); border: none;
            border-radius: .7rem; padding: .6rem 1.4rem;
            font-weight: 800; font-size: .875rem; cursor: pointer;
            transition: all .18s; white-space: nowrap; flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,.12);
        }
        .barra-btn:hover  { background: #f5faff; transform: translateY(-1px); }
        .barra-btn:active { transform: translateY(0); }

        /* ── Método de pago (multi) ─────────────────────── */
        .metodo-item {
            position: relative;
            border: 2px solid var(--pago-border); border-radius: .9rem;
            background: #fff; cursor: pointer; overflow: hidden;
            transition: border-color .2s, background .2s, box-shadow .2s, transform .15s;
            user-select: none;
            box-shadow: 0 1px 4px rgba(28, 41, 126,.05);
        }
        .metodo-item:hover {
            border-color: var(--pago-sky);
            background: #f5faff;
            box-shadow: 0 4px 16px rgba(33, 49, 150,.12);
            transform: translateY(-1px);
        }
        .metodo-item.selected {
            border-color: var(--pago-sky);
            background: linear-gradient(160deg, #f5faff 0%, #eaf6fe 100%);
            box-shadow: 0 4px 18px rgba(33, 49, 150,.22);
            transform: translateY(-1px);
        }
        /* check badge top-right */
        .metodo-item::after {
            content: '';
            position: absolute; top: .5rem; right: .5rem;
            width: 18px; height: 18px; border-radius: 50%;
            border: 2px solid var(--pago-border);
            background: #fff;
            transition: all .18s;
        }
        .metodo-item.selected::after {
            content: '✓';
            background: var(--pago-sky); border-color: var(--pago-sky);
            color: #fff; font-size: .62rem; font-weight: 900;
            display: flex; align-items: center; justify-content: center;
            line-height: 14px; text-align: center;
        }
        .metodo-head {
            padding: .85rem .5rem .65rem;
            text-align: center;
            font-size: .82rem; font-weight: 700;
            color: #6b7280; letter-spacing: .01em;
            transition: color .18s;
        }
        .metodo-item.selected .metodo-head { color: var(--pago-blue); }
        .metodo-item:hover .metodo-head     { color: var(--pago-sky); }
        .metodo-amount-row {
            border-top: 1.5px dashed var(--pago-border);
            padding: .55rem .65rem .6rem;
            background: rgba(245, 250, 255,.7);
            display: none;
            animation: slideDown .15s ease;
        }
        @keyframes slideDown {
            from { opacity:0; transform:translateY(-6px); }
            to   { opacity:1; transform:translateY(0);    }
        }
        .metodo-item.selected .metodo-amount-row { display: block; }
        .metodo-amount-label {
            font-size: .67rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: .06em; color: var(--pago-muted); margin-bottom: .3rem;
        }
        .metodo-amount-wrap {
            display: flex; align-items: center; gap: .35rem;
            background: #fff; border: 1.5px solid var(--pago-sky);
            border-radius: .55rem; padding: .22rem .5rem;
            box-shadow: 0 1px 4px rgba(33, 49, 150,.1);
        }
        .metodo-amount-prefix {
            font-size: .78rem; font-weight: 800; color: var(--pago-muted);
            flex-shrink: 0;
        }
        .metodo-amount-inp {
            flex: 1; text-align: right; font-weight: 800; font-size: .92rem;
            border: none; outline: none; background: transparent;
            color: var(--pago-blue); min-width: 0; padding: 0;
        }
        .metodo-op-wrap {
            display: flex; align-items: center; gap: .35rem; margin-top: .4rem;
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: .45rem; padding: .2rem .5rem;
        }
        .metodo-op-wrap i { font-size: .8rem; color: var(--pago-muted); flex-shrink:0; }
        .metodo-op-inp {
            flex: 1; font-size: .77rem; border: none; outline: none;
            background: transparent; color: #374151; min-width: 0; padding: 0;
        }
        .metodo-ico {
            display: flex; align-items: center; justify-content: center;
            margin-bottom: .4rem;
        }
        .metodo-ico-bubble {
            width: 48px; height: 48px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: transform .18s;
        }
        .metodo-item:hover .metodo-ico-bubble,
        .metodo-item.selected .metodo-ico-bubble { transform: scale(1.1); }
        .metodo-ico-bubble i { font-size: 1.5rem; }
        .metodo-ico-bubble img { width: 30px; height: 30px; object-fit: contain; border-radius: 50%; }
        .cat-icon i { font-size: 1.15rem; }
        /* Barra de distribución */
        .dist-bar {
            border-radius: .8rem; padding: .7rem 1rem; font-size: .84rem;
            border: 1.5px solid #86efac;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            transition: background .2s, border-color .2s;
            box-shadow: 0 2px 8px rgba(134,239,172,.25);
        }
        .dist-bar.warn   { background: linear-gradient(135deg,#ebfaff,#c3f0fe); border-color: #b8c1fd; box-shadow: 0 2px 8px rgba(184, 193, 253,.3); }
        .dist-bar.danger { background: linear-gradient(135deg,#fef2f2,#fee2e2); border-color: #fecaca; box-shadow: 0 2px 8px rgba(254,202,202,.3); }

        /* ── Resumen panel 3 ─────────────────────────────── */
        .resumen-wrap { border: 1.5px solid var(--pago-border); border-radius: .9rem .9rem 0 0; }
        .resumen-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: .5rem 1rem; border-bottom: 1px solid #eaf6fe; font-size: .85rem;
        }
        .resumen-row:last-child { border-bottom: none; }
        .resumen-total {
            background: var(--pago-grad);
            color: #fff; border-radius: 0 0 .9rem .9rem;
            padding: .9rem 1.1rem;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* ── Línea decorativa lateral de cards ──────────── */
        .jp-soft-card { border-left: 4px solid transparent; }
        #panel1  { border-left-color: var(--pago-blue); }
        #panel2  { border-left-color: var(--pago-sky); }
        #panel3  { border-left-color: var(--pago-cyan); }

        /* ── Descuento H- ───────────────────────────────── */
        .dscto-card {
            background: linear-gradient(135deg,#e8f9fe,#c3f0fe);
            border: 1.5px solid #b8c1fd; border-radius: .9rem;
            padding: 1rem 1.1rem; margin-top: .75rem;
        }
        .dscto-label {
            font-size: .78rem; font-weight: 800;
            color: #17226a; text-transform: uppercase;
            letter-spacing: .04em; display: flex; align-items: center; gap: 6px;
            margin-bottom: .55rem;
        }
        .dscto-input-wrap {
            display: flex; align-items: center; gap: .6rem;
        }
        .dscto-prefix {
            font-size: .95rem; font-weight: 800; color: #17226a; flex-shrink: 0;
        }
        .dscto-input {
            width: 120px; text-align: right;
            font-size: .95rem; font-weight: 700; color: #141e5d;
            border: 1.5px solid #23339f; border-radius: .5rem;
            padding: .3rem .6rem; background: #fff;
        }
        .dscto-input:focus {
            outline: none; border-color: #22329c;
            box-shadow: 0 0 0 2px rgba(34, 50, 156,.2);
        }
        .dscto-hint {
            font-size: .73rem; color: #17226a; opacity: .8; margin-top: .4rem;
        }
        .dscto-aplicado {
            font-size: .78rem; font-weight: 700; color: #166534;
            background: #dcfce7; border-radius: .4rem; padding: .15em .6em;
            margin-left: auto; flex-shrink: 0;
        }

        /* ── Badge hermano ───────────────────────────────── */
        .hermano-badge {
            display: inline-flex; align-items: center;
            font-size: .72rem; font-weight: 800;
            border-radius: .4rem; padding: .15em .55em;
            letter-spacing: .02em; flex-shrink: 0;
        }
        .hermano-badge.hm  { background:#c3f0fe; color:#17226a; border:1px solid #b8c1fd; } /* H-  amarillo */
        .hermano-badge.hp  { background:#eff6ff; color:#1f2e8d; border:1px solid #bfdbfe; } /* H+  azul     */
        .hermano-badge.hm0 { background:#f0fdf4; color:#166534; border:1px solid #86efac; } /* H   verde    */
    </style>
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-xl-10 col-lg-11">

    {{-- ══════════════════════════════════════════
         PASO 1 · Seleccionar alumno
    ══════════════════════════════════════════ --}}
    <div class="card jp-soft-card mt-3" id="panel1">
        <div class="card-body">

            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="step-pill">
                    <span class="step-num">1</span>
                    Seleccionar alumno
                </span>
            </div>
            <p class="text-muted small mb-4" style="margin-left:.25rem;">
                Busca al alumno por DNI o nombre completo
            </p>

            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Año escolar *</label>
                    <select id="anioSelect" class="form-select">
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id }}">{{ $anio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold small">Alumno *</label>
                    <div class="position-relative">
                        <span class="position-absolute top-50 translate-middle-y"
                              style="left:.8rem; color:#1f2e8e; z-index:5; pointer-events:none;">
                            <i class="ti ti-search" style="font-size:1.05rem;"></i>
                        </span>
                        <input type="text" id="alumnoSearch" class="form-control"
                               style="padding-left:2.2rem;"
                               placeholder="Escribe DNI o nombre..." autocomplete="off">
                        <div id="alumnoResults" style="display:none;"></div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta alumno --}}
            <div id="alumnoCard" class="alumno-card p-3 mt-3 d-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="alumno-avatar" id="alumnoInitials">??</div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold" style="font-size:1.05rem; color:var(--pago-ink);"
                                 id="alumnoNombre">-</span>
                            <span id="alumnoBadgeHermano" class="hermano-badge d-none"></span>
                        </div>
                        <div class="small" style="color:var(--pago-muted);">
                            DNI: <strong id="alumnoDni">-</strong>
                            <span class="mx-1">·</span>
                            <span id="alumnoGrado">-</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCambiarAlumno">
                        <i class="ti ti-refresh me-1"></i>Cambiar
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════
         HISTORIAL · Pagos realizados del alumno
    ══════════════════════════════════════════ --}}
    <div class="card jp-soft-card mt-3 d-none" id="panelHistorial"
         style="border-left-color:#213197;">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="step-pill" style="background:linear-gradient(135deg,#1f2e8d,#213197,#25359c);">
                    <span class="step-num" style="background:rgba(255,255,255,.25);">
                        <i class="ti ti-history" style="font-size:.85rem;"></i>
                    </span>
                    Pagos realizados
                </span>
                <span id="historialResumenMonto"
                      class="fw-bold small"
                      style="color:#1f2e8d; background:#eff6ff; border:1px solid #bfdbfe;
                             border-radius:.5rem; padding:.25rem .75rem;">
                    S/ 0.00 pagados
                </span>
            </div>

            {{-- Spinner --}}
            <div id="historialSpinner" class="text-center py-4 d-none">
                <div class="spinner-border spinner-border-sm" style="color:#25359c;" role="status"></div>
                <span class="ms-2 small text-muted">Cargando historial...</span>
            </div>

            {{-- Sin pagos --}}
            <div id="historialVacio" class="d-none">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                     style="background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1.5px solid #bfdbfe;">
                    <span style="width:40px;height:40px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="ti ti-receipt-off" style="font-size:1.2rem;color:#213197;"></i>
                    </span>
                    <div>
                        <div class="fw-bold" style="color:#1f2e8d; font-size:.88rem;">Sin pagos anteriores</div>
                        <div class="small text-muted">Este alumno no tiene pagos confirmados en el año seleccionado.</div>
                    </div>
                </div>
            </div>

            {{-- Tabla de pagos --}}
            <div id="historialTablaWrap" class="d-none">
                <div class="table-responsive" style="max-height:280px; overflow-y:auto;">
                    <table class="w-100" style="border-collapse:separate; border-spacing:0; font-size:.845rem;">
                        <thead>
                            <tr style="background:#eff6ff;">
                                <th style="padding:8px 12px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:1.5px solid #bfdbfe; white-space:nowrap;">Fecha</th>
                                <th style="padding:8px 12px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:1.5px solid #bfdbfe;">Concepto / Período</th>
                                <th style="padding:8px 12px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:1.5px solid #bfdbfe; white-space:nowrap;">Monto</th>
                                <th style="padding:8px 12px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:1.5px solid #bfdbfe; white-space:nowrap;">Método</th>
                                <th style="padding:8px 12px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:1.5px solid #bfdbfe;"></th>
                            </tr>
                        </thead>
                        <tbody id="historialTbody"></tbody>
                    </table>
                </div>
                <div id="historialVerTodos" class="d-none"
                     style="padding:.6rem 1rem; text-align:center; border-top:1px solid #dbeafe; background:#f8faff; border-radius:0 0 .5rem .5rem;">
                    <a id="historialVerTodosLink" href="#" target="_blank"
                       style="font-size:.8rem; font-weight:700; color:#213197;">
                        <i class="ti ti-external-link me-1"></i>Ver historial completo en nueva pestaña
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════
         PASO 2 · Seleccionar deudas
    ══════════════════════════════════════════ --}}
    <div class="card jp-soft-card mt-3 wizard-panel" id="panel2">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="step-pill">
                    <span class="step-num">2</span>
                    Seleccionar deudas
                </span>
                <button type="button" class="btn btn-sm btn-link p-0 d-none"
                        style="color:#1f2e8f; font-size:.83rem; font-weight:700;"
                        id="btnLimpiar">
                    <i class="ti ti-x me-1"></i>Limpiar todo
                </button>
            </div>
            <p class="text-muted small mb-4" style="margin-left:.25rem;">
                Marca libremente las deudas a pagar — puedes combinar matrícula, pensiones y otros conceptos
            </p>

            {{-- Spinner --}}
            <div id="deudasSpinner" class="text-center py-5 d-none">
                <div class="spinner-border" style="color:var(--pago-sky); width:2.5rem; height:2.5rem;"
                     role="status"></div>
                <p class="mt-3 text-muted small mb-0 fw-semibold">Cargando deudas...</p>
            </div>

            {{-- Sin deudas --}}
            <div id="sinDeudas" class="d-none">
                <div class="d-flex align-items-center gap-3 p-4 rounded-3"
                     style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1.5px solid #86efac;">
                    <span style="width:52px;height:52px;border-radius:50%;background:#bbf7d0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="ti ti-circle-check" style="font-size:1.7rem;color:#16a34a;"></i>
                    </span>
                    <div>
                        <div class="fw-bold" style="color:#166534;">¡Todo al día!</div>
                        <div class="small text-muted">Este alumno no tiene deudas pendientes.</div>
                    </div>
                </div>
            </div>

            {{-- Contenido de deudas --}}
            <div id="deudasContenido" class="d-none">

                {{-- Ajuste manual hermano (solo cuando la matrícula viene del seeder) --}}
                <div id="ajusteHermanoBlock" class="d-none mb-3">
                    <div class="p-3 rounded-3 border" style="background:#edf8ff;border-color:#ccd2fe!important;">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold text-warning-emphasis" style="font-size:.93rem;">
                                    <i class="ti ti-users me-1"></i>Ajuste manual de matrícula por hermano
                                </div>
                                <div class="text-muted mt-1" style="font-size:.8rem;">
                                    Úsalo cuando el alumno sí es hermano, pero todavía no está vinculado con el mismo apoderado en el sistema.
                                </div>
                                <div id="ajusteHermanoDetalle" class="mt-2 d-none">
                                    <div class="text-muted" style="font-size:.8rem;">
                                        Sugerido por hermano: <strong>S/ 100.00</strong>
                                        &nbsp;·&nbsp;
                                        <span id="ajusteHermanoInfoActual"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch flex-shrink-0 pt-1">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="toggleEsHermano" style="cursor:pointer;">
                                <label class="form-check-label fw-medium" for="toggleEsHermano"
                                       style="font-size:.85rem;cursor:pointer;">Es hermano</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick chips --}}
                <div class="d-flex flex-wrap gap-2 mb-4" id="quickChips"></div>

                {{-- Grid de categorías --}}
                <div class="row g-3" id="categoriasGrid"></div>

                {{-- Barra flotante --}}
                <div id="barraTotal" class="d-none">
                    <div class="barra-inner">
                        <div class="barra-icon"><i class="ti ti-receipt" style="font-size:1.5rem;"></i></div>
                        <div class="flex-grow-1">
                            <div class="barra-label" id="barraLabel">0 deudas seleccionadas</div>
                            <div class="barra-monto" id="barraMonto">S/ 0.00</div>
                        </div>
                        <button type="button" class="barra-btn" id="btnIrPago">
                            Continuar →
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         PASO 3 · Confirmar pago
    ══════════════════════════════════════════ --}}
    <div class="card jp-soft-card mt-3 wizard-panel" id="panel3">
        <div class="card-body">

            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="step-pill">
                    <span class="step-num">3</span>
                    Confirmar pago
                </span>
            </div>
            <p class="text-muted small mb-4" style="margin-left:.25rem;">
                Revisa el resumen y completa los datos del pago
            </p>

            <form method="POST" action="{{ route($rp . 'pagos.store') }}"
                  enctype="multipart/form-data" id="pagoForm">
                @csrf
                <input type="hidden" name="anio_escolar_id" id="hiddenAnioId">
                <div id="hiddenDeudas"></div>

                <div class="row g-4">

                    {{-- Izquierda: resumen de deudas --}}
                    <div class="col-lg-6">
                        <p class="section-label mb-2">Deudas a pagar</p>
                        <div class="resumen-wrap" id="resumenDeudas"
                             style="max-height:330px; overflow-y:auto;"></div>
                        <div class="resumen-total">
                            <span class="fw-semibold">Total a pagar</span>
                            <span class="fw-bold" style="font-size:1.25rem;" id="resumenTotal">S/ 0.00</span>
                        </div>

                        {{-- Descuento H- (sólo visible si el alumno es hermano menor) --}}
                        <div id="dsctoHermanoBloque" class="dscto-card d-none">
                            <div class="dscto-label">
                                <span class="hermano-badge hm" style="font-size:.75rem;">H-</span>
                                Descuento hermano menor — pensiones
                            </div>
                            <div class="dscto-input-wrap">
                                <span class="dscto-prefix">S/</span>
                                <input type="number" id="dsctoMonto" class="dscto-input"
                                       min="0" step="0.01" value="0.00"
                                       placeholder="0.00">
                                <span id="dsctoAplicado" class="dscto-aplicado d-none">− S/ 0.00</span>
                            </div>
                            <div class="dscto-hint">
                                Se descuenta del monto de las mensualidades seleccionadas.
                                El monto es flexible — ingresa el que corresponda.
                            </div>
                        </div>
                        <input type="hidden" name="descuento_hermano_monto" id="hiddenDsctoHermano" value="0">
                    </div>

                    {{-- Derecha: datos del pago --}}
                    <div class="col-lg-6">
                        <p class="section-label mb-2">Método de pago *</p>
                        <p class="text-muted small mb-2" style="margin-left:.1rem;">
                            Selecciona uno o más métodos y distribuye el monto
                        </p>
                        <div class="row g-2 mb-2" id="metodosGrid">
                            @foreach([
                                'Efectivo'      => ['ico'=>'ti ti-cash',          'clr'=>'#16a34a','bg'=>'#dcfce7','op'=>false,'label'=>'Efectivo'           ],
                                'Yape'          => ['ico'=>'ti ti-device-mobile', 'clr'=>'#7c3aed','bg'=>'#ede9fe','op'=>true, 'label'=>'Yape'                ],
                                'Plin'          => ['ico'=>'ti ti-credit-card',   'clr'=>'#213197','bg'=>'#dbeafe','op'=>true, 'label'=>'Plin'                ],
                                'Transferencia' => ['ico'=>'ti ti-building-bank', 'clr'=>'#192572','bg'=>'#e0f2fe','op'=>true, 'label'=>'Tarjeta BBVA'        ],
                                'Tarjeta'       => ['ico'=>'ti ti-credit-card',   'clr'=>'#1f2e8f','bg'=>'#e2eefe','op'=>true, 'label'=>'Tarjeta BCP'          ],
                                'BancoNacion'   => ['ico'=>'ti ti-building-bank', 'clr'=>'#1b287b','bg'=>'#c7effe','op'=>true, 'label'=>'Banco de la Nación', 'img'=>'imagenes_caja/logo-banco-nacion-peru.webp'],
                                'Otro'          => ['ico'=>'ti ti-dots-circle-horizontal','clr'=>'#6b7280','bg'=>'#f3f4f6','op'=>true, 'label'=>'Otro'         ],
                            ] as $met => $cfg)
                            <div class="col-6">
                                <div class="metodo-item" data-met="{{ $met }}"
                                     id="metItem_{{ $met }}">
                                    <div class="metodo-head">
                                        <div class="metodo-ico">
                                            <span class="metodo-ico-bubble"
                                                  style="background:{{ $cfg['bg'] }}; margin:0 auto .4rem;">
                                                @if(!empty($cfg['img']))
                                                    <img src="{{ asset('images/' . $cfg['img']) }}" alt="{{ $cfg['label'] }}">
                                                @else
                                                    <i class="{{ $cfg['ico'] }}"
                                                       style="color:{{ $cfg['clr'] }};"></i>
                                                @endif
                                            </span>
                                        </div>
                                        {{ $cfg['label'] }}
                                    </div>
                                    <div class="metodo-amount-row" id="metAmtRow_{{ $met }}">
                                        <div class="metodo-amount-label">Monto</div>
                                        <div class="metodo-amount-wrap">
                                            <span class="metodo-amount-prefix">S/</span>
                                            <input type="number" class="metodo-amount-inp"
                                                   id="metAmt_{{ $met }}" data-met="{{ $met }}"
                                                   placeholder="0.00" min="0.01" step="0.01">
                                        </div>
                                        @if($cfg['op'])
                                        <div class="metodo-op-wrap">
                                            <i class="ti ti-hash"></i>
                                            <input type="text" class="metodo-op-inp"
                                                   id="metOp_{{ $met }}" data-met="{{ $met }}"
                                                   placeholder="N° operación" maxlength="60">
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Barra de distribución --}}
                        <div id="distBar" class="dist-bar mb-3 d-none">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-semibold">Distribuido:</span>
                                    <span id="distMonto" class="fw-bold"
                                          style="color:var(--pago-blue);">S/ 0.00</span>
                                    <span class="text-muted">de</span>
                                    <span id="distTotal" class="fw-bold">S/ 0.00</span>
                                </div>
                                <div>
                                    <span id="distOk"    class="badge bg-success d-none">✓ Completo</span>
                                    <span id="distFalta" class="badge bg-warning text-dark d-none">
                                        Falta S/ <span id="distFaltaAmt">0.00</span>
                                    </span>
                                    <span id="distExcede" class="badge bg-danger d-none">
                                        Excede S/ <span id="distExcedeAmt">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Fecha de pago *</label>
                                <input type="text" id="fechaPago" name="fecha_pago" class="form-control"
                                       required value="{{ date('Y-m-d') }}"
                                       placeholder="dd/mm/yyyy" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    Voucher
                                    <span class="text-muted fw-normal">(opcional · imagen o PDF)</span>
                                </label>
                                <input type="file" name="voucher_archivo" class="form-control"
                                       accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Comentario</label>
                                <input type="text" name="comentario" class="form-control"
                                       placeholder="Observación opcional...">
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route($rp . 'pagos.index') }}"
                       class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn jp-btn-incidencia px-4"
                            id="btnConfirmar" disabled>
                        <i class="ti ti-circle-check me-1"></i>Confirmar pago
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Estado ───────────────────────────────────────────── */
    const state = {
        anioId:        null,
        estudianteId:  null,
        matriculaId:   null,
        nombreAlumno:  '',
        deudas:        [],
        seleccionadas: [],
        hermanoTag:    null,
    };

    /* ── DOM ──────────────────────────────────────────────── */
    const anioSelect       = document.getElementById('anioSelect');
    const alumnoSearch     = document.getElementById('alumnoSearch');
    const alumnoResults    = document.getElementById('alumnoResults');
    const alumnoCard       = document.getElementById('alumnoCard');
    const btnCambiarAlumno = document.getElementById('btnCambiarAlumno');
    const panel2           = document.getElementById('panel2');
    const panel3           = document.getElementById('panel3');
    const deudasSpinner    = document.getElementById('deudasSpinner');
    const sinDeudas        = document.getElementById('sinDeudas');
    const deudasContenido  = document.getElementById('deudasContenido');
    const quickChips       = document.getElementById('quickChips');
    const categoriasGrid   = document.getElementById('categoriasGrid');
    const barraTotal       = document.getElementById('barraTotal');
    const barraLabel       = document.getElementById('barraLabel');
    const barraMonto       = document.getElementById('barraMonto');
    const btnIrPago        = document.getElementById('btnIrPago');
    const btnLimpiar       = document.getElementById('btnLimpiar');
    const hiddenAnioId     = document.getElementById('hiddenAnioId');
    const hiddenDeudas     = document.getElementById('hiddenDeudas');
    const btnConfirmar     = document.getElementById('btnConfirmar');
    const resumenDeudas    = document.getElementById('resumenDeudas');
    const resumenTotal     = document.getElementById('resumenTotal');

    const MESES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                      'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    const esc  = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const fmt  = n => 'S/ ' + parseFloat(n||0).toFixed(2);
    const ini  = n => (n||'').split(' ').slice(0,2).map(p=>p[0]||'').join('').toUpperCase()||'??';
    const show = el => el.classList.remove('d-none');
    const hide = el => el.classList.add('d-none');

    /* ── Ajuste manual hermano ───────────────────────────────── */
    const HERMANO_RATE = 100.00;
    const ajusteBlock  = document.getElementById('ajusteHermanoBlock');
    const toggleHermano = document.getElementById('toggleEsHermano');
    const ajusteDetalle = document.getElementById('ajusteHermanoDetalle');
    const ajusteInfo    = document.getElementById('ajusteHermanoInfoActual');

    function getDeudaGastosInternos() {
        return state.deudas.find(d => d.concepto_tipo_db === 'gastos_internos');
    }

    function actualizarAjusteHermanoBlock() {
        const d = getDeudaGastosInternos();
        if (!d) { hide(ajusteBlock); return; }
        show(ajusteBlock);
        ajusteInfo.textContent = 'La deuda actual de matrícula es S/ ' + d.saldo.toFixed(2) +
            '. Si marcas "Es hermano", se ajustará a S/ ' + HERMANO_RATE.toFixed(2) + '.';
    }

    toggleHermano.addEventListener('change', () => {
        const d = getDeudaGastosInternos();
        if (!d) return;

        const nuevoSaldo = toggleHermano.checked ? HERMANO_RATE : d.monto;
        d.saldo = nuevoSaldo;

        // Actualizar label visible y el input de la fila
        const lbl = document.getElementById('monto_lbl_' + d.id);
        const inp = document.getElementById('amt_' + d.id);
        const row = document.querySelector('.deuda-row[data-id="' + d.id + '"]');
        if (lbl) lbl.textContent = fmt(nuevoSaldo);
        if (inp) { inp.max = nuevoSaldo; inp.value = nuevoSaldo.toFixed(2); inp.dataset.saldo = nuevoSaldo; }
        if (row) row.dataset.saldo = nuevoSaldo;

        // Actualizar subtotal del card de matrícula
        const catTotal = categoriasGrid.querySelector('.cat-header.matricula .cat-subtotal');
        if (catTotal) {
            const totalMat = state.deudas
                .filter(x => x.concepto_tipo === 'matricula')
                .reduce((s, x) => s + x.saldo, 0);
            catTotal.textContent = fmt(totalMat);
        }

        // Mostrar/ocultar el detalle
        toggleHermano.checked ? show(ajusteDetalle) : hide(ajusteDetalle);

        actualizarBarra();
        actualizarHiddenInputs();
        actualizarPanel3();
    });

    /* ── Búsqueda ─────────────────────────────────────────── */
    let searchTimer = null;

    alumnoSearch.addEventListener('input', () => {
        const q = alumnoSearch.value.trim();
        if (q.length < 2) { alumnoResults.style.display = 'none'; return; }
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => fetchEstudiantes(q), 300);
    });

    alumnoSearch.addEventListener('keydown', e => {
        if (e.key === 'Escape') alumnoResults.style.display = 'none';
    });

    document.addEventListener('click', e => {
        if (!alumnoSearch.contains(e.target) && !alumnoResults.contains(e.target))
            alumnoResults.style.display = 'none';
    });

    async function fetchEstudiantes(q) {
        const url = `{{ route($rp . 'pagos.buscar-estudiante') }}?q=${encodeURIComponent(q)}&anio_escolar_id=${anioSelect.value}`;
        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            renderResultados(data);
        } catch (_) { alumnoResults.style.display = 'none'; }
    }

    function renderResultados(items) {
        if (!items.length) {
            alumnoResults.innerHTML = '<div class="result-item text-muted">Sin resultados</div>';
            alumnoResults.style.display = 'block';
            return;
        }
        alumnoResults.innerHTML = items.map(it => {
            const tagHtml = it.hermano_tag ? hermanoTagHtml(it.hermano_tag, 'ms-1') : '';
            return `
            <div class="result-item"
                 data-id="${it.estudiante_id}"
                 data-matricula="${it.matricula_id}"
                 data-dni="${esc(it.dni)}"
                 data-nombre="${esc(it.nombre_completo)}"
                 data-grado="${esc(it.grado)} ${esc(it.seccion)}"
                 data-hermano="${esc(it.hermano_tag ?? '')}">
                <strong>${esc(it.apellidos)}, ${esc(it.nombres)}</strong>${tagHtml}
                <span class="text-muted ms-2 small">${esc(it.dni)}</span>
                <br>
                <small class="text-muted">${esc(it.grado)} – ${esc(it.seccion)}</small>
            </div>
        `}).join('');
        alumnoResults.style.display = 'block';
        alumnoResults.querySelectorAll('.result-item[data-id]').forEach(el =>
            el.addEventListener('click', () => seleccionarAlumno(el))
        );
    }

    function hermanoTagHtml(tag, extraClass = '') {
        if (!tag) return '';
        const clsMap = { 'H-': 'hm', 'H+': 'hp', 'H': 'hm0' };
        const cls = clsMap[tag] ?? 'hm0';
        return `<span class="hermano-badge ${cls}${extraClass ? ' ' + extraClass : ''}">${tag}</span>`;
    }

    function seleccionarAlumno(el) {
        state.estudianteId = el.dataset.id;
        state.matriculaId  = el.dataset.matricula;
        state.nombreAlumno = el.dataset.nombre;

        document.getElementById('alumnoInitials').textContent = ini(el.dataset.nombre);
        document.getElementById('alumnoNombre').textContent   = el.dataset.nombre;
        document.getElementById('alumnoDni').textContent      = el.dataset.dni;
        document.getElementById('alumnoGrado').textContent    = el.dataset.grado;

        // Guardar tag en state
        state.hermanoTag = el.dataset.hermano || null;

        // Badge hermano
        const badgeEl = document.getElementById('alumnoBadgeHermano');
        const tag = el.dataset.hermano ?? '';
        if (tag) {
            badgeEl.className = 'hermano-badge ' + ({ 'H-': 'hm', 'H+': 'hp', 'H': 'hm0' }[tag] ?? 'hm0');
            badgeEl.textContent = tag;
            badgeEl.classList.remove('d-none');
        } else {
            badgeEl.classList.add('d-none');
        }

        alumnoCard.classList.remove('d-none');
        alumnoSearch.value = '';
        alumnoResults.style.display = 'none';
        panel2.classList.add('active');
        cargarDeudas();
        cargarHistorial();
    }

    btnCambiarAlumno.addEventListener('click', resetDesdeEstudiante);

    /* ── Cargar deudas ────────────────────────────────────── */
    async function cargarDeudas() {
        show(deudasSpinner);
        hide(sinDeudas);
        hide(deudasContenido);

        const url = `{{ route($rp . 'pagos.deudas-estudiante') }}?estudiante_id=${state.estudianteId}&anio_escolar_id=${anioSelect.value}`;
        try {
            const res   = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            state.deudas = await res.json();
        } catch (_) { state.deudas = []; }

        hide(deudasSpinner);

        if (!state.deudas.length) { show(sinDeudas); return; }

        state.seleccionadas = [];
        toggleHermano.checked = false;
        hide(ajusteDetalle);
        renderQuickChips();
        renderCategoriasGrid();
        actualizarAjusteHermanoBlock();
        actualizarBarra();
        show(deudasContenido);
    }

    /* ── Cargar historial de pagos ────────────────────────── */
    async function cargarHistorial() {
        const panel    = document.getElementById('panelHistorial');
        const spinner  = document.getElementById('historialSpinner');
        const vacio    = document.getElementById('historialVacio');
        const tablaW   = document.getElementById('historialTablaWrap');
        const tbody    = document.getElementById('historialTbody');
        const resumen  = document.getElementById('historialResumenMonto');
        const verTodos = document.getElementById('historialVerTodos');
        const verLink  = document.getElementById('historialVerTodosLink');

        panel.classList.remove('d-none');
        show(spinner);
        hide(vacio);
        hide(tablaW);

        const url = `{{ route($rp . 'pagos.historial-estudiante') }}?estudiante_id=${state.estudianteId}&anio_escolar_id=${anioSelect.value}`;
        let pagos = [];
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            pagos = await res.json();
        } catch (_) { pagos = []; }

        hide(spinner);

        if (!pagos.length) {
            resumen.textContent = 'S/ 0.00 pagados';
            show(vacio);
            return;
        }

        // Total pagado
        const totalPagado = pagos.reduce((s, p) => s + p.monto, 0);
        resumen.textContent = 'S/ ' + totalPagado.toFixed(2) + ' pagados';

        // Renderizar filas
        const metodoIco = {
            'Efectivo':      '<i class="ti ti-cash" style="color:#16a34a;"></i>',
            'Yape':          '<i class="ti ti-device-mobile" style="color:#7c3aed;"></i>',
            'Plin':          '<i class="ti ti-credit-card" style="color:#213197;"></i>',
            'Transferencia': '<i class="ti ti-building-bank" style="color:#192572;"></i>',
            'Tarjeta':       '<i class="ti ti-credit-card" style="color:#1f2e8f;"></i>',
            'BancoNacion':   '<i class="ti ti-building-bank" style="color:#1b287b;"></i>',
            'Otro':          '<i class="ti ti-dots-circle-horizontal" style="color:#6b7280;"></i>',
        };
        const metodoLabel = {
            'Efectivo':      'Efectivo',
            'Yape':          'Yape',
            'Plin':          'Plin',
            'Transferencia': 'Tarjeta BBVA',
            'Tarjeta':       'Tarjeta BCP',
            'BancoNacion':   'Banco de la Nación',
            'Otro':          'Otro',
        };

        function renderMetodos(metodos, display) {
            if (metodos && metodos.length > 1) {
                return metodos.map(m => {
                    const ico = metodoIco[m.metodo] ?? '<i class="ti ti-credit-card-pay" style="color:#6b7280;"></i>';
                    const lbl = metodoLabel[m.metodo] ?? m.metodo;
                    return `<span style="display:inline-flex;align-items:center;gap:3px;font-size:.8rem;">${ico} ${esc(lbl)} <span style="color:#1f2e8d;font-weight:700;">S/${parseFloat(m.monto).toFixed(2)}</span></span>`;
                }).join('<br>');
            }
            const ico = metodoIco[display] ?? '<i class="ti ti-credit-card-pay" style="color:#6b7280;"></i>';
            const lbl = metodoLabel[display] ?? display;
            return `<span style="display:inline-flex;align-items:center;gap:5px;font-size:.82rem;">${ico} ${esc(lbl)}</span>`;
        }

        tbody.innerHTML = pagos.map(p => {
            const conceptoStr = [...p.conceptos].slice(0,2).map(c => esc(c)).join(', ')
                              + (p.conceptos.length > 2 ? ' +' + (p.conceptos.length - 2) : '');
            const periodoStr  = [...p.periodos].slice(0,2).map(c => esc(c)).join(', ')
                              + (p.periodos.length > 2 ? ' +' + (p.periodos.length - 2) : '');
            return `
            <tr style="border-bottom:1px solid #f0f4ff; transition:background .12s;"
                onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                <td style="padding:9px 12px; white-space:nowrap; color:#374151; font-weight:600;">
                    <i class="ti ti-calendar-check me-1" style="color:#bdc6fd; font-size:.85rem;"></i>${esc(p.fecha_pago)}
                </td>
                <td style="padding:9px 12px; color:#374151; max-width:240px;">
                    <div style="font-weight:600; font-size:.84rem;">${conceptoStr || '-'}</div>
                    ${periodoStr ? `<div style="font-size:.75rem; color:#9ca3af;">${periodoStr}</div>` : ''}
                </td>
                <td style="padding:9px 12px; white-space:nowrap;">
                    <strong style="color:#1f2e8d;">S/ ${parseFloat(p.monto).toFixed(2)}</strong>
                </td>
                <td style="padding:9px 12px;">
                    ${renderMetodos(p.metodos, p.metodo)}
                </td>
                <td style="padding:9px 12px; white-space:nowrap;">
                    <a href="${p.show_url}" target="_blank"
                       style="width:28px;height:28px;border-radius:7px;background:rgba(31,46,141,.1);color:#1f2e8d;
                              display:inline-flex;align-items:center;justify-content:center;text-decoration:none;"
                       title="Ver detalle">
                        <i class="ti ti-eye" style="font-size:.85rem;"></i>
                    </a>
                    <a href="${p.voucher_url}" target="_blank"
                       style="width:28px;height:28px;border-radius:7px;background:rgba(5,150,105,.1);color:#059669;
                              display:inline-flex;align-items:center;justify-content:center;text-decoration:none;margin-left:4px;"
                       title="Imprimir voucher">
                        <i class="ti ti-printer" style="font-size:.85rem;"></i>
                    </a>
                </td>
            </tr>`;
        }).join('');

        // Link "ver todos" — filtra índice por este estudiante (DNI del alumno seleccionado si disponible)
        const dniActual = document.getElementById('alumnoDni')?.textContent?.trim() ?? '';
        if (dniActual) {
            verLink.href = `{{ route($rp . 'pagos.index') }}?dni=${encodeURIComponent(dniActual)}`;
            show(verTodos);
        } else {
            hide(verTodos);
        }

        show(tablaW);
    }

    /* ── Quick chips ──────────────────────────────────────── */
    function renderQuickChips() {
        const tiene = t => state.deudas.some(d => d.concepto_tipo === t);

        const opciones = [
            { key:'todas',     icon:'<i class="ti ti-checks" style="color:#16a34a;font-size:1rem;"></i>',             label:'Todo pendiente',      cond: true              },
            { key:'matricula', icon:'<i class="ti ti-school" style="color:#1c297e;font-size:1rem;"></i>',             label:'Toda la matrícula',   cond: tiene('matricula') },
            { key:'pension',   icon:'<i class="ti ti-calendar" style="color:#213196;font-size:1rem;"></i>',          label:'Todas las pensiones', cond: tiene('pension')   },
            { key:'otro',      icon:'<i class="ti ti-file-invoice" style="color:#213197;font-size:1rem;"></i>',       label:'Otros conceptos',     cond: tiene('otro')      },
        ].filter(o => o.cond);

        quickChips.innerHTML = opciones.map(o => `
            <button type="button" class="qchip" data-key="${o.key}">
                ${o.icon} ${esc(o.label)}
            </button>
        `).join('');

        quickChips.querySelectorAll('.qchip').forEach(chip => {
            chip.addEventListener('click', () => {
                const k = chip.dataset.key;
                let ids;
                if (k === 'todas')      ids = state.deudas.map(d => d.id);
                else if (k === 'matricula') ids = state.deudas.filter(d => d.concepto_tipo === 'matricula').map(d => d.id);
                else if (k === 'pension')   ids = state.deudas.filter(d => d.concepto_tipo === 'pension').map(d => d.id);
                else if (k === 'otro')      ids = state.deudas.filter(d => d.concepto_tipo === 'otro').map(d => d.id);

                const allIn = ids.every(id => state.seleccionadas.includes(id));
                if (allIn) {
                    state.seleccionadas = state.seleccionadas.filter(id => !ids.includes(id));
                } else {
                    ids.forEach(id => { if (!state.seleccionadas.includes(id)) state.seleccionadas.push(id); });
                }

                sincronizarCheckboxes();
                actualizarBarra();
                actualizarHiddenInputs();
                actualizarPanel3();
            });
        });
    }

    /* ── Grid de categorías ───────────────────────────────── */
    function renderCategoriasGrid() {
        const tipos = [
            { tipo:'matricula', label:'Matrícula',       cls:'matricula', icon:'<i class="ti ti-school"></i>' },
            { tipo:'pension',   label:'Mensualidades',   cls:'pension',   icon:'<i class="ti ti-calendar"></i>' },
            { tipo:'otro',      label:'Otros conceptos', cls:'otro',      icon:'<i class="ti ti-file-invoice"></i>' },
        ].filter(t => state.deudas.some(d => d.concepto_tipo === t.tipo));

        const col = tipos.length === 1 ? 'col-md-6 col-lg-5'
                  : tipos.length === 2 ? 'col-md-6'
                  : tipos.length === 3 ? 'col-md-4'
                  : 'col-md-6 col-xl-3';

        categoriasGrid.innerHTML = tipos.map(t => {
            const deudas = state.deudas.filter(d => d.concepto_tipo === t.tipo);
            const total  = deudas.reduce((s, d) => s + d.saldo, 0);

            const rows = deudas.map(d => {
                const mes    = d.periodo && d.periodo <= 12 ? MESES[d.periodo] : (d.periodo ?? '');
                const label  = mes || esc(d.concepto_nombre);
                const vBadge = d.vencida
                    ? '<span class="badge bg-danger badge-venc ms-1">Vencida</span>' : '';
                return `
                    <div class="deuda-row" data-id="${d.id}" data-saldo="${d.saldo}">
                        <input type="checkbox" class="deuda-chk" data-id="${d.id}" id="chk_${d.id}">
                        <label class="deuda-nombre" for="chk_${d.id}" style="cursor:pointer;">
                            ${label}${vBadge}
                        </label>
                        <span class="deuda-monto" id="monto_lbl_${d.id}">${fmt(d.saldo)}</span>
                        <input type="number" class="deuda-amount-inp d-none" id="amt_${d.id}"
                               data-id="${d.id}" data-saldo="${d.saldo}"
                               value="${d.saldo.toFixed(2)}" min="0.01" max="${d.saldo}" step="0.01">
                        <span class="deuda-saldo-ref d-none" id="ref_${d.id}">${fmt(d.saldo)}</span>
                    </div>`;
            }).join('');

            return `
                <div class="${col}">
                    <div class="cat-card">
                        <div class="cat-header ${t.cls}">
                            <div class="d-flex align-items-center gap-2">
                                <span class="cat-icon ${t.cls}">${t.icon}</span>
                                <span class="cat-title">${esc(t.label)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="cat-subtotal">${fmt(total)}</span>
                                <button type="button" class="cat-toggle" data-tipo="${t.tipo}">
                                    Sel. todo
                                </button>
                            </div>
                        </div>
                        ${rows}
                    </div>
                </div>`;
        }).join('');

        /* Checkbox individual */
        categoriasGrid.querySelectorAll('.deuda-chk').forEach(chk => {
            chk.addEventListener('change', () => {
                const id     = parseInt(chk.dataset.id);
                const amtInp = document.getElementById('amt_' + id);
                const monLbl = document.getElementById('monto_lbl_' + id);
                const refLbl = document.getElementById('ref_' + id);
                if (chk.checked) {
                    if (!state.seleccionadas.includes(id)) state.seleccionadas.push(id);
                    amtInp?.classList.remove('d-none');
                    monLbl?.classList.add('d-none');
                    refLbl?.classList.remove('d-none');
                    amtInp?.focus();
                    amtInp?.select();
                } else {
                    state.seleccionadas = state.seleccionadas.filter(x => x !== id);
                    amtInp?.classList.add('d-none');
                    monLbl?.classList.remove('d-none');
                    refLbl?.classList.add('d-none');
                    // restaurar al saldo original
                    if (amtInp) amtInp.value = parseFloat(amtInp.dataset.saldo).toFixed(2);
                }
                actualizarBarra();
                actualizarHiddenInputs();
                actualizarPanel3();
            });
        });

        /* Actualizar totales cuando cambia el monto del input */
        categoriasGrid.querySelectorAll('.deuda-amount-inp').forEach(inp => {
            inp.addEventListener('input', () => {
                const max = parseFloat(inp.dataset.saldo);
                if (parseFloat(inp.value) > max) inp.value = max.toFixed(2);
                // No forzar valor mínimo mientras el usuario escribe (permite borrar y redigitar)
                actualizarBarra();
                actualizarHiddenInputs();
                actualizarPanel3();
            });
            inp.addEventListener('blur', () => {
                const max  = parseFloat(inp.dataset.saldo);
                let v = parseFloat(inp.value);
                if (!isFinite(v) || v < 0.01) v = 0.01;
                if (v > max) v = max;
                inp.value = v.toFixed(2);
                actualizarBarra();
                actualizarHiddenInputs();
                actualizarPanel3();
            });
            inp.addEventListener('click', e => e.stopPropagation());
        });

        /* Click en fila completa */
        categoriasGrid.querySelectorAll('.deuda-row').forEach(row => {
            row.addEventListener('click', e => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'LABEL') return;
                const chk = row.querySelector('.deuda-chk');
                chk.checked = !chk.checked;
                chk.dispatchEvent(new Event('change'));
            });
        });

        /* Sel. todo por categoría */
        categoriasGrid.querySelectorAll('.cat-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const tipo  = btn.dataset.tipo;
                const ids   = state.deudas.filter(d => d.concepto_tipo === tipo).map(d => d.id);
                const allIn = ids.every(id => state.seleccionadas.includes(id));

                if (allIn) {
                    state.seleccionadas = state.seleccionadas.filter(id => !ids.includes(id));
                    btn.textContent = 'Sel. todo';
                } else {
                    ids.forEach(id => { if (!state.seleccionadas.includes(id)) state.seleccionadas.push(id); });
                    btn.textContent = 'Quitar todo';
                }
                sincronizarCheckboxes();
                actualizarBarra();
                actualizarHiddenInputs();
                actualizarPanel3();
            });
        });
    }

    /* ── Sincronizar checkboxes ───────────────────────────── */
    function sincronizarCheckboxes() {
        categoriasGrid.querySelectorAll('.deuda-chk').forEach(chk => {
            chk.checked = state.seleccionadas.includes(parseInt(chk.dataset.id));
        });
        categoriasGrid.querySelectorAll('.deuda-row').forEach(row => {
            row.classList.toggle('sel', state.seleccionadas.includes(parseInt(row.dataset.id)));
        });
    }

    /* ── Obtener monto a pagar por deuda (usa input si existe) ── */
    function getMontoPagar(d) {
        const inp = document.getElementById('amt_' + d.id);
        return (inp && !inp.classList.contains('d-none'))
            ? Math.min(parseFloat(inp.value || 0), d.saldo)
            : d.saldo;
    }

    /* ── Barra flotante ───────────────────────────────────── */
    function actualizarBarra() {
        const sel   = state.deudas.filter(d => state.seleccionadas.includes(d.id));
        const total = sel.reduce((s, d) => s + getMontoPagar(d), 0);
        const n     = sel.length;

        barraLabel.textContent = `${n} deuda${n !== 1 ? 's' : ''} seleccionada${n !== 1 ? 's' : ''}`;
        barraMonto.textContent = fmt(total);

        if (n > 0) { show(barraTotal); show(btnLimpiar); }
        else       { hide(barraTotal); hide(btnLimpiar); }

        sincronizarCheckboxes();
    }

    btnIrPago.addEventListener('click', () => {
        actualizarPanel3();
        setTimeout(() => panel3.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
    });

    btnLimpiar.addEventListener('click', () => {
        // Resetear inputs de monto y ocultar
        state.seleccionadas.forEach(id => {
            const inp = document.getElementById('amt_' + id);
            const lbl = document.getElementById('monto_lbl_' + id);
            const ref = document.getElementById('ref_' + id);
            if (inp) { inp.value = parseFloat(inp.dataset.saldo).toFixed(2); inp.classList.add('d-none'); }
            lbl?.classList.remove('d-none');
            ref?.classList.add('d-none');
        });
        state.seleccionadas = [];
        sincronizarCheckboxes();
        actualizarBarra();
        actualizarHiddenInputs();
        actualizarPanel3();
    });

    /* ── Hidden inputs ────────────────────────────────────── */
    function actualizarHiddenInputs() {
        hiddenAnioId.value = anioSelect.value;

        let html = '';

        // Deudas
        state.seleccionadas.forEach(id => {
            const inp    = document.getElementById('amt_' + id);
            const deuda  = state.deudas.find(d => d.id === id);
            const amount = (inp && !inp.classList.contains('d-none'))
                ? Math.min(parseFloat(inp.value || 0), deuda?.saldo ?? 0).toFixed(2)
                : (deuda?.saldo ?? 0).toFixed(2);
            html += `<input type="hidden" name="deuda_ids[]" value="${id}">`;
            html += `<input type="hidden" name="deuda_amounts[${id}]" value="${amount}">`;
        });

        // Métodos de pago
        let idx = 0;
        document.querySelectorAll('.metodo-item.selected').forEach(item => {
            const met   = item.dataset.met;
            const monto = parseFloat(document.getElementById('metAmt_' + met)?.value || 0) || 0;
            const op    = (document.getElementById('metOp_' + met)?.value ?? '').trim();
            html += `<input type="hidden" name="metodos[${idx}][metodo]" value="${esc(met)}">`;
            html += `<input type="hidden" name="metodos[${idx}][monto]" value="${monto.toFixed(2)}">`;
            if (op) html += `<input type="hidden" name="metodos[${idx}][op]" value="${esc(op)}">`;
            idx++;
        });

        hiddenDeudas.innerHTML = html;
    }

    /* ── Panel 3: resumen ─────────────────────────────────── */
    function actualizarPanel3() {
        const sel   = state.deudas.filter(d => state.seleccionadas.includes(d.id));
        const total = sel.reduce((s, d) => s + getMontoPagar(d), 0);

        if (!sel.length) {
            panel3.classList.remove('active');
            btnConfirmar.disabled = true;
            document.getElementById('dsctoHermanoBloque')?.classList.add('d-none');
            return;
        }

        const badgeMap = {
            matricula: `<span class="badge" style="background:#e2eefe;color:#18246e;font-size:.7rem;">Matrícula</span>`,
            pension:   `<span class="badge" style="background:#d5f0ff;color:#18246e;font-size:.7rem;">Pensión</span>`,
            otro:      `<span class="badge" style="background:#dbeafe;color:#1f2e8d;font-size:.7rem;">Otro</span>`,
        };

        resumenDeudas.innerHTML = sel.map(d => {
            const mes        = d.periodo && d.periodo <= 12 ? MESES[d.periodo] : (d.periodo ?? '');
            const badge      = badgeMap[d.concepto_tipo] || '';
            const nombre     = mes ? `${esc(d.concepto_nombre)} – ${mes}` : esc(d.concepto_nombre);
            const vBadge     = d.vencida ? ' <span class="badge bg-danger" style="font-size:.65rem;">Venc.</span>' : '';
            const montoPagar = getMontoPagar(d);
            const esParcial  = montoPagar < d.saldo - 0.001;
            const montoHtml  = esParcial
                ? `<span class="fw-bold ms-2" style="color:var(--pago-blue);">${fmt(montoPagar)}</span>
                   <span class="text-muted ms-1" style="font-size:.72rem;">de ${fmt(d.saldo)}</span>`
                : `<span class="fw-bold ms-2" style="color:var(--pago-blue);">${fmt(montoPagar)}</span>`;
            return `
                <div class="resumen-row">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        ${badge}<span>${nombre}</span>${vBadge}
                    </div>
                    <div class="d-flex align-items-center gap-1">${montoHtml}</div>
                </div>`;
        }).join('');

        // ── Descuento H- ──────────────────────────────────
        const tienePension  = sel.some(d => d.concepto_tipo === 'pension');
        const esHermanoMenor = state.hermanoTag === 'H-';
        const dsctoBloque   = document.getElementById('dsctoHermanoBloque');
        const dsctoInp      = document.getElementById('dsctoMonto');
        const dsctoAplicado = document.getElementById('dsctoAplicado');
        const hiddenDscto   = document.getElementById('hiddenDsctoHermano');

        if (esHermanoMenor && tienePension) {
            dsctoBloque.classList.remove('d-none');
            const dscto = Math.max(0, parseFloat(dsctoInp?.value || 0));
            // Máximo = suma de salidos de pensiones seleccionadas
            const maxDscto = sel.filter(d => d.concepto_tipo === 'pension')
                               .reduce((s, d) => s + getMontoPagar(d), 0);
            const dsctoFinal = Math.min(dscto, maxDscto);
            if (hiddenDscto) hiddenDscto.value = dsctoFinal.toFixed(2);
            if (dsctoFinal > 0) {
                dsctoAplicado.textContent = `− S/ ${dsctoFinal.toFixed(2)}`;
                dsctoAplicado.classList.remove('d-none');
            } else {
                dsctoAplicado.classList.add('d-none');
            }
            resumenTotal.textContent = fmt(Math.max(0, total - dsctoFinal));
        } else {
            dsctoBloque?.classList.add('d-none');
            if (hiddenDscto) hiddenDscto.value = '0';
            resumenTotal.textContent = fmt(total);
        }

        panel3.classList.add('active');

        // Sincronizar monto único con el total si solo hay 1 método seleccionado
        const totalFinalStr = resumenTotal.textContent.replace(/[^0-9.]/g, '');
        const totalFinal    = parseFloat(totalFinalStr) || 0;
        sincronizarMontoMetodoUnico(totalFinal);
        actualizarDistribucion();
        btnConfirmar.disabled = !distribucionValida();
    }

    /* ── Listener del input de descuento H- ──────────────── */
    document.getElementById('dsctoMonto')?.addEventListener('input', () => actualizarPanel3());
    document.getElementById('dsctoMonto')?.addEventListener('blur', function () {
        const v = parseFloat(this.value);
        this.value = (isFinite(v) && v >= 0 ? v : 0).toFixed(2);
        actualizarPanel3();
    });

    /* ── Reset ────────────────────────────────────────────── */
    function resetDesdeEstudiante() {
        Object.assign(state, {
            estudianteId: null, matriculaId: null,
            nombreAlumno: '', deudas: [], seleccionadas: [], hermanoTag: null,
        });
        toggleHermano.checked = false;
        hide(ajusteBlock);
        hide(ajusteDetalle);
        const dsctoInp = document.getElementById('dsctoMonto');
        if (dsctoInp) dsctoInp.value = '0.00';
        document.getElementById('hiddenDsctoHermano').value = '0';
        document.getElementById('dsctoHermanoBloque')?.classList.add('d-none');
        document.getElementById('dsctoAplicado')?.classList.add('d-none');
        alumnoCard.classList.add('d-none');
        const badgeReset = document.getElementById('alumnoBadgeHermano');
        if (badgeReset) badgeReset.classList.add('d-none');
        alumnoSearch.value = '';
        panel2.classList.remove('active');
        panel3.classList.remove('active');
        hide(deudasSpinner);
        hide(sinDeudas);
        hide(deudasContenido);
        hide(barraTotal);
        hide(btnLimpiar);
        hiddenDeudas.innerHTML = '';
        // Ocultar historial
        document.getElementById('panelHistorial').classList.add('d-none');
        hide(document.getElementById('historialSpinner'));
        hide(document.getElementById('historialVacio'));
        hide(document.getElementById('historialTablaWrap'));
        document.getElementById('historialResumenMonto').textContent = 'S/ 0.00 pagados';
        // Limpiar métodos de pago
        document.querySelectorAll('.metodo-item.selected').forEach(item => {
            item.classList.remove('selected');
            const met = item.dataset.met;
            const inp = document.getElementById('metAmt_' + met);
            if (inp) inp.value = '';
            const opInp = document.getElementById('metOp_' + met);
            if (opInp) opInp.value = '';
        });
        document.getElementById('distBar')?.classList.add('d-none');
    }

    anioSelect.addEventListener('change', resetDesdeEstudiante);

    /* ── Métodos de pago (multi) ─────────────────────────── */

    function getDistribuido(exceptMet = null) {
        let sum = 0;
        document.querySelectorAll('.metodo-item.selected').forEach(item => {
            if (item.dataset.met === exceptMet) return;
            const v = parseFloat(document.getElementById('metAmt_' + item.dataset.met)?.value || 0);
            sum += isFinite(v) ? v : 0;
        });
        return sum;
    }

    function distribucionValida() {
        const nSel = document.querySelectorAll('.metodo-item.selected').length;
        if (nSel === 0) return false;
        const total = parseFloat(resumenTotal.textContent.replace(/[^0-9.]/g, '')) || 0;
        if (total <= 0) return false;
        return Math.abs(getDistribuido() - total) < 0.005;
    }

    function sincronizarMontoMetodoUnico(total) {
        const selectedItems = Array.from(document.querySelectorAll('.metodo-item.selected'));
        if (selectedItems.length === 1) {
            const met = selectedItems[0].dataset.met;
            const inp = document.getElementById('metAmt_' + met);
            if (inp) inp.value = total.toFixed(2);
        }
    }

    function actualizarDistribucion() {
        const nSel    = document.querySelectorAll('.metodo-item.selected').length;
        const distBar = document.getElementById('distBar');
        if (!distBar) return;

        if (nSel === 0 || !panel3.classList.contains('active')) {
            distBar.classList.add('d-none');
            return;
        }

        const total   = parseFloat(resumenTotal.textContent.replace(/[^0-9.]/g, '')) || 0;
        const dist    = getDistribuido();
        const diff    = dist - total;
        const absDiff = Math.abs(diff);

        distBar.classList.remove('d-none', 'warn', 'danger');
        document.getElementById('distMonto').textContent = fmt(dist);
        document.getElementById('distTotal').textContent = fmt(total);

        ['distOk','distFalta','distExcede'].forEach(id =>
            document.getElementById(id).classList.add('d-none')
        );

        if (absDiff < 0.005) {
            document.getElementById('distOk').classList.remove('d-none');
        } else if (diff < 0) {
            document.getElementById('distFaltaAmt').textContent = absDiff.toFixed(2);
            document.getElementById('distFalta').classList.remove('d-none');
            distBar.classList.add('warn');
        } else {
            document.getElementById('distExcedeAmt').textContent = absDiff.toFixed(2);
            document.getElementById('distExcede').classList.remove('d-none');
            distBar.classList.add('danger');
        }
    }

    document.querySelectorAll('.metodo-item').forEach(item => {
        item.addEventListener('click', e => {
            if (e.target.tagName === 'INPUT') return;
            const met = item.dataset.met;
            if (item.classList.contains('selected')) {
                item.classList.remove('selected');
                const inp = document.getElementById('metAmt_' + met);
                if (inp) inp.value = '';
                const opInp = document.getElementById('metOp_' + met);
                if (opInp) opInp.value = '';
            } else {
                item.classList.add('selected');
                // Auto-completar con el monto restante
                const total    = parseFloat(resumenTotal.textContent.replace(/[^0-9.]/g, '')) || 0;
                const restante = Math.max(0.01, total - getDistribuido(met));
                const inp = document.getElementById('metAmt_' + met);
                if (inp) {
                    inp.value = restante.toFixed(2);
                    setTimeout(() => { inp.focus(); inp.select(); }, 50);
                }
            }
            actualizarDistribucion();
            actualizarHiddenInputs();
            btnConfirmar.disabled = !distribucionValida();
        });
    });

    document.querySelectorAll('.metodo-amount-inp').forEach(inp => {
        inp.addEventListener('input', () => {
            actualizarDistribucion();
            actualizarHiddenInputs();
            btnConfirmar.disabled = !distribucionValida();
        });
        inp.addEventListener('blur', function () {
            let v = parseFloat(this.value);
            if (!isFinite(v) || v <= 0) v = 0.01;
            this.value = v.toFixed(2);
            actualizarDistribucion();
            actualizarHiddenInputs();
            btnConfirmar.disabled = !distribucionValida();
        });
        inp.addEventListener('click', e => e.stopPropagation());
    });

    document.querySelectorAll('.metodo-op-inp').forEach(inp => {
        inp.addEventListener('input', () => actualizarHiddenInputs());
        inp.addEventListener('click', e => e.stopPropagation());
    });

});
</script>

<script src="{{ url('assets/js/plugins/flatpickr.min.js') }}"></script>
<script>
flatpickr('#fechaPago', {
    dateFormat:  'Y-m-d',
    altInput:    true,
    altFormat:   'd/m/Y',
    defaultDate: 'today',
    allowInput:  true,
    clickOpens:  true,
    disableMobile: true,
    locale: {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
            longhand:  ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
        },
        months: {
            shorthand: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
            longhand:  ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        },
    },
});
</script>
@endpush
