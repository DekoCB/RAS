@extends('layouts.mantis')

@section('title', 'Evaluaciones | R.A.S')
@section('page-title', 'Examenes y ranking')
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
    @include('partials.modulo')

    @can('academico.gestionar')
        <x-mod-modal id="crear-evaluacion" titulo="Crear evaluacion"
                     boton="Crear evaluacion">
            <form method="POST" action="{{ route('academico.evaluaciones.guardar') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="anio_escolar_id">Ano escolar</label>
                    <select id="anio_escolar_id" name="anio_escolar_id" class="form-select" required>
                        @foreach ($anios as $a)
                            <option value="{{ $a->id }}" @selected($a->activo)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipo_f">Tipo</label>
                    <select id="tipo_f" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="titulo">Titulo</label>
                    <input id="titulo" type="text" name="titulo" class="form-control" required maxlength="180">
                </div>
                <div class="col-md-3">
                    <label for="curso_id">Curso</label>
                    <select id="curso_id" name="curso_id" class="form-select">
                        <option value="">Sin curso</option>
                        @foreach ($cursos as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="seccion_id">Seccion</label>
                    <select id="seccion_id" name="seccion_id" class="form-select">
                        <option value="">Sin seccion</option>
                        @foreach ($secciones as $s)
                            <option value="{{ $s->id }}">{{ $s->grado?->nombre }} &mdash; {{ $s->nombre ?? $s->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="periodo">Periodo</label>
                    <input id="periodo" type="text" name="periodo" class="form-control" maxlength="60">
                </div>
                <div class="col-md-2">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" class="form-control"
                           value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-2">
                    <label for="puntaje_maximo">Puntaje max.</label>
                    <input id="puntaje_maximo" type="number" step="0.01" min="1" name="puntaje_maximo"
                           class="form-control" value="20" required>
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Crear</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Evaluaciones</h5>
            <form method="GET" action="{{ route('academico.evaluaciones') }}" class="mod-form d-flex gap-2">
                <select name="tipo" class="form-select" style="min-width:180px">
                    <option value="">Todo tipo</option>
                    @foreach ($tipos as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mod-btn mod-btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Fecha</th><th>Tipo</th><th>Titulo</th><th>Curso</th><th>Seccion</th><th>Resultados</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($evaluaciones as $e)
                        <tr>
                            <td>{{ $e->fecha->format('d/m/Y') }}</td>
                            <td><span class="mod-chip">{{ $tipos[$e->tipo] ?? $e->tipo }}</span></td>
                            <td>{{ $e->titulo }}</td>
                            <td>{{ $e->curso?->nombre ?? '--' }}</td>
                            <td>{{ $e->seccion?->grado?->nombre ?? '--' }}</td>
                            <td>{{ $e->resultados_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('academico.ranking', $e) }}" class="btn btn-sm btn-outline-primary">
                                    Ranking
                                </a>
                                @can('academico.gestionar')
                                    <form method="POST" action="{{ route('academico.evaluaciones.eliminar', $e) }}"
                                          class="d-inline" onsubmit="return confirm('Eliminar esta evaluacion y sus resultados?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">Aun no hay evaluaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($evaluaciones->hasPages())
            <div class="mod-card__body">{{ $evaluaciones->links() }}</div>
        @endif
    </div>
@endsection
