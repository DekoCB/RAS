@extends('layouts.mantis')

@section('title', 'Sedes | R.A.S')
@section('page-title', 'Sedes')
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
        <span class="mod-barra__acciones">
            <a href="{{ route('institucion.index') }}" class="mod-btn-ghost">Datos institucionales</a>
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

    @can('sede.gestionar')
        <x-mod-modal id="registrar-sede" titulo="Registrar sede"
                     boton="Registrar sede">
            <form method="POST" action="{{ route('sedes.store') }}" enctype="multipart/form-data"
                  class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" type="text" name="nombre" class="form-control" required maxlength="120"
                           value="{{ old('nombre') }}">
                </div>
                <div class="col-md-5">
                    <label for="direccion">Direccion</label>
                    <input id="direccion" type="text" name="direccion" class="form-control" required
                           value="{{ old('direccion') }}">
                </div>
                <div class="col-md-2">
                    <label for="telefono">Telefono</label>
                    <input id="telefono" type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                </div>
                <div class="col-md-2">
                    <label for="orden">Orden</label>
                    <input id="orden" type="number" name="orden" class="form-control" min="0" value="{{ old('orden', 0) }}">
                </div>
                <div class="col-md-4">
                    <label for="referencia">Referencia</label>
                    <input id="referencia" type="text" name="referencia" class="form-control" value="{{ old('referencia') }}">
                </div>
                <div class="col-md-4">
                    <label>Niveles que atiende</label>
                    <div class="d-flex gap-3 pt-1">
                        @foreach ($niveles as $n)
                            <label class="d-inline-flex align-items-center gap-2" style="text-transform:none">
                                <input type="checkbox" name="niveles[]" value="{{ $n }}"> {{ $n }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="portada">Imagen de portada</label>
                    <input id="portada" type="file" name="portada" class="form-control" accept="image/*">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar sede</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    @forelse ($sedes as $sede)
        <div class="mod-card">
            <div class="mod-card__head">
                <div>
                    <h5>{{ $sede->nombre }}</h5>
                    <small>{{ $sede->referencia ?: 'Sin referencia' }} &middot;
                        {{ $sede->documentos_count }} documento(s)</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sedes.documentos', $sede) }}" class="mod-btn mod-btn-sm">Documentos</a>
                    @can('sede.gestionar')
                        <form method="POST" action="{{ route('sedes.destroy', $sede) }}"
                              onsubmit="return confirm('Eliminar la sede y todos sus documentos?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    @endcan
                </div>
            </div>
            <div class="mod-card__body">
                @can('sede.gestionar')
                    <form method="POST" action="{{ route('sedes.update', $sede) }}" enctype="multipart/form-data"
                          class="mod-form row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-3">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" required value="{{ $sede->nombre }}">
                        </div>
                        <div class="col-md-5">
                            <label>Direccion</label>
                            <input type="text" name="direccion" class="form-control" required value="{{ $sede->direccion }}">
                        </div>
                        <div class="col-md-2">
                            <label>Telefono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ $sede->telefono }}">
                        </div>
                        <div class="col-md-2">
                            <label>Orden</label>
                            <input type="number" name="orden" class="form-control" min="0" value="{{ $sede->orden }}">
                        </div>
                        <div class="col-md-4">
                            <label>Referencia</label>
                            <input type="text" name="referencia" class="form-control" value="{{ $sede->referencia }}">
                        </div>
                        <div class="col-md-4">
                            <label>Niveles que atiende</label>
                            <div class="d-flex gap-3 pt-1">
                                @foreach ($niveles as $n)
                                    <label class="d-inline-flex align-items-center gap-2" style="text-transform:none">
                                        <input type="checkbox" name="niveles[]" value="{{ $n }}"
                                               @checked($sede->atiendeNivel($n))> {{ $n }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Cambiar portada</label>
                            <input type="file" name="portada" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-8">
                            <label>Mapa (URL)</label>
                            <input type="url" name="mapa_url" class="form-control" value="{{ $sede->mapa_url }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-3">
                            <label class="d-inline-flex align-items-center gap-2" style="text-transform:none">
                                <input type="checkbox" name="activo" value="1" @checked($sede->activo)> Activa
                            </label>
                            <button type="submit" class="mod-btn">Guardar</button>
                        </div>
                    </form>
                @else
                    <p class="mb-1"><strong>Direccion:</strong> {{ $sede->direccion }}</p>
                    <p class="mb-1"><strong>Telefono:</strong> {{ $sede->telefono ?: '--' }}</p>
                    <p class="mb-0"><strong>Niveles:</strong> {{ implode(', ', $sede->niveles ?? []) ?: '--' }}</p>
                @endcan
            </div>
        </div>
    @empty
        <div class="mod-card">
            <div class="mod-empty">Aun no hay sedes registradas.</div>
        </div>
    @endforelse
@endsection
