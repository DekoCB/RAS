@extends('layouts.mantis')

@section('title', 'Cartas de compromiso | R.A.S')
@section('page-title', 'Cartas de compromiso')
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

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['total'] }}</div>
            <div class="mod-stat__label">Cartas registradas</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $resumen['firmadas'] }}</div>
            <div class="mod-stat__label">Firmadas</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">{{ $resumen['pendiente'] }}</div>
            <div class="mod-stat__label">Sin firmar</div>
        </div>
    </div>

    @can('reunion.gestionar')
        <x-mod-modal id="registrar-carta" titulo="Registrar carta"
                     subtitulo="Si ya existe una carta del estudiante para ese ano, se actualiza."
                     boton="Registrar carta">
            <form method="POST" action="{{ route('reuniones.compromisos.guardar') }}"
                  enctype="multipart/form-data" class="mod-form row g-3">
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
                    <label for="anio">Ano</label>
                    <input id="anio" type="number" name="anio" class="form-control"
                           min="2000" max="2100" value="{{ now()->year }}" required>
                </div>
                <div class="col-md-3">
                    <label for="fecha_firma">Fecha de firma</label>
                    <input id="fecha_firma" type="date" name="fecha_firma" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <label class="d-inline-flex align-items-center gap-2" style="text-transform:none">
                        <input type="checkbox" name="firmada" value="1"> Firmada
                    </label>
                </div>
                <div class="col-md-8">
                    <label for="compromisos">Compromisos asumidos</label>
                    <input id="compromisos" type="text" name="compromisos" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="archivo">Carta escaneada</label>
                    <input id="archivo" type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Guardar carta</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Cartas registradas</h5>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Ano</th><th>Estudiante</th><th>Compromisos</th><th>Estado</th><th>Archivo</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($cartas as $carta)
                        <tr>
                            <td>{{ $carta->anio }}</td>
                            <td>{{ $carta->estudiante?->apellidos }}, {{ $carta->estudiante?->nombres }}</td>
                            <td>{{ Str::limit($carta->compromisos, 60) ?: '--' }}</td>
                            <td>
                                @if ($carta->firmada)
                                    <span class="mod-chip mod-chip--ok">
                                        Firmada{{ $carta->fecha_firma ? ' ' . $carta->fecha_firma->format('d/m/Y') : '' }}
                                    </span>
                                @else
                                    <span class="mod-chip mod-chip--pend">Sin firmar</span>
                                @endif
                            </td>
                            <td>
                                @if ($carta->archivo)
                                    <a href="{{ asset('storage/' . $carta->archivo) }}" target="_blank" rel="noopener">Ver</a>
                                @else
                                    --
                                @endif
                            </td>
                            <td class="text-end">
                                @can('reunion.gestionar')
                                    <form method="POST" action="{{ route('reuniones.compromisos.eliminar', $carta) }}"
                                          onsubmit="return confirm('Eliminar esta carta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mod-empty">Aun no hay cartas de compromiso.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cartas->hasPages())
            <div class="mod-card__body">{{ $cartas->links() }}</div>
        @endif
    </div>
@endsection
