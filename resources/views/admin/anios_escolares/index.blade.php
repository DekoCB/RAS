@extends('layouts.mantis')

@section('title', 'Año escolar | SuperAdmin')
@section('page-title', 'Año escolar')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-soft-card jp-role-hero mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-role-pill">
                        <i data-feather="calendar"></i>
                        Año escolar
                    </div>
                    <h5 class="mb-1 jp-role-title">Gestión del año escolar</h5>
                    <p class="mb-0 text-muted jp-role-sub">
                        Administra los periodos academicos y define los bimestres del ciclo.
                    </p>
                </div>
                <button class="btn jp-btn-search" data-bs-toggle="modal" data-bs-target="#modalAnio">
                    Nuevo año
                </button>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table jp-table jp-usuarios-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Periodos</th>
                            <th>Bimestres</th>
                            <th>Estado</th>
                            <th>Pre-inscripcion web</th>
                            <th class="text-end">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($anios as $anio)
                            <tr>
                                <td>{{ $anio->nombre }}</td>
                                <td>{{ $anio->fecha_inicio }}</td>
                                <td>{{ $anio->fecha_fin }}</td>
                                <td>
                                    @if ($anio->periodosAcademicos->isEmpty())
                                        <span class="text-muted">Sin periodos</span>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($anio->periodosAcademicos->sortBy('orden') as $periodo)
                                                <li class="small text-muted">
                                                    {{ $periodo->codigo }} - {{ $periodo->nombre }}
                                                    @if ($periodo->fecha_inicio && $periodo->fecha_fin)
                                                        ({{ $periodo->fecha_inicio }} - {{ $periodo->fecha_fin }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    @if ($anio->bimestres->isEmpty())
                                        <span class="text-muted">Sin bimestres</span>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($anio->bimestres as $bimestre)
                                                <li class="small text-muted">
                                                    {{ $bimestre->nombre }}: {{ $bimestre->fecha_inicio }} - {{ $bimestre->fecha_fin }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $anio->activo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $anio->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST"
                                        action="{{ route('admin-sistema.anios-escolares.admision', $anio) }}"
                                        class="d-flex flex-column gap-1" style="min-width:210px">
                                        @csrf
                                        <input type="hidden" name="admision_abierta"
                                            value="{{ $anio->admision_abierta ? 0 : 1 }}">

                                        @if ($anio->admision_abierta)
                                            <span class="badge bg-success align-self-start">Abierta al publico</span>
                                            @if ($anio->admision_cierre)
                                                <span class="small text-muted">
                                                    Cierra el {{ $anio->admision_cierre->format('d/m/Y') }}
                                                </span>
                                            @endif
                                            <button type="submit" class="btn btn-sm btn-outline-danger mt-1">
                                                Cerrar
                                            </button>
                                        @else
                                            <input type="date" name="admision_cierre" class="form-control form-control-sm"
                                                min="{{ now()->toDateString() }}" placeholder="Cierre (opcional)">
                                            <input type="text" name="admision_mensaje" class="form-control form-control-sm"
                                                maxlength="255" placeholder="Mensaje para la web (opcional)">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                Abrir pre-inscripcion
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="text-end">
                                    @if (!$anio->activo)
                                        <form method="POST"
                                            action="{{ route('admin-sistema.anios-escolares.activar', $anio) }}"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                Activar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-success small">Actual</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No hay anos escolares registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAnio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content jp-ano-modal">
                <div class="modal-header jp-ano-modal__header">
                    <h5 class="modal-title">Nuevo año escolar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin-sistema.anios-escolares.store') }}">
                    @csrf
                    <div class="modal-body jp-ano-modal__body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" placeholder="2026" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4 jp-ano-divider">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 jp-ano-section-title">Periodos acad&eacute;micos</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnPresetPeriodos">
                                Autocompletar periodos 0/1/2
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="jp-ano-section-label">Periodo 0 (Vacacional)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Inicio</label>
                                <input type="date" name="periodo_0_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fin</label>
                                <input type="date" name="periodo_0_fin" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <div class="jp-ano-section-label">Periodo 1</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Inicio</label>
                                <input type="date" name="periodo_1_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fin</label>
                                <input type="date" name="periodo_1_fin" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <div class="jp-ano-section-label">Periodo 2</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Inicio</label>
                                <input type="date" name="periodo_2_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fin</label>
                                <input type="date" name="periodo_2_fin" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4 jp-ano-divider">
                        <h6 class="mb-3 jp-ano-section-title">Bimestres</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 1 inicio</label>
                                <input type="date" name="bimestre_1_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 1 fin</label>
                                <input type="date" name="bimestre_1_fin" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 2 inicio</label>
                                <input type="date" name="bimestre_2_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 2 fin</label>
                                <input type="date" name="bimestre_2_fin" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 3 inicio</label>
                                <input type="date" name="bimestre_3_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 3 fin</label>
                                <input type="date" name="bimestre_3_fin" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 4 inicio</label>
                                <input type="date" name="bimestre_4_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bimestre 4 fin</label>
                                <input type="date" name="bimestre_4_fin" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer jp-ano-modal__footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn jp-btn-search">Guardar ano</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const get = (name) => document.querySelector(`[name=\"${name}\"]`);
    const btnPreset = document.getElementById('btnPresetPeriodos');

    const pad2 = (n) => String(n).padStart(2, '0');
    const dateStr = (y, m, d) => `${y}-${pad2(m)}-${pad2(d)}`;
    const endOfMonth = (y, m) => new Date(y, m, 0).getDate();

    const resolverAnio = () => {
        const txt = (get('nombre')?.value || '').trim();
        const match = txt.match(/(\\d{4})/);
        if (match) {
            const anio = Number(match[1]);
            if (Number.isInteger(anio) && anio >= 2000) return anio;
        }
        return new Date().getFullYear();
    };

    const aplicarPreset = (forzar = false) => {
        const anio = resolverAnio();
        const campos = {
            fecha_inicio: dateStr(anio, 1, 1),
            fecha_fin: dateStr(anio, 12, 31),
            periodo_0_inicio: dateStr(anio, 1, 1),
            periodo_0_fin: dateStr(anio, 2, endOfMonth(anio, 2)),
            periodo_1_inicio: dateStr(anio, 3, 1),
            periodo_1_fin: dateStr(anio, 6, 30),
            periodo_2_inicio: dateStr(anio, 8, 1),
            periodo_2_fin: dateStr(anio, 12, 31),
            bimestre_1_inicio: dateStr(anio, 3, 1),
            bimestre_1_fin: dateStr(anio, 5, 31),
            bimestre_2_inicio: dateStr(anio, 6, 1),
            bimestre_2_fin: dateStr(anio, 8, 31),
            bimestre_3_inicio: dateStr(anio, 9, 1),
            bimestre_3_fin: dateStr(anio, 10, 31),
            bimestre_4_inicio: dateStr(anio, 11, 1),
            bimestre_4_fin: dateStr(anio, 12, 31),
        };

        Object.entries(campos).forEach(([name, value]) => {
            const input = get(name);
            if (!input) return;
            if (forzar || !input.value) {
                input.value = value;
            }
        });
    };

    btnPreset?.addEventListener('click', () => aplicarPreset(true));
    get('nombre')?.addEventListener('blur', () => aplicarPreset(false));
});
</script>
@endpush

