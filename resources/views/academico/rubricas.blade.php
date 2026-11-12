@extends('layouts.mantis')

@section('title', 'Rubricas | R.A.S')
@section('page-title', 'Rubricas de evaluacion')
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

    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Niveles de logro</h5>
                <small>Escala oficial aplicada en Secundaria (RF-33).</small>
            </div>
        </div>
        <div class="mod-card__body d-flex gap-2 flex-wrap">
            @foreach ($niveles as $n => $etiqueta)
                <span class="mod-chip {{ $n >= 3 ? 'mod-chip--ok' : ($n === 2 ? '' : 'mod-chip--pend') }}">{{ $etiqueta }}</span>
            @endforeach
        </div>
    </div>

    @can('academico.gestionar')
        <x-mod-modal id="crear-rubrica" titulo="Crear rubrica"
                     subtitulo="Describe cada criterio en los cuatro niveles. Los criterios vacios se ignoran."
                     boton="Crear rubrica">
            <form method="POST" action="{{ route('academico.rubricas.guardar') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="anio_escolar_id">Ano escolar</label>
                    <select id="anio_escolar_id" name="anio_escolar_id" class="form-select" required>
                        @foreach ($anios as $a)
                            <option value="{{ $a->id }}" @selected($a->activo)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
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
                <div class="col-md-2">
                    <label for="nivel">Nivel</label>
                    <select id="nivel" name="nivel" class="form-select">
                        <option value="">Todos</option>
                        @foreach (['Inicial', 'Primaria', 'Secundaria'] as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="titulo">Titulo</label>
                    <input id="titulo" type="text" name="titulo" class="form-control" required maxlength="180">
                </div>
                <div class="col-12">
                    <label for="descripcion">Descripcion</label>
                    <input id="descripcion" type="text" name="descripcion" class="form-control">
                </div>

                {{-- Tres criterios con su descriptor por nivel de logro --}}
                @for ($i = 1; $i <= 3; $i++)
                    <div class="col-12">
                        <label>Criterio {{ $i }}</label>
                        <input type="text" name="criterio[Criterio {{ $i }}][nombre]" class="form-control mb-2"
                               placeholder="Nombre del criterio {{ $i }}">
                        <div class="row g-2">
                            @foreach ($niveles as $n => $etiqueta)
                                <div class="col-md-3">
                                    <input type="text" name="criterio[Criterio {{ $i }}][{{ $n }}]"
                                           class="form-control" placeholder="{{ $etiqueta }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endfor

                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Crear rubrica</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Rubricas registradas</h5>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead><tr><th>Titulo</th><th>Curso</th><th>Nivel</th><th>Criterios</th><th></th></tr></thead>
                <tbody>
                    @forelse ($rubricas as $r)
                        <tr>
                            <td>
                                {{ $r->titulo }}
                                @if ($r->descripcion)
                                    <div class="text-muted" style="font-size:.78rem">{{ $r->descripcion }}</div>
                                @endif
                            </td>
                            <td>{{ $r->curso?->nombre ?? '--' }}</td>
                            <td>{{ $r->nivel ?: 'Todos' }}</td>
                            <td>{{ is_array($r->criterios) ? count($r->criterios) : 0 }}</td>
                            <td class="text-end">
                                @can('academico.gestionar')
                                    <form method="POST" action="{{ route('academico.rubricas.eliminar', $r) }}"
                                          onsubmit="return confirm('Eliminar esta rubrica?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="mod-empty">Aun no hay rubricas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rubricas->hasPages())
            <div class="mod-card__body">{{ $rubricas->links() }}</div>
        @endif
    </div>
@endsection
