<div class="navbar-wrapper">
    <div class="m-header">
        <a href="{{ route('secretaria.dashboard') }}" class="b-brand text-white">
            <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
        </a>
    </div>

    <div class="navbar-content">

        {{-- Perfil del usuario --}}
        <div class="sec-sb-profile">
            <div class="sec-sb-avatar-placeholder">
                <i data-feather="user" style="width:22px;height:22px;stroke:currentColor;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <p class="sec-sb-name">{{ auth()->user()->name ?? 'Secretaria' }}</p>
                <span class="sec-sb-role">
                    <i data-feather="shield"></i>
                    Secretaria
                </span>
            </div>
        </div>

        <div class="sec-sb-divider"></div>

        <ul class="pc-navbar">
            <li class="pc-item pc-caption">
                <label>Menú Principal</label>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.dashboard') ? 'active' : '' }}">
                <a href="{{ route('secretaria.dashboard') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="home"></i></span>
                    <span class="pc-mtext">Inicio</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.asistencias-docentes.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.asistencias-docentes.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user"></i></span>
                    <span class="pc-mtext">QR Docentes</span>
                </a>
            </li>
            
            <li class="pc-item {{ request()->routeIs('secretaria.usuarios.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.usuarios.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="users"></i></span>
                    <span class="pc-mtext">Personal</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('gestionar-secciones.*') ? 'active' : '' }}">
                <a href="{{ route('gestionar-secciones.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="layers"></i></span>
                    <span class="pc-mtext">Gestionar Secciones</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.matriculas.*') && !request()->routeIs('secretaria.matriculas.pre-matriculas') ? 'active' : '' }}">
                <a href="{{ route('secretaria.matriculas.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="file-text"></i></span>
                    <span class="pc-mtext">Matrículas</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.matriculas.pre-matriculas') ? 'active' : '' }}">
                <a href="{{ route('secretaria.matriculas.pre-matriculas') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="globe"></i></span>
                    <span class="pc-mtext">Pre-matrículas</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.estudiantes.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.estudiantes.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user-check"></i></span>
                    <span class="pc-mtext">Alumnos / Padres</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.retiros.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.retiros.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user-minus"></i></span>
                    <span class="pc-mtext">Retiros</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.asistencias.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.asistencias.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="calendar"></i></span>
                    <span class="pc-mtext">Asistencias</span>
                </a>
            </li>

            <li class="pc-item pc-caption">
                <label>Administración</label>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.productos.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.productos.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="package"></i></span>
                    <span class="pc-mtext">Productos</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.pagos.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.pagos.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="credit-card"></i></span>
                    <span class="pc-mtext">Pagos</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.caja.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.caja.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="archive"></i></span>
                    <span class="pc-mtext">Caja</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.reportes.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.reportes.pagos-dia') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="bar-chart-2"></i></span>
                    <span class="pc-mtext">Reportes</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.incidencias.*') ? 'active' : '' }}">
                <a href="{{ route('secretaria.incidencias.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="alert-triangle"></i></span>
                    <span class="pc-mtext">Incidencias</span>
                </a>
            </li>

            <li class="pc-item pc-caption">
                <label>Cuenta</label>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.mi-carnet') ? 'active' : '' }}">
                <a href="{{ route('secretaria.mi-carnet') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="credit-card"></i></span>
                    <span class="pc-mtext">Mi Carnet</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.mis-asistencias') ? 'active' : '' }}">
                <a href="{{ route('secretaria.mis-asistencias') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="clock"></i></span>
                    <span class="pc-mtext">Mis Asistencias</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('secretaria.perfil') ? 'active' : '' }}">
                <a href="{{ route('secretaria.perfil') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user"></i></span>
                    <span class="pc-mtext">Mi Perfil</span>
                </a>
            </li>
        </ul>

        <div class="sec-sb-divider"></div>

        {{-- Cerrar sesión --}}
        <div class="sec-sb-logout">
            <ul class="pc-navbar" style="padding:0;margin:0;">
                <li class="pc-item">
                    <a href="{{ route('logout') }}" class="pc-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form-sidebar-sec').submit();">
                        <span class="pc-micon"><i data-feather="log-out"></i></span>
                        <span class="pc-mtext">Cerrar sesión</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form-sidebar-sec"
                        class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>

        <div class="sec-sb-footer">
            <span>R.A.S &mdash; {{ now()->year }}</span>
        </div>

    </div>
</div>
