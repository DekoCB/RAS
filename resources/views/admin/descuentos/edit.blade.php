@extends('layouts.mantis')

@section('title', 'Editar Descuento | R.A.S')
@section('page-title', 'Editar Descuento')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── PAGE HEADER ── */
        .dsc-page-header { display: flex; align-items: center; gap: 16px; margin-bottom: 28px; }
        .dsc-page-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, #26369f, #1d2a82);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(38, 54, 159,.4); flex-shrink: 0;
        }
        .dsc-page-icon svg { width: 24px; height: 24px; color: #fff; }
        .dsc-page-header h4 { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0 0 3px; }
        .dsc-page-header p  { font-size: .84rem; color: #64748b; margin: 0; }

        /* ── FORM CARD ── */
        .dsc-form-card { background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(0,0,0,.09); overflow: hidden; }
        .dsc-form-card-header {
            background: linear-gradient(135deg, #26369f 0%, #1c297d 100%);
            padding: 20px 28px; display: flex; align-items: center; justify-content: space-between;
        }
        .dsc-form-card-header h5 {
            color: #fff; margin: 0; font-weight: 700; font-size: 1.05rem;
            display: flex; align-items: center; gap: 9px;
        }
        .dsc-form-card-header h5 svg { width: 18px; height: 18px; color: #c2caff; }
        .dsc-btn-volver {
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.35);
            color: #fff; font-weight: 600; font-size: .82rem;
            padding: 8px 18px; border-radius: 9px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all .2s;
        }
        .dsc-btn-volver:hover { background: rgba(255,255,255,.25); color: #fff; }
        .dsc-btn-volver svg { width: 14px; height: 14px; }

        /* ── INFO BANNER ── */
        .dsc-info-banner {
            background: #edf8ff; border: 1px solid #ccd2fe; border-radius: 10px;
            padding: 12px 18px; display: flex; align-items: center; gap: 10px;
            color: #18246e; font-size: .84rem; font-weight: 500; margin-bottom: 24px;
        }
        .dsc-info-banner svg { width: 16px; height: 16px; color: #23339e; flex-shrink: 0; }

        /* ── FORM BODY ── */
        .dsc-form-body { padding: 32px 28px; }

        /* Section divider */
        .dsc-section { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; margin-top: 10px; }
        .dsc-section-icon {
            width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
            background: linear-gradient(135deg, #23339e, #213196);
            display: flex; align-items: center; justify-content: center;
        }
        .dsc-section-icon svg { width: 15px; height: 15px; color: #fff; }
        .dsc-section-label { font-weight: 700; font-size: .82rem; text-transform: uppercase; letter-spacing: .07em; color: #475569; }
        .dsc-section-line { flex: 1; height: 1px; background: #e2e8f0; }

        /* ── INPUTS ── */
        .dsc-input-group { position: relative; }
        .dsc-input-group .dsc-input-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none;
        }
        .dsc-input-group .dsc-input-icon svg { width: 15px; height: 15px; }
        .dsc-input-group .form-control,
        .dsc-input-group .form-select { padding-left: 38px; }
        .dsc-label {
            font-size: .82rem; font-weight: 700; color: #374151;
            margin-bottom: 6px; display: flex; align-items: center; gap: 5px;
        }
        .dsc-label .dsc-req { color: #1f2e8f; font-size: .7rem; }
        .dsc-label .dsc-opt {
            color: #94a3b8; font-size: .72rem; font-weight: 500;
            background: #f1f5f9; padding: 1px 7px; border-radius: 4px;
        }
        .dsc-hint { font-size: .77rem; color: #94a3b8; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        .dsc-hint svg { width: 12px; height: 12px; flex-shrink: 0; }
        .form-control, .form-select {
            border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .87rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #23339e; box-shadow: 0 0 0 3px rgba(35, 51, 158,.15);
        }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #dc2626; }

        /* ── VALOR WRAP ── */
        .dsc-valor-wrap { position: relative; }
        .dsc-valor-wrap .dsc-prefix {
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 42px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(180deg, #26369f, #213196);
            border-radius: 9px 0 0 9px; color: #fff;
            font-weight: 800; font-size: .8rem;
            border: 1.5px solid #213196; border-right: none; pointer-events: none;
        }
        .dsc-valor-wrap .form-control { padding-left: 50px; border-radius: 9px; }

        /* ── NOTE ── */
        .dsc-note {
            background: #edf8ff; border: 1px solid #ccd2fe; border-radius: 10px;
            padding: 12px 18px; display: flex; align-items: center; gap: 10px;
            color: #18246e; font-size: .83rem; font-weight: 500; margin-top: 20px;
        }
        .dsc-note svg { width: 16px; height: 16px; color: #23339e; flex-shrink: 0; }

        /* ── ALERT ERROR ── */
        .dsc-alert-error {
            background: #fff5f5; border: 1px solid #fecaca;
            border-radius: 11px; padding: 16px 20px; color: #dc2626; margin-bottom: 24px;
        }
        .dsc-alert-error strong { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; }
        .dsc-alert-error strong svg { width: 16px; height: 16px; }
        .dsc-alert-error ul { margin: 0; padding-left: 18px; }
        .dsc-alert-error li { font-size: .84rem; }

        /* ── FOOTER ── */
        .dsc-form-footer {
            padding: 20px 28px; border-top: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 12px; background: #fafafa;
        }
        .dsc-btn-actualizar {
            background: linear-gradient(135deg, #26369f, #1d2a82);
            color: #fff; border: none; padding: 11px 28px; border-radius: 10px;
            font-size: .88rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(38, 54, 159,.4); cursor: pointer; transition: all .22s;
        }
        .dsc-btn-actualizar:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(38, 54, 159,.55); }
        .dsc-btn-actualizar svg { width: 16px; height: 16px; }
        .dsc-btn-cancelar {
            background: transparent; color: #64748b; border: 1.5px solid #cbd5e1;
            padding: 11px 22px; border-radius: 10px; font-size: .88rem; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all .2s;
        }
        .dsc-btn-cancelar:hover { background: #f1f5f9; color: #374151; border-color: #94a3b8; }
        .dsc-btn-cancelar svg { width: 15px; height: 15px; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- ── PAGE HEADER ── --}}
    <div class="dsc-page-header">
        <div class="dsc-page-icon"><i data-feather="edit"></i></div>
        <div>
            <h4>Editar Descuento #{{ $descuento->id }}</h4>
            <p>Modifica los datos de este descuento o promoción.</p>
        </div>
    </div>

    {{-- ── FORM CARD ── --}}
    <div class="dsc-form-card">

        <div class="dsc-form-card-header">
            <h5><i data-feather="edit-3"></i> Actualizar Descuento</h5>
            <a href="{{ route('admin-sistema.descuentos.index') }}" class="dsc-btn-volver">
                <i data-feather="arrow-left"></i> Volver
            </a>
        </div>

        <div class="dsc-form-body">

            <div class="dsc-info-banner">
                <i data-feather="info"></i>
                Editando descuento <strong>#{{ $descuento->id }}</strong> —
                Tipo: {{ $descuento->tipo }} /
                Concepto: {{ $descuento->conceptoPago->nombre ?? '-' }}
            </div>

            @if ($errors->any())
                <div class="dsc-alert-error">
                    <strong><i data-feather="alert-circle"></i> Revisa los siguientes campos:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form id="dsc-form-edit" method="POST" action="{{ route('admin-sistema.descuentos.update', $descuento) }}">
                @csrf
                @method('PUT')

                {{-- SECCIÓN 1: Clasificación --}}
                <div class="dsc-section">
                    <div class="dsc-section-icon"><i data-feather="filter"></i></div>
                    <span class="dsc-section-label">Clasificación</span>
                    <div class="dsc-section-line"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="dsc-label">Año escolar <span class="dsc-opt">opcional</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="calendar"></i></span>
                            <select name="anio_escolar_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($anios as $a)
                                    <option value="{{ $a->id }}"
                                        @selected(old('anio_escolar_id', $descuento->anio_escolar_id) == $a->id)>
                                        {{ $a->nombre ?? $a->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dsc-hint"><i data-feather="info"></i> Vacío = aplica a cualquier año.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="dsc-label">Nivel <span class="dsc-opt">opcional</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="layers"></i></span>
                            <select name="nivel" class="form-select">
                                <option value="">Todos</option>
                                @foreach(['Inicial','Primaria','Secundaria'] as $n)
                                    <option value="{{ $n }}" @selected(old('nivel', $descuento->nivel) == $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dsc-hint"><i data-feather="info"></i> Vacío = aplica a cualquier nivel.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="dsc-label">Concepto <span class="dsc-req">*</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="file-text"></i></span>
                            <select name="concepto_pago_id" class="form-select @error('concepto_pago_id') is-invalid @enderror" required>
                                @foreach($conceptos as $c)
                                    <option value="{{ $c->id }}"
                                        @selected(old('concepto_pago_id', $descuento->concepto_pago_id) == $c->id)>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: Tipo de descuento --}}
                <div class="dsc-section">
                    <div class="dsc-section-icon"><i data-feather="tag"></i></div>
                    <span class="dsc-section-label">Tipo de descuento</span>
                    <div class="dsc-section-line"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="dsc-label">Tipo <span class="dsc-req">*</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="grid"></i></span>
                            <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach(['HERMANOS','PRONTO_PAGO','BECA','OTRO'] as $t)
                                    <option value="{{ $t }}" @selected(old('tipo', $descuento->tipo) == $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="dsc-label">Aplica a <span class="dsc-opt">opcional</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="users"></i></span>
                            <select name="aplica_a" class="form-select">
                                <option value="">(Ninguno)</option>
                                @foreach(['MENOR','SEGUNDO_EN_ADELANTE','TODOS'] as $a)
                                    <option value="{{ $a }}" @selected(old('aplica_a', $descuento->aplica_a) == $a)>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dsc-hint"><i data-feather="info"></i> Útil para tipo HERMANOS.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="dsc-label">Día límite <span class="dsc-opt">opcional</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="clock"></i></span>
                            <input type="number" min="1" max="31" name="dia_limite"
                                   value="{{ old('dia_limite', $descuento->dia_limite) }}"
                                   class="form-control" placeholder="Ej: 5">
                        </div>
                        <div class="dsc-hint"><i data-feather="info"></i> Útil para PRONTO_PAGO.</div>
                    </div>
                </div>

                {{-- SECCIÓN 3: Valor --}}
                <div class="dsc-section">
                    <div class="dsc-section-icon"><i data-feather="percent"></i></div>
                    <span class="dsc-section-label">Valor del descuento</span>
                    <div class="dsc-section-line"></div>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="dsc-label">Porcentaje <span class="dsc-opt">opcional</span></label>
                        <div class="dsc-valor-wrap">
                            <span class="dsc-prefix">%</span>
                            <input type="number" step="0.01" min="0" max="100" name="porcentaje"
                                   value="{{ old('porcentaje', $descuento->porcentaje) }}"
                                   class="form-control" placeholder="Ej: 5.00">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="dsc-label">Monto fijo <span class="dsc-opt">opcional</span></label>
                        <div class="dsc-valor-wrap">
                            <span class="dsc-prefix">S/</span>
                            <input type="number" step="0.01" min="0" name="monto_fijo"
                                   value="{{ old('monto_fijo', $descuento->monto_fijo) }}"
                                   class="form-control" placeholder="Ej: 20.00">
                        </div>
                    </div>
                </div>

                <div class="dsc-note">
                    <i data-feather="alert-circle"></i>
                    Debes ingresar <strong>Porcentaje</strong> o <strong>Monto fijo</strong> (al menos uno de los dos).
                </div>

                {{-- SECCIÓN 4: Estado --}}
                <div class="dsc-section" style="margin-top:24px;">
                    <div class="dsc-section-icon"><i data-feather="toggle-right"></i></div>
                    <span class="dsc-section-label">Estado</span>
                    <div class="dsc-section-line"></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="dsc-label">Estado del descuento <span class="dsc-req">*</span></label>
                        <div class="dsc-input-group">
                            <span class="dsc-input-icon"><i data-feather="check-square"></i></span>
                            <select name="activo" class="form-select">
                                <option value="1" @selected(old('activo', (string)$descuento->activo) === '1')>Activo</option>
                                <option value="0" @selected(old('activo', (string)$descuento->activo) === '0')>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="dsc-form-footer">
            <button form="dsc-form-edit" type="submit" class="dsc-btn-actualizar">
                <i data-feather="save"></i> Actualizar Descuento
            </button>
            <a href="{{ route('admin-sistema.descuentos.index') }}" class="dsc-btn-cancelar">
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
