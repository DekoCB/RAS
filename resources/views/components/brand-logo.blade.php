@props(['alt' => 'R.A.S'])

@php
    // Se prefiere el archivo real si existe; si no, cae al escudo SVG.
    // Basta con dejar el logo en public/images/logo-ras.svg para reemplazarlo.
    $candidatos = [
        'images/logo-ras.svg',
    ];

    $ruta = 'images/logo-ras.svg';
    foreach ($candidatos as $candidato) {
        if (is_file(public_path($candidato))) {
            $ruta = $candidato;
            break;
        }
    }

    $version = @filemtime(public_path($ruta)) ?: null;
@endphp

<img src="{{ url($ruta) }}{{ $version ? '?v=' . $version : '' }}" alt="{{ $alt }}"
    {{ $attributes->merge(['class' => 'b-logo']) }}>
