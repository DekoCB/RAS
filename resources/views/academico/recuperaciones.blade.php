@extends('layouts.mantis')

@section('title', 'Recuperaciones | R.A.S')
@section('page-title', 'Recuperacion y nivelacion')
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

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['total'] }}</div>
            <div class="mod-stat__label">Registros</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $resumen['aprobadas'] }}</div>
            <div class="mod-stat__label">Aprobaron (11 o mas)</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $resumen['pagadas'] }}</div>
            <div class="mod-stat__label">Pagadas</div>
        </div>
    </div>

    @can('academico.gestionar')
        <x-mod-modal id="registrar-proceso" titulo="Registrar proceso"
                     boton="Registrar proceso">
            <form method="POST" action="{{ route('academico.recuperaciones.guardar') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="anio_escolar_id">Ano escolar</label>
                    <select id="anio_escolar_id" name="anio_escolar_id" class="form-select" required>
                        @foreach ($anios as $a)
                            <option value="{{ $a->id }}" @selected($a->activo)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="estudiante_id">Estudiante</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select" required>
                        <option value="">Selecciona</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
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
                    <label for="tipo_f">Tipo</label>
                    <select id="tipo_f" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="periodo">Periodo</label>
                    <input id="periodo" type="text" name="periodo" class="form-control" maxlength="60"
                           placeholder="Bimestre I">
                </div>
                <div class="col-md-2">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="nota_original">Nota original</label>
                    <input id="nota_original" type="number" step="0.01" min="0" max="20"
                           name="nota_original" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="nota_final">Nota final</label>
                    <input id="nota_final" type="number" step="0.01" min="0" max="20"
                           name="nota_final" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="costo_f">Costo (S/)</label>
                    <input id="costo_f" type="number" step="0.01" min="0" name="costo" class="form-control"
                           value="{{ $costo }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <label class="d-inline-flex align-items-center gap-2" style="text-transform:none">
                        <input type="checkbox" name="pagado" value="1"> Pagado
                    </label>
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
            <h5>Procesos registrados</h5>
            <form method="GET" action="{{ route('academico.recuperaciones') }}" class="mod-form d-flex gap-2">
                <select name="tipo" class="form-select" style="min-width:160px">
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
                    <tr><th>Estudiante</th><th>Curso</th><th>Tipo</th><th>Periodo</th><th>Notas</th><th>Pago</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($recuperaciones as $r)
                        <tr>
                            <td>{{ $r->estudiante?->apellidos }}, {{ $r->estudiante?->nombres }}</td>
                            <td>{{ $r->curso?->nombre ?? '--' }}</td>
                            <td><span class="mod-chip">{{ $tipos[$r->tipo] ?? $r->tipo }}</span></td>
                            <td>{{ $r->periodo ?: '--' }}</td>
                            <td>
                                {{ $r->nota_original ?? '--' }}
                                &rarr;
                                <strong>{{ $r->nota_final ?? '--' }}</strong>
                            </td>
                            <td>
                                @if ($r->pagado)
                                    <span class="mod-chip mod-chip--ok">Pagado</span>
                                @else
                                    <span class="mod-chip mod-chip--pend">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('academico.gestionar')
                                    <form method="POST" action="{{ route('academico.recuperaciones.eliminar', $r) }}"
                                          onsubmit="return confirm('Eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">Aun no hay procesos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($recuperaciones->hasPages())
            <div class="mod-card__body">{{ $recuperaciones->links() }}</div>
        @endif
    </div>
@endsection
