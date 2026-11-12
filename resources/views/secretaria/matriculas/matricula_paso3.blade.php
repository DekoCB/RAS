@extends('layouts.mantis')

@section('title', 'Paso 3 | Matricula')
@section('page-title', 'Pago de Matricula')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/matricula-wizard.css') }}">
    <style>
    /* ─── Variables ─────────────────────────────────────────────── */
    :root {
        --p3-primary: #172269;
        --p3-accent:  #213196;
        --p3-accent2: #26369f;
        --p3-light:   #f5faff;
        --p3-border:  #d4ecfd;
        --p3-muted:   #6b7280;
        --p3-radius:  14px;
        --p3-shadow:  0 2px 16px rgba(0,0,0,.07);
        --p3-shadow-lg: 0 8px 32px rgba(0,0,0,.10);
    }

    /* ─── Layout ────────────────────────────────────────────────── */
    .p3-sticky { position: sticky; top: 76px; }
    @media(max-width:991px){ .p3-sticky { position: static; } }

    /* ─── Tarjetas de configuración (izquierda) ─────────────────── */
    .p3-config-card {
        background: #fff;
        border: 1px solid #f0f0f5;
        border-radius: var(--p3-radius);
        box-shadow: var(--p3-shadow);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .p3-config-card__header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #f5f5f8;
        background: #fafafa;
    }
    .p3-config-card__num {
        width: 26px; height: 26px;
        background: linear-gradient(135deg, var(--p3-primary), var(--p3-accent));
        border-radius: 50%;
        color: #fff;
        font-size: .75rem;
        font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .p3-config-card__title {
        font-size: .88rem;
        font-weight: 700;
        color: #1f2937;
        flex: 1;
    }
    .p3-config-card__subtitle {
        font-size: .75rem;
        color: var(--p3-muted);
        font-weight: 400;
        margin-left: 4px;
    }
    .p3-config-card__body {
        padding: 16px 18px;
    }

    /* ─── Tarifas chips ─────────────────────────────────────────── */
    .p3-tarifa-chip {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 14px;
        min-width: 100px;
        text-align: center;
    }
    .p3-tarifa-chip .chip-label {
        font-size: .72rem;
        color: var(--p3-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .p3-tarifa-chip .chip-value {
        font-size: .95rem;
        font-weight: 800;
        color: var(--p3-primary);
    }
    .p3-tarifa-chip .chip-sub {
        font-size: .68rem;
        color: #9ca3af;
    }

    /* ─── Pills de modo ─────────────────────────────────────────── */
    .p3-mode-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .p3-mode-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        font-size: .82rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: all .18s ease;
        line-height: 1.3;
        white-space: nowrap;
    }
    .p3-mode-pill:hover { border-color: var(--p3-accent2); color: var(--p3-accent); }
    .p3-mode-pill.active {
        background: linear-gradient(135deg, var(--p3-primary), var(--p3-accent));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 3px 10px rgba(33, 49, 150,.3);
    }
    .p3-mode-pill.active small { color: rgba(255,255,255,.75); }
    .p3-mode-pill small { font-size: .72rem; color: #9ca3af; }

    /* ─── Sub-panel (rango / n-meses) ───────────────────────────── */
    .p3-sub-panel {
        margin-top: 12px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e8edf2;
        border-radius: 10px;
        animation: p3FadeIn .2s ease;
    }
    @keyframes p3FadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }

    /* ─── Check pills (descuentos) ──────────────────────────────── */
    .p3-check-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        font-size: .83rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: all .18s ease;
        user-select: none;
    }
    .p3-check-pill input[type="checkbox"] { width:16px; height:16px; accent-color: var(--p3-accent); cursor:pointer; }
    .p3-check-pill:has(input:checked) {
        background: var(--p3-light);
        border-color: var(--p3-accent2);
        color: #18246e;
    }

    /* ─── Toggle colapsable ─────────────────────────────────────── */
    .p3-collapse-toggle {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .78rem;
        font-weight: 600;
        color: var(--p3-accent);
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: color .15s;
    }
    .p3-collapse-toggle:hover { color: var(--p3-primary); }

    /* ─── Panel derecho: Resumen ────────────────────────────────── */
    .p3-sidebar-panel {
        background: #fff;
        border-radius: var(--p3-radius);
        box-shadow: var(--p3-shadow-lg);
        border: 1px solid #f0f0f5;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .p3-sidebar-panel__header {
        padding: 14px 18px;
        background: linear-gradient(135deg, var(--p3-primary) 0%, #1d2a81 60%, var(--p3-accent) 100%);
        color: #fff;
    }
    .p3-sidebar-panel__header h6 {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        opacity: .85;
        margin: 0 0 6px;
    }
    .p3-sidebar-panel__total-amount {
        font-size: 1.7rem;
        font-weight: 900;
        letter-spacing: -.02em;
        line-height: 1;
    }
    .p3-sidebar-panel__total-label {
        font-size: .75rem;
        opacity: .7;
        margin-top: 2px;
    }
    .p3-sidebar-panel__body { padding: 14px 18px; }

    /* Filas del resumen */
    .p3-resumen-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        border-bottom: 1px dashed #f1f5f9;
        font-size: .85rem;
    }
    .p3-resumen-item:last-child { border-bottom: none; }
    .p3-resumen-item .concepto { color: #6b7280; }
    .p3-resumen-item .monto { font-weight: 700; color: #1f2937; }
    .p3-resumen-empty { text-align:center; color:#d1d5db; font-size:.83rem; padding: 10px 0; }

    /* ─── Panel de Pago ─────────────────────────────────────────── */
    .p3-pago-panel {
        background: #fff;
        border-radius: var(--p3-radius);
        box-shadow: var(--p3-shadow);
        border: 1px solid #f0f0f5;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .p3-pago-panel__header {
        padding: 12px 18px;
        background: #fafafa;
        border-bottom: 1px solid #f5f5f8;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .p3-pago-panel__body { padding: 16px 18px; }

    /* Grupos de abono */
    .p3-abono-item {
        background: #f8fafc;
        border: 1.5px solid #e8edf2;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 10px;
        transition: border-color .2s;
    }
    .p3-abono-item:focus-within { border-color: var(--p3-accent2); }
    .p3-abono-item__label {
        font-size: .78rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .p3-abono-item__max {
        font-size: .72rem;
        font-weight: 500;
        color: #9ca3af;
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 6px;
    }
    .p3-abono-item .input-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .p3-abono-item input[type="number"] {
        flex: 1;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .9rem;
        font-weight: 600;
        color: #1f2937;
        background: #fff;
        outline: none;
        transition: border-color .2s;
    }
    .p3-abono-item input[type="number"]:focus { border-color: var(--p3-accent); }
    .p3-btn-todo {
        padding: 7px 12px;
        font-size: .75rem;
        font-weight: 700;
        border-radius: 8px;
        border: 1.5px solid var(--p3-border);
        background: var(--p3-light);
        color: var(--p3-accent);
        cursor: pointer;
        white-space: nowrap;
        transition: all .15s;
    }
    .p3-btn-todo:hover { background: var(--p3-accent); color: #fff; border-color: var(--p3-accent); }
    .p3-abono-item__status {
        margin-top: 5px;
        min-height: 18px;
    }

    /* Badges de estado */
    .p3-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
    }
    .p3-badge.pagado  { background: #d1fae5; color: #065f46; }
    .p3-badge.parcial { background: #c7effe; color: #17226a; }
    .p3-badge.pendiente { background: #e2eefe; color: #18246e; }

    /* Total cobrado */
    .p3-cobrado-box {
        background: linear-gradient(135deg, var(--p3-light), #fff);
        border: 1.5px solid var(--p3-border);
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .p3-cobrado-box .label { font-size: .8rem; font-weight: 600; color: #162061; }
    .p3-cobrado-box .amount { font-size: 1.15rem; font-weight: 900; color: var(--p3-accent); }

    /* Campos de método / fecha / voucher / comentario */
    .p3-field-row { margin-bottom: 10px; }
    .p3-field-label {
        font-size: .75rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 4px;
        display: block;
    }
    .p3-input-sm {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .84rem;
        color: #1f2937;
        background: #fff;
        outline: none;
        transition: border-color .2s;
    }
    .p3-input-sm:focus { border-color: var(--p3-accent); }
    .p3-input-sm[readonly] { background: #f8fafc; color: #6b7280; }

    /* ─── Botones de acción ─────────────────────────────────────── */
    .p3-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .p3-btn-submit {
        flex: 1;
        padding: 10px 18px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(135deg, var(--p3-primary), var(--p3-accent));
        color: #fff;
        font-size: .88rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(33, 49, 150,.3);
        transition: all .2s;
    }
    .p3-btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(33, 49, 150,.4); }
    .p3-btn-back {
        padding: 10px 14px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #374151;
        font-size: .84rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all .15s;
    }
    .p3-btn-back:hover { border-color: #c0c5cf; background: #f8fafc; color: #1f2937; }
    .p3-btn-cancel {
        padding: 10px 14px;
        border-radius: 10px;
        border: 1.5px solid #e2eefe;
        background: #fff;
        color: #18246e;
        font-size: .84rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all .15s;
    }
    .p3-btn-cancel:hover { background: #e2eefe; color: #162061; }

    /* ─── Info banner ───────────────────────────────────────────── */
    .p3-info-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(90deg, #f5faff, #fff);
        border: 1px solid #d4ecfd;
        border-left: 3px solid var(--p3-accent);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 18px;
        font-size: .83rem;
        color: #6b7280;
    }
    .p3-info-banner strong { color: #1f2937; }
    .p3-info-banner .dot { color: #d1d5db; }

    /* ─── Extras resumen texto ──────────────────────────────────── */
    .p3-extras-hint {
        font-size: .78rem;
        color: #9ca3af;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .p3-extras-hint.has-items { color: var(--p3-accent); font-weight: 600; }

    /* ─── Grupos expandibles del resumen mensual ───────────────── */
    .p3-resumen-group {
        border-bottom: 1px dashed #f1f5f9;
    }
    .p3-resumen-group:last-child { border-bottom: none; }
    .p3-resumen-group__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        cursor: pointer;
        font-size: .85rem;
        user-select: none;
        transition: opacity .15s;
    }
    .p3-resumen-group__header:hover { opacity: .8; }
    .p3-resumen-group__header .concepto { color: #6b7280; }
    .p3-resumen-group__header .monto { font-weight: 700; color: #1f2937; }
    .p3-resumen-group__header .p3-chevron {
        color: #9ca3af;
        flex-shrink: 0;
        transition: transform .2s ease;
    }
    .p3-resumen-group.p3-expanded .p3-chevron { transform: rotate(180deg); }
    .p3-resumen-group__detail {
        display: none;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 8px;
        overflow: hidden;
        border: 1px solid #eef2f7;
    }
    .p3-resumen-group.p3-expanded .p3-resumen-group__detail { display: block; }
    .p3-month-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 10px;
        font-size: .79rem;
        border-bottom: 1px solid #eef2f7;
    }
    .p3-month-row:last-child { border-bottom: none; }
    .p3-mes-nombre { color: #6b7280; }
    .p3-mes-monto { font-weight: 600; color: #374151; font-variant-numeric: tabular-nums; }
    .p3-month-row.p3-especial { background: #ebfaff; }
    .p3-month-row.p3-especial .p3-mes-nombre { color: #17226a; }
    .p3-month-row.p3-especial .p3-mes-monto { color: #213196; }
    .p3-especial-tag {
        display: inline-block;
        font-size: .62rem;
        background: #c7effe;
        color: #17226a;
        border-radius: 4px;
        padding: 0 4px;
        margin-left: 4px;
        font-weight: 700;
        vertical-align: middle;
    }
    .p3-resumen-hint {
        font-size: .72rem;
        color: #9ca3af;
        text-align: center;
        padding: 2px 0 4px;
        letter-spacing: .01em;
    }

    /* ─── Validación de campos requeridos ───────────────────────── */
    .p3-field-invalid,
    .p3-field-invalid.p3-input-sm,
    select.p3-field-invalid {
        border-color: #f43f5e !important;
        background-color: #fff5f7 !important;
        outline: none;
        animation: p3Shake .3s ease;
    }
    .p3-field-invalid:focus,
    .p3-field-invalid.p3-input-sm:focus {
        border-color: #f43f5e !important;
        box-shadow: 0 0 0 3px rgba(244,63,94,.15) !important;
    }
    @keyframes p3Shake {
        0%,100% { transform: translateX(0); }
        25%      { transform: translateX(-4px); }
        75%      { transform: translateX(4px); }
    }
    </style>
@endpush

@section('sidebar')
    @if (auth()->user()?->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
@php
    $nivel   = session('matricula.nivel', '-');
    $grado   = session('matricula.grado', '-');
    $seccion = session('matricula.seccion', '-');
    $paso3 = isset($paso3) && is_array($paso3) ? $paso3 : [];

    $montoMatriculaFija   = (float) ($montoMatriculaFija ?? 160.00);
    $montoGastosInternos  = (float) ($montoGastosInternos ?? 100.00);
    $montoMatricula       = $montoMatriculaFija + $montoGastosInternos;
    $esPrimerHijo         = (bool) ($esPrimerHijo ?? true);
    $esMenorHijo          = (bool) ($esMenorHijo ?? false);
    $hermanos             = $hermanos ?? collect();
    $montoPension         = (float) ($tarifaPension->monto ?? 0);
    $esVacacionalPeriodo  = (bool) ($esVacacional ?? false);

    $periodoModelV = $matricula->periodo_academico_id
        ? \App\Models\PeriodoAcademico::find($matricula->periodo_academico_id) : null;
    [$mesInicioPeriodo, $mesFinPeriodo] = ($periodoModelV && method_exists($periodoModelV, 'rangoMesesPensiones'))
        ? $periodoModelV->rangoMesesPensiones() : [3, 12];
    $mesesExcluidosPeriodo = ($periodoModelV && method_exists($periodoModelV, 'mesesExcluidosPensiones'))
        ? $periodoModelV->mesesExcluidosPensiones() : [];
    $mesesGenerablesPeriodo = ($periodoModelV && method_exists($periodoModelV, 'mesesGenerablesDesdePeriodo'))
        ? $periodoModelV->mesesGenerablesDesdePeriodo()
        : (($periodoModelV && method_exists($periodoModelV, 'mesesGenerablesPensiones'))
            ? $periodoModelV->mesesGenerablesPensiones()
            : range($mesInicioPeriodo, $mesFinPeriodo));
    if (empty($mesesGenerablesPeriodo)) {
        $mesesGenerablesPeriodo = range($mesInicioPeriodo, $mesFinPeriodo);
        $mesesExcluidosPeriodo  = [];
    }
    $mesInicioGenerable = (int) $mesesGenerablesPeriodo[0];
    $mesFinGenerable    = (int) $mesesGenerablesPeriodo[count($mesesGenerablesPeriodo) - 1];

    $nombresMeses = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
    $nombresMesesLargo = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $tarifasEspecialesMes = is_array($tarifasEspecialesMes ?? null) ? $tarifasEspecialesMes : [];
    $libroMonto          = (float) ($libroMonto ?? 0);
    $libroNombre         = (string) ($libroNombre ?? '');
    $categoriaUniformeId = isset($categoriaUniformeId) ? (int) $categoriaUniformeId : null;

    $anioEscolarNum = (int) ($matricula->anioEscolar?->nombre ?? date('Y'));
    if ($anioEscolarNum < 2000) $anioEscolarNum = (int) date('Y');
    $numMesesTotal = count($mesesGenerablesPeriodo);

    $oldWizardForm = (string) old('_wizard_form', '');
    $oldWizardMatriculaId = (int) old('_wizard_matricula_id', 0);
    $usarOldPaso3 = $oldWizardForm === 'matricula_paso3' && $oldWizardMatriculaId === (int) ($matricula->id ?? 0);

    $genModoSeleccionado = $usarOldPaso3 ? old('gen_modo', $paso3['gen_modo'] ?? 'todo') : ($paso3['gen_modo'] ?? 'todo');
    $mesesGenerablesLookup = array_fill_keys($mesesGenerablesPeriodo, true);

    $mesGenIniRaw = $usarOldPaso3 ? old('mes_gen_inicio', $paso3['mes_gen_inicio'] ?? $mesInicioGenerable) : ($paso3['mes_gen_inicio'] ?? $mesInicioGenerable);
    $mesGenFinRaw = $usarOldPaso3 ? old('mes_gen_fin',    $paso3['mes_gen_fin']    ?? $mesFinGenerable)    : ($paso3['mes_gen_fin']    ?? $mesFinGenerable);
    $mesGenIniSeleccionado = is_numeric($mesGenIniRaw) ? (int) $mesGenIniRaw : $mesInicioGenerable;
    $mesGenFinSeleccionado = is_numeric($mesGenFinRaw) ? (int) $mesGenFinRaw : $mesFinGenerable;
    if (!isset($mesesGenerablesLookup[$mesGenIniSeleccionado])) $mesGenIniSeleccionado = $mesInicioGenerable;
    if (!isset($mesesGenerablesLookup[$mesGenFinSeleccionado])) $mesGenFinSeleccionado = $mesFinGenerable;
    if ($mesGenIniSeleccionado !== null && $mesGenFinSeleccionado !== null && $mesGenFinSeleccionado < $mesGenIniSeleccionado) $mesGenFinSeleccionado = $mesGenIniSeleccionado;
    if ($genModoSeleccionado === 'solo_matricula') { $mesGenIniSeleccionado = null; $mesGenFinSeleccionado = null; }

    $nMesesSeleccionado = 1;
    if ($mesGenIniSeleccionado !== null && $mesGenFinSeleccionado !== null && $mesGenIniSeleccionado === $mesInicioGenerable) {
        $nMesesSeleccionado = count(array_filter($mesesGenerablesPeriodo, fn($m) => $m >= $mesInicioGenerable && $m <= $mesGenFinSeleccionado));
        $nMesesSeleccionado = max(1, min($numMesesTotal, $nMesesSeleccionado));
    }


    $soloMatriculaConfirmSeleccionado = $usarOldPaso3
        ? (bool) old('solo_matricula_confirm', $paso3['solo_matricula_confirm'] ?? false)
        : (bool) ($paso3['solo_matricula_confirm'] ?? false);

    $fechaIso = old('fecha_pago', $paso3['fecha_pago'] ?? now('America/Lima')->toDateString());
    try { $fechaPe = \Carbon\Carbon::parse($fechaIso)->format('d/m/Y'); }
    catch (\Throwable $e) { $fechaIso = now('America/Lima')->toDateString(); $fechaPe = \Carbon\Carbon::parse($fechaIso)->format('d/m/Y'); }

    $carritoInicial = $paso3['carrito'] ?? [];
    if (!is_array($carritoInicial)) $carritoInicial = [];
@endphp

{{-- Wizard steps --}}
<div class="card jp-soft-card wizard-top mb-3">
    <div class="card-body py-3">
        <div class="wizard-steps">
            <div class="wizard-step"><div class="wizard-step__circle">1</div><span>Nivel y Grado</span></div>
            <div class="wizard-step"><div class="wizard-step__circle">2</div><span>Estudiante</span></div>
            <div class="wizard-step is-active"><div class="wizard-step__circle">3</div><span>Pago</span></div>
            <div class="wizard-step"><div class="wizard-step__circle">4</div><span>Cursos</span></div>
            <div class="wizard-step"><div class="wizard-step__circle">5</div><span>Resumen</span></div>
            <div class="wizard-progress"><span style="width:50%"></span></div>
        </div>
    </div>
</div>

<form id="formPaso3" method="POST" action="{{ route('secretaria.matriculas.paso3.guardar') }}" enctype="multipart/form-data" autocomplete="off" novalidate>
    @csrf
    <input type="hidden" name="_wizard_form"        value="matricula_paso3">
    <input type="hidden" name="_wizard_matricula_id" value="{{ $matricula->id }}">
    <input type="hidden" id="hidMesIni"         name="mes_gen_inicio"         value="{{ $mesGenIniSeleccionado ?? '' }}">
    <input type="hidden" id="hidMesFin"         name="mes_gen_fin"            value="{{ $mesGenFinSeleccionado ?? '' }}">

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:12px;">
            <div class="fw-bold mb-1"><i data-feather="alert-circle" style="width:15px;height:15px;"></i> Revisa los campos:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li style="font-size:.85rem;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Banner info ─────────────────────────────────────────────── --}}
    <div class="p3-info-banner">
        <i data-feather="user" style="width:14px;height:14px;color:#213196;flex-shrink:0;"></i>
        <span>
            <strong>{{ $nivel }}</strong>
            <span class="dot">·</span> {{ $grado }}
            <span class="dot">·</span> {{ $seccion }}
            <span class="dot">·</span> Año {{ $matricula->anioEscolar->nombre ?? date('Y') }}
            <span class="dot">·</span> {{ $periodoModelV?->nombre ?? 'Sin periodo' }}
        </span>
    </div>

    <div class="row g-3 align-items-start">

        {{-- ══════════════ COLUMNA IZQUIERDA ══════════════ --}}
        <div class="col-lg-7">

            {{-- 1. Pensiones --}}
            @if (!$esVacacionalPeriodo)
            <div class="p3-config-card">
                <div class="p3-config-card__header">
                    <div class="p3-config-card__num">1</div>
                    <div>
                        <span class="p3-config-card__title">Pensiones</span>
                        <span class="p3-config-card__subtitle">— ¿cuántos meses generar?</span>
                    </div>
                </div>
                <div class="p3-config-card__body">

                    {{-- Tarifas chips --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <div class="p3-tarifa-chip">
                            <span class="chip-label">Matrícula</span>
                            <span class="chip-value">S/ {{ number_format($montoMatricula, 2) }}</span>
                            <span class="chip-sub">cargo único</span>
                        </div>
                        <div class="p3-tarifa-chip">
                            <span class="chip-label">Pensión</span>
                            <span class="chip-value">S/ {{ number_format($montoPension, 2) }}</span>
                            <span class="chip-sub">por mes</span>
                        </div>
                    </div>

                    {{-- Pills de modo --}}
                    <div class="p3-mode-pills mb-1" id="pillsGenModo">
                        <button type="button" class="p3-mode-pill {{ in_array($genModoSeleccionado, ['todo','n_meses']) ? 'active' : '' }}" data-modo="todo">
                            Todo el periodo
                            <small>({{ $nombresMeses[$mesInicioGenerable] }}–{{ $nombresMeses[$mesFinGenerable] }})</small>
                        </button>
                        <button type="button" class="p3-mode-pill {{ $genModoSeleccionado === 'rango' ? 'active' : '' }}" data-modo="rango">
                            Rango personalizado
                        </button>
                        <button type="button" class="p3-mode-pill {{ $genModoSeleccionado === 'solo_matricula' ? 'active' : '' }}" data-modo="solo_matricula">
                            Solo matr&iacute;cula
                        </button>
                    </div>

                    {{-- Radios ocultos (compatibilidad backend) --}}
                    @foreach(['todo','rango','solo_matricula'] as $m)
                        <input type="radio" name="gen_modo" value="{{ $m }}" id="genRadio_{{ $m }}" style="display:none;"
                            @checked(in_array($genModoSeleccionado, ['todo','n_meses']) ? $m === 'todo' : $genModoSeleccionado === $m)>
                    @endforeach

                    {{-- Sub-panel Rango --}}
                    <div id="panelRango" class="p3-sub-panel" style="display:none;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <label style="font-size:.83rem;color:#374151;">Desde:</label>
                                <select class="form-select form-select-sm" id="selMesIni" style="width:auto;">
                                    @foreach ($mesesGenerablesPeriodo as $m)
                                        <option value="{{ $m }}" @selected($m === ($mesGenIniSeleccionado ?? $mesInicioGenerable))>{{ $nombresMesesLargo[$m] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label style="font-size:.83rem;color:#374151;">Hasta:</label>
                                <select class="form-select form-select-sm" id="selMesFin" style="width:auto;">
                                    @foreach ($mesesGenerablesPeriodo as $m)
                                        <option value="{{ $m }}" @selected($m === ($mesGenFinSeleccionado ?? $mesFinGenerable))>{{ $nombresMesesLargo[$m] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @endif

            {{-- 2. Descuentos --}}
            <div class="p3-config-card">
                <div class="p3-config-card__header">
                    <div class="p3-config-card__num">{{ $esVacacionalPeriodo ? 1 : 2 }}</div>
                    <div>
                        <span class="p3-config-card__title">Descuentos</span>
                        <span class="p3-config-card__subtitle">— aplican sobre cada pensión</span>
                    </div>
                </div>
                <div class="p3-config-card__body">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="descuentoMonto" style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Monto (S/)</label>
                            <input type="number" id="descuentoMonto" name="descuento_monto"
                                min="0" step="0.01" placeholder="0.00"
                                value="{{ old('descuento_monto', $paso3['descuento_monto'] ?? '') }}"
                                style="width:110px;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.9rem;font-family:inherit;">
                        </div>
                        <div class="col">
                            <label for="descuentoConcepto" style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Concepto</label>
                            <input type="text" id="descuentoConcepto" name="descuento_concepto"
                                placeholder="Ej: Beca excelencia, hermanos, convenio..."
                                maxlength="120"
                                value="{{ old('descuento_concepto', $paso3['descuento_concepto'] ?? '') }}"
                                style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.9rem;font-family:inherit;">
                        </div>
                    </div>
                    <p style="font-size:.77rem;color:#9ca3af;margin:6px 0 0;">Se resta de cada pensión mensual. Dejar en 0 para no aplicar.</p>
                </div>
            </div>

            {{-- 3. Extras / Productos --}}
            <div class="p3-config-card">
                <div class="p3-config-card__header">
                    <div class="p3-config-card__num">{{ $esVacacionalPeriodo ? 2 : 3 }}</div>
                    <div class="flex-1">
                        <span class="p3-config-card__title">Extras / Uniformes</span>
                        <span class="p3-config-card__subtitle">— opcional</span>
                    </div>
                    <button type="button" class="p3-collapse-toggle" data-bs-toggle="collapse" data-bs-target="#extrasCollapse">
                        <i data-feather="chevron-down" style="width:14px;height:14px;"></i>
                        Gestionar
                    </button>
                </div>
                <div class="p3-config-card__body" style="padding-top:10px;padding-bottom:10px;">
                    <div id="extrasResumenTexto" class="p3-extras-hint">
                        <i data-feather="shopping-bag" style="width:13px;height:13px;"></i>
                        Sin extras agregados.
                    </div>
                </div>
                <div class="collapse {{ !empty($carritoInicial) ? 'show' : '' }}" id="extrasCollapse">
                    <div style="padding: 0 18px 16px;">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalProductos"
                                style="background:linear-gradient(135deg,#f8fafc,#fff);border:1.5px solid #e2e8f0;color:#374151;border-radius:8px;font-size:.8rem;">
                                <i data-feather="plus" style="width:13px;height:13px;"></i> Agregar producto
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size:.83rem;">
                                <thead style="background:#f8fafc;">
                                    <tr>
                                        <th style="border-radius:8px 0 0 8px;">Producto</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-center" style="width:70px;">Cant.</th>
                                        <th class="text-end">Subtotal</th>
                                        <th style="border-radius:0 8px 8px 0;"></th>
                                    </tr>
                                </thead>
                                <tbody id="carritoBody">
                                    <tr id="carritoVacio">
                                        <td colspan="5" class="text-center text-muted py-3">Sin productos agregados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="carritoInputs"></div>
                        <input type="hidden" name="libros_monto" value="{{ $libroMonto }}">
                    </div>
                </div>
            </div>


        </div>{{-- /col izquierda --}}

        {{-- ══════════════ COLUMNA DERECHA ══════════════ --}}
        <div class="col-lg-5">
            <div class="p3-sticky">

                {{-- Panel: Resumen de deudas --}}
                <div class="p3-sidebar-panel">
                    <div class="p3-sidebar-panel__header">
                        <h6><i data-feather="list" style="width:12px;height:12px;"></i>&nbsp; Total a generar</h6>
                        <div class="p3-sidebar-panel__total-amount" id="previewTotalGen">S/ 0.00</div>
                        <div class="p3-sidebar-panel__total-label">deudas que se crearán al confirmar</div>
                    </div>
                    <div class="p3-sidebar-panel__body">
                        <div id="resumenDeudas">
                            <div class="p3-resumen-empty">Calculando...</div>
                        </div>
                    </div>
                    {{-- tabla oculta compat JS --}}
                    <table style="display:none;"><tbody id="previewBody"></tbody></table>
                </div>

                {{-- Panel: Pago de hoy --}}
                <div class="p3-pago-panel">
                    <div class="p3-pago-panel__header">
                        <i data-feather="credit-card" style="width:13px;height:13px;color:#213196;"></i>
                        Pago de hoy
                        <span style="font-size:.7rem;font-weight:400;color:#9ca3af;margin-left:4px;">(opcional)</span>
                    </div>
                    <div class="p3-pago-panel__body">

                        {{-- Abono matrícula --}}
                        @if(!$esVacacionalPeriodo)
                        <div class="p3-abono-item" id="abonoItemMatricula">
                            <div class="p3-abono-item__label">
                                Abono a Matrícula
                                <span class="p3-abono-item__max" id="maxMatriculaLabel">Máx S/ {{ number_format($montoMatricula, 2) }}</span>
                            </div>
                            <div class="input-row">
                                <input type="number" step="0.01" min="0" name="abono_matricula" id="abonoMatricula"
                                    placeholder="0.00"
                                    value="{{ old('abono_matricula', $paso3['abono_matricula'] ?? '') }}">
                                <button type="button" class="p3-btn-todo" id="btnPagarTodoMat">Todo</button>
                            </div>
                            <div class="p3-abono-item__status">
                                <span id="saldoMatBadge" class="p3-badge" style="display:none;"></span>
                            </div>
                        </div>

                        {{-- Abono extras/productos --}}
                        <div class="p3-abono-item" id="abonoItemExtras"{{ empty($carritoInicial) ? ' style="display:none;"' : '' }}>
                            <div class="p3-abono-item__label">
                                Abono a Extras / Productos
                                <span class="p3-abono-item__max" id="maxExtrasLabel">Máx S/ 0.00</span>
                            </div>
                            <div class="input-row">
                                <input type="number" step="0.01" min="0" name="abono_extras" id="abonoExtras"
                                    placeholder="0.00"
                                    value="{{ old('abono_extras', $paso3['abono_extras'] ?? '') }}">
                                <button type="button" class="p3-btn-todo" id="btnPagarTodoExt">Todo</button>
                            </div>
                            <div class="p3-abono-item__status">
                                <span id="saldoExtBadge" class="p3-badge" style="display:none;"></span>
                            </div>
                        </div>
                        @endif

                        {{-- Abono libros --}}
                        @if($libroMonto > 0)
                        <div class="p3-abono-item" id="abonoItemLibros">
                            <div class="p3-abono-item__label">
                                Abono a {{ $libroNombre ?: 'Libros' }}
                                <span class="p3-abono-item__max" id="maxLibrosLabel">Máx S/ {{ number_format($libroMonto, 2) }}</span>
                            </div>
                            <div class="input-row">
                                <input type="number" step="0.01" min="0" name="abono_libros" id="abonoLibros"
                                    placeholder="0.00"
                                    value="{{ old('abono_libros', $paso3['abono_libros'] ?? '') }}">
                                <button type="button" class="p3-btn-todo" id="btnPagarTodoLib">Todo</button>
                            </div>
                            <div class="p3-abono-item__status">
                                <span id="saldoLibBadge" class="p3-badge" style="display:none;"></span>
                            </div>
                        </div>
                        @endif

                        {{-- Abono pensiones --}}
                        <div class="p3-abono-item" id="abonoItemPensiones">
                            <div class="p3-abono-item__label">
                                Abono a Pensiones
                                <span class="p3-abono-item__max" id="maxPensionesLabel">Máx S/ 0.00</span>
                            </div>
                            <div class="input-row">
                                <input type="number" step="0.01" min="0" name="abono_pensiones" id="abonoPensiones"
                                    placeholder="0.00"
                                    value="{{ old('abono_pensiones', $paso3['abono_pensiones'] ?? '') }}">
                                <button type="button" class="p3-btn-todo" id="btnPagarTodoPen">Todo</button>
                            </div>
                            <div class="p3-abono-item__status">
                                <span id="saldoPenBadge" class="p3-badge" style="display:none;"></span>
                            </div>
                        </div>

                        {{-- Total cobrado --}}
                        <div class="p3-cobrado-box">
                            <span class="label">Total cobrado hoy</span>
                            <span class="amount" id="montoPagadoDisplay">S/ 0.00</span>
                            <input type="hidden" name="monto_pagado" id="montoPagado" value="0.00">
                        </div>

                        {{-- Fecha de pago (oculta) --}}
                        <input type="hidden" name="fecha_pago" value="{{ $fechaIso }}">

                        {{-- Medios de pago (multi-método) --}}
                        <div class="p3-field-row" id="seccMediosPago" style="display:none;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <span class="p3-field-label" style="margin-bottom:0;">Medios de pago</span>
                                <button type="button" id="btnAgregarMedio"
                                    style="font-size:.72rem;padding:2px 10px;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;color:#166534;cursor:pointer;font-weight:600;">
                                    + Agregar
                                </button>
                            </div>
                            <div id="listaMedios"></div>
                            <div id="alertaMedios" style="display:none;font-size:.75rem;color:#1f2e8f;margin-top:4px;padding:4px 8px;background:#f2f7fe;border-radius:5px;"></div>
                            <div id="totalMediosBox" style="display:none;font-size:.76rem;text-align:right;margin-top:4px;color:#374151;"></div>
                        </div>

                        {{-- Voucher --}}
                        <div class="p3-field-row">
                            <span class="p3-field-label">Voucher <span style="color:#d1d5db;">(opcional)</span></span>
                            <input type="file" class="p3-input-sm" name="voucher" accept=".jpg,.jpeg,.png,.pdf" style="padding:5px 10px;">
                        </div>

                        {{-- Comentario --}}
                        <div class="p3-field-row mb-0">
                            <span class="p3-field-label">Comentario</span>
                            <input type="text" class="p3-input-sm" name="comentario"
                                placeholder="Detalle del pago..."
                                value="{{ old('comentario', $paso3['comentario'] ?? '') }}">
                        </div>

                    </div>
                </div>

                {{-- Acciones --}}
                <div class="p3-actions">
                    <a href="{{ route('secretaria.matriculas.paso2') }}" class="p3-btn-back">
                        <i data-feather="arrow-left" style="width:14px;height:14px;"></i> Volver
                    </a>
                    <a href="{{ route('secretaria.matriculas.index') }}" class="p3-btn-cancel">
                        Cancelar
                    </a>
                    <button type="button" id="btnContinuar" class="p3-btn-submit">
                        Continuar <i data-feather="arrow-right" style="width:15px;height:15px;"></i>
                    </button>
                </div>

            </div>
        </div>{{-- /col derecha --}}

    </div>{{-- /row --}}
</form>

{{-- MODAL PRODUCTOS ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#172269,#213196);border:none;padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff;font-size:.95rem;">
                    <i data-feather="shopping-bag" style="width:16px;height:16px;"></i>&nbsp; Agregar productos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <input type="text" class="form-control form-control-sm" id="qProducto" placeholder="Buscar por nombre o SKU...">
                    <button type="button" class="btn btn-sm btn-primary" id="btnBuscarProd">Buscar</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle" style="font-size:.84rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center" style="width:100px;">Cant.</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="productosBody">
                            <tr><td colspan="5" class="text-center text-muted py-3">Busca un producto&hellip;</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Listo</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Constantes PHP → JS ──────────────────────────────────────
    const MONTO_MATRICULA_FIJA   = @json($montoMatriculaFija);
    const MONTO_GASTOS_INTERNOS  = @json($montoGastosInternos);
    const MONTO_MATRICULA        = MONTO_MATRICULA_FIJA + MONTO_GASTOS_INTERNOS;
    const MONTO_PENSION          = @json($montoPension);
    const MES_INICIO_PERIODO     = @json($mesInicioPeriodo);
    const MES_FIN_PERIODO        = @json($mesFinPeriodo);
    const MESES_EXCLUIDOS        = @json(array_values($mesesExcluidosPeriodo));
    const MESES_ACTIVOS_PERIODO  = @json(array_values($mesesGenerablesPeriodo));
    const ANIO                   = @json($anioEscolarNum);
    const ES_VACACIONAL          = @json($esVacacionalPeriodo);
    const HAS_PASO3_CONFIG       = @json(!empty($paso3));
    const NOMBRES_MESES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const TARIFAS_ESPECIALES_MES = @json($tarifasEspecialesMes); // {7: 260, 12: 260, ...}
    const CATEGORIA_UNIFORME_ID  = @json($categoriaUniformeId);
    const LIBRO_MONTO            = @json($libroMonto);
    const LIBRO_NOMBRE           = @json($libroNombre);

    const MES_INICIO_ACTIVO = Array.isArray(MESES_ACTIVOS_PERIODO) && MESES_ACTIVOS_PERIODO.length
        ? Number(MESES_ACTIVOS_PERIODO[0]) : MES_INICIO_PERIODO;
    const MES_FIN_ACTIVO = Array.isArray(MESES_ACTIVOS_PERIODO) && MESES_ACTIVOS_PERIODO.length
        ? Number(MESES_ACTIVOS_PERIODO[MESES_ACTIVOS_PERIODO.length - 1]) : MES_FIN_PERIODO;

    let genMesIni = MES_INICIO_ACTIVO, genMesFin = MES_FIN_ACTIVO;

    // ── DOM ──────────────────────────────────────────────────────
    const descuentoMontoEl   = document.getElementById('descuentoMonto');
    const abonoMatriculaEl   = document.getElementById('abonoMatricula');
    const abonoPensionesEl   = document.getElementById('abonoPensiones');
    const montoPagadoEl      = document.getElementById('montoPagado');
    const montoPagadoDisplay = document.getElementById('montoPagadoDisplay');
    const previewBody        = document.getElementById('previewBody');
    const previewTotal       = document.getElementById('previewTotalGen');
    const resumenDeudas      = document.getElementById('resumenDeudas');
    const soloMatriculaConfirmEl = document.getElementById('soloMatriculaConfirm');
    const hidMesIni          = document.getElementById('hidMesIni');
    const hidMesFin          = document.getElementById('hidMesFin');
    const maxPensionesLabel  = document.getElementById('maxPensionesLabel');
    const maxMatriculaLabel  = document.getElementById('maxMatriculaLabel');
    const maxExtrasLabel     = document.getElementById('maxExtrasLabel');
    const abonoExtrasEl      = document.getElementById('abonoExtras');
    const saldoMatBadge      = document.getElementById('saldoMatBadge');
    const saldoPenBadge      = document.getElementById('saldoPenBadge');
    const saldoExtBadge      = document.getElementById('saldoExtBadge');
    const saldoLibBadge      = document.getElementById('saldoLibBadge');
    const abonoItemExtras    = document.getElementById('abonoItemExtras');
    const abonoItemLibros    = document.getElementById('abonoItemLibros');
    const abonoLibrosEl      = document.getElementById('abonoLibros');
    const maxLibrosLabel     = document.getElementById('maxLibrosLabel');
    const extrasResumenTexto = document.getElementById('extrasResumenTexto');

    let abonoMatriculaDirty = Boolean(abonoMatriculaEl?.value?.trim());
    let abonoPensionesDirty = Boolean(abonoPensionesEl?.value?.trim());
    let abonoExtrasDirty    = Boolean(abonoExtrasEl?.value?.trim());
    let abonoLibrosDirty    = Boolean(abonoLibrosEl?.value?.trim());

    const urlBuscar     = @json(route('secretaria.productos.buscar'));
    const INITIAL_CARRITO = @json($carritoInicial);
    const productosBody = document.getElementById('productosBody');
    const carritoBody   = document.getElementById('carritoBody');
    const carritoVacio  = document.getElementById('carritoVacio');
    const carritoInputs = document.getElementById('carritoInputs');

    const carrito = {};
    if (Array.isArray(INITIAL_CARRITO)) {
        INITIAL_CARRITO.forEach(item => {
            const id = Number(item?.id ?? item?.producto_id ?? 0);
            const nombre = String(item?.nombre ?? '').trim();
            const precio = Number(item?.precio ?? 0);
            let cantidad = Number(item?.cantidad ?? 1);
            let stock    = Number(item?.stock ?? 0);
            if (!Number.isFinite(id) || id <= 0 || !nombre) return;
            if (!Number.isFinite(cantidad) || cantidad < 1) cantidad = 1;
            if (!Number.isFinite(stock) || stock < 1) stock = cantidad;
            if (cantidad > stock) cantidad = stock;
            carrito[id] = { id, nombre, precio, cantidad, stock };
        });
    }

    const money      = n => `S/ ${Number(n||0).toFixed(2)}`;
    const moneyAprox = n => `~S/ ${Number(n||0).toFixed(2)}`;

    // ── Helpers ──────────────────────────────────────────────────
    function getPensionParaMes(mes) {
        const base = (TARIFAS_ESPECIALES_MES && TARIFAS_ESPECIALES_MES[mes] !== undefined)
            ? Number(TARIFAS_ESPECIALES_MES[mes])
            : MONTO_PENSION;
        const descuento = Math.max(0, parseFloat(descuentoMontoEl?.value || 0) || 0);
        return Math.max(0, Math.round((base - descuento) * 100) / 100);
    }
    function getExtrasTotal() {
        return Object.values(carrito).reduce((acc, it) => acc + it.precio * it.cantidad, 0);
    }
    function getMesesActivosEnRango(mesIni, mesFin) {
        if (!Number.isInteger(mesIni) || !Number.isInteger(mesFin) || mesFin < mesIni) return [];
        if (!Array.isArray(MESES_ACTIVOS_PERIODO) || !MESES_ACTIVOS_PERIODO.length) return [];
        return MESES_ACTIVOS_PERIODO.map(m => Number(m)).filter(m => Number.isInteger(m) && m >= mesIni && m <= mesFin);
    }
    function getNumeroMesesSeleccionados() { return getMesesActivosEnRango(genMesIni, genMesFin).length; }

    function saldoBadgeInfo(abono, maximo) {
        if (maximo <= 0) return null;
        if (abono <= 0) return { clase: 'pendiente', texto: 'Pendiente' };
        if (abono >= maximo) return { clase: 'pagado', texto: '✓ Pagado' };
        return { clase: 'parcial', texto: `Saldo: ${money(maximo - abono)}` };
    }

    // ── Preview en sidebar ───────────────────────────────────────
    function actualizarPreview() {
        const extras        = getExtrasTotal();
        const mesesPension  = getMesesActivosEnRango(genMesIni, genMesFin);
        const nMeses        = mesesPension.length;

        // Per-month pension (respeta meses especiales)
        let totalPensiones = 0;
        const detallePensiones = [];
        mesesPension.forEach(mes => {
            const p = getPensionParaMes(mes);
            totalPensiones += p;
            const esEspecial = TARIFAS_ESPECIALES_MES && TARIFAS_ESPECIALES_MES[mes] !== undefined;
            detallePensiones.push({ mes, monto: p, especial: esEspecial });
        });
        totalPensiones = Math.round(totalPensiones * 100) / 100;

        const totalMatricula = ES_VACACIONAL ? 0 : MONTO_MATRICULA;
        const totalGen       = totalMatricula + totalPensiones + extras + LIBRO_MONTO;

        // Mostrar/ocultar abono extras
        if (abonoItemExtras) abonoItemExtras.style.display = (extras > 0 && !ES_VACACIONAL) ? '' : 'none';

        // Hiddens
        if (hidMesIni) hidMesIni.value = Number.isInteger(genMesIni) ? String(genMesIni) : '';
        if (hidMesFin) hidMesFin.value = Number.isInteger(genMesFin) ? String(genMesFin) : '';

        // ── Resumen sidebar con desglose mensual ──────────────────
        const descMonto = Math.max(0, parseFloat(descuentoMontoEl?.value || 0) || 0);
        const descTag = descMonto > 0
            ? `<span style="font-size:.65rem;background:#c7effe;color:#17226a;border-radius:4px;padding:1px 5px;margin-left:4px;">-S/${descMonto.toFixed(2)}/mes</span>` : '';
        const chevronSvg = `<svg class="p3-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>`;

        let filas = '';

        // Matrícula
        if (!ES_VACACIONAL && MONTO_MATRICULA > 0) {
            filas += `<div class="p3-resumen-item">
                <span class="concepto">Matrícula <small style="opacity:.6;">(único)</small></span>
                <span class="monto">${money(MONTO_MATRICULA)}</span>
            </div>`;
        }

        // Pensiones — expandible con detalle por mes
        if (nMeses > 0) {
            const monthRows = detallePensiones.map(d => `
                <div class="p3-month-row${d.especial ? ' p3-especial' : ''}">
                    <span class="p3-mes-nombre">Pensión ${NOMBRES_MESES[d.mes]}${d.especial ? '<span class="p3-especial-tag">Especial</span>' : ''}</span>
                    <span class="p3-mes-monto">${money(d.monto)}</span>
                </div>`).join('');

            filas += `<div class="p3-resumen-group">
                <div class="p3-resumen-group__header" onclick="this.parentElement.classList.toggle('p3-expanded')">
                    <div><span class="concepto">Pensiones${descTag}</span> <small style="opacity:.55;font-size:.74rem;">${nMeses} mes${nMeses!==1?'es':''}</small></div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="monto">${money(totalPensiones)}</span>
                        ${chevronSvg}
                    </div>
                </div>
                <div class="p3-resumen-group__detail">${monthRows}</div>
            </div>`;
        }

        // Extras
        if (extras > 0) {
            filas += `<div class="p3-resumen-item">
                <span class="concepto">Extras / Uniformes</span>
                <span class="monto">${money(extras)}</span>
            </div>`;
        }

        // Libros (automático por nivel)
        if (LIBRO_MONTO > 0) {
            filas += `<div class="p3-resumen-item">
                <span class="concepto">${LIBRO_NOMBRE || 'Libros'} <small style="opacity:.6;">(automático)</small></span>
                <span class="monto">${money(LIBRO_MONTO)}</span>
            </div>`;
        }

        if (!filas) filas = `<div class="p3-resumen-empty">Solo matrícula, sin pensiones.</div>`;

        if (nMeses > 1) {
            filas += `<div class="p3-resumen-hint">Toca Pensiones para ver el desglose</div>`;
        }

        if (resumenDeudas) resumenDeudas.innerHTML = filas;

        const totalStr = money(totalGen);
        if (previewTotal) previewTotal.textContent = totalStr;

        // Compat previewBody (oculto, solo para compat interna)
        if (previewBody) {
            let rows = '';
            if (!ES_VACACIONAL && MONTO_MATRICULA_FIJA > 0) rows += `<tr><td>Matrícula</td><td>1</td><td>${money(MONTO_MATRICULA_FIJA)}</td><td>${money(MONTO_MATRICULA_FIJA)}</td></tr>`;
            if (!ES_VACACIONAL) rows += `<tr><td>Matrícula (Gastos Internos)${MONTO_GASTOS_INTERNOS < 100 ? ' <small style="color:#15803d">▼5%</small>' : ''}</td><td>1</td><td>${money(MONTO_GASTOS_INTERNOS)}</td><td>${money(MONTO_GASTOS_INTERNOS)}</td></tr>`;
            if (nMeses > 0) rows += `<tr><td>Pensión</td><td>${nMeses}</td><td>—</td><td>${money(totalPensiones)}</td></tr>`;
            previewBody.innerHTML = rows;
        }

        // Máximos separados
        const totalMatriculaAbonable  = Math.max(totalMatricula, 0);
        const totalExtrasAbonable     = Math.max(extras, 0);
        const totalPensionesAbonables = Math.max(totalPensiones, 0);

        if (maxMatriculaLabel) maxMatriculaLabel.textContent = `Máx ${money(totalMatriculaAbonable)}`;
        if (maxExtrasLabel)    maxExtrasLabel.textContent    = `Máx ${money(totalExtrasAbonable)}`;
        if (maxLibrosLabel)    maxLibrosLabel.textContent    = `Máx ${money(LIBRO_MONTO)}`;
        if (maxPensionesLabel) maxPensionesLabel.textContent = `Máx ${money(totalPensionesAbonables)}`;

        // Auto-rellenar solo si el usuario aún no tocó el campo
        if (abonoMatriculaEl && !abonoMatriculaDirty) {
            abonoMatriculaEl.value = totalMatriculaAbonable.toFixed(2);
        }
        if (abonoExtrasEl && !abonoExtrasDirty) {
            abonoExtrasEl.value = totalExtrasAbonable.toFixed(2);
        }
        if (abonoLibrosEl && !abonoLibrosDirty) {
            abonoLibrosEl.value = LIBRO_MONTO.toFixed(2);
        }
        if (abonoPensionesEl && !abonoPensionesDirty) {
            abonoPensionesEl.value = totalPensionesAbonables.toFixed(2);
        }

        // Leer valores actuales SIN sobreescribir
        const abMat = abonoMatriculaEl ? (Number.parseFloat(abonoMatriculaEl.value) || 0) : 0;
        const abExt = abonoExtrasEl    ? (Number.parseFloat(abonoExtrasEl.value)    || 0) : 0;
        const abLib = abonoLibrosEl    ? (Number.parseFloat(abonoLibrosEl.value)    || 0) : 0;
        const abPen = abonoPensionesEl ? (Number.parseFloat(abonoPensionesEl.value) || 0) : 0;

        // Badges
        if (saldoMatBadge) {
            const b = saldoBadgeInfo(Math.min(abMat, totalMatriculaAbonable), totalMatriculaAbonable);
            if (b) { saldoMatBadge.style.display = 'inline-flex'; saldoMatBadge.className = `p3-badge ${b.clase}`; saldoMatBadge.textContent = b.texto; }
            else saldoMatBadge.style.display = 'none';
        }
        if (saldoExtBadge) {
            const b = saldoBadgeInfo(Math.min(abExt, totalExtrasAbonable), totalExtrasAbonable);
            if (b) { saldoExtBadge.style.display = 'inline-flex'; saldoExtBadge.className = `p3-badge ${b.clase}`; saldoExtBadge.textContent = b.texto; }
            else saldoExtBadge.style.display = 'none';
        }
        if (saldoLibBadge) {
            const b = saldoBadgeInfo(Math.min(abLib, LIBRO_MONTO), LIBRO_MONTO);
            if (b) { saldoLibBadge.style.display = 'inline-flex'; saldoLibBadge.className = `p3-badge ${b.clase}`; saldoLibBadge.textContent = b.texto; }
            else saldoLibBadge.style.display = 'none';
        }
        if (saldoPenBadge) {
            const b = saldoBadgeInfo(Math.min(abPen, totalPensionesAbonables), totalPensionesAbonables);
            if (b) { saldoPenBadge.style.display = 'inline-flex'; saldoPenBadge.className = `p3-badge ${b.clase}`; saldoPenBadge.textContent = b.texto; }
            else saldoPenBadge.style.display = 'none';
        }
        const totalCobrado = abMat + abExt + abLib + abPen;
        if (montoPagadoEl) montoPagadoEl.value = totalCobrado.toFixed(2);
        if (montoPagadoDisplay) montoPagadoDisplay.textContent = money(totalCobrado);
        // Mostrar/ocultar sección medios y actualizar su total
        const seccMedios = document.getElementById('seccMediosPago');
        if (seccMedios) seccMedios.style.display = totalCobrado > 0 ? '' : 'none';
        if (typeof window.actualizarTotalMedios === 'function') window.actualizarTotalMedios();
    }

    // ── Modo generación ──────────────────────────────────────────
    function updateGenMode() {
        const modo = document.querySelector('[name=gen_modo]:checked')?.value ?? 'todo';

        document.querySelectorAll('#pillsGenModo .p3-mode-pill').forEach(b => b.classList.toggle('active', b.dataset.modo === modo));

        const soloMatricula = modo === 'solo_matricula';
        const confirmWrap = document.getElementById('soloMatriculaConfirmWrap');
        if (confirmWrap) confirmWrap.style.display = soloMatricula ? '' : 'none';
        if (soloMatriculaConfirmEl) { soloMatriculaConfirmEl.disabled = !soloMatricula; if (!soloMatricula) soloMatriculaConfirmEl.checked = false; }

        document.getElementById('panelRango').style.display = modo === 'rango' ? '' : 'none';

        const abonoItemPensiones = document.getElementById('abonoItemPensiones');
        if (abonoItemPensiones) abonoItemPensiones.style.display = soloMatricula ? 'none' : '';

        if (soloMatricula) {
            genMesIni = null; genMesFin = null;
            if (abonoExtrasEl) { abonoExtrasEl.value = '0.00'; abonoExtrasDirty = false; }
        } else if (modo === 'todo') {
            genMesIni = MES_INICIO_ACTIVO; genMesFin = MES_FIN_ACTIVO;
        } else if (modo === 'rango') {
            genMesIni = parseInt(document.getElementById('selMesIni').value);
            genMesFin = parseInt(document.getElementById('selMesFin').value);
            if (genMesFin < genMesIni) { document.getElementById('selMesFin').value = genMesIni; genMesFin = genMesIni; }
        }
        actualizarPreview();
    }

    // ── Pills → radios ───────────────────────────────────────────
    document.querySelectorAll('#pillsGenModo .p3-mode-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            const modo = btn.dataset.modo;
            const radio = document.getElementById(`genRadio_${modo}`);
            if (radio) radio.checked = true;
            if (soloMatriculaConfirmEl && modo !== 'solo_matricula') soloMatriculaConfirmEl.checked = false;
            updateGenMode();
        });
    });

    document.getElementById('selMesIni')?.addEventListener('change', () => {
        const ini = parseInt(document.getElementById('selMesIni').value);
        const finEl = document.getElementById('selMesFin');
        if (parseInt(finEl.value) < ini) finEl.value = ini;
        updateGenMode();
    });
    document.getElementById('selMesFin')?.addEventListener('change', updateGenMode);

    descuentoMontoEl?.addEventListener('input', () => { abonoPensionesDirty = false; actualizarPreview(); });
    // Mientras escribe: solo marcar dirty y actualizar totales/badges (sin tocar el campo)
    abonoMatriculaEl?.addEventListener('input', () => { abonoMatriculaDirty = true; actualizarPreview(); });
    abonoExtrasEl?.addEventListener('input',    () => { abonoExtrasDirty    = true; actualizarPreview(); });
    abonoLibrosEl?.addEventListener('input',    () => { abonoLibrosDirty    = true; actualizarPreview(); });
    abonoPensionesEl?.addEventListener('input', () => { abonoPensionesDirty = true; actualizarPreview(); });

    // Al salir del campo: normalizar (clamp al máximo + 2 decimales)
    abonoMatriculaEl?.addEventListener('blur', () => {
        const max = Number.parseFloat(maxMatriculaLabel?.textContent?.replace(/[^0-9.]/g, '') || '0');
        let v = Number.parseFloat(abonoMatriculaEl.value);
        if (!Number.isFinite(v) || v < 0) v = 0;
        if (v > max) v = max;
        abonoMatriculaEl.value = v.toFixed(2);
        actualizarPreview();
    });
    abonoExtrasEl?.addEventListener('blur', () => {
        const max = Number.parseFloat(maxExtrasLabel?.textContent?.replace(/[^0-9.]/g, '') || '0');
        let v = Number.parseFloat(abonoExtrasEl.value);
        if (!Number.isFinite(v) || v < 0) v = 0;
        if (v > max) v = max;
        abonoExtrasEl.value = v.toFixed(2);
        actualizarPreview();
    });
    abonoLibrosEl?.addEventListener('blur', () => {
        let v = Number.parseFloat(abonoLibrosEl.value);
        if (!Number.isFinite(v) || v < 0) v = 0;
        if (v > LIBRO_MONTO) v = LIBRO_MONTO;
        abonoLibrosEl.value = v.toFixed(2);
        actualizarPreview();
    });
    abonoPensionesEl?.addEventListener('blur', () => {
        const max = Number.parseFloat(maxPensionesLabel?.textContent?.replace(/[^0-9.]/g, '') || '0');
        let v = Number.parseFloat(abonoPensionesEl.value);
        if (!Number.isFinite(v) || v < 0) v = 0;
        if (v > max) v = max;
        abonoPensionesEl.value = v.toFixed(2);
        actualizarPreview();
    });

    // ── Botones "Todo" ───────────────────────────────────────────
    document.getElementById('btnPagarTodoMat')?.addEventListener('click', () => {
        if (!abonoMatriculaEl || !maxMatriculaLabel) return;
        const txt = maxMatriculaLabel.textContent.replace(/[^0-9.]/g, '');
        abonoMatriculaEl.value = parseFloat(txt || 0).toFixed(2);
        abonoMatriculaDirty = true;
        actualizarPreview();
    });
    document.getElementById('btnPagarTodoExt')?.addEventListener('click', () => {
        if (!abonoExtrasEl || !maxExtrasLabel) return;
        const txt = maxExtrasLabel.textContent.replace(/[^0-9.]/g, '');
        abonoExtrasEl.value = parseFloat(txt || 0).toFixed(2);
        abonoExtrasDirty = true;
        actualizarPreview();
    });
    document.getElementById('btnPagarTodoLib')?.addEventListener('click', () => {
        if (!abonoLibrosEl) return;
        abonoLibrosEl.value = LIBRO_MONTO.toFixed(2);
        abonoLibrosDirty = true;
        actualizarPreview();
    });
    document.getElementById('btnPagarTodoPen')?.addEventListener('click', () => {
        if (!abonoPensionesEl || !maxPensionesLabel) return;
        const txt = maxPensionesLabel.textContent.replace(/[^0-9.]/g, '');
        abonoPensionesEl.value = parseFloat(txt || 0).toFixed(2);
        abonoPensionesDirty = true;
        actualizarPreview();
    });

    // ── Carrito ──────────────────────────────────────────────────
    function renderCarrito() {
        const items = Object.values(carrito);
        carritoVacio.style.display = items.length ? 'none' : '';
        carritoBody.querySelectorAll('tr.carrito-item').forEach(tr => tr.remove());
        carritoInputs.innerHTML = '';

        items.forEach((it, idx) => {
            const subtotal = it.precio * it.cantidad;
            const tr = document.createElement('tr');
            tr.className = 'carrito-item';
            tr.innerHTML = `
                <td style="font-size:.83rem;">${it.nombre}</td>
                <td class="text-end" style="font-size:.83rem;">${money(it.precio)}</td>
                <td class="text-center" style="font-size:.83rem;">${it.cantidad}</td>
                <td class="text-end" style="font-size:.83rem;font-weight:600;">${money(subtotal)}</td>
                <td class="text-end">
                    <button type="button" data-del="${it.id}"
                        style="background:none;border:1px solid #c8cffc;color:#2b3a99;border-radius:6px;padding:2px 8px;font-size:.72rem;cursor:pointer;">✕</button>
                </td>`;
            carritoBody.appendChild(tr);
            carritoInputs.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="extras[${idx}][producto_id]" value="${it.id}">
                <input type="hidden" name="extras[${idx}][cantidad]" value="${it.cantidad}">
            `);
        });

        if (extrasResumenTexto) {
            if (items.length === 0) {
                extrasResumenTexto.className = 'p3-extras-hint';
                extrasResumenTexto.innerHTML = `<i data-feather="shopping-bag" style="width:13px;height:13px;"></i> Sin extras agregados.`;
            } else {
                const total = items.reduce((a, it) => a + it.precio * it.cantidad, 0);
                extrasResumenTexto.className = 'p3-extras-hint has-items';
                extrasResumenTexto.innerHTML = `<i data-feather="check-circle" style="width:13px;height:13px;"></i> ${items.length} producto${items.length!==1?'s':''} — Total: ${money(total)}`;
            }
        }
        if (typeof feather !== 'undefined') feather.replace();
        actualizarPreview();
    }

    async function buscarProductos() {
        const q = (document.getElementById('qProducto').value || '').trim();
        productosBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">Buscando...</td></tr>`;
        const catParam = CATEGORIA_UNIFORME_ID ? `&categoria=${CATEGORIA_UNIFORME_ID}` : '';
        const res  = await fetch(`${urlBuscar}?q=${encodeURIComponent(q)}${catParam}`);
        const data = await res.json();
        if (!data.length) {
            productosBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">Sin resultados.</td></tr>`;
            return;
        }
        productosBody.innerHTML = data.map(p => `
            <tr>
                <td>
                    <div class="fw-semibold" style="font-size:.84rem;">${p.nombre}</div>
                    <small class="text-muted">${p.sku || ''}${p.categoria ? ' · ' + p.categoria : ''}</small>
                </td>
                <td class="text-end" style="font-size:.84rem;">${money(p.precio)}</td>
                <td class="text-center" style="font-size:.84rem;">${p.stock}</td>
                <td class="text-center">
                    <input type="number" min="1" max="${p.stock}" value="1" class="form-control form-control-sm" id="cant_${p.id}" style="width:70px;margin:auto;">
                </td>
                <td class="text-end">
                    ${p.stock > 0
                        ? `<button type="button" class="btn btn-sm btn-outline-primary"
                            style="font-size:.78rem;border-radius:8px;"
                            data-add="${p.id}" data-nombre="${encodeURIComponent(p.nombre)}"
                            data-precio="${p.precio}" data-stock="${p.stock}">
                            + Agregar
                          </button>`
                        : `<span style="font-size:.75rem;color:#2b3a99;font-weight:600;">Sin stock</span>`
                    }
                </td>
            </tr>`).join('');
    }

    document.getElementById('btnBuscarProd')?.addEventListener('click', buscarProductos);
    document.getElementById('qProducto')?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscarProductos(); } });

    productosBody?.addEventListener('click', e => {
        const btn = e.target.closest('[data-add]');
        if (!btn) return;
        const id = Number(btn.dataset.add), nombre = decodeURIComponent(btn.dataset.nombre);
        const precio = Number(btn.dataset.precio), stock = Number(btn.dataset.stock);
        const cantInput = document.getElementById(`cant_${id}`);
        let cantidad = Number(cantInput?.value || 1);
        if (cantidad < 1) cantidad = 1;
        if (cantidad > stock) cantidad = stock;
        if (!carrito[id]) carrito[id] = { id, nombre, precio, cantidad, stock };
        else carrito[id].cantidad = Math.min(carrito[id].cantidad + cantidad, stock);
        renderCarrito();
    });

    carritoBody?.addEventListener('click', e => {
        const btn = e.target.closest('[data-del]');
        if (!btn) return;
        delete carrito[Number(btn.dataset.del)];
        renderCarrito();
    });

    // ── Init ─────────────────────────────────────────────────────
    const resetDefaults = () => {
        const r = document.getElementById('genRadio_todo');
        if (r) r.checked = true;
        if (soloMatriculaConfirmEl) soloMatriculaConfirmEl.checked = false;
    };

    if (!HAS_PASO3_CONFIG) resetDefaults();
    renderCarrito();
    updateGenMode();
    // Clamp abono values to their current maxes (handles stale session data)
    [
        [abonoMatriculaEl, maxMatriculaLabel, () => { abonoMatriculaDirty = false; }],
        [abonoExtrasEl,    maxExtrasLabel,    () => { abonoExtrasDirty    = false; }],
        [abonoLibrosEl,    maxLibrosLabel,    () => { abonoLibrosDirty    = false; }],
        [abonoPensionesEl, maxPensionesLabel, () => { abonoPensionesDirty = false; }],
    ].forEach(([el, lbl, resetDirty]) => {
        if (!el || !lbl) return;
        const max = parseFloat(lbl.textContent.replace(/[^0-9.]/g, '') || '0');
        const v   = parseFloat(el.value) || 0;
        if (v > max) { el.value = max.toFixed(2); resetDirty(); actualizarPreview(); }
    });
    if (typeof feather !== 'undefined') feather.replace();

    window.addEventListener('pageshow', () => {
        if (HAS_PASO3_CONFIG) return;
        resetDefaults(); updateGenMode();
    });

    // ── Medios de pago (multi-método) ───────────────────────────
    const formPaso3       = document.getElementById('formPaso3');
    const listaMediosEl   = document.getElementById('listaMedios');
    const alertaMediosEl  = document.getElementById('alertaMedios');
    const totalMediosBox  = document.getElementById('totalMediosBox');
    const METODOS_OP      = ['Yape', 'Plin', 'Transferencia'];
    const METODOS_LIST    = ['Efectivo', 'Yape', 'Transferencia', 'Plin', 'Voucher'];
    let medioIdx          = 0;

    function opcionesMetodo(sel) {
        return METODOS_LIST.map(m =>
            `<option value="${m}"${sel === m ? ' selected' : ''}>${m}</option>`
        ).join('');
    }

    window.actualizarTotalMedios = function () {
        if (!listaMediosEl) return;
        const totalCobrado = parseFloat(montoPagadoEl?.value || '0');
        const sumMedios = [...listaMediosEl.querySelectorAll('.medio-monto')]
            .reduce((s, el) => s + (parseFloat(el.value) || 0), 0);

        if (totalCobrado > 0 && listaMediosEl.querySelectorAll('.medio-row').length > 0) {
            const diff = Math.abs(sumMedios - totalCobrado);
            if (diff > 0.01) {
                totalMediosBox.style.display = '';
                totalMediosBox.innerHTML = `Total medios: <strong>${money(sumMedios)}</strong> &nbsp;·&nbsp; Falta asignar: <span style="color:#1f2e8f;font-weight:700;">${money(totalCobrado - sumMedios)}</span>`;
            } else {
                totalMediosBox.style.display = '';
                totalMediosBox.innerHTML = `Total medios: <strong style="color:#059669;">${money(sumMedios)} ✓</strong>`;
            }
        } else {
            if (totalMediosBox) totalMediosBox.style.display = 'none';
        }
    };

    function crearFilaMedio(metodo = '', monto = '', op = '') {
        const idx = medioIdx++;
        const needOp = METODOS_OP.includes(metodo);
        const div = document.createElement('div');
        div.className = 'medio-row';
        div.style.cssText = 'display:flex;gap:5px;margin-bottom:6px;align-items:flex-start;flex-wrap:wrap;';
        div.innerHTML = `
            <select name="medios[${idx}][metodo]" class="p3-input-sm medio-sel" style="width:118px;flex-shrink:0;">
                <option value="">Método...</option>
                ${opcionesMetodo(metodo)}
            </select>
            <input type="number" name="medios[${idx}][monto]" class="p3-input-sm medio-monto"
                placeholder="0.00" step="0.01" min="0" value="${monto}"
                style="width:88px;flex-shrink:0;">
            <input type="text" name="medios[${idx}][op]" class="p3-input-sm medio-op"
                placeholder="N° operación" value="${op}" maxlength="60"
                style="flex:1;min-width:80px;${needOp ? '' : 'display:none;'}">
            <button type="button" class="btn-quitar-medio" title="Quitar"
                style="width:26px;height:26px;flex-shrink:0;background:#e2eefe;border:none;border-radius:6px;color:#1f2e8f;font-size:1.1rem;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>
        `;
        const selEl   = div.querySelector('.medio-sel');
        const opEl    = div.querySelector('.medio-op');
        const montoEl = div.querySelector('.medio-monto');
        selEl.addEventListener('change', () => {
            const need = METODOS_OP.includes(selEl.value);
            opEl.style.display = need ? '' : 'none';
            if (!need) opEl.value = '';
            window.actualizarTotalMedios();
        });
        montoEl.addEventListener('input', window.actualizarTotalMedios);
        div.querySelector('.btn-quitar-medio').addEventListener('click', () => {
            if (listaMediosEl.querySelectorAll('.medio-row').length <= 1) return;
            div.remove();
            window.actualizarTotalMedios();
        });
        return div;
    }

    // Inicializar con 1 fila vacía
    if (listaMediosEl) listaMediosEl.appendChild(crearFilaMedio());

    document.getElementById('btnAgregarMedio')?.addEventListener('click', () => {
        const fila = crearFilaMedio();
        listaMediosEl?.appendChild(fila);
        fila.querySelector('.medio-sel')?.focus();
    });

    // ── Validación al enviar ─────────────────────────────────────
    const marcarInvalido = (el) => {
        if (!el) return;
        el.classList.add('p3-field-invalid');
        const limpiar = () => {
            el.classList.remove('p3-field-invalid');
            el.removeEventListener('change', limpiar);
            el.removeEventListener('input',  limpiar);
        };
        el.addEventListener('change', limpiar);
        el.addEventListener('input',  limpiar);
    };

    document.getElementById('btnContinuar')?.addEventListener('click', () => {
        const totalCobrado = parseFloat(montoPagadoEl?.value || '0');

        if (totalCobrado > 0) {
            const filas = [...listaMediosEl.querySelectorAll('.medio-row')];
            let sumMedios = 0;
            let hayError = false;

            filas.forEach(fila => {
                const selEl   = fila.querySelector('.medio-sel');
                const montoEl = fila.querySelector('.medio-monto');
                const opEl    = fila.querySelector('.medio-op');
                const monto   = parseFloat(montoEl?.value) || 0;
                const metodo  = selEl?.value ?? '';

                if (!metodo) { marcarInvalido(selEl);   hayError = true; }
                if (monto <= 0) { marcarInvalido(montoEl); hayError = true; }
                if (metodo && METODOS_OP.includes(metodo) && !opEl?.value.trim()) {
                    marcarInvalido(opEl); hayError = true;
                }
                sumMedios += monto;
            });

            if (hayError) {
                listaMediosEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const diff = Math.abs(sumMedios - totalCobrado);
            if (diff > 0.01) {
                if (alertaMediosEl) {
                    alertaMediosEl.textContent = `La suma de medios (S/ ${sumMedios.toFixed(2)}) debe igualar el total cobrado (S/ ${totalCobrado.toFixed(2)}).`;
                    alertaMediosEl.style.display = '';
                    alertaMediosEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            if (alertaMediosEl) alertaMediosEl.style.display = 'none';
        }

        formPaso3?.submit();
    });
});
</script>
@endpush
