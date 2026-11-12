@extends('layouts.mantis')

@section('title', 'Deudas | Estudiante')
@section('page-title', 'Deudas')
@section('body-class', 'role-estudiante')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/estudiante-modules.css') }}">
    <style>
        .est-debt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .est-debt-card {
            background: #fff;
            border: 1.5px solid rgba(0,0,0,0.07);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }
        .est-debt-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(33, 48, 149,.14);
            border-color: rgba(33, 48, 149,.25);
        }
        .est-debt-card__bar { height: 5px; }
        .est-debt-card.is-pendiente .est-debt-card__bar { background: linear-gradient(90deg,#1f2e8e,#23339e); }
        .est-debt-card.is-vencida   .est-debt-card__bar { background: linear-gradient(90deg,#8b0000,#e81c28); }
        .est-debt-card.is-parcial   .est-debt-card__bar { background: linear-gradient(90deg,#1f2e8d,#25359c); }
        .est-debt-card.is-pagado    .est-debt-card__bar { background: linear-gradient(90deg,#166534,#16a34a); }
        .est-debt-card__header {
            padding: 14px 16px 10px;
            display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;
        }
        .est-debt-card__header h6 { font-size:.92rem; font-weight:800; color:#06081a; margin:0; }
        .est-debt-state {
            display: inline-block; padding: 4px 12px; border-radius: 999px;
            font-size: .72rem; font-weight: 800; letter-spacing: .3px; white-space: nowrap;
        }
        .is-pendiente .est-debt-state { background:#ebfaff; color:#1f2e8e; border: 1px solid rgba(31, 46, 142,.25); }
        .is-vencida   .est-debt-state { background:#fff0f0; color:#c0111c; border: 1px solid rgba(192,17,28,.25); }
        .is-parcial   .est-debt-state { background:#eff6ff; color:#1f2e8d; border: 1px solid rgba(31,46,141,.2); }
        .is-pagado    .est-debt-state { background:#f0fdf4; color:#16a34a; border: 1px solid rgba(22,163,74,.2); }
        .est-debt-card__body { padding: 4px 16px 12px; }
        .est-debt-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: .84rem;
        }
        .est-debt-row:last-child { border-bottom: none; }
        .est-debt-row span { color: #9ca3af; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .3px; }
        .est-debt-row strong { color: #06081a; font-weight: 700; }
        .est-debt-card__footer { padding: 10px 16px 14px; }
        .est-debt-action {
            width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            padding: 10px 16px; border-radius: 12px; font-weight: 800; font-size: .83rem;
            border: 1.5px solid #e5e7eb; background: #fafafa;
            color: #9ca3af; cursor: not-allowed; text-decoration: none;
        }
        .est-debt-action.is-paid {
            background: linear-gradient(135deg,#f0fdf4,#dcfce7);
            border-color: rgba(22,163,74,.3); color: #166534; cursor: default;
        }
        .est-debt-action i { width: 16px; height: 16px; }
        .est-debt-empty {
            grid-column: 1 / -1; text-align: center; padding: 36px 0;
            color: #9ca3af; font-size: .86rem;
        }
    </style>
@endpush

@section('sidebar')
    @include('estudiante.partials.sidebar')
@endsection

@section('content')
    {{-- Botón volver --}}
    <a class="est-back-link" href="{{ route('estudiante.dashboard') }}">
        <i data-feather="arrow-left"></i> Volver al panel
    </a>

    {{-- Hero --}}
    <div class="est-module-hero">
        <div class="est-module-hero__left">
            <div class="est-module-hero__icon">
                <i data-feather="credit-card"></i>
            </div>
            <div>
                <h1 class="est-module-hero__title">Mis deudas</h1>
                <p class="est-module-hero__sub">Controla tus pagos pendientes y vencimientos.</p>
            </div>
        </div>
        <div class="est-module-hero__right">
            <div style="text-align:center; background:rgba(0,0,0,.18); border:1.5px solid rgba(255,255,255,.28); border-radius:16px; padding:14px 24px; min-width:160px; backdrop-filter:blur(6px);">
                <div style="font-size:.72rem; font-weight:700; color:rgba(255,255,255,.8); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">Saldo pendiente</div>
                <div style="font-size:1.8rem; font-weight:900; color:#fff; line-height:1;">S/ {{ number_format((float) $saldoTotal, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Métricas --}}
    <div class="est-metrics">
        <div class="est-metric" style="--met-color:linear-gradient(90deg,#172167,#213095);--met-num:#213095;">
            <div class="est-metric__num">S/ {{ number_format((float) $resumen['saldo_pendiente'], 2) }}</div>
            <div class="est-metric__label">Saldo pendiente</div>
        </div>
        <div class="est-metric" style="--met-color:linear-gradient(90deg,#1c2a81,#23339e);--met-num:#1c2a81;">
            <div class="est-metric__num">S/ {{ number_format((float) $resumen['saldo_vencido'], 2) }}</div>
            <div class="est-metric__label">Vencidas</div>
        </div>
        <div class="est-metric" style="--met-color:linear-gradient(90deg,#1f2e8e,#23339e);--met-num:#1f2e8e;">
            <div class="est-metric__num">S/ {{ number_format((float) $resumen['saldo_por_vencer'], 2) }}</div>
            <div class="est-metric__label">Por vencer</div>
        </div>
        <div class="est-metric" style="--met-color:linear-gradient(90deg,#166534,#16a34a);--met-num:#16a34a;">
            <div class="est-metric__num">S/ {{ number_format((float) $resumen['monto_pagado'], 2) }}</div>
            <div class="est-metric__label">Pagado</div>
        </div>
    </div>

    {{-- Barra de progreso --}}
    <div class="est-progress-wrap">
        @php
            $totalMonto = (float) $resumen['total_monto'];
            $porcentaje = $totalMonto > 0 ? min(100, max(0, ($resumen['monto_pagado'] / $totalMonto) * 100)) : 0;
        @endphp
        <div class="est-progress__labels">
            <span class="est-progress__label">Progreso de pagos</span>
            <span class="est-progress__pct">{{ number_format($porcentaje, 1) }}%</span>
        </div>
        <div class="est-progress__bar">
            <span style="width: {{ $porcentaje }}%"></span>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="est-filters">
        @php
            $filters = [
                'todas'     => 'Todas',
                'pendientes' => 'Pendientes',
                'vencidas'  => 'Vencidas',
                'por-vencer' => 'Por vencer',
                'pagadas'   => 'Pagadas',
            ];
        @endphp
        @foreach ($filters as $key => $label)
            <a href="{{ route('estudiante.deudas.index', ['f' => $key]) }}"
               class="est-filter {{ $filtro === $key ? 'is-active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Tarjetas de deuda --}}
    <div class="est-debt-grid">
        @forelse ($deudas as $deuda)
            @php
                $estado = strtolower($deuda->estado ?? '');
                $fechaVence = $deuda->fecha_vencimiento
                    ? \Carbon\Carbon::parse($deuda->fecha_vencimiento)->locale('es')
                    : null;
                $esVencida = $fechaVence && $fechaVence->isPast() && in_array($estado, ['pendiente', 'parcial']);
                $estadoLabel = $estado ?: 'pendiente';
                $estadoClass = match ($estadoLabel) {
                    'pagado'    => 'is-pagado',
                    'parcial'   => 'is-parcial',
                    'pendiente' => $esVencida ? 'is-vencida' : 'is-pendiente',
                    default     => 'is-pendiente',
                };
            @endphp
            <div class="est-debt-card {{ $estadoClass }}">
                <div class="est-debt-card__bar"></div>
                <div class="est-debt-card__header">
                    <h6>{{ $deuda->concepto?->nombre ?? 'Concepto' }}</h6>
                    <span class="est-debt-state">{{ ucfirst($estadoLabel) }}</span>
                </div>
                <div class="est-debt-card__body">
                    <div class="est-debt-row">
                        <span>Periodo</span>
                        <strong>{{ $deuda->periodo ?? '-' }}</strong>
                    </div>
                    <div class="est-debt-row">
                        <span>Vencimiento</span>
                        <strong>{{ $fechaVence ? $fechaVence->translatedFormat('d M Y') : 'Sin fecha' }}</strong>
                    </div>
                    <div class="est-debt-row">
                        <span>Saldo</span>
                        <strong>S/ {{ number_format((float) $deuda->saldo, 2) }}</strong>
                    </div>
                </div>
                <div class="est-debt-card__footer">
                    @if (in_array($estado, ['pendiente', 'parcial']))
                        <span class="est-debt-action">
                            <i data-feather="clock"></i> Sin pagar
                        </span>
                    @else
                        <span class="est-debt-action is-paid">
                            <i data-feather="check-circle"></i> Pagado
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="est-debt-empty">No hay deudas registradas con este filtro.</div>
        @endforelse
    </div>

    @if (method_exists($deudas, 'links'))
        <div style="margin-top:12px;">{{ $deudas->links() }}</div>
    @endif
@endsection
