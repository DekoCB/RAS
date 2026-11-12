@extends('layouts.mantis')

@section('title', 'Historial de Caja | Secretaria')
@section('page-title', 'Historial de Caja')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <style>
        .ch-hero {
            background: linear-gradient(135deg,#162061,#1c297e,#1f2e8f);
            border-radius: 20px; padding: 24px 30px; color:#fff;
            display:flex; align-items:center; justify-content:space-between;
            box-shadow:0 4px 20px rgba(22, 32, 97,.10); margin-bottom:24px;
        }
        .ch-hero-title { font-size:1.4rem; font-weight:700; margin:0; }
        .ch-hero-sub   { opacity:.85; font-size:.9rem; margin-top:4px; }

        .ch-card {
            background:#fff; border-radius:16px;
            box-shadow:0 2px 12px rgba(0,0,0,.06);
            border:1.5px solid #f1f5f9; overflow:hidden;
        }
        .ch-table { width:100%; border-collapse:collapse; }
        .ch-table th {
            background:#f8fafc; font-size:.73rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.06em; color:#94a3b8;
            padding:11px 16px; text-align:left;
        }
        .ch-table td { padding:12px 16px; border-bottom:1px solid #f1f5f9; font-size:.88rem; color:#374151; }
        .ch-table tr:last-child td { border-bottom:none; }
        .ch-table tr:hover td { background:#fafafa; }

        .badge-abierta  { background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:700; }
        .badge-cerrada  { background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:700; }

        .btn-nueva-caja {
            background:linear-gradient(135deg,#162061,#1c297e);
            color:#fff; border:none; border-radius:12px;
            padding:10px 22px; font-size:.9rem; font-weight:700;
            cursor:pointer; text-decoration:none;
            display:inline-flex; align-items:center; gap:8px;
            transition:opacity .2s;
        }
        .btn-nueva-caja:hover { opacity:.9; color:#fff; }
    </style>
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')

    <div class="ch-hero">
        <div>
            <p class="ch-hero-title">
                <i data-feather="archive" style="width:24px;height:24px;vertical-align:middle;margin-right:8px;"></i>
                Historial de Caja
            </p>
            <p class="ch-hero-sub">Registro de todas tus sesiones de caja</p>
        </div>
        <a href="{{ route('secretaria.caja.index') }}" class="btn-nueva-caja">
            <i data-feather="plus" style="width:15px;height:15px;"></i>
            Ir a Caja
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="ch-card">
        @if($cajas->isEmpty())
            <div style="padding:40px;text-align:center;color:#94a3b8;">
                <i data-feather="inbox" style="width:36px;height:36px;margin-bottom:10px;"></i>
                <p style="margin:0;">No hay cajas registradas aún.</p>
            </div>
        @else
            <table class="ch-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Saldo Inicial</th>
                        <th>Total Cobrado</th>
                        <th>Efectivo</th>
                        <th>Digital</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cajas as $caja)
                        @php
                            $pagos  = $caja->getPagos();
                            $totMet = collect();
                            foreach ($pagos as $p) {
                                foreach ($p->metodos_decoded as $m) {
                                    $nm = $m['metodo'] ?? 'Otro';
                                    $totMet[$nm] = ($totMet[$nm] ?? 0) + (float)($m['monto'] ?? 0);
                                }
                            }
                            $efec  = (float)($totMet->get('Efectivo', 0));
                            $dig   = (float)($totMet->except('Efectivo')->sum());
                            $total = $efec + $dig;
                        @endphp
                        <tr>
                            <td style="color:#94a3b8;">{{ $caja->id }}</td>
                            <td>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</td>
                            <td>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '—' }}</td>
                            <td>S/ {{ number_format($caja->saldo_inicial, 2) }}</td>
                            <td style="font-weight:700;">S/ {{ number_format($total, 2) }}</td>
                            <td style="color:#15803d;font-weight:600;">S/ {{ number_format($efec, 2) }}</td>
                            <td style="color:#1f2e8d;font-weight:600;">S/ {{ number_format($dig, 2) }}</td>
                            <td>
                                @if($caja->estado === 'abierta')
                                    <span class="badge-abierta">Abierta</span>
                                @else
                                    <span class="badge-cerrada">Cerrada</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:14px 16px;">
                {{ $cajas->links() }}
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
