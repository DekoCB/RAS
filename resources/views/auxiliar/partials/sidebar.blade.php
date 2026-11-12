<div class="navbar-wrapper">

    {{-- ── Logo / Brand ── --}}
    <div class="m-header">
        <a href="{{ route('auxiliar.dashboard') }}" class="b-brand text-white">
            <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
        </a>
    </div>

    <div class="navbar-content">

        {{-- ── Perfil del usuario ── --}}
        <div class="aux-sb-profile">
            @if (auth()->user()->avatar_path)
                <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}"
                    class="aux-sb-avatar">
            @else
                <div class="aux-sb-avatar-placeholder">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
            @endif
            <div style="min-width:0;">
                <p class="aux-sb-name">{{ auth()->user()->name ?? 'Auxiliar' }}</p>
                <span class="aux-sb-role">
                    <i data-feather="shield"></i>
                    Auxiliar
                </span>
            </div>
        </div>

        <div class="aux-sb-divider"></div>

        <ul class="pc-navbar">

            <li class="pc-item pc-caption">
                <label>Men&uacute; Principal</label>
            </li>

            <li class="pc-item {{ request()->routeIs('auxiliar.dashboard') ? 'active' : '' }}">
                <a href="{{ route('auxiliar.dashboard') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="home"></i></span>
                    <span class="pc-mtext">Inicio</span>
                </a>
            </li>

            <li class="pc-item pc-caption">
                <label>Asistencia</label>
            </li>

            <li class="pc-item pc-hasmenu {{ request()->routeIs('auxiliar.asistencias.*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="pc-link">
                    <span class="pc-micon"><i data-feather="calendar"></i></span>
                    <span class="pc-mtext">Asistencias</span>
                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                </a>
                <ul class="pc-submenu">
                    <li class="pc-item {{ request()->routeIs('auxiliar.asistencias.index') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('auxiliar.asistencias.index') }}">
                            Secciones
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('auxiliar.asistencias.create') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('auxiliar.asistencias.create') }}">
                            Registrar Manual
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('auxiliar.asistencias.historial') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('auxiliar.asistencias.historial') }}">
                            Historial
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('auxiliar.asistencias.qr') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('auxiliar.asistencias.qr') }}">
                            Asistencia QR
                        </a>
                    </li>
                </ul>
            </li>

            <li class="pc-item pc-caption">
                <label>Cuenta</label>
            </li>

            <li class="pc-item {{ request()->routeIs('auxiliar.carnet.show') ? 'active' : '' }}">
                <a href="{{ route('auxiliar.carnet.show') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="credit-card"></i></span>
                    <span class="pc-mtext">Mi Carnet</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('auxiliar.mis-asistencias.index') ? 'active' : '' }}">
                <a href="{{ route('auxiliar.mis-asistencias.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="clock"></i></span>
                    <span class="pc-mtext">Mis Asistencias</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('auxiliar.perfil') ? 'active' : '' }}">
                <a href="{{ route('auxiliar.perfil') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user"></i></span>
                    <span class="pc-mtext">Mi perfil</span>
                </a>
            </li>

            <li class="pc-item" style="padding:0;margin:8px 0;">
                <div class="aux-sb-divider" style="margin:0 6px;"></div>
            </li>

            <li class="pc-item aux-sb-logout">
                <a href="{{ route('logout') }}" class="pc-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form-auxiliar').submit();">
                    <span class="pc-micon"><i data-feather="log-out"></i></span>
                    <span class="pc-mtext">Cerrar sesi&oacute;n</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form-auxiliar" class="d-none">
                    @csrf
                </form>
            </li>

        </ul>

        <div class="aux-sb-footer">
            <span>R.A.S &mdash; {{ date('Y') }}</span>
        </div>

    </div>
</div>
