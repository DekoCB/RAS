@extends('layouts.mantis')

@section('title', 'Estudiantes | Secretaria')
@section('page-title', 'Alumnos/Padres')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success jp-incidencia-alert mt-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="card jp-search-card mt-3">
        <div class="card-body">
            <form method="GET" action="{{ route('secretaria.estudiantes.index') }}" id="estudiantesFilterForm">
                <input type="hidden" name="nivel" value="{{ $nivel }}">
                <div class="row g-2 align-items-end">
                    {{-- Búsqueda DNI / nombre --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="search" style="width:12px;height:12px;"></i>
                            Buscar alumno (DNI o nombre)
                        </label>
                        <div class="input-group jp-search-input">
                            <span class="input-group-text"><i data-feather="search"></i></span>
                            <input type="text" name="q" class="form-control"
                                placeholder="Buscar por DNI o nombre" value="{{ request('q') }}">
                        </div>
                    </div>
                    {{-- Grado --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="book" style="width:12px;height:12px;"></i>
                            Nivel / Grado
                        </label>
                        <select name="grado_id" class="form-select" style="border-radius:10px;min-height:44px;"
                                onchange="document.getElementById('estudiantesFilterForm').submit()">
                            <option value="">— Todos los grados —</option>
                            @if($nivel !== '')
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}" @selected(($gradoId ?? null) == $grado->id)>
                                        {{ $grado->nombre }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($grados->groupBy('nivel') as $nivelGrupo => $gradosGrupo)
                                    <optgroup label="{{ $nivelGrupo }}">
                                        @foreach($gradosGrupo as $grado)
                                            <option value="{{ $grado->id }}" @selected(($gradoId ?? null) == $grado->id)>
                                                {{ $grado->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    {{-- Sección --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="grid" style="width:12px;height:12px;"></i>
                            Sección
                        </label>
                        <select name="seccion_id" class="form-select" style="border-radius:10px;min-height:44px;"
                                onchange="document.getElementById('estudiantesFilterForm').submit()">
                            <option value="">— Todas las secciones —</option>
                            @foreach($secciones as $seccion)
                                <option value="{{ $seccion->id }}" @selected(($seccionId ?? null) == $seccion->id)>
                                    {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Botones --}}
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn jp-btn-search">
                            <i data-feather="search" style="width:15px;height:15px;"></i>
                            Buscar
                        </button>
                        @if(request()->hasAny(['q', 'grado_id', 'seccion_id']))
                            <a href="{{ route('secretaria.estudiantes.index', array_filter(['nivel' => $nivel ?: null])) }}"
                               class="btn btn-light rounded-pill px-3 d-flex align-items-center gap-1 small fw-bold">
                                <i data-feather="x" style="width:14px;height:14px;"></i>
                                Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs de nivel --}}
    <div class="jp-nivel-tabs mt-3">
        @php
            $tabs = [
                ''           => ['label' => 'Todos',       'icon' => 'users'],
                'Inicial'    => ['label' => 'Inicial',     'icon' => 'star'],
                'Primaria'   => ['label' => 'Primaria',    'icon' => 'book'],
                'Secundaria' => ['label' => 'Secundaria',  'icon' => 'award'],
            ];
        @endphp
        @foreach ($tabs as $tabNivel => $tab)
            @php
                $isActive = $nivel === $tabNivel;
                $tabParams = array_filter(['nivel' => $tabNivel ?: null, 'q' => request('q') ?: null]);
                $url = route('secretaria.estudiantes.index', $tabParams);
                $count = $tabNivel === '' ? array_sum($conteosPorNivel) : ($conteosPorNivel[$tabNivel] ?? 0);
            @endphp
            <a href="{{ $url }}" class="jp-nivel-tab {{ $isActive ? 'is-active' : '' }}">
                <i data-feather="{{ $tab['icon'] }}"></i>
                {{ $tab['label'] }}
                <span class="jp-nivel-tab-badge">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <div class="card jp-soft-card mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table jp-table jp-students-table mb-0">
                    <thead>
                        <tr>
                            <th>DNI</th>
                            <th>Alumno</th>
                            <th>C&oacute;digo</th>
                            <th>Apoderado principal</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiantes as $estudiante)
                            @php
                                $principal = $estudiante->apoderados?->firstWhere('pivot.principal', true)
                                    ?? $estudiante->apoderados?->first();
                                $codigoEstudiante = trim((string) ($estudiante->codigo ?? ''));
                                if ($codigoEstudiante === '' || $codigoEstudiante === '-') {
                                    $codigoEstudiante = $estudiante->carnet_codigo
                                        ? trim((string) $estudiante->carnet_codigo)
                                        : 'ALU-' . str_pad((string) $estudiante->id, 5, '0', STR_PAD_LEFT);
                                }
                            @endphp
                            <tr>
                                <td>{{ $estudiante->dni ?? '-' }}</td>
                                <td>{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</td>
                                <td>{{ $codigoEstudiante }}</td>
                                <td>
                                    {{ $principal ? $principal->apellidos . ' ' . $principal->nombres : '-' }}
                                </td>
                                <td>
                                    @php
                                        $estadoAlumno = strtolower(trim((string) ($estudiante->estado ?? 'activo')));
                                        $estadoMatricula = strtolower(trim((string) ($estudiante->ultima_matricula_estado ?? '')));

                                        if ($estadoMatricula === 'retirada') {
                                            $estadoAlumno = 'retirado';
                                        }

                                        if ($estadoAlumno === '') {
                                            $estadoAlumno = 'activo';
                                        }

                                        $estadoClass = $estadoAlumno === 'activo' ? 'is-activo' : 'is-inactivo';
                                    @endphp
                                    <span class="jp-status-pill {{ $estadoClass }}">
                                        {{ strtoupper($estadoAlumno) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="jp-action-group">
                                        <button type="button" class="jp-action-icon is-view jp-estudiante-info"
                                            data-bs-toggle="modal" data-bs-target="#estudianteInfoModal"
                                            data-url="{{ route('secretaria.estudiantes.info', $estudiante) }}"
                                            title="Ver detalle" aria-label="Ver detalle">
                                            <i data-feather="eye"></i>
                                        </button>
                                        <button type="button" class="jp-action-icon is-view jp-estudiante-carnet"
                                            data-bs-toggle="modal" data-bs-target="#estudianteCarnetModal"
                                            data-url="{{ route('secretaria.estudiantes.carnet', $estudiante) }}"
                                            title="Ver carnet" aria-label="Ver carnet">
                                            <i data-feather="credit-card"></i>
                                        </button>
                                        <button type="button" class="jp-action-icon is-print jp-estudiante-pagos"
                                            data-bs-toggle="modal" data-bs-target="#estudiantePagosModal"
                                            data-url="{{ route('secretaria.estudiantes.pagos', $estudiante) }}"
                                            title="Ver pagos" aria-label="Ver pagos">
                                            <i data-feather="dollar-sign"></i>
                                        </button>
                                        <a href="{{ route('secretaria.estudiantes.edit', $estudiante) }}"
                                            class="jp-action-icon is-edit" title="Editar" aria-label="Editar">
                                            <i data-feather="edit-2"></i>
                                        </a>
                                        <button type="button"
                                            class="jp-action-icon is-delete btn-eliminar-estudiante"
                                            title="Eliminar" aria-label="Eliminar"
                                            data-url="{{ route('secretaria.estudiantes.destroy', $estudiante) }}"
                                            data-nombre="{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay estudiantes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $estudiantes->links() }}
        </div>
    </div>

    <div class="modal fade jp-info-modal" id="estudianteInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="user"></i>
                        Detalle del estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="jp-info-loading">Cargando detalle...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade jp-info-modal" id="estudianteCarnetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="credit-card"></i>
                        Carnet del estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="jp-info-loading">Cargando carnet...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade jp-info-modal" id="estudiantePagosModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="dollar-sign"></i>
                        Pagos del estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="jp-info-loading">Cargando pagos...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ url('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const infoModal = document.getElementById('estudianteInfoModal');
        if (infoModal) {
            infoModal.addEventListener('show.bs.modal', async (event) => {
                const button = event.relatedTarget;
                const url = button?.getAttribute('data-url');
                const body = infoModal.querySelector('.modal-body');
                if (!url || !body) return;

                body.innerHTML = '<div class="jp-info-loading">Cargando detalle...</div>';

                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) throw new Error();
                    body.innerHTML = await response.text();
                    if (window.feather) {
                        window.feather.replace();
                    }
                } catch (error) {
                    body.innerHTML = '<div class="text-danger">No se pudo cargar el detalle.</div>';
                }
            });
        }

        const carnetModal = document.getElementById('estudianteCarnetModal');
        if (carnetModal) {
            carnetModal.addEventListener('show.bs.modal', async (event) => {
                const button = event.relatedTarget;
                const url = button?.getAttribute('data-url');
                const body = carnetModal.querySelector('.modal-body');
                if (!url || !body) return;

                body.innerHTML = '<div class="jp-info-loading">Cargando carnet...</div>';

                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) throw new Error();
                    body.innerHTML = await response.text();
                    if (window.feather) {
                        window.feather.replace();
                    }
                } catch (error) {
                    body.innerHTML = '<div class="text-danger">No se pudo cargar el carnet.</div>';
                }
            });
        }

        const pagosModal = document.getElementById('estudiantePagosModal');
        if (pagosModal) {
            pagosModal.addEventListener('show.bs.modal', async (event) => {
                const button = event.relatedTarget;
                const url = button?.getAttribute('data-url');
                const body = pagosModal.querySelector('.modal-body');
                if (!url || !body) return;

                body.innerHTML = '<div class="jp-info-loading">Cargando pagos...</div>';

                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) throw new Error();
                    body.innerHTML = await response.text();
                    if (window.feather) {
                        window.feather.replace();
                    }
                } catch (error) {
                    body.innerHTML = '<div class="text-danger">No se pudo cargar los pagos.</div>';
                }
            });
        }
    });

    /* ── Eliminar estudiante con SweetAlert ── */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-eliminar-estudiante');
        if (!btn) return;

        const url    = btn.dataset.url;
        const nombre = btn.dataset.nombre;

        const result = await Swal.fire({
            title: '¿Eliminar estudiante?',
            html: `Se eliminará a <strong>${nombre}</strong>.<br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1c297e',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        });

        if (!result.isConfirmed) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res   = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (res.status === 403) {
                Swal.fire({
                    title: 'Sin permiso',
                    text: 'No tienes permiso para eliminar estudiantes.',
                    icon: 'error',
                    confirmButtonColor: '#1c297e',
                });
                return;
            }

            if (res.ok || res.redirected) {
                await Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El estudiante fue eliminado correctamente.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                });
                window.location.reload();
                return;
            }

            throw new Error();
        } catch {
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al intentar eliminar.',
                icon: 'error',
                confirmButtonColor: '#1c297e',
            });
        }
    });
</script>
@endpush
