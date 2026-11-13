@php
$dashboardUrl = match(true) {
    auth()->user()?->hasRole('SuperAdmin') => route('admin-sistema.dashboard'),
    auth()->user()?->hasRole('Director')   => route('director.dashboard'),
    auth()->user()?->hasRole('Secretaria') => route('secretaria.dashboard'),
    auth()->user()?->hasRole('Docente')    => route('docente.dashboard'),
    auth()->user()?->hasRole('Auxiliar')   => route('auxiliar.dashboard'),
    auth()->user()?->hasRole('Estudiante') => route('estudiante.dashboard'),
    default => url('/'),
};
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ url('images/logo-ras.svg') }}">
    <link rel="apple-touch-icon" href="{{ url('images/logo-ras.svg') }}">

    <title>@yield('title', 'Sistema de Raul')</title>

    <link rel="stylesheet" href="{{ url('assets/css/plugins/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style-preset.css') }}">
    <link rel="stylesheet" href="{{ url('css/mantis-custom.css') }}">
    <link rel="stylesheet" href="{{ url('css/marca.css') }}">

    @stack('styles')
</head>

<body class="@yield('body-class')" data-pc-layout="vertical" data-pc-theme="light" data-pc-theme_contrast="false"
    data-pc-sidebar-caption="true">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <nav class="pc-sidebar">
        @hasSection('sidebar')
            @yield('sidebar')
        @else
            <div class="navbar-wrapper">
                <div class="m-header">
                    <a href="{{ route('dashboard') }}" class="b-brand">
                        <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
                    </a>
                </div>
                <div class="navbar-content">
                    <ul class="pc-navbar">
                        <li class="pc-item pc-caption">
                            <label>Menu</label>
                        </li>
                        <li class="pc-item">
                            <a href="{{ $dashboardUrl }}" class="pc-link">
                                <span class="pc-micon"><i data-feather="home"></i></span>
                                <span class="pc-mtext">Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </nav>

    @php
        $roleLabelRaw = 'Invitado';
        if (auth()->check()) {
            $roles = auth()->user()->getRoleNames();
            $roleLabelRaw = $roles->first() ?? 'Usuario';

            $priorityRoles = ['SuperAdmin', 'Super Admin', 'Administrador', 'Admin', 'Director', 'Secretaria', 'Docente'];
            foreach ($priorityRoles as $priorityRole) {
                if ($roles->contains($priorityRole)) {
                    $roleLabelRaw = $priorityRole;
                    break;
                }
            }
        }
        $roleLabel = \Illuminate\Support\Str::headline($roleLabelRaw);
        $avatarPath = auth()->user()?->avatar_path;
        $avatarUrl = $avatarPath
            ? url('storage/' . ltrim($avatarPath, '/'))
            : url('assets/images/user/avatar-default.svg');
        $profileRoute = route('profile.edit');
        if (auth()->check()) {
            if (auth()->user()->hasRole('SuperAdmin') && \Illuminate\Support\Facades\Route::has('admin-sistema.perfil')) {
                $profileRoute = route('admin-sistema.perfil');
            } elseif (auth()->user()->hasRole('Secretaria') && \Illuminate\Support\Facades\Route::has('secretaria.perfil')) {
                $profileRoute = route('secretaria.perfil');
            } elseif (auth()->user()->hasRole('Docente') && \Illuminate\Support\Facades\Route::has('docente.perfil')) {
                $profileRoute = route('docente.perfil');
            }
        }
    @endphp

    <header class="pc-header">
        <div class="header-wrapper">
            {{-- [Mobile Media Block] start --}}
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    {{-- ======= Menu collapse Icon for DESKTOP ===== --}}
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i data-feather="menu"></i>
                    </a>
                    </li>
                    {{-- ======= Menu collapse Icon for MOBILE ===== --}}
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i data-feather="menu"></i>
                        </a>
                    </li>
                    <li class="pc-h-item d-none d-md-inline-flex">
                        <div class="header-search">
                            <i data-feather="search" class="icon-search"></i>
                            <input type="search" class="form-control" placeholder="Buscar...">
                        </div>
                    </li>
                </ul>
            </div>
            {{-- [Mobile Media Block end] --}}
            <div class="ms-auto">
                <ul class="list-unstyled mb-0">
                    
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                            <img src="{{ $avatarUrl }}" alt="user-image" class="user-avtar">
                            <span>
                                <span class="d-flex align-items-center gap-1">
                                    <span class="h6 mb-0">{{ auth()->user()->name ?? 'Usuario' }}</span>
                                </span>
                                <span class="text-muted d-block text-xs">{{ $roleLabel }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex mb-1">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $avatarUrl }}" alt="user-image"
                                            class="user-avtar wid-35">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ auth()->user()->name ?? 'Usuario' }}</h6>
                                        <span>{{ $roleLabel }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ $profileRoute }}" class="dropdown-item">
                                <i class="ti ti-user"></i>
                                <span>Mi Perfil</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="ti ti-power"></i>
                                    <span>Cerrar Sesión</span>
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="page-header-title">
                                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <ul class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ $dashboardUrl }}">Inicio</a></li>
                                <li class="breadcrumb-item">@yield('page-title', 'Dashboard')</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    <footer class="pc-footer">
        <div class="footer-wrapper">
            <span>© {{ date('Y') }} Plataforma Educativa – R.A.S</span>
        </div>
    </footer>

    <script src="{{ url('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ url('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ url('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ url('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ url('assets/js/pcoded.js') }}"></script>

    @vite(['resources/js/app.js'])

    @stack('scripts')

    <script>
        (function () {
            var sidebar = document.querySelector('.navbar-content');
            if (!sidebar) return;

            // Restaurar posición al cargar
            var saved = sessionStorage.getItem('sidebarScroll');
            if (saved) sidebar.scrollTop = parseInt(saved, 10);

            // Guardar posición antes de navegar
            document.querySelectorAll('.pc-sidebar a[href]').forEach(function (link) {
                link.addEventListener('click', function () {
                    sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
                });
            });
        })();
    </script>

    {{-- ── Session expiry: redirige al login si la sesión expira ── --}}
    <script>
    (function () {
        // Tiempo de sesión en ms (SESSION_LIFETIME en minutos × 60 × 1000)
        // Se redirige 1 minuto antes para evitar el 500
        var sessionMinutes = {{ (int) config('session.lifetime', 120) }};
        var redirectMs = (sessionMinutes - 1) * 60 * 1000;
        var loginUrl   = "{{ route('login') }}";
        var warningMs  = redirectMs - 60000; // aviso 1 min antes de redirigir

        var warnTimer, redirectTimer;

        function resetTimers() {
            clearTimeout(warnTimer);
            clearTimeout(redirectTimer);

            warnTimer = setTimeout(function () {
                // Toast de aviso (si Bootstrap está disponible)
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Tu sesión expirará en 1 minuto.',
                        timer: 8000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            }, warningMs > 0 ? warningMs : 0);

            redirectTimer = setTimeout(function () {
                window.location.href = loginUrl;
            }, redirectMs);
        }

        // Reiniciar el contador cuando el usuario interactúa
        ['click', 'keydown', 'mousemove', 'touchstart'].forEach(function (evt) {
            document.addEventListener(evt, resetTimers, { passive: true });
        });

        resetTimers();
    })();
    </script>

</body>

</html>
