@extends('layouts.mantis')

@section('title', 'Catalogo de biblioteca | R.A.S')
@section('page-title', 'Catalogo de biblioteca')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/biblioteca.css') }}?v={{ filemtime(public_path('css/biblioteca.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="bib-barra">
        <span class="bib-barra__acciones">
            <a href="{{ route('biblioteca.index') }}" class="bib-btn bib-btn--borde">Volver al seguimiento</a>
        </span>
    </div>

    @if (session('status'))
        <div class="bib-aviso bib-aviso--info">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="bib-aviso bib-aviso--error">
            <strong>Revisa lo siguiente:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('biblioteca.gestionar')
        <section class="bib-bloque">
            <div class="bib-bloque__head">
                <h2>Agregar obra al catalogo</h2>
                <p>El nivel se deduce del grado que asignes.</p>
            </div>

            <form method="POST" action="{{ route('biblioteca.libros.guardar') }}">
                @csrf
                <div class="bib-bloque__body bib-rejilla">
                    <div class="bib-c4">
                        <label class="bib-etiqueta" for="titulo">Titulo</label>
                        <input id="titulo" type="text" name="titulo" class="bib-input" required maxlength="200"
                               value="{{ old('titulo') }}">
                    </div>
                    <div class="bib-c3">
                        <label class="bib-etiqueta" for="autor">Autor</label>
                        <input id="autor" type="text" name="autor" class="bib-input" value="{{ old('autor') }}">
                    </div>
                    <div class="bib-c3">
                        <label class="bib-etiqueta" for="editorial">Editorial</label>
                        <input id="editorial" type="text" name="editorial" class="bib-input" value="{{ old('editorial') }}">
                    </div>
                    <div class="bib-c2">
                        <label class="bib-etiqueta" for="codigo">Codigo</label>
                        <input id="codigo" type="text" name="codigo" class="bib-input" value="{{ old('codigo') }}">
                    </div>
                    <div class="bib-c5">
                        <label class="bib-etiqueta" for="grado_id">Grado</label>
                        <select id="grado_id" name="grado_id" class="bib-select">
                            <option value="">Sin grado asignado</option>
                            @foreach ($grados as $g)
                                <option value="{{ $g->id }}" @selected(old('grado_id') == $g->id)>
                                    {{ $g->nivel }} &mdash; {{ $g->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bib-c3">
                        <label class="bib-etiqueta" for="ejemplares">Ejemplares</label>
                        <input id="ejemplares" type="number" name="ejemplares" class="bib-input"
                               value="{{ old('ejemplares', 1) }}" min="1" max="9999" required>
                    </div>
                </div>

                <div class="bib-bloque__pie">
                    <button type="submit" class="bib-btn bib-btn--teal">Guardar obra</button>
                </div>
            </form>
        </section>
    @endcan

    <section class="bib-bloque">
        <div class="bib-bloque__head">
            <h2>Obras registradas</h2>
            <p>{{ $libros->total() }} titulo(s) en el catalogo.</p>
        </div>

        <div class="bib-bloque__body" style="padding-bottom:16px">
            <form method="GET" action="{{ route('biblioteca.libros') }}" class="bib-filtros">
                <div>
                    <label class="bib-etiqueta" for="q">Buscar</label>
                    <input id="q" type="text" name="q" class="bib-input" placeholder="Titulo, autor o editorial"
                           value="{{ $buscar }}">
                </div>
                <div class="bib-fin-campo">
                    <button type="submit" class="bib-btn bib-btn--tinta">Buscar</button>
                </div>
            </form>
        </div>

        <div class="bib-tabla-wrap">
            <table class="bib-tabla">
                <thead>
                    <tr>
                        <th>Obra</th>
                        <th>Editorial</th>
                        <th>Nivel / grado</th>
                        <th>Codigo</th>
                        <th>Ejemplares</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($libros as $libro)
                        <tr>
                            <td>
                                <div class="bib-titulo-libro">{{ $libro->titulo }}</div>
                                <div class="bib-autor">{{ $libro->autor ?: 'Autor no indicado' }}</div>
                            </td>
                            <td>{{ $libro->editorial ?: '--' }}</td>
                            <td>
                                <span class="bib-sello bib-sello--neutro">
                                    {{ $libro->grado?->nombre ?? ($libro->nivel ?: 'General') }}
                                </span>
                            </td>
                            <td>{{ $libro->codigo ?: '--' }}</td>
                            <td>{{ $libro->ejemplares }}</td>
                            <td class="bib-derecha">
                                @can('biblioteca.gestionar')
                                    <form method="POST" action="{{ route('biblioteca.libros.eliminar', $libro) }}"
                                          class="bib-en-linea"
                                          onsubmit="return confirm('Eliminar esta obra del catalogo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bib-btn bib-btn--borde bib-btn--mini">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="bib-vacio">
                                <div class="bib-vacio__titulo">Catalogo vacio</div>
                                Todavia no se ha registrado ninguna obra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($libros->hasPages())
            <div class="bib-bloque__pie">{{ $libros->links() }}</div>
        @endif
    </section>
@endsection
