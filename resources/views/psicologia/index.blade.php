@extends('layouts.mantis')

@section('title', 'Psicologia | R.A.S')
@section('page-title', 'Departamento de Psicologia')
@section('body-class', 'role-superadmin tema-psicologia')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-psicologia.css') }}?v={{ filemtime(public_path('css/tema-psicologia.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo')

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['total'] }}</div>
            <div class="mod-stat__label">Atenciones</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $resumen['abiertas'] }}</div>
            <div class="mod-stat__label">Abiertas</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $resumen['seguimiento'] }}</div>
            <div class="mod-stat__label">En seguimiento</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">{{ $resumen['citas'] }}</div>
            <div class="mod-stat__label">Citas proximas</div>
        </div>
    </div>

    {{-- RF-50: las siete areas del departamento --}}
    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Areas de trabajo</h5>
                <small>Filtra las atenciones por area.</small>
            </div>
        </div>
        <div class="mod-card__body d-flex gap-2 flex-wrap">
            <a href="{{ route('psicologia.index') }}"
               class="mod-chip {{ $area ? 'mod-chip--neutral' : '' }}">Todas</a>
            @foreach ($areas as $clave => $etiqueta)
                <a href="{{ route('psicologia.index', ['area' => $clave]) }}"
                   class="mod-chip {{ $area === $clave ? '' : 'mod-chip--neutral' }}">
                    {{ $etiqueta }} ({{ $porArea[$clave] ?? 0 }})
                </a>
            @endforeach
        </div>
    </div>

    @can('psicologia.gestionar')
        <x-mod-modal id="registrar-atencion" titulo="Registrar atencion"
                     subtitulo="RF-51: tambien sirve para la asesoria psicologica personalizada."
                     boton="Registrar atencion">
            <form method="POST" action="{{ route('psicologia.store') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="area_f">Area</label>
                    <select id="area_f" name="area" class="form-select" required>
                        @foreach ($areas as $clave => $etiqueta)
                            <option value="{{ $clave }}" @selected($area === $clave)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="estudiante_id">Estudiante (opcional)</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select">
                        <option value="">No aplica</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" class="form-control"
                           value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-2">
                    <label for="estado_f">Estado</label>
                    <select id="estado_f" name="estado" class="form-select" required>
                        @foreach ($estados as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="motivo">Motivo</label>
                    <input id="motivo" type="text" name="motivo" class="form-control" required maxlength="200">
                </div>
                <div class="col-md-3">
                    <label for="responsable">Responsable</label>
                    <input id="responsable" type="text" name="responsable" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="proxima_cita">Proxima cita</label>
                    <input id="proxima_cita" type="date" name="proxima_cita" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="observacion">Observacion</label>
                    <textarea id="observacion" name="observacion" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <label for="acuerdos">Acuerdos</label>
                    <textarea id="acuerdos" name="acuerdos" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12 mod-form__pie">
                    <label class="d-inline-flex align-items-center gap-2 mod-form__extra">
                        <input type="checkbox" name="confidencial" value="1" checked> Confidencial
                    </label>
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar atencion</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Atenciones</h5>
                <small>{{ $area ? ($areas[$area] ?? $area) : 'Todas las areas' }}</small>
            </div>
            <form method="GET" action="{{ route('psicologia.index') }}" class="mod-form d-flex gap-2">
                <input type="hidden" name="area" value="{{ $area }}">
                <select name="estado" class="form-select" style="min-width:160px">
                    <option value="">Todo estado</option>
                    @foreach ($estados as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($estado === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mod-btn mod-btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Fecha</th><th>Area</th><th>Estudiante</th><th>Motivo</th><th>Estado</th><th>Proxima cita</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($atenciones as $a)
                        <tr>
                            <td>{{ $a->fecha->format('d/m/Y') }}</td>
                            <td>{{ $a->etiquetaArea() }}</td>
                            <td>
                                {{ $a->estudiante ? $a->estudiante->apellidos . ', ' . $a->estudiante->nombres : '--' }}
                                @if ($a->confidencial)
                                    <span class="mod-chip mod-chip--pend">Confidencial</span>
                                @endif
                            </td>
                            <td>{{ $a->motivo }}</td>
                            <td>
                                <span class="mod-chip {{ $a->estado === 'cerrada' ? 'mod-chip--ok' : ($a->estado === 'abierta' ? 'mod-chip--pend' : '') }}">
                                    {{ $estados[$a->estado] ?? $a->estado }}
                                </span>
                            </td>
                            <td>{{ $a->proxima_cita?->format('d/m/Y') ?: '--' }}</td>
                            <td class="text-end">
                                @can('psicologia.gestionar')
                                    <form method="POST" action="{{ route('psicologia.destroy', $a) }}"
                                          onsubmit="return confirm('Eliminar esta atencion?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">No hay atenciones con este filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($atenciones->hasPages())
            <div class="mod-card__body">{{ $atenciones->links() }}</div>
        @endif
    </div>
@endsection
