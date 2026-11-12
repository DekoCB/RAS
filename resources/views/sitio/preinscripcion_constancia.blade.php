@extends('layouts.sitio')

@section('title', 'Constancia de inscripcion')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $telefonos = array_values(array_filter([
        $institucion->telefono_central,
        $institucion->telefono_secundaria,
    ]));

    $nombreColegio = $institucion->nombre ?: 'R.A.S';
    $anioEscolar = $postulante->anioEscolar?->nombre ?? '--';

    $bajoMembrete = 'Institucion Educativa Privada'
        . ($institucion->ciudad ? ' - ' . $institucion->ciudad . ', Peru' : '');
@endphp

<section class="sw-page">
    <div class="sw-shell">
        <div class="sw-card sw-ticket sw-doc">

            {{-- Membrete: solo aparece en el papel --}}
            <header class="sw-doc__membrete">
                <img src="{{ url('images/logo-ras.png') }}" alt="{{ $nombreColegio }}">
                <div class="sw-doc__membrete-texto">
                    <strong>{{ $nombreColegio }}</strong>
                    <span>{{ $bajoMembrete }}</span>
                </div>
                <span class="sw-doc__membrete-anio">Admision {{ $anioEscolar }}</span>
            </header>

            <div class="sw-ticket__icon sw-doc__pantalla">
                <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </div>

            <h1 class="sw-doc__titulo">
                <span class="sw-doc__pantalla">Inscripcion recibida</span>
                <span class="sw-doc__papel">Constancia de inscripcion</span>
            </h1>
            <p class="sw-doc__bajada">
                Guarda este codigo. Con el y el DNI del postulante puedes consultar el avance cuando quieras.
            </p>

            <span class="sw-ticket__code">{{ $postulante->codigo }}</span>

            <dl class="sw-dl">
                <div>
                    <dt>Postulante</dt>
                    <dd>{{ $postulante->nombres }} {{ $postulante->apellidos }}</dd>
                </div>
                <div>
                    <dt>DNI</dt>
                    <dd>{{ $postulante->dni }}</dd>
                </div>
                <div>
                    <dt>Ano escolar</dt>
                    <dd>{{ $anioEscolar }}</dd>
                </div>
                <div>
                    <dt>Nivel y grado</dt>
                    <dd>{{ $postulante->nivelGrado() }}</dd>
                </div>
                <div>
                    <dt>Local</dt>
                    <dd>{{ $postulante->sede?->etiquetaPublica() ?? 'Sin preferencia' }}</dd>
                </div>
                <div>
                    <dt>Fecha de registro</dt>
                    <dd>{{ $postulante->preinscrito_at?->format('d/m/Y H:i') ?? '--' }}</dd>
                </div>
            </dl>

            <div class="sw-alert sw-alert--info sw-doc__nota">
                <strong>Esto todavia no es una matricula.</strong>
                Tu solicitud quedo registrada y reservada en el sistema de admision. La matricula se firma
                en el colegio una vez completado el proceso.
            </div>

            <h2 class="sw-doc__subtitulo">Que sigue ahora</h2>
            <ol class="sw-steps-next">
                <li>Secretaria revisa tu solicitud y se comunica contigo al telefono o correo que registraste.</li>
                <li>Se agenda la entrevista de presentacion y se te entrega el cronograma de admision.</li>
                <li>Presentas los documentos del postulante y el pago del derecho de admision.</li>
                <li>Publicado el resultado, pasas a firmar la matricula en el colegio.</li>
            </ol>

            {{-- Pie del documento: solo en el papel --}}
            <footer class="sw-doc__pie">
                <div class="sw-doc__pie-contacto">
                    @foreach ($telefonos as $telefono)
                        <span>{{ $telefono }}</span>
                    @endforeach
                    @if ($institucion->email)
                        <span>{{ $institucion->email }}</span>
                    @endif
                </div>
                <p>
                    Documento generado automaticamente el {{ now()->format('d/m/Y H:i') }}.
                    No requiere firma ni sello y no constituye matricula.
                </p>
            </footer>

            <div class="sw-actions" style="justify-content:center;">
                @if (!empty($telefonos[0]))
                    <a class="sw-btn sw-btn--gold" target="_blank" rel="noopener"
                        href="https://wa.me/51{{ preg_replace('/\D+/', '', $telefonos[0]) }}?text={{ urlencode('Hola, acabo de enviar la inscripcion ' . $postulante->codigo) }}">
                        Avisar por WhatsApp
                    </a>
                @endif
                <a class="sw-btn sw-btn--claro"
                    href="{{ route('preinscripcion.consulta', ['codigo' => $postulante->codigo, 'dni' => $postulante->dni]) }}">
                    Consultar mi inscripcion
                </a>
                <button type="button" class="sw-btn sw-btn--navy" onclick="window.print()">Imprimir constancia</button>
            </div>
        </div>
    </div>
</section>
@endsection
