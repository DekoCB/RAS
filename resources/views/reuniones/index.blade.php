@extends('layouts.mantis')

@section('title', 'Reuniones | R.A.S')
@section('page-title', 'Reuniones')
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
        'acciones' => '<a href="' . route('reuniones.capacitaciones') . '" class="mod-btn-ghost">Capacitaciones</a>'
                    . '<a href="' . route('reuniones.compromisos') . '" class="mod-btn-ghost">Cartas de compromiso</a>',
    ])

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['total'] }}</div>
            <div class="mod-stat__label">Reuniones</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $resumen['programadas'] }}</div>
            <div class="mod-stat__label">Programadas</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $resumen['realizadas'] }}</div>
            <div class="mod-stat__label">Realizadas</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">{{ $resumen['con_acta'] }}</div>
            <div class="mod-stat__label">Con acta cargada</div>
        </div>
    </div>

    @can('reunion.gestionar')
        <x-mod-modal id="programar-reunion" titulo="Programar reunion"
                     subtitulo="El acta se puede cargar ahora o despues de realizada."
                     boton="Programar reunion">
            <form method="POST" action="{{ route('reuniones.store') }}"
                  enctype="multipart/form-data" class="mod-form row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="titulo_r">Titulo</label>
                    <input id="titulo_r" type="text" name="titulo" class="form-control" required maxlength="180">
                </div>
                <div class="col-md-3">
                    <label for="fecha">Fecha y hora</label>
                    <input id="fecha" type="datetime-local" name="fecha" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="lugar">Lugar</label>
                    <input id="lugar" type="text" name="lugar" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="responsable">Responsable</label>
                    <input id="responsable" type="text" name="responsable" class="form-control">
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
                <div class="col-md-2">
                    <label for="grado_id">Grado</label>
                    <select id="grado_id" name="grado_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($grados as $g)
                            <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" class="form-select" required>
                        @foreach ($estados as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="agenda">Agenda</label>
                    <input id="agenda" type="text" name="agenda" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="acta">Acta (PDF o Word)</label>
                    <input id="acta" type="file" name="acta" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Programar</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Agenda de reuniones</h5>
            </div>
            <form method="GET" action="{{ route('reuniones.index') }}" class="mod-form d-flex gap-2 flex-wrap">
                <select name="tipo" class="form-select" style="min-width:190px">
                    <option value="">Todo tipo</option>
                    @foreach ($tipos as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <select name="estado" class="form-select" style="min-width:150px">
                    <option value="">Todo estado</option>
                    @foreach ($estados as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($estado === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mod-btn mod-btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Fecha</th><th>Tipo</th><th>Titulo</th><th>Alcance</th><th>Estado</th><th>Acta</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($reuniones as $r)
                        <tr>
                            <td>{{ $r->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ $r->etiquetaTipo() }}</td>
                            <td>
                                {{ $r->titulo }}
                                @if ($r->notificada)
                                    <span class="mod-chip mod-chip--ok">Notificada</span>
                                @endif
                            </td>
                            <td>{{ $r->grado?->nombre ?? ($r->nivel ?: 'General') }}</td>
                            <td>
                                <span class="mod-chip {{ $r->estado === 'realizada' ? 'mod-chip--ok' : ($r->estado === 'cancelada' ? 'mod-chip--neutral' : 'mod-chip--pend') }}">
                                    {{ $estados[$r->estado] ?? $r->estado }}
                                </span>
                            </td>
                            <td>
                                @if ($r->acta_archivo)
                                    <a href="{{ asset('storage/' . $r->acta_archivo) }}" target="_blank" rel="noopener">Ver acta</a>
                                @else
                                    --
                                @endif
                            </td>
                            <td class="text-end">
                                @can('reunion.gestionar')
                                    @unless ($r->notificada)
                                        <form method="POST" action="{{ route('reuniones.notificar', $r) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">Notificar</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('reuniones.destroy', $r) }}" class="d-inline"
                                          onsubmit="return confirm('Eliminar esta reunion?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">No hay reuniones con este filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reuniones->hasPages())
            <div class="mod-card__body">{{ $reuniones->links() }}</div>
        @endif
    </div>
@endsection
