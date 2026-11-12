@props(['alt' => 'R.A.S'])

@php
    // Se prefiere el archivo real si existe; si no, cae al escudo SVG.
    // Basta con dejar el logo en public/images/logo-real-school.png para reemplazarlo.
    $candidatos = [
        'images/logo-real-school.png',
        'images/logo-real-school.webp',
        'images/logo-real-school.jpg',
        'images/logo-real-school.svg',
    ];

    $ruta = 'images/logo-real-school.svg';
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
