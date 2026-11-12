@extends('layouts.sitio')

@section('title', 'Consultar inscripcion')
@section('meta', 'Consulta el avance de la inscripcion con el codigo recibido.')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    // Lo que ve la familia; el detalle interno del expediente no se expone.
    $publico = [
        'preinscrito'  => ['Recibida - en revision', 'sw-state--wait', 'Tu solicitud llego. Secretaria la revisara y te contactara.'],
        'borrador'     => ['Recibida - en revision', 'sw-state--wait', 'Tu solicitud llego. Secretaria la revisara y te contactara.'],
        'en_proceso'   => ['En proceso de admision', '', 'Tu expediente esta avanzando en el proceso de admision.'],
        'admitido'     => ['Admitido', 'sw-state--ok', 'Felicitaciones. Acercate al colegio para firmar la matricula.'],
        'no_admitido'  => ['No admitido', 'sw-state--bad', 'Comunicate con el colegio para conocer el detalle.'],
        'desistido'    => ['Postulacion cerrada', 'sw-state--bad', 'La postulacion figura como desistida.'],
    ];
@endphp

<section class="sw-page">
    <div class="sw-shell">

        <div class="sw-page__head">
            <span class="sw-eyebrow" style="justify-content:center;">Admision</span>
            <h1>Consultar mi inscripcion</h1>
            <p>Ingresa el codigo que recibiste al inscribirte y el DNI del postulante.</p>
        </div>

        <div class="sw-card" style="max-width:640px;margin:0 auto;">
            <form method="GET" action="{{ route('preinscripcion.consulta') }}">
                <div class="sw-grid">
                    <div class="sw-field sw-col-6">
                        <label for="codigo">Codigo de inscripcion</label>
                        <input id="codigo" type="text" name="codigo" value="{{ $codigo }}"
                            placeholder="ADM-2027-0001" maxlength="30" required>
                    </div>
                    <div class="sw-field sw-col-6">
                        <label for="dni">DNI del postulante</label>
                        <input id="dni" type="text" name="dni" value="{{ $dni }}" inputmode="numeric"
                            pattern="[0-9]{8}" maxlength="8" required>
                    </div>
                </div>

                <div class="sw-actions">
                    <span class="sw-actions__note">Sin el codigo, escribenos y lo buscamos por ti.</span>
                    <button type="submit" class="sw-btn sw-btn--gold">Consultar</button>
                </div>
            </form>
        </div>

        @if ($buscado)
            @if (!$postulante)
                <div class="sw-card" style="max-width:640px;margin:22px auto 0;">
                    <div class="sw-alert sw-alert--bad" style="margin:0;">
                        No encontramos una inscripcion con ese codigo y DNI. Revisa que ambos datos
                        coincidan con los de la constancia.
                    </div>
                </div>
            @else
                @php
                    [$etiqueta, $clase, $detalle] = $publico[$postulante->estado]
                        ?? ['En revision', 'sw-state--wait', 'Secretaria esta revisando tu expediente.'];
                @endphp
                <div class="sw-card" style="max-width:640px;margin:22px auto 0;">
                    <div style="text-align:center;">
                        <span class="sw-state {{ $clase }}">{{ $etiqueta }}</span>
                        <p style="margin:14px 0 0;color:var(--sw-ink-soft);">{{ $detalle }}</p>
                    </div>

                    <dl class="sw-dl">
                        <div>
                            <dt>Postulante</dt>
                            <dd>{{ $postulante->nombres }} {{ $postulante->apellidos }}</dd>
                        </div>
                        <div>
                            <dt>Codigo</dt>
                            <dd>{{ $postulante->codigo }}</dd>
                        </div>
                        <div>
                            <dt>Ano escolar</dt>
                            <dd>{{ $postulante->anioEscolar?->nombre ?? '--' }}</dd>
                        </div>
                        <div>
                            <dt>Nivel y grado</dt>
                            <dd>{{ $postulante->nivelGrado() }}</dd>
                        </div>
                    </dl>

                    @if ($postulante->matricula_id)
                        <div class="sw-alert sw-alert--info" style="margin:20px 0 0;">
                            <strong>Matricula generada.</strong> El colegio ya registro la matricula de este
                            estudiante para el {{ $postulante->anioEscolar?->nombre }}.
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
