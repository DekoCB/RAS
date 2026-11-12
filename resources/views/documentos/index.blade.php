@extends('layouts.mantis')

@section('title', $titulo . ' | R.A.S')
@section('page-title', $titulo)
@section('body-class', 'role-superadmin tema-documentos')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-documentos.css') }}?v={{ filemtime(public_path('css/tema-documentos.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $filtrando = $categoria || $anio || $buscar !== '';
    $tipoArchivo = function (?string $nombre): array {
        $ext = strtolower(pathinfo((string) $nombre, PATHINFO_EXTENSION));
        return match (true) {
            $ext === 'pdf'                                   => ['pdf',   'PDF',   'file-text'],
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])   => ['img',   strtoupper($ext), 'image'],
            in_array($ext, ['doc', 'docx'])                  => ['doc',   'WORD',  'file'],
            in_array($ext, ['xls', 'xlsx'])                  => ['xls',   'EXCEL', 'grid'],
            $ext === 'mp4'                                   => ['video', 'VIDEO', 'video'],
            default                                          => ['none',  '',      'file'],
        };
    };
@endphp
<div class="dc-page">
    @include('partials.modulo')

    {{-- Barra superior --}}
    <div class="dc-top">
        <div>
            <div class="dc-top__kicker">Archivador &middot; {{ $titulo }}</div>
            <div class="dc-top__text">
                <strong>{{ $total }}</strong> {{ $total === 1 ? 'documento' : 'documentos' }} en {{ count($categorias) }} carpetas
            </div>
        </div>
        @can('documento.gestionar')
            <x-mod-modal id="doc-nuevo" titulo="Registrar documento"
                         subtitulo="PDF, imagen, Word, Excel o video. Hasta 20 MB."
                         boton="Registrar documento">
                <form method="POST" action="{{ route('documentos.store', $modulo) }}"
                      enctype="multipart/form-data" class="mod-form row g-3">
                    @csrf
                    <div class="col-12">
                        <label for="titulo_doc">Titulo del documento</label>
                        <input id="titulo_doc" type="text" name="titulo" class="form-control" required maxlength="180"
                               placeholder="Ej. Plan anual de actividades 2026" value="{{ old('titulo') }}" autofocus>
                    </div>
                    <div class="col-md-6">
                        <label for="categoria">Carpeta</label>
                        <select id="categoria" name="categoria" class="form-select" required>
                            @foreach ($categorias as $clave => $etiqueta)
                                <option value="{{ $clave }}" @selected(old('categoria', $categoria) === $clave)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="anio">A&ntilde;o</label>
                        <input id="anio" type="number" name="anio" class="form-control"
                               min="2000" max="2100" value="{{ old('anio', now()->year) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="nivel">Nivel</label>
                        <select id="nivel" name="nivel" class="form-select">
                            <option value="">No aplica</option>
                            @foreach (['Inicial', 'Primaria', 'Secundaria'] as $n)
                                <option value="{{ $n }}" @selected(old('nivel') === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($docentes->isNotEmpty())
                        <div class="col-12">
                            <label for="docente_id">Docente al que pertenece (opcional)</label>
                            <select id="docente_id" name="docente_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($docentes as $d)
                                    <option value="{{ $d->id }}" @selected((int) old('docente_id') === $d->id)>{{ $d->apellidos }}, {{ $d->nombres }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-12">
                        <label for="archivo">Archivo</label>
                        <div class="dc-drop">
                            <i data-feather="upload-cloud"></i>
                            <div style="flex:1;min-width:0">
                                <input id="archivo" type="file" name="archivo" class="form-control"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.mp4">
                                <small>PDF, JPG, PNG, Word, Excel o MP4 &middot; m&aacute;ximo 20 MB. Puedes registrar el documento sin archivo y adjuntarlo despu&eacute;s.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="descripcion">Descripci&oacute;n (opcional)</label>
                        <input id="descripcion" type="text" name="descripcion" class="form-control"
                               placeholder="Breve nota sobre el contenido" value="{{ old('descripcion') }}">
                    </div>
                    <div class="col-12 mod-form__pie">
                        <label class="dc-switch mod-form__extra">
                            <input type="checkbox" name="confidencial" value="1" @checked(old('confidencial'))>
                            Confidencial <small>&middot; solo lo ve quien gestiona documentos</small>
                        </label>
                        <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="mod-btn">Registrar documento</button>
                    </div>
                </form>
            </x-mod-modal>
        @endcan
    </div>

    {{-- Carpetas (RF-41) --}}
    <div class="dc-folders">
        <a href="{{ route('documentos.index', $modulo) }}" class="dc-folder {{ !$categoria ? 'is-on' : '' }}">
            <span class="dc-folder__icon"><i data-feather="archive"></i></span>
            <span>
                <span class="dc-folder__name">Todas las carpetas</span>
                <div class="dc-folder__count">{{ $total }} documento(s)</div>
            </span>
        </a>
        @foreach ($categorias as $clave => $etiqueta)
            @php $n = (int) ($conteo[$clave] ?? 0); @endphp
            <a href="{{ route('documentos.index', [$modulo, 'categoria' => $clave]) }}"
               class="dc-folder {{ $categoria === $clave ? 'is-on' : '' }} {{ $n === 0 ? 'is-empty' : '' }}">
                <span class="dc-folder__icon"><i data-feather="{{ $n ? 'folder' : 'folder-minus' }}"></i></span>
                <span style="min-width:0">
                    <span class="dc-folder__name">{{ $etiqueta }}</span>
                    <div class="dc-folder__count">{{ $n ? $n . ' documento(s)' : 'Vacía' }}</div>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Documentos --}}
    <section class="dc-card">
        <div class="dc-card__head">
            <div>
                <h2>{{ $categoria ? ($categorias[$categoria] ?? $categoria) : 'Todos los documentos' }}</h2>
                <p>{{ $documentos->total() }} resultado(s){{ $filtrando ? ' con el filtro actual' : '' }}.</p>
            </div>
            @if ($filtrando)
                <a href="{{ route('documentos.index', $modulo) }}" class="dc-btn dc-btn--ghost dc-btn--sm">
                    <i data-feather="x"></i> Limpiar filtros
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('documentos.index', $modulo) }}" class="dc-filters">
            @if ($categoria)<input type="hidden" name="categoria" value="{{ $categoria }}">@endif
            <div>
                <label for="q">Buscar</label>
                <div class="dc-search">
                    <i data-feather="search"></i>
                    <input id="q" type="text" name="q" class="dc-input" placeholder="Titulo, descripcion o nombre del archivo" value="{{ $buscar }}">
                </div>
            </div>
            <div>
                <label for="f_anio">A&ntilde;o</label>
                <select id="f_anio" name="anio" class="dc-select">
                    <option value="">Todos</option>
                    @foreach ($anios as $a)
                        <option value="{{ $a }}" @selected((string) $anio === (string) $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="dc-btn dc-btn--navy"><i data-feather="filter"></i> Filtrar</button>
            </div>
        </form>

        <ul class="dc-list">
            @forelse ($documentos as $doc)
                @php [$cls, $label, $icono] = $tipoArchivo($doc->nombre_original); @endphp
                <li class="dc-item">
                    <span class="dc-file dc-file--{{ $cls }}">
                        <i data-feather="{{ $icono }}"></i>
                        @if ($label)<span>{{ $label }}</span>@endif
                    </span>
                    <div style="min-width:0">
                        <div class="dc-item__title">
                            @if ($doc->archivo)
                                <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" rel="noopener">{{ $doc->titulo }}</a>
                            @else
                                {{ $doc->titulo }}
                            @endif
                        </div>
                        @if ($doc->descripcion)
                            <div class="dc-item__desc">{{ $doc->descripcion }}</div>
                        @endif
                        <div class="dc-item__meta">
                            <span class="dc-tag dc-tag--folder"><i data-feather="folder"></i>{{ $doc->etiquetaCategoria() }}</span>
                            @if ($doc->anio)<span class="dc-tag"><i data-feather="calendar"></i>{{ $doc->anio }}</span>@endif
                            @if ($doc->nivel)<span class="dc-tag"><i data-feather="layers"></i>{{ $doc->nivel }}</span>@endif
                            @if ($doc->docente)<span class="dc-tag"><i data-feather="user"></i>{{ $doc->docente->apellidos }}, {{ $doc->docente->nombres }}</span>@endif
                            @if ($doc->archivo)
                                <span class="dc-tag"><i data-feather="paperclip"></i>{{ Str::limit($doc->nombre_original, 34) }}</span>
                            @else
                                <span class="dc-tag"><i data-feather="alert-circle"></i>Sin archivo adjunto</span>
                            @endif
                            @if ($doc->confidencial)<span class="dc-tag dc-tag--lock"><i data-feather="lock"></i>Confidencial</span>@endif
                        </div>
                    </div>
                    <span class="dc-item__acts">
                        @if ($doc->archivo)
                            <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" rel="noopener" class="dc-btn dc-btn--ghost dc-btn--sm">
                                <i data-feather="external-link"></i> Abrir
                            </a>
                        @endif
                        @can('documento.gestionar')
                            <form method="POST" action="{{ route('documentos.destroy', $doc) }}" class="d-inline"
                                  onsubmit="return confirm('Eliminar este documento?')">
                                @csrf
                                @method('DELETE')
                                <button class="dc-btn dc-btn--danger dc-btn--sm" title="Eliminar"><i data-feather="trash-2"></i></button>
                            </form>
                        @endcan
                    </span>
                </li>
            @empty
                <li class="dc-empty">
                    <span class="dc-empty__icon"><i data-feather="{{ $filtrando ? 'search' : 'folder-plus' }}"></i></span>
                    <strong>{{ $filtrando ? 'Sin resultados' : 'Esta carpeta está vacía' }}</strong>
                    {{ $filtrando ? 'Prueba con otra búsqueda o quita los filtros.' : 'Usa "Registrar documento" para guardar el primer archivo aquí.' }}
                </li>
            @endforelse
        </ul>

        @if ($documentos->hasPages())
            <div class="dc-pager">{{ $documentos->links() }}</div>
        @endif
    </section>
</div>
@endsection
