@extends('layouts.mantis')

@section('title', 'Admision - Paso 5 | R.A.S')
@section('page-title', 'Cronograma del proceso')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php $completadas = $postulante->etapas->where('estado', 'completada')->count(); @endphp
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

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon"><i data-feather="calendar"></i></span>
                <div>
                    <h2>Etapas del proceso</h2>
                    <p>Programa cada etapa y marca su cierre a medida que ocurre.</p>
                </div>
            </div>
            <div class="ad-card__aside">
                <span class="ad-pill {{ $completadas === count($etapas) ? 'ad-pill--ok' : 'ad-pill--navy' }}">
                    {{ $completadas }} de {{ count($etapas) }} completadas
                </span>
            </div>
        </div>

        <div class="ad-card__body">
            @include('admision._etapas')
        </div>

        <div class="ad-card__foot">
            <a href="{{ route('admision.paso4', $postulante) }}" class="ad-btn ad-btn--ghost">
                <i data-feather="arrow-left"></i> Volver
            </a>
            <form method="POST" action="{{ route('admision.finalizar', $postulante) }}">
                @csrf
                <button type="submit" class="ad-btn ad-btn--gold">
                    <i data-feather="folder-plus"></i> Finalizar y abrir expediente
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
