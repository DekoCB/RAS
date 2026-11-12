@extends('layouts.mantis')

@section('title', 'Horarios de la academia | R.A.S')
@section('page-title', 'Horarios de la academia')
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
        'acciones' => '<a href="' . route('academia.index') . '" class="mod-btn-ghost">Volver a la academia</a>',
    ])

    @can('academia.gestionar')
        <x-mod-modal id="agregar-bloque" titulo="Agregar bloque"
                     boton="Agregar bloque">
            <form method="POST" action="{{ route('academia.horarios.guardar') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="area">Area</label>
                    <input id="area" type="text" name="area" class="form-control" required maxlength="120"
                           placeholder="Ciencias, Letras...">
                </div>
                <div class="col-md-3">
                    <label for="curso">Curso</label>
                    <input id="curso" type="text" name="curso" class="form-control" required maxlength="120">
                </div>
                <div class="col-md-2">
                    <label for="dia">Dia</label>
                    <select id="dia" name="dia" class="form-select" required>
                        @foreach ($dias as $numero => $nombre)
                            <option value="{{ $numero }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="hora_inicio">Desde</label>
                    <input id="hora_inicio" type="time" name="hora_inicio" class="form-control" value="16:00" required>
                </div>
                <div class="col-md-2">
                    <label for="hora_fin">Hasta</label>
                    <input id="hora_fin" type="time" name="hora_fin" class="form-control" value="17:30" required>
                </div>
                <div class="col-md-4">
                    <label for="docente">Docente</label>
                    <input id="docente" type="text" name="docente" class="form-control">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Agregar bloque</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Horario semanal</h5>
            <small>{{ $horarios->count() }} bloque(s)</small>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead><tr><th>Area</th><th>Curso</th><th>Dia</th><th>Horario</th><th>Docente</th><th></th></tr></thead>
                <tbody>
                    @forelse ($horarios as $h)
                        <tr>
                            <td><span class="mod-chip">{{ $h->area }}</span></td>
                            <td>{{ $h->curso }}</td>
                            <td>{{ $h->etiquetaDia() }}</td>
                            <td>
                                {{ \Illuminate\Support\Carbon::parse($h->hora_inicio)->format('g:i a') }}
                                &ndash;
                                {{ \Illuminate\Support\Carbon::parse($h->hora_fin)->format('g:i a') }}
                            </td>
                            <td>{{ $h->docente ?: '--' }}</td>
                            <td class="text-end">
                                @can('academia.gestionar')
                                    <form method="POST" action="{{ route('academia.horarios.eliminar', $h) }}"
                                          onsubmit="return confirm('Eliminar este bloque?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mod-empty">Aun no hay horarios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
