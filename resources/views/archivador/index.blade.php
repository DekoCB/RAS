@extends('layouts.mantis')

@section('title', 'Archivador | R.A.S')
@section('page-title', 'Archivador')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/archivador.css') }}?v={{ filemtime(public_path('css/archivador.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $rutaSubida = $carpeta
        ? route('archivador.subir.dentro', $carpeta)
        : route('archivador.subir');

    $rutaCrear = $carpeta
        ? route('archivador.carpetas.crear.dentro', $carpeta)
        : route('archivador.carpetas.crear');

    $pesoTotal = $resumen['peso'];
    $pesoLegible = $pesoTotal < 1048576
        ? round($pesoTotal / 1024, 1) . ' KB'
        : ($pesoTotal < 1073741824
            ? round($pesoTotal / 1048576, 1) . ' MB'
            : round($pesoTotal / 1073741824, 2) . ' GB');
@endphp

<div class="arc-page">

    {{-- Barra de acciones --}}
    <div class="arc-toolbar">
        <div>
            <div class="arc-toolbar__kicker">Archivador &middot; carpetas del colegio</div>
            <div class="arc-toolbar__text">
                <strong>{{ $resumen['archivos'] }}</strong> archivo(s) en
                <strong>{{ $resumen['carpetas'] }}</strong> carpeta(s) &middot; {{ $pesoLegible }} ocupados
            </div>
        </div>
        <div class="arc-toolbar__actions">
            <form method="GET" action="{{ route('archivador.index') }}" class="arc-buscar">
                <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar archivo o carpeta">
                <button type="submit" class="arc-btn arc-btn--ghost arc-btn--sm">Buscar</button>
                @if ($buscar !== '')
                    <a href="{{ route('archivador.index') }}" class="arc-btn arc-btn--ghost arc-btn--sm">Limpiar</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Avisos --}}
    @if (session('status'))
        <div class="arc-alert arc-alert--ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="arc-alert arc-alert--danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="arc-card">
        <div class="arc-card__head">
            <div class="arc-card__title">
                <span class="arc-card__icon">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
                    </svg>
                </span>
                <div>
                    <h2>
                        @if ($buscar !== '')
                            Resultados de "{{ $buscar }}"
                        @else
                            {{ $carpeta?->nombre ?? 'Todas las carpetas' }}
                        @endif
                    </h2>
                    <p>
                        @if ($buscar !== '')
                            {{ $subcarpetas->count() }} carpeta(s) y {{ $archivos->total() }} archivo(s) encontrados.
                        @else
                            Arrastra una carpeta de tu PC y se sube completa, con sus subcarpetas.
                        @endif
                    </p>
                </div>
            </div>

            @if ($buscar === '')
                <div class="arc-toolbar__actions">
                    <button type="button" class="arc-btn arc-btn--teal arc-btn--sm" id="btnElegirCarpeta"
                            title="Sube una carpeta entera; se conserva su estructura de subcarpetas">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
                            <path d="M12 17V11M9 14l3-3 3 3"/>
                        </svg>
                        Subir carpeta
                    </button>
                    <button type="button" class="arc-btn arc-btn--ghost arc-btn--sm" id="btnElegirArchivos"
                            title="Maximo {{ $limiteMb }} MB por archivo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <path d="M17 8l-5-5-5 5M12 3v12"/>
                        </svg>
                        Subir archivos
                    </button>
                    <button type="button" class="arc-btn arc-btn--ghost arc-btn--sm" id="btnNuevaCarpeta">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
                            <path d="M12 11v6M9 14h6"/>
                        </svg>
                        Nueva carpeta
                    </button>
                    @if ($carpeta)
                        <a href="{{ $carpeta->parent_id ? route('archivador.carpeta', $carpeta->parent_id) : route('archivador.index') }}"
                           class="arc-btn arc-btn--ghost arc-btn--sm">Subir un nivel</a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Migas de pan --}}
        @if ($buscar === '')
            <div class="arc-ruta">
                <a href="{{ route('archivador.index') }}">Archivador</a>
                @foreach ($cadena as $paso)
                    <span class="sep">/</span>
                    @if ($loop->last)
                        <span class="actual">{{ $paso->nombre }}</span>
                    @else
                        <a href="{{ route('archivador.carpeta', $paso) }}">{{ $paso->nombre }}</a>
                    @endif
                @endforeach
            </div>

            {{-- Formulario oculto de nueva carpeta --}}
            <form method="POST" action="{{ $rutaCrear }}" id="formNuevaCarpeta" hidden>
                @csrf
                <input type="hidden" name="nombre" id="inputNuevaCarpeta">
            </form>

            {{-- webkitdirectory deja elegir una carpeta entera --}}
            <input type="file" id="inputCarpeta" webkitdirectory directory multiple hidden>
            <input type="file" id="inputArchivos" multiple hidden>

            {{-- Barra de subida: ocupa una linea y solo crece al arrastrar --}}
            <div class="arc-subida" id="zonaSubida"
                 data-url="{{ $rutaSubida }}" data-limite="{{ $limiteMb }}">
                <p class="arc-subida__pista">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <path d="M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    Tambien puedes arrastrar carpetas o archivos sobre esta ventana &middot;
                    maximo {{ $limiteMb }} MB por archivo
                </p>

                <div class="arc-progreso" id="progreso" hidden>
                    <span class="arc-progreso__barra"><span class="arc-progreso__fill" id="progresoFill"></span></span>
                    <p class="arc-progreso__texto" id="progresoTexto"></p>
                </div>
            </div>

            {{-- Aviso que solo aparece mientras se arrastra algo encima --}}
            <div class="arc-soltar" id="zonaSoltar" hidden>
                <div class="arc-soltar__caja">
                    <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <path d="M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    <strong>Suelta para subir</strong>
                    <span>Se conserva la estructura de subcarpetas</span>
                </div>
            </div>
        @endif

        {{-- Carpetas --}}
        @if ($subcarpetas->isNotEmpty())
            <div class="arc-carpetas">
                @foreach ($subcarpetas as $sub)
                    <a class="arc-carpeta" href="{{ route('archivador.carpeta', $sub) }}">
                        <span class="arc-carpeta__icono">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
                            </svg>
                        </span>
                        <span class="arc-carpeta__datos">
                            <span class="arc-carpeta__nombre">{{ $sub->nombre }}</span>
                            <span class="arc-carpeta__meta">
                                @if (isset($sub->archivos_count))
                                    {{ $sub->archivos_count }} archivo(s)
                                    @if ($sub->subcarpetas_count > 0)
                                        &middot; {{ $sub->subcarpetas_count }} carpeta(s)
                                    @endif
                                @else
                                    Abrir carpeta
                                @endif
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Archivos --}}
        @if ($archivos->count() > 0)
            <div class="arc-tabla-wrap">
                <table class="arc-tabla">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            @if ($buscar !== '')<th>Carpeta</th>@endif
                            <th>Tamano</th>
                            <th>Subido</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archivos as $archivo)
                            <tr>
                                <td>
                                    <span class="arc-archivo">
                                        <span class="arc-archivo__icono arc-tipo--{{ $archivo->tipo() }}">
                                            {{ strtoupper(substr($archivo->extension ?: '?', 0, 4)) }}
                                        </span>
                                        <span>
                                            <span class="arc-archivo__nombre">{{ $archivo->nombre }}</span>
                                        </span>
                                    </span>
                                </td>
                                @if ($buscar !== '')
                                    <td class="arc-muted">
                                        @if ($archivo->carpeta)
                                            <a href="{{ route('archivador.carpeta', $archivo->carpeta) }}">
                                                {{ $archivo->carpeta->nombre }}
                                            </a>
                                        @else
                                            Raiz
                                        @endif
                                    </td>
                                @endif
                                <td class="arc-muted">{{ $archivo->pesoLegible() }}</td>
                                <td class="arc-muted">{{ $archivo->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="arc-right">
                                    <span class="arc-acciones">
                                        {{-- Solo iconos: el nombre va en el tooltip y para el lector de pantalla --}}
                                        <button type="button" class="arc-btn arc-btn--ghost arc-btn--icono js-ver"
                                                data-url="{{ route('archivador.archivos.previsualizar', $archivo) }}"
                                                title="Ver" aria-label="Ver {{ $archivo->nombre }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>

                                        <a class="arc-btn arc-btn--ghost arc-btn--icono"
                                           href="{{ route('archivador.archivos.descargar', $archivo) }}"
                                           title="Descargar" aria-label="Descargar {{ $archivo->nombre }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                <path d="M7 10l5 5 5-5M12 15V3"/>
                                            </svg>
                                        </a>

                                        <button type="button" class="arc-btn arc-btn--ghost arc-btn--icono js-renombrar"
                                                data-url="{{ route('archivador.archivos.renombrar', $archivo) }}"
                                                data-nombre="{{ $archivo->nombre }}"
                                                title="Renombrar" aria-label="Renombrar {{ $archivo->nombre }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                                            </svg>
                                        </button>

                                        <form method="POST" class="arc-inline"
                                              action="{{ route('archivador.archivos.eliminar', $archivo) }}"
                                              onsubmit="return confirm('Eliminar &quot;{{ $archivo->nombre }}&quot;? No se puede deshacer.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="arc-btn arc-btn--danger arc-btn--icono"
                                                    title="Eliminar" aria-label="Eliminar {{ $archivo->nombre }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                    <path d="M10 11v6M14 11v6"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($archivos->hasPages())
                <div class="arc-pager">{{ $archivos->links() }}</div>
            @endif
        @elseif ($subcarpetas->isEmpty())
            <div class="arc-empty">
                <span class="arc-empty__icon">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
                    </svg>
                </span>
                <strong>{{ $buscar !== '' ? 'Sin resultados' : 'Esta carpeta esta vacia' }}</strong>
                {{ $buscar !== '' ? 'Prueba con otro nombre.' : 'Arrastra una carpeta de tu PC o crea una nueva.' }}
            </div>
        @endif

        {{-- Acciones sobre la carpeta actual --}}
        @if ($carpeta && $buscar === '')
            <div class="arc-card__head" style="border-top:1px solid var(--arc-line);border-bottom:0;">
                <div class="arc-toolbar__text">
                    Acciones sobre la carpeta <strong>{{ $carpeta->nombre }}</strong>
                </div>
                <div class="arc-toolbar__actions">
                    <button type="button" class="arc-btn arc-btn--ghost arc-btn--sm js-renombrar"
                            data-url="{{ route('archivador.carpetas.renombrar', $carpeta) }}"
                            data-nombre="{{ $carpeta->nombre }}">Renombrar carpeta</button>
                    @php $contenido = $carpeta->contenidoTotal(); @endphp
                    <form method="POST" action="{{ route('archivador.carpetas.eliminar', $carpeta) }}"
                          onsubmit="return confirm('Se eliminara la carpeta &quot;{{ $carpeta->nombre }}&quot; con {{ $contenido['archivos'] }} archivo(s) y {{ $contenido['carpetas'] }} subcarpeta(s). No se puede deshacer.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="arc-btn arc-btn--danger arc-btn--sm">Eliminar carpeta</button>
                    </form>
                </div>
            </div>
        @endif
    </section>

    {{-- Visor de archivos --}}
    <div class="arc-visor" id="visor" role="dialog" aria-modal="true" aria-labelledby="visorNombre" hidden>
        <div class="arc-visor__caja">
            <header class="arc-visor__head">
                <div class="arc-visor__titulo">
                    <strong id="visorNombre"></strong>
                    <span id="visorMeta"></span>
                </div>
                <div class="arc-visor__acciones">
                    <a class="arc-btn arc-btn--ghost arc-btn--sm" id="visorBajar" href="#">Descargar</a>
                    <button type="button" class="arc-btn arc-btn--ghost arc-btn--icono" id="visorCerrar"
                            aria-label="Cerrar vista previa">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </header>

            <div class="arc-visor__cuerpo" id="visorCuerpo">
                <p class="arc-visor__cargando">Cargando vista previa...</p>
            </div>
        </div>
    </div>

    {{-- Formulario reutilizable para renombrar --}}
    <form method="POST" id="formRenombrar" hidden>
        @csrf @method('PUT')
        <input type="hidden" name="nombre" id="inputRenombrar">
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ── Nueva carpeta ──────────────────────────────────────────────
    var btnNueva = document.getElementById('btnNuevaCarpeta');

    if (btnNueva) {
        btnNueva.addEventListener('click', function () {
            var nombre = window.prompt('Nombre de la nueva carpeta:');
            if (!nombre || !nombre.trim()) return;

            document.getElementById('inputNuevaCarpeta').value = nombre.trim();
            document.getElementById('formNuevaCarpeta').submit();
        });
    }

    // ── Renombrar carpeta o archivo ────────────────────────────────
    document.querySelectorAll('.js-renombrar').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var nombre = window.prompt('Nuevo nombre:', boton.dataset.nombre);
            if (!nombre || !nombre.trim()) return;

            var form = document.getElementById('formRenombrar');
            form.action = boton.dataset.url;
            document.getElementById('inputRenombrar').value = nombre.trim();
            form.submit();
        });
    });

    // ── Visor de archivos ──────────────────────────────────────────
    (function () {
        var visor = document.getElementById('visor');
        if (!visor) return;

        var cuerpo = document.getElementById('visorCuerpo');
        var nombre = document.getElementById('visorNombre');
        var meta = document.getElementById('visorMeta');
        var bajar = document.getElementById('visorBajar');
        var ultimoFoco = null;

        function abrir(url) {
            ultimoFoco = document.activeElement;
            visor.hidden = false;
            document.body.style.overflow = 'hidden';
            cuerpo.innerHTML = '<p class="arc-visor__cargando">Cargando vista previa...</p>';
            nombre.textContent = '';
            meta.textContent = '';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(pintar)
                .catch(function (e) {
                    cuerpo.innerHTML = '<p class="arc-visor__aviso">No se pudo cargar la vista previa (' +
                        e.message + ').</p>';
                });
        }

        function pintar(d) {
            nombre.textContent = d.nombre || '';
            meta.textContent = d.peso || '';
            bajar.href = d.bajar || '#';

            if (d.modo === 'nativo') {
                cuerpo.innerHTML = '';
                cuerpo.appendChild(elementoNativo(d));
                return;
            }

            if (d.modo === 'html') {
                // El servidor ya escapo el contenido al armar este HTML.
                cuerpo.innerHTML = '<div class="arc-prev">' + d.html + '</div>';
                return;
            }

            if (d.modo === 'texto') {
                cuerpo.innerHTML = '<pre class="arc-prev__texto"></pre>';
                cuerpo.querySelector('pre').textContent = descodificar(d.html);
                if (d.aviso) cuerpo.insertAdjacentHTML('afterbegin', aviso(d.aviso));
                return;
            }

            cuerpo.innerHTML = aviso(d.aviso || 'No se puede previsualizar este archivo.');
        }

        /** El servidor manda el texto ya escapado; aqui se vuelve a texto plano. */
        function descodificar(html) {
            var caja = document.createElement('textarea');
            caja.innerHTML = html;
            return caja.value;
        }

        function aviso(texto) {
            var p = document.createElement('p');
            p.className = 'arc-visor__aviso';
            p.textContent = texto;
            return p.outerHTML;
        }

        function elementoNativo(d) {
            var el;

            if (d.tipo === 'imagen') {
                el = document.createElement('img');
                el.className = 'arc-visor__imagen';
                el.alt = d.nombre;
            } else if (d.tipo === 'video') {
                el = document.createElement('video');
                el.className = 'arc-visor__video';
                el.controls = true;
            } else if (d.tipo === 'audio') {
                el = document.createElement('audio');
                el.className = 'arc-visor__audio';
                el.controls = true;
            } else {
                el = document.createElement('iframe');
                el.className = 'arc-visor__pdf';
                el.title = d.nombre;
            }

            el.src = d.url;
            return el;
        }

        function cerrar() {
            visor.hidden = true;
            document.body.style.overflow = '';
            // Corta la reproduccion de un video o audio al salir.
            cuerpo.innerHTML = '';
            if (ultimoFoco) ultimoFoco.focus();
        }

        document.querySelectorAll('.js-ver').forEach(function (boton) {
            boton.addEventListener('click', function () { abrir(boton.dataset.url); });
        });

        document.getElementById('visorCerrar').addEventListener('click', cerrar);

        visor.addEventListener('click', function (e) {
            if (e.target === visor) cerrar();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !visor.hidden) cerrar();
        });
    })();

    // ── Subida ─────────────────────────────────────────────────────
    var zona = document.getElementById('zonaSubida');
    if (!zona || !token) return;

    var url = zona.dataset.url;
    var limiteBytes = Number(zona.dataset.limite) * 1024 * 1024;

    var progreso = document.getElementById('progreso');
    var fill = document.getElementById('progresoFill');
    var texto = document.getElementById('progresoTexto');

    // PHP acepta pocos archivos por peticion (max_file_uploads), asi que las
    // carpetas grandes van en tandas cortas y sucesivas.
    var POR_TANDA = 15;

    document.getElementById('btnElegirCarpeta').addEventListener('click', function () {
        document.getElementById('inputCarpeta').click();
    });

    document.getElementById('btnElegirArchivos').addEventListener('click', function () {
        document.getElementById('inputArchivos').click();
    });

    document.getElementById('inputCarpeta').addEventListener('change', function (e) {
        subir(Array.prototype.slice.call(e.target.files));
    });

    document.getElementById('inputArchivos').addEventListener('change', function (e) {
        subir(Array.prototype.slice.call(e.target.files));
    });

    // Arrastrar y soltar sobre toda la ventana: el aviso solo aparece
    // mientras hay algo encima, asi no ocupa sitio el resto del tiempo.
    var soltar = document.getElementById('zonaSoltar');
    var arrastres = 0;

    /** Ignora el arrastre de texto o enlaces: solo interesan los archivos. */
    function traeArchivos(e) {
        var tipos = e.dataTransfer && e.dataTransfer.types;
        return tipos && Array.prototype.indexOf.call(tipos, 'Files') !== -1;
    }

    window.addEventListener('dragenter', function (e) {
        if (!traeArchivos(e)) return;
        e.preventDefault();
        arrastres++;
        soltar.hidden = false;
    });

    window.addEventListener('dragover', function (e) {
        if (traeArchivos(e)) e.preventDefault();
    });

    // dragleave salta tambien al pasar de un hijo a otro: se cuenta para
    // ocultar el aviso solo cuando el puntero sale de verdad.
    window.addEventListener('dragleave', function () {
        arrastres = Math.max(0, arrastres - 1);
        if (arrastres === 0) soltar.hidden = true;
    });

    window.addEventListener('drop', function (e) {
        if (!traeArchivos(e)) return;
        e.preventDefault();
        arrastres = 0;
        soltar.hidden = true;

        var items = e.dataTransfer.items;

        // Una carpeta soltada solo se recorre con la API de entradas.
        if (items && items.length && items[0].webkitGetAsEntry) {
            var entradas = [];
            for (var i = 0; i < items.length; i++) {
                var entrada = items[i].webkitGetAsEntry();
                if (entrada) entradas.push(entrada);
            }
            recorrer(entradas).then(subir);
            return;
        }

        subir(Array.prototype.slice.call(e.dataTransfer.files));
    });

    /** Baja por el arbol soltado y devuelve los archivos con su ruta. */
    function recorrer(entradas) {
        var archivos = [];

        function leerEntrada(entrada, prefijo) {
            return new Promise(function (resolve) {
                if (entrada.isFile) {
                    entrada.file(function (file) {
                        file.rutaRelativa = prefijo + file.name;
                        archivos.push(file);
                        resolve();
                    }, resolve);
                    return;
                }

                var lector = entrada.createReader();
                var hijos = [];

                (function leerTanda() {
                    lector.readEntries(function (lote) {
                        if (!lote.length) {
                            Promise.all(hijos.map(function (h) {
                                return leerEntrada(h, prefijo + entrada.name + '/');
                            })).then(resolve);
                            return;
                        }
                        hijos = hijos.concat(Array.prototype.slice.call(lote));
                        leerTanda();
                    }, resolve);
                })();
            });
        }

        return Promise.all(entradas.map(function (e) {
            return leerEntrada(e, '');
        })).then(function () {
            return archivos;
        });
    }

    function subir(archivos) {
        if (!archivos.length) return;

        var grandes = archivos.filter(function (f) { return f.size > limiteBytes; });
        archivos = archivos.filter(function (f) { return f.size <= limiteBytes; });

        if (grandes.length) {
            window.alert(
                grandes.length + ' archivo(s) superan el limite de ' + zona.dataset.limite +
                ' MB y no se subiran:\n\n' +
                grandes.slice(0, 10).map(function (f) { return '- ' + f.name; }).join('\n')
            );
        }

        if (!archivos.length) return;

        progreso.hidden = false;
        bloquear(true);

        var total = archivos.length;
        var hechos = 0;
        var rechazados = [];

        function siguienteTanda() {
            if (!archivos.length) {
                texto.textContent = 'Listo. Actualizando...';

                if (rechazados.length) {
                    window.alert('No se subieron:\n\n' + rechazados.slice(0, 15).join('\n'));
                }

                window.location.reload();
                return;
            }

            var tanda = archivos.splice(0, POR_TANDA);
            var datos = new FormData();
            datos.append('_token', token);

            tanda.forEach(function (file, i) {
                datos.append('archivos[]', file);
                // webkitRelativePath para el selector; rutaRelativa para el arrastre.
                datos.append('rutas[' + i + ']', file.rutaRelativa || file.webkitRelativePath || '');
            });

            fetch(url, {
                method: 'POST',
                body: datos,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    hechos += tanda.length;
                    if (data.rechazados) rechazados = rechazados.concat(data.rechazados);
                    pintar(hechos, total);
                    siguienteTanda();
                })
                .catch(function (error) {
                    bloquear(false);
                    progreso.hidden = true;
                    window.alert('La subida se interrumpio: ' + error.message);
                });
        }

        pintar(0, total);
        siguienteTanda();
    }

    function pintar(hechos, total) {
        var pct = Math.round((hechos / total) * 100);
        fill.style.width = pct + '%';
        texto.textContent = 'Subiendo ' + hechos + ' de ' + total + ' archivo(s)... ' + pct + '%';
    }

    function bloquear(si) {
        document.getElementById('btnElegirCarpeta').disabled = si;
        document.getElementById('btnElegirArchivos').disabled = si;
    }
})();
</script>
@endpush
