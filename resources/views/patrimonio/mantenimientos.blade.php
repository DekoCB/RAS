@extends('layouts.mantis')

@section('title', 'Mantenimiento | R.A.S')
@section('page-title', 'Historial del activo')
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
    $iconoCat = ['inmueble' => 'home', 'mobiliario' => 'layout', 'equipo' => 'monitor', 'vehiculo' => 'truck'];
    $pillEstado = ['operativo' => 'pt-pill--ok', 'mantenimiento' => 'pt-pill--warn', 'baja' => 'pt-pill--mute'];
    $categorias = \App\Models\Activo::CATEGORIAS;
    $estados = \App\Models\Activo::ESTADOS;
    $gasto = $mantenimientos->sum('costo');
    $ultimo = $mantenimientos->first();
    $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
@endphp
<div class="pt-page">
    @include('partials.modulo')

    <div class="pt-top">
        <div>
            <div class="pt-top__kicker">Patrimonio &middot; Historial de mantenimiento</div>
            <div class="pt-top__text"><strong>{{ $activo->codigo ?: 'Sin código' }}</strong> &middot; {{ $activo->nombre }}</div>
        </div>
        <div class="pt-top__actions">
            <a href="{{ route('patrimonio.activos') }}" class="pt-btn pt-btn--ghost"><i data-feather="arrow-left"></i> Volver al inventario</a>
            @can('patrimonio.gestionar')
                <x-mod-modal id="registrar-mantenimiento" titulo="Registrar mantenimiento"
                             subtitulo="{{ $activo->nombre }}" boton="Registrar mantenimiento">
                    <form method="POST" action="{{ route('patrimonio.mantenimientos.guardar', $activo) }}" class="mod-form row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="fecha">Fecha</label>
                            <input id="fecha" type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select" required>
                                <option value="preventivo" @selected(old('tipo') === 'preventivo')>Preventivo (revisi&oacute;n programada)</option>
                                <option value="correctivo" @selected(old('tipo') === 'correctivo')>Correctivo (reparaci&oacute;n)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="costo">Costo (S/)</label>
                            <input id="costo" type="number" step="0.01" min="0" name="costo" class="form-control" placeholder="0.00" value="{{ old('costo') }}">
                        </div>
                        <div class="col-12">
                            <label for="responsable">Responsable / proveedor</label>
                            <input id="responsable" type="text" name="responsable" class="form-control" maxlength="150"
                                   placeholder="T&eacute;cnico, empresa o personal del colegio" value="{{ old('responsable') }}">
                        </div>
                        <div class="col-12">
                            <label for="detalle">Detalle del trabajo</label>
                            <textarea id="detalle" name="detalle" class="form-control" rows="3" placeholder="Qu&eacute; se hizo, repuestos, observaciones">{{ old('detalle') }}</textarea>
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

    {{-- Ficha del activo --}}
    <div class="pt-sheet">
        <div class="pt-sheet__main">
            <span class="pt-sheet__icon"><i data-feather="{{ $iconoCat[$activo->categoria] ?? 'box' }}"></i></span>
            <div style="min-width:0">
                <div class="pt-sheet__name">{{ $activo->nombre }}</div>
                <div class="pt-sheet__chips">
                    <span class="pt-pill {{ $pillEstado[$activo->estado] ?? 'pt-pill--mute' }}">{{ $estados[$activo->estado] ?? $activo->estado }}</span>
                    <span class="pt-chip"><i data-feather="tag"></i>{{ $categorias[$activo->categoria] ?? $activo->categoria }}</span>
                    @if ($activo->sede)<span class="pt-chip"><i data-feather="map-pin"></i>{{ $activo->sede->nombre }}</span>@endif
                    @if ($activo->ubicacion)<span class="pt-chip"><i data-feather="navigation"></i>{{ $activo->ubicacion }}</span>@endif
                    @if ($activo->fecha_adquisicion)<span class="pt-chip"><i data-feather="calendar"></i>Adquirido el {{ $activo->fecha_adquisicion->format('d/m/Y') }}</span>@endif
                </div>
                @if ($activo->observacion)
                    <div class="pt-sheet__obs">{{ $activo->observacion }}</div>
                @endif
            </div>
        </div>
        <div class="pt-sheet__side">
            <div class="pt-meter">
                <div class="pt-meter__lbl">Valor del bien</div>
                <div class="pt-meter__val">{{ $activo->valor ? 'S/ ' . number_format($activo->valor, 2) : '—' }}</div>
            </div>
            <div class="pt-meter">
                <div class="pt-meter__lbl">Intervenciones</div>
                <div class="pt-meter__val">{{ $mantenimientos->count() }} <small>{{ $ultimo ? '· últ. ' . $ultimo->fecha->format('d/m/y') : '' }}</small></div>
            </div>
            <div class="pt-meter">
                <div class="pt-meter__lbl">Gasto acumulado</div>
                <div class="pt-meter__val">S/ {{ number_format($gasto, 2) }}</div>
            </div>
        </div>
    </div>

    <section class="pt-card">
        <div class="pt-card__head">
            <div>
                <h2>Historial de mantenimiento</h2>
                <p>Del m&aacute;s reciente al m&aacute;s antiguo. Preventivo = revisi&oacute;n programada; correctivo = reparaci&oacute;n por falla.</p>
            </div>
        </div>
        <div class="pt-card__body">
            @if ($mantenimientos->isEmpty())
                <div class="pt-empty" style="padding:30px 10px">
                    <span class="pt-empty__icon"><i data-feather="tool"></i></span>
                    <strong>Sin mantenimientos registrados</strong>
                    Registra aqu&iacute; cada revisi&oacute;n o reparaci&oacute;n para conocer el costo real de mantener este bien.
                </div>
            @else
                <div class="pt-tl">
                    @foreach ($mantenimientos as $m)
                        <div class="pt-tl__item is-{{ $m->tipo }}">
                            <span class="pt-tl__dot"></span>
                            <div class="pt-tl__date">{{ $m->fecha->format('d') }} {{ $meses[$m->fecha->month - 1] }}<br>{{ $m->fecha->format('Y') }}</div>
                            <div style="min-width:0">
                                <div class="pt-tl__title">{{ $m->detalle ? \Illuminate\Support\Str::limit($m->detalle, 90) : ucfirst($m->tipo) }}</div>
                                <div class="pt-tl__meta">
                                    <span class="pt-pill {{ $m->tipo === 'preventivo' ? 'pt-pill--steel' : 'pt-pill--warn' }}">{{ ucfirst($m->tipo) }}</span>
                                    @if ($m->responsable)<span class="pt-chip"><i data-feather="user"></i>{{ $m->responsable }}</span>@endif
                                </div>
                                @if ($m->detalle && mb_strlen($m->detalle) > 90)
                                    <div class="pt-tl__text">{{ $m->detalle }}</div>
                                @endif
                            </div>
                            <div style="display:flex;align-items:center;gap:12px">
                                <div class="pt-tl__cost"><small>Costo</small>{{ $m->costo ? 'S/ ' . number_format($m->costo, 2) : '—' }}</div>
                                @can('patrimonio.gestionar')
                                    <form method="POST" action="{{ route('patrimonio.mantenimientos.eliminar', $m) }}"
                                          onsubmit="return confirm('Eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="pt-btn pt-btn--danger pt-btn--sm" title="Eliminar"><i data-feather="trash-2"></i></button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
