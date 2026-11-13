@extends('layouts.sitio')

@section('title', 'Inscripcion ' . ($campana->nombre ?? ''))
@section('meta', 'Inscribe a tu hijo o hija en el proceso de admision.')

@section('content')
@php
    $anio = $campana->nombre ?? null;
    $lema = $institucion->lema ?: 'La educacion que sonaste para ti, ahora para ellos';
@endphp

<section class="sw-page">
    <div class="sw-shell">
        <div class="sw-wizard">

            <div class="sw-wizard__head">
                <p class="sw-wizard__kicker">Admisiones {{ $anio ?? '' }}</p>
                <h1 class="sw-wizard__title">Inscripcion</h1>

                @if ($campana)
                    <div class="sw-progress">
                        <div class="sw-progress__track">
                            <span class="sw-progress__fill" id="swProgreso"></span>
                        </div>
                        <div class="sw-progress__meta">
                            <span id="swPasoTexto">Paso 1 de 5</span>
                            <span id="swPorcentaje">0% completo</span>
                        </div>
                    </div>
                @endif
            </div>

            @if (!$campana)
                <div class="sw-wizard__card">
                    <div class="sw-wizard__body">
                        <div class="sw-alert sw-alert--warn">
                            <strong>Las inscripciones estan cerradas por el momento.</strong>
                            <p style="margin:6px 0 0;">
                                Aun no hay una campana de admision publicada. Escribenos y te avisamos apenas se abra.
                            </p>
                        </div>
                        <a class="sw-btn sw-btn--dark" style="width:100%;justify-content:center;"
                            href="{{ route('sitio.inicio') }}#admision">Contactar al colegio</a>
                    </div>
                </div>
            @else
                <div class="sw-wizard__card">

                    {{-- Portada de la campana --}}
                    @if ($banner)
                        <div class="sw-wizard__banner">
                            <img src="{{ url($banner) }}" alt="Admision {{ $anio }}">
                        </div>
                    @else
                        <div class="sw-wizard__banner sw-wizard__banner--dibujado">
                            <img class="sw-wizard__escudo" src="{{ url('images/logo-ras.svg') }}"
                                alt="{{ $institucion->nombre ?: 'R.A.S' }}">
                            <span class="sw-wizard__banner-text">
                                <strong>Admision {{ $anio }}</strong>
                                <span>{{ $lema }}</span>
                            </span>
                        </div>
                    @endif

                    <div class="sw-wizard__body">

                        @if ($campana->admision_mensaje)
                            <div class="sw-alert sw-alert--info">{{ $campana->admision_mensaje }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="sw-alert sw-alert--bad">
                                <strong>Revisa los datos marcados:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('preinscripcion.guardar') }}" id="formInscripcion">
                            @csrf

                            {{-- Trampa para bots --}}
                            <div class="sw-hp" aria-hidden="true">
                                <label for="website">No llenar</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            {{-- ══ Paso 1: local ══ --}}
                            <section class="sw-step is-on" data-paso="1">
                                <h2 class="sw-step__title">
                                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    Seleccionar local de postulacion
                                </h2>

                                <div class="sw-field">
                                    <label for="sede_id">Local <span class="req">*</span></label>
                                    <select id="sede_id" name="sede_id" required data-paso-campo="1"
                                        class="{{ $errors->has('sede_id') ? 'is-bad' : '' }}">
                                        <option value="">Selecciona un local</option>
                                        @foreach ($sedes as $sede)
                                            <option value="{{ $sede->id }}"
                                                data-niveles="{{ implode(',', $sede->niveles ?? []) }}"
                                                data-nombre="{{ $sede->etiquetaPublica() }}"
                                                @selected((string) old('sede_id') === (string) $sede->id)>
                                                {{ $sede->etiquetaPublica() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sede_id')
                                        <span class="sw-field__error">{{ $message }}</span>
                                    @else
                                        <span class="sw-field__hint" id="sedeDireccion"></span>
                                    @enderror
                                </div>

                                @foreach ($sedes as $sede)
                                    @if ($sede->direccion)
                                        <template data-sede="{{ $sede->id }}">{{ $sede->direccion }}</template>
                                    @endif
                                @endforeach
                            </section>

                            {{-- ══ Paso 2: nivel y grado ══ --}}
                            <section class="sw-step" data-paso="2">
                                <h2 class="sw-step__title">
                                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 10 12 5 2 10l10 5 10-5z"/>
                                        <path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/>
                                    </svg>
                                    Nivel y grado al que postula
                                </h2>
                                <p class="sw-step__intro" id="nivelIntro"></p>

                                <div class="sw-grid">
                                    <div class="sw-field sw-col-12">
                                        <label for="nivel">Nivel <span class="req">*</span></label>
                                        <select id="nivel" name="nivel" required data-paso-campo="2"
                                            class="{{ $errors->has('nivel') ? 'is-bad' : '' }}">
                                            <option value="">Selecciona un nivel</option>
                                            @foreach ($niveles as $n)
                                                <option value="{{ $n }}" @selected(old('nivel') === $n)>{{ $n }}</option>
                                            @endforeach
                                        </select>
                                        @error('nivel')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-12">
                                        <label for="grado_id">Grado <span class="req">*</span></label>
                                        <select id="grado_id" name="grado_id" required data-paso-campo="2"
                                            class="{{ $errors->has('grado_id') ? 'is-bad' : '' }}">
                                            <option value="">Primero elige el nivel</option>
                                            @foreach ($grados as $grado)
                                                <option value="{{ $grado->id }}" data-nivel="{{ $grado->nivel }}"
                                                    @selected((string) old('grado_id') === (string) $grado->id)>
                                                    {{ $grado->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('grado_id')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </section>

                            {{-- ══ Paso 3: postulante ══ --}}
                            <section class="sw-step" data-paso="3">
                                <h2 class="sw-step__title">
                                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Datos del postulante
                                </h2>

                                <div class="sw-grid">
                                    <div class="sw-field sw-col-6">
                                        <label for="nombres">Nombres <span class="req">*</span></label>
                                        <input id="nombres" type="text" name="nombres" value="{{ old('nombres') }}"
                                            maxlength="120" required data-paso-campo="3"
                                            class="{{ $errors->has('nombres') ? 'is-bad' : '' }}">
                                        @error('nombres')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="apellidos">Apellidos <span class="req">*</span></label>
                                        <input id="apellidos" type="text" name="apellidos" value="{{ old('apellidos') }}"
                                            maxlength="120" required data-paso-campo="3"
                                            class="{{ $errors->has('apellidos') ? 'is-bad' : '' }}">
                                        @error('apellidos')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="dni">DNI <span class="req">*</span></label>
                                        <input id="dni" type="text" name="dni" value="{{ old('dni') }}"
                                            inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required
                                            data-paso-campo="3" class="{{ $errors->has('dni') ? 'is-bad' : '' }}">
                                        @error('dni')
                                            <span class="sw-field__error">{{ $message }}</span>
                                        @else
                                            <span class="sw-field__hint">8 digitos, sin puntos ni guiones.</span>
                                        @enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="sexo">Sexo <span class="req">*</span></label>
                                        <select id="sexo" name="sexo" required data-paso-campo="3"
                                            class="{{ $errors->has('sexo') ? 'is-bad' : '' }}">
                                            <option value="">Selecciona...</option>
                                            <option value="M" @selected(old('sexo') === 'M')>Masculino</option>
                                            <option value="F" @selected(old('sexo') === 'F')>Femenino</option>
                                        </select>
                                        @error('sexo')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-12">
                                        <label for="fecha_nacimiento">Fecha de nacimiento <span class="req">*</span></label>
                                        <input id="fecha_nacimiento" type="date" name="fecha_nacimiento"
                                            value="{{ old('fecha_nacimiento') }}"
                                            max="{{ now()->subDay()->toDateString() }}" required data-paso-campo="3"
                                            class="{{ $errors->has('fecha_nacimiento') ? 'is-bad' : '' }}">
                                        @error('fecha_nacimiento')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-12">
                                        <label for="colegio_procedencia">Colegio de procedencia</label>
                                        <input id="colegio_procedencia" type="text" name="colegio_procedencia"
                                            value="{{ old('colegio_procedencia') }}" maxlength="180"
                                            placeholder="Si viene de otra institucion">
                                    </div>

                                    <div class="sw-field sw-col-12">
                                        <label for="direccion">Direccion del domicilio</label>
                                        <input id="direccion" type="text" name="direccion"
                                            value="{{ old('direccion') }}" maxlength="255">
                                    </div>
                                </div>
                            </section>

                            {{-- ══ Paso 4: apoderado ══ --}}
                            <section class="sw-step" data-paso="4">
                                <h2 class="sw-step__title">
                                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>
                                    </svg>
                                    Datos del apoderado
                                </h2>
                                <p class="sw-step__intro">Por estos datos te contactara secretaria.</p>

                                <div class="sw-grid">
                                    <div class="sw-field sw-col-6">
                                        <label for="apoderado_parentesco">Parentesco <span class="req">*</span></label>
                                        <select id="apoderado_parentesco" name="apoderado_parentesco" required
                                            data-paso-campo="4"
                                            class="{{ $errors->has('apoderado_parentesco') ? 'is-bad' : '' }}">
                                            <option value="">Selecciona...</option>
                                            @foreach (['Madre', 'Padre', 'Tutor'] as $rel)
                                                <option value="{{ $rel }}" @selected(old('apoderado_parentesco') === $rel)>
                                                    {{ $rel }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('apoderado_parentesco')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="apoderado_dni">DNI <span class="req">*</span></label>
                                        <input id="apoderado_dni" type="text" name="apoderado_dni"
                                            value="{{ old('apoderado_dni') }}" inputmode="numeric" pattern="[0-9]{8}"
                                            maxlength="8" required data-paso-campo="4"
                                            class="{{ $errors->has('apoderado_dni') ? 'is-bad' : '' }}">
                                        @error('apoderado_dni')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="apoderado_nombres">Nombres <span class="req">*</span></label>
                                        <input id="apoderado_nombres" type="text" name="apoderado_nombres"
                                            value="{{ old('apoderado_nombres') }}" maxlength="120" required
                                            data-paso-campo="4"
                                            class="{{ $errors->has('apoderado_nombres') ? 'is-bad' : '' }}">
                                        @error('apoderado_nombres')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="apoderado_apellidos">Apellidos <span class="req">*</span></label>
                                        <input id="apoderado_apellidos" type="text" name="apoderado_apellidos"
                                            value="{{ old('apoderado_apellidos') }}" maxlength="120" required
                                            data-paso-campo="4"
                                            class="{{ $errors->has('apoderado_apellidos') ? 'is-bad' : '' }}">
                                        @error('apoderado_apellidos')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="apoderado_telefono">Telefono / WhatsApp <span class="req">*</span></label>
                                        <input id="apoderado_telefono" type="tel" name="apoderado_telefono"
                                            value="{{ old('apoderado_telefono') }}" maxlength="40" required
                                            placeholder="9XX XXX XXX" data-paso-campo="4"
                                            class="{{ $errors->has('apoderado_telefono') ? 'is-bad' : '' }}">
                                        @error('apoderado_telefono')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-6">
                                        <label for="apoderado_email">Correo electronico <span class="req">*</span></label>
                                        <input id="apoderado_email" type="email" name="apoderado_email"
                                            value="{{ old('apoderado_email') }}" maxlength="150" required
                                            data-paso-campo="4"
                                            class="{{ $errors->has('apoderado_email') ? 'is-bad' : '' }}">
                                        @error('apoderado_email')<span class="sw-field__error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="sw-field sw-col-12">
                                        <label for="apoderado_direccion">Direccion del apoderado</label>
                                        <input id="apoderado_direccion" type="text" name="apoderado_direccion"
                                            value="{{ old('apoderado_direccion') }}" maxlength="255">
                                    </div>
                                </div>
                            </section>

                            {{-- ══ Paso 5: confirmacion ══ --}}
                            <section class="sw-step" data-paso="5">
                                <h2 class="sw-step__title">
                                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 11l3 3L22 4"/>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                    </svg>
                                    Revisa y confirma
                                </h2>

                                <dl class="sw-resumen" id="swResumen"></dl>

                                <div class="sw-field">
                                    <label for="observacion">Comentario o consulta (opcional)</label>
                                    <textarea id="observacion" name="observacion" maxlength="500"
                                        placeholder="Hermanos en el colegio, necesidades educativas, horario de contacto...">{{ old('observacion') }}</textarea>
                                </div>

                                <label class="sw-check" style="margin-top:16px;">
                                    <input type="checkbox" name="acepta_terminos" value="1" required
                                        data-paso-campo="5" @checked(old('acepta_terminos'))>
                                    <span>
                                        Autorizo a {{ $institucion->nombre ?: 'R.A.S' }} a tratar estos datos
                                        con el unico fin de gestionar el proceso de admision y a contactarme por
                                        telefono, WhatsApp o correo. <span class="req">*</span>
                                    </span>
                                </label>
                                @error('acepta_terminos')<span class="sw-field__error">{{ $message }}</span>@enderror
                            </section>

                            {{-- Botonera --}}
                            <div class="sw-wizard__nav">
                                <button type="button" class="sw-btn sw-btn--claro sw-btn--atras" id="btnAtras" hidden>
                                    Atras
                                </button>
                                <button type="button" class="sw-btn sw-btn--dark" id="btnSiguiente">Siguiente</button>
                                <button type="submit" class="sw-btn sw-btn--gold" id="btnEnviar" hidden>
                                    Enviar inscripcion
                                </button>
                            </div>
                        </form>

                        <p class="sw-wizard__pie">
                            Esto reserva la vacante; la matricula se firma en el colegio.
                            &middot; <a href="{{ route('preinscripcion.consulta') }}">Ya me inscribi</a>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('formInscripcion');
        if (!form) return;

        var pasos = Array.prototype.slice.call(form.querySelectorAll('.sw-step'));
        var total = pasos.length;
        var actual = 1;

        var btnAtras = document.getElementById('btnAtras');
        var btnSiguiente = document.getElementById('btnSiguiente');
        var btnEnviar = document.getElementById('btnEnviar');
        var barra = document.getElementById('swProgreso');
        var textoPaso = document.getElementById('swPasoTexto');
        var textoPct = document.getElementById('swPorcentaje');

        var sede = document.getElementById('sede_id');
        var nivel = document.getElementById('nivel');
        var grado = document.getElementById('grado_id');

        // ── Navegacion ────────────────────────────────────────────
        function pintar() {
            pasos.forEach(function (seccion) {
                seccion.classList.toggle('is-on', Number(seccion.dataset.paso) === actual);
            });

            var avance = Math.round(((actual - 1) / total) * 100);
            barra.style.width = avance + '%';
            textoPaso.textContent = 'Paso ' + actual + ' de ' + total;
            textoPct.textContent = avance + '% completo';

            btnAtras.hidden = actual === 1;
            btnSiguiente.hidden = actual === total;
            btnEnviar.hidden = actual !== total;

            if (actual === total) llenarResumen();

            window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
        }

        /** Solo deja avanzar si los campos obligatorios del paso estan bien. */
        function pasoValido() {
            var campos = form.querySelectorAll('[data-paso-campo="' + actual + '"]');
            var ok = true;

            Array.prototype.forEach.call(campos, function (campo) {
                campo.classList.remove('is-bad');

                if (!campo.checkValidity()) {
                    campo.classList.add('is-bad');
                    if (ok) campo.reportValidity();
                    ok = false;
                }
            });

            return ok;
        }

        btnSiguiente.addEventListener('click', function () {
            if (!pasoValido()) return;
            if (actual < total) { actual++; pintar(); }
        });

        btnAtras.addEventListener('click', function () {
            if (actual > 1) { actual--; pintar(); }
        });

        // ── El local manda: define los niveles disponibles ─────────
        function nivelesDelLocal() {
            var op = sede.options[sede.selectedIndex];
            return op && op.value ? (op.dataset.niveles || '').split(',').filter(Boolean) : [];
        }

        function aplicarLocal() {
            var permitidos = nivelesDelLocal();
            var op = sede.options[sede.selectedIndex];

            // La direccion del local, como ayuda bajo el select.
            var hint = document.getElementById('sedeDireccion');
            if (hint) {
                var plantilla = op && op.value
                    ? form.querySelector('template[data-sede="' + op.value + '"]')
                    : null;
                hint.textContent = plantilla ? plantilla.textContent : '';
            }

            Array.prototype.forEach.call(nivel.options, function (opcion) {
                if (!opcion.value) return;
                var visible = permitidos.indexOf(opcion.value) !== -1;
                opcion.hidden = !visible;
                opcion.disabled = !visible;
                if (!visible && opcion.selected) nivel.value = '';
            });

            // Un local de un solo nivel no obliga a elegir.
            if (permitidos.length === 1) {
                nivel.value = permitidos[0];
            }

            var intro = document.getElementById('nivelIntro');
            if (intro) {
                intro.textContent = op && op.value
                    ? 'Estas postulando al ' + op.dataset.nombre + '.'
                    : '';
            }

            filtrarGrados();
        }

        function filtrarGrados() {
            var elegido = nivel.value;

            Array.prototype.forEach.call(grado.options, function (opcion) {
                if (!opcion.value) {
                    opcion.textContent = elegido ? 'Selecciona un grado' : 'Primero elige el nivel';
                    return;
                }
                var visible = opcion.dataset.nivel === elegido;
                opcion.hidden = !visible;
                opcion.disabled = !visible;
                if (!visible && opcion.selected) grado.value = '';
            });
        }

        sede.addEventListener('change', aplicarLocal);
        nivel.addEventListener('change', filtrarGrados);

        // ── Resumen del ultimo paso ───────────────────────────────
        function llenarResumen() {
            var texto = function (id) {
                var campo = document.getElementById(id);
                if (!campo) return '';
                if (campo.tagName === 'SELECT') {
                    var op = campo.options[campo.selectedIndex];
                    return op && op.value ? op.textContent.trim() : '';
                }
                return campo.value.trim();
            };

            var filas = [
                ['Local', texto('sede_id')],
                ['Nivel y grado', [texto('nivel'), texto('grado_id')].filter(Boolean).join(' - ')],
                ['Postulante', [texto('nombres'), texto('apellidos')].filter(Boolean).join(' ')],
                ['DNI', texto('dni')],
                ['Apoderado', [texto('apoderado_nombres'), texto('apoderado_apellidos')].filter(Boolean).join(' ')],
                ['Contacto', [texto('apoderado_telefono'), texto('apoderado_email')].filter(Boolean).join(' - ')]
            ];

            var caja = document.getElementById('swResumen');
            caja.textContent = '';

            filas.forEach(function (fila) {
                var bloque = document.createElement('div');
                var titulo = document.createElement('dt');
                var valor = document.createElement('dd');

                titulo.textContent = fila[0];
                valor.textContent = fila[1] || '--';

                bloque.appendChild(titulo);
                bloque.appendChild(valor);
                caja.appendChild(bloque);
            });
        }

        // ── Detalles ──────────────────────────────────────────────
        ['dni', 'apoderado_dni'].forEach(function (id) {
            var campo = document.getElementById(id);
            if (!campo) return;
            campo.addEventListener('input', function () {
                campo.value = campo.value.replace(/\D+/g, '').slice(0, 8);
            });
        });

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            btnEnviar.disabled = true;
            btnEnviar.textContent = 'Enviando...';
        });

        // Si el servidor devolvio errores, abre el primer paso que los tiene.
        var primerMal = form.querySelector('.is-bad[data-paso-campo]');
        if (primerMal) actual = Number(primerMal.dataset.pasoCampo);

        aplicarLocal();
        pintar();
    })();
</script>

<noscript>
    <style>
        .sw-step { display: block !important; border-top: 1px solid var(--sw-line); padding-top: 20px; margin-top: 20px; }
        .sw-step:first-of-type { border-top: 0; padding-top: 0; margin-top: 0; }
        #btnSiguiente, #btnAtras, .sw-progress { display: none !important; }
        #btnEnviar { display: inline-flex !important; }
    </style>
</noscript>
@endpush
