@extends('layouts.mantis')

@section('title', 'Admision - Paso 3 | R.A.S')
@section('page-title', 'Documentos del postulante')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $obligatorios = \App\Models\PostulanteDocumento::OBLIGATORIOS;
    $cargados     = count($obligatorios) - count($faltantes);
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

    @if (count($faltantes) > 0)
        <div class="ad-alert ad-alert--info">
            <i data-feather="info"></i>
            <div>
                <strong>Los documentos no son necesarios para continuar.</strong>
                Puedes cargarlos ahora o mas adelante desde el expediente;
                el sistema solo lleva la cuenta de los requisitos del reglamento ({{ $cargados }} de {{ count($obligatorios) }}).
            </div>
        </div>
    @else
        <div class="ad-alert ad-alert--ok">
            <i data-feather="check-circle"></i>
            <div><strong>Requisitos completos.</strong> Todos los documentos obligatorios estan cargados.</div>
        </div>
    @endif

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon ad-card__icon--gold"><i data-feather="clipboard"></i></span>
                <div>
                    <h2>Requisitos del reglamento</h2>
                    <p>{{ $cargados }} de {{ count($obligatorios) }} requisitos cargados. Sube cada archivo desde su fila; al resubir un tipo se reemplaza el anterior.</p>
                </div>
            </div>
            <div class="ad-card__aside">
                @if (count($faltantes) > 0)
                    <span class="ad-pill ad-pill--mute">Pendientes: {{ count($faltantes) }}</span>
                @else
                    <span class="ad-pill ad-pill--ok">Completos</span>
                @endif
            </div>
        </div>

        @include('admision._checklist', ['puedeSubir' => true])

        <div class="ad-card__foot">
            <a href="{{ route('admision.paso2', $postulante) }}" class="ad-btn ad-btn--ghost">
                <i data-feather="arrow-left"></i> Volver
            </a>
            <form method="POST" action="{{ route('admision.paso3.continuar', $postulante) }}">
                @csrf
                <button type="submit" class="ad-btn ad-btn--navy">
                    Continuar al paso 4 <i data-feather="arrow-right"></i>
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
