@extends('layouts.mantis')

@section('title', 'Biblioteca | R.A.S')
@section('page-title', 'Biblioteca')
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
            <a href="{{ route('biblioteca.libros') }}" class="bib-btn bib-btn--borde">Catalogo de libros</a>
                @can('biblioteca.lectura_registrar')
                    <a href="{{ route('biblioteca.lecturas.crear') }}" class="bib-btn bib-btn--teal">Nueva ficha de lectura</a>
                @endcan
        </span>
    </div>

    @if (session('status'))
        <div class="bib-aviso bib-aviso--info">{{ session('status') }}</div>
    @endif

    <div class="bib-estante">
        <div class="bib-lomo">
            <div class="bib-lomo__num">{{ $resumen['total'] }}</div>
            <div class="bib-lomo__etq">Fichas registradas</div>
        </div>
        <div class="bib-lomo es-cielo">
            <div class="bib-lomo__num">{{ $resumen['en_curso'] }}</div>
            <div class="bib-lomo__etq">Lecturas en curso</div>
        </div>
        <div class="bib-lomo es-verde">
            <div class="bib-lomo__num">{{ $resumen['terminadas'] }}</div>
            <div class="bib-lomo__etq">Lecturas terminadas</div>
        </div>
        <div class="bib-lomo es-hondo">
            <div class="bib-lomo__num">{{ $resumen['libros'] }}</div>
            <div class="bib-lomo__etq">Libros en catalogo</div>
        </div>
    </div>

    <section class="bib-bloque">
        <div class="bib-bloque__head">
            <h2>Seguimiento de lectura</h2>
            <p>Filtra por nivel o grado para revisar el avance del aula.</p>
        </div>

        <div class="bib-bloque__body" style="padding-bottom:16px">
            <form method="GET" action="{{ route('biblioteca.index') }}" class="bib-filtros">
                <div>
                    <label class="bib-etiqueta" for="f_nivel">Nivel</label>
                    <select id="f_nivel" name="nivel" class="bib-select">
                        <option value="">Todos los niveles</option>
                        @foreach (['Inicial', 'Primaria', 'Secundaria'] as $n)
                            <option value="{{ $n }}" @selected($nivel === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="bib-etiqueta" for="f_grado">Grado</label>
                    <select id="f_grado" name="grado_id" class="bib-select">
                        <option value="">Todos los grados</option>
                        @foreach ($grados as $g)
                            <option value="{{ $g->id }}" @selected((string) $gradoId === (string) $g->id)>{{ $g->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bib-fin-campo">
                    <button type="submit" class="bib-btn bib-btn--tinta">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="bib-tabla-wrap">
            <table class="bib-tabla">
                <thead>
                    <tr>
                        <th>Lector</th>
                        <th>Obra</th>
                        <th>Editorial</th>
                        <th>Tiempo</th>
                        <th>Ficha</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lecturas as $lectura)
                        <tr>
                            <td>
                                <div class="bib-lector">
                                    <span class="bib-lector__marca"></span>
                                    <span class="bib-lector__nombre">
                                        {{ $lectura->estudiante?->apellidos }}, {{ $lectura->estudiante?->nombres }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="bib-titulo-libro">{{ $lectura->titulo }}</div>
                                <div class="bib-autor">{{ $lectura->autor ?: 'Autor no indicado' }}</div>
                            </td>
                            <td>{{ $lectura->editorial ?: '--' }}</td>
                            <td>{{ $lectura->tiempo_lectura ? $lectura->tiempo_lectura . ' min' : '--' }}</td>
                            <td>
                                @if ($lectura->fichaCompleta())
                                    <span class="bib-sello bib-sello--fin">Completa</span>
                                @else
                                    <span class="bib-sello bib-sello--falta">Incompleta</span>
                                @endif
                            </td>
                            <td>
                                @if ($lectura->estado === 'terminado')
                                    <span class="bib-sello bib-sello--fin">Terminado</span>
                                @else
                                    <span class="bib-sello bib-sello--curso">En curso</span>
                                @endif
                            </td>
                            <td class="bib-derecha">
                                @can('biblioteca.lectura_registrar')
                                    <a href="{{ route('biblioteca.lecturas.editar', $lectura) }}"
                                       class="bib-btn bib-btn--borde bib-btn--mini">Ver ficha</a>
                                    <form method="POST" action="{{ route('biblioteca.lecturas.eliminar', $lectura) }}"
                                          class="bib-en-linea"
                                          onsubmit="return confirm('Eliminar esta ficha de lectura?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bib-btn bib-btn--borde bib-btn--mini">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="bib-vacio">
                                <div class="bib-vacio__titulo">El registro esta vacio</div>
                                Aun no hay fichas de lectura con este filtro.
                                @can('biblioteca.lectura_registrar')
                                    <div style="margin-top:16px">
                                        <a href="{{ route('biblioteca.lecturas.crear') }}" class="bib-btn bib-btn--teal">
                                            Registrar la primera lectura
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lecturas->hasPages())
            <div class="bib-bloque__pie">{{ $lecturas->links() }}</div>
        @endif
    </section>
@endsection
