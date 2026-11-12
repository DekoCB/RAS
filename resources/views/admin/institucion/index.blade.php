@extends('layouts.mantis')

@section('title', 'Datos institucionales | R.A.S')
@section('page-title', 'Datos institucionales')
@section('body-class', 'role-superadmin tema-institucion')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-institucion.css') }}?v={{ filemtime(public_path('css/tema-institucion.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mod-barra">
        <span class="mod-barra__acciones">
            <a href="{{ route('sedes.index') }}" class="mod-btn-ghost">Sedes y documentos</a>
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

    {{-- ── Identidad ────────────────────────────────────────────────── --}}
    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Identidad y contacto</h5>
        </div>
        <div class="mod-card__body">
            <form method="POST" action="{{ route('institucion.update') }}" class="mod-form row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label for="nombre">Nombre del colegio</label>
                    <input id="nombre" type="text" name="nombre" class="form-control" required
                           value="{{ old('nombre', $institucion->nombre) }}">
                </div>
                <div class="col-md-3">
                    <label for="sigla">Nombre corto</label>
                    <input id="sigla" type="text" name="sigla" class="form-control"
                           value="{{ old('sigla', $institucion->sigla) }}">
                </div>
                <div class="col-md-3">
                    <label for="ciudad">Ciudad</label>
                    <input id="ciudad" type="text" name="ciudad" class="form-control"
                           value="{{ old('ciudad', $institucion->ciudad) }}">
                </div>

                <div class="col-12">
                    <label for="lema">Lema</label>
                    <input id="lema" type="text" name="lema" class="form-control"
                           value="{{ old('lema', $institucion->lema) }}">
                </div>

                <div class="col-md-3">
                    <label for="telefono_central">Telefono oficina central</label>
                    <input id="telefono_central" type="text" name="telefono_central" class="form-control"
                           value="{{ old('telefono_central', $institucion->telefono_central) }}">
                </div>
                <div class="col-md-3">
                    <label for="telefono_secundaria">Telefono oficina secundaria</label>
                    <input id="telefono_secundaria" type="text" name="telefono_secundaria" class="form-control"
                           value="{{ old('telefono_secundaria', $institucion->telefono_secundaria) }}">
                </div>
                <div class="col-md-3">
                    <label for="telefono_adicional">Telefono adicional</label>
                    <input id="telefono_adicional" type="text" name="telefono_adicional" class="form-control"
                           value="{{ old('telefono_adicional', $institucion->telefono_adicional) }}">
                </div>
                <div class="col-md-3">
                    <label for="email">Correo de contacto</label>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email', $institucion->email) }}">
                </div>

                @php $codigos = $institucion->codigos_modulares ?? []; @endphp
                <div class="col-md-4">
                    <label for="cod_inicial">Codigo modular &mdash; Inicial</label>
                    <input id="cod_inicial" type="text" name="cod_inicial" class="form-control"
                           value="{{ old('cod_inicial', $codigos['Inicial'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="cod_primaria">Codigo modular &mdash; Primaria</label>
                    <input id="cod_primaria" type="text" name="cod_primaria" class="form-control"
                           value="{{ old('cod_primaria', $codigos['Primaria'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="cod_secundaria">Codigo modular &mdash; Secundaria</label>
                    <input id="cod_secundaria" type="text" name="cod_secundaria" class="form-control"
                           value="{{ old('cod_secundaria', $codigos['Secundaria'] ?? '') }}">
                </div>

                <div class="col-md-6">
                    <label for="vision">Vision</label>
                    <textarea id="vision" name="vision" class="form-control" rows="6">{{ old('vision', $institucion->vision) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="mision">Mision</label>
                    <textarea id="mision" name="mision" class="form-control" rows="6">{{ old('mision', $institucion->mision) }}</textarea>
                </div>

                <div class="col-md-4">
                    <label for="himno_titulo">Himno &mdash; titulo</label>
                    <input id="himno_titulo" type="text" name="himno_titulo" class="form-control"
                           value="{{ old('himno_titulo', $institucion->himno_titulo) }}">
                </div>
                <div class="col-md-4">
                    <label for="himno_autor">Himno &mdash; autor</label>
                    <input id="himno_autor" type="text" name="himno_autor" class="form-control"
                           value="{{ old('himno_autor', $institucion->himno_autor) }}">
                </div>
                <div class="col-md-4">
                    <label for="video_institucional">Video institucional (URL)</label>
                    <input id="video_institucional" type="text" name="video_institucional" class="form-control"
                           value="{{ old('video_institucional', $institucion->video_institucional) }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="mod-btn">Guardar datos institucionales</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Valores ──────────────────────────────────────────────────── --}}
    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Valores institucionales</h5>
                <small>{{ $valores->count() }} valor(es) registrados.</small>
            </div>
        </div>
        <div class="mod-card__body">
            <form method="POST" action="{{ route('institucion.valores.guardar') }}" class="mod-form row g-3 mb-4">
                @csrf
                <div class="col-md-3">
                    <label for="valor_nombre">Valor</label>
                    <input id="valor_nombre" type="text" name="nombre" class="form-control" required maxlength="120">
                </div>
                <div class="col-md-7">
                    <label for="valor_descripcion">Descripcion</label>
                    <input id="valor_descripcion" type="text" name="descripcion" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="mod-btn w-100">Agregar</button>
                </div>
            </form>

            <div class="mod-table-wrap">
                <table class="mod-table">
                    <thead>
                        <tr><th>#</th><th>Valor</th><th>Descripcion</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($valores as $v)
                            <tr>
                                <td>{{ $v->orden }}</td>
                                <td><strong>{{ $v->nombre }}</strong></td>
                                <td>{{ $v->descripcion }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('institucion.valores.eliminar', $v) }}"
                                          onsubmit="return confirm('Eliminar este valor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="mod-empty">Sin valores registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Cultura ──────────────────────────────────────────────────── --}}
    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Cultura institucional</h5>
                <small>Frases de cultura; el area es opcional y corresponde al letrero fisico.</small>
            </div>
        </div>
        <div class="mod-card__body">
            <form method="POST" action="{{ route('institucion.frases.guardar') }}" class="mod-form row g-3 mb-4">
                @csrf
                <div class="col-md-7">
                    <label for="frase_texto">Frase</label>
                    <input id="frase_texto" type="text" name="frase" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-3">
                    <label for="frase_area">Area (opcional)</label>
                    <input id="frase_area" type="text" name="area" class="form-control" maxlength="120">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="mod-btn w-100">Agregar</button>
                </div>
            </form>

            <div class="mod-table-wrap">
                <table class="mod-table">
                    <thead>
                        <tr><th>#</th><th>Frase</th><th>Area</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($frases as $f)
                            <tr>
                                <td>{{ $f->orden }}</td>
                                <td>{{ $f->frase }}</td>
                                <td>{{ $f->area ? $f->area : '--' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('institucion.frases.eliminar', $f) }}"
                                          onsubmit="return confirm('Eliminar esta frase?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="mod-empty">Sin frases registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Horarios ─────────────────────────────────────────────────── --}}
    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Horarios de ingreso, salida y almuerzo</h5>
                <small>Art. 153 del Reglamento Interno. Sin grado la regla aplica a todo el nivel; con grado, la del grado manda.</small>
            </div>
        </div>
        <div class="mod-card__body">
            <form method="POST" action="{{ route('institucion.horarios.guardar') }}" class="mod-form row g-3 mb-4">
                @csrf
                <div class="col-md-2">
                    <label for="h_nivel">Nivel</label>
                    <select id="h_nivel" name="nivel" class="form-select" required>
                        @foreach (['Inicial', 'Primaria', 'Secundaria'] as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="h_grado">Grado (opcional)</label>
                    <select id="h_grado" name="grado_id" class="form-select">
                        <option value="">Todo el nivel</option>
                        @foreach ($grados as $g)
                            <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="h_ingreso">Ingreso</label>
                    <input id="h_ingreso" type="time" name="hora_ingreso" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="h_salida">Salida</label>
                    <input id="h_salida" type="time" name="hora_salida" class="form-control">
                </div>
                <div class="col-md-3 row g-2">
                    <div class="col-6">
                        <label for="h_tolerancia">Tolerancia (min)</label>
                        <input id="h_tolerancia" type="number" min="0" max="120" name="tolerancia_minutos" class="form-control" placeholder="10">
                    </div>
                    <div class="col-6">
                        <label for="h_espera">Espera máx. (min)</label>
                        <input id="h_espera" type="number" min="0" max="120" name="espera_maxima_minutos" class="form-control" placeholder="15">
                    </div>
                </div>
                <div class="col-md-3 row g-2">
                    <div class="col-6">
                        <label for="h_alm_ini">Almuerzo desde</label>
                        <input id="h_alm_ini" type="time" name="almuerzo_inicio" class="form-control">
                    </div>
                    <div class="col-6">
                        <label for="h_alm_fin">hasta</label>
                        <input id="h_alm_fin" type="time" name="almuerzo_fin" class="form-control">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="mod-btn">Guardar horario</button>
                </div>
            </form>

            <div class="mod-table-wrap">
                <table class="mod-table">
                    <thead>
                        <tr><th>Nivel</th><th>Grado</th><th>Ingreso</th><th>Tolerancia</th><th>Salida</th><th>Espera máx.</th><th>Almuerzo</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($horarios as $h)
                            <tr>
                                <td>{{ $h->nivel }}</td>
                                <td>{{ $h->grado?->nombre ?? 'Todo el nivel' }}</td>
                                <td>{{ $h->hora_ingreso ? \Illuminate\Support\Carbon::parse($h->hora_ingreso)->format('g:i a') : '--' }}</td>
                                <td>{{ $h->tolerancia_minutos !== null ? $h->tolerancia_minutos . ' min' : '--' }}</td>
                                <td>{{ $h->hora_salida ? \Illuminate\Support\Carbon::parse($h->hora_salida)->format('g:i a') : '--' }}</td>
                                <td>{{ $h->espera_maxima_minutos !== null ? $h->espera_maxima_minutos . ' min' : '--' }}</td>
                                <td>
                                    @if ($h->almuerzo_inicio && $h->almuerzo_fin)
                                        {{ \Illuminate\Support\Carbon::parse($h->almuerzo_inicio)->format('g:i a') }}
                                        &ndash;
                                        {{ \Illuminate\Support\Carbon::parse($h->almuerzo_fin)->format('g:i a') }}
                                    @else
                                        --
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('institucion.horarios.eliminar', $h) }}"
                                          onsubmit="return confirm('Eliminar este horario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="mod-empty">Sin horarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
