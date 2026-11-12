@extends('layouts.mantis')

@section('title', 'Capacitaciones | R.A.S')
@section('page-title', 'Capacitaciones y pasantias')
@section('body-class', 'role-superadmin tema-reuniones')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-reuniones.css') }}?v={{ filemtime(public_path('css/tema-reuniones.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo', [
        'acciones' => '<a href="' . route('reuniones.index') . '" class="mod-btn-ghost">Volver a reuniones</a>',
    ])

    @can('reunion.gestionar')
        <x-mod-modal id="registrar-capacitacion" titulo="Registrar capacitacion"
                     boton="Registrar capacitacion">
            <form method="POST" action="{{ route('reuniones.capacitaciones.guardar') }}"
                  enctype="multipart/form-data" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="dirigido_a">Dirigido a</label>
                    <select id="dirigido_a" name="dirigido_a" class="form-select" required>
                        @foreach ($dirigidos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="titulo">Titulo</label>
                    <input id="titulo" type="text" name="titulo" class="form-control" required maxlength="180">
                </div>
                <div class="col-md-3">
                    <label for="fecha_inicio">Fecha de inicio</label>
                    <input id="fecha_inicio" type="date" name="fecha_inicio" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin">Fecha de fin</label>
                    <input id="fecha_fin" type="date" name="fecha_fin" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="horas">Horas</label>
                    <input id="horas" type="number" name="horas" class="form-control" min="1">
                </div>
                <div class="col-md-4">
                    <label for="facilitador">Facilitador</label>
                    <input id="facilitador" type="text" name="facilitador" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="lugar">Lugar</label>
                    <input id="lugar" type="text" name="lugar" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="evidencia">Evidencia</label>
                    <input id="evidencia" type="file" name="evidencia" class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png,.webp">
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
            <h5>Historial</h5>
            <form method="GET" action="{{ route('reuniones.capacitaciones') }}" class="mod-form d-flex gap-2">
                <select name="dirigido_a" class="form-select" style="min-width:170px">
                    <option value="">Todos</option>
                    @foreach ($dirigidos as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($dirigido === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mod-btn mod-btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Fecha</th><th>Titulo</th><th>Dirigido a</th><th>Tipo</th><th>Horas</th><th>Facilitador</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($capacitaciones as $c)
                        <tr>
                            <td>
                                {{ $c->fecha_inicio->format('d/m/Y') }}
                                @if ($c->fecha_fin)
                                    <div class="text-muted" style="font-size:.76rem">
                                        al {{ $c->fecha_fin->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{ $c->titulo }}
                                @if ($c->evidencia)
                                    <a href="{{ asset('storage/' . $c->evidencia) }}" target="_blank" rel="noopener"
                                       style="font-size:.76rem">Ver evidencia</a>
                                @endif
                            </td>
                            <td>{{ $dirigidos[$c->dirigido_a] ?? $c->dirigido_a }}</td>
                            <td><span class="mod-chip">{{ $tipos[$c->tipo] ?? $c->tipo }}</span></td>
                            <td>{{ $c->horas ?: '--' }}</td>
                            <td>{{ $c->facilitador ?: '--' }}</td>
                            <td class="text-end">
                                @can('reunion.gestionar')
                                    <form method="POST" action="{{ route('reuniones.capacitaciones.eliminar', $c) }}"
                                          onsubmit="return confirm('Eliminar esta capacitacion?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">Aun no hay capacitaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($capacitaciones->hasPages())
            <div class="mod-card__body">{{ $capacitaciones->links() }}</div>
        @endif
    </div>
@endsection
