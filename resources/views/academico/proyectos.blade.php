@extends('layouts.mantis')

@section('title', 'Proyectos pedagogicos | R.A.S')
@section('page-title', 'Proyectos y metodos')
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
        <x-mod-modal id="registrar-proyecto-o-metodo" titulo="Registrar proyecto o metodo"
                     boton="Registrar proyecto o metodo">
            <form method="POST" action="{{ route('academico.proyectos.guardar') }}" class="mod-form row g-3">
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
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="titulo">Titulo</label>
                    <input id="titulo" type="text" name="titulo" class="form-control" required maxlength="180">
                </div>
                <div class="col-md-2">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" class="form-select" required>
                        <option value="en_curso">En curso</option>
                        <option value="terminado">Terminado</option>
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
                    <label for="fecha_inicio">Inicio</label>
                    <input id="fecha_inicio" type="date" name="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="fecha_fin">Fin</label>
                    <input id="fecha_fin" type="date" name="fecha_fin" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="responsable">Responsable</label>
                    <input id="responsable" type="text" name="responsable" class="form-control">
                </div>
                <div class="col-12">
                    <label for="descripcion">Descripcion</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head"><h5>Proyectos registrados</h5></div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Titulo</th><th>Tipo</th><th>Nivel / curso</th><th>Periodo</th><th>Responsable</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($proyectos as $p)
                        <tr>
                            <td>
                                {{ $p->titulo }}
                                @if ($p->descripcion)
                                    <div class="text-muted" style="font-size:.78rem">{{ Str::limit($p->descripcion, 70) }}</div>
                                @endif
                            </td>
                            <td><span class="mod-chip">{{ $tipos[$p->tipo] ?? $p->tipo }}</span></td>
                            <td>{{ $p->curso?->nombre ?? ($p->nivel ?: 'General') }}</td>
                            <td>
                                {{ $p->fecha_inicio?->format('d/m/Y') ?: '--' }}
                                @if ($p->fecha_fin)
                                    &ndash; {{ $p->fecha_fin->format('d/m/Y') }}
                                @endif
                            </td>
                            <td>{{ $p->responsable ?: '--' }}</td>
                            <td>
                                <span class="mod-chip {{ $p->estado === 'terminado' ? 'mod-chip--ok' : '' }}">
                                    {{ $p->estado === 'terminado' ? 'Terminado' : 'En curso' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('academico.gestionar')
                                    <form method="POST" action="{{ route('academico.proyectos.eliminar', $p) }}"
                                          onsubmit="return confirm('Eliminar este proyecto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">Aun no hay proyectos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($proyectos->hasPages())
            <div class="mod-card__body">{{ $proyectos->links() }}</div>
        @endif
    </div>
@endsection
