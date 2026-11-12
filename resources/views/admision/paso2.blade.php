@extends('layouts.mantis')

@section('title', 'Admision - Paso 2 | R.A.S')
@section('page-title', 'Datos del apoderado')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $tieneSegundo = (string) old('tiene_segundo_apoderado', $postulante->apoderado2_nombres ? '1' : '0') === '1';
@endphp
<div class="ad-page">
    <div class="ad-toolbar">
        @include('admision._contexto')
        <div class="ad-toolbar__actions">
            <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">
                <i data-feather="log-out"></i> Guardar y salir
            </a>
        </div>
    </div>

    @include('admision._stepper')
    @include('admision._alertas')

    <form method="POST" action="{{ route('admision.paso2.guardar', $postulante) }}" class="ad-page">
        @csrf
        @method('PUT')
        <input type="hidden" name="tiene_segundo_apoderado" id="tieneSegundoApoderado" value="{{ $tieneSegundo ? '1' : '0' }}">

        <section class="ad-card">
            <div class="ad-card__head">
                <div class="ad-card__title">
                    <span class="ad-card__icon"><i data-feather="users"></i></span>
                    <div>
                        <h2>Apoderado principal</h2>
                        <p>A este contacto se le comunica el resultado. Son los mismos datos que pide la matricula, asi no se vuelven a digitar.</p>
                    </div>
                </div>
            </div>
            <div class="ad-card__body ad-grid">
                @include('admision._apoderado_campos', ['p' => 'apoderado', 'obligatorio' => true])
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card__head">
                <div class="ad-card__title">
                    <span class="ad-card__icon ad-card__icon--gold"><i data-feather="user-plus"></i></span>
                    <div>
                        <h2>Segundo apoderado</h2>
                        <p>Opcional: el otro padre, madre o tutor que tambien responde por el postulante.</p>
                    </div>
                </div>
                <div class="ad-card__aside">
                    <label class="ad-check" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;cursor:pointer">
                        <input type="checkbox" id="chkSegundo" style="width:17px;height:17px;accent-color:#172269" {{ $tieneSegundo ? 'checked' : '' }}>
                        &iquest;Hay un segundo apoderado?
                    </label>
                </div>
            </div>
            <div class="ad-card__body ad-grid" id="segundoApoderado" @if (!$tieneSegundo) hidden @endif>
                @include('admision._apoderado_campos', ['p' => 'apoderado2', 'obligatorio' => false])
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card__foot">
                <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">
                    <i data-feather="arrow-left"></i> Volver
                </a>
                <button type="submit" class="ad-btn ad-btn--navy">
                    Continuar al paso 3 <i data-feather="arrow-right"></i>
                </button>
            </div>
        </section>
    </form>
</div>

<script>
    // El bloque del segundo apoderado solo se muestra (y se exige) cuando se marca la casilla.
    (function () {
        var chk = document.getElementById('chkSegundo');
        var bloque = document.getElementById('segundoApoderado');
        var flag = document.getElementById('tieneSegundoApoderado');
        var obligatorios = ['apoderado2_parentesco', 'apoderado2_nombres', 'apoderado2_apellidos'];

        var aplicar = function () {
            bloque.hidden = !chk.checked;
            flag.value = chk.checked ? '1' : '0';
            obligatorios.forEach(function (n) {
                var el = bloque.querySelector('[name="' + n + '"]');
                if (el) el.required = chk.checked;
            });
        };
        chk.addEventListener('change', aplicar);
        aplicar();
    })();
</script>
@endsection
