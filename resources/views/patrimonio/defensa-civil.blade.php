@extends('layouts.mantis')

@section('title', 'Defensa Civil | R.A.S')
@section('page-title', 'Defensa Civil')
@section('body-class', 'role-superadmin tema-patrimonio')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-patrimonio.css') }}?v={{ filemtime(public_path('css/tema-patrimonio.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $iconoTipo = ['simulacro' => 'alert-triangle', 'inspeccion' => 'clipboard', 'senalizacion' => 'map', 'extintores' => 'zap'];
    $pillResultado = ['conforme' => 'pt-pill--ok', 'observado' => 'pt-pill--warn', 'pendiente' => 'pt-pill--mute'];
    $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    $hoy = now()->startOfDay();
@endphp
<div class="pt-page">
    @include('partials.modulo')

    <div class="pt-top">
        <div>
            <div class="pt-top__kicker">Patrimonio &middot; Seguridad de los locales</div>
            <div class="pt-top__text">
                <strong>{{ $resumen['total'] }}</strong> actividades registradas
                &middot; <strong>{{ $resumen['proximas'] }}</strong> programadas pr&oacute;ximamente
            </div>
        </div>
        <div class="pt-top__actions">
            <a href="{{ route('patrimonio.activos') }}" class="pt-btn pt-btn--ghost"><i data-feather="package"></i> Inmobiliario</a>
            @can('patrimonio.gestionar')
                <x-mod-modal id="registrar-actividad" titulo="Registrar actividad"
                             subtitulo="Simulacro, inspecci&oacute;n, se&ntilde;alizaci&oacute;n o revisi&oacute;n de extintores."
                             boton="Registrar actividad">
                    <form method="POST" action="{{ route('patrimonio.defensa-civil.guardar') }}"
                          enctype="multipart/form-data" class="mod-form row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="tipo">Tipo de actividad</label>
                            <select id="tipo" name="tipo" class="form-select" required>
                                @foreach ($tipos as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected(old('tipo', $tipo) === $clave)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="titulo">T&iacute;tulo</label>
                            <input id="titulo" type="text" name="titulo" class="form-control" required maxlength="180"
                                   placeholder="Ej. Simulacro nacional de sismo" value="{{ old('titulo') }}" autofocus>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha">Fecha</label>
                            <input id="fecha" type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="sede_id">Sede</label>
                            <select id="sede_id" name="sede_id" class="form-select">
                                <option value="">Ambos locales</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s->id }}" @selected((int) old('sede_id') === $s->id)>{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="participantes">Participantes</label>
                            <input id="participantes" type="number" name="participantes" class="form-control" min="0" placeholder="0" value="{{ old('participantes') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="responsable">Responsable</label>
                            <input id="responsable" type="text" name="responsable" class="form-control" maxlength="150"
                                   placeholder="Brigada, docente o empresa" value="{{ old('responsable') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="resultado">Resultado</label>
                            <select id="resultado" name="resultado" class="form-select">
                                @foreach ($resultados as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected(old('resultado', 'conforme') === $clave)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="proxima_fecha">Pr&oacute;xima fecha</label>
                            <input id="proxima_fecha" type="date" name="proxima_fecha" class="form-control" value="{{ old('proxima_fecha') }}">
                        </div>
                        <div class="col-md-5">
                            <label for="evidencia">Evidencia (PDF o foto)</label>
                            <input id="evidencia" type="file" name="evidencia" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-7">
                            <label for="observacion">Observaci&oacute;n</label>
                            <input id="observacion" type="text" name="observacion" class="form-control" placeholder="Opcional" value="{{ old('observacion') }}">
                        </div>
                        <div class="col-12 mod-form__pie">
                            <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="mod-btn">Registrar</button>
                        </div>
                    </form>
                </x-mod-modal>
            @endcan
        </div>
    </div>

    <div class="pt-kpis">
        <div class="pt-kpi">
            <span class="pt-kpi__icon"><i data-feather="shield"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['total'] }}</div><div class="pt-kpi__lbl">Actividades</div></div>
        </div>
        <div class="pt-kpi">
            <span class="pt-kpi__icon" style="background:#fee2e2;color:#b91c1c"><i data-feather="alert-triangle"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['simulacros'] }}</div><div class="pt-kpi__lbl">Simulacros</div></div>
        </div>
        <div class="pt-kpi pt-kpi--warn">
            <span class="pt-kpi__icon"><i data-feather="eye"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['observadas'] }}</div><div class="pt-kpi__lbl">Con observaciones</div></div>
        </div>
        <div class="pt-kpi pt-kpi--ok">
            <span class="pt-kpi__icon"><i data-feather="calendar"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['proximas'] }}</div><div class="pt-kpi__lbl">Pr&oacute;ximas programadas</div></div>
        </div>
    </div>

    <section class="pt-card">
        <div class="pt-card__head">
            <div>
                <h2>{{ $tipo ? $tipos[$tipo] : 'Todas las actividades' }}</h2>
                <p>{{ $actividades->total() }} registro(s). Cada actividad queda con su evidencia para las inspecciones.</p>
            </div>
            @if ($tipo)
                <a href="{{ route('patrimonio.defensa-civil') }}" class="pt-btn pt-btn--ghost pt-btn--sm"><i data-feather="x"></i> Ver todas</a>
            @endif
        </div>

        <div class="pt-filters">
            <div>
                <label>Tipo</label>
                <div class="pt-tabs">
                    <a href="{{ route('patrimonio.defensa-civil') }}" class="{{ !$tipo ? 'is-on' : '' }}"><i data-feather="grid"></i> Todas</a>
                    @foreach ($tipos as $clave => $etiqueta)
                        <a href="{{ route('patrimonio.defensa-civil', ['tipo' => $clave]) }}" class="{{ $tipo === $clave ? 'is-on' : '' }}">
                            <i data-feather="{{ $iconoTipo[$clave] }}"></i> {{ $etiqueta }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pt-table-wrap">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Actividad</th><th>Sede</th><th class="pt-right">Particip.</th>
                        <th>Resultado</th><th>Pr&oacute;xima</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($actividades as $a)
                        @php
                            $prox = $a->proxima_fecha;
                            $proxCls = $prox ? ($prox->lt($hoy) ? 'is-late' : ($prox->lte($hoy->copy()->addDays(15)) ? 'is-soon' : '')) : '';
                        @endphp
                        <tr>
                            <td>
                                <span class="pt-date"><b>{{ $a->fecha->format('d') }}</b><small>{{ $meses[$a->fecha->month - 1] }} {{ $a->fecha->format('y') }}</small></span>
                            </td>
                            <td>
                                <div style="display:flex;gap:12px;align-items:center;min-width:0">
                                    <span class="pt-tipo pt-tipo--{{ $a->tipo }}"><i data-feather="{{ $iconoTipo[$a->tipo] ?? 'shield' }}"></i></span>
                                    <div style="min-width:0">
                                        <div class="pt-name">{{ $a->titulo }}</div>
                                        <div class="pt-sub">
                                            {{ $tipos[$a->tipo] ?? $a->tipo }}
                                            @if ($a->responsable) &middot; {{ $a->responsable }} @endif
                                            @if ($a->observacion) &middot; {{ \Illuminate\Support\Str::limit($a->observacion, 70) }} @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="pt-chip"><i data-feather="map-pin"></i>{{ $a->sede?->nombre ?? 'Ambos locales' }}</span></td>
                            <td class="pt-num">{{ $a->participantes ?: '—' }}</td>
                            <td><span class="pt-pill {{ $pillResultado[$a->resultado] ?? 'pt-pill--mute' }}">{{ $resultados[$a->resultado] ?? 'Pendiente' }}</span></td>
                            <td>
                                @if ($prox)
                                    <span class="pt-next {{ $proxCls }}">
                                        {{ $prox->format('d/m/Y') }}
                                        @if ($proxCls === 'is-late') &middot; vencida @elseif ($proxCls === 'is-soon') &middot; pronto @endif
                                    </span>
                                @else
                                    <span class="pt-sub">—</span>
                                @endif
                            </td>
                            <td class="pt-right">
                                <span class="pt-acts">
                                    @if ($a->evidencia)
                                        <a href="{{ asset('storage/' . $a->evidencia) }}" target="_blank" rel="noopener" class="pt-btn pt-btn--ghost pt-btn--sm">
                                            <i data-feather="paperclip"></i> Evidencia
                                        </a>
                                    @endif
                                    @can('patrimonio.gestionar')
                                        <form method="POST" action="{{ route('patrimonio.defensa-civil.eliminar', $a) }}" class="d-inline"
                                              onsubmit="return confirm('Eliminar esta actividad?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="pt-btn pt-btn--danger pt-btn--sm" title="Eliminar"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @endcan
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="pt-empty">
                                <span class="pt-empty__icon"><i data-feather="shield"></i></span>
                                <strong>{{ $tipo ? 'Sin actividades de este tipo' : 'Aún no hay actividades registradas' }}</strong>
                                Registra simulacros, inspecciones, señalización y revisión de extintores con su evidencia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($actividades->hasPages())
            <div class="pt-pager">{{ $actividades->links() }}</div>
        @endif
    </section>
</div>
@endsection
