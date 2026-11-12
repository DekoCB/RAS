@extends('layouts.mantis')

@section('title', 'Imprimir QR | Secretaria')
@section('page-title', 'Imprimir Carnets QR')

@php
    $isAdmin    = auth()->user()->hasRole('SuperAdmin');
    $isDirector = auth()->user()->hasRole('Director');
@endphp
@section('body-class', $isAdmin ? 'role-superadmin' : ($isDirector ? 'role-director' : 'role-secretaria'))

@push('styles')
    @if($isAdmin)
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @elseif($isDirector)
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <style>
        .iqr-page { max-width: 860px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }

        .iqr-hero {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            border-radius: 16px;
            padding: 2rem 2.2rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 1.4rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(124,58,237,.25);
        }
        .iqr-hero__icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,.18);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .iqr-hero__icon svg { width: 32px; height: 32px; stroke: #fff; }
        .iqr-hero__title { font-size: 1.5rem; font-weight: 800; margin: 0 0 .25rem; }
        .iqr-hero__sub { font-size: .92rem; opacity: .85; margin: 0; }

        .iqr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }

        .iqr-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,.08);
            padding: 2rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            border: 2px solid transparent;
            transition: transform .18s, box-shadow .18s, border-color .18s;
            cursor: pointer;
        }
        .iqr-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0,0,0,.14);
            border-color: var(--card-color, #7c3aed);
            text-decoration: none;
        }
        .iqr-card__icon {
            width: 64px; height: 64px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            background: var(--card-bg, #f3e8ff);
        }
        .iqr-card__icon svg { width: 32px; height: 32px; stroke: var(--card-color, #7c3aed); }
        .iqr-card__label {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
        }
        .iqr-card__desc {
            font-size: .78rem;
            color: #6b7280;
            text-align: center;
            line-height: 1.4;
        }
        .iqr-card__btn {
            margin-top: auto;
            padding: .45rem 1.2rem;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 700;
            background: var(--card-color, #7c3aed);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: opacity .15s;
        }
        .iqr-card__btn:hover { opacity: .88; }

        /* colores por tipo */
        .iqr-card--estudiante { --card-color:#213197; --card-bg:#eff6ff; }
        .iqr-card--docente    { --card-color:#059669; --card-bg:#ecfdf5; }
        .iqr-card--secretaria { --card-color:#1f2e8e; --card-bg:#ebfaff; }
        .iqr-card--auxiliar   { --card-color:#1f2e8f; --card-bg:#f2f7fe; }

        .iqr-note {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 1rem 1.4rem;
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            font-size: .85rem;
            color: #166534;
        }
        .iqr-note svg { width: 20px; height: 20px; stroke: #16a34a; flex-shrink: 0; margin-top: 1px; }

        .iqr-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: #6b7280;
            font-size: .85rem;
            text-decoration: none;
            margin-bottom: 1.2rem;
        }
        .iqr-back:hover { color: #374151; text-decoration: none; }
        .iqr-back svg { width: 16px; height: 16px; }
    </style>
@endpush

@section('sidebar')
    @if($isAdmin)
        @include('admin.partials.sidebar')
    @elseif($isDirector)
        @include('director.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
<div class="iqr-page mt-2">

    {{-- Back --}}
    <a href="{{ $isAdmin ? route('admin-sistema.qr-docentes.index') : ($isDirector ? route('director.qr-docentes.index') : route('secretaria.asistencias-docentes.index')) }}" class="iqr-back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Volver al Lector QR
    </a>

    {{-- Hero --}}
    <div class="iqr-hero">
        <div class="iqr-hero__icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
            </svg>
        </div>
        <div>
            <h1 class="iqr-hero__title">Imprimir Carnets QR</h1>
            <p class="iqr-hero__sub">Selecciona el grupo de usuarios para generar e imprimir sus c&oacute;digos QR de asistencia</p>
        </div>
    </div>

    {{-- Cards de selección --}}
    <div class="iqr-grid">

        {{-- Estudiantes --}}
        <a href="{{ $isAdmin ? route('admin-sistema.imprimir-qr.estudiantes') : ($isDirector ? route('director.imprimir-qr.estudiantes') : route('secretaria.imprimir-qr.estudiantes')) }}" class="iqr-card iqr-card--estudiante">
            <div class="iqr-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
            </div>
            <div class="iqr-card__label">QR Estudiantes</div>
            <div class="iqr-card__desc">Todos los carnets QR de los alumnos matriculados</div>
            <span class="iqr-card__btn">Imprimir</span>
        </a>

        {{-- Docentes --}}
        <a href="{{ $isAdmin ? route('admin-sistema.imprimir-qr.docentes') : ($isDirector ? route('director.imprimir-qr.docentes') : route('secretaria.imprimir-qr.docentes')) }}" class="iqr-card iqr-card--docente">
            <div class="iqr-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            </div>
            <div class="iqr-card__label">QR Docentes</div>
            <div class="iqr-card__desc">Carnets QR de todos los profesores registrados</div>
            <span class="iqr-card__btn">Imprimir</span>
        </a>

        {{-- Secretaria --}}
        <a href="{{ $isAdmin ? route('admin-sistema.imprimir-qr.secretaria') : ($isDirector ? route('director.imprimir-qr.secretaria') : route('secretaria.imprimir-qr.secretaria')) }}" class="iqr-card iqr-card--secretaria">
            <div class="iqr-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
            </div>
            <div class="iqr-card__label">QR Secretaria</div>
            <div class="iqr-card__desc">Carnets QR del personal de secretar&iacute;a</div>
            <span class="iqr-card__btn">Imprimir</span>
        </a>

        {{-- Auxiliar --}}
        <a href="{{ $isAdmin ? route('admin-sistema.imprimir-qr.auxiliar') : ($isDirector ? route('director.imprimir-qr.auxiliar') : route('secretaria.imprimir-qr.auxiliar')) }}" class="iqr-card iqr-card--auxiliar">
            <div class="iqr-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            </div>
            <div class="iqr-card__label">QR Auxiliar</div>
            <div class="iqr-card__desc">Carnets QR del personal auxiliar</div>
            <span class="iqr-card__btn">Imprimir</span>
        </a>

    </div>

    {{-- Nota informativa --}}
    <div class="iqr-note">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        <span>
            Al hacer clic en <strong>Imprimir</strong>, se abre una p&aacute;gina con todos los carnets QR listos para imprimir.
            Si un usuario no tiene c&oacute;digo QR asignado, se generar&aacute; autom&aacute;ticamente.
            Usa <strong>Ctrl + P</strong> (o el bot&oacute;n imprimir en la p&aacute;gina) para enviar a la impresora.
            Recorta cada carnet y entr&eacute;gaselo al usuario para registrar su asistencia.
        </span>
    </div>

</div>
@endsection
