@extends('layouts.mantis')

@section('title', 'Admision - Paso 1 | R.A.S')
@section('page-title', 'Nueva postulacion')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="ad-page">
    <div class="ad-toolbar">
        <div class="ad-toolbar__lead">
            <div>
                <div class="ad-toolbar__kicker">Paso 1 de 5</div>
                <div class="ad-toolbar__text">Con estos datos se genera el codigo del expediente.</div>
            </div>
        </div>
        <div class="ad-toolbar__actions">
            <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">
                <i data-feather="arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    @include('admision._stepper')
    @include('admision._alertas')

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon"><i data-feather="user"></i></span>
                <div>
                    <h2>Datos del postulante</h2>
                    <p>La sede debe atender el nivel elegido.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admision.paso1.guardar') }}">
            @csrf
            <div class="ad-card__body ad-grid">
                <div class="ad-field c-4">
                    <label for="nombres">Nombres</label>
                    <input id="nombres" type="text" name="nombres" class="ad-input" required maxlength="120"
                           value="{{ old('nombres') }}" autofocus>
                </div>
                <div class="ad-field c-4">
                    <label for="apellidos">Apellidos</label>
                    <input id="apellidos" type="text" name="apellidos" class="ad-input" required maxlength="120"
                           value="{{ old('apellidos') }}">
                </div>
                <div class="ad-field c-2">
                    <label for="dni">DNI</label>
                    <input id="dni" type="text" name="dni" class="ad-input" maxlength="20" value="{{ old('dni') }}">
                </div>
                <div class="ad-field c-2">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" class="ad-select">
                        <option value="">--</option>
                        <option value="M" @selected(old('sexo') === 'M')>Masculino</option>
                        <option value="F" @selected(old('sexo') === 'F')>Femenino</option>
                    </select>
                </div>

                <div class="ad-field c-3">
                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" class="ad-input"
                           value="{{ old('fecha_nacimiento') }}">
                </div>
                <div class="ad-field c-5">
                    <label for="direccion">Direccion</label>
                    <input id="direccion" type="text" name="direccion" class="ad-input" value="{{ old('direccion') }}">
                </div>
                <div class="ad-field c-4">
                    <label for="colegio_procedencia">Colegio de procedencia</label>
                    <input id="colegio_procedencia" type="text" name="colegio_procedencia" class="ad-input"
                           value="{{ old('colegio_procedencia') }}">
                </div>

                <div class="ad-field c-4">
                    <label for="sede_id">Sede a la que postula</label>
                    <select id="sede_id" name="sede_id" class="ad-select">
                        <option value="">Sin definir</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}" @selected(old('sede_id') == $sede->id)>
                                {{ $sede->nombre }} ({{ implode(', ', $sede->niveles ?? []) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ad-field c-3">
                    <label for="nivel">Nivel</label>
                    <select id="nivel" name="nivel" class="ad-select" required>
                        @foreach ($niveles as $n)
                            <option value="{{ $n }}" @selected(old('nivel') === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ad-field c-5">
                    <label for="grado_id">Grado</label>
                    <select id="grado_id" name="grado_id" class="ad-select">
                        <option value="">Sin definir</option>
                        @foreach ($grados as $g)
                            <option value="{{ $g->id }}" data-nivel="{{ $g->nivel }}"
                                    @selected(old('grado_id') == $g->id)>{{ $g->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="ad-card__foot">
                <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">Cancelar</a>
                <button type="submit" class="ad-btn ad-btn--navy">
                    Continuar al paso 2 <i data-feather="arrow-right"></i>
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    // El selector de grado solo ofrece los grados del nivel elegido.
    (function () {
        var nivel = document.getElementById('nivel');
        var grado = document.getElementById('grado_id');
        var opciones = Array.prototype.slice.call(grado.options);

        function filtrar() {
            grado.innerHTML = '';
            opciones.forEach(function (op) {
                if (!op.value || op.dataset.nivel === nivel.value) {
                    grado.appendChild(op);
                }
            });
        }

        nivel.addEventListener('change', filtrar);
        filtrar();
    })();
</script>
@endsection
