@extends('layouts.mantis')

@section('title', 'Paso 2 | Matricula')
@section('page-title', 'Datos del Estudiante')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/matricula-wizard.css') }}">
    <style>
        .form-card .form-control.jp-field-invalid,
        .form-card .form-select.jp-field-invalid {
            border-color: #ef476f !important;
            box-shadow: 0 0 0 3px rgba(239, 71, 111, 0.14) !important;
        }

        .form-card .form-control.jp-field-valid,
        .form-card .form-select.jp-field-valid {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12) !important;
        }

        /* Forzar mayúsculas en campos de texto (excepto email, fecha, DNI) */
        input[type="text"]:not([inputmode="numeric"]):not([readonly]) {
            text-transform: uppercase;
        }
        input[type="text"]:not([inputmode="numeric"]):not([readonly])::placeholder {
            text-transform: none;
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
    <div class="card jp-soft-card wizard-top">
        <div class="card-body">
            <div class="wizard-steps">
                <div class="wizard-step">
                    <div class="wizard-step__circle">1</div><span>Nivel y Grado</span>
                </div>
                <div class="wizard-step is-active">
                    <div class="wizard-step__circle">2</div><span>Datos del Estudiante</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">3</div><span>Pago de Matricula</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">4</div><span>Asignaci&oacute;n de Cursos</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">5</div><span>Resumen Final</span>
                </div>
                <div class="wizard-progress"><span style="width:25%"></span></div>
            </div>
        </div>
    </div>

    @php
        $wizardNivel = session('matricula.nivel', '');
        $wizardGrado = session('matricula.grado', '');
        $wizardSeccion = session('matricula.seccion', '');
        $wizardAnioEscolarId = session('matricula.anio_escolar_id');
        $wizardPeriodoAcademicoId = session('matricula.periodo_academico_id');
        $nivel = $wizardNivel !== '' ? $wizardNivel : '-';
        $grado = $wizardGrado !== '' ? $wizardGrado : '-';
        $seccion = $wizardSeccion !== '' ? $wizardSeccion : '-';
        $paso2 = is_array($paso2 ?? null) ? $paso2 : [];
    @endphp

    <form id="matriculaPaso2Form" method="POST" action="{{ route('secretaria.matriculas.paso2.guardar') }}" novalidate>
        @csrf
        <input type="hidden" name="wizard_nivel" value="{{ $wizardNivel }}">
        <input type="hidden" name="wizard_grado" value="{{ $wizardGrado }}">
        <input type="hidden" name="wizard_seccion" value="{{ $wizardSeccion }}">
        <input type="hidden" name="wizard_anio_escolar_id" value="{{ $wizardAnioEscolarId }}">
        <input type="hidden" name="wizard_periodo_academico_id" value="{{ $wizardPeriodoAcademicoId }}">
        <div class="card jp-soft-card matricula-card">
            <div class="card-body">
            <div class="matricula-intro">
                <div class="matricula-title">
                    <span class="matricula-icon"><i data-feather="user-plus"></i></span>
                    <div>
                        <h4 class="mb-1">Datos del Estudiante</h4>
                        <p class="mb-0 text-muted">Complete la informaci&oacute;n del estudiante y su apoderado.</p>
                    </div>
                </div>
            </div>

            <div class="info-banner">
                <div class="info-banner__icon"><i data-feather="info"></i></div>
                <div>
                    <div class="info-banner__text">
                        <strong>Nivel Seleccionado:</strong>
                        {{ $nivel }} - {{ $grado }} - {{ $seccion }}
                    </div>
                    <div class="info-banner__meta">Periodo Acad&eacute;mico: {{ date('Y') }}</div>
                </div>
            </div>

            @php
                $admitidoSel = ($admitidos ?? collect())->first(fn ($a) => $a['id'] === (int) session('matricula.postulante_id', 0));
            @endphp
            @if ($admitidoSel)
                {{-- Los datos ya vienen del expediente de Admision elegido en el paso 1 --}}
                <div class="alert alert-success d-flex align-items-center gap-2 py-2" style="font-size:.88rem">
                    <i data-feather="user-check" style="width:16px;height:16px;flex-shrink:0"></i>
                    <span>
                        Datos cargados desde Admisi&oacute;n: <strong>{{ $admitidoSel['nombre'] }}</strong>
                        &middot; {{ $admitidoSel['codigo'] }} &middot; {{ $admitidoSel['grado'] ?? $admitidoSel['nivel'] }}.
                        Revisa que todo este correcto antes de continuar.
                    </span>
                </div>
            @endif

            <div class="section-head">
                <div class="section-title"><i data-feather="check-circle"></i> Situaci&oacute;n del Alumno</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="form-grid">
                    <div>
                        <label class="form-label">Nombre</label>
                        <select class="form-select" name="situacion_alumno">
                            <option value="">Seleccione...</option>
                            <option value="Nuevo ingreso"
                                @selected(old('situacion_alumno', $paso2['situacion_alumno'] ?? '') === 'Nuevo ingreso')>
                                Nuevo ingreso
                            </option>
                            <option value="Traslado"
                                @selected(old('situacion_alumno', $paso2['situacion_alumno'] ?? '') === 'Traslado')>
                                Traslado
                            </option>
                            <option value="Reingreso"
                                @selected(old('situacion_alumno', $paso2['situacion_alumno'] ?? '') === 'Reingreso')>
                                Reingreso
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="book"></i> Datos Acad&eacute;micos</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="form-grid form-grid-3">
                    <div>
                        <label class="form-label">Instituci&oacute;n de Procedencia</label>
                        <input type="text" class="form-control" name="institucion_procedencia"
                            placeholder="Nombre del instituto anterior"
                            value="{{ old('institucion_procedencia', $paso2['institucion_procedencia'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Es Repitente?</label>
                        <select class="form-select" name="repitente">
                            <option value="">Seleccione...</option>
                            <option value="Si" @selected(old('repitente', $paso2['repitente'] ?? '') === 'Si')>Si</option>
                            <option value="No" @selected(old('repitente', $paso2['repitente'] ?? '') === 'No')>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Grado y Secci&oacute;n</label>
                        <input type="text" class="form-control" value="{{ $grado }} - {{ $seccion }}" readonly>
                    </div>
                </div>
                <div class="form-note">
                    C&Oacute;DIGO DE MATR&Iacute;CULA
                    <small>Se generar&aacute; autom&aacute;ticamente al finalizar.</small>
                </div>
            </div>

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="user"></i> Datos del Alumno</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="form-grid form-grid-3">
                    <div>
                        <label class="form-label">Documento de Identidad</label>
                        <input type="text" class="form-control jp-critical-field" name="alumno_dni"
                            placeholder="Ingrese DNI" required maxlength="8" inputmode="numeric"
                            pattern="[0-9]{8}" value="{{ old('alumno_dni', $paso2['alumno_dni'] ?? '') }}">
                        @error('alumno_dni')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control jp-critical-field" name="alumno_nombres"
                            placeholder="Nombres completos" required
                            value="{{ old('alumno_nombres', $paso2['alumno_nombres'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control jp-critical-field" name="alumno_apellidos"
                            placeholder="Apellidos completos" required
                            value="{{ old('alumno_apellidos', $paso2['alumno_apellidos'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control jp-critical-field" name="alumno_nacimiento" required
                            value="{{ old('alumno_nacimiento', $paso2['alumno_nacimiento'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Sexo</label>
                        <select class="form-select jp-critical-field" name="alumno_sexo" required>
                            <option value="">Seleccione...</option>
                            <option value="Masculino"
                                @selected(old('alumno_sexo', $paso2['alumno_sexo'] ?? '') === 'Masculino')>Masculino</option>
                            <option value="Femenino"
                                @selected(old('alumno_sexo', $paso2['alumno_sexo'] ?? '') === 'Femenino')>Femenino</option>
                        </select>
                        @error('alumno_sexo')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Ciudad</label>
                        <input type="text" class="form-control" name="alumno_ciudad" placeholder="Ciudad de residencia"
                            value="{{ old('alumno_ciudad', $paso2['alumno_ciudad'] ?? '') }}">
                    </div>
                    <div class="span-2">
                        <label class="form-label">Direcci&oacute;n</label>
                        <input type="text" class="form-control" name="alumno_direccion" placeholder="Direccion completa"
                            value="{{ old('alumno_direccion', $paso2['alumno_direccion'] ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="user-check"></i> Datos del Apoderado</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="form-grid form-grid-3">
                    <div>
                        <label class="form-label">Tipo de Relaci&oacute;n</label>
                        <select class="form-select jp-critical-field" name="apoderado_relacion" required>
                            <option value="">Seleccione...</option>
                            <option value="Madre"
                                @selected(old('apoderado_relacion', $paso2['apoderado_relacion'] ?? '') === 'Madre')>Madre</option>
                            <option value="Padre"
                                @selected(old('apoderado_relacion', $paso2['apoderado_relacion'] ?? '') === 'Padre')>Padre</option>
                            <option value="Tutor"
                                @selected(old('apoderado_relacion', $paso2['apoderado_relacion'] ?? '') === 'Tutor')>Tutor</option>
                        </select>
                        @error('apoderado_relacion')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Documento de Identidad</label>
                        <input type="text" class="form-control jp-critical-field" name="apoderado_dni"
                            placeholder="Ingrese DNI" required maxlength="8" inputmode="numeric"
                            pattern="[0-9]{8}" value="{{ old('apoderado_dni', $paso2['apoderado_dni'] ?? '') }}">
                        @error('apoderado_dni')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control jp-critical-field" name="apoderado_nombres"
                            placeholder="Nombres completos" required
                            value="{{ old('apoderado_nombres', $paso2['apoderado_nombres'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control jp-critical-field" name="apoderado_apellidos"
                            placeholder="Apellidos completos" required
                            value="{{ old('apoderado_apellidos', $paso2['apoderado_apellidos'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control jp-critical-field" name="apoderado_nacimiento" required
                            value="{{ old('apoderado_nacimiento', $paso2['apoderado_nacimiento'] ?? '') }}">
                        @error('apoderado_nacimiento')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Sexo</label>
                        <select class="form-select jp-critical-field" name="apoderado_sexo" required>
                            <option value="">Seleccione...</option>
                            <option value="Masculino"
                                @selected(old('apoderado_sexo', $paso2['apoderado_sexo'] ?? '') === 'Masculino')>Masculino</option>
                            <option value="Femenino"
                                @selected(old('apoderado_sexo', $paso2['apoderado_sexo'] ?? '') === 'Femenino')>Femenino</option>
                        </select>
                        @error('apoderado_sexo')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Estado Civil</label>
                        <select class="form-select" name="apoderado_estado_civil">
                            <option value="">Seleccione...</option>
                            <option value="Soltero(a)"
                                @selected(old('apoderado_estado_civil', $paso2['apoderado_estado_civil'] ?? '') === 'Soltero(a)')>Soltero(a)</option>
                            <option value="Casado(a)"
                                @selected(old('apoderado_estado_civil', $paso2['apoderado_estado_civil'] ?? '') === 'Casado(a)')>Casado(a)</option>
                            <option value="Conviviente"
                                @selected(old('apoderado_estado_civil', $paso2['apoderado_estado_civil'] ?? '') === 'Conviviente')>Conviviente</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ciudad</label>
                        <input type="text" class="form-control" name="apoderado_ciudad" placeholder="Ciudad de residencia"
                            value="{{ old('apoderado_ciudad', $paso2['apoderado_ciudad'] ?? '') }}">
                    </div>
                    <div class="span-2">
                        <label class="form-label">Direcci&oacute;n</label>
                        <input type="text" class="form-control" name="apoderado_direccion" placeholder="Direccion completa"
                            value="{{ old('apoderado_direccion', $paso2['apoderado_direccion'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Tel&eacute;fono / Celular</label>
                        <input type="text" class="form-control" name="apoderado_telefono"
                            placeholder="Ingrese numero de tel&eacute;fono"
                            inputmode="numeric" maxlength="9" pattern="[0-9]{9}"
                            value="{{ old('apoderado_telefono', $paso2['apoderado_telefono'] ?? '') }}">
                    </div>
                    <div>
                        <label class="form-label">Correo Electr&oacute;nico</label>
                        <input type="email" class="form-control" name="apoderado_email" placeholder="correo@ejemplo.com"
                            value="{{ old('apoderado_email', $paso2['apoderado_email'] ?? '') }}">
                    </div>
                </div>
            </div>

            {{-- ── Segundo Apoderado (Opcional) ─────────────────────────── --}}
            <div class="section-head section-head--spaced d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="section-title"><i data-feather="users"></i> Segundo Apoderado <span class="text-muted fw-normal" style="font-size:.85em">(Opcional)</span></div>
                <button type="button" id="btnToggleApoderado2" class="btn btn-sm btn-outline-secondary">
                    <span id="btnToggleApoderado2Text">+ Agregar segundo apoderado</span>
                </button>
            </div>
            <div class="section-line"></div>
            <input type="hidden" name="tiene_segundo_apoderado" id="tieneSegundoApoderado"
                value="{{ old('tiene_segundo_apoderado', $paso2['tiene_segundo_apoderado'] ?? '0') }}">

            <div id="segundoApoderadoSection"
                class="{{ (old('tiene_segundo_apoderado', $paso2['tiene_segundo_apoderado'] ?? '0') === '1') ? '' : 'd-none' }}">
                <div class="form-card">
                    <div class="form-grid form-grid-3">
                        <div>
                            <label class="form-label">Tipo de Relaci&oacute;n</label>
                            <select class="form-select" name="apoderado2_relacion">
                                <option value="">Seleccione...</option>
                                <option value="Madre" @selected(old('apoderado2_relacion', $paso2['apoderado2_relacion'] ?? '') === 'Madre')>Madre</option>
                                <option value="Padre" @selected(old('apoderado2_relacion', $paso2['apoderado2_relacion'] ?? '') === 'Padre')>Padre</option>
                                <option value="Tutor" @selected(old('apoderado2_relacion', $paso2['apoderado2_relacion'] ?? '') === 'Tutor')>Tutor</option>
                            </select>
                            @error('apoderado2_relacion')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Documento de Identidad</label>
                            <input type="text" class="form-control" name="apoderado2_dni" id="apoderado2DniField"
                                placeholder="Ingrese DNI" maxlength="8" inputmode="numeric" pattern="[0-9]{8}"
                                value="{{ old('apoderado2_dni', $paso2['apoderado2_dni'] ?? '') }}">
                            @error('apoderado2_dni')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="apoderado2_nombres"
                                placeholder="Nombres completos"
                                value="{{ old('apoderado2_nombres', $paso2['apoderado2_nombres'] ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apoderado2_apellidos"
                                placeholder="Apellidos completos"
                                value="{{ old('apoderado2_apellidos', $paso2['apoderado2_apellidos'] ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="apoderado2_nacimiento"
                                value="{{ old('apoderado2_nacimiento', $paso2['apoderado2_nacimiento'] ?? '') }}">
                            @error('apoderado2_nacimiento')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="apoderado2_sexo">
                                <option value="">Seleccione...</option>
                                <option value="Masculino" @selected(old('apoderado2_sexo', $paso2['apoderado2_sexo'] ?? '') === 'Masculino')>Masculino</option>
                                <option value="Femenino" @selected(old('apoderado2_sexo', $paso2['apoderado2_sexo'] ?? '') === 'Femenino')>Femenino</option>
                            </select>
                            @error('apoderado2_sexo')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Estado Civil</label>
                            <select class="form-select" name="apoderado2_estado_civil">
                                <option value="">Seleccione...</option>
                                <option value="Soltero(a)" @selected(old('apoderado2_estado_civil', $paso2['apoderado2_estado_civil'] ?? '') === 'Soltero(a)')>Soltero(a)</option>
                                <option value="Casado(a)" @selected(old('apoderado2_estado_civil', $paso2['apoderado2_estado_civil'] ?? '') === 'Casado(a)')>Casado(a)</option>
                                <option value="Conviviente" @selected(old('apoderado2_estado_civil', $paso2['apoderado2_estado_civil'] ?? '') === 'Conviviente')>Conviviente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="apoderado2_ciudad" placeholder="Ciudad de residencia"
                                value="{{ old('apoderado2_ciudad', $paso2['apoderado2_ciudad'] ?? '') }}">
                        </div>
                        <div class="span-2">
                            <label class="form-label">Direcci&oacute;n</label>
                            <input type="text" class="form-control" name="apoderado2_direccion" placeholder="Direccion completa"
                                value="{{ old('apoderado2_direccion', $paso2['apoderado2_direccion'] ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">Tel&eacute;fono / Celular</label>
                            <input type="text" class="form-control" name="apoderado2_telefono"
                                placeholder="Ingrese numero de tel&eacute;fono"
                                inputmode="numeric" maxlength="9" pattern="[0-9]{9}"
                                value="{{ old('apoderado2_telefono', $paso2['apoderado2_telefono'] ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">Correo Electr&oacute;nico</label>
                            <input type="email" class="form-control" name="apoderado2_email" placeholder="correo@ejemplo.com"
                                value="{{ old('apoderado2_email', $paso2['apoderado2_email'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

                <div class="wizard-actions">
                    <a href="{{ route('secretaria.matriculas.paso1') }}" class="btn btn-secondary">Volver</a>
                    <a href="{{ route('secretaria.matriculas.index') }}" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Continuar a Pago</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('matriculaPaso2Form');
            if (!form) return;

            const criticalNames = new Set([
                'alumno_dni',
                'alumno_nombres',
                'alumno_apellidos',
                'alumno_nacimiento',
                'alumno_sexo',
                'apoderado_relacion',
                'apoderado_dni',
                'apoderado_nombres',
                'apoderado_apellidos',
                'apoderado_nacimiento',
                'apoderado_sexo',
            ]);
            const serverErrorFields = new Set(@json(array_keys($errors->toArray())));

            const fields = Array.from(form.querySelectorAll('input.form-control, select.form-select'))
                .filter((field) => field.name && !field.readOnly && !field.disabled);

            const trimValue = (value) => (value ?? '').toString().trim();
            const isEmpty = (field) => trimValue(field.value) === '';
            const alumnoDniField = form.querySelector('input[name="alumno_dni"]');
            const apoderadoDniField = form.querySelector('input[name="apoderado_dni"]');

            const isEmailInvalid = (field) => {
                if (field.type !== 'email') return false;
                const value = trimValue(field.value);
                return value !== '' && !field.checkValidity();
            };

            const isAlumnoDniInvalid = (field) => {
                if (field.name !== 'alumno_dni') return false;
                const value = trimValue(field.value);
                if (value === '') return true;
                return !/^\d{8}$/.test(value);
            };

            const isApoderadoDniInvalid = (field) => {
                if (field.name !== 'apoderado_dni') return false;
                const value = trimValue(field.value);
                if (value === '') return true;
                return !/^\d{8}$/.test(value);
            };

            const isFieldInvalid = (field) => {
                if (serverErrorFields.has(field.name)) return true;
                if (isEmailInvalid(field)) return true;
                if (isAlumnoDniInvalid(field)) return true;
                if (isApoderadoDniInvalid(field)) return true;
                if (criticalNames.has(field.name)) return isEmpty(field);
                return false;
            };

            const paintField = (field) => {
                field.classList.remove('jp-field-invalid', 'jp-field-valid');

                const empty = isEmpty(field);
                const invalid = isFieldInvalid(field);

                if (invalid) {
                    field.classList.add('jp-field-invalid');
                    return false;
                }

                if (!empty) {
                    field.classList.add('jp-field-valid');
                }

                return true;
            };

            const validateForm = () => {
                let allValid = true;
                let firstInvalidField = null;

                fields.forEach((field) => {
                    const valid = paintField(field);
                    if (!valid && !firstInvalidField) firstInvalidField = field;
                    allValid = allValid && valid;
                });

                if (!allValid && firstInvalidField) {
                    firstInvalidField.focus();
                }

                return allValid;
            };

            const normalizeAlumnoDni = () => {
                if (!alumnoDniField) return;
                const normalized = trimValue(alumnoDniField.value).replace(/\D+/g, '').slice(0, 8);
                if (alumnoDniField.value !== normalized) {
                    alumnoDniField.value = normalized;
                }
            };

            const normalizeApoderadoDni = () => {
                if (!apoderadoDniField) return;
                const normalized = trimValue(apoderadoDniField.value).replace(/\D+/g, '').slice(0, 8);
                if (apoderadoDniField.value !== normalized) {
                    apoderadoDniField.value = normalized;
                }
            };

            normalizeAlumnoDni();
            normalizeApoderadoDni();

            form.addEventListener('submit', (event) => {
                form.dataset.validationTried = '1';
                normalizeAlumnoDni();
                normalizeApoderadoDni();
                if (!validateForm()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });

            fields.forEach((field) => {
                const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
                field.addEventListener(eventName, () => {
                    if (field === alumnoDniField) normalizeAlumnoDni();
                    if (field === apoderadoDniField) normalizeApoderadoDni();
                    if (serverErrorFields.has(field.name)) serverErrorFields.delete(field.name);
                    if (form.dataset.validationTried === '1') {
                        paintField(field);
                    }
                });
            });

            const hasServerErrors = serverErrorFields.size > 0;
            if (hasServerErrors) {
                form.dataset.validationTried = '1';
                validateForm();
            }

            // ── Auto-llenado del apoderado por DNI ───────────────────────────
            const buscarUrl = '{{ route("secretaria.matriculas.buscar-apoderado") }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            let apoderadoBuscando = false;

            const setApoderadoField = (name, value) => {
                const el = form.querySelector(`[name="apoderado_${name}"]`);
                if (!el || !value) return;
                if (el.tagName === 'SELECT') {
                    const opt = Array.from(el.options).find(o => o.value === value);
                    if (opt) el.value = value;
                } else {
                    el.value = value;
                }
                paintField(el);
            };

            const showApoderadoStatus = (msg, tipo) => {
                let badge = document.getElementById('apoderado-status-badge');
                if (!badge) {
                    badge = document.createElement('small');
                    badge.id = 'apoderado-status-badge';
                    badge.style.cssText = 'display:block;margin-top:4px;font-weight:600;';
                    apoderadoDniField.parentElement.appendChild(badge);
                }
                badge.textContent = msg;
                badge.style.color = tipo === 'ok' ? '#16a34a' : tipo === 'error' ? '#dc2626' : '#6b7280';
            };

            const buscarApoderadoPorDni = async (dni) => {
                if (apoderadoBuscando || dni.length !== 8) return;
                apoderadoBuscando = true;
                showApoderadoStatus('Buscando...', 'info');

                try {
                    const resp = await fetch(`${buscarUrl}?dni=${dni}`, {
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const data = await resp.json();

                    if (data.found) {
                        setApoderadoField('relacion', data.relacion);
                        setApoderadoField('nombres', data.nombres);
                        setApoderadoField('apellidos', data.apellidos);
                        setApoderadoField('nacimiento', data.nacimiento);
                        setApoderadoField('sexo', data.sexo);
                        setApoderadoField('estado_civil', data.estado_civil);
                        setApoderadoField('ciudad', data.ciudad);
                        setApoderadoField('direccion', data.direccion);
                        setApoderadoField('telefono', data.telefono);
                        setApoderadoField('email', data.email);
                        showApoderadoStatus('✓ Apoderado encontrado y datos cargados', 'ok');
                    } else {
                        showApoderadoStatus('Apoderado no registrado — complete los datos manualmente', 'info');
                    }
                } catch {
                    showApoderadoStatus('', 'info');
                } finally {
                    apoderadoBuscando = false;
                }
            };

            apoderadoDniField?.addEventListener('blur', () => {
                const dni = trimValue(apoderadoDniField.value).replace(/\D+/g, '');
                buscarApoderadoPorDni(dni);
            });

            // ── Segundo Apoderado: toggle ─────────────────────────────────
            const tieneSegundoApoderadoInput = document.getElementById('tieneSegundoApoderado');
            const segundoApoderadoSection = document.getElementById('segundoApoderadoSection');
            const btnToggle2 = document.getElementById('btnToggleApoderado2');
            const btnToggle2Text = document.getElementById('btnToggleApoderado2Text');
            const apoderado2DniField = document.getElementById('apoderado2DniField');

            const actualizarToggleApoderado2 = () => {
                const visible = !segundoApoderadoSection.classList.contains('d-none');
                btnToggle2Text.textContent = visible ? '− Quitar segundo apoderado' : '+ Agregar segundo apoderado';
            };

            actualizarToggleApoderado2();

            btnToggle2?.addEventListener('click', () => {
                const visible = segundoApoderadoSection.classList.toggle('d-none');
                tieneSegundoApoderadoInput.value = visible ? '0' : '1';
                actualizarToggleApoderado2();
                if (feather) feather.replace();
            });

            // ── Segundo Apoderado: auto-fill por DNI ──────────────────────
            const setApoderado2Field = (name, value) => {
                const el = form.querySelector(`[name="apoderado2_${name}"]`);
                if (!el || !value) return;
                if (el.tagName === 'SELECT') {
                    const opt = Array.from(el.options).find(o => o.value === value);
                    if (opt) el.value = value;
                } else {
                    el.value = value;
                }
            };

            const showApoderado2Status = (msg, tipo) => {
                let badge = document.getElementById('apoderado2-status-badge');
                if (!badge) {
                    badge = document.createElement('small');
                    badge.id = 'apoderado2-status-badge';
                    badge.style.cssText = 'display:block;margin-top:4px;font-weight:600;';
                    apoderado2DniField.parentElement.appendChild(badge);
                }
                badge.textContent = msg;
                badge.style.color = tipo === 'ok' ? '#16a34a' : tipo === 'error' ? '#dc2626' : '#6b7280';
            };

            let apoderado2Buscando = false;

            const buscarApoderado2PorDni = async (dni) => {
                if (apoderado2Buscando || dni.length !== 8) return;
                apoderado2Buscando = true;
                showApoderado2Status('Buscando...', 'info');
                try {
                    const resp = await fetch(`${buscarUrl}?dni=${dni}`, {
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const data = await resp.json();
                    if (data.found) {
                        setApoderado2Field('relacion', data.relacion);
                        setApoderado2Field('nombres', data.nombres);
                        setApoderado2Field('apellidos', data.apellidos);
                        setApoderado2Field('nacimiento', data.nacimiento);
                        setApoderado2Field('sexo', data.sexo);
                        setApoderado2Field('estado_civil', data.estado_civil);
                        setApoderado2Field('ciudad', data.ciudad);
                        setApoderado2Field('direccion', data.direccion);
                        setApoderado2Field('telefono', data.telefono);
                        setApoderado2Field('email', data.email);
                        showApoderado2Status('✓ Apoderado encontrado y datos cargados', 'ok');
                    } else {
                        showApoderado2Status('No registrado — complete los datos manualmente', 'info');
                    }
                } catch {
                    showApoderado2Status('', 'info');
                } finally {
                    apoderado2Buscando = false;
                }
            };

            apoderado2DniField?.addEventListener('blur', () => {
                const dni = trimValue(apoderado2DniField.value).replace(/\D+/g, '');
                buscarApoderado2PorDni(dni);
            });

            // ── Forzar mayúsculas en campos de texto ──────────────────────
            document.querySelectorAll('input[type="text"]:not([inputmode="numeric"]):not([readonly])')
                .forEach(el => {
                    // Convertir valor existente (al cargar con old())
                    if (el.value) el.value = el.value.toUpperCase();
                    el.addEventListener('input', () => {
                        const pos = el.selectionStart;
                        el.value = el.value.toUpperCase();
                        el.setSelectionRange(pos, pos);
                    });
                });

            // ── Campos de teléfono: solo 9 dígitos numéricos ─────────────
            document.querySelectorAll('input[name*="telefono"]').forEach(el => {
                el.addEventListener('input', () => {
                    el.value = el.value.replace(/\D/g, '').slice(0, 9);
                });
            });
        })();
    </script>
@endpush
