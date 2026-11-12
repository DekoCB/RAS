@extends('layouts.mantis')

@section('title', 'Inmobiliario | R.A.S')
@section('page-title', 'Inmobiliario e infraestructura')
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
    $filtrando = $categoria || $estado || $buscar !== '';
    $urlCat = fn ($c) => route('patrimonio.activos', array_filter(['categoria' => $c ?: null, 'estado' => $estado, 'q' => $buscar ?: null]));
@endphp
<div class="pt-page">
    @include('partials.modulo')

    <div class="pt-top">
        <div>
            <div class="pt-top__kicker">Patrimonio &middot; Inventario de bienes</div>
            <div class="pt-top__text">
                <strong>{{ $resumen['total'] }}</strong> activos registrados
                &middot; valor en uso <strong>S/ {{ number_format($resumen['valor'], 2) }}</strong>
            </div>
        </div>
        <div class="pt-top__actions">
            <a href="{{ route('patrimonio.defensa-civil') }}" class="pt-btn pt-btn--ghost">
                <i data-feather="shield"></i> Defensa Civil
            </a>
            @can('patrimonio.gestionar')
                <x-mod-modal id="registrar-activo" titulo="Registrar activo"
                             subtitulo="Bien inmueble, mobiliario, equipo o vehiculo del colegio."
                             boton="Registrar activo">
                    <form method="POST" action="{{ route('patrimonio.activos.guardar') }}" class="mod-form row g-3">
                        @csrf
                        <div class="col-md-8">
                            <label for="nombre">Nombre del bien</label>
                            <input id="nombre" type="text" name="nombre" class="form-control" required maxlength="180"
                                   placeholder="Ej. Proyector Epson X49 - Aula 2do Sec." value="{{ old('nombre') }}" autofocus>
                        </div>
                        <div class="col-md-4">
                            <label for="codigo">C&oacute;digo</label>
                            <input id="codigo" type="text" name="codigo" class="form-control" maxlength="60"
                                   placeholder="EQP-019" value="{{ old('codigo') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="categoria">Categor&iacute;a</label>
                            <select id="categoria" name="categoria" class="form-select" required>
                                @foreach ($categorias as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected(old('categoria', $categoria) === $clave)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="sede_id">Sede</label>
                            <select id="sede_id" name="sede_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s->id }}" @selected((int) old('sede_id') === $s->id)>{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="estado_a">Estado</label>
                            <select id="estado_a" name="estado" class="form-select" required>
                                @foreach ($estados as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected(old('estado', 'operativo') === $clave)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ubicacion">Ubicaci&oacute;n</label>
                            <input id="ubicacion" type="text" name="ubicacion" class="form-control" maxlength="180"
                                   placeholder="Aula, ambiente o direccion" value="{{ old('ubicacion') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_adquisicion">Fecha de adquisici&oacute;n</label>
                            <input id="fecha_adquisicion" type="date" name="fecha_adquisicion" class="form-control" value="{{ old('fecha_adquisicion') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="valor">Valor (S/)</label>
                            <input id="valor" type="number" step="0.01" min="0" name="valor" class="form-control" placeholder="0.00" value="{{ old('valor') }}">
                        </div>
                        <div class="col-12">
                            <label for="observacion">Observaci&oacute;n</label>
                            <input id="observacion" type="text" name="observacion" class="form-control" placeholder="Opcional" value="{{ old('observacion') }}">
                        </div>
                        <div class="col-12 mod-form__pie">
                            <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="mod-btn">Registrar activo</button>
                        </div>
                    </form>
                </x-mod-modal>
            @endcan
        </div>
    </div>

    <div class="pt-kpis">
        <div class="pt-kpi pt-kpi--ok">
            <span class="pt-kpi__icon"><i data-feather="check-circle"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['operativos'] }}</div><div class="pt-kpi__lbl">Operativos</div></div>
        </div>
        <div class="pt-kpi pt-kpi--warn">
            <span class="pt-kpi__icon"><i data-feather="tool"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['mantenimiento'] }}</div><div class="pt-kpi__lbl">En mantenimiento</div></div>
        </div>
        <div class="pt-kpi pt-kpi--mute">
            <span class="pt-kpi__icon"><i data-feather="archive"></i></span>
            <div><div class="pt-kpi__num">{{ $resumen['baja'] }}</div><div class="pt-kpi__lbl">De baja</div></div>
        </div>
        <div class="pt-kpi">
            <span class="pt-kpi__icon"><i data-feather="dollar-sign"></i></span>
            <div>
                <div class="pt-kpi__num"><small>S/</small> {{ number_format($resumen['gasto_anio'], 2) }}</div>
                <div class="pt-kpi__lbl">Mantenimiento {{ now()->year }}</div>
            </div>
        </div>
    </div>

    <section class="pt-card">
        <div class="pt-card__head">
            <div>
                <h2>{{ $categoria ? $categorias[$categoria] : 'Todos los activos' }}</h2>
                <p>{{ $activos->total() }} resultado(s){{ $filtrando ? ' con el filtro actual' : '' }}.</p>
            </div>
            @if ($filtrando)
                <a href="{{ route('patrimonio.activos') }}" class="pt-btn pt-btn--ghost pt-btn--sm"><i data-feather="x"></i> Limpiar filtros</a>
            @endif
        </div>

        <div class="pt-filters">
            <div>
                <label>Categor&iacute;a</label>
                <div class="pt-tabs">
                    <a href="{{ $urlCat('') }}" class="{{ !$categoria ? 'is-on' : '' }}"><i data-feather="grid"></i> Todas <small>{{ $resumen['total'] }}</small></a>
                    @foreach ($categorias as $clave => $etiqueta)
                        <a href="{{ $urlCat($clave) }}" class="{{ $categoria === $clave ? 'is-on' : '' }}">
                            <i data-feather="{{ $iconoCat[$clave] }}"></i> {{ $etiqueta }} <small>{{ $porCategoria[$clave] ?? 0 }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
            <form method="GET" action="{{ route('patrimonio.activos') }}" class="d-flex gap-2 align-items-end flex-wrap" style="flex:1 1 420px">
                @if ($categoria)<input type="hidden" name="categoria" value="{{ $categoria }}">@endif
                <div class="pt-search">
                    <label for="q">Buscar</label>
                    <i data-feather="search" style="top:auto;bottom:11px;transform:none"></i>
                    <input id="q" type="text" name="q" class="pt-input" placeholder="Nombre, codigo o ubicacion" value="{{ $buscar }}">
                </div>
                <div style="min-width:170px">
                    <label for="f_estado">Estado</label>
                    <select id="f_estado" name="estado" class="pt-select">
                        <option value="">Todos</option>
                        @foreach ($estados as $clave => $etiqueta)
                            <option value="{{ $clave }}" @selected($estado === $clave)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="pt-btn pt-btn--navy"><i data-feather="filter"></i> Filtrar</button>
            </form>
        </div>

        <div class="pt-table-wrap">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th>C&oacute;digo</th><th>Bien</th><th>Categor&iacute;a</th><th>Sede</th>
                        <th class="pt-right">Valor</th><th>Estado</th><th class="pt-right">Mantenim.</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activos as $a)
                        <tr>
                            <td><span class="pt-code">{{ $a->codigo ?: '—' }}</span></td>
                            <td>
                                <div class="pt-name"><a href="{{ route('patrimonio.mantenimientos', $a) }}">{{ $a->nombre }}</a></div>
                                @if ($a->ubicacion)<div class="pt-sub">{{ $a->ubicacion }}</div>@endif
                            </td>
                            <td>
                                <span class="pt-cat pt-cat--{{ $a->categoria }}">
                                    <span class="pt-cat__icon"><i data-feather="{{ $iconoCat[$a->categoria] ?? 'box' }}"></i></span>
                                    {{ $categorias[$a->categoria] ?? $a->categoria }}
                                </span>
                            </td>
                            <td>{!! $a->sede ? '<span class="pt-chip"><i data-feather="map-pin"></i>' . e($a->sede->nombre) . '</span>' : '<span class="pt-sub">—</span>' !!}</td>
                            <td class="pt-num">{{ $a->valor ? 'S/ ' . number_format($a->valor, 2) : '—' }}</td>
                            <td><span class="pt-pill {{ $pillEstado[$a->estado] ?? 'pt-pill--mute' }}">{{ $estados[$a->estado] ?? $a->estado }}</span></td>
                            <td class="pt-num">
                                {{ $a->mantenimientos_count }}
                                @if ($a->mantenimientos_sum_costo)
                                    <div class="pt-sub">S/ {{ number_format($a->mantenimientos_sum_costo, 2) }}</div>
                                @endif
                            </td>
                            <td class="pt-right">
                                <span class="pt-acts">
                                    <a href="{{ route('patrimonio.mantenimientos', $a) }}" class="pt-btn pt-btn--ghost pt-btn--sm">
                                        <i data-feather="tool"></i> Historial
                                    </a>
                                    @can('patrimonio.gestionar')
                                        <form method="POST" action="{{ route('patrimonio.activos.eliminar', $a) }}" class="d-inline"
                                              onsubmit="return confirm('Eliminar este activo y su historial?')">
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
                            <td colspan="8" class="pt-empty">
                                <span class="pt-empty__icon"><i data-feather="{{ $filtrando ? 'search' : 'package' }}"></i></span>
                                <strong>{{ $filtrando ? 'Sin resultados' : 'Aún no hay activos registrados' }}</strong>
                                {{ $filtrando ? 'Prueba con otra búsqueda o quita los filtros.' : 'Registra los bienes del colegio para llevar su inventario y mantenimiento.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activos->hasPages())
            <div class="pt-pager">{{ $activos->links() }}</div>
        @endif
    </section>
</div>
@endsection
