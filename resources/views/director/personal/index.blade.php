@extends('layouts.mantis')

@section('title', 'Personal | Director')
@section('page-title', 'Personal')
@section('body-class', 'role-director')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    <style>
        /* ── Tabs ── */
        .jp-tabs-nav {
            display: flex;
            gap: .5rem;
            border-bottom: 2px solid var(--dir-border, #d4ecfd);
            margin-bottom: 1.5rem;
        }
        .jp-tabs-nav .jp-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border: none;
            background: none;
            font-weight: 700;
            font-size: .82rem;
            color: #78716c;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            transition: color .2s, border-color .2s, background .2s;
        }
        .jp-tabs-nav .jp-tab-btn:hover { color: #1c297e; background: rgba(28, 41, 126,.05); }
        .jp-tabs-nav .jp-tab-btn.active {
            color: #1c297e;
            border-bottom-color: #213196;
            background: rgba(28, 41, 126,.06);
        }
        .jp-tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 800;
            padding: 0 6px;
            background: rgba(28, 41, 126,.12);
            color: #1c297e;
        }
        .jp-tab-btn.active .jp-tab-badge {
            background: var(--dir-gradient, linear-gradient(135deg,#1c297e,#213196));
            color: #fff;
        }

        /* ── Role pill ── */
        .jp-role-pill {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            border-radius: 999px;
            padding: .28rem .7rem;
            font-size: .72rem;
            font-weight: 700;
        }
        .jp-role-pill.is-docente {
            background: rgba(34,197,94,.12);
            color: #15803d;
            border: 1px solid rgba(34,197,94,.26);
        }
        .jp-role-pill.is-secretaria {
            background: rgba(37,53,156,.12);
            color: #1f2e8d;
            border: 1px solid rgba(37,53,156,.25);
        }

        /* ── Estado badge ── */
        .jp-estado-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            border-radius: 999px;
            padding: .25rem .65rem;
            font-size: .7rem;
            font-weight: 700;
        }
        .jp-estado-badge.activo  { background: rgba(34,197,94,.12); color: #15803d; }
        .jp-estado-badge.inactivo{ background: rgba(43, 58, 153,.12);  color: #1c297e; }
        .jp-estado-badge .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            display: inline-block;
        }
        .jp-estado-badge.activo  .dot { background: #16a34a; }
        .jp-estado-badge.inactivo .dot { background: #1f2e8f; }

        /* ── Register button ── */
        .jp-btn-registrar {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem 1.2rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 700;
            background: var(--dir-gradient, linear-gradient(135deg,#1c297e,#213196));
            color: #fff;
            border: none;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            white-space: nowrap;
        }
        .jp-btn-registrar:hover { opacity: .88; transform: translateY(-1px); color: #fff; }

        /* ── Search bar ── */
        .jp-personal-search .input-group-text {
            background: #fff;
            border-right: none;
            color: #78716c;
        }
        .jp-personal-search .form-control {
            border-left: none;
            font-size: .875rem;
        }
        .jp-personal-search .form-control:focus { box-shadow: none; border-color: #ced4da; }

        /* ── Empty state ── */
        .jp-empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #a8a29e;
        }
        .jp-empty-state i { width: 48px; height: 48px; margin-bottom: 1rem; opacity: .4; }

        /* ── Modal form labels ── */
        .jp-modal-form .form-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #78716c;
            margin-bottom: .3rem;
        }
        .jp-modal-form .form-control,
        .jp-modal-form .form-select {
            font-size: .875rem;
            border-radius: 10px;
        }
        .jp-modal-form .form-control:focus,
        .jp-modal-form .form-select:focus {
            border-color: #213196;
            box-shadow: 0 0 0 3px rgba(33, 49, 150,.15);
        }

        /* ── Divider inside modal ── */
        .jp-modal-section {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #1c297e;
            margin: 1rem 0 .6rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .jp-modal-section::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #d4ecfd;
        }

        /* ── Table avatar ── */
        .jp-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .8rem;
            flex-shrink: 0;
        }
        .jp-avatar.av-docente   { background: rgba(34,197,94,.15); color: #15803d; }
        .jp-avatar.av-secretaria{ background: rgba(37,53,156,.15); color: #1f2e8d; }

        .jp-user-cell {
            display: flex;
            align-items: center;
            gap: .65rem;
        }
        .jp-user-name { font-weight: 700; font-size: .88rem; color: #1c1917; line-height: 1.2; }
        .jp-user-email{ font-size: .75rem; color: #a8a29e; }

        .jp-btn-accion {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid rgba(28, 41, 126, .22);
            background: rgba(28, 41, 126, .08);
            color: #1c297e;
            border-radius: 999px;
            padding: .35rem .8rem;
            font-size: .74rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s ease;
            white-space: nowrap;
        }
        .jp-btn-accion:hover {
            background: rgba(28, 41, 126, .15);
            transform: translateY(-1px);
        }
        .jp-btn-accion.is-blue {
            border-color: rgba(37,53,156,.25);
            background: rgba(37,53,156,.1);
            color: #1f2e8d;
        }
        .jp-btn-accion.is-blue:hover {
            background: rgba(37,53,156,.16);
        }
    </style>
@endpush

@section('sidebar')
    @include('director.partials.sidebar')
@endsection

@section('content')
    @php
        $totalPersonal = $totalDocentes + $totalSecretarias;
    @endphp

    {{-- ── Alerts ── --}}
    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3 border-0 rounded-3" style="background:rgba(34,197,94,.1);color:#15803d;">
            <i data-feather="check-circle"></i>
            {{ session('status') }}
        </div>
    @endif
    @if (session('password_temporal'))
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 border-0 rounded-3" style="background:rgba(33, 49, 150,.12);color:#192572;">
            <i data-feather="key"></i>
            Nueva contraseña generada para <strong>{{ session('password_temporal_user') ?? 'el usuario' }}</strong>:
            <strong>{{ session('password_temporal') }}</strong>
        </div>
    @endif
    @if ($errors->docente->any() || $errors->secretaria->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 border-0 rounded-3">
            <i data-feather="alert-circle"></i>
            Revisa los datos del formulario e intenta nuevamente.
        </div>
    @endif
    @if ($errors->any() && !$errors->docente->any() && !$errors->secretaria->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 border-0 rounded-3">
            <i data-feather="alert-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── KPIs ── --}}
    <div class="dir-kpi-grid mb-4">
        <div class="dir-kpi">
            <div class="dir-kpi__icon">
                <i data-feather="users"></i>
            </div>
            <div>
                <div class="dir-kpi__label">Personal total</div>
                <div class="dir-kpi__num">{{ $totalPersonal }}</div>
                <span class="dir-kpi__chip">Docente + Secretaria</span>
            </div>
        </div>
        <div class="dir-kpi">
            <div class="dir-kpi__icon is-orange">
                <i data-feather="book-open"></i>
            </div>
            <div>
                <div class="dir-kpi__label">Docentes</div>
                <div class="dir-kpi__num">{{ $totalDocentes }}</div>
                <span class="dir-kpi__chip">Rol docente</span>
            </div>
        </div>
        <div class="dir-kpi">
            <div class="dir-kpi__icon is-blue">
                <i data-feather="shield"></i>
            </div>
            <div>
                <div class="dir-kpi__label">Secretarias</div>
                <div class="dir-kpi__num">{{ $totalSecretarias }}</div>
                <span class="dir-kpi__chip">Rol secretaria</span>
            </div>
        </div>
        <div class="dir-kpi">
            <div class="dir-kpi__icon is-amber">
                <i data-feather="search"></i>
            </div>
            <div>
                <div class="dir-kpi__label">Resultados</div>
                <div class="dir-kpi__num">{{ $docentes->total() + $secretarias->total() }}</div>
                <span class="dir-kpi__chip">Búsqueda actual</span>
            </div>
        </div>
    </div>

    {{-- ── Main card ── --}}
    <div class="card jp-soft-card">
        <div class="card-body">

            {{-- Search + Tabs header --}}
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                {{-- Search --}}
                <form method="GET" action="{{ route('director.personal.index') }}"
                      class="d-flex gap-2 flex-grow-1 jp-personal-search" style="max-width:460px;">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="input-group">
                        <span class="input-group-text border-end-0">
                            <i data-feather="search" style="width:16px;height:16px;"></i>
                        </span>
                        <input type="text" class="form-control" name="q"
                               value="{{ $busqueda }}"
                               placeholder="Buscar por nombre o correo…">
                    </div>
                    <button type="submit" class="jp-btn-registrar" style="padding:.5rem 1rem;">
                        Buscar
                    </button>
                    @if($busqueda)
                        <a href="{{ route('director.personal.index', ['tab' => $tab]) }}"
                           class="btn btn-sm btn-light d-flex align-items-center gap-1 rounded-pill"
                           title="Limpiar búsqueda">
                            <i data-feather="x" style="width:14px;height:14px;"></i>
                        </a>
                    @endif
                </form>

                {{-- Register buttons --}}
                <div class="d-flex gap-2">
                    <button class="jp-btn-registrar" data-bs-toggle="modal" data-bs-target="#modalDocente">
                        <i data-feather="plus" style="width:15px;height:15px;"></i>
                        Nuevo Docente
                    </button>
                    <button class="jp-btn-registrar" data-bs-toggle="modal" data-bs-target="#modalSecretaria"
                            style="background:linear-gradient(135deg,#1f2e8d,#25359c);">
                        <i data-feather="plus" style="width:15px;height:15px;"></i>
                        Nueva Secretaria
                    </button>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="jp-tabs-nav">
                <button class="jp-tab-btn {{ $tab === 'docentes' ? 'active' : '' }}"
                        onclick="switchTab(event, 'docentes')">
                    <i data-feather="book-open" style="width:15px;height:15px;"></i>
                    Docentes
                    <span class="jp-tab-badge">{{ $docentes->total() }}</span>
                </button>
                <button class="jp-tab-btn {{ $tab === 'secretarias' ? 'active' : '' }}"
                        onclick="switchTab(event, 'secretarias')">
                    <i data-feather="shield" style="width:15px;height:15px;"></i>
                    Secretarias
                    <span class="jp-tab-badge">{{ $secretarias->total() }}</span>
                </button>
            </div>

            {{-- ────────── TAB: DOCENTES ────────── --}}
            <div id="tab-docentes" class="{{ $tab !== 'docentes' ? 'd-none' : '' }}">
                <div class="table-responsive">
                    <table class="table table-hover jp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>DNI</th>
                                <th>Especialidad</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Fecha ingreso</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($docentes as $usuario)
                                @php
                                    $doc = $usuario->docente;
                                    $iniciales = collect(explode(' ', $usuario->name))
                                        ->take(2)->map(fn($p) => strtoupper(substr($p,0,1)))->implode('');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="jp-user-cell">
                                            <div class="jp-avatar av-docente">{{ $iniciales }}</div>
                                            <div>
                                                <div class="jp-user-name">{{ $usuario->name }}</div>
                                                <div class="jp-user-email">{{ $usuario->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace small fw-semibold text-secondary">
                                            {{ $doc?->dni ?? '—' }}
                                        </span>
                                    </td>
                                    <td>{{ $doc?->especialidad ?? '—' }}</td>
                                    <td>{{ $doc?->telefono ?? '—' }}</td>
                                    <td>
                                        @if($doc)
                                            <span class="jp-estado-badge {{ $doc->estado === 'activo' ? 'activo' : 'inactivo' }}">
                                                <span class="dot"></span>
                                                {{ ucfirst($doc->estado ?? 'activo') }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            {{ $doc?->fecha_ingreso ? \Carbon\Carbon::parse($doc->fecha_ingreso)->format('d/m/Y') : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            {{ optional($usuario->created_at)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="jp-btn-accion"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarDocente"
                                                data-edit-url="{{ route('director.personal.update', $usuario) }}"
                                                data-id="{{ $usuario->id }}"
                                                data-role="Docente"
                                                data-registro="{{ optional($usuario->created_at)->format('d/m/Y H:i') }}"
                                                data-email="{{ $usuario->email }}"
                                                data-doc-nombres="{{ $doc?->nombres }}"
                                                data-doc-apellidos="{{ $doc?->apellidos }}"
                                                data-doc-dni="{{ $doc?->dni }}"
                                                data-doc-telefono="{{ $doc?->telefono }}"
                                                data-doc-especialidad="{{ $doc?->especialidad }}"
                                                data-doc-fecha-ingreso="{{ $doc?->fecha_ingreso ? \Carbon\Carbon::parse($doc->fecha_ingreso)->format('Y-m-d') : '' }}"
                                                data-doc-estado="{{ $doc?->estado }}"
                                                data-doc-sexo="{{ $doc?->sexo }}"
                                                data-doc-fecha-nacimiento="{{ $doc?->fecha_nacimiento ? \Carbon\Carbon::parse($doc->fecha_nacimiento)->format('Y-m-d') : '' }}"
                                                data-doc-direccion="{{ $doc?->direccion }}"
                                                data-doc-ciudad="{{ $doc?->ciudad }}"
                                                data-doc-grado-academico="{{ $doc?->grado_academico }}"
                                                data-doc-tipo-contrato="{{ $doc?->tipo_contrato }}">
                                            <i data-feather="eye" style="width:14px;height:14px;"></i>
                                            Ver / Editar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="jp-empty-state">
                                            <i data-feather="book-open"></i>
                                            <p class="mb-0 fw-semibold">No hay docentes registrados</p>
                                            @if($busqueda)
                                                <p class="small mt-1">No se encontraron resultados para "<strong>{{ $busqueda }}</strong>"</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($docentes->hasPages())
                    <div class="mt-3">
                        {{ $docentes->appends(['tab' => 'docentes', 'q' => $busqueda])->links() }}
                    </div>
                @endif
            </div>

            {{-- ────────── TAB: SECRETARIAS ────────── --}}
            <div id="tab-secretarias" class="{{ $tab !== 'secretarias' ? 'd-none' : '' }}">
                <div class="table-responsive">
                    <table class="table table-hover jp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Secretaria</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($secretarias as $usuario)
                                @php
                                    $iniciales = collect(explode(' ', $usuario->name))
                                        ->take(2)->map(fn($p) => strtoupper(substr($p,0,1)))->implode('');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="jp-user-cell">
                                            <div class="jp-avatar av-secretaria">{{ $iniciales }}</div>
                                            <div>
                                                <div class="jp-user-name">{{ $usuario->name }}</div>
                                                <div class="jp-user-email">{{ $usuario->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            {{ optional($usuario->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="jp-btn-accion is-blue"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarSecretaria"
                                                data-edit-url="{{ route('director.personal.update', $usuario) }}"
                                                data-id="{{ $usuario->id }}"
                                                data-role="Secretaria"
                                                data-registro="{{ optional($usuario->created_at)->format('d/m/Y H:i') }}"
                                                data-name="{{ $usuario->name }}"
                                                data-dni="{{ $usuario->dni }}"
                                                data-email="{{ $usuario->email }}">
                                            <i data-feather="eye" style="width:14px;height:14px;"></i>
                                            Ver / Editar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="jp-empty-state">
                                            <i data-feather="shield"></i>
                                            <p class="mb-0 fw-semibold">No hay secretarias registradas</p>
                                            @if($busqueda)
                                                <p class="small mt-1">No se encontraron resultados para "<strong>{{ $busqueda }}</strong>"</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($secretarias->hasPages())
                    <div class="mt-3">
                        {{ $secretarias->appends(['tab' => 'secretarias', 'q' => $busqueda])->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ════════════ MODAL: Registrar Docente ════════════ --}}
    <div class="modal fade" id="modalDocente" tabindex="-1" aria-labelledby="modalDocenteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden;">
                <div class="modal-header border-0 pb-0"
                     style="background:linear-gradient(135deg,#1c297e,#213196);padding:1.4rem 1.75rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i data-feather="book-open" style="color:#fff;width:20px;height:20px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 text-white fw-800" id="modalDocenteLabel">Registrar Docente</h5>
                            <p class="mb-0 small" style="color:rgba(255,255,255,.75);">Completa los datos del nuevo docente</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('director.personal.docentes.store') }}" class="jp-modal-form" id="formDocente">
                        @csrf

                        <div class="jp-modal-section">Datos personales</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres"
                                    class="form-control @error('nombres', 'docente') is-invalid @enderror"
                                    value="{{ old('nombres') }}" placeholder="Ej: Juan Carlos" required>
                                @error('nombres', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos"
                                    class="form-control @error('apellidos', 'docente') is-invalid @enderror"
                                    value="{{ old('apellidos') }}" placeholder="Ej: Pérez López" required>
                                @error('apellidos', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DNI <span class="text-danger">*</span></label>
                                <input type="text" name="dni"
                                    class="form-control @error('dni', 'docente') is-invalid @enderror"
                                    value="{{ old('dni') }}" placeholder="8 dígitos" maxlength="20" required>
                                @error('dni', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código</label>
                                <input type="text" class="form-control bg-light" value="{{ $nextCodigo }}" readonly>
                                <div class="form-text">Asignado automáticamente.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono"
                                    class="form-control @error('telefono', 'docente') is-invalid @enderror"
                                    value="{{ old('telefono') }}" placeholder="Opcional">
                                @error('telefono', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Especialidad</label>
                                <input type="text" name="especialidad"
                                    class="form-control @error('especialidad', 'docente') is-invalid @enderror"
                                    value="{{ old('especialidad') }}" placeholder="Ej: Matemática">
                                @error('especialidad', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de ingreso</label>
                                <input type="date" name="fecha_ingreso"
                                    class="form-control @error('fecha_ingreso', 'docente') is-invalid @enderror"
                                    value="{{ old('fecha_ingreso') }}">
                                @error('fecha_ingreso', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select @error('estado', 'docente') is-invalid @enderror">
                                    <option value="activo"   @selected(old('estado', 'activo') === 'activo')>Activo</option>
                                    <option value="inactivo" @selected(old('estado') === 'inactivo')>Inactivo</option>
                                </select>
                                @error('estado', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="jp-modal-section">Acceso al sistema</div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email', 'docente') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                                @error('email', 'docente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="alert alert-light border mb-0">
                                    Usuario y contraseña inicial: DNI del docente.
                                </div>
                            </div>





















                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formDocente" class="jp-btn-registrar">
                        <i data-feather="user-plus" style="width:15px;height:15px;"></i>
                        Crear docente
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════ MODAL: Registrar Secretaria ════════════ --}}
    <div class="modal fade" id="modalSecretaria" tabindex="-1" aria-labelledby="modalSecretariaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden;">
                <div class="modal-header border-0 pb-0"
                     style="background:linear-gradient(135deg,#1f2e8d,#25359c);padding:1.4rem 1.75rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i data-feather="shield" style="color:#fff;width:20px;height:20px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 text-white fw-800" id="modalSecretariaLabel">Registrar Secretaria</h5>
                            <p class="mb-0 small" style="color:rgba(255,255,255,.75);">Datos de acceso para la nueva secretaria</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('director.personal.secretarias.store') }}" class="jp-modal-form" id="formSecretaria">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name', 'secretaria') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Nombre y apellidos completos" required>
                                @error('name', 'secretaria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email', 'secretaria') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                                @error('email', 'secretaria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DNI <span class="text-danger">*</span></label>
                                <input type="text" name="dni"
                                    class="form-control @error('dni', 'secretaria') is-invalid @enderror"
                                    value="{{ old('dni') }}" placeholder="DNI" required>
                                @error('dni', 'secretaria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="alert alert-light border w-100 mb-0">
                                    Usuario y contraseña inicial: DNI de la secretaria.
                                </div>
                            </div>












                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formSecretaria"
                            class="jp-btn-registrar" style="background:linear-gradient(135deg,#1f2e8d,#25359c);">
                        <i data-feather="user-plus" style="width:15px;height:15px;"></i>
                        Crear secretaria
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ MODAL: Editar Docente ═══════════ --}}
    <div class="modal fade" id="modalEditarDocente" tabindex="-1" aria-labelledby="modalEditarDocenteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden;">
                <div class="modal-header border-0 pb-0"
                     style="background:linear-gradient(135deg,#1c297e,#213196);padding:1.4rem 1.75rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i data-feather="edit-3" style="color:#fff;width:20px;height:20px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 text-white fw-800" id="modalEditarDocenteLabel">Ver / Editar Docente</h5>
                            <p class="mb-0 small" style="color:rgba(255,255,255,.75);">Actualiza datos personales y de acceso</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <form method="POST"
                          action="{{ route('director.personal.update', $docentes->first()?->id ?? 0) }}"
                          class="jp-modal-form"
                          id="formEditarDocente">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tab" value="docentes">

                        <div class="jp-modal-section">Datos del sistema</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">ID usuario</label>
                                <input type="text" id="edit_doc_id" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rol</label>
                                <input type="text" id="edit_doc_role" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Registro</label>
                                <input type="text" id="edit_doc_registro" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        <div class="jp-modal-section">Datos personales</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" id="edit_doc_nombres" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" id="edit_doc_apellidos" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input type="text" name="dni" id="edit_doc_dni" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sexo</label>
                                <select name="sexo" id="edit_doc_sexo" class="form-select">
                                    <option value="">Seleccionar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="edit_doc_fecha_nacimiento" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="edit_doc_telefono" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="ciudad" id="edit_doc_ciudad" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" id="edit_doc_direccion" class="form-control">
                            </div>
                        </div>

                        <div class="jp-modal-section">Datos profesionales</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Especialidad</label>
                                <input type="text" name="especialidad" id="edit_doc_especialidad" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo contrato</label>
                                <select name="tipo_contrato" id="edit_doc_tipo_contrato" class="form-select">
                                    <option value="">Seleccionar</option>
                                    <option value="Nombrado">Nombrado</option>
                                    <option value="Contratado">Contratado</option>
                                    <option value="Suplente">Suplente</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Grado académico</label>
                                <input type="text" name="grado_academico" id="edit_doc_grado_academico" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" id="edit_doc_estado" class="form-select">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha ingreso</label>
                                <input type="date" name="fecha_ingreso" id="edit_doc_fecha_ingreso" class="form-control">
                            </div>
                        </div>

                        <div class="jp-modal-section">Acceso al sistema</div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit_doc_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" id="edit_doc_password" class="form-control" placeholder="Opcional (mínimo 8 caracteres)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="password_confirmation" id="edit_doc_password_confirmation" class="form-control" placeholder="Repite la contraseña">
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">
                                    Por seguridad, la contrase&ntilde;a actual no se puede visualizar porque esta cifrada.
                                </small>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="generar_password" value="1" id="edit_doc_generate_password">
                                    <label class="form-check-label" for="edit_doc_generate_password">
                                        Generar nueva contraseña automáticamente y mostrarla al guardar
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarDocente" class="jp-btn-registrar">
                        <i data-feather="save" style="width:15px;height:15px;"></i>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ MODAL: Editar Secretaria ═══════════ --}}
    <div class="modal fade" id="modalEditarSecretaria" tabindex="-1" aria-labelledby="modalEditarSecretariaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden;">
                <div class="modal-header border-0 pb-0"
                     style="background:linear-gradient(135deg,#1f2e8d,#25359c);padding:1.4rem 1.75rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i data-feather="edit-3" style="color:#fff;width:20px;height:20px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 text-white fw-800" id="modalEditarSecretariaLabel">Ver / Editar Secretaria</h5>
                            <p class="mb-0 small" style="color:rgba(255,255,255,.75);">Actualiza datos de acceso</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <form method="POST"
                          action="{{ route('director.personal.update', $secretarias->first()?->id ?? 0) }}"
                          class="jp-modal-form"
                          id="formEditarSecretaria">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tab" value="secretarias">

                        <div class="jp-modal-section">Datos del sistema</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">ID usuario</label>
                                <input type="text" id="edit_sec_id" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rol</label>
                                <input type="text" id="edit_sec_role" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Registro</label>
                                <input type="text" id="edit_sec_registro" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        <div class="jp-modal-section">Información general</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_sec_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit_sec_email" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">DNI <span class="text-danger">*</span></label>
                                <input type="text" name="dni" id="edit_sec_dni" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" id="edit_sec_password" class="form-control" placeholder="Opcional (mínimo 8 caracteres)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="password_confirmation" id="edit_sec_password_confirmation" class="form-control" placeholder="Repite la contraseña">
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">
                                    Por seguridad, la contrase&ntilde;a actual no se puede visualizar porque esta cifrada.
                                </small>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="generar_password" value="1" id="edit_sec_generate_password">
                                    <label class="form-check-label" for="edit_sec_generate_password">
                                        Generar nueva contraseña automáticamente y mostrarla al guardar
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarSecretaria"
                            class="jp-btn-registrar" style="background:linear-gradient(135deg,#1f2e8d,#25359c);">
                        <i data-feather="save" style="width:15px;height:15px;"></i>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function switchTab(event, name) {
        document.querySelectorAll('.jp-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('#tab-docentes, #tab-secretarias').forEach(t => t.classList.add('d-none'));
        document.getElementById('tab-' + name).classList.remove('d-none');
        if (event?.currentTarget) {
            event.currentTarget.classList.add('active');
        }

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', name);
        history.replaceState(null, '', url);

        feather.replace();
    }

    function togglePwd(inputId, btn) {
        const input = document.getElementById(inputId);
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.innerHTML = isText
            ? '<i data-feather="eye" style="width:15px;height:15px;"></i>'
            : '<i data-feather="eye-off" style="width:15px;height:15px;"></i>';
        feather.replace();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const modalEditarDocente = document.getElementById('modalEditarDocente');
        if (modalEditarDocente) {
            modalEditarDocente.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const form = document.getElementById('formEditarDocente');
                const generate = document.getElementById('edit_doc_generate_password');
                const pwd = document.getElementById('edit_doc_password');
                const pwdC = document.getElementById('edit_doc_password_confirmation');

                const toggleDocPwd = () => {
                    const disable = !!generate?.checked;
                    if (pwd) {
                        pwd.value = '';
                        pwd.disabled = disable;
                    }
                    if (pwdC) {
                        pwdC.value = '';
                        pwdC.disabled = disable;
                    }
                };

                if (form) form.action = trigger.getAttribute('data-edit-url') || form.action;
                document.getElementById('edit_doc_id').value = trigger.getAttribute('data-id') || '';
                document.getElementById('edit_doc_role').value = trigger.getAttribute('data-role') || 'Docente';
                document.getElementById('edit_doc_registro').value = trigger.getAttribute('data-registro') || '';
                document.getElementById('edit_doc_email').value = trigger.getAttribute('data-email') || '';
                document.getElementById('edit_doc_nombres').value = trigger.getAttribute('data-doc-nombres') || '';
                document.getElementById('edit_doc_apellidos').value = trigger.getAttribute('data-doc-apellidos') || '';
                document.getElementById('edit_doc_dni').value = trigger.getAttribute('data-doc-dni') || '';
                document.getElementById('edit_doc_telefono').value = trigger.getAttribute('data-doc-telefono') || '';
                document.getElementById('edit_doc_especialidad').value = trigger.getAttribute('data-doc-especialidad') || '';
                document.getElementById('edit_doc_fecha_ingreso').value = trigger.getAttribute('data-doc-fecha-ingreso') || '';
                document.getElementById('edit_doc_estado').value = trigger.getAttribute('data-doc-estado') || 'activo';
                document.getElementById('edit_doc_sexo').value = trigger.getAttribute('data-doc-sexo') || '';
                document.getElementById('edit_doc_fecha_nacimiento').value = trigger.getAttribute('data-doc-fecha-nacimiento') || '';
                document.getElementById('edit_doc_direccion').value = trigger.getAttribute('data-doc-direccion') || '';
                document.getElementById('edit_doc_ciudad').value = trigger.getAttribute('data-doc-ciudad') || '';
                document.getElementById('edit_doc_grado_academico').value = trigger.getAttribute('data-doc-grado-academico') || '';
                document.getElementById('edit_doc_tipo_contrato').value = trigger.getAttribute('data-doc-tipo-contrato') || '';

                if (generate) {
                    generate.checked = false;
                    generate.onchange = toggleDocPwd;
                }
                toggleDocPwd();
            });
        }

        const modalEditarSecretaria = document.getElementById('modalEditarSecretaria');
        if (modalEditarSecretaria) {
            modalEditarSecretaria.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const form = document.getElementById('formEditarSecretaria');
                const generate = document.getElementById('edit_sec_generate_password');
                const pwd = document.getElementById('edit_sec_password');
                const pwdC = document.getElementById('edit_sec_password_confirmation');

                const toggleSecPwd = () => {
                    const disable = !!generate?.checked;
                    if (pwd) {
                        pwd.value = '';
                        pwd.disabled = disable;
                    }
                    if (pwdC) {
                        pwdC.value = '';
                        pwdC.disabled = disable;
                    }
                };

                if (form) form.action = trigger.getAttribute('data-edit-url') || form.action;
                document.getElementById('edit_sec_id').value = trigger.getAttribute('data-id') || '';
                document.getElementById('edit_sec_role').value = trigger.getAttribute('data-role') || 'Secretaria';
                document.getElementById('edit_sec_registro').value = trigger.getAttribute('data-registro') || '';
                document.getElementById('edit_sec_name').value = trigger.getAttribute('data-name') || '';
                document.getElementById('edit_sec_email').value = trigger.getAttribute('data-email') || '';
                document.getElementById('edit_sec_dni').value = trigger.getAttribute('data-dni') || '';

                if (generate) {
                    generate.checked = false;
                    generate.onchange = toggleSecPwd;
                }
                toggleSecPwd();
            });
        }
    });

    // Auto-open modal if there were validation errors
    @if ($errors->docente->any())
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('modalDocente')).show();
        });
    @elseif ($errors->secretaria->any())
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('modalSecretaria')).show();
        });
    @endif
</script>
@endpush
