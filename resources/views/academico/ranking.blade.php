@extends('layouts.mantis')

@section('title', 'Ranking | R.A.S')
@section('page-title', 'Ranking de la evaluacion')
@section('body-class', 'role-superadmin tema-academico')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-academico.css') }}?v={{ filemtime(public_path('css/tema-academico.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo', [
        'contexto' => $evaluacion->titulo . ' - ' . $evaluacion->fecha->format('d/m/Y') . ' - Puntaje maximo ' . $evaluacion->puntaje_maximo,
        'acciones' => '<a href="' . route('academico.evaluaciones') . '" class="mod-btn-ghost">Volver a evaluaciones</a>',
    ])

    @can('academico.gestionar')
        <x-mod-modal id="cargar-puntaje" titulo="Cargar puntaje"
                     subtitulo="Si el estudiante ya tiene puntaje en esta evaluacion, se corrige."
                     boton="Cargar puntaje">
            <form method="POST" action="{{ route('academico.resultado.guardar', $evaluacion) }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-5">
                    <label for="estudiante_id">Estudiante</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select" required>
                        <option value="">Selecciona</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="puntaje">Puntaje</label>
                    <input id="puntaje" type="number" step="0.01" min="0" name="puntaje" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="observacion">Observacion</label>
                    <input id="observacion" type="text" name="observacion" class="form-control" maxlength="200">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Ranking</h5>
            <small>{{ $ranking->count() }} estudiante(s) con puntaje</small>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead><tr><th>Puesto</th><th>Estudiante</th><th>Puntaje</th><th>Observacion</th></tr></thead>
                <tbody>
                    @forelse ($ranking as $i => $r)
                        <tr>
                            <td>
                                <span class="mod-chip {{ $i < 3 ? 'mod-chip--ok' : 'mod-chip--neutral' }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td>{{ $r->estudiante?->apellidos }}, {{ $r->estudiante?->nombres }}</td>
                            <td><strong>{{ $r->puntaje }} / {{ $evaluacion->puntaje_maximo }}</strong></td>
                            <td>{{ $r->observacion ?: '--' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="mod-empty">Aun no se han cargado puntajes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
