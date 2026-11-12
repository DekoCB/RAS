@extends('layouts.mantis')

@section('title', 'Comedor Realino | R.A.S')
@section('page-title', 'Comedor Realino')
@section('body-class', 'role-superadmin tema-comedor')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-comedor.css') }}?v={{ filemtime(public_path('css/tema-comedor.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.mod-modal-script')
    <div class="mod-barra">
        <span class="mod-barra__acciones">
            <a href="{{ route('comedor.evidencias') }}" class="mod-btn-ghost">Evidencias</a>
        </span>
    </div>
    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['comedor'] }}</div>
            <div class="mod-stat__label">Usan el comedor</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $resumen['propio'] }}</div>
            <div class="mod-stat__label">Traen su almuerzo</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">S/ {{ number_format($resumen['cobrado'], 2) }}</div>
            <div class="mod-stat__label">Cobrado</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">S/ {{ number_format($resumen['por_cobrar'], 2) }}</div>
            <div class="mod-stat__label">Por cobrar</div>
        </div>
    </div>

    @if ($horarios->isNotEmpty())
        <div class="mod-card">
            <div class="mod-card__head">
                <div>
                    <h5>Horario de recepcion de almuerzo</h5>
                    <small>Definido por nivel en los datos institucionales (RF-30).</small>
                </div>
            </div>
            <div class="mod-card__body d-flex gap-3 flex-wrap">
                @foreach ($horarios as $h)
                    <span class="mod-chip">
                        {{ $h->nivel }}:
                        {{ \Illuminate\Support\Carbon::parse($h->almuerzo_inicio)->format('g:i a') }}
                        &ndash;
                        {{ \Illuminate\Support\Carbon::parse($h->almuerzo_fin)->format('g:i a') }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    @can('comedor.registrar')
        <x-mod-modal id="registrar-recepcion-de-almuerzo" titulo="Registrar recepcion de almuerzo"
                     subtitulo="Comedor: S/ {{ number_format($tarifas['estudiante'], 2) }} estudiante &middot; S/ {{ number_format($tarifas['docente'], 2) }} docente. Quien trae su propio almuerzo no genera cobro."
                     boton="Registrar recepcion de almuerzo">
            <form method="POST" action="{{ route('comedor.registrar') }}" class="mod-form row g-3">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">

                <div class="col-md-2">
                    <label for="tipo_persona">Tipo</label>
                    <select id="tipo_persona" name="tipo_persona" class="form-select" required>
                        <option value="estudiante">Estudiante</option>
                        <option value="docente">Docente</option>
                    </select>
                </div>

                <div class="col-md-4" id="campo-estudiante">
                    <label for="estudiante_id">Estudiante</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select">
                        <option value="">Selecciona</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-none" id="campo-docente">
                    <label for="docente_id">Docente</label>
                    <select id="docente_id" name="docente_id" class="form-select">
                        <option value="">Selecciona</option>
                        @foreach ($docentes as $d)
                            <option value="{{ $d->id }}">{{ $d->apellidos }}, {{ $d->nombres }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="origen">Origen</label>
                    <select id="origen" name="origen" class="form-select" required>
                        <option value="comedor">Comedor</option>
                        <option value="propio">Trae el suyo</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="hora_recepcion">Hora</label>
                    <input id="hora_recepcion" type="time" name="hora_recepcion" class="form-control"
                           value="{{ now()->format('H:i') }}">
                </div>

                <div class="col-md-8">
                    <label for="observacion">Observacion</label>
                    <input id="observacion" type="text" name="observacion" class="form-control" maxlength="255">
                </div>

                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar recepcion</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Registros del dia</h5>
                <small>{{ \Illuminate\Support\Carbon::parse($fecha)->translatedFormat('l d \d\e F \d\e Y') }}</small>
            </div>
            <form method="GET" action="{{ route('comedor.index') }}" class="mod-form d-flex gap-2">
                <input type="date" name="fecha" class="form-control" value="{{ $fecha }}">
                <button type="submit" class="mod-btn mod-btn-sm">Ver</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Persona</th>
                        <th>Tipo</th>
                        <th>Origen</th>
                        <th>Monto</th>
                        <th>Cobro</th>
                        <th>Observacion</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registros as $r)
                        <tr>
                            <td>{{ $r->hora_recepcion ? \Illuminate\Support\Carbon::parse($r->hora_recepcion)->format('g:i a') : '--' }}</td>
                            <td>{{ $r->nombrePersona() }}</td>
                            <td>{{ $r->estudiante_id ? 'Estudiante' : 'Docente' }}</td>
                            <td>
                                <span class="mod-chip {{ $r->origen === 'comedor' ? '' : 'mod-chip--neutral' }}">
                                    {{ $r->origen === 'comedor' ? 'Comedor' : 'Trae el suyo' }}
                                </span>
                            </td>
                            <td>{{ $r->monto > 0 ? 'S/ ' . number_format($r->monto, 2) : '--' }}</td>
                            <td>
                                @if ($r->origen !== 'comedor')
                                    <span class="mod-chip mod-chip--neutral">Sin cobro</span>
                                @elseif ($r->pagado)
                                    <span class="mod-chip mod-chip--ok">Cobrado</span>
                                @else
                                    <span class="mod-chip mod-chip--pend">Pendiente</span>
                                @endif
                            </td>
                            <td>{{ $r->observacion ?: '--' }}</td>
                            <td class="text-end">
                                @can('comedor.cobrar')
                                    @if ($r->origen === 'comedor' && !$r->pagado)
                                        <form method="POST" action="{{ route('comedor.cobrar', $r) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">Cobrar</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('comedor.registrar')
                                    <form method="POST" action="{{ route('comedor.eliminar', $r) }}" class="d-inline"
                                          onsubmit="return confirm('Eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="mod-empty">No hay registros de almuerzo en esta fecha.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // El formulario alterna entre el selector de estudiantes y el de docentes.
        (function () {
            var tipo = document.getElementById('tipo_persona');
            var campoEstudiante = document.getElementById('campo-estudiante');
            var campoDocente = document.getElementById('campo-docente');

            if (!tipo) return;

            tipo.addEventListener('change', function () {
                var esEstudiante = this.value === 'estudiante';
                campoEstudiante.classList.toggle('d-none', !esEstudiante);
                campoDocente.classList.toggle('d-none', esEstudiante);
                document.getElementById(esEstudiante ? 'docente_id' : 'estudiante_id').value = '';
            });
        })();
    </script>
@endsection
