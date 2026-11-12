@extends('layouts.mantis')

@section('title', 'Registrar Incidencia | Secretaria')
@section('page-title', 'Registrar Incidencia')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card jp-soft-card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1">Registrar incidencia</h4>
                        <p class="mb-0 text-muted">Completa la informacion para registrar la incidencia.</p>
                    </div>
                    <a href="{{ route('secretaria.incidencias.index') }}" class="btn btn-outline-secondary">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card jp-soft-card">
                <div class="card-body">
                    @php
                        $tipos = ['Disciplinaria', 'Conducta', 'Academica', 'Convivencia', 'Otro'];
                        $gravedades = ['Leve', 'Moderada', 'Grave', 'Critica'];
                    @endphp

                    <form method="POST" action="{{ route('secretaria.incidencias.store') }}">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Revisa los campos marcados.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ano escolar</label>
                                <select name="anio_escolar_id" class="form-select" required>
                                    @foreach ($anios as $anio)
                                        <option value="{{ $anio->id }}">{{ $anio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estudiante</label>
                                <select name="estudiante_id" class="form-select" required>
                                    @foreach ($estudiantes as $estudiante)
                                        <option value="{{ $estudiante->id }}">
                                            {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de incidencia</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="">Seleccione tipo</option>
                                    @foreach ($tipos as $tipo)
                                        <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>
                                            {{ $tipo === 'Academica' ? 'Acad&eacute;mica' : $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gravedad</label>
                                <select name="gravedad" class="form-select" required>
                                    <option value="">Seleccione gravedad</option>
                                    @foreach ($gravedades as $gravedad)
                                        <option value="{{ $gravedad }}" @selected(old('gravedad') === $gravedad)>
                                            {{ $gravedad === 'Critica' ? 'Cr&iacute;tica' : $gravedad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha reporte</label>
                                <input type="date" name="fecha_reporte" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripcion</label>
                                <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('secretaria.incidencias.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar incidencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
