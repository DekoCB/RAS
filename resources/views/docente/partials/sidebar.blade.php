<div class="navbar-wrapper">

    {{-- ── Logo / Brand ── --}}
    <div class="m-header">
        <a href="{{ route('docente.dashboard') }}" class="b-brand text-white">
            <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
        </a>
    </div>

    <div class="navbar-content">

        {{-- ── Perfil del usuario ── --}}
        <div class="doc-sb-profile">
            @if (auth()->user()->avatar_path)
                <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}"
                    class="doc-sb-avatar">
            @else
                <div class="doc-sb-avatar-placeholder">
                    {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                </div>
            @endif
            <div style="min-width:0;">
                <p class="doc-sb-name">{{ auth()->user()->name ?? 'Docente' }}</p>
                <span class="doc-sb-role">
                    <i data-feather="book-open"></i>
                    Docente
                </span>
            </div>
        </div>

        <div class="doc-sb-divider"></div>

        <ul class="pc-navbar">

            <li class="pc-item pc-caption">
                <label>Men&uacute; Principal</label>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.dashboard') ? 'active' : '' }}">
                <a href="{{ route('docente.dashboard') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="home"></i></span>
                    <span class="pc-mtext">Inicio</span>
                </a>
            </li>

            <li class="pc-item pc-caption">
                <label>Acad&eacute;mico</label>
            </li>

            <li class="pc-item pc-hasmenu {{ request()->routeIs('docente.asistencias.*') ? 'active' : '' }}">
                <a href="javascript:void(0)" class="pc-link">
                    <span class="pc-micon"><i data-feather="calendar"></i></span>
                    <span class="pc-mtext">Asistencias</span>
                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                </a>

                <ul class="pc-submenu">
                    <li class="pc-item {{ request()->routeIs('docente.asistencias.index') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('docente.asistencias.index') }}">
                            Lista / Secciones
                        </a>
                    </li>

                    <li class="pc-item {{ request()->routeIs('docente.asistencias.create') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('docente.asistencias.create') }}">
                            Registrar (Manual)
                        </a>
                    </li>

                    <li class="pc-item {{ request()->routeIs('docente.asistencias.historial') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('docente.asistencias.historial') }}">
                            Historial
                        </a>
                    </li>

                    <li class="pc-item {{ request()->routeIs('docente.asistencias.qr') ? 'active' : '' }}">
                        <a class="pc-link" href="{{ route('docente.asistencias.qr') }}">
                            Asistencia QR
                        </a>
                    </li>
                </ul>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.notas.*') ? 'active' : '' }}">
                <a href="{{ route('docente.notas.create') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="edit-3"></i></span>
                    <span class="pc-mtext">Notas</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.incidencias.*') ? 'active' : '' }}">
                <a href="{{ route('docente.incidencias.create') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="alert-triangle"></i></span>
                    <span class="pc-mtext">Incidencias</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.horarios.*') ? 'active' : '' }}">
                <a href="{{ route('docente.horarios.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="clock"></i></span>
                    <span class="pc-mtext">Mi horario</span>
                </a>
            </li>

            <li class="pc-item pc-caption">
                <label>Cuenta</label>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.carnet.*') ? 'active' : '' }}">
                <a href="{{ route('docente.carnet.show') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="credit-card"></i></span>
                    <span class="pc-mtext">Mi carnet</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.asistencia-docente.*') ? 'active' : '' }}">
                <a href="{{ route('docente.asistencia-docente.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="check-square"></i></span>
                    <span class="pc-mtext">Mis asistencias</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('docente.perfil') ? 'active' : '' }}">
                <a href="{{ route('docente.perfil') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user"></i></span>
                    <span class="pc-mtext">Mi perfil</span>
                </a>
            </li>

            <li class="pc-item" style="padding:0;margin:8px 0;">
                <div class="doc-sb-divider" style="margin:0 6px;"></div>
            </li>

            <li class="pc-item doc-sb-logout">
                <a href="{{ route('logout') }}" class="pc-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form-docente').submit();">
                    <span class="pc-micon"><i data-feather="log-out"></i></span>
                    <span class="pc-mtext">Cerrar sesi&oacute;n</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form-docente" class="d-none">
                    @csrf
                </form>
            </li>

        </ul>

        <div class="doc-sb-footer">
            <span>R.A.S &mdash; {{ date('Y') }}</span>
        </div>

    </div>
</div>
