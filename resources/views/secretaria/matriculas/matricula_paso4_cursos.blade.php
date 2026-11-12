@extends('layouts.mantis')

@section('title', 'Paso 4 | Matricula')
@section('page-title', 'Asignaci&oacute;n de Cursos')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/matricula-wizard.css') }}">
@endpush

@section('sidebar')
    @if (auth()->user()?->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
    <div class="card jp-soft-card wizard-top">
        <div class="card-body">
            <div class="wizard-steps">
                <div class="wizard-step">
                    <div class="wizard-step__circle">1</div><span>Nivel y Grado</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">2</div><span>Datos del Estudiante</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">3</div><span>Pago de Matricula</span>
                </div>
                <div class="wizard-step is-active">
                    <div class="wizard-step__circle">4</div><span>Asignaci&oacute;n de Cursos</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">5</div><span>Resumen Final</span>
                </div>
                <div class="wizard-progress"><span style="width:75%"></span></div>
            </div>
        </div>
    </div>

    @php
        $nivel = session('matricula.nivel', '-');
    @endphp

    <form method="POST" action="{{ route('secretaria.matriculas.paso4.guardar') }}">
        @csrf
        <div class="card jp-soft-card matricula-card">
            <div class="card-body">
                <div class="matricula-intro">
                    <div class="matricula-title">
                        <span class="matricula-icon"><i data-feather="book-open"></i></span>
                        <div>
                            <h4 class="mb-1">Cursos del sal&oacute;n</h4>
                            <p class="mb-0 text-muted">Selecciona los cursos que llevar&aacute;n los alumnos de esta
                                secci&oacute;n.</p>
                        </div>
                    </div>
                </div>

                <div class="info-banner">
                    <div class="info-banner__icon"><i data-feather="info"></i></div>
                    <div>
                        <div class="info-banner__text">
                            <strong>Nivel Seleccionado:</strong>
                            {{ $nivel }} - {{ $matricula->grado?->nombre ?? '-' }} -
                            {{ $matricula->seccion?->nombre ?? '-' }}
                        </div>
                        <div class="info-banner__meta">
                            Periodo Acad&eacute;mico:
                            {{ $matricula->periodoAcademico?->codigo ?? ($matricula->anioEscolar?->nombre ?? date('Y')) }}
                            @if ($matricula->periodoAcademico?->nombre)
                                - {{ $matricula->periodoAcademico->nombre }}
                            @endif
                        </div>
                    </div>
                </div>

                @if ($asignacionBloqueada)
                    <div class="info-banner" style="margin-top: 16px;">
                        <div class="info-banner__icon"><i data-feather="lock"></i></div>
                        <div>
                            <div class="info-banner__text">
                                Esta secci&oacute;n ya tiene cursos asignados. No se pueden modificar desde este paso.
                            </div>
                        </div>
                    </div>
                    <div class="form-card mt-3">
                        <div class="d-flex flex-wrap gap-2">
                            @forelse ($cursosAsignados as $curso)
                                <span class="badge bg-primary">{{ $curso->nombre }}</span>
                            @empty
                                <span class="text-muted">No hay cursos asignados.</span>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="info-banner" style="margin-top: 16px;">
                        <div class="info-banner__icon"><i data-feather="info"></i></div>
                        <div>
                            <div class="info-banner__text">
                                Esta secci&oacute;n a&uacute;n no tiene cursos asignados. Puedes continuar y asignarlos m&aacute;s adelante.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="wizard-actions">
                    <a href="{{ route('secretaria.matriculas.paso3') }}" class="btn btn-secondary">Volver</a>
                    <a href="{{ route('secretaria.matriculas.index') }}" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Continuar a Resumen</button>
                </div>
            </div>
        </div>
    </form>
@endsection
