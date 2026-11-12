@extends('layouts.mantis')

@section('title', 'Documentos de sede | R.A.S')
@section('page-title', 'Documentos de sede')
@section('body-class', 'role-superadmin tema-institucion')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-institucion.css') }}?v={{ filemtime(public_path('css/tema-institucion.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.mod-modal-script')
    <div class="mod-barra">
        <span class="mod-barra__contexto">{{ $sede->nombre }} &mdash; {{ $sede->direccion }}</span>
        <span class="mod-barra__acciones">
            <a href="{{ route('sedes.index') }}" class="mod-btn-ghost">Volver a sedes</a>
        </span>
    </div>
    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('documento.gestionar')
        <x-mod-modal id="publicar-documento" titulo="Publicar documento"
                     subtitulo="Solo PDF, hasta 10 MB. La lista de utiles se publica por nivel."
                     boton="Publicar documento">
            <form method="POST" action="{{ route('sedes.documentos.subir', $sede) }}"
                  enctype="multipart/form-data" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="titulo">Titulo</label>
                    <input id="titulo" type="text" name="titulo" class="form-control" required maxlength="180">
                </div>
                <div class="col-md-2">
                    <label for="anio">Anio</label>
                    <input id="anio" type="number" name="anio" class="form-control" required
                           min="2000" max="2100" value="{{ now()->year }}">
                </div>
                <div class="col-md-3">
                    <label for="nivel">Nivel (solo utiles)</label>
                    <select id="nivel" name="nivel" class="form-select">
                        <option value="">No aplica</option>
                        @foreach ($niveles as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="archivo">Archivo PDF</label>
                    <input id="archivo" type="file" name="archivo" class="form-control" accept="application/pdf" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="d-inline-flex align-items-center gap-2" style="text-transform:none">
                        <input type="checkbox" name="publicado" value="1" checked> Publicado
                    </label>
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Publicar</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Documentos publicados</h5>
            <small>{{ $documentos->count() }} documento(s)</small>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Anio</th><th>Tipo</th><th>Titulo</th><th>Nivel</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $doc)
                        <tr>
                            <td>{{ $doc->anio }}</td>
                            <td>{{ $tipos[$doc->tipo] ?? $doc->tipo }}</td>
                            <td>{{ $doc->titulo }}</td>
                            <td>{{ $doc->nivel ?: '--' }}</td>
                            <td>
                                <span class="mod-chip {{ $doc->publicado ? 'mod-chip--ok' : 'mod-chip--neutral' }}">
                                    {{ $doc->publicado ? 'Publicado' : 'Oculto' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary">Ver PDF</a>
                                @can('documento.gestionar')
                                    <form method="POST" action="{{ route('sedes.documentos.eliminar', $doc) }}"
                                          class="d-inline" onsubmit="return confirm('Eliminar este documento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mod-empty">Esta sede aun no tiene documentos publicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
