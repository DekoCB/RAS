@extends('layouts.mantis')

@section('title', 'Admision | R.A.S')
@section('page-title', 'Admision')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $total     = (int) $resumen['total'];
    $totalPasos = \App\Models\Postulante::TOTAL_PASOS;
    $segUrl    = fn ($n) => request()->fullUrlWithQuery(['nivel' => $n ?: null, 'page' => null]);
    $filtrando = $buscar !== '' || $nivel || $estado || $origen;
    $puedeMatricular = auth()->user()?->hasAnyRole(['Secretaria', 'SuperAdmin']);
@endphp
<div class="ad-page">

    {{-- Barra de acciones --}}
    <div class="ad-toolbar">
        <div class="ad-toolbar__lead">
            <div>
                <div class="ad-toolbar__kicker">Proceso de admision {{ now()->year }}</div>
                <div class="ad-toolbar__text">
                    <strong>{{ $total }}</strong> {{ $total === 1 ? 'expediente' : 'expedientes' }} en el sistema
                    @if ($resumen['borradores'] > 0)
                        &middot; {{ $resumen['borradores'] }} sin terminar
                    @endif
                    @if (($resumen['web'] ?? 0) > 0)
                        &middot;
                        <a href="{{ route('admision.index', ['estado' => 'preinscrito']) }}">
                            {{ $resumen['web'] }} pre-inscripcion(es) web por revisar
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="ad-toolbar__actions">
            @can('admision.evaluar')
                <a href="{{ route('admision.examenes') }}" class="ad-btn ad-btn--ghost">
                    <i data-feather="award"></i> Examenes por nivel
                </a>
            @endcan
            @can('admision.registrar')
                <a href="{{ route('admision.crear') }}" class="ad-btn ad-btn--gold">
                    <i data-feather="user-plus"></i> Nueva postulacion
                </a>
            @endcan
        </div>
    </div>

    @include('admision._alertas')

    {{-- Expedientes --}}
    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon"><i data-feather="folder"></i></span>
                <div>
                    <h2>Expedientes</h2>
                    <p>Una postulacion sin terminar se retoma en el paso donde quedo.</p>
                </div>
            </div>
            <div class="ad-card__aside">
                <span class="ad-pill ad-pill--navy">{{ $postulantes->total() }} resultado(s)</span>
                @if ($filtrando)
                    <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--soft ad-btn--sm">
                        <i data-feather="x"></i> Limpiar filtros
                    </a>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('admision.index') }}" class="ad-filters">
            @if ($nivel)
                <input type="hidden" name="nivel" value="{{ $nivel }}">
            @endif
            <div class="ad-field">
                <label for="q">Buscar</label>
                <div class="ad-search">
                    <i data-feather="search"></i>
                    <input id="q" type="text" name="q" class="ad-input" placeholder="Nombre, DNI o codigo"
                           value="{{ $buscar }}">
                </div>
            </div>
            <div class="ad-field">
                <label>Nivel</label>
                <div class="ad-segs">
                    <a href="{{ $segUrl('') }}" class="{{ !$nivel ? 'is-on' : '' }}">Todos</a>
                    @foreach ($niveles as $n)
                        <a href="{{ $segUrl($n) }}" class="{{ $nivel === $n ? 'is-on' : '' }}">{{ $n }}</a>
                    @endforeach
                </div>
            </div>
            <div class="ad-field">
                <label for="f_estado">Estado</label>
                <select id="f_estado" name="estado" class="ad-select">
                    <option value="">Todos</option>
                    @foreach ($estados as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($estado === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ad-field">
                <label for="f_origen">Origen</label>
                <select id="f_origen" name="origen" class="ad-select">
                    <option value="">Todos</option>
                    @foreach ($origenes as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($origen === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ad-field ad-field--end">
                <button type="submit" class="ad-btn ad-btn--navy">
                    <i data-feather="filter"></i> Filtrar
                </button>
            </div>
        </form>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Postulante</th>
                        <th>Nivel / grado</th>
                        <th>Sede</th>
                        <th>Avance</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($postulantes as $p)
                        @php
                            $iniciales = mb_strtoupper(mb_substr($p->nombres, 0, 1) . mb_substr($p->apellidos, 0, 1));
                            $nivelCls = strtolower($p->nivel ?? '');
                            $pill = match ($p->estado) {
                                'admitido'    => 'ad-pill--ok',
                                'no_admitido' => 'ad-pill--danger',
                                'desistido'   => 'ad-pill--mute',
                                'borrador', 'preinscrito' => 'ad-pill--gold',
                                default       => 'ad-pill--navy',
                            };
                            $terminado = !$p->enCaptura();
                        @endphp
                        <tr>
                            <td><span class="ad-code">{{ $p->codigo }}</span></td>
                            <td>
                                <div class="ad-who">
                                    <span class="ad-avatar ad-avatar--{{ $nivelCls }}">{{ $iniciales }}</span>
                                    <span>
                                        <span class="ad-who__name">{{ $p->nombreCompleto() }}</span><br>
                                        <span class="ad-who__meta">
                                            {{ $p->dni ? 'DNI ' . $p->dni : 'Sin DNI' }}
                                            @if ($p->esWeb())
                                                &middot; <span class="ad-pill ad-pill--gold">Web</span>
                                            @endif
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="ad-nivel ad-nivel--{{ $nivelCls }}">
                                    {{ $p->nivel }}
                                    @if ($p->grado)<small>&middot; {{ $p->grado->nombre }}</small>@endif
                                </span>
                            </td>
                            <td class="ad-muted">{{ $p->sede?->nombre ?? '--' }}</td>
                            <td>
                                <span class="ad-steps">
                                    <span class="ad-steps__bar">
                                        @for ($i = 1; $i <= $totalPasos; $i++)
                                            <i class="{{ $i <= $p->paso_actual ? ($terminado ? 'on' : 'on gold') : '' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="ad-steps__lbl">
                                        {{ $terminado ? 'Completo' : "Paso {$p->paso_actual} de {$totalPasos}" }}
                                    </span>
                                </span>
                            </td>
                            <td>
                                <span class="ad-pill {{ $pill }}">{{ $estados[$p->estado] ?? $p->estado }}</span>
                                @if ($p->matricula_id)
                                    <br><span class="ad-pill ad-pill--navy" style="margin-top:4px">Matriculado</span>
                                @endif
                            </td>
                            <td class="ad-right">
                                <span class="ad-actions">
                                    <a href="{{ route('admision.expediente', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">
                                        <i data-feather="eye"></i> Expediente
                                    </a>
                                    @can('admision.registrar')
                                        @if ($p->enCaptura())
                                            <a href="{{ route('admision.paso' . max(2, $p->paso_actual), $p) }}"
                                               class="ad-btn ad-btn--navy ad-btn--sm">
                                                Continuar <i data-feather="arrow-right"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @if ($puedeMatricular && $p->puedeMatricularse())
                                        <form method="POST" action="{{ route('admision.matricular', $p) }}" class="ad-inline">
                                            @csrf
                                            <button type="submit" class="ad-btn ad-btn--gold ad-btn--sm">
                                                <i data-feather="user-check"></i> Matricular
                                            </button>
                                        </form>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ad-empty">
                                <span class="ad-empty__icon"><i data-feather="inbox"></i></span>
                                <strong>{{ $filtrando ? 'Sin resultados para este filtro' : 'Todavia no hay postulantes' }}</strong>
                                {{ $filtrando ? 'Prueba con otro nombre, nivel o estado.' : 'Registra la primera postulacion para iniciar el proceso.' }}
                                @can('admision.registrar')
                                    @unless ($filtrando)
                                        <div>
                                            <a href="{{ route('admision.crear') }}" class="ad-btn ad-btn--gold">
                                                <i data-feather="user-plus"></i> Registrar la primera postulacion
                                            </a>
                                        </div>
                                    @endunless
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($postulantes->hasPages())
            <div class="ad-pager">{{ $postulantes->links() }}</div>
        @endif
    </section>
</div>
@endsection
