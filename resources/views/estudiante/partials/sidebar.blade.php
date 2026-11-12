<div class="navbar-wrapper">
    <div class="m-header">
        <a href="{{ route('dashboard') }}" class="b-brand text-white">
            <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
        </a>
    </div>
    <div class="navbar-content">
        <ul class="pc-navbar">
            <li class="pc-item pc-caption">
                <label>Estudiante</label>
            </li>

            <li class="pc-item {{ request()->routeIs('estudiante.dashboard') ? 'active' : '' }}">
                <a href="{{ route('estudiante.dashboard') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="home"></i></span>
                    <span class="pc-mtext">Inicio</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('estudiante.asistencias.*') ? 'active' : '' }}">
                <a href="{{ route('estudiante.asistencias.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="calendar"></i></span>
                    <span class="pc-mtext">Asistencias</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('estudiante.notas.*') ? 'active' : '' }}">
                <a href="{{ route('estudiante.notas.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="edit-3"></i></span>
                    <span class="pc-mtext">Notas</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('estudiante.deudas.*') ? 'active' : '' }}">
                <a href="{{ route('estudiante.deudas.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="credit-card"></i></span>
                    <span class="pc-mtext">Deudas</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('estudiante.pagos.*') ? 'active' : '' }}">
                <a href="{{ route('estudiante.pagos.create') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="dollar-sign"></i></span>
                    <span class="pc-mtext">Pago Virtual</span>
                </a>
            </li>

            <li class="pc-item">
                <a href="{{ route('logout') }}" class="pc-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form-estudiante').submit();">
                    <span class="pc-micon"><i data-feather="log-out"></i></span>
                    <span class="pc-mtext">Cerrar Sesion</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form-estudiante" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</div>
