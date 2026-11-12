@extends('layouts.mantis')

@section('title', 'Expediente de academia | R.A.S')
@section('page-title', 'Expediente de academia')
@section('body-class', 'role-superadmin tema-academia')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-academia.css') }}?v={{ filemtime(public_path('css/tema-academia.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo', [
        'contexto' => $alumno->nombreCompleto() . ' - ' . ($alumno->area ?: 'Sin area') . ' - Pension S/ ' . number_format($alumno->pension, 2),
        'acciones' => '<a href="' . route('academia.index') . '" class="mod-btn-ghost">Volver a la academia</a>',
    ])

    <div class="mod-stats">
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $totales['presente'] ?? 0 }}</div>
            <div class="mod-stat__label">Asistencias</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $totales['tardanza'] ?? 0 }}</div>
            <div class="mod-stat__label">Tardanzas</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">{{ $totales['falta'] ?? 0 }}</div>
            <div class="mod-stat__label">Faltas</div>
        </div>
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $alumno->evaluaciones->count() }}</div>
            <div class="mod-stat__label">Evaluaciones</div>
        </div>
    </div>

    @can('academia.gestionar')
        <x-mod-modal id="registrar-asistencia" titulo="Registrar asistencia"
                     boton="Registrar asistencia" ancho="modal-md">
            <form method="POST" action="{{ route('academia.asistencia', $alumno) }}" class="mod-form">
                @csrf
                <label for="fecha_a">Fecha</label>
                <input id="fecha_a" type="date" name="fecha" class="form-control mb-2"
                       value="{{ now()->toDateString() }}" required>
                <label for="estado_a">Estado</label>
                <select id="estado_a" name="estado" class="form-select mb-2" required>
                    @foreach ($estados as $clave => $etiqueta)
                        <option value="{{ $clave }}">{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <label for="obs_a">Observacion</label>
                <input id="obs_a" type="text" name="observacion" class="form-control" maxlength="200">
                <div class="mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar</button>
                </div>
            </form>
        </x-mod-modal>

        <x-mod-modal id="registrar-evaluacion" titulo="Registrar evaluacion"
                     boton="Registrar evaluacion">
            <form method="POST" action="{{ route('academia.evaluacion', $alumno) }}" class="mod-form">
                @csrf
                <label for="titulo_e">Titulo</label>
                <input id="titulo_e" type="text" name="titulo" class="form-control mb-2" required maxlength="180">
                <label for="curso_e">Curso</label>
                <input id="curso_e" type="text" name="curso" class="form-control mb-2">
                <div class="row g-2">
                    <div class="col-4">
                        <label for="fecha_e">Fecha</label>
                        <input id="fecha_e" type="date" name="fecha" class="form-control"
                               value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-4">
                        <label for="puntaje_e">Puntaje</label>
                        <input id="puntaje_e" type="number" step="0.01" min="0" name="puntaje" class="form-control">
                    </div>
                    <div class="col-4">
                        <label for="max_e">Max.</label>
                        <input id="max_e" type="number" step="0.01" min="1" name="puntaje_maximo"
                               class="form-control" value="20" required>
                    </div>
                </div>
                <div class="mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar</button>
                </div>
            </form>
        </x-mod-modal>

        <x-mod-modal id="entregar-material" titulo="Entregar material"
                     boton="Entregar material">
            <form method="POST" action="{{ route('academia.material', $alumno) }}" class="mod-form">
                @csrf
                <label for="titulo_m">Material</label>
                <input id="titulo_m" type="text" name="titulo" class="form-control mb-2" required maxlength="180">
                <label for="curso_m">Curso</label>
                <input id="curso_m" type="text" name="curso" class="form-control mb-2">
                <div class="row g-2">
                    <div class="col-8">
                        <label for="fecha_m">Fecha de entrega</label>
                        <input id="fecha_m" type="date" name="fecha_entrega" class="form-control"
                               value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-4">
                        <label for="cant_m">Cantidad</label>
                        <input id="cant_m" type="number" name="cantidad" class="form-control"
                               min="1" value="1" required>
                    </div>
                </div>
                <div class="mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Ultimas asistencias</h5>
            <small>{{ $alumno->asistencias->count() }} registro(s) en total</small>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead><tr><th>Fecha</th><th>Estado</th><th>Observacion</th></tr></thead>
                <tbody>
                    @forelse ($asistencias as $a)
                        <tr>
                            <td>{{ $a->fecha->format('d/m/Y') }}</td>
                            <td>
                                <span class="mod-chip {{ $a->estado === 'presente' ? 'mod-chip--ok' : ($a->estado === 'falta' ? 'mod-chip--pend' : 'mod-chip--neutral') }}">
                                    {{ $estados[$a->estado] ?? $a->estado }}
                                </span>
                            </td>
                            <td>{{ $a->observacion ?: '--' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="mod-empty">Sin asistencias registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="mod-card">
                <div class="mod-card__head"><h5>Evaluaciones</h5></div>
                <div class="mod-table-wrap">
                    <table class="mod-table">
                        <thead><tr><th>Fecha</th><th>Titulo</th><th>Curso</th><th>Puntaje</th></tr></thead>
                        <tbody>
                            @forelse ($alumno->evaluaciones->sortByDesc('fecha') as $e)
                                <tr>
                                    <td>{{ $e->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $e->titulo }}</td>
                                    <td>{{ $e->curso ?: '--' }}</td>
                                    <td><strong>{{ $e->puntaje !== null ? $e->puntaje . ' / ' . $e->puntaje_maximo : '--' }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="mod-empty">Sin evaluaciones.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="mod-card">
                <div class="mod-card__head"><h5>Materiales entregados</h5></div>
                <div class="mod-table-wrap">
                    <table class="mod-table">
                        <thead><tr><th>Fecha</th><th>Material</th><th>Curso</th><th>Cant.</th></tr></thead>
                        <tbody>
                            @forelse ($alumno->materiales->sortByDesc('fecha_entrega') as $m)
                                <tr>
                                    <td>{{ $m->fecha_entrega->format('d/m/Y') }}</td>
                                    <td>{{ $m->titulo }}</td>
                                    <td>{{ $m->curso ?: '--' }}</td>
                                    <td>{{ $m->cantidad }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="mod-empty">Sin materiales entregados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
