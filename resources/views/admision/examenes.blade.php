@extends('layouts.mantis')

@section('title', 'Examenes de admision | R.A.S')
@section('page-title', 'Examenes de admision')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="ad-page">
    <div class="ad-toolbar">
        <div class="ad-toolbar__lead">
            <div>
                <div class="ad-toolbar__kicker">Evaluacion</div>
                <div class="ad-toolbar__text">
                    <strong>{{ $examenes->count() }}</strong> examen(es) configurados por nivel y año escolar.
                </div>
            </div>
        </div>
        <div class="ad-toolbar__actions">
            <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">
                <i data-feather="arrow-left"></i> Volver a admision
            </a>
        </div>
    </div>

    @include('admision._alertas')

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon ad-card__icon--gold"><i data-feather="award"></i></span>
                <div>
                    <h2>Registrar examen</h2>
                    <p>El puntaje minimo define si un postulante alcanza o no la valla.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admision.examenes.guardar') }}">
            @csrf
            <div class="ad-card__body ad-grid">
                <div class="ad-field c-3">
                    <label for="anio_escolar_id">Año escolar</label>
                    <select id="anio_escolar_id" name="anio_escolar_id" class="ad-select" required>
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id }}" @selected($anio->activo)>{{ $anio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ad-field c-2">
                    <label for="nivel">Nivel</label>
                    <select id="nivel" name="nivel" class="ad-select" required>
                        @foreach ($niveles as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ad-field c-7">
                    <label for="titulo">Titulo</label>
                    <input id="titulo" type="text" name="titulo" class="ad-input" required maxlength="180"
                           value="{{ old('titulo') }}" placeholder="Ej. Examen de admision Primaria 2026">
                </div>
                <div class="ad-field c-2">
                    <label for="puntaje_maximo">Puntaje maximo</label>
                    <input id="puntaje_maximo" type="number" step="0.01" min="1" name="puntaje_maximo"
                           class="ad-input" required value="{{ old('puntaje_maximo', 20) }}">
                </div>
                <div class="ad-field c-2">
                    <label for="puntaje_minimo">Puntaje minimo</label>
                    <input id="puntaje_minimo" type="number" step="0.01" min="0" name="puntaje_minimo"
                           class="ad-input" required value="{{ old('puntaje_minimo', 11) }}">
                </div>
                <div class="ad-field c-8">
                    <label for="descripcion">Descripcion</label>
                    <textarea id="descripcion" name="descripcion" class="ad-textarea" rows="2"
                              placeholder="Opcional">{{ old('descripcion') }}</textarea>
                </div>
            </div>

            <div class="ad-card__foot">
                <button type="submit" class="ad-btn ad-btn--navy">
                    <i data-feather="plus"></i> Registrar examen
                </button>
            </div>
        </form>
    </section>

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon"><i data-feather="list"></i></span>
                <div>
                    <h2>Examenes registrados</h2>
                    <p>Cada postulante solo puede rendir los examenes de su nivel.</p>
                </div>
            </div>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr><th>Año</th><th>Nivel</th><th>Titulo</th><th>Puntajes</th><th>Aplicaciones</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($examenes as $ex)
                        <tr>
                            <td><span class="ad-code">{{ $ex->anioEscolar?->nombre }}</span></td>
                            <td><span class="ad-nivel ad-nivel--{{ strtolower($ex->nivel) }}">{{ $ex->nivel }}</span></td>
                            <td>
                                <span class="ad-strong">{{ $ex->titulo }}</span>
                                @if ($ex->descripcion)
                                    <br><span class="ad-muted" style="font-size:.78rem">{{ $ex->descripcion }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="ad-chip">min {{ $ex->puntaje_minimo }}</span>
                                <span class="ad-chip">max {{ $ex->puntaje_maximo }}</span>
                            </td>
                            <td>{{ $ex->aplicaciones()->count() }}</td>
                            <td class="ad-right">
                                <form method="POST" action="{{ route('admision.examenes.eliminar', $ex) }}"
                                      onsubmit="return confirm('Eliminar este examen y sus resultados?')" class="ad-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-btn ad-btn--danger ad-btn--sm"><i data-feather="trash-2"></i> Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="ad-empty">
                                <span class="ad-empty__icon"><i data-feather="award"></i></span>
                                <strong>Aun no hay examenes registrados</strong>
                                Crea uno por nivel para poder calificar a los postulantes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
