@extends('layouts.mantis')

@section('title', 'Cumpleanos de docentes | R.A.S')
@section('page-title', 'Cumpleanos de docentes')
@section('body-class', 'role-superadmin tema-documentos')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-documentos.css') }}?v={{ filemtime(public_path('css/tema-documentos.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $hoy = now();
    $mesActual = (int) $hoy->format('n');
    $totalDocentes = $docentes->flatten()->count();
    $esteMes = ($docentes[$mesActual] ?? collect())->count();
    // Meses ordenados desde el actual, para que lo proximo quede primero
    $orden = array_merge(range($mesActual, 12), range(1, $mesActual - 1));
@endphp
<div class="dc-page">
    @include('partials.modulo')

    <div class="dc-top">
        <div>
            <div class="dc-top__kicker">Recursos humanos &middot; Cumplea&ntilde;os</div>
            <div class="dc-top__text">
                <strong>{{ $totalDocentes }}</strong> docentes activos con fecha de nacimiento registrada
                &middot; <strong>{{ $esteMes }}</strong> cumplen en {{ $meses[$mesActual] }}
            </div>
        </div>
        <a href="{{ route('documentos.index', 'rrhh') }}" class="dc-btn dc-btn--ghost">
            <i data-feather="folder"></i> Archivador de RR.HH.
        </a>
    </div>

    @if ($totalDocentes === 0)
        <div class="dc-card">
            <div class="dc-empty">
                <span class="dc-empty__icon"><i data-feather="gift"></i></span>
                <strong>Sin fechas de nacimiento registradas</strong>
                Los cumplea&ntilde;os salen de la ficha de cada docente; agrega su fecha de nacimiento y aparecer&aacute;n aqu&iacute;.
            </div>
        </div>
    @endif

    <div class="dc-months">
        @foreach ($orden as $numero)
            @php
                $mes = $meses[$numero];
                $lista = collect($docentes[$numero] ?? []);
                $esActual = $numero === $mesActual;
            @endphp
            <div class="dc-month {{ $esActual ? 'is-now' : '' }}">
                <div class="dc-month__head">
                    <span class="dc-month__name">{{ $mes }}</span>
                    @if ($esActual)
                        <span class="dc-month__pill">Este mes</span>
                    @else
                        <span class="dc-month__count">{{ $lista->count() ?: 'Sin' }} cumplea&ntilde;os</span>
                    @endif
                </div>
                <div class="dc-month__body">
                    @forelse ($lista as $d)
                        @php
                            $nac = \Illuminate\Support\Carbon::parse($d->fecha_nacimiento);
                            $esHoy = $esActual && (int) $nac->format('j') === (int) $hoy->format('j');
                            $cumple = $hoy->year - $nac->year; // edad que cumple este año
                        @endphp
                        <div class="dc-bday {{ $esHoy ? 'is-today' : '' }}">
                            <span class="dc-bday__day">{{ $nac->format('d') }}<small>{{ mb_substr($mes, 0, 3) }}</small></span>
                            <span style="min-width:0">
                                <div class="dc-bday__name">{{ $d->apellidos }}, {{ $d->nombres }}</div>
                                <div class="dc-bday__meta">{{ $d->especialidad ?: 'Docente' }}{{ $esHoy ? ' · ¡Hoy!' : '' }}</div>
                            </span>
                            <span class="dc-bday__age" title="Edad que cumple este a&ntilde;o">{{ $cumple }} a&ntilde;os</span>
                        </div>
                    @empty
                        <div class="dc-month__empty">Nadie cumple a&ntilde;os este mes.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
