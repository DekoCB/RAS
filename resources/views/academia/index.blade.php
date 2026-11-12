@extends('layouts.mantis')

@section('title', 'Real Academia | R.A.S')
@section('page-title', 'Real Academia')
@section('body-class', 'role-superadmin tema-academia')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-academia.css') }}?v={{ filemtime(public_path('css/tema-academia.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo', [
        'acciones' => '<a href="' . route('academia.horarios') . '" class="mod-btn-ghost">Horarios por area</a>',
    ])

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['total'] }}</div>
            <div class="mod-stat__label">Alumnos activos</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $resumen['regular'] }}</div>
            <div class="mod-stat__label">Del colegio &middot; S/ {{ number_format($pensiones['regular'], 2) }}</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">{{ $resumen['libre'] }}</div>
            <div class="mod-stat__label">Libres &middot; S/ {{ number_format($pensiones['libre'], 2) }}</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $resumen['ingresos'] }}</div>
            <div class="mod-stat__label">Ingresaron a la universidad</div>
        </div>
    </div>

    @can('academia.gestionar')
        <x-mod-modal id="matricular-alumno" titulo="Matricular alumno"
                     subtitulo="Un alumno del colegio se enlaza a su ficha; uno libre trae sus propios datos."
                     boton="Matricular alumno">
            <form method="POST" action="{{ route('academia.store') }}" class="mod-form row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="anio_escolar_id">Ano escolar</label>
                    <select id="anio_escolar_id" name="anio_escolar_id" class="form-select" required>
                        @foreach ($anios as $a)
                            <option value="{{ $a->id }}" @selected($a->activo)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo">Tipo de alumno</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6" id="campo-regular">
                    <label for="estudiante_id">Estudiante del colegio</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select">
                        <option value="">Selecciona</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-none" id="campo-libre">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="nombres">Nombres</label>
                            <input id="nombres" type="text" name="nombres" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="apellidos">Apellidos</label>
                            <input id="apellidos" type="text" name="apellidos" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="dni">DNI</label>
                    <input id="dni" type="text" name="dni" class="form-control" maxlength="20">
                </div>
                <div class="col-md-3">
                    <label for="telefono">Telefono</label>
                    <input id="telefono" type="text" name="telefono" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="area">Area</label>
                    <input id="area" type="text" name="area" class="form-control" placeholder="Ciencias, Letras...">
                </div>
                <div class="col-md-2">
                    <label for="pension">Pension (S/)</label>
                    <input id="pension" type="number" step="0.01" min="0" name="pension" class="form-control"
                           placeholder="Del tarifario">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Matricular</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Alumnos de la academia</h5>
            <form method="GET" action="{{ route('academia.index') }}" class="mod-form d-flex gap-2">
                <select name="tipo" class="form-select" style="min-width:180px">
                    <option value="">Todos</option>
                    @foreach ($tipos as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mod-btn mod-btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr><th>Alumno</th><th>Tipo</th><th>Area</th><th>Pension</th><th>Universidad</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $al)
                        <tr>
                            <td>
                                {{ $al->nombreCompleto() }}
                                <div class="text-muted" style="font-size:.76rem">
                                    {{ $al->documentoIdentidad() ?: 'Sin DNI' }}
                                </div>
                            </td>
                            <td>
                                <span class="mod-chip {{ $al->tipo === 'regular' ? '' : 'mod-chip--neutral' }}">
                                    {{ $tipos[$al->tipo] ?? $al->tipo }}
                                </span>
                            </td>
                            <td>{{ $al->area ?: '--' }}</td>
                            <td>S/ {{ number_format($al->pension, 2) }}</td>
                            <td>
                                @if ($al->ingreso_universidad)
                                    <span class="mod-chip mod-chip--ok">{{ $al->universidad_destino ?: 'Ingreso' }}</span>
                                @else
                                    {{ $al->universidad_destino ?: '--' }}
                                @endif
                            </td>
                            <td>
                                <span class="mod-chip {{ $al->activo ? 'mod-chip--ok' : 'mod-chip--neutral' }}">
                                    {{ $al->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('academia.alumno', $al) }}" class="btn btn-sm btn-outline-primary">
                                    Expediente
                                </a>
                                @can('academia.gestionar')
                                    <form method="POST" action="{{ route('academia.destroy', $al) }}" class="d-inline"
                                          onsubmit="return confirm('Eliminar a este alumno de la academia?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mod-empty">Aun no hay alumnos en la academia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($alumnos->hasPages())
            <div class="mod-card__body">{{ $alumnos->links() }}</div>
        @endif
    </div>

    <script>
        // Alterna entre enlazar a un estudiante del colegio o capturar datos propios.
        (function () {
            var tipo = document.getElementById('tipo');
            if (!tipo) return;

            var regular = document.getElementById('campo-regular');
            var libre = document.getElementById('campo-libre');

            tipo.addEventListener('change', function () {
                var esRegular = this.value === 'regular';
                regular.classList.toggle('d-none', !esRegular);
                libre.classList.toggle('d-none', esRegular);
                if (esRegular) {
                    document.getElementById('nombres').value = '';
                    document.getElementById('apellidos').value = '';
                } else {
                    document.getElementById('estudiante_id').value = '';
                }
            });
        })();
    </script>
@endsection
