@extends('layouts.mantis')

@section('title', 'Caja | Secretaria')
@section('page-title', 'Caja')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <style>
        :root {
            --caja-primary:    #162061;
            --caja-accent:     #1c297e;
            --caja-light:      #f2f7fe;
            --caja-border:     #cae0fe;
            --caja-green:      #15803d;
            --caja-green-bg:   #dcfce7;
            --caja-blue:       #1f2e8d;
            --caja-blue-bg:    #dbeafe;
            --caja-shadow:     0 4px 20px rgba(22, 32, 97,.10);
        }

        /* ── HERO ── */
        .caja-hero {
            background: linear-gradient(135deg, #162061 0%, #1c297e 40%, #1f2e8f 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: var(--caja-shadow);
            margin-bottom: 24px;
        }
        .caja-hero-title { font-size: 1.6rem; font-weight: 700; margin: 0; }
        .caja-hero-sub   { opacity: .85; font-size: .95rem; margin-top: 4px; }
        .caja-badge-abierta {
            background: #fff;
            color: var(--caja-accent);
            font-weight: 700;
            font-size: .8rem;
            padding: 4px 14px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .caja-badge-cerrada {
            background: rgba(255,255,255,.2);
            color: #fff;
            font-weight: 600;
            font-size: .8rem;
            padding: 4px 14px;
            border-radius: 20px;
        }

        /* ── ABRIR CAJA ── */
        .caja-open-card {
            max-width: 480px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: var(--caja-shadow);
            padding: 40px 36px;
            text-align: center;
            border: 1.5px solid var(--caja-border);
        }
        .caja-open-icon {
            width: 72px; height: 72px;
            background: var(--caja-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            color: var(--caja-accent);
        }
        .caja-open-title { font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .caja-open-sub   { color: #64748b; font-size: .93rem; margin-bottom: 28px; }
        .caja-saldo-label {
            font-size: .82rem; font-weight: 600; color: #374151;
            text-transform: uppercase; letter-spacing: .05em;
            text-align: left; margin-bottom: 6px; display: block;
        }
        .caja-saldo-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            transition: border-color .2s;
            margin-bottom: 20px;
        }
        .caja-saldo-input:focus {
            outline: none;
            border-color: var(--caja-accent);
            box-shadow: 0 0 0 3px rgba(28, 41, 126,.1);
        }
        .btn-abrir-caja {
            background: linear-gradient(135deg, var(--caja-primary), var(--caja-accent));
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 13px 36px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: opacity .2s;
        }
        .btn-abrir-caja:hover { opacity: .9; }

        /* ── STAT CARDS ── */
        .caja-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 16px; margin-bottom: 24px; }
        .caja-stat {
            background: #fff;
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9;
        }
        .caja-stat-label { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: 8px; }
        .caja-stat-value { font-size: 1.65rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .caja-stat-value.green  { color: var(--caja-green); }
        .caja-stat-value.blue   { color: var(--caja-blue); }
        .caja-stat-value.red    { color: var(--caja-accent); }

        /* ── MÉTODOS DE PAGO ── */
        .caja-metodos { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px,1fr)); gap: 14px; margin-bottom: 24px; }
        .caja-metodo-card {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            text-align: center;
            border: 1.5px solid #f1f5f9;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            transition: transform .15s;
        }
        .caja-metodo-card:hover { transform: translateY(-2px); }
        .caja-metodo-img {
            width: 52px; height: 52px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .caja-metodo-name { font-size: .8rem; color: #64748b; font-weight: 600; }
        .caja-metodo-monto { font-size: 1.1rem; font-weight: 800; color: #1e293b; }

        /* ── TABLA RECIENTES ── */
        .caja-table-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .caja-table-header {
            background: var(--caja-light);
            padding: 16px 22px;
            font-weight: 700;
            color: var(--caja-primary);
            font-size: .95rem;
            border-bottom: 1.5px solid var(--caja-border);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .caja-table { width: 100%; border-collapse: collapse; }
        .caja-table th {
            background: #f8fafc;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            padding: 10px 16px;
            text-align: left;
        }
        .caja-table td { padding: 11px 16px; border-bottom: 1px solid #f1f5f9; font-size: .88rem; color: #374151; }
        .caja-table tr:last-child td { border-bottom: none; }
        .caja-table tr:hover td { background: #fafafa; }

        /* ── PILL MÉTODO ── */
        .pill-met {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px; font-size: .78rem; font-weight: 600;
        }
        .pill-met.efectivo { background: var(--caja-green-bg); color: var(--caja-green); }
        .pill-met.digital  { background: var(--caja-blue-bg);  color: var(--caja-blue); }

        /* ── BTN CIERRE ── */
        .btn-cierre {
            background: linear-gradient(135deg, #162061, #1c297e);
            color: #fff; border: none;
            border-radius: 12px; padding: 13px 30px;
            font-size: .95rem; font-weight: 700;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px;
            transition: opacity .2s; text-decoration: none;
        }
        .btn-cierre:hover { opacity: .9; color: #fff; }

        .caja-actions {
            display: flex; justify-content: flex-end;
            gap: 12px; margin-bottom: 32px;
        }

        .efectivo-highlight {
            background: var(--caja-green-bg);
            border: 1.5px solid #bbf7d0;
        }
        .digital-highlight {
            background: var(--caja-blue-bg);
            border: 1.5px solid #bfdbfe;
        }
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

    {{-- HERO --}}
    <div class="caja-hero">
        <div>
            <p class="caja-hero-title">
                <i data-feather="archive" style="width:28px;height:28px;vertical-align:middle;margin-right:8px;"></i>
                Caja Diaria
            </p>
            <p class="caja-hero-sub">Gestión y control de ingresos del día</p>
        </div>
        <div>
            @if($cajaAbierta)
                <span class="caja-badge-abierta">
                    <span style="width:8px;height:8px;background:var(--caja-accent);border-radius:50%;display:inline-block;"></span>
                    CAJA ABIERTA
                </span>
                <div style="color:rgba(255,255,255,.8);font-size:.82rem;margin-top:6px;">
                    Apertura: {{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}
                </div>
            @else
                <span class="caja-badge-cerrada">SIN CAJA ACTIVA</span>
            @endif
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$cajaAbierta)
        {{-- ── FORMULARIO ABRIR CAJA ── --}}
        <div class="caja-open-card">
            <div class="caja-open-icon">
                <i data-feather="unlock" style="width:32px;height:32px;"></i>
            </div>
            <p class="caja-open-title">Abrir caja del día</p>
            <p class="caja-open-sub">Ingresa el saldo inicial con el que cuentas al comenzar la jornada.</p>
            <form action="{{ route('secretaria.caja.abrir') }}" method="POST">
                @csrf
                <label class="caja-saldo-label">Saldo inicial (S/)</label>
                <input
                    type="number"
                    name="saldo_inicial"
                    class="caja-saldo-input @error('saldo_inicial') border-danger @enderror"
                    step="0.01" min="0" value="0.00"
                    placeholder="0.00"
                    required
                >
                @error('saldo_inicial')
                    <p class="text-danger small mb-2">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn-abrir-caja">
                    <i data-feather="unlock" style="width:17px;height:17px;vertical-align:middle;margin-right:6px;"></i>
                    Abrir Caja
                </button>
            </form>
        </div>

    @else
        {{-- ── CAJA ABIERTA ── --}}

        {{-- Stats --}}
        <div class="caja-stats">
            <div class="caja-stat">
                <div class="caja-stat-label">Total General</div>
                <div class="caja-stat-value red">S/ {{ number_format($totalGeneral, 2) }}</div>
            </div>
            <div class="caja-stat efectivo-highlight">
                <div class="caja-stat-label">Efectivo</div>
                <div class="caja-stat-value green">S/ {{ number_format($totalEfectivo, 2) }}</div>
            </div>
            <div class="caja-stat digital-highlight">
                <div class="caja-stat-label">Digital</div>
                <div class="caja-stat-value blue">S/ {{ number_format($totalDigital, 2) }}</div>
            </div>
            <div class="caja-stat">
                <div class="caja-stat-label">Nro. Pagos</div>
                <div class="caja-stat-value">{{ $cantidadPagos }}</div>
            </div>
            <div class="caja-stat">
                <div class="caja-stat-label">Saldo Inicial</div>
                <div class="caja-stat-value" style="font-size:1.3rem;">S/ {{ number_format($cajaAbierta->saldo_inicial, 2) }}</div>
            </div>
        </div>

        {{-- Métodos de pago --}}
        @if($totalesPorMetodo->isNotEmpty())
        <div class="caja-table-card">
            <div class="caja-table-header">
                <i data-feather="pie-chart" style="width:16px;height:16px;"></i>
                Ingresos por método de pago
            </div>
            <div style="padding:16px;">
                <div class="caja-metodos">
                    @php
                        $imagenesMetodo = [
                            'Efectivo'      => null,
                            'Yape'          => 'imagenes_caja/yape.jpg',
                            'Plin'          => 'imagenes_caja/plin.png',
                            'Transferencia' => 'imagenes_caja/bbva.jpg',
                            'Tarjeta'       => 'imagenes_caja/tarjeta.jpg',
                            'Tarjeta BCP'   => 'imagenes_caja/tarjeta.jpg',
                            'BancoNacion'   => 'imagenes_caja/logo-banco-nacion-peru.webp',
                        ];
                    @endphp
                    @foreach($totalesPorMetodo as $metodo => $monto)
                        <div class="caja-metodo-card">
                            @if(isset($imagenesMetodo[$metodo]) && $imagenesMetodo[$metodo])
                                <img src="{{ url('images/' . $imagenesMetodo[$metodo]) }}" alt="{{ $metodo }}" class="caja-metodo-img">
                            @else
                                <div style="width:52px;height:52px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                                    <i data-feather="dollar-sign" style="width:26px;height:26px;color:#15803d;"></i>
                                </div>
                            @endif
                            <div class="caja-metodo-name">{{ $metodo ?? 'Sin método' }}</div>
                            <div class="caja-metodo-monto">S/ {{ number_format($monto, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Pagos recientes --}}
        <div class="caja-table-card">
            <div class="caja-table-header">
                <i data-feather="list" style="width:16px;height:16px;"></i>
                Últimos pagos registrados
            </div>
            @if($pagosRecientes->isEmpty())
                <div style="padding:28px;text-align:center;color:#94a3b8;">
                    <i data-feather="inbox" style="width:32px;height:32px;margin-bottom:8px;"></i>
                    <p style="margin:0;">Aún no hay pagos registrados en esta sesión.</p>
                </div>
            @else
                <table class="caja-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Alumno</th>
                            <th>Concepto</th>
                            <th>Método</th>
                            <th style="text-align:right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagosRecientes as $pago)
                            @php
                                $alumno = $pago->deudas->first()?->matricula?->estudiante;
                                $conceptos = $pago->deudas->pluck('concepto.nombre')->filter()->unique()->implode(', ');
                                $esEfectivo = $pago->metodo_display === 'Efectivo';
                            @endphp
                            <tr>
                                <td style="color:#94a3b8;">{{ $pago->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                                <td>
                                    @if($alumno)
                                        {{ $alumno->apellidos }} {{ $alumno->nombres }}
                                    @else
                                        <span style="color:#cbd5e1;">—</span>
                                    @endif
                                </td>
                                <td>{{ $conceptos ?: '—' }}</td>
                                <td>
                                    <span class="pill-met {{ $esEfectivo ? 'efectivo' : 'digital' }}">
                                        <i data-feather="{{ $esEfectivo ? 'dollar-sign' : 'smartphone' }}" style="width:11px;height:11px;"></i>
                                        {{ $pago->metodo_display }}
                                    </span>
                                </td>
                                <td style="text-align:right;font-weight:700;">S/ {{ number_format($pago->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="caja-actions">
            <a href="{{ route('secretaria.pagos.create') }}" class="btn-cierre" style="background:linear-gradient(135deg,#1f2e8d,#213197);">
                <i data-feather="plus" style="width:16px;height:16px;"></i>
                Registrar Pago
            </a>
            <a href="{{ route('secretaria.caja.cierre', $cajaAbierta->id) }}" class="btn-cierre">
                <i data-feather="lock" style="width:16px;height:16px;"></i>
                Ir a Cierre de Caja
            </a>
        </div>

    @endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
