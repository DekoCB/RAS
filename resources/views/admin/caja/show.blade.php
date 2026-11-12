@extends('layouts.mantis')

@section('title', 'Detalle de Caja #' . $caja->id)
@section('page-title', 'Detalle de Caja')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    <style>
        .dc-hero {
            background: linear-gradient(135deg, #162061, #1c297e, #1f2e8f);
            border-radius: 20px; padding: 22px 28px; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 20px rgba(22, 32, 97,.10); margin-bottom: 22px;
        }
        .dc-hero-title { font-size: 1.3rem; font-weight: 700; margin: 0; }
        .dc-hero-sub   { opacity: .85; font-size: .88rem; margin-top: 4px; }

        .dc-stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px; margin-bottom: 20px;
        }
        .dc-stat-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9; padding: 16px 20px;
        }
        .dc-stat-label { font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
        .dc-stat-value { font-size: 1.45rem; font-weight: 800; color: #1e293b; }
        .dc-stat-value.verde { color: #15803d; }
        .dc-stat-value.azul  { color: #1f2e8d; }
        .dc-stat-value.rojo  { color: #1c297e; }
        .dc-stat-value.gris  { color: #64748b; }

        .dc-info-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9; padding: 18px 22px;
            margin-bottom: 20px;
        }
        .dc-info-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px 24px;
        }
        .dc-info-item label { font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 2px; }
        .dc-info-item span  { font-size: .9rem; font-weight: 600; color: #1e293b; }

        .dc-section-title {
            font-size: .8rem; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
        }

        .dc-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9; overflow: hidden;
            margin-bottom: 20px;
        }
        .dc-table { width: 100%; border-collapse: collapse; }
        .dc-table th {
            background: #f8fafc; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em; color: #94a3b8;
            padding: 10px 16px; text-align: left; white-space: nowrap;
        }
        .dc-table td { padding: 11px 16px; border-bottom: 1px solid #f1f5f9; font-size: .875rem; color: #374151; }
        .dc-table tr:last-child td { border-bottom: none; }
        .dc-table tr:hover td { background: #fafafa; }

        .badge-confirmado { background: #dcfce7; color: #15803d; padding: 2px 9px; border-radius: 20px; font-size: .72rem; font-weight: 700; }
        .badge-pendiente  { background: #c3f0fe; color: #162064; padding: 2px 9px; border-radius: 20px; font-size: .72rem; font-weight: 700; }
        .badge-anulado    { background: #e2eefe; color: #1c297e; padding: 2px 9px; border-radius: 20px; font-size: .72rem; font-weight: 700; }

        .badge-abierta { background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 700; }
        .badge-cerrada { background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 700; }

        .metodo-chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f1f5f9; color: #374151;
            border-radius: 20px; padding: 2px 8px;
            font-size: .75rem; font-weight: 600; white-space: nowrap;
        }
        .metodo-chip.efectivo { background: #dcfce7; color: #15803d; }
        .metodo-chip.digital  { background: #eff6ff; color: #1f2e8d; }

        .empty-state { padding: 40px; text-align: center; color: #94a3b8; }

        .btn-volver {
            background: rgba(255,255,255,.2); color: #fff; border: none;
            border-radius: 10px; padding: 8px 18px; font-size: .875rem;
            font-weight: 600; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background .2s;
        }
        .btn-volver:hover { background: rgba(255,255,255,.3); color: #fff; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- Hero --}}
    <div class="dc-hero">
        <div>
            <p class="dc-hero-title">
                <i data-feather="archive" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;"></i>
                Caja #{{ $caja->id }}
                @if($caja->estado === 'abierta')
                    <span class="badge-abierta" style="font-size:.75rem;margin-left:8px;">Abierta</span>
                @else
                    <span class="badge-cerrada" style="font-size:.75rem;margin-left:8px;">Cerrada</span>
                @endif
            </p>
            <p class="dc-hero-sub">{{ $caja->usuario->name ?? '—' }} &nbsp;·&nbsp; Apertura: {{ $caja->fecha_apertura->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('admin-sistema.caja.historial-secretarias') }}" class="btn-volver">
            <i data-feather="arrow-left" style="width:15px;height:15px;"></i>
            Volver
        </a>
    </div>

    {{-- Info de la sesión --}}
    <div class="dc-info-card">
        <div class="dc-info-grid">
            <div class="dc-info-item">
                <label>Secretaria</label>
                <span>{{ $caja->usuario->name ?? '—' }}</span>
            </div>
            <div class="dc-info-item">
                <label>Apertura</label>
                <span>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</span>
            </div>
            <div class="dc-info-item">
                <label>Cierre</label>
                <span>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '—' }}</span>
            </div>
            <div class="dc-info-item">
                <label>Saldo inicial</label>
                <span>S/ {{ number_format($caja->saldo_inicial, 2) }}</span>
            </div>
            <div class="dc-info-item">
                <label>Estado</label>
                <span>
                    @if($caja->estado === 'abierta')
                        <span class="badge-abierta">Abierta</span>
                    @else
                        <span class="badge-cerrada">Cerrada</span>
                    @endif
                </span>
            </div>
            @if($caja->observaciones)
            <div class="dc-info-item" style="grid-column: 1/-1;">
                <label>Observaciones</label>
                <span>{{ $caja->observaciones }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="dc-stats">
        <div class="dc-stat-card">
            <div class="dc-stat-label">Total Pagos</div>
            <div class="dc-stat-value gris">{{ $pagos->count() }}</div>
        </div>
        <div class="dc-stat-card">
            <div class="dc-stat-label">Efectivo</div>
            <div class="dc-stat-value verde">S/ {{ number_format($totalEfectivo, 2) }}</div>
        </div>
        <div class="dc-stat-card">
            <div class="dc-stat-label">Digital</div>
            <div class="dc-stat-value azul">S/ {{ number_format($totalDigital, 2) }}</div>
        </div>
        <div class="dc-stat-card">
            <div class="dc-stat-label">Total Cobrado</div>
            <div class="dc-stat-value rojo">S/ {{ number_format($totalGeneral, 2) }}</div>
        </div>
        @foreach($totalesPorMetodo as $metNombre => $metMonto)
        <div class="dc-stat-card">
            <div class="dc-stat-label">{{ $metNombre }}</div>
            <div class="dc-stat-value" style="font-size:1.15rem; color:{{ $metNombre === 'Efectivo' ? '#15803d' : '#1f2e8d' }}">
                S/ {{ number_format($metMonto, 2) }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tabla de pagos --}}
    <div class="dc-card">
        <div style="padding: 16px 20px 8px;">
            <div class="dc-section-title">
                <i data-feather="credit-card" style="width:15px;height:15px;"></i>
                Pagos registrados en esta sesión
            </div>
        </div>
        @if($pagos->isEmpty())
            <div class="empty-state">
                <i data-feather="inbox" style="width:36px;height:36px;display:block;margin:0 auto 10px;"></i>
                <p style="margin:0;">No hay pagos registrados en esta sesión de caja.</p>
            </div>
        @else
        <div style="overflow-x:auto;">
            <table class="dc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno(s)</th>
                        <th>Concepto(s)</th>
                        <th>Método</th>
                        <th style="text-align:right;">Monto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        @php
                            $alumnos = $pago->deudas->map(function($d) {
                                $e = $d->matricula?->estudiante;
                                return $e ? $e->apellidos . ', ' . $e->nombres : null;
                            })->filter()->unique()->values();

                            $conceptos = $pago->deudas->map(function($d) use ($pago) {
                                $nombre = $d->concepto?->nombre ?? '-';
                                $periodo = trim($d->periodo ?? '');
                                $label = $periodo !== '' ? $periodo : $nombre;
                                $aplicado = (float)($d->pivot->monto_aplicado ?? 0);
                                return $label . ' <span style="color:#94a3b8;font-size:.78rem;">(S/ ' . number_format($aplicado, 2) . ')</span>';
                            })->filter()->values();

                            $montoAplicado = $pago->deudas->sum(fn($d) => (float)($d->pivot->monto_aplicado ?? 0));
                            $montoMostrado = $montoAplicado > 0 ? $montoAplicado : (float)$pago->monto;

                            $metodos = $pago->metodos_decoded;
                        @endphp
                        <tr>
                            <td style="color:#94a3b8;font-size:.8rem;font-weight:600;">{{ $pago->id }}</td>
                            <td>
                                @if($alumnos->isEmpty())
                                    <span style="color:#94a3b8;">—</span>
                                @else
                                    @foreach($alumnos as $al)
                                        <div style="font-size:.82rem;font-weight:600;">{{ $al }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @foreach($conceptos as $con)
                                    <div style="font-size:.82rem;">{!! $con !!}</div>
                                @endforeach
                            </td>
                            <td>
                                @if(count($metodos) === 0)
                                    <span class="metodo-chip">{{ $pago->metodo ?? '—' }}</span>
                                @else
                                    @foreach($metodos as $met)
                                        <span class="metodo-chip {{ strtolower($met['metodo'] ?? '') === 'efectivo' ? 'efectivo' : 'digital' }}">
                                            {{ $met['metodo'] ?? '—' }}
                                            <span style="opacity:.7;">S/ {{ number_format((float)($met['monto'] ?? 0), 2) }}</span>
                                        </span>
                                    @endforeach
                                @endif
                            </td>
                            <td style="text-align:right;font-weight:700;">S/ {{ number_format($montoMostrado, 2) }}</td>
                            <td>
                                @if($pago->estado === 'confirmado')
                                    <span class="badge-confirmado">Confirmado</span>
                                @elseif($pago->estado === 'anulado')
                                    <span class="badge-anulado">Anulado</span>
                                @else
                                    <span class="badge-pendiente">{{ ucfirst($pago->estado) }}</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;font-size:.8rem;color:#64748b;">
                                {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                <div style="font-size:.74rem;color:#94a3b8;">{{ \Carbon\Carbon::parse($pago->created_at)->format('H:i') }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin-sistema.pagos.show', $pago->id) }}"
                                   style="font-size:.78rem;color:#1c297e;font-weight:600;text-decoration:none;white-space:nowrap;">
                                    Ver pago
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
