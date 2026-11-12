@extends('layouts.mantis')

@section('title', 'Docentes | Secretaria')
@section('page-title', 'Docentes')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            Revisa los datos del formulario e intenta nuevamente.
        </div>
    @endif

    @php
        $queryBase = array_filter(
            [
                'q' => $busqueda,
                'dni' => $dni,
            ],
            static fn($value) => $value !== null && $value !== '',
        );
    @endphp

    <div class="jp-product-tabs">
        <span class="jp-product-tab is-active">
            <i data-feather="users"></i>
            Docentes
        </span>
    </div>

    <div class="jp-docente-filter mt-3">
        <div class="jp-docente-filter__top">
            <div class="jp-product-filter-label">Filtrar por:</div>
            <div class="jp-product-chip-group">
                <a class="jp-product-chip {{ $estado ? '' : 'is-active' }}"
                    href="{{ route('secretaria.usuarios.index', $queryBase) }}">
                    Todos
                </a>
                <a class="jp-product-chip {{ $estado === 'activo' ? 'is-active' : '' }}"
                    href="{{ route('secretaria.usuarios.index', array_merge($queryBase, ['estado' => 'activo'])) }}">
                    Activos
                </a>
                <a class="jp-product-chip {{ $estado === 'inactivo' ? 'is-active' : '' }}"
                    href="{{ route('secretaria.usuarios.index', array_merge($queryBase, ['estado' => 'inactivo'])) }}">
                    Inactivos
                </a>
            </div>
            <span class="badge bg-light text-dark">Total: {{ $docentes->total() }}</span>
        </div>
        <div class="jp-docente-filter__bottom">
            <form method="GET" action="{{ route('secretaria.usuarios.index') }}" class="jp-docente-search-form">
                <input type="hidden" name="estado" value="{{ $estado }}">
                <div class="jp-docente-search-inputs">
                    <div class="input-group jp-docente-search-main">
                        <span class="input-group-text"><i data-feather="search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Buscar docente..."
                            value="{{ $busqueda }}">
                    </div>
                    <input type="text" name="dni" class="form-control jp-docente-search-dni" placeholder="DNI"
                        value="{{ $dni }}">
                </div>
                <button class="btn jp-docente-search-btn" type="submit" aria-label="Buscar">
                    <i data-feather="search"></i>
                </button>
                <button type="button" class="btn jp-btn-incidencia jp-docente-new-btn" data-bs-toggle="modal"
                    data-bs-target="#modal-docente">
                    <i data-feather="plus"></i>
                    Nuevo docente
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card jp-soft-card mt-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Docentes registrados</h5>
                    <span class="badge jp-badge-outline">Actualizado</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover jp-table">
                            <thead>
                                <tr>
                                    <th>C&oacute;digo</th>
                                    <th>Docente</th>
                                    <th>DNI</th>
                                    <th>Email</th>
                                    <th>Especialidad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($docentes as $docente)
                                    <tr>
                                        <td>{{ $docente->codigo ?? '-' }}</td>
                                        <td>{{ $docente->nombres }} {{ $docente->apellidos }}</td>
                                        <td>{{ $docente->dni ?? '-' }}</td>
                                        <td>{{ $docente->user?->email ?? '-' }}</td>
                                        <td>{{ $docente->especialidad ?? '-' }}</td>

                                        @php
                                            $estadoDocente = strtolower($docente->estado ?? 'activo');
                                            $estadoClase = $estadoDocente === 'activo' ? 'is-activo' : 'is-inactivo';
                                        @endphp

                                        <td>
                                            <span class="jp-status-pill {{ $estadoClase }}">
                                                {{ ucfirst($estadoDocente) }}
                                            </span>
                                        </td>

                                        {{-- ✅ ACCIONES (Perfil + Carnet modal) --}}
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                {{-- Ver perfil --}}
                                                <button type="button" class="jp-action-icon is-view jp-docente-info"
                                                    data-bs-toggle="modal" data-bs-target="#docenteInfoModal"
                                                    data-route="{{ route('secretaria.usuarios.credenciales', $docente) }}"
                                                    data-codigo="{{ $docente->codigo ?? '-' }}"
                                                    data-nombres="{{ e($docente->nombres ?? '') }}"
                                                    data-apellidos="{{ e($docente->apellidos ?? '') }}"
                                                    data-dni="{{ $docente->dni ?? '-' }}"
                                                    data-email="{{ $docente->user?->email ?? '' }}"
                                                    data-sexo="{{ $docente->sexo ?? '-' }}"
                                                    data-fecha_nacimiento="{{ $docente->fecha_nacimiento ?? '-' }}"
                                                    data-telefono="{{ $docente->telefono ?? '-' }}"
                                                    data-ciudad="{{ e($docente->ciudad ?? '-') }}"
                                                    data-direccion="{{ e($docente->direccion ?? '-') }}"
                                                    data-especialidad="{{ e($docente->especialidad ?? '-') }}"
                                                    data-grado_academico="{{ e($docente->grado_academico ?? '-') }}"
                                                    data-tipo_contrato="{{ e($docente->tipo_contrato ?? '-') }}"
                                                    data-fecha_ingreso="{{ $docente->fecha_ingreso ?? '-' }}"
                                                    title="Ver perfil" aria-label="Ver perfil">
                                                    <i data-feather="eye"></i>
                                                </button>

                                                {{-- Ver carnet (modal) --}}
                                                <button type="button" class="jp-action-icon js-carnet-docente"
                                                    data-carnet-url="{{ route('secretaria.usuarios.carnet.modal', $docente) }}"
                                                    title="Ver carnet" aria-label="Ver carnet">
                                                    <i data-feather="credit-card"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No hay docentes registrados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 jp-table-pagination">
                        {{ $docentes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ MODAL CARNET (UNA SOLA VEZ, fuera del foreach) --}}
    <div class="modal fade" id="carnetDocenteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                <div class="modal-header">
                    <h5 class="modal-title">Carnet del docente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="carnetDocenteBody" class="py-2 text-center">
                        <div class="text-muted">Selecciona un docente...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL registrar docente --}}
    <div class="modal fade jp-product-modal" id="modal-docente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar docente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form method="POST" action="{{ route('secretaria.usuarios.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input class="form-control" type="text" name="nombres" value="{{ old('nombres') }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input class="form-control" type="text" name="apellidos"
                                    value="{{ old('apellidos') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input class="form-control" type="text" name="dni" value="{{ old('dni') }}"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">C&oacute;digo</label>
                                <input class="form-control" type="text" value="{{ $nextCodigo ?? 'DOC-00001' }}"
                                    readonly>
                                <small class="text-muted">Se asigna autom&aacute;ticamente al registrar.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sexo</label>
                                <select class="form-select" name="sexo">
                                    <option value="">Seleccionar</option>
                                    <option value="Masculino" @selected(old('sexo') === 'Masculino')>Masculino</option>
                                    <option value="Femenino" @selected(old('sexo') === 'Femenino')>Femenino</option>
                                    <option value="Otro" @selected(old('sexo') === 'Otro')>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input class="form-control" type="date" name="fecha_nacimiento"
                                    value="{{ old('fecha_nacimiento') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tel&eacute;fono</label>
                                <input class="form-control" type="text" name="telefono"
                                    value="{{ old('telefono') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correo</label>
                                <input class="form-control" type="email" name="email" value="{{ old('email') }}"
                                    required>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-light border mb-0">
                                    <strong>Credenciales:</strong>
                                    Se crear&aacute; un usuario para el docente con usuario y contrase&ntilde;a igual a su
                                    <strong>DNI</strong>.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input class="form-control" type="text" name="ciudad" value="{{ old('ciudad') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Direcci&oacute;n</label>
                                <input class="form-control" type="text" name="direccion"
                                    value="{{ old('direccion') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Especialidad</label>
                                <input class="form-control" type="text" name="especialidad"
                                    value="{{ old('especialidad') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Grado acad&eacute;mico</label>
                                <input class="form-control" type="text" name="grado_academico"
                                    value="{{ old('grado_academico') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de contrato</label>
                                <input class="form-control" type="text" name="tipo_contrato"
                                    value="{{ old('tipo_contrato') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de ingreso</label>
                                <input class="form-control" type="date" name="fecha_ingreso"
                                    value="{{ old('fecha_ingreso') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn jp-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn jp-btn-incidencia">Registrar docente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL perfil docente --}}
    <div class="modal fade jp-info-modal" id="docenteInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2 text-white">
                        <i data-feather="user"></i>
                        Perfil del docente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="jp-info-wrap">
                        <div class="jp-info-hero">
                            <div class="jp-info-avatar">
                                <i data-feather="user"></i>
                            </div>
                            <div>
                                <div class="jp-info-name" id="infoNombreCompleto">-</div>
                                <div class="jp-info-sub">
                                    <i data-feather="credit-card"></i>
                                    DNI: <span id="infoHeroDni">-</span>
                                </div>
                                <div class="jp-info-sub">
                                    <i data-feather="hash"></i>
                                    C&oacute;digo: <span id="infoHeroCodigo">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="jp-info-section">
                            <div class="jp-info-section-title">
                                <i data-feather="user-check"></i>
                                Datos del docente
                            </div>
                            <div class="jp-info-grid">
                                <div class="jp-info-item"><span>Nombres</span><strong id="infoNombres">-</strong></div>
                                <div class="jp-info-item"><span>Apellidos</span><strong id="infoApellidos">-</strong>
                                </div>
                                <div class="jp-info-item"><span>Sexo</span><strong id="infoSexo">-</strong></div>
                                <div class="jp-info-item"><span>Fecha de nacimiento</span><strong
                                        id="infoNacimiento">-</strong></div>
                                <div class="jp-info-item"><span>Tel&eacute;fono</span><strong id="infoTelefono">-</strong>
                                </div>
                                <div class="jp-info-item"><span>Especialidad</span><strong
                                        id="infoEspecialidad">-</strong></div>
                                <div class="jp-info-item"><span>Ciudad</span><strong id="infoCiudad">-</strong></div>
                                <div class="jp-info-item"><span>Direcci&oacute;n</span><strong
                                        id="infoDireccion">-</strong></div>
                                <div class="jp-info-item"><span>Grado acad&eacute;mico</span><strong
                                        id="infoGrado">-</strong></div>
                                <div class="jp-info-item"><span>Tipo de contrato</span><strong
                                        id="infoContrato">-</strong></div>
                                <div class="jp-info-item"><span>Fecha de ingreso</span><strong id="infoIngreso">-</strong>
                                </div>
                            </div>
                        </div>

                        <div class="jp-info-section">
                            <div class="jp-info-section-title">
                                <i data-feather="lock"></i>
                                Credenciales de acceso
                            </div>
                            <form id="docenteCredForm" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="jp-info-grid">
                                    <div class="jp-info-item">
                                        <span>Usuario (correo)</span>
                                        <input type="email" class="form-control" name="email" id="infoEmail"
                                            required>
                                    </div>
                                    <div class="jp-info-item">
                                        <span>DNI de acceso</span>
                                        <input type="text" class="form-control" id="infoDniCred" readonly>
                                    </div>
                                </div>
                                <div class="alert alert-light border mt-3 mb-0">
                                    La contrase&ntilde;a se sincroniza autom&aacute;ticamente con el DNI del docente.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn jp-btn-outline" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="docenteCredForm" class="btn jp-btn-incidencia">
                        Guardar credenciales
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalEl = document.getElementById('modal-docente');
                if (!modalEl || typeof bootstrap === 'undefined') return;
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('docenteInfoModal');
            const form = document.getElementById('docenteCredForm');
            if (!modal || !form) return;

            modal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (!button) return;

                const get = (key, fallback = '-') => button.getAttribute(`data-${key}`) || fallback;

                const fields = {
                    infoNombres: 'nombres',
                    infoApellidos: 'apellidos',
                    infoSexo: 'sexo',
                    infoNacimiento: 'fecha_nacimiento',
                    infoTelefono: 'telefono',
                    infoEspecialidad: 'especialidad',
                    infoCiudad: 'ciudad',
                    infoDireccion: 'direccion',
                    infoGrado: 'grado_academico',
                    infoContrato: 'tipo_contrato',
                    infoIngreso: 'fecha_ingreso',
                };

                Object.keys(fields).forEach((id) => {
                    const target = document.getElementById(id);
                    if (!target) return;
                    target.textContent = get(fields[id]);
                });

                const nombreCompleto = `${get('nombres', '')} ${get('apellidos', '')}`.trim() || '-';
                const nombreEl = document.getElementById('infoNombreCompleto');
                if (nombreEl) nombreEl.textContent = nombreCompleto;

                const heroDni = document.getElementById('infoHeroDni');
                if (heroDni) heroDni.textContent = get('dni');

                const heroCodigo = document.getElementById('infoHeroCodigo');
                if (heroCodigo) heroCodigo.textContent = get('codigo');

                const emailInput = document.getElementById('infoEmail');
                if (emailInput) emailInput.value = get('email', '');

                const dniInput = document.getElementById('infoDniCred');
                if (dniInput) dniInput.value = get('dni', '');

                const route = button.getAttribute('data-route');
                if (route) form.setAttribute('action', route);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('carnetDocenteModal');
            const bodyEl = document.getElementById('carnetDocenteBody');
            if (!modalEl || !bodyEl) return;

            const bsModal = new bootstrap.Modal(modalEl);

            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('.js-carnet-docente');
                if (!btn) return;

                const url = btn.getAttribute('data-carnet-url');
                if (!url) return;

                bodyEl.innerHTML = '<div class="text-muted">Cargando...</div>';
                bsModal.show();

                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) throw data;

                    bodyEl.innerHTML = data.html;
                    if (window.feather) feather.replace();

                } catch (err) {
                    bodyEl.innerHTML = `<div class="alert alert-danger mb-0">
                        No se pudo cargar el carnet. ${err?.message ?? ''}
                    </div>`;
                }
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                bodyEl.innerHTML = '';
            });
        });
    </script>
@endpush
