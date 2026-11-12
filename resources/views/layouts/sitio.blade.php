@php
    $nombre = $institucion->nombre ?: 'R.A.S';
    $sigla = $institucion->sigla ?: 'R.A.S';

    $telefonos = array_values(array_filter([
        $institucion->telefono_central,
        $institucion->telefono_secundaria,
        $institucion->telefono_adicional,
    ]));

    $inicio = route('sitio.inicio');
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Admisión') | {{ $nombre }}</title>
    <meta name="description" content="@yield('meta', 'Preinscripción en línea para el proceso de admisión.')">
    <meta name="robots" content="@yield('robots', 'index,follow')">

    <link rel="icon" type="image/png" href="{{ url('images/favicon-ras.png') }}">
    <link rel="apple-touch-icon" href="{{ url('images/favicon-ras.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet"
        href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800|playfair+display:600,700,700i&display=swap">
    <link rel="stylesheet" href="{{ url('css/sitio.css') }}?v={{ filemtime(public_path('css/sitio.css')) }}">
</head>

<body class="sw">

    {{-- Barra de contacto --}}
    <div class="sw-topbar">
        <div class="sw-shell">
            <div class="sw-topbar__list">
                @foreach ($telefonos as $telefono)
                    <a class="sw-topbar__item" href="tel:{{ preg_replace('/\s+/', '', $telefono) }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
                        </svg>
                        {{ $telefono }}
                    </a>
                @endforeach

                @if ($institucion->email)
                    <a class="sw-topbar__item" href="mailto:{{ $institucion->email }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-10 6L2 7"/>
                        </svg>
                        {{ $institucion->email }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Cabecera --}}
    <header class="sw-header">
        <div class="sw-shell">
            <a class="sw-brand" href="{{ $inicio }}">
                <x-brand-logo :alt="$nombre" />
                <span>
                    <span class="sw-brand__name">{{ $sigla }}</span>
                    <span class="sw-brand__sub">Institución Educativa Privada</span>
                </span>
            </a>

            <nav class="sw-nav" id="swNav">
                <a class="sw-nav__link" href="{{ $inicio }}#nosotros">Nosotros</a>
                <a class="sw-nav__link" href="{{ $inicio }}#niveles">Niveles</a>
                <a class="sw-nav__link" href="{{ $inicio }}#sedes">Sedes</a>
                <a class="sw-nav__link" href="{{ route('preinscripcion.consulta') }}">Consultar inscripción</a>
                <a class="sw-btn sw-btn--navy" href="{{ route('login') }}">Intranet</a>
            </nav>

            <button class="sw-burger" id="swBurger" type="button" aria-label="Abrir menú" aria-expanded="false"
                aria-controls="swNav">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- Pie --}}
    <footer class="sw-footer">
        <div class="sw-shell">
            <div class="sw-footer__bottom">
                <span>&copy; {{ date('Y') }} {{ $nombre }}. Todos los derechos reservados.</span>
                <span><a href="{{ $inicio }}">Volver al inicio</a></span>
            </div>
        </div>
    </footer>

    <script>
        // Menu movil
        var burger = document.getElementById('swBurger');
        var nav = document.getElementById('swNav');

        burger.addEventListener('click', function () {
            var abierto = nav.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });
    </script>

    @stack('scripts')
</body>

</html>
