@extends('layouts.mantis')

@section('title', 'Reportes | SuperAdmin')
@section('page-title', 'Reportes globales')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ===== Reportes Hero ===== */
        .rpt-hero {
            background: var(--jp-gradient);
            border-radius: var(--jp-radius);
            padding: 28px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(28, 41, 126, 0.28);
        }
        .rpt-hero::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .rpt-hero::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: 60%;
            width: 160px;
            height: 160px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .rpt-hero__icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.28);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }
        .rpt-hero__icon i { width: 26px; height: 26px; }
        .rpt-hero__text { flex: 1; min-width: 0; }
        .rpt-hero__title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 6px;
            line-height: 1.2;
        }
        .rpt-hero__sub {
            color: rgba(255,255,255,0.88);
            font-size: 0.92rem;
            margin: 0;
        }
        .rpt-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0,0,0,0.18);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 999px;
            font-size: 0.85rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .rpt-hero__badge i { width: 14px; height: 14px; }

        /* ===== Report Card ===== */
        .rpt-card {
            background: #fff;
            border: 1px solid var(--jp-border);
            border-radius: var(--jp-radius);
            box-shadow: 0 16px 36px rgba(16, 24, 74, 0.07);
            overflow: hidden;
        }
        .rpt-card__header {
            background: linear-gradient(135deg, rgba(245, 250, 255,1), rgba(255,255,255,1));
            border-bottom: 1px solid var(--jp-border);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .rpt-card__header-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(28, 41, 126,0.10);
            color: #1c297e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .rpt-card__header-icon i { width: 18px; height: 18px; }
        .rpt-card__header-text h5 {
            font-weight: 800;
            color: #1c1917;
            margin: 0 0 2px;
            font-size: 1rem;
        }
        .rpt-card__header-text small {
            color: #6b7280;
            font-size: 0.82rem;
        }
        .rpt-card__body { padding: 24px; }

        /* ===== Filter Section Divider ===== */
        .rpt-section {
            margin-bottom: 20px;
        }
        .rpt-section-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #18246e;
            margin-bottom: 12px;
        }
        .rpt-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, rgba(28, 41, 126,0.18), transparent);
        }
        .rpt-section-label i { width: 13px; height: 13px; }

        /* ===== Styled Select ===== */
        .rpt-select-wrap { position: relative; }
        .rpt-select-wrap .rpt-field-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #1c297e;
            pointer-events: none;
            z-index: 2;
            width: 15px;
            height: 15px;
        }
        .rpt-select-wrap .form-select {
            padding-left: 36px;
            border: 1.5px solid #d4ecfd;
            border-radius: 12px;
            background-color: #fff;
            color: #1c1917;
            font-weight: 500;
            font-size: 0.88rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .rpt-select-wrap .form-select:focus {
            border-color: #213196;
            box-shadow: 0 0 0 3px rgba(33, 49, 150, 0.14);
        }
        .form-label.rpt-label {
            font-weight: 600;
            font-size: 0.82rem;
            color: #6b7280;
            margin-bottom: 6px;
        }

        /* ===== Mode Selector ===== */
        .rpt-mode-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .rpt-mode-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 10px;
            border: 1.5px solid #d4ecfd;
            background: #fff;
            color: #6b7280;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .rpt-mode-btn i { width: 14px; height: 14px; }
        .rpt-mode-btn:hover {
            border-color: #213196;
            color: #213196;
            background: rgba(33, 49, 150,0.05);
        }
        .rpt-mode-btn.is-active {
            background: linear-gradient(135deg, rgba(28, 41, 126,0.1), rgba(33, 49, 150,0.08));
            border-color: #1c297e;
            color: #162061;
        }
        .rpt-mode-btn input[type="radio"] { display: none; }

        /* ===== Action Buttons ===== */
        .rpt-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 1px solid var(--jp-border);
            margin-top: 8px;
        }
        .rpt-btn-apply {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, var(--jp-blue-700), #1d2b84);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.88rem;
            border-radius: 12px;
            padding: 11px 22px;
            box-shadow: 0 10px 20px rgba(28, 41, 126, 0.25);
            transition: all 0.2s ease;
        }
        .rpt-btn-apply:hover {
            background: linear-gradient(90deg, #18246e, #1c297e);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(28, 41, 126, 0.32);
        }
        .rpt-btn-apply i { width: 16px; height: 16px; }

        .rpt-btn-excel-unica {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #162166, #1f2e8d);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            padding: 11px 22px;
            box-shadow: 0 10px 20px rgba(31, 46, 141, 0.28);
            transition: all 0.2s ease;
        }
        .rpt-btn-excel-unica:hover {
            background: linear-gradient(135deg, #162166, #1b2779);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(31, 46, 141, 0.35);
        }
        .rpt-btn-excel-unica i { width: 16px; height: 16px; }

        .rpt-btn-excel {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #166534, #15803d);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.88rem;
            border-radius: 12px;
            padding: 11px 22px;
            box-shadow: 0 10px 20px rgba(22, 101, 52, 0.28);
            transition: all 0.2s ease;
        }
        .rpt-btn-excel:hover {
            background: linear-gradient(135deg, #14532d, #166534);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(22, 101, 52, 0.35);
        }
        .rpt-btn-excel i { width: 16px; height: 16px; }

        .rpt-actions-hint {
            color: #9ca3af;
            font-size: 0.78rem;
            margin-left: auto;
        }

        /* Info box */
        .rpt-info-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: rgba(33, 49, 150, 0.06);
            border: 1px solid rgba(33, 49, 150, 0.2);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
        }
        .rpt-info-box__icon {
            color: #1d2a82;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .rpt-info-box__icon i { width: 16px; height: 16px; }
        .rpt-info-box p {
            margin: 0;
            font-size: 0.83rem;
            color: #151f5f;
            line-height: 1.5;
        }

        /* Conditional fields visibility */
        .rpt-fields-periodo,
        .rpt-fields-rango {
            display: none;
        }
        .rpt-fields-periodo.show,
        .rpt-fields-rango.show {
            display: contents;
        }

        @media (max-width: 575px) {
            .rpt-hero { flex-direction: column; text-align: center; }
            .rpt-hero__badge { display: none; }
            .rpt-actions { flex-direction: column; align-items: stretch; }
            .rpt-actions-hint { margin-left: 0; }
        }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    {{-- Hero --}}
    <div class="rpt-hero">
        <div class="rpt-hero__icon">
            <i data-feather="bar-chart-2"></i>
        </div>
        <div class="rpt-hero__text">
            <h1 class="rpt-hero__title">Reportes globales</h1>
            <p class="rpt-hero__sub">Genera y exporta reportes detallados de pagos por sección con filtros avanzados.</p>
        </div>
        <div class="rpt-hero__badge">
            <i data-feather="download-cloud"></i>
            Exportar en Excel
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="row">
        <div class="col-12">
            <form id="rptForm" class="rpt-card" method="GET" action="{{ route('admin-sistema.reportes.index') }}">

                {{-- Card Header --}}
                <div class="rpt-card__header">
                    <div class="rpt-card__header-icon">
                        <i data-feather="file-text"></i>
                    </div>
                    <div class="rpt-card__header-text">
                        <h5>Exportar Excel de pagos por sección</h5>
                        <small>Incluye todos los alumnos matriculados según los filtros seleccionados</small>
                    </div>
                </div>

                <div class="rpt-card__body">

                    {{-- Info Box --}}
                    <div class="rpt-info-box">
                        <div class="rpt-info-box__icon"><i data-feather="info"></i></div>
                        <p>
                            Selecciona el año escolar y el modo de reporte. Puedes filtrar por nivel, grado y sección.
                            El archivo Excel se generará con una hoja por sección, mostrando el monto pagado por concepto.
                        </p>
                    </div>

                    {{-- Sección 1: Período --}}
                    <div class="rpt-section">
                        <div class="rpt-section-label">
                            <i data-feather="calendar"></i>
                            Año y modo de reporte
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label rpt-label">Año escolar</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="calendar" class="rpt-field-icon"></i>
                                    <select name="anio_escolar_id" class="form-select">
                                        @foreach ($anios as $opcion)
                                            <option value="{{ $opcion->id }}" @selected(($filtrosExcel['anio_escolar_id'] ?? $anioId) == $opcion->id)>
                                                {{ $opcion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-6">
                                <label class="form-label rpt-label">Modo de reporte</label>
                                <div class="rpt-mode-group" id="modeGroup">
                                    <label class="rpt-mode-btn {{ ($filtrosExcel['tipo_reporte'] ?? 'general') === 'general' ? 'is-active' : '' }}" data-mode="general">
                                        <input type="radio" name="tipo_reporte" value="general" @checked(($filtrosExcel['tipo_reporte'] ?? 'general') === 'general')>
                                        <i data-feather="globe"></i>
                                        General (todo el año)
                                    </label>
                                    <label class="rpt-mode-btn {{ ($filtrosExcel['tipo_reporte'] ?? 'general') === 'periodo' ? 'is-active' : '' }}" data-mode="periodo">
                                        <input type="radio" name="tipo_reporte" value="periodo" @checked(($filtrosExcel['tipo_reporte'] ?? 'general') === 'periodo')>
                                        <i data-feather="bookmark"></i>
                                        Por período
                                    </label>
                                    <label class="rpt-mode-btn {{ ($filtrosExcel['tipo_reporte'] ?? 'general') === 'rango' ? 'is-active' : '' }}" data-mode="rango">
                                        <input type="radio" name="tipo_reporte" value="rango" @checked(($filtrosExcel['tipo_reporte'] ?? 'general') === 'rango')>
                                        <i data-feather="sliders"></i>
                                        Rango de meses
                                    </label>
                                </div>
                            </div>

                            {{-- Período académico (visible solo en modo 'periodo') --}}
                            <div class="col-lg-4 col-md-6 rpt-fields-periodo {{ ($filtrosExcel['tipo_reporte'] ?? 'general') === 'periodo' ? 'show' : '' }}" id="fieldsPeriodo">
                                <label class="form-label rpt-label">Período académico</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="bookmark" class="rpt-field-icon"></i>
                                    <select name="periodo_academico_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach (($catalogoExcel['periodos'] ?? collect()) as $periodo)
                                            <option value="{{ $periodo->id }}" @selected(($filtrosExcel['periodo_academico_id'] ?? null) == $periodo->id)>
                                                {{ $periodo->codigo ?? '' }} - {{ $periodo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Mes desde / hasta (visible solo en modo 'rango') --}}
                            <div class="col-lg-2 col-md-6 rpt-fields-rango {{ ($filtrosExcel['tipo_reporte'] ?? 'general') === 'rango' ? 'show' : '' }}" id="fieldsMesDesde">
                                <label class="form-label rpt-label">Mes desde</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="arrow-right" class="rpt-field-icon"></i>
                                    <select name="mes_desde" class="form-select">
                                        <option value="">-</option>
                                        @foreach (($mesesExcel ?? []) as $numero => $meta)
                                            <option value="{{ $numero }}" @selected(($filtrosExcel['mes_desde'] ?? null) == $numero)>
                                                {{ $meta['nombre'] ?? $numero }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 rpt-fields-rango {{ ($filtrosExcel['tipo_reporte'] ?? 'general') === 'rango' ? 'show' : '' }}" id="fieldsMesHasta">
                                <label class="form-label rpt-label">Mes hasta</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="arrow-left" class="rpt-field-icon"></i>
                                    <select name="mes_hasta" class="form-select">
                                        <option value="">-</option>
                                        @foreach (($mesesExcel ?? []) as $numero => $meta)
                                            <option value="{{ $numero }}" @selected(($filtrosExcel['mes_hasta'] ?? null) == $numero)>
                                                {{ $meta['nombre'] ?? $numero }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sección 2: Filtros académicos --}}
                    <div class="rpt-section">
                        <div class="rpt-section-label">
                            <i data-feather="filter"></i>
                            Filtros académicos
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label rpt-label">Nivel</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="layers" class="rpt-field-icon"></i>
                                    <select name="nivel" class="form-select">
                                        <option value="">Todos los niveles</option>
                                        @foreach (($catalogoExcel['niveles'] ?? collect()) as $nivel)
                                            <option value="{{ $nivel }}" @selected(($filtrosExcel['nivel'] ?? null) === $nivel)>
                                                {{ $nivel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <label class="form-label rpt-label">Grado</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="book-open" class="rpt-field-icon"></i>
                                    <select name="grado_id" class="form-select">
                                        <option value="">Todos los grados</option>
                                        @foreach (($catalogoExcel['grados'] ?? collect()) as $grado)
                                            <option value="{{ $grado->id }}" @selected(($filtrosExcel['grado_id'] ?? null) == $grado->id)>
                                                {{ trim(($grado->nivel ?? '') . ' ' . ($grado->nombre ?? '')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <label class="form-label rpt-label">Sección</label>
                                <div class="rpt-select-wrap">
                                    <i data-feather="users" class="rpt-field-icon"></i>
                                    <select name="seccion_id" class="form-select">
                                        <option value="">Todas las secciones</option>
                                        @foreach (($catalogoExcel['secciones'] ?? collect()) as $seccion)
                                            <option value="{{ $seccion->id }}" @selected(($filtrosExcel['seccion_id'] ?? null) == $seccion->id)>
                                                {{ trim(($seccion->grado->nivel ?? '') . ' ' . ($seccion->grado->nombre ?? '')) }} - {{ $seccion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="rpt-actions">
<button
                            type="submit"
                            formaction="{{ route('admin-sistema.reportes.pagos.excel.unica') }}"
                            class="rpt-btn-excel-unica"
                        >
                            <i data-feather="layers"></i>
                            Hoja única
                        </button>
                        <span class="rpt-actions-hint">
                            <i data-feather="info" style="width:12px;height:12px;display:inline;"></i>
                            Aplica los filtros antes de descargar
                        </span>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const modeGroup = document.getElementById('modeGroup');
        if (!modeGroup) return;

        const fieldsPeriodo = document.querySelectorAll('.rpt-fields-periodo');
        const fieldsRango = document.querySelectorAll('.rpt-fields-rango');

        function updateVisibility(mode) {
            fieldsPeriodo.forEach(el => el.classList.toggle('show', mode === 'periodo'));
            fieldsRango.forEach(el => el.classList.toggle('show', mode === 'rango'));
        }

        modeGroup.addEventListener('change', function (e) {
            if (e.target.name === 'tipo_reporte') {
                const mode = e.target.value;
                // Update active button style
                modeGroup.querySelectorAll('.rpt-mode-btn').forEach(btn => {
                    btn.classList.toggle('is-active', btn.dataset.mode === mode);
                });
                updateVisibility(mode);
                // Re-render feather icons inside newly shown elements
                if (typeof feather !== 'undefined') feather.replace();
            }
        });
    })();
</script>
@endpush
