@extends('layouts.mantis')

@section('title', 'Ficha de lectura | R.A.S')
@section('page-title', 'Ficha de lectura')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/biblioteca.css') }}?v={{ filemtime(public_path('css/biblioteca.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @php $esEdicion = $lectura->exists; @endphp

    <div class="bib-barra">
        <span class="bib-barra__acciones">
            <a href="{{ route('biblioteca.index') }}" class="bib-btn bib-btn--borde">Volver</a>
        </span>
    </div>

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

    <form method="POST"
          action="{{ $esEdicion ? route('biblioteca.lecturas.actualizar', $lectura) : route('biblioteca.lecturas.guardar') }}">
        @csrf
        @if ($esEdicion)
            @method('PUT')
        @endif

        <div class="bib-ficha">
            <div class="bib-ficha__titulo">Ficha de resumen de lectura</div>
            <div class="bib-ficha__sub">
                {{ $esEdicion ? 'Registro N.° ' . $lectura->id : 'Registro nuevo' }}
            </div>

            <div class="bib-rejilla">
                <div class="bib-c6">
                    <label class="bib-etiqueta" for="estudiante_id">Lector</label>
                    <select id="estudiante_id" name="estudiante_id" class="bib-select" required>
                        <option value="">Selecciona un estudiante</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}" @selected(old('estudiante_id', $lectura->estudiante_id) == $e->id)>
                                {{ $e->apellidos }}, {{ $e->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="bib-c6">
                    <label class="bib-etiqueta" for="libro_id">Obra del catalogo (opcional)</label>
                    <select id="libro_id" name="libro_id" class="bib-select">
                        <option value="">Lectura libre / fuera del catalogo</option>
                        @foreach ($libros as $l)
                            <option value="{{ $l->id }}"
                                    data-titulo="{{ $l->titulo }}"
                                    data-autor="{{ $l->autor }}"
                                    data-editorial="{{ $l->editorial }}"
                                    @selected(old('libro_id', $lectura->libro_id) == $l->id)>
                                {{ $l->titulo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="bib-c6">
                    <label class="bib-etiqueta" for="titulo">Titulo leido</label>
                    <input id="titulo" type="text" name="titulo" class="bib-input" required maxlength="200"
                           value="{{ old('titulo', $lectura->titulo) }}">
                </div>

                <div class="bib-c3">
                    <label class="bib-etiqueta" for="autor">Autor</label>
                    <input id="autor" type="text" name="autor" class="bib-input"
                           value="{{ old('autor', $lectura->autor) }}">
                </div>

                <div class="bib-c3">
                    <label class="bib-etiqueta" for="editorial">Editorial</label>
                    <input id="editorial" type="text" name="editorial" class="bib-input"
                           value="{{ old('editorial', $lectura->editorial) }}">
                </div>

                <div class="bib-c3">
                    <label class="bib-etiqueta" for="tiempo_lectura">Tiempo de lectura (min)</label>
                    <input id="tiempo_lectura" type="number" name="tiempo_lectura" class="bib-input" min="1"
                           value="{{ old('tiempo_lectura', $lectura->tiempo_lectura) }}">
                </div>

                <div class="bib-c3">
                    <label class="bib-etiqueta" for="fecha_inicio">Fecha de inicio</label>
                    <input id="fecha_inicio" type="date" name="fecha_inicio" class="bib-input"
                           value="{{ old('fecha_inicio', $lectura->fecha_inicio?->format('Y-m-d')) }}">
                </div>

                <div class="bib-c3">
                    <label class="bib-etiqueta" for="fecha_fin">Fecha de fin</label>
                    <input id="fecha_fin" type="date" name="fecha_fin" class="bib-input"
                           value="{{ old('fecha_fin', $lectura->fecha_fin?->format('Y-m-d')) }}">
                </div>

                <div class="bib-c3">
                    <label class="bib-etiqueta" for="estado">Estado</label>
                    <select id="estado" name="estado" class="bib-select" required>
                        <option value="en_curso" @selected(old('estado', $lectura->estado) === 'en_curso')>En curso</option>
                        <option value="terminado" @selected(old('estado', $lectura->estado) === 'terminado')>Terminado</option>
                    </select>
                </div>

                <div class="bib-c12">
                    <label class="bib-etiqueta" for="resumen">Resumen del contenido</label>
                    <textarea id="resumen" name="resumen" class="bib-area bib-renglones"
                              rows="7">{{ old('resumen', $lectura->resumen) }}</textarea>
                </div>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('biblioteca.index') }}" class="bib-btn bib-btn--borde">Cancelar</a>
            <button type="submit" class="bib-btn bib-btn--teal">
                {{ $esEdicion ? 'Actualizar ficha' : 'Registrar ficha' }}
            </button>
        </div>
    </form>

    <script>
        // Al elegir una obra del catalogo se completan titulo, autor y editorial;
        // el usuario puede corregirlos despues.
        document.getElementById('libro_id').addEventListener('change', function () {
            var opcion = this.options[this.selectedIndex];
            if (!opcion.value) return;

            document.getElementById('titulo').value = opcion.dataset.titulo || '';
            document.getElementById('autor').value = opcion.dataset.autor || '';
            document.getElementById('editorial').value = opcion.dataset.editorial || '';
        });
    </script>
@endsection
