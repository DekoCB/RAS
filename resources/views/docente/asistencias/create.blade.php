@extends('layouts.mantis')

@section('title', 'Registrar asistencia | Docente')
@section('page-title', 'Registrar asistencia')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-count-card mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-pill">Turno Ma&ntilde;ana</div>
                    <h5 class="mb-1">Registro de asistencia</h5>
                    <p class="mb-0 opacity-75">
                        Selecciona la secci&oacute;n y fecha para registrar la asistencia. No se permiten modificaciones.
                    </p>
                </div>
            </div>
            <div class="row mt-3 g-3">
                <div class="col-md-3">
                    <div class="jp-metrics-card">
                        <span>Inicial</span>
                        <div class="num">07:50 - 12:50</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="jp-metrics-card">
                        <span>Primaria</span>
                        <div class="num">07:50 - 13:10</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="jp-metrics-card">
                        <span>Secundaria</span>
                        <div class="num">07:40 - 14:30</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="jp-metrics-card">
                        <span>Tolerancia</span>
                        <div class="num">10 minutos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Revisa la informaci&oacute;n:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (!empty($mensajeFecha))
        <div class="alert alert-info mt-3">
            {{ $mensajeFecha }}
        </div>
    @endif

    <div class="card jp-search-card mt-3">
        <div class="card-body">
            <form method="GET" action="{{ route('docente.asistencias.create') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label">Secci&oacute;n</label>
                        <select name="seccion_id" class="form-select">
                            <option value="">Selecciona una secci&oacute;n</option>
                            @foreach ($secciones as $seccion)
                                <option value="{{ $seccion->id }}" @selected((string) old('seccion_id', request('seccion_id')) === (string) $seccion->id)>
                                    {{ $seccion->grado?->nombre ?? '-' }} - {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', $fechaSeleccionada) }}">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">A&ntilde;o escolar</label>
                        <input type="text" class="form-control" value="{{ $anio?->nombre ?? date('Y') }}" disabled>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Periodo</label>
                        <input type="text" class="form-control"
                            value="{{ $periodoAcademico?->codigo ?? '-' }}{{ $periodoAcademico?->nombre ? ' - ' . $periodoAcademico->nombre : '' }}"
                            disabled>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button class="btn jp-btn-search" type="submit">Cargar estudiantes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (request('seccion_id') && $estudiantes->isEmpty())
        <div class="alert alert-warning mt-3">
            No hay estudiantes matriculados en la secci&oacute;n seleccionada.
        </div>
    @endif

    @if ($estudiantes->isNotEmpty())
        <form method="POST" action="{{ route('docente.asistencias.store') }}" id="asistencia-form">
            @csrf
            <input type="hidden" name="anio_escolar_id" value="{{ $anio?->id }}">
            <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">
            <input type="hidden" name="fecha" value="{{ old('fecha', $fechaSeleccionada) }}">

            <div class="card jp-soft-card mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h6 class="mb-1">Estudiantes de {{ $seccion->grado->nombre }} - {{ $seccion->nombre }}</h6>
                            <p class="text-muted mb-0">Haz clic en el cÃ­rculo para cambiar el estado (Presente -> Ausente ->
                                Tarde).</p>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="d-flex align-items-center gap-3 small">
                                <span class="d-flex align-items-center gap-1"><i class="text-success"
                                        data-feather="check-circle"></i> Presente</span>
                                <span class="d-flex align-items-center gap-1"><i class="text-danger"
                                        data-feather="x-circle"></i> Ausente</span>
                                <span class="d-flex align-items-center gap-1"><i class="text-warning" data-feather="clock"></i>
                                    Tardanza</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table jp-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Alumno</th>
                                    <th>DNI</th>
                                    <th class="text-center" style="width: 150px;">Asistencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estudiantes as $index => $estudiante)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</td>
                                        <td>{{ $estudiante->dni ?? '-' }}</td>
                                        <td class="text-center">
                                            <input type="hidden" name="asistencias[{{ $index }}][estudiante_id]"
                                                value="{{ $estudiante->id }}">
                                            <input type="hidden" name="asistencias[{{ $index }}][estado]" id="estado-{{ $index }}"
                                                value="presente">

                                            <button type="button" class="btn btn-icon rounded-circle asistencia-toggle"
                                                data-index="{{ $index }}" data-state="presente"
                                                style="width: 42px; height: 42px; transition: all 0.2s;">
                                                <i data-feather="check"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn jp-btn-incidencia" type="submit">
                            <i data-feather="save"></i>
                            Guardar Asistencia
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar iconos
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            const states = ['presente', 'ausente', 'tarde'];

            // FunciÃ³n para actualizar visualmente el botÃ³n
            function updateButtonVisuals(btn, state) {
                // Limpiar clases previas
                btn.classList.remove('btn-success', 'btn-danger', 'btn-warning', 'text-white');

                // Icono por defecto
                let iconName = 'check';

                if (state === 'presente') {
                    btn.classList.add('btn-success', 'text-white');
                    iconName = 'check';
                } else if (state === 'ausente') {
                    btn.classList.add('btn-danger', 'text-white');
                    iconName = 'x';
                } else if (state === 'tarde') {
                    btn.classList.add('btn-warning', 'text-dark');
                    iconName = 'clock';
                }

                // Actualizar icono feather
                btn.innerHTML = `<i data-feather="${iconName}"></i>`;
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }

            // Inicializar todos los botones en 'presente' (verde)
            document.querySelectorAll('.asistencia-toggle').forEach(btn => {
                updateButtonVisuals(btn, 'presente');

                btn.addEventListener('click', function () {
                    const index = this.dataset.index;
                    const currentState = this.dataset.state;

                    // Calcular siguiente estado
                    let nextIndex = (states.indexOf(currentState) + 1) % states.length;
                    let nextState = states[nextIndex];

                    // Actualizar datos
                    this.dataset.state = nextState;
                    document.getElementById(`estado-${index}`).value = nextState;

                    // Actualizar visuales
                    updateButtonVisuals(this, nextState);
                });
            });
        });
    </script>
@endsection
