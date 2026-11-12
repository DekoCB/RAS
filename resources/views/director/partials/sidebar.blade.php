<div class="navbar-wrapper">

    {{-- ── Logo / Brand ── --}}
    <div class="m-header">
        <a href="{{ route('director.dashboard') }}" class="b-brand text-white">
            <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
        </a>
    </div>

    <div class="navbar-content">

        {{-- ── Perfil del usuario ── --}}
        <div class="dir-sb-profile">
            @if(auth()->user()->avatar_path)
                <img src="{{ Storage::url(auth()->user()->avatar_path) }}"
                     alt="{{ auth()->user()->name }}"
                     class="dir-sb-avatar">
            @else
                <div class="dir-sb-avatar-placeholder">
                    {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                </div>
            @endif
            <div style="min-width:0;">
                <p class="dir-sb-name">{{ auth()->user()->name ?? 'Director' }}</p>
                <span class="dir-sb-role">
                    <i data-feather="shield"></i>
                    Director
                </span>
            </div>
        </div>

        <div class="dir-sb-divider"></div>

        <ul class="pc-navbar">

            {{-- Caption --}}
            <li class="pc-item pc-caption">
                <label>Men&uacute; Principal</label>
            </li>

            {{-- Inicio --}}
            <li class="pc-item {{ request()->routeIs('director.dashboard') ? 'active' : '' }}">
                <a href="{{ route('director.dashboard') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="home"></i></span>
                    <span class="pc-mtext">Inicio</span>
                </a>
            </li>

            {{-- Caption Académico --}}
            <li class="pc-item pc-caption">
                <label>Acad&eacute;mico</label>
            </li>

            {{-- Personal --}}
            <li class="pc-item {{ request()->routeIs('director.personal.*') ? 'active' : '' }}">
                <a href="{{ route('director.personal.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user-plus"></i></span>
                    <span class="pc-mtext">Personal</span>
                </a>
            </li>

            {{-- Gestionar secciones --}}
            <li class="pc-item {{ request()->routeIs('gestionar-secciones.*') ? 'active' : '' }}">
                <a href="{{ route('gestionar-secciones.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="sliders"></i></span>
                    <span class="pc-mtext">Gestionar secciones</span>
                </a>
            </li>

            {{-- Asignar docentes --}}
            <li class="pc-item {{ request()->routeIs('director.docentes.asignaciones*') ? 'active' : '' }}">
                <a href="{{ route('director.docentes.asignaciones') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="users"></i></span>
                    <span class="pc-mtext">Asignar docentes</span>
                </a>
            </li>

            {{-- Horario escolar --}}

            <li class="pc-item {{ request()->routeIs('director.asistencias-docentes.*') ? 'active' : '' }}">
                <a href="{{ route('director.asistencias-docentes.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="clock"></i></span>
                    <span class="pc-mtext">Asistencias docentes</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('director.qr-docentes.*') ? 'active' : '' }}">
                <a href="{{ route('director.qr-docentes.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="camera"></i></span>
                    <span class="pc-mtext">QR Docentes</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('director.horarios.index') ? 'active' : '' }}">
                <a href="{{ route('director.horarios.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="clock"></i></span>
                    <span class="pc-mtext">Horario escolar</span>
                </a>
            </li>

            {{-- Lista de horarios --}}
            <li class="pc-item {{ request()->routeIs('director.horarios.lista') ? 'active' : '' }}">
                <a href="{{ route('director.horarios.lista') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="list"></i></span>
                    <span class="pc-mtext">Lista de horarios</span>
                </a>
            </li>

            {{-- Cierre de notas --}}
            <li class="pc-item {{ request()->routeIs('director.notas.*') ? 'active' : '' }}">
                <a href="{{ route('director.notas.cierre') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="check-square"></i></span>
                    <span class="pc-mtext">Cierre de notas</span>
                </a>
            </li>

            {{-- Separador antes de logout --}}
            <li class="pc-item" style="padding: 0; margin: 8px 0;">
                <div class="dir-sb-divider" style="margin: 0 6px;"></div>
            </li>

            {{-- Logout --}}
            <li class="pc-item dir-sb-logout">
                <a href="{{ route('logout') }}" class="pc-link"
                   onclick="event.preventDefault(); document.getElementById('logout-form-director').submit();">
                    <span class="pc-micon"><i data-feather="log-out"></i></span>
                    <span class="pc-mtext">Cerrar sesi&oacute;n</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form-director" class="d-none">
                    @csrf
                </form>
            </li>

        </ul>

        {{-- ── Footer ── --}}
        <div class="dir-sb-footer">
            <span>R.A.S &mdash; {{ date('Y') }}</span>
        </div>

    </div>
</div>
