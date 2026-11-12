@extends('layouts.mantis')

@section('title', 'Egresos | Sistema de Raul')
@section('page-title', 'Egresos')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── HERO ───────────────────────────────────────────── */
        .eg-hero {
            background: linear-gradient(135deg, #162061 0%, #1c297e 45%, #1f2e8f 75%, #2b3a99 100%);
            border-radius: 20px;
            border: none;
            overflow: hidden;
            position: relative;
        }
        .eg-hero::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .eg-hero::after {
            content: '';
            position: absolute;
            bottom: -60px; left: 30%;
            width: 280px; height: 280px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }
        .eg-hero .card-body { position: relative; z-index: 2; padding: 1.6rem 2rem; }
        .eg-hero-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.18);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.22);
        }
        .eg-hero-icon svg { width: 28px; height: 28px; color: #fff; stroke: #fff; }
        .eg-hero-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.74rem; font-weight: 700;
            color: #fff; letter-spacing: 0.4px;
            text-transform: uppercase; margin-bottom: 8px;
        }
        .eg-hero-pill svg { width: 13px; height: 13px; }
        .eg-hero-title { color: #fff; font-weight: 800; font-size: 1.55rem; margin-bottom: 4px; line-height: 1.2; }
        .eg-hero-sub { color: rgba(255,255,255,0.78); font-size: 0.88rem; margin: 0; }

        .eg-btn-nuevo {
            background: #fff;
            color: #1c297e;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 0.88rem;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
        }
        .eg-btn-nuevo:hover { background: #f7faff; color: #18246e; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
        .eg-btn-nuevo svg { width: 16px; height: 16px; }

        .eg-btn-export {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 0.88rem;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
        }
        .eg-btn-export:hover { background: rgba(255,255,255,0.25); color: #fff; transform: translateY(-1px); }
        .eg-btn-export svg { width: 16px; height: 16px; }

        /* ── KPI CARDS ──────────────────────────────────────── */
        .eg-kpi-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #d4ecfd;
            box-shadow: 0 2px 12px rgba(33, 49, 150,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
            position: relative;
        }
        .eg-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(33, 49, 150,0.12); }
        .eg-kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .eg-kpi-card.kpi-danger::before  { background: linear-gradient(180deg, #ef4444, #b91c1c); }
        .eg-kpi-card.kpi-primary::before { background: linear-gradient(180deg, #23339e, #213196); }
        .eg-kpi-card.kpi-warning::before { background: linear-gradient(180deg, #23339f, #22329c); }
        .eg-kpi-card.kpi-success::before { background: linear-gradient(180deg, #34d399, #1f8f5f); }

        .eg-kpi-card .card-body { padding: 1.25rem 1.4rem; }
        .eg-kpi-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 6px; }
        .eg-kpi-value { font-size: 1.6rem; font-weight: 800; line-height: 1.1; color: #111427; margin-bottom: 4px; }
        .eg-kpi-value.danger  { color: #b91c1c; }
        .eg-kpi-value.success { color: #1f8f5f; }
        .eg-kpi-sub { font-size: 0.75rem; color: #9ca3af; font-weight: 500; }
        .eg-kpi-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .eg-kpi-icon svg { width: 20px; height: 20px; }
        .eg-kpi-icon.danger  { background: rgba(239,68,68,0.12); color: #ef4444; }
        .eg-kpi-icon.primary { background: rgba(35, 51, 158,0.12); color: #23339e; }
        .eg-kpi-icon.warning { background: rgba(34, 50, 156,0.12); color: #22329c; }
        .eg-kpi-icon.success { background: rgba(31,143,95,0.12);  color: #1f8f5f; }

        /* ── FILTER CARD ────────────────────────────────────── */
        .eg-filter-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #d4ecfd;
            box-shadow: 0 2px 8px rgba(33, 49, 150,0.05);
        }
        .eg-filter-card .card-header {
            background: #f5faff;
            border-bottom: 1px solid #d4ecfd;
            border-radius: 16px 16px 0 0;
            padding: 0.9rem 1.4rem;
            display: flex; align-items: center; gap: 10px;
        }
        .eg-filter-header-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #b8c1fd, #23339f);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .eg-filter-header-icon svg { width: 15px; height: 15px; color: #17226a; stroke: #17226a; }
        .eg-filter-title { font-weight: 700; font-size: 0.9rem; color: #374151; margin: 0; }

        .eg-filter-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.8rem;
            margin-bottom: 5px;
            display: flex; align-items: center; gap: 5px;
        }
        .eg-filter-label svg { width: 12px; height: 12px; }
        .eg-filter-card .form-control,
        .eg-filter-card .form-select {
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .eg-filter-card .form-control:focus,
        .eg-filter-card .form-select:focus {
            border-color: #23339e;
            box-shadow: 0 0 0 3px rgba(35, 51, 158,0.12);
        }
        .eg-btn-filter {
            background: linear-gradient(135deg, #1c297e, #1f2e8f);
            color: #fff; border: none;
            border-radius: 10px;
            font-weight: 700; font-size: 0.88rem;
            padding: 9px 18px;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .eg-btn-filter:hover { background: linear-gradient(135deg, #18246e, #1c297e); color: #fff; transform: translateY(-1px); }
        .eg-btn-filter svg { width: 15px; height: 15px; }
        .eg-btn-clear {
            border: 1.5px solid #e5e7eb;
            background: #fff; color: #6b7280;
            border-radius: 10px;
            font-weight: 600; font-size: 0.88rem;
            padding: 9px 18px;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .eg-btn-clear:hover { border-color: #d1d5db; background: #f9fafb; color: #374151; }
        .eg-btn-clear svg { width: 15px; height: 15px; }

        /* ── TABLE CARD ─────────────────────────────────────── */
        .eg-table-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #d4ecfd;
            box-shadow: 0 2px 12px rgba(33, 49, 150,0.06);
            overflow: hidden;
        }
        .eg-table-card .card-header {
            background: #fff;
            border-bottom: 1px solid #d4ecfd;
            padding: 1.1rem 1.5rem;
        }
        .eg-table-card-title { font-weight: 800; font-size: 1rem; color: #111427; margin: 0; }
        .eg-table-badge {
            display: inline-flex; align-items: center;
            background: rgba(28, 41, 126,0.1);
            color: #1c297e;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.75rem; font-weight: 700;
        }

        .eg-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .eg-table thead th {
            background: #f5faff;
            font-size: 0.73rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: #6b7280;
            padding: 12px 14px;
            border-bottom: 2px solid #d4ecfd;
            white-space: nowrap;
        }
        .eg-table thead th:first-child { border-radius: 0; padding-left: 20px; }
        .eg-table thead th:last-child  { padding-right: 20px; }
        .eg-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s ease;
        }
        .eg-table tbody tr:hover { background: #f5faff; }
        .eg-table tbody tr:last-child { border-bottom: none; }
        .eg-table tbody td {
            padding: 13px 14px;
            font-size: 0.865rem;
            color: #374151;
            vertical-align: middle;
        }
        .eg-table tbody td:first-child { padding-left: 20px; }
        .eg-table tbody td:last-child  { padding-right: 20px; }

        .eg-date-badge {
            display: inline-flex; flex-direction: column;
            align-items: center;
            background: #f2f7fe;
            border: 1px solid #cae0fe;
            border-radius: 8px;
            padding: 4px 9px; min-width: 52px;
            line-height: 1.2;
        }
        .eg-date-day   { font-size: 1rem; font-weight: 800; color: #1c297e; }
        .eg-date-month { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #2b3a99; letter-spacing: 0.4px; }

        .eg-concepto-main { font-weight: 600; color: #111427; font-size: 0.875rem; }
        .eg-concepto-sub  { font-size: 0.73rem; color: #9ca3af; margin-top: 2px; }

        .eg-chip {
            display: inline-flex; align-items: center;
            border-radius: 999px; padding: 3px 9px;
            font-size: 0.7rem; font-weight: 700;
            border: 1px solid transparent;
            text-transform: uppercase; letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .eg-chip--cat  { background: rgba(34, 50, 156,0.14); color: #17226a; border-color: rgba(34, 50, 156,0.3); }
        .eg-chip--type { background: rgba(37,53,156,0.1);  color: #1f2e8d; border-color: rgba(37,53,156,0.22); text-transform: none; }

        .eg-money {
            font-weight: 800; color: #1c297e;
            white-space: nowrap; font-size: 0.95rem;
        }
        .eg-money-prefix { font-size: 0.73rem; font-weight: 600; opacity: 0.7; }

        .eg-metodo {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f3f4f6; border-radius: 8px;
            padding: 4px 9px; font-size: 0.76rem; font-weight: 600; color: #374151;
        }
        .eg-metodo svg { width: 12px; height: 12px; color: #6b7280; }

        .eg-comprobante-tipo { font-weight: 600; font-size: 0.82rem; color: #374151; }
        .eg-comprobante-nro  { font-size: 0.73rem; color: #9ca3af; margin-top: 2px; }

        .eg-status {
            display: inline-flex; align-items: center; gap: 5px;
            border-radius: 999px; font-size: 0.72rem; font-weight: 700;
            padding: 4px 11px; border: 1px solid transparent; white-space: nowrap;
        }
        .eg-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .eg-status--registrado {
            background: rgba(31,143,95,0.1); color: #1f8f5f; border-color: rgba(31,143,95,0.24);
        }
        .eg-status--registrado::before { background: #1f8f5f; }
        .eg-status--anulado {
            background: rgba(31, 46, 143,0.1); color: #1c297e; border-color: rgba(31, 46, 143,0.24);
        }
        .eg-status--anulado::before { background: #1c297e; }

        .eg-btn-anular {
            background: rgba(43, 58, 153,0.08);
            color: #1f2e8f;
            border: 1.5px solid rgba(43, 58, 153,0.3);
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 0.76rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 5px;
            transition: all 0.2s;
        }
        .eg-btn-anular:hover { background: #1f2e8f; color: #fff; border-color: #1f2e8f; transform: scale(1.03); }
        .eg-btn-anular svg { width: 13px; height: 13px; }

        .eg-empty {
            padding: 4rem 2rem; text-align: center;
        }
        .eg-empty-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #e2eefe, #cae0fe);
            border-radius: 20px; margin: 0 auto 1rem;
            display: flex; align-items: center; justify-content: center;
        }
        .eg-empty-icon svg { width: 36px; height: 36px; color: #2b3a99; stroke: #2b3a99; }
        .eg-empty-title { font-weight: 700; color: #374151; font-size: 1rem; margin-bottom: 6px; }
        .eg-empty-sub   { color: #9ca3af; font-size: 0.85rem; }

        /* ── MODAL EGRESO ───────────────────────────────────── */
        .eg-modal .modal-dialog {
            max-width: 860px;
            /* Constrain height so modal-body puede hacer scroll */
            max-height: calc(100vh - 3.5rem);
            margin-top: 1.75rem;
            margin-bottom: 1.75rem;
        }
        .eg-modal .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 24px 64px rgba(0,0,0,0.22);
            /* flex column para que el body crezca y haga scroll */
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 3.5rem);
            /* NO overflow:hidden aqui — lo maneja Bootstrap con modal-dialog-scrollable */
        }
        .eg-modal .modal-header {
            background: linear-gradient(135deg, #162061 0%, #1c297e 50%, #1f2e8f 100%);
            padding: 1.3rem 1.8rem;
            border: none;
            border-radius: 20px 20px 0 0;
            position: relative; overflow: hidden;
            flex-shrink: 0; /* nunca se comprime */
        }
        .eg-modal .modal-header::before {
            content: ''; position: absolute;
            top: -50px; right: -50px;
            width: 160px; height: 160px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .eg-modal .modal-header::after {
            content: ''; position: absolute;
            bottom: -40px; left: 20%;
            width: 120px; height: 120px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }
        .eg-modal-header-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255,255,255,0.28);
            flex-shrink: 0;
        }
        .eg-modal-header-icon svg { width: 22px; height: 22px; color: #fff; stroke: #fff; }
        .eg-modal .modal-title    { color: #fff; font-weight: 800; font-size: 1.1rem; line-height: 1.2; }
        .eg-modal .modal-subtitle { color: rgba(255,255,255,0.72); font-size: 0.78rem; margin-top: 2px; }
        .eg-modal .btn-close { filter: brightness(0) invert(1); opacity: 0.85; position: relative; z-index: 2; }

        /* body con scroll propio */
        .eg-modal .modal-body {
            padding: 1.5rem 1.8rem;
            background: #fff;
            overflow-y: auto;        /* ← el scroll va aqui */
            flex: 1 1 auto;
        }
        .eg-modal .modal-footer {
            background: #f9fafb;
            border-top: 1px solid #f0f0f0;
            padding: 1rem 1.8rem;
            border-radius: 0 0 20px 20px;
            flex-shrink: 0;          /* nunca se comprime */
        }

        /* ── TIPO CARD GRID ─────────────────────────────────── */
        .eg-tipo-section-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.72rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.7px;
            color: #1c297e; margin-bottom: 12px;
        }
        .eg-tipo-section-label svg { width: 15px; height: 15px; flex-shrink: 0; }
        .eg-tipo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 4px;
        }
        @media(max-width:576px){ .eg-tipo-grid { grid-template-columns: repeat(3,1fr); } }
        .eg-tipo-btn {
            border: 1.5px solid #e5e7eb;
            background: #fff;
            border-radius: 12px;
            padding: 12px 6px 10px;
            font-weight: 600; font-size: 0.72rem;
            color: #6b7280;
            transition: all 0.18s ease;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 7px; cursor: pointer; line-height: 1.3;
            text-align: center; min-height: 76px;
        }
        .eg-tipo-btn .et-icon {
            width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center;
            color: #9ca3af;
            transition: color 0.18s;
        }
        .eg-tipo-btn .et-icon svg { width: 22px; height: 22px; }
        .eg-tipo-btn:hover {
            border-color: rgba(28, 41, 126,0.4);
            background: #f7faff;
            color: #1c297e;
        }
        .eg-tipo-btn:hover .et-icon { color: #1f2e8f; }
        .eg-tipo-btn.active {
            border-color: #1f2e8f;
            background: #f2f7fe;
            color: #18246e;
            box-shadow: 0 0 0 3px rgba(31, 46, 143,0.12);
        }
        .eg-tipo-btn.active .et-icon { color: #1c297e; }

        /* ── SECCIONES DEL FORM ─────────────────────────────── */
        .eg-form-section {
            background: #f5faff;
            border: 1.5px solid #d4ecfd;
            border-radius: 14px;
            padding: 1.1rem 1.3rem 0.8rem;
            margin-bottom: 1rem;
        }
        .eg-form-section:last-of-type { margin-bottom: 0; }
        .eg-form-section-title {
            font-size: 0.72rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.6px;
            color: #1b287b; margin-bottom: 14px;
            display: flex; align-items: center; gap: 7px;
        }
        .eg-form-section-title::after {
            content: ''; flex: 1; height: 1px; background: #d4ecfd;
        }
        .eg-form-section-title svg { width: 14px; height: 14px; flex-shrink: 0; }

        .eg-modal .form-label {
            font-weight: 600; font-size: 0.81rem;
            color: #374151; margin-bottom: 5px;
        }
        .eg-modal .form-control,
        .eg-modal .form-select {
            border-radius: 10px; border: 1.5px solid #e5e7eb;
            font-size: 0.875rem; background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .eg-modal .form-control:focus,
        .eg-modal .form-select:focus {
            border-color: #1f2e8f;
            box-shadow: 0 0 0 3px rgba(31, 46, 143,0.1);
        }
        .eg-modal textarea.form-control { resize: none; }

        /* ── MODAL ANULAR ───────────────────────────────────── */
        .eg-modal-anular .modal-content {
            border-radius: 18px; border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.16);
        }
        .eg-modal-anular .modal-header {
            background: linear-gradient(135deg, #0f1645, #18246e);
            border: none; padding: 1.3rem 1.6rem;
        }
        .eg-modal-anular .modal-title { color: #fff; font-weight: 800; }
        .eg-modal-anular .btn-close { filter: brightness(0) invert(1); opacity: 0.85; }
        .eg-modal-anular .modal-body { padding: 1.5rem 1.6rem; }
        .eg-anular-concepto-box {
            background: #f2f7fe; border: 1px solid #cae0fe;
            border-radius: 12px; padding: 12px 16px;
            margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .eg-anular-concepto-icon {
            width: 34px; height: 34px;
            background: rgba(43, 58, 153,0.15);
            border-radius: 8px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .eg-anular-concepto-icon svg { width: 16px; height: 16px; color: #1f2e8f; stroke: #1f2e8f; }
        .eg-anular-concepto-label { font-size: 0.73rem; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .eg-anular-concepto-text  { font-weight: 700; color: #374151; font-size: 0.9rem; margin-top: 2px; }

        /* ── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 576px) {
            .eg-hero .card-body { padding: 1.2rem 1.2rem; }
            .eg-hero-title { font-size: 1.2rem; }
            .eg-kpi-value { font-size: 1.35rem; }
        }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius:12px;border:none;box-shadow:0 4px 16px rgba(31,143,95,0.15);">
            <i data-feather="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius:12px;border:none;box-shadow:0 4px 16px rgba(34, 50, 156,0.15);">
            <i data-feather="alert-triangle" style="width:18px;height:18px;flex-shrink:0;"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ══ HERO ══════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card eg-hero">
                <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="eg-hero-icon">
                            <i data-feather="trending-down"></i>
                        </div>
                        <div>
                            <div class="eg-hero-pill">
                                <i data-feather="layers"></i>
                                Modulo de Finanzas
                            </div>
                            <h4 class="eg-hero-title mb-1">Gestion de Egresos</h4>
                            <p class="eg-hero-sub">Administra gastos y salidas de caja del periodo seleccionado.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="eg-btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalEgreso">
                            <i data-feather="plus-circle"></i>
                            Nuevo Egreso
                        </button>
                        <a href="{{ route('admin-sistema.finanzas.egresos.csv', request()->query()) }}" class="eg-btn-export">
                            <i data-feather="download-cloud"></i>
                            Exportar CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ KPI CARDS ═════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card eg-kpi-card kpi-danger h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eg-kpi-icon danger">
                        <i data-feather="credit-card"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="eg-kpi-label">Total del periodo</div>
                        <div class="eg-kpi-value danger">S/ {{ number_format((float) ($kpis['total_periodo'] ?? 0), 2) }}</div>
                        <div class="eg-kpi-sub">
                            {{ ($filtros['fecha_inicio_input'] ?? '-') ?: 'Inicio' }}
                            &rarr;
                            {{ ($filtros['fecha_fin_input'] ?? '-') ?: 'Fin' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card eg-kpi-card kpi-primary h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eg-kpi-icon primary">
                        <i data-feather="activity"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="eg-kpi-label">Transacciones</div>
                        <div class="eg-kpi-value">{{ number_format((int) ($kpis['transacciones'] ?? 0)) }}</div>
                        <div class="eg-kpi-sub">Registros activos</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card eg-kpi-card kpi-warning h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eg-kpi-icon warning">
                        <i data-feather="calendar"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="eg-kpi-label">Este mes</div>
                        <div class="eg-kpi-value success">S/ {{ number_format((float) ($kpis['este_mes'] ?? 0), 2) }}</div>
                        <div class="eg-kpi-sub">Prom. S/ {{ number_format((float) ($kpis['promedio_dia'] ?? 0), 2) }}/dia</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card eg-kpi-card kpi-success h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eg-kpi-icon success">
                        <i data-feather="bar-chart-2"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="eg-kpi-label">Mayor gasto</div>
                        <div class="eg-kpi-value">S/ {{ number_format((float) ($kpis['mayor_gasto'] ?? 0), 2) }}</div>
                        <div class="eg-kpi-sub">Transaccion mas alta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FILTROS ════════════════════════════════════════════ --}}
    <div class="card eg-filter-card mb-4">
        <div class="card-header">
            <div class="eg-filter-header-icon">
                <i data-feather="filter"></i>
            </div>
            <span class="eg-filter-title">Filtros de busqueda</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin-sistema.finanzas.egresos') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label class="eg-filter-label">
                            <i data-feather="book-open"></i>
                            Anio escolar
                        </label>
                        <select name="anio_escolar_id" class="form-select">
                            @forelse ($anios as $opcion)
                                <option value="{{ $opcion->id }}" @selected((int) ($anioId ?? 0) === (int) $opcion->id)>
                                    {{ $opcion->nombre }}
                                </option>
                            @empty
                                <option value="">Sin anios escolares</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="eg-filter-label">
                            <i data-feather="calendar"></i>
                            Desde
                        </label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio_input'] ?? '' }}">
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="eg-filter-label">
                            <i data-feather="calendar"></i>
                            Hasta
                        </label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin_input'] ?? '' }}">
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="eg-filter-label">
                            <i data-feather="tag"></i>
                            Categoria
                        </label>
                        <select name="categoria" class="form-select">
                            <option value="todas" @selected(($filtros['categoria'] ?? 'todas') === 'todas')>Todas</option>
                            @foreach ($categoriasEgreso as $valor => $label)
                                <option value="{{ $valor }}" @selected(($filtros['categoria'] ?? '') === $valor)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-md-8">
                        <label class="eg-filter-label">
                            <i data-feather="search"></i>
                            Buscar
                        </label>
                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Concepto o comprobante..."
                            value="{{ $filtros['buscar'] ?? '' }}"
                        >
                    </div>
                    <div class="col-xl-auto col-md-4 d-flex gap-2">
                        <button type="submit" class="eg-btn-filter flex-grow-1">
                            <i data-feather="search"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('admin-sistema.finanzas.egresos', ['anio_escolar_id' => $anioId]) }}" class="eg-btn-clear">
                            <i data-feather="x"></i>
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ TABLA ══════════════════════════════════════════════ --}}
    <div class="card eg-table-card">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h5 class="eg-table-card-title">Historial de Egresos</h5>
                    <small class="text-muted">Listado de salidas registradas</small>
                </div>
            </div>
            <span class="eg-table-badge">
                {{ number_format((int) ($totalMovimientos ?? 0)) }} registros
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="eg-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Categoria</th>
                            <th>Tipo</th>
                            <th>Docente / Curso</th>
                            <th class="text-end">Monto</th>
                            <th>Metodo</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th class="text-center">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimientos as $mov)
                            @php
                                $fechaRender = '-';
                                $fechaDia = '-';
                                $fechaMes = '-';
                                try {
                                    if (!empty($mov['fecha'])) {
                                        $dt = \Illuminate\Support\Carbon::parse($mov['fecha']);
                                        $fechaRender = $dt->format('d/m/Y');
                                        $fechaDia    = $dt->format('d');
                                        $fechaMes    = strtoupper($dt->translatedFormat('M'));
                                    }
                                } catch (\Throwable $e) {
                                    $fechaRender = (string) ($mov['fecha'] ?? '-');
                                    $fechaDia = $fechaRender;
                                    $fechaMes = '';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="eg-date-badge" title="{{ $fechaRender }}">
                                        <span class="eg-date-day">{{ $fechaDia }}</span>
                                        @if($fechaMes)
                                            <span class="eg-date-month">{{ $fechaMes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="eg-concepto-main">{{ $mov['concepto'] ?? '-' }}</div>
                                    @if(!empty($mov['observaciones']))
                                        <div class="eg-concepto-sub">{{ Str::limit($mov['observaciones'], 40) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="eg-chip eg-chip--cat">{{ $mov['categoria_label'] ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="eg-chip eg-chip--type">{{ $mov['tipo_label'] ?? '-' }}</span>
                                </td>
                                <td>
                                    @if(!empty($mov['docente_curso']) && $mov['docente_curso'] !== '-')
                                        <span style="font-weight:600;font-size:0.82rem;color:#374151;">{{ $mov['docente_curso'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="eg-money">
                                        <span class="eg-money-prefix">S/</span> {{ number_format((float) ($mov['monto'] ?? 0), 2) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="eg-metodo">
                                        <i data-feather="credit-card"></i>
                                        {{ $mov['metodo_pago'] ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="eg-comprobante-tipo">{{ $mov['tipo_comprobante'] ?? '-' }}</div>
                                    @if(!empty($mov['nro_comprobante']))
                                        <div class="eg-comprobante-nro">{{ $mov['nro_comprobante'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="eg-status eg-status--{{ $mov['estado'] ?? 'registrado' }}">
                                        {{ $mov['estado_label'] ?? 'Registrado' }}
                                    </span>
                                    @if (($mov['estado'] ?? '') === 'anulado' && !empty($mov['anulado_motivo']))
                                        <div><small class="text-danger" style="font-size:0.7rem;">{{ Str::limit($mov['anulado_motivo'], 30) }}</small></div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if (($mov['estado'] ?? '') === 'registrado')
                                        <button
                                            type="button"
                                            class="eg-btn-anular js-open-anular"
                                            data-egreso-id="{{ $mov['id'] }}"
                                            data-egreso-concepto="{{ $mov['concepto'] }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAnularEgreso"
                                        >
                                            <i data-feather="x-circle"></i>
                                            Anular
                                        </button>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="eg-empty">
                                        <div class="eg-empty-icon">
                                            <i data-feather="inbox"></i>
                                        </div>
                                        <div class="eg-empty-title">Sin egresos registrados</div>
                                        <div class="eg-empty-sub">No hay egresos en el rango de fechas seleccionado.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movimientos->hasPages())
                <div class="px-4 py-3 border-top" style="border-color:#d4ecfd!important;">
                    {{ $movimientos->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ══ MODAL NUEVO EGRESO ════════════════════════════════ --}}
    <div class="modal fade eg-modal" id="modalEgreso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                {{-- Header degradado --}}
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-3" style="position:relative;z-index:2;">
                        <div class="eg-modal-header-icon">
                            <i data-feather="trending-down"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0">Registrar Egreso</h5>
                            <div class="modal-subtitle">Completa los datos para registrar un nuevo gasto</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="position:relative;z-index:2;"></button>
                </div>

                <form method="POST" action="{{ route('admin-sistema.finanzas.egresos.store') }}">
                    @csrf

                    {{-- Body con scroll --}}
                    <div class="modal-body">

                        {{-- ── Tipo de egreso (card grid con iconos) ── --}}
                        @php
                        $tipoIconos = [
                            'pago_docente' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><circle cx="12" cy="10" r="2.5"/></svg>',
                            'salario_administrativo' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="7" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>',
                            'alquiler_local' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/></svg>',
                            'servicios_basicos' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
                            'materiales_belleza' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9.5 16 2 2 4-4"/></svg>',
                            'insumos_productos' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/></svg>',
                            'equipos_herramientas' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
                            'marketing_publicidad' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M22 12A10 10 0 1 1 2 12a10 10 0 0 1 20 0z"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>',
                            'limpieza_mantenimiento' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"/><path d="M9 10h.01M12 10h.01M15 10h.01"/></svg>',
                            'impuestos_tributos' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>',
                            'papeleria_utiles' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>',
                            'alimentacion_personal' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>',
                            'transporte_movilidad' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v9a2 2 0 0 1-2 2h-2"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
                            'capacitacion_docente' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
                            'uniformes_vestimenta' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.86H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.86l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg>',
                            'seguro_sctr' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                            'otros_gastos' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>',
                        ];
                        @endphp
                        <div class="eg-tipo-section-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            Tipo de egreso
                        </div>
                        <div class="eg-tipo-grid mb-1" id="egresoTipoGrid">
                            @foreach ($tiposEgreso as $valor => $label)
                                <button
                                    type="button"
                                    class="eg-tipo-btn {{ old('subtipo', 'otros_gastos') === $valor ? 'active' : '' }}"
                                    data-egreso-tipo="{{ $valor }}"
                                >
                                    <span class="et-icon">{!! $tipoIconos[$valor] ?? $tipoIconos['otros_gastos'] !!}</span>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="subtipo" id="egresoSubtipoInput" value="{{ old('subtipo', 'otros_gastos') }}">
                        @error('subtipo')
                            <div class="text-danger small mb-2 mt-1">{{ $message }}</div>
                        @enderror

                        {{-- ── Seccion: Informacion principal ── --}}
                        <div class="eg-form-section">
                            <div class="eg-form-section-title">
                                <i data-feather="file-text"></i>
                                Informacion principal
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Concepto / Descripcion <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="concepto"
                                        class="form-control @error('concepto') is-invalid @enderror"
                                        value="{{ old('concepto') }}"
                                        placeholder="Describe brevemente el gasto..."
                                        required
                                    >
                                    @error('concepto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Categoria</label>
                                    <select name="categoria" id="egresoCategoriaInput" class="form-select @error('categoria') is-invalid @enderror">
                                        @foreach ($categoriasEgreso as $valor => $label)
                                            <option value="{{ $valor }}" @selected(old('categoria', 'sin_categoria') === $valor)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('categoria')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="egresoPersonaLabel">Docente / Curso</label>

                                    <div id="egresoPersonaSelectWrap" class="d-none">
                                        <select id="egresoPersonaSelect" class="form-select @error('docente_curso') is-invalid @enderror">
                                            <option value="">Selecciona personal</option>
                                        </select>
                                    </div>

                                    <div id="egresoPersonaInputWrap">
                                        <input
                                            type="text"
                                            id="egresoPersonaInput"
                                            name="docente_curso"
                                            class="form-control @error('docente_curso') is-invalid @enderror"
                                            value="{{ old('docente_curso') }}"
                                            placeholder="Requerido para pago de docente o salario administrativo"
                                        >
                                    </div>

                                    @error('docente_curso')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Seccion: Datos financieros ── --}}
                        <div class="eg-form-section">
                            <div class="eg-form-section-title">
                                <i data-feather="dollar-sign"></i>
                                Datos financieros
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Monto (S/) <span class="text-danger">*</span></label>
                                    <input
                                        type="number" min="0.01" step="0.01"
                                        name="monto"
                                        class="form-control @error('monto') is-invalid @enderror"
                                        value="{{ old('monto') }}"
                                        placeholder="0.00"
                                        required
                                    >
                                    @error('monto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        name="fecha"
                                        class="form-control @error('fecha') is-invalid @enderror"
                                        value="{{ old('fecha', now()->format('Y-m-d')) }}"
                                        required
                                    >
                                    @error('fecha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Metodo de pago <span class="text-danger">*</span></label>
                                    <select name="metodo_pago" class="form-select @error('metodo_pago') is-invalid @enderror" required>
                                        @foreach ($metodosEgreso as $metodo)
                                            <option value="{{ $metodo }}" @selected(old('metodo_pago', 'Efectivo') === $metodo)>{{ $metodo }}</option>
                                        @endforeach
                                    </select>
                                    @error('metodo_pago')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Seccion: Comprobante ── --}}
                        <div class="eg-form-section">
                            <div class="eg-form-section-title">
                                <i data-feather="clipboard"></i>
                                Comprobante y observaciones
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo comprobante <span class="text-danger">*</span></label>
                                    <select name="tipo_comprobante" class="form-select @error('tipo_comprobante') is-invalid @enderror" required>
                                        @foreach ($tiposComprobanteEgreso as $tipoComprobante)
                                            <option value="{{ $tipoComprobante }}" @selected(old('tipo_comprobante', 'Recibo') === $tipoComprobante)>{{ $tipoComprobante }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipo_comprobante')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">N. comprobante</label>
                                    <input
                                        type="text"
                                        name="nro_comprobante"
                                        class="form-control @error('nro_comprobante') is-invalid @enderror"
                                        value="{{ old('nro_comprobante') }}"
                                        placeholder="Ej: F001-000123"
                                    >
                                    @error('nro_comprobante')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Observaciones</label>
                                    <textarea
                                        name="observaciones"
                                        class="form-control @error('observaciones') is-invalid @enderror"
                                        rows="2"
                                        placeholder="Informacion adicional (opcional)..."
                                    >{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Campos ocultos --}}
                        <input type="hidden" name="anio_escolar_id"       value="{{ old('anio_escolar_id', $anioId) }}">
                        <input type="hidden" name="redirect_fecha_inicio"  value="{{ $filtros['fecha_inicio_input'] ?? '' }}">
                        <input type="hidden" name="redirect_fecha_fin"     value="{{ $filtros['fecha_fin_input'] ?? '' }}">
                        <input type="hidden" name="redirect_categoria"     value="{{ $filtros['categoria'] ?? 'todas' }}">
                        <input type="hidden" name="redirect_buscar"        value="{{ $filtros['buscar'] ?? '' }}">
                    </div>

                    {{-- Footer fijo --}}
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-light border fw-semibold px-4" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="eg-btn-filter px-5">
                            <i data-feather="save"></i>
                            Guardar Egreso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MODAL ANULAR ══════════════════════════════════════ --}}
    <div class="modal fade eg-modal-anular" id="modalAnularEgreso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i data-feather="alert-octagon" style="width:22px;height:22px;color:#fff;stroke:#fff;"></i>
                        <h5 class="modal-title mb-0">Anular egreso</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form
                    method="POST"
                    id="formAnularEgreso"
                    data-route-template="{{ route('admin-sistema.finanzas.egresos.anular', ['egreso' => '__ID__']) }}"
                >
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="eg-anular-concepto-box">
                            <div class="eg-anular-concepto-icon">
                                <i data-feather="file-minus"></i>
                            </div>
                            <div>
                                <div class="eg-anular-concepto-label">Egreso a anular</div>
                                <div class="eg-anular-concepto-text" id="anularConceptoText">-</div>
                            </div>
                        </div>
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Motivo de anulacion <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required minlength="6" maxlength="255" placeholder="Describe el motivo de la anulacion..."></textarea>
                        <div class="text-muted mt-2" style="font-size:0.76rem;">
                            <i data-feather="info" style="width:12px;height:12px;"></i>
                            Esta accion no se puede deshacer. El registro quedara marcado como anulado.
                        </div>
                    </div>
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-light border fw-semibold px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4">
                            <i data-feather="x-circle" style="width:15px;height:15px;"></i>
                            Confirmar anulacion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const subtipoInput   = document.getElementById('egresoSubtipoInput');
            const categoriaInput = document.getElementById('egresoCategoriaInput');
            const tipoBtns       = document.querySelectorAll('[data-egreso-tipo]');
            const mapCategoria   = @json($tipoCategoriaMap ?? []);
            const personaLabel   = document.getElementById('egresoPersonaLabel');
            const personaInput   = document.getElementById('egresoPersonaInput');
            const personaInputWrap = document.getElementById('egresoPersonaInputWrap');
            const personaSelect  = document.getElementById('egresoPersonaSelect');
            const personaSelectWrap = document.getElementById('egresoPersonaSelectWrap');
            const docentesEgreso = @json($docentesEgreso ?? []);
            const administrativosEgreso = @json($administrativosEgreso ?? []);

            function setPersonaControlByTipo(subtipo) {
                if (!personaLabel || !personaInput || !personaInputWrap || !personaSelect || !personaSelectWrap) {
                    return;
                }

                const configuraciones = {
                    pago_docente: {
                        label: 'Docente',
                        placeholder: 'Selecciona docente',
                        options: docentesEgreso,
                    },
                    salario_administrativo: {
                        label: 'Personal administrativo',
                        placeholder: 'Selecciona personal administrativo',
                        options: administrativosEgreso,
                    },
                };

                const config = configuraciones[subtipo] ?? null;
                const valueActual = (personaSelect.value || personaInput.value || '').trim();
                const esTipoPersonal = subtipo === 'pago_docente' || subtipo === 'salario_administrativo';

                if (!config || !Array.isArray(config.options) || config.options.length === 0) {
                    personaLabel.textContent = 'Docente / Curso';
                    personaSelectWrap.classList.add('d-none');
                    personaInputWrap.classList.remove('d-none');

                    if (personaSelect.value) {
                        personaInput.value = personaSelect.value;
                    }

                    personaSelect.removeAttribute('name');
                    personaSelect.required = false;
                    personaInput.setAttribute('name', 'docente_curso');
                    personaInput.required = esTipoPersonal;
                    personaInput.placeholder = esTipoPersonal
                        ? 'Ingresa el nombre del personal'
                        : 'Opcional';
                    return;
                }

                personaLabel.textContent = config.label;
                personaInputWrap.classList.add('d-none');
                personaSelectWrap.classList.remove('d-none');
                personaInput.removeAttribute('name');
                personaInput.required = false;
                personaSelect.setAttribute('name', 'docente_curso');
                personaSelect.required = true;

                personaSelect.innerHTML = '';

                const optDefault = document.createElement('option');
                optDefault.value = '';
                optDefault.textContent = config.placeholder;
                personaSelect.appendChild(optDefault);

                config.options.forEach((item) => {
                    const texto = String(item || '').trim();
                    if (!texto) return;
                    const option = document.createElement('option');
                    option.value = texto;
                    option.textContent = texto;
                    personaSelect.appendChild(option);
                });

                if (valueActual) {
                    const existe = config.options.includes(valueActual);
                    if (!existe) {
                        const option = document.createElement('option');
                        option.value = valueActual;
                        option.textContent = valueActual;
                        personaSelect.appendChild(option);
                    }
                    personaSelect.value = valueActual;
                } else {
                    personaSelect.value = '';
                }
            }

            function setTipoSeleccionado(value) {
                subtipoInput.value = value;
                tipoBtns.forEach((btn) => {
                    btn.classList.toggle('active', btn.dataset.egresoTipo === value);
                });
                if (categoriaInput && mapCategoria[value]) {
                    categoriaInput.value = mapCategoria[value];
                }
                setPersonaControlByTipo(value);
            }

            tipoBtns.forEach((btn) => {
                btn.addEventListener('click', () => setTipoSeleccionado(btn.dataset.egresoTipo));
            });

            if (subtipoInput && subtipoInput.value) {
                setTipoSeleccionado(subtipoInput.value);
            }

            const formAnular        = document.getElementById('formAnularEgreso');
            const anularConceptoText = document.getElementById('anularConceptoText');
            const anularButtons     = document.querySelectorAll('.js-open-anular');

            anularButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id       = btn.dataset.egresoId      || '';
                    const concepto = btn.dataset.egresoConcepto || '-';
                    const template = formAnular.dataset.routeTemplate || '';
                    if (template && id) {
                        formAnular.setAttribute('action', template.replace('__ID__', id));
                    }
                    anularConceptoText.textContent = concepto;
                });
            });

            @if ($errors->hasAny(['subtipo', 'concepto', 'categoria', 'metodo_pago', 'monto', 'fecha', 'tipo_comprobante', 'docente_curso', 'observaciones', 'nro_comprobante']))
                const modalEgreso = document.getElementById('modalEgreso');
                if (modalEgreso && window.bootstrap?.Modal) {
                    const instance = new bootstrap.Modal(modalEgreso);
                    instance.show();
                }
            @endif
        })();
    </script>
@endpush
