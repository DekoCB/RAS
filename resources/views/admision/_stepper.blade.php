{{-- RF-12: stepper horizontal del asistente. Los pasos ya alcanzados son enlaces. --}}
@php
    $stepperPasos = [
        1 => ['Postulante', 'Datos, sede y grado'],
        2 => ['Apoderado', 'Contacto de la familia'],
        3 => ['Documentos', 'Requisitos'],
        4 => ['Derecho de admision', 'Pago y voucher'],
        5 => ['Cronograma', 'Etapas del proceso'],
    ];
    $stepperExiste = isset($postulante) && $postulante->exists;
    $stepperAlcanzado = $stepperExiste ? max($postulante->paso_actual, $paso) : $paso;
@endphp

<nav class="ad-stepper" aria-label="Pasos de la postulacion">
    @foreach ($stepperPasos as $numero => [$titulo, $detalle])
        @php
            $hecho = $numero < $paso;
            $ahora = $numero === $paso;
            $enlazable = $stepperExiste && $numero >= 2 && $numero <= $stepperAlcanzado && !$ahora;
            $clase = $ahora ? 'is-now' : ($hecho ? 'is-done' : '');
        @endphp
        @if ($enlazable)
            <a href="{{ route('admision.paso' . $numero, $postulante) }}" class="ad-step {{ $clase }}">
        @else
            <div class="ad-step {{ $clase }}">
        @endif
                <span class="ad-step__dot">
                    @if ($hecho)<i data-feather="check"></i>@else{{ $numero }}@endif
                </span>
                <span class="ad-step__lbl">{{ $titulo }}</span>
                <span class="ad-step__sub">{{ $ahora ? $detalle : 'Paso ' . $numero }}</span>
        @if ($enlazable)
            </a>
        @else
            </div>
        @endif
    @endforeach
</nav>
