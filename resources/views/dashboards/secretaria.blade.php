@extends('layouts.mantis')

@section('title', 'Dashboard Secretaria | R.A.S')
@section('page-title', 'Dashboard Secretaria')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}?v={{ filemtime(public_path('css/secretaria-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')

    {{-- ══ HERO BANNER ══ --}}
    <div class="sec-hero mb-4">
        <div class="sec-hero__inner">
            <div class="sec-hero__left">
                <div>
                    <p class="sec-hero__school">R.A.S</p>
                    <h2 class="sec-hero__welcome">Bienvenida, Secretaria</h2>
                    <p class="sec-hero__sub">Panel de gestión administrativa y matrículas.</p>
                </div>
            </div>
            <div class="sec-hero__right">
                <div class="sec-hero__pills">
                    <span class="sec-hero__pill">
                        <i data-feather="calendar"></i>
                        Año {{ now()->year }}
                    </span>
                    <span class="sec-hero__pill is-light">
                        <i data-feather="activity"></i>
                        Matrículas hoy: {{ number_format($matriculasHoy) }}
                    </span>
                </div>
                <span class="sec-hero__pill">
                    <i data-feather="trending-up"></i>
                    Pagos hoy: S/ {{ number_format($pagosMontoHoy, 2) }}
                </span>
            </div>
        </div>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="sec-kpi-grid mb-4">
        <div class="sec-kpi">
            <div class="sec-kpi__icon">
                <i data-feather="users"></i>
            </div>
            <div>
                <div class="sec-kpi__num">{{ number_format($estudiantesTotal) }}</div>
                <div class="sec-kpi__label">Estudiantes</div>
                <span class="sec-kpi__chip"><i data-feather="user-plus"></i> Total registrados</span>
            </div>
        </div>
        <div class="sec-kpi">
            <div class="sec-kpi__icon is-green">
                <i data-feather="file-text"></i>
            </div>
            <div>
                <div class="sec-kpi__num">{{ number_format($matriculasTotal) }}</div>
                <div class="sec-kpi__label">Matrículas</div>
                <span class="sec-kpi__chip"><i data-feather="calendar"></i> Año activo</span>
            </div>
        </div>
        <div class="sec-kpi">
            <div class="sec-kpi__icon is-amber">
                <i data-feather="credit-card"></i>
            </div>
            <div>
                <div class="sec-kpi__num">{{ number_format($pagosHoy) }}</div>
                <div class="sec-kpi__label">Pagos del día</div>
                <span class="sec-kpi__chip"><i data-feather="trending-up"></i> Registros de hoy</span>
            </div>
        </div>
    </div>

    {{-- ══ ACCIONES RÁPIDAS ══ --}}
    <div class="card jp-soft-card mb-4">
        <div class="card-header d-flex align-items-center gap-3 py-3 px-4">
            <div class="sec-card-icon">
                <i data-feather="zap"></i>
            </div>
            <div>
                <div class="sec-card-title">Acciones rápidas</div>
                <div class="sec-card-sub">Accesos directos para operaciones frecuentes.</div>
            </div>
        </div>
        <div class="card-body">
            <div class="sec-actions-grid">
                <a href="{{ route('secretaria.estudiantes.index') }}" class="sec-action">
                    <div class="sec-action__top">
                        <div class="sec-action__icon is-green">
                            <i data-feather="user-plus"></i>
                        </div>
                        <span class="sec-action__cta is-green"><i data-feather="arrow-right"></i> Ir</span>
                    </div>
                    <div class="sec-action__title">Registrar estudiante</div>
                    <div class="sec-action__desc">Gestiona el padrón de alumnos matriculados.</div>
                </a>
                <a href="{{ route('secretaria.matriculas.paso1') }}" class="sec-action">
                    <div class="sec-action__top">
                        <div class="sec-action__icon is-amber">
                            <i data-feather="clipboard"></i>
                        </div>
                        <span class="sec-action__cta is-amber"><i data-feather="arrow-right"></i> Ir</span>
                    </div>
                    <div class="sec-action__title">Nueva matrícula</div>
                    <div class="sec-action__desc">Inicia el proceso de matrícula paso a paso.</div>
                </a>
                <a href="{{ route('secretaria.pagos.index') }}" class="sec-action">
                    <div class="sec-action__top">
                        <div class="sec-action__icon">
                            <i data-feather="credit-card"></i>
                        </div>
                        <span class="sec-action__cta"><i data-feather="arrow-right"></i> Ir</span>
                    </div>
                    <div class="sec-action__title">Registrar pago</div>
                    <div class="sec-action__desc">Registra y controla los pagos de pensiones.</div>
                </a>
                <a href="{{ route('secretaria.apoderados.index') }}" class="sec-action">
                    <div class="sec-action__top">
                        <div class="sec-action__icon is-blue">
                            <i data-feather="users"></i>
                        </div>
                        <span class="sec-action__cta is-blue"><i data-feather="arrow-right"></i> Ir</span>
                    </div>
                    <div class="sec-action__title">Apoderados</div>
                    <div class="sec-action__desc">Gestiona el registro de padres y apoderados.</div>
                </a>
                <a href="{{ route('secretaria.reportes.pagos-dia') }}" class="sec-action">
                    <div class="sec-action__top">
                        <div class="sec-action__icon is-slate">
                            <i data-feather="bar-chart-2"></i>
                        </div>
                        <span class="sec-action__cta is-slate"><i data-feather="arrow-right"></i> Ir</span>
                    </div>
                    <div class="sec-action__title">Reportes</div>
                    <div class="sec-action__desc">Consulta reportes de pagos, morosos y pensiones.</div>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            {{-- Matrículas recientes --}}
            <div class="card jp-soft-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="sec-card-icon">
                            <i data-feather="clipboard"></i>
                        </div>
                        <div>
                            <div class="sec-card-title">Matrículas recientes</div>
                            <div class="sec-card-sub">Últimos registros del año activo</div>
                        </div>
                    </div>
                    <a href="{{ route('secretaria.matriculas.index') }}" class="jp-btn-report">
                        Ver todas
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table jp-usuarios-table mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>DNI</th>
                                    <th>Estudiante</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($matriculasRecientes as $matricula)
                                    <tr>
                                        <td>{{ $matricula->fecha_matricula ? \Illuminate\Support\Carbon::parse($matricula->fecha_matricula)->format('d/m/Y') : $matricula->created_at?->format('d/m/Y') }}</td>
                                        <td>{{ $matricula->estudiante->dni ?? '-' }}</td>
                                        <td>{{ $matricula->estudiante->apellidos ?? '' }}, {{ $matricula->estudiante->nombres ?? '' }}</td>
                                        <td>{{ $matricula->grado->nivel ?? '' }} {{ $matricula->grado->nombre ?? '' }}</td>
                                        <td>{{ $matricula->seccion->nombre ?? '-' }}</td>
                                        <td><span class="jp-status-pill is-registrada">REGISTRADA</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Sin matrículas recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagos recientes --}}
            <div class="card jp-soft-card">
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="sec-card-icon is-amber" style="background:linear-gradient(135deg,#1b287b,#22329c)">
                            <i data-feather="credit-card"></i>
                        </div>
                        <div>
                            <div class="sec-card-title">Pagos recientes</div>
                            <div class="sec-card-sub">Pagos registrados recientemente</div>
                        </div>
                    </div>
                    <a href="{{ route('secretaria.reportes.pagos-dia') }}" class="jp-btn-report">
                        Ver reporte
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table jp-usuarios-table mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estudiante</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pagosRecientes as $pago)
                                    @php
                                        $deuda = $pago->deudas->first();
                                        $estudiante = $deuda?->matricula?->estudiante;
                                        $concepto = $deuda?->concepto?->nombre ?? 'Pago';
                                    @endphp
                                    <tr>
                                        <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $estudiante?->apellidos ?? '—' }}, {{ $estudiante?->nombres ?? '' }}</td>
                                        <td>{{ $concepto }}</td>
                                        <td>S/ {{ number_format($pago->monto, 2) }}</td>
                                        @php
                                            $estadoPago = strtolower($pago->estado ?? 'pendiente');
                                            $estadoPagoClase = $estadoPago === 'confirmado' ? 'is-confirmado'
                                                : ($estadoPago === 'pagado' ? 'is-pagado' : 'is-pendiente');
                                        @endphp
                                        <td><span class="jp-status-pill {{ $estadoPagoClase }}">{{ $pago->estado ?? 'pendiente' }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Sin pagos recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            {{-- Alertas de cobranza --}}
            <div class="card jp-soft-card mb-4">
                <div class="card-header d-flex align-items-center gap-3 py-3 px-4">
                    <div class="sec-card-icon is-danger" style="background:linear-gradient(135deg,#b91c1c,#dc2626)">
                        <i data-feather="bell"></i>
                    </div>
                    <div>
                        <div class="sec-card-title">Alertas de cobranza</div>
                        <div class="sec-card-sub">Seguimiento de cobros pendientes</div>
                    </div>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush jp-alert-list">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-500">Morosos de este mes</span>
                            <span class="jp-alert-pill is-danger">{{ number_format($morososMes) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-500">Pagos por conciliar</span>
                            <span class="jp-alert-pill is-primary">{{ number_format($pagosPendientes) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-500">Saldo pendiente</span>
                            <span class="jp-alert-pill is-success">S/ {{ number_format($deudasPendientesMonto, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Recordatorios --}}
            <div class="card jp-soft-card">
                <div class="card-header d-flex align-items-center gap-3 py-3 px-4">
                    <div class="sec-card-icon is-amber" style="background:linear-gradient(135deg,#1b287b,#22329c)">
                        <i data-feather="calendar"></i>
                    </div>
                    <div>
                        <div class="sec-card-title">Recordatorios</div>
                        <div class="sec-card-sub">Actividades pendientes del día</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div style="border-radius:14px; background:#ebf6ff; border:1px solid #d1d7fc; padding:14px 16px;">
                            <div style="font-weight:700; color:#213196; font-size:.85rem; margin-bottom:4px;">
                                <i data-feather="clipboard" style="width:14px;height:14px;"></i>
                                Matrículas hoy
                            </div>
                            <div style="font-size:.82rem; color:#6b7280;">
                                {{ number_format($matriculasHoy) }} matrícula(s) registrada(s) hoy.
                            </div>
                        </div>
                        <div style="border-radius:14px; background:#edf8ff; border:1px solid rgba(34, 50, 156,.3); padding:14px 16px;">
                            <div style="font-weight:700; color:#1b287b; font-size:.85rem; margin-bottom:4px;">
                                <i data-feather="credit-card" style="width:14px;height:14px;"></i>
                                Pagos del día
                            </div>
                            <div style="font-size:.82rem; color:#6b7280;">
                                Monto total: S/ {{ number_format($pagosMontoHoy, 2) }}.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
