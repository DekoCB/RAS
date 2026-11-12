@extends('layouts.mantis')

@section('title', 'Informes descriptivos | R.A.S')
@section('page-title', 'Informes descriptivos de Inicial')
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
        <x-mod-modal id="registrar-informe" titulo="Registrar informe"
                     subtitulo="Si ya existe un informe del mismo estudiante, tipo y periodo, se actualiza."
                     boton="Registrar informe">
            <form method="POST" action="{{ route('academico.informes.guardar') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="anio_escolar_id">Ano escolar</label>
                    <select id="anio_escolar_id" name="anio_escolar_id" class="form-select" required>
                        @foreach ($anios as $a)
                            <option value="{{ $a->id }}" @selected($a->activo)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="estudiante_id">Estudiante de Inicial</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select" required>
                        <option value="">Selecciona</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
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
                <div class="col-md-2">
                    <label for="periodo">Periodo</label>
                    <input id="periodo" type="text" name="periodo" class="form-control" required maxlength="60"
                           placeholder="Marzo / Bimestre I">
                </div>
                <div class="col-md-4">
                    <label for="logros">Logros</label>
                    <textarea id="logros" name="logros" class="form-control" rows="4"></textarea>
                </div>
                <div class="col-md-4">
                    <label for="dificultades">Dificultades</label>
                    <textarea id="dificultades" name="dificultades" class="form-control" rows="4"></textarea>
                </div>
                <div class="col-md-4">
                    <label for="recomendaciones">Recomendaciones</label>
                    <textarea id="recomendaciones" name="recomendaciones" class="form-control" rows="4"></textarea>
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Guardar informe</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Informes registrados</h5>
            <form method="GET" action="{{ route('academico.informes') }}" class="mod-form d-flex gap-2">
                <select name="tipo" class="form-select" style="min-width:150px">
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
                <thead><tr><th>Estudiante</th><th>Tipo</th><th>Periodo</th><th>Logros</th><th></th></tr></thead>
                <tbody>
                    @forelse ($informes as $i)
                        <tr>
                            <td>{{ $i->estudiante?->apellidos }}, {{ $i->estudiante?->nombres }}</td>
                            <td><span class="mod-chip">{{ $tipos[$i->tipo] ?? $i->tipo }}</span></td>
                            <td>{{ $i->periodo }}</td>
                            <td>{{ Str::limit($i->logros, 70) ?: '--' }}</td>
                            <td class="text-end">
                                @can('academico.gestionar')
                                    <form method="POST" action="{{ route('academico.informes.eliminar', $i) }}"
                                          onsubmit="return confirm('Eliminar este informe?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="mod-empty">Aun no hay informes descriptivos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($informes->hasPages())
            <div class="mod-card__body">{{ $informes->links() }}</div>
        @endif
    </div>
@endsection
