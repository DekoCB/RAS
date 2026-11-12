@extends('layouts.mantis')

@section('title', 'Pastoral | R.A.S')
@section('page-title', 'Departamento Pastoral')
@section('body-class', 'role-superadmin tema-pastoral')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-pastoral.css') }}?v={{ filemtime(public_path('css/tema-pastoral.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo')

    <div class="mod-stats">
        <div class="mod-stat">
            <div class="mod-stat__num">{{ $resumen['total'] }}</div>
            <div class="mod-stat__label">Registros</div>
        </div>
        <div class="mod-stat s2">
            <div class="mod-stat__num">{{ $resumen['tests'] }}</div>
            <div class="mod-stat__label">Tests pastorales</div>
        </div>
        <div class="mod-stat s4">
            <div class="mod-stat__num">{{ $resumen['cartas'] }}</div>
            <div class="mod-stat__label">Cartas de fe</div>
        </div>
        <div class="mod-stat s3">
            <div class="mod-stat__num">{{ $resumen['favorable'] }}</div>
            <div class="mod-stat__label">Resultado favorable</div>
        </div>
    </div>

    @can('pastoral.gestionar')
        <x-mod-modal id="registrar" titulo="Registrar"
                     subtitulo="Un registro puede corresponder a un postulante en proceso o a un estudiante matriculado."
                     boton="Registrar">
            <form method="POST" action="{{ route('pastoral.store') }}"
                  enctype="multipart/form-data" class="mod-form row g-3">
                @csrf
                <div class="col-md-2">
                    <label for="persona">Corresponde a</label>
                    <select id="persona" name="persona" class="form-select" required>
                        <option value="postulante">Postulante</option>
                        <option value="estudiante">Estudiante</option>
                    </select>
                </div>

                <div class="col-md-4" id="campo-postulante">
                    <label for="postulante_id">Postulante</label>
                    <select id="postulante_id" name="postulante_id" class="form-select">
                        <option value="">Selecciona</option>
                        @foreach ($postulantes as $p)
                            <option value="{{ $p->id }}">{{ $p->codigo }} &middot; {{ $p->apellidos }}, {{ $p->nombres }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-none" id="campo-estudiante">
                    <label for="estudiante_id">Estudiante</label>
                    <select id="estudiante_id" name="estudiante_id" class="form-select">
                        <option value="">Selecciona</option>
                        @foreach ($estudiantes as $e)
                            <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombres }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach ($tipos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" class="form-control"
                           value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-3">
                    <label for="responsable">Responsable</label>
                    <input id="responsable" type="text" name="responsable" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="resultado">Resultado</label>
                    <select id="resultado" name="resultado" class="form-select">
                        @foreach ($resultados as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="archivo">Archivo</label>
                    <input id="archivo" type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="col-md-9">
                    <label for="observacion">Observacion</label>
                    <input id="observacion" type="text" name="observacion" class="form-control">
                </div>
                <div class="col-12 mod-form__pie">
                    <button type="button" class="mod-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mod-btn">Registrar</button>
                </div>
            </form>
        </x-mod-modal>
    @endcan

    <div class="mod-card">
        <div class="mod-card__head">
            <h5>Registros pastorales</h5>
            <form method="GET" action="{{ route('pastoral.index') }}" class="mod-form d-flex gap-2">
                <select name="tipo" class="form-select" style="min-width:200px">
                    <option value="">Todo tipo</option>
                    @foreach ($tipos as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mod-btn mod-btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead><tr><th>Fecha</th><th>Persona</th><th>Tipo</th><th>Resultado</th><th>Responsable</th><th></th></tr></thead>
                <tbody>
                    @forelse ($registros as $r)
                        <tr>
                            <td>{{ $r->fecha->format('d/m/Y') }}</td>
                            <td>{{ $r->persona() }}</td>
                            <td>{{ $tipos[$r->tipo] ?? $r->tipo }}</td>
                            <td>
                                <span class="mod-chip {{ $r->resultado === 'favorable' ? 'mod-chip--ok' : ($r->resultado === 'pendiente' ? 'mod-chip--pend' : 'mod-chip--neutral') }}">
                                    {{ $resultados[$r->resultado] ?? 'Pendiente' }}
                                </span>
                            </td>
                            <td>{{ $r->responsable ?: '--' }}</td>
                            <td class="text-end">
                                @if ($r->archivo)
                                    <a href="{{ asset('storage/' . $r->archivo) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-primary">Ver</a>
                                @endif
                                @can('pastoral.gestionar')
                                    <form method="POST" action="{{ route('pastoral.destroy', $r) }}" class="d-inline"
                                          onsubmit="return confirm('Eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mod-empty">Aun no hay registros pastorales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registros->hasPages())
            <div class="mod-card__body">{{ $registros->links() }}</div>
        @endif
    </div>

    <script>
        // El registro corresponde a un postulante o a un estudiante, no a ambos.
        (function () {
            var persona = document.getElementById('persona');
            if (!persona) return;

            var post = document.getElementById('campo-postulante');
            var est = document.getElementById('campo-estudiante');

            persona.addEventListener('change', function () {
                var esPostulante = this.value === 'postulante';
                post.classList.toggle('d-none', !esPostulante);
                est.classList.toggle('d-none', esPostulante);
                document.getElementById(esPostulante ? 'estudiante_id' : 'postulante_id').value = '';
            });
        })();
    </script>
@endsection
