@extends('layouts.mantis')

@section('title', 'Editar Tarifa | R.A.S')
@section('page-title', 'Editar Tarifa')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        :root {
            --tar-blue:   #202f91;
            --tar-blue2:  #1c297d;
            --tar-cyan:  #23339e;
            --tar-cyan2: #c2caff;
            --tar-cyan3: #213196;
        }

        /* ── PAGE HEADER ── */
        .tar-page-header {
            display: flex; align-items: center; gap: 16px;
            margin-bottom: 28px;
        }
        .tar-page-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, #26369f, #1d2a82);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(38, 54, 159,.4);
            flex-shrink: 0;
        }
        .tar-page-icon svg { width: 24px; height: 24px; color: #fff; }
        .tar-page-header h4 { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0 0 3px; }
        .tar-page-header p  { font-size: .84rem; color: #64748b; margin: 0; }

        /* ── FORM CARD ── */
        .tar-form-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,.09);
            overflow: hidden;
        }
        .tar-form-card-header {
            background: linear-gradient(135deg, #26369f 0%, #1c297d 100%);
            padding: 20px 28px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .tar-form-card-header h5 {
            color: #fff; margin: 0;
            font-weight: 700; font-size: 1.05rem;
            display: flex; align-items: center; gap: 9px;
        }
        .tar-form-card-header h5 svg { width: 18px; height: 18px; color: var(--tar-cyan2); }
        .tar-btn-volver {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff; font-weight: 600; font-size: .82rem;
            padding: 8px 18px; border-radius: 9px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: all .2s;
        }
        .tar-btn-volver:hover { background: rgba(255,255,255,.25); color: #fff; }
        .tar-btn-volver svg { width: 14px; height: 14px; }

        /* ── INFO BANNER ── */
        .tar-info-banner {
            background: #edf8ff; border: 1px solid #ccd2fe;
            border-radius: 10px; padding: 12px 18px;
            display: flex; align-items: center; gap: 10px;
            color: #18246e; font-size: .84rem; font-weight: 500;
            margin-bottom: 24px;
        }
        .tar-info-banner svg { width: 16px; height: 16px; color: #23339e; flex-shrink: 0; }

        /* ── FORM BODY ── */
        .tar-form-body { padding: 32px 28px; }

        /* Section divider */
        .tar-section {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 20px; margin-top: 10px;
        }
        .tar-section-icon {
            width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--tar-cyan), var(--tar-cyan3));
            display: flex; align-items: center; justify-content: center;
        }
        .tar-section-icon svg { width: 15px; height: 15px; color: #fff; }
        .tar-section-label { font-weight: 700; font-size: .82rem; text-transform: uppercase; letter-spacing: .07em; color: #475569; }
        .tar-section-line { flex: 1; height: 1px; background: #e2e8f0; }

        /* ── STYLED INPUTS ── */
        .tar-input-group { position: relative; }
        .tar-input-group .tar-input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; pointer-events: none;
        }
        .tar-input-group .tar-input-icon svg { width: 15px; height: 15px; }
        .tar-input-group .form-control,
        .tar-input-group .form-select { padding-left: 38px; }
        .tar-label {
            font-size: .82rem; font-weight: 700; color: #374151;
            margin-bottom: 6px; display: flex; align-items: center; gap: 5px;
        }
        .tar-label .tar-req { color: #1f2e8f; font-size: .7rem; }
        .tar-label .tar-opt {
            color: #94a3b8; font-size: .72rem; font-weight: 500;
            background: #f1f5f9; padding: 1px 7px; border-radius: 4px;
        }
        .form-control, .form-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 9px; font-size: .87rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #23339e;
            box-shadow: 0 0 0 3px rgba(35, 51, 158,.15);
        }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #dc2626; }

        /* ── ALERT ERROR ── */
        .tar-alert-error {
            background: #fff5f5; border: 1px solid #fecaca;
            border-radius: 11px; padding: 16px 20px;
            color: #dc2626; margin-bottom: 24px;
        }
        .tar-alert-error strong { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; }
        .tar-alert-error strong svg { width: 16px; height: 16px; }
        .tar-alert-error ul { margin: 0; padding-left: 18px; }
        .tar-alert-error li { font-size: .84rem; }

        /* ── FOOTER BUTTONS ── */
        .tar-form-footer {
            padding: 20px 28px;
            border-top: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 12px;
            background: #fafafa;
        }
        .tar-btn-actualizar {
            background: linear-gradient(135deg, #26369f, #1d2a82);
            color: #fff; border: none;
            padding: 11px 28px; border-radius: 10px;
            font-size: .88rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(38, 54, 159,.4);
            cursor: pointer; transition: all .22s;
        }
        .tar-btn-actualizar:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(38, 54, 159,.55);
        }
        .tar-btn-actualizar svg { width: 16px; height: 16px; }
        .tar-btn-cancelar {
            background: transparent; color: #64748b;
            border: 1.5px solid #cbd5e1;
            padding: 11px 22px; border-radius: 10px;
            font-size: .88rem; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
            transition: all .2s;
        }
        .tar-btn-cancelar:hover {
            background: #f1f5f9; color: #374151; border-color: #94a3b8;
        }
        .tar-btn-cancelar svg { width: 15px; height: 15px; }

        /* ── MONTO PREFIX ── */
        .tar-monto-wrap { position: relative; }
        .tar-monto-wrap .tar-sol {
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 42px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(180deg, #26369f, #213196);
            border-radius: 9px 0 0 9px; color: #fff;
            font-weight: 800; font-size: .85rem;
            border: 1.5px solid #213196; border-right: none;
            pointer-events: none;
        }
        .tar-monto-wrap .form-control { padding-left: 50px; border-radius: 9px; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- ── PAGE HEADER ── --}}
    <div class="tar-page-header">
        <div class="tar-page-icon"><i data-feather="edit"></i></div>
        <div>
            <h4>Editar Tarifa #{{ $tarifa->id }}</h4>
            <p>Modifica los datos de la tarifa registrada.</p>
        </div>
    </div>

    {{-- ── FORM CARD ── --}}
    <div class="tar-form-card">

        {{-- Header --}}
        <div class="tar-form-card-header">
            <h5><i data-feather="edit-3"></i> Actualizar Tarifa</h5>
            <a href="{{ route('admin-sistema.tarifas.index') }}" class="tar-btn-volver">
                <i data-feather="arrow-left"></i> Volver
            </a>
        </div>

        {{-- Body --}}
        <div class="tar-form-body">

            <div class="tar-info-banner">
                <i data-feather="info"></i>
                Estás editando la tarifa <strong>#{{ $tarifa->id }}</strong> —
                {{ $tarifa->conceptoPago->nombre ?? '' }} / {{ $tarifa->nivel }} / {{ $tarifa->anioEscolar->nombre ?? '' }}.
            </div>

            @if ($errors->any())
                <div class="tar-alert-error">
                    <strong><i data-feather="alert-circle"></i> Revisa los siguientes campos:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="tar-form-edit" method="POST" action="{{ route('admin-sistema.tarifas.update', $tarifa) }}">
                @csrf
                @method('PUT')

                {{-- SECCIÓN 1: Clasificación --}}
                <div class="tar-section">
                    <div class="tar-section-icon"><i data-feather="tag"></i></div>
                    <span class="tar-section-label">Clasificación</span>
                    <div class="tar-section-line"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="tar-label">Año escolar <span class="tar-req">*</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="calendar"></i></span>
                            <select name="anio_escolar_id" class="form-select @error('anio_escolar_id') is-invalid @enderror" required>
                                @foreach ($anios as $a)
                                    <option value="{{ $a->id }}" @selected(old('anio_escolar_id', $tarifa->anio_escolar_id) == $a->id)>
                                        {{ $a->nombre ?? $a->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="tar-label">Nivel educativo <span class="tar-req">*</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="layers"></i></span>
                            <select name="nivel" class="form-select @error('nivel') is-invalid @enderror" required>
                                @foreach (['General', 'Inicial', 'Primaria', 'Secundaria'] as $n)
                                    <option value="{{ $n }}" @selected(old('nivel', $tarifa->nivel) == $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="tar-label">Concepto de pago <span class="tar-req">*</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="file-text"></i></span>
                            <select name="concepto_pago_id" class="form-select @error('concepto_pago_id') is-invalid @enderror" required>
                                @foreach ($conceptos as $c)
                                    <option value="{{ $c->id }}" @selected(old('concepto_pago_id', $tarifa->concepto_pago_id) == $c->id)>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="tar-label">Aplica en mes <span class="tar-req">*</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="repeat"></i></span>
                            <select name="mes" class="form-select @error('mes') is-invalid @enderror" required>
                                <option value="0"  @selected(old('mes', $tarifa->mes) == 0)>Todos los meses (general)</option>
                                <option value="1"  @selected(old('mes', $tarifa->mes) == 1)>Solo Enero</option>
                                <option value="2"  @selected(old('mes', $tarifa->mes) == 2)>Solo Febrero</option>
                                <option value="3"  @selected(old('mes', $tarifa->mes) == 3)>Solo Marzo</option>
                                <option value="4"  @selected(old('mes', $tarifa->mes) == 4)>Solo Abril</option>
                                <option value="5"  @selected(old('mes', $tarifa->mes) == 5)>Solo Mayo</option>
                                <option value="6"  @selected(old('mes', $tarifa->mes) == 6)>Solo Junio</option>
                                <option value="7"  @selected(old('mes', $tarifa->mes) == 7)>Solo Julio</option>
                                <option value="8"  @selected(old('mes', $tarifa->mes) == 8)>Solo Agosto</option>
                                <option value="9"  @selected(old('mes', $tarifa->mes) == 9)>Solo Septiembre</option>
                                <option value="10" @selected(old('mes', $tarifa->mes) == 10)>Solo Octubre</option>
                                <option value="11" @selected(old('mes', $tarifa->mes) == 11)>Solo Noviembre</option>
                                <option value="12" @selected(old('mes', $tarifa->mes) == 12)>Solo Diciembre</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: Monto y Vigencia --}}
                <div class="tar-section">
                    <div class="tar-section-icon"><i data-feather="dollar-sign"></i></div>
                    <span class="tar-section-label">Monto y Vigencia</span>
                    <div class="tar-section-line"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="tar-label">Monto <span class="tar-req">*</span></label>
                        <div class="tar-monto-wrap">
                            <span class="tar-sol">S/</span>
                            <input type="number" step="0.01" min="0" name="monto"
                                value="{{ old('monto', $tarifa->monto) }}"
                                class="form-control @error('monto') is-invalid @enderror"
                                placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="tar-label">Vigente desde <span class="tar-opt">opcional</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="calendar"></i></span>
                            <input type="date" name="vigente_desde"
                                value="{{ old('vigente_desde', optional($tarifa->vigente_desde)->format('Y-m-d')) }}"
                                class="form-control @error('vigente_desde') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="tar-label">Vigente hasta <span class="tar-opt">opcional</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="calendar"></i></span>
                            <input type="date" name="vigente_hasta"
                                value="{{ old('vigente_hasta', optional($tarifa->vigente_hasta)->format('Y-m-d')) }}"
                                class="form-control @error('vigente_hasta') is-invalid @enderror">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: Estado --}}
                <div class="tar-section">
                    <div class="tar-section-icon"><i data-feather="toggle-right"></i></div>
                    <span class="tar-section-label">Estado</span>
                    <div class="tar-section-line"></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="tar-label">Estado de la tarifa <span class="tar-req">*</span></label>
                        <div class="tar-input-group">
                            <span class="tar-input-icon"><i data-feather="check-square"></i></span>
                            <select name="activo" class="form-select">
                                <option value="1" @selected(old('activo', (string) $tarifa->activo) === '1')>Activo</option>
                                <option value="0" @selected(old('activo', (string) $tarifa->activo) === '0')>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="tar-form-footer">
            <button form="tar-form-edit" type="submit" class="tar-btn-actualizar">
                <i data-feather="save"></i> Actualizar Tarifa
            </button>
            <a href="{{ route('admin-sistema.tarifas.index') }}" class="tar-btn-cancelar">
                <i data-feather="x"></i> Cancelar
            </a>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
