<div class="navbar-wrapper">
    <div class="m-header">
        <a href="{{ route('admin-sistema.dashboard') }}" class="b-brand text-white">
            <x-brand-logo class="b-logo" />
            <span class="b-title">R.A.S<small>Sistema de gestión</small></span>
        </a>
    </div>

    <div class="navbar-content">

        {{-- Perfil del usuario --}}
        <div class="sa-sb-profile">
            @if (auth()->user()->avatar ?? false)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="sa-sb-avatar">
            @else
                <div class="sa-sb-avatar-placeholder">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
            @endif
            <div style="flex:1;min-width:0;">
                <p class="sa-sb-name">{{ auth()->user()->name ?? 'Gerente' }}</p>
                <span class="sa-sb-role">
                    <i data-feather="briefcase"></i>
                    {{ auth()->user()->getRoleNames()->first() ?? 'SuperAdmin' }}
                </span>
            </div>
        </div>

        <div class="sa-sb-divider"></div>

        @php
            // ── Estado activo de cada módulo (abre su submenú al entrar) ──
            $mConfigAcad = request()->routeIs('admin-sistema.anios-escolares.*')
                || request()->routeIs('admin-sistema.periodos.*')
                || request()->routeIs('admin-sistema.gestionar-secciones.*')
                || request()->routeIs('admin-sistema.cursos.*');

            $mHorarios = request()->routeIs('admin-sistema.horarios.*');

            $mEvaluacion = request()->routeIs('admin-sistema.notas.*')
                || request()->routeIs('academico.evaluaciones')
                || request()->routeIs('academico.ranking')
                || request()->routeIs('academico.rubricas')
                || request()->routeIs('academico.informes')
                || request()->routeIs('academico.recuperaciones')
                || request()->routeIs('academico.proyectos');

            $mDocentes = request()->routeIs('admin-sistema.asignacion-docente.*')
                || request()->routeIs('admin-sistema.asistencias-docentes.*')
                || request()->routeIs('admin-sistema.qr-docentes.*')
                || request()->routeIs('academico.modalidades');

            $mAlumnado = request()->routeIs('secretaria.estudiantes.*')
                || request()->routeIs('secretaria.apoderados.*')
                || request()->routeIs('admin-sistema.matriculas*')
                || request()->routeIs('admin-sistema.retiros.*')
                || request()->routeIs('admin-sistema.incidencias.*');

            $mCaja = request()->routeIs('admin-sistema.caja.*');

            $mCobranza = request()->routeIs('admin-sistema.tarifas.*')
                || request()->routeIs('admin-sistema.descuentos.*')
                || request()->routeIs('admin-sistema.productos.*');

            $mTesoreria = request()->routeIs('admin-sistema.finanzas.*');

            $mServicios = request()->routeIs('biblioteca.*')
                || request()->routeIs('comedor.*')
                || request()->routeIs('psicologia.*')
                || request()->routeIs('pastoral.*')
                || request()->routeIs('academia.*');

            $mInstitucion = request()->routeIs('institucion.*')
                || request()->routeIs('sedes.*')
                || request()->routeIs('reuniones.*');

            $mDocumentos = request()->routeIs('documentos.*');

            $mPatrimonio = request()->routeIs('patrimonio.*');

            $mAccesos = request()->routeIs('admin-sistema.usuarios.*');
        @endphp

        <ul class="pc-navbar">

            {{-- ═══════════════ GENERAL ═══════════════ --}}
            <li class="pc-item pc-caption">
                <label>General</label>
            </li>

            <li class="pc-item {{ request()->routeIs('admin-sistema.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin-sistema.dashboard') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="home"></i></span>
                    <span class="pc-mtext">Inicio</span>
                </a>
            </li>

            <li class="pc-item {{ request()->routeIs('admin-sistema.reportes.*') || request()->routeIs('admin-sistema.reportes-globales.*') ? 'active' : '' }}">
                <a href="{{ route('admin-sistema.reportes.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="bar-chart-2"></i></span>
                    <span class="pc-mtext">Reportes</span>
                </a>
            </li>

            {{-- ═══════════════ ACADÉMICO ═══════════════ --}}
            <li class="pc-item pc-caption">
                <label>Académico</label>
            </li>

            {{-- Configuración Académica --}}
            <li class="pc-item jp-has-sub {{ $mConfigAcad ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="sliders"></i></span>
                    <span class="pc-mtext">Configuración Académica</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.anios-escolares.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.anios-escolares.index') }}">
                            <span class="jp-sub-dot"></span>
                            Año escolar
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.periodos.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.periodos.index') }}">
                            <span class="jp-sub-dot"></span>
                            Periodos
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.gestionar-secciones.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.gestionar-secciones.index') }}">
                            <span class="jp-sub-dot"></span>
                            Gestionar Secciones
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.cursos.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.cursos.index') }}">
                            <span class="jp-sub-dot"></span>
                            Cursos
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Horarios --}}
            <li class="pc-item jp-has-sub {{ $mHorarios ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="clock"></i></span>
                    <span class="pc-mtext">Horarios</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.horarios.index') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.horarios.index') }}">
                            <span class="jp-sub-dot"></span>
                            Horario escolar
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.horarios.lista') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.horarios.lista') }}">
                            <span class="jp-sub-dot"></span>
                            Lista de horarios
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Evaluación y Notas --}}
            <li class="pc-item jp-has-sub {{ $mEvaluacion ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="check-square"></i></span>
                    <span class="pc-mtext">Evaluación y Notas</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.notas.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.notas.cierre') }}">
                            <span class="jp-sub-dot"></span>
                            Cierre de notas
                        </a>
                    </li>
                    @can('academico.ver')
                        <li class="{{ request()->routeIs('academico.evaluaciones') || request()->routeIs('academico.ranking') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('academico.evaluaciones') }}">
                                <span class="jp-sub-dot"></span>
                                Exámenes y ranking
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('academico.rubricas') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('academico.rubricas') }}">
                                <span class="jp-sub-dot"></span>
                                Rúbricas
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('academico.informes') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('academico.informes') }}">
                                <span class="jp-sub-dot"></span>
                                Informes de Inicial
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('academico.recuperaciones') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('academico.recuperaciones') }}">
                                <span class="jp-sub-dot"></span>
                                Recuperaciones
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('academico.proyectos') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('academico.proyectos') }}">
                                <span class="jp-sub-dot"></span>
                                Proyectos pedagógicos
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>

            {{-- Docentes --}}
            <li class="pc-item jp-has-sub {{ $mDocentes ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="user-check"></i></span>
                    <span class="pc-mtext">Docentes</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.asignacion-docente.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.asignacion-docente.index') }}">
                            <span class="jp-sub-dot"></span>
                            Asignación docente
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.asistencias-docentes.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.asistencias-docentes.index') }}">
                            <span class="jp-sub-dot"></span>
                            Asistencias docentes
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.qr-docentes.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.qr-docentes.index') }}">
                            <span class="jp-sub-dot"></span>
                            QR Docentes
                        </a>
                    </li>
                    @can('academico.ver')
                        <li class="{{ request()->routeIs('academico.modalidades') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('academico.modalidades') }}">
                                <span class="jp-sub-dot"></span>
                                Modalidad docente
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>

            {{-- ═══════════════ ESTUDIANTES ═══════════════ --}}
            <li class="pc-item pc-caption">
                <label>Estudiantes</label>
            </li>

            @can('admision.ver')
                <li class="pc-item {{ request()->routeIs('admision.*') ? 'active' : '' }}">
                    <a href="{{ route('admision.index') }}" class="pc-link">
                        <span class="pc-micon"><i data-feather="user-plus"></i></span>
                        <span class="pc-mtext">Admisión</span>
                    </a>
                </li>
            @endcan

            {{-- Alumnado --}}
            <li class="pc-item jp-has-sub {{ $mAlumnado ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="users"></i></span>
                    <span class="pc-mtext">Alumnado</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('secretaria.estudiantes.*') || request()->routeIs('secretaria.apoderados.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('secretaria.estudiantes.index') }}">
                            <span class="jp-sub-dot"></span>
                            Alumnos / Padres
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.matriculas*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.matriculas.index') }}">
                            <span class="jp-sub-dot"></span>
                            Matrículas
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.matriculas.pre-matriculas') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.matriculas.pre-matriculas') }}">
                            <span class="jp-sub-dot"></span>
                            Pre-matr&iacute;culas
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.retiros.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.retiros.index') }}">
                            <span class="jp-sub-dot"></span>
                            Retiros
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.incidencias.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.incidencias.index') }}">
                            <span class="jp-sub-dot"></span>
                            Incidencias
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ═══════════════ FINANZAS ═══════════════ --}}
            <li class="pc-item pc-caption">
                <label>Finanzas</label>
            </li>

            <li class="pc-item {{ request()->routeIs('admin-sistema.pagos.*') || request()->routeIs('admin-sistema.reportes.pagos-dia') ? 'active' : '' }}">
                <a href="{{ route('admin-sistema.pagos.index') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="credit-card"></i></span>
                    <span class="pc-mtext">Pagos</span>
                </a>
            </li>

            {{-- Caja --}}
            <li class="pc-item jp-has-sub {{ $mCaja ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="archive"></i></span>
                    <span class="pc-mtext">Caja</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.caja.index') || request()->routeIs('admin-sistema.caja.cierre') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.caja.index') }}">
                            <span class="jp-sub-dot"></span>
                            Mi Caja
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.caja.historial-secretarias') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.caja.historial-secretarias') }}">
                            <span class="jp-sub-dot"></span>
                            Historial Secretarias
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Cobranza --}}
            <li class="pc-item jp-has-sub {{ $mCobranza ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="dollar-sign"></i></span>
                    <span class="pc-mtext">Cobranza</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.tarifas.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.tarifas.index') }}">
                            <span class="jp-sub-dot"></span>
                            Tarifario
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.descuentos.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.descuentos.index') }}">
                            <span class="jp-sub-dot"></span>
                            Descuentos
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.productos.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.productos.index') }}">
                            <span class="jp-sub-dot"></span>
                            Productos
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Tesorería --}}
            <li class="pc-item jp-has-sub {{ $mTesoreria ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="trending-up"></i></span>
                    <span class="pc-mtext">Tesorería</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.finanzas.ingresos') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.finanzas.ingresos') }}">
                            <span class="jp-sub-dot"></span>
                            Ingresos
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.finanzas.egresos') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.finanzas.egresos') }}">
                            <span class="jp-sub-dot"></span>
                            Egresos
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin-sistema.finanzas.flujo-caja') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.finanzas.flujo-caja') }}">
                            <span class="jp-sub-dot"></span>
                            Flujo de Caja
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ═══════════════ SERVICIOS ═══════════════ --}}
            @canany(['biblioteca.ver', 'comedor.ver', 'psicologia.ver', 'pastoral.ver', 'academia.ver'])
                <li class="pc-item pc-caption">
                    <label>Servicios</label>
                </li>

                {{-- Bienestar y Servicios --}}
                <li class="pc-item jp-has-sub {{ $mServicios ? 'jp-sub-open' : '' }}">
                    <a href="#!" class="pc-link jp-sub-toggle">
                        <span class="pc-micon"><i data-feather="heart"></i></span>
                        <span class="pc-mtext">Bienestar y Servicios</span>
                        <span class="jp-sub-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </span>
                    </a>
                    <ul class="jp-submenu">
                        @can('biblioteca.ver')
                            <li class="{{ request()->routeIs('biblioteca.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('biblioteca.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Biblioteca
                                </a>
                            </li>
                        @endcan
                        @can('comedor.ver')
                            <li class="{{ request()->routeIs('comedor.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('comedor.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Comedor R.A.S
                                </a>
                            </li>
                        @endcan
                        @can('psicologia.ver')
                            <li class="{{ request()->routeIs('psicologia.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('psicologia.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Psicología
                                </a>
                            </li>
                        @endcan
                        @can('pastoral.ver')
                            <li class="{{ request()->routeIs('pastoral.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('pastoral.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Pastoral
                                </a>
                            </li>
                        @endcan
                        @can('academia.ver')
                            <li class="{{ request()->routeIs('academia.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('academia.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Academia R.A.S
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- ═══════════════ INSTITUCIÓN ═══════════════ --}}
            @canany(['institucion.gestionar', 'sede.ver', 'reunion.ver', 'documento.ver', 'patrimonio.ver'])
                <li class="pc-item pc-caption">
                    <label>Institución</label>
                </li>
            @endcanany

            @canany(['institucion.gestionar', 'sede.ver', 'reunion.ver'])
                {{-- Datos de la IE --}}
                <li class="pc-item jp-has-sub {{ $mInstitucion ? 'jp-sub-open' : '' }}">
                    <a href="#!" class="pc-link jp-sub-toggle">
                        <span class="pc-micon"><i data-feather="book-open"></i></span>
                        <span class="pc-mtext">Datos de la IE</span>
                        <span class="jp-sub-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </span>
                    </a>
                    <ul class="jp-submenu">
                        @can('institucion.gestionar')
                            <li class="{{ request()->routeIs('institucion.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('institucion.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Datos institucionales
                                </a>
                            </li>
                        @endcan
                        @can('sede.ver')
                            <li class="{{ request()->routeIs('sedes.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('sedes.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Sedes y documentos
                                </a>
                            </li>
                        @endcan
                        @can('reunion.ver')
                            <li class="{{ request()->routeIs('reuniones.*') ? 'jp-sub-active' : '' }}">
                                <a href="{{ route('reuniones.index') }}">
                                    <span class="jp-sub-dot"></span>
                                    Reuniones y actas
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @can('documento.ver')
                {{-- Documentos y RR.HH. --}}
                <li class="pc-item jp-has-sub {{ $mDocumentos ? 'jp-sub-open' : '' }}">
                    <a href="#!" class="pc-link jp-sub-toggle">
                        <span class="pc-micon"><i data-feather="folder"></i></span>
                        <span class="pc-mtext">Documentos y RR.HH.</span>
                        <span class="jp-sub-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </span>
                    </a>
                    <ul class="jp-submenu">
                        <li class="{{ request()->routeIs('documentos.index') && request()->route('modulo') === 'coordinacion' ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('documentos.index', 'coordinacion') }}">
                                <span class="jp-sub-dot"></span>
                                Coordinación
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('documentos.index') && request()->route('modulo') === 'rrhh' ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('documentos.index', 'rrhh') }}">
                                <span class="jp-sub-dot"></span>
                                Recursos Humanos
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('documentos.cumpleanos') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('documentos.cumpleanos') }}">
                                <span class="jp-sub-dot"></span>
                                Cumpleaños docentes
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('documentos.index') && request()->route('modulo') === 'legal' ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('documentos.index', 'legal') }}">
                                <span class="jp-sub-dot"></span>
                                Documentos legales
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @hasanyrole('SuperAdmin|Director')
                {{-- Archivador: carpetas libres de la direccion --}}
                <li class="pc-item {{ request()->routeIs('archivador.*') ? 'active' : '' }}">
                    <a href="{{ route('archivador.index') }}" class="pc-link">
                        <span class="pc-micon"><i data-feather="hard-drive"></i></span>
                        <span class="pc-mtext">Archivador</span>
                    </a>
                </li>
            @endhasanyrole

            @can('patrimonio.ver')
                {{-- Patrimonio --}}
                <li class="pc-item jp-has-sub {{ $mPatrimonio ? 'jp-sub-open' : '' }}">
                    <a href="#!" class="pc-link jp-sub-toggle">
                        <span class="pc-micon"><i data-feather="home"></i></span>
                        <span class="pc-mtext">Patrimonio</span>
                        <span class="jp-sub-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </span>
                    </a>
                    <ul class="jp-submenu">
                        <li class="{{ request()->routeIs('patrimonio.activos') || request()->routeIs('patrimonio.mantenimientos') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('patrimonio.activos') }}">
                                <span class="jp-sub-dot"></span>
                                Inmobiliario
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('patrimonio.defensa-civil') ? 'jp-sub-active' : '' }}">
                            <a href="{{ route('patrimonio.defensa-civil') }}">
                                <span class="jp-sub-dot"></span>
                                Defensa Civil
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            {{-- ═══════════════ SISTEMA ═══════════════ --}}
            <li class="pc-item pc-caption">
                <label>Sistema</label>
            </li>

            {{-- Accesos --}}
            <li class="pc-item jp-has-sub {{ $mAccesos ? 'jp-sub-open' : '' }}">
                <a href="#!" class="pc-link jp-sub-toggle">
                    <span class="pc-micon"><i data-feather="shield"></i></span>
                    <span class="pc-mtext">Accesos</span>
                    <span class="jp-sub-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </a>
                <ul class="jp-submenu">
                    <li class="{{ request()->routeIs('admin-sistema.usuarios.*') ? 'jp-sub-active' : '' }}">
                        <a href="{{ route('admin-sistema.usuarios.index') }}">
                            <span class="jp-sub-dot"></span>
                            Usuarios
                        </a>
                    </li>
                </ul>
            </li>

            <li class="pc-item {{ request()->routeIs('admin-sistema.perfil') ? 'active' : '' }}">
                <a href="{{ route('admin-sistema.perfil') }}" class="pc-link">
                    <span class="pc-micon"><i data-feather="user"></i></span>
                    <span class="pc-mtext">Mi Perfil</span>
                </a>
            </li>

        </ul>

        <div class="sa-sb-divider"></div>

        {{-- Cerrar sesión --}}
        <div class="sa-sb-logout">
            <ul class="pc-navbar" style="padding:0;margin:0;">
                <li class="pc-item">
                    <a href="{{ route('logout') }}" class="pc-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form-superadmin').submit();">
                        <span class="pc-micon"><i data-feather="log-out"></i></span>
                        <span class="pc-mtext">Cerrar sesión</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form-superadmin" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>

        <div class="sa-sb-footer">
            <span>R.A.S &mdash; {{ now()->year }}</span>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.jp-sub-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var parent = this.closest('.jp-has-sub');
            var abrir  = !parent.classList.contains('jp-sub-open');

            // Acordeón: al abrir un módulo, cierra los demás del mismo menú
            parent.parentElement.querySelectorAll(':scope > .jp-has-sub.jp-sub-open')
                .forEach(function (otro) {
                    if (otro !== parent) otro.classList.remove('jp-sub-open');
                });

            parent.classList.toggle('jp-sub-open', abrir);
        });
    });
});
</script>
