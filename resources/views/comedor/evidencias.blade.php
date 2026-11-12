@extends('layouts.mantis')

@section('title', 'Evidencias del comedor | R.A.S')
@section('page-title', 'Evidencias del comedor')
@section('body-class', 'role-superadmin tema-comedor')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-comedor.css') }}?v={{ filemtime(public_path('css/tema-comedor.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.mod-modal-script')
    <div class="mod-barra">
        <span class="mod-barra__acciones">
            <a href="{{ route('comedor.index') }}" class="mod-btn-ghost">Volver al comedor</a>
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

    @can('comedor.evidencia')
        <x-mod-modal id="cargar-evidencia" titulo="Cargar evidencia"
                     subtitulo="Imagenes (JPG, PNG, WEBP) o video (MP4, WEBM). Maximo 50 MB."
                     boton="Cargar evidencia">
            <form method="POST" action="{{ route('comedor.evidencias.subir') }}"
                  enctype="multipart/form-data" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" class="form-control"
                           value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-5">
                    <label for="descripcion">Descripcion</label>
                    <input id="descripcion" type="text" name="descripcion" class="form-control" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label for="archivo">Archivo</label>
                    <input id="archivo" type="file" name="archivo" class="form-control"
                           accept=".jpg,.jpeg,.png,.webp,.mp4,.webm" required>
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Cargar evidencia</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Galeria</h5>
            <small>{{ $evidencias->total() }} archivo(s)</small>
        </div>
        <div class="mod-card__body">
            @if ($evidencias->isEmpty())
                <div class="mod-empty">Aun no se han cargado evidencias.</div>
            @else
                <div class="mod-gallery">
                    @foreach ($evidencias as $ev)
                        <div class="mod-gallery__item">
                            @if ($ev->tipo === 'video')
                                <video controls preload="metadata">
                                    <source src="{{ asset('storage/' . $ev->archivo) }}">
                                </video>
                            @else
                                <img src="{{ asset('storage/' . $ev->archivo) }}"
                                     alt="{{ $ev->descripcion ?: 'Evidencia del comedor' }}" loading="lazy">
                            @endif
                            <div class="mod-gallery__meta">
                                <strong>{{ $ev->fecha->format('d/m/Y') }}</strong>
                                {{ $ev->descripcion ?: 'Sin descripcion' }}
                                @can('comedor.evidencia')
                                    <form method="POST" action="{{ route('comedor.evidencias.eliminar', $ev) }}"
                                          class="mt-2" onsubmit="return confirm('Eliminar esta evidencia?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($evidencias->hasPages())
            <div class="mod-card__body">{{ $evidencias->links() }}</div>
        @endif
    </div>
@endsection
