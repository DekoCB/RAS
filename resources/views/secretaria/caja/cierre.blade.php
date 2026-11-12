@extends('layouts.mantis')

@section('title', 'Cierre de Caja | Secretaria')
@section('page-title', 'Cierre de Caja')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <style>
        :root {
            --cc-primary: #162061;
            --cc-accent:  #1c297e;
            --cc-light:   #f2f7fe;
            --cc-border:  #cae0fe;
            --cc-shadow:  0 4px 20px rgba(22, 32, 97,.10);
            --cc-green:   #15803d;
            --cc-blue:    #1f2e8d;
        }

        /* ── HERO ── */
        .cc-hero {
            background: linear-gradient(135deg, #162061 0%, #1c297e 50%, #1f2e8f 100%);
            border-radius: 20px;
            padding: 24px 30px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: var(--cc-shadow);
            margin-bottom: 28px;
        }
        .cc-hero-title { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .cc-hero-sub   { opacity: .85; font-size: .9rem; margin-top: 4px; }

        /* ── LAYOUT ── */
        .cc-layout {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 900px) {
            .cc-layout { grid-template-columns: 1fr; }
        }

        /* ── PANEL IZQUIERDO (resumen) ── */
        .cc-resumen {
            background: #fff;
            border-radius: 18px;
            box-shadow: var(--cc-shadow);
            border: 1.5px solid var(--cc-border);
            overflow: hidden;
        }
        .cc-resumen-header {
            background: var(--cc-light);
            padding: 16px 22px;
            font-weight: 700;
            color: var(--cc-primary);
            font-size: .95rem;
            border-bottom: 1.5px solid var(--cc-border);
        }
        .cc-resumen-body { padding: 22px; }

        .cc-field-group { margin-bottom: 18px; }
        .cc-field-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            margin-bottom: 5px;
            display: block;
        }
        .cc-field-label.green  { color: var(--cc-green); }
        .cc-field-label.blue   { color: var(--cc-blue); }
        .cc-field-label.red    { color: var(--cc-accent); font-size: .82rem; }
        .cc-field-value {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: .95rem;
            color: #1e293b;
            font-weight: 600;
            width: 100%;
        }
        .cc-field-value.big {
            font-size: 1.25rem;
            font-weight: 800;
            background: var(--cc-light);
            border-color: var(--cc-border);
            color: var(--cc-accent);
        }
        .cc-field-value.green-val {
            background: #dcfce7; border-color: #bbf7d0; color: var(--cc-green);
        }
        .cc-field-value.blue-val {
            background: #dbeafe; border-color: #bfdbfe; color: var(--cc-blue);
        }
        .cc-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .cc-divider { border: none; border-top: 1.5px solid #f1f5f9; margin: 16px 0; }

        /* Métodos digitales en resumen */
        .cc-met-list { list-style: none; padding: 0; margin: 0; }
        .cc-met-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: .88rem;
        }
        .cc-met-item:last-child { border-bottom: none; }
        .cc-met-logo {
            width: 34px; height: 34px;
            object-fit: contain; border-radius: 8px;
            flex-shrink: 0;
        }
        .cc-met-logo-placeholder {
            width: 34px; height: 34px;
            background: #f1f5f9; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8; flex-shrink: 0;
        }
        .cc-met-name { flex: 1; color: #374151; font-weight: 600; }
        .cc-met-monto { font-weight: 800; color: #1e293b; }

        /* conteo dinámico */
        .cc-conteo-row { display: flex; justify-content: space-between; align-items: center; }
        .cc-conteo-val { font-size: 1.35rem; font-weight: 800; color: #1e293b; }
        .cc-dif-pos { color: var(--cc-green); }
        .cc-dif-neg { color: var(--cc-accent); }
        .cc-dif-zero{ color: #94a3b8; }

        /* ── PANEL DERECHO (billetes/monedas) ── */
        .cc-conteo-panel {
            background: #fff;
            border-radius: 18px;
            box-shadow: var(--cc-shadow);
            border: 1.5px solid #e2e8f0;
            overflow: hidden;
        }
        .cc-conteo-header {
            background: #f8fafc;
            padding: 16px 22px;
            font-weight: 700;
            color: #374151;
            font-size: .95rem;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex; align-items: center; gap: 8px;
        }
        .cc-conteo-body { padding: 22px; }

        .cc-denom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .cc-denom-card {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 10px;
            text-align: center;
            transition: border-color .2s, box-shadow .2s;
        }
        .cc-denom-card:hover { border-color: var(--cc-accent); }
        .cc-denom-img {
            width: 64px; height: 64px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 8px;
            display: block; margin-left: auto; margin-right: auto;
        }
        .cc-denom-label {
            font-size: .8rem; font-weight: 700; color: #374151;
            margin-bottom: 8px;
        }
        .cc-denom-input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 8px;
            text-align: center;
            font-size: .95rem;
            font-weight: 700;
            color: #1e293b;
            transition: border-color .2s;
        }
        .cc-denom-input:focus {
            outline: none;
            border-color: var(--cc-accent);
            box-shadow: 0 0 0 3px rgba(28, 41, 126,.1);
        }
        .cc-denom-subtotal {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 4px;
            font-weight: 600;
            min-height: 18px;
        }

        /* Observaciones */
        .cc-obs-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            border: 1.5px solid #e2e8f0;
            padding: 22px;
            margin-top: 20px;
        }
        .cc-obs-label {
            font-weight: 700; color: #374151; font-size: .9rem;
            margin-bottom: 10px; display: block;
        }
        .cc-obs-textarea {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: .9rem;
            resize: vertical;
            min-height: 80px;
            color: #374151;
            transition: border-color .2s;
        }
        .cc-obs-textarea:focus {
            outline: none;
            border-color: var(--cc-accent);
            box-shadow: 0 0 0 3px rgba(28, 41, 126,.1);
        }

        /* ── TABS ── */
        .cc-tab-panel {
            background: #fff;
            border-radius: 18px;
            box-shadow: var(--cc-shadow);
            border: 1.5px solid #e2e8f0;
            overflow: hidden;
        }
        .cc-tab-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
        }
        .cc-tab-btn {
            padding: 14px 10px;
            border: none; background: transparent;
            font-weight: 700; font-size: .88rem;
            color: #94a3b8; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            transition: all .2s;
            position: relative;
        }
        .cc-tab-btn::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 3px; border-radius: 3px 3px 0 0;
            background: transparent;
            transition: background .2s;
        }
        .cc-tab-btn.active-cash {
            color: var(--cc-primary);
        }
        .cc-tab-btn.active-cash::after { background: var(--cc-accent); }
        .cc-tab-btn.active-digital {
            color: var(--cc-blue);
        }
        .cc-tab-btn.active-digital::after { background: var(--cc-blue); }
        .cc-tab-badge {
            font-size: .68rem; font-weight: 800;
            padding: 1px 7px; border-radius: 20px;
            min-width: 28px; text-align: center;
        }
        .badge-cash    { background: var(--cc-light); color: var(--cc-accent); }
        .badge-digital { background: #dbeafe; color: var(--cc-blue); }

        .cc-tab-content { display: none; padding: 22px; }
        .cc-tab-content.active { display: block; }

        /* digital inside tab */
        .cc-digital-row {
            display: flex; align-items: center; gap: 14px;
            padding: 13px 0; border-bottom: 1px solid #f1f5f9;
        }
        .cc-digital-row:last-of-type { border-bottom: none; }
        .cc-digital-info { display: flex; align-items: center; gap: 10px; flex: 1; }
        .cc-digital-name { font-weight: 700; color: #1e293b; font-size: .92rem; }
        .cc-digital-sist { font-size: .75rem; color: #64748b; margin-top: 2px; }
        .cc-digital-right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
        .cc-digital-input-wrap {
            display: flex; align-items: center; gap: 6px;
            background: #eff6ff; border: 1.5px solid #bdc6fd;
            border-radius: 10px; padding: 7px 12px;
            transition: border-color .2s, box-shadow .2s;
        }
        .cc-digital-input-wrap:focus-within {
            border-color: var(--cc-blue);
            box-shadow: 0 0 0 3px rgba(31,46,141,.12);
        }
        .cc-digital-input-wrap span { font-size: .82rem; font-weight: 800; color: #64748b; }
        .cc-digital-inp {
            width: 100px; text-align: right; font-weight: 800; font-size: 1rem;
            border: none; outline: none; background: transparent;
            color: var(--cc-blue);
        }
        .cc-digital-dif {
            font-size: .73rem; font-weight: 700;
            padding: 2px 9px; border-radius: 20px;
        }
        .dif-cero { color: #94a3b8; background: #f1f5f9; }
        .dif-pos  { color: var(--cc-green); background: #dcfce7; }
        .dif-neg  { color: var(--cc-accent); background: #e2eefe; }
        .cc-digital-total {
            margin-top: 16px;
            background: linear-gradient(135deg, #1f2e8d, #213197);
            border-radius: 12px; padding: 16px 20px;
            color: #fff; display: flex; justify-content: space-between; align-items: center;
        }
        .cc-digital-total-label { font-size: .78rem; opacity: .85; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
        .cc-digital-total-val   { font-size: 1.6rem; font-weight: 900; margin-top: 2px; }

        /* ── BOTONES ── */
        .cc-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            margin-bottom: 32px;
        }
        .btn-volver {
            background: #f1f5f9; color: #374151; border: none;
            border-radius: 12px; padding: 12px 26px;
            font-size: .95rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            transition: background .2s;
        }
        .btn-volver:hover { background: #e2e8f0; color: #374151; }
        .btn-confirmar {
            background: linear-gradient(135deg, #162061, #1c297e);
            color: #fff; border: none;
            border-radius: 12px; padding: 12px 28px;
            font-size: .95rem; font-weight: 700;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px;
            transition: opacity .2s;
        }
        .btn-confirmar:hover { opacity: .9; }
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
    <div class="cc-hero">
        <div>
            <p class="cc-hero-title">
                <i data-feather="lock" style="width:26px;height:26px;vertical-align:middle;margin-right:8px;"></i>
                Cierre de Caja
            </p>
            <p class="cc-hero-sub">Cuenta el dinero en efectivo y confirma el cierre del día</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.85rem;opacity:.85;">Caja #{{ $caja->id }}</div>
            <div style="font-size:.85rem;opacity:.85;">{{ $caja->fecha_apertura->format('d/m/Y h:i A') }} → {{ now()->format('h:i A') }}</div>
        </div>
    </div>

    <form action="{{ route('secretaria.caja.cerrar', $caja->id) }}" method="POST" id="formCierre">
        @csrf

        <div class="cc-layout">

            {{-- ── PANEL IZQUIERDO: RESUMEN ── --}}
            <div>
                <div class="cc-resumen">
                    <div class="cc-resumen-header">
                        <i data-feather="file-text" style="width:15px;height:15px;vertical-align:middle;margin-right:6px;"></i>
                        Resumen de cierre
                    </div>
                    <div class="cc-resumen-body">

                        <div class="cc-field-row">
                            <div class="cc-field-group">
                                <span class="cc-field-label">Caja ID</span>
                                <div class="cc-field-value">{{ $caja->id }}</div>
                            </div>
                            <div class="cc-field-group">
                                <span class="cc-field-label">Saldo Inicial</span>
                                <div class="cc-field-value">S/ {{ number_format($caja->saldo_inicial, 2) }}</div>
                            </div>
                        </div>

                        <div class="cc-field-row">
                            <div class="cc-field-group">
                                <span class="cc-field-label">F. Apertura</span>
                                <div class="cc-field-value">{{ $caja->fecha_apertura->format('d/m/Y h:i A') }}</div>
                            </div>
                            <div class="cc-field-group">
                                <span class="cc-field-label">F. Cierre</span>
                                <div class="cc-field-value" id="fechaCierre">{{ now()->format('d/m/Y h:i A') }}</div>
                            </div>
                        </div>

                        <hr class="cc-divider">

                        <div class="cc-field-group">
                            <span class="cc-field-label red">Total General</span>
                            <div class="cc-field-value big" id="resumenTotalGeneral">S/ {{ number_format($totalGeneral, 2) }}</div>
                        </div>

                        <hr class="cc-divider">

                        {{-- Efectivo --}}
                        <div class="cc-field-group">
                            <span class="cc-field-label green">
                                <i data-feather="dollar-sign" style="width:11px;height:11px;vertical-align:middle;margin-right:3px;"></i>
                                Efectivo (Sistema)
                            </span>
                            <div class="cc-field-value green-val">S/ {{ number_format($totalEfectivo, 2) }}</div>
                        </div>

                        <hr class="cc-divider">

                        {{-- Desglose digital (siempre visible) --}}
                        @php
                            $metodosResumen = [
                                'Yape'          => ['img' => $imagenesMetodo['Yape']          ?? null, 'label' => 'Yape'                ],
                                'Plin'          => ['img' => $imagenesMetodo['Plin']          ?? null, 'label' => 'Plin'                ],
                                'Transferencia' => ['img' => $imagenesMetodo['Transferencia'] ?? null, 'label' => 'Tarjeta BBVA'        ],
                                'Tarjeta BCP'   => ['img' => $imagenesMetodo['Tarjeta BCP']   ?? null, 'label' => 'Tarjeta BCP'         ],
                                'BancoNacion'   => ['img' => $imagenesMetodo['BancoNacion']   ?? null, 'label' => 'Banco de la Nación'  ],
                                'Otro'          => ['img' => null,                                     'label' => 'Otro'                ],
                            ];
                        @endphp
                        <div class="cc-field-group">
                            <span class="cc-field-label blue">
                                <i data-feather="smartphone" style="width:11px;height:11px;vertical-align:middle;margin-right:3px;"></i>
                                Pagos Digitales
                            </span>
                            <ul class="cc-met-list">
                                @foreach($metodosResumen as $metodo => $cfg)
                                @php $montoMet = (float)($totalesPorMetodo->get($metodo, 0)); @endphp
                                <li class="cc-met-item">
                                    @if($cfg['img'])
                                        <img src="{{ url('images/' . $cfg['img']) }}" alt="{{ $cfg['label'] }}" class="cc-met-logo">
                                    @else
                                        <div class="cc-met-logo-placeholder">
                                            <i data-feather="credit-card" style="width:14px;height:14px;"></i>
                                        </div>
                                    @endif
                                    <span class="cc-met-name">{{ $cfg['label'] }}</span>
                                    <span class="cc-met-monto" id="resumen_{{ Str::slug($metodo) }}">S/ {{ number_format($montoMet, 2) }}</span>
                                </li>
                                @endforeach
                                <li class="cc-met-item" style="border-top:1.5px solid #dbeafe;margin-top:4px;padding-top:10px;">
                                    <span class="cc-met-name" style="color:#1f2e8d;font-weight:700;">Total Digital</span>
                                    <span class="cc-met-monto" id="resumenTotalDigital" style="color:#1f2e8d;">S/ {{ number_format($totalDigital, 2) }}</span>
                                </li>
                            </ul>
                        </div>

                        <hr class="cc-divider">

                        <div class="cc-field-row">
                            <div class="cc-field-group">
                                <span class="cc-field-label">Recibos Ingreso</span>
                                <div class="cc-field-value">{{ $cantidadPagos }}</div>
                            </div>
                            <div class="cc-field-group">
                                <span class="cc-field-label">Recibos Egreso</span>
                                <div class="cc-field-value">0</div>
                            </div>
                        </div>

                        <hr class="cc-divider">

                        <div class="cc-field-group">
                            <span class="cc-field-label">Conteo Efectivo (físico)</span>
                            <div class="cc-field-value" id="resumenConteo">S/ 0.00</div>
                        </div>

                        <div class="cc-field-group">
                            <span class="cc-field-label">Diferencia Efectivo (Conteo − Sistema)</span>
                            <div class="cc-field-value" id="resumenDif" style="font-weight:800;">S/ 0.00</div>
                        </div>

                        <hr class="cc-divider">

                        <div class="cc-field-group">
                            <span class="cc-field-label blue">Digital Confirmado (ingresado)</span>
                            <div class="cc-field-value blue-val" id="resumenDigitalConf">S/ 0.00</div>
                        </div>

                        <div class="cc-field-group">
                            <span class="cc-field-label">Diferencia Digital (Confirmado − Sistema)</span>
                            <div class="cc-field-value" id="resumenDifDigital" style="font-weight:800;">S/ 0.00</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── PANEL DERECHO: TABS ── --}}
            <div>
                <div class="cc-tab-panel">

                    {{-- Tab bar --}}
                    <div class="cc-tab-bar">
                        <button type="button" class="cc-tab-btn active-cash" id="tabBtnCash" onclick="switchTab('cash')">
                            <i data-feather="layers" style="width:15px;height:15px;"></i>
                            Efectivo
                            <span class="cc-tab-badge badge-cash" id="badgeCash">S/ 0.00</span>
                        </button>
                        <button type="button" class="cc-tab-btn" id="tabBtnDigital" onclick="switchTab('digital')">
                            <i data-feather="smartphone" style="width:15px;height:15px;"></i>
                            Digital
                            <span class="cc-tab-badge badge-digital" id="badgeDigital">S/ 0.00</span>
                        </button>
                    </div>

                    {{-- TAB: EFECTIVO --}}
                    <div class="cc-tab-content active" id="tabCash">

                        <div style="background:#f5faff;border:1.5px solid #d4ecfd;border-radius:12px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;">
                            <i data-feather="info" style="width:16px;height:16px;color:#213196;flex-shrink:0;margin-top:2px;"></i>
                            <div style="font-size:.8rem;color:#151f5f;line-height:1.5;">
                                <strong>¿Para qué sirve esto?</strong><br>
                                Cuenta los billetes y monedas que tienes físicamente en caja. El sistema compara ese total con lo registrado en pagos de efectivo. Si hay diferencia, aparecerá en el resumen de la izquierda.
                            </div>
                        </div>

                        <div class="cc-denom-grid">
                            @foreach($denominaciones as $denom)
                                @php $img = $imagenesDenom[$denom] ?? null; @endphp
                                <div class="cc-denom-card">
                                    @if($img)
                                        <img src="{{ url('images/' . $img) }}" alt="S/ {{ $denom }}" class="cc-denom-img">
                                    @endif
                                    <div class="cc-denom-label">S/ {{ number_format((float)$denom, 2) }}</div>
                                    <input
                                        type="number"
                                        name="denominaciones[{{ $denom }}]"
                                        class="cc-denom-input denom-qty"
                                        data-valor="{{ $denom }}"
                                        min="0" value="0" placeholder="0"
                                    >
                                    <div class="cc-denom-subtotal" id="sub_{{ str_replace('.', '_', $denom) }}">S/ 0.00</div>
                                </div>
                            @endforeach
                        </div>

                        <div style="background:linear-gradient(135deg,#162061,#1c297e);border-radius:14px;padding:18px 22px;color:#fff;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <div style="font-size:.8rem;opacity:.85;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Total contado en efectivo</div>
                                <div style="font-size:1.8rem;font-weight:900;" id="totalContado">S/ 0.00</div>
                            </div>
                            <i data-feather="check-circle" style="width:40px;height:40px;opacity:.6;"></i>
                        </div>
                    </div>

                    {{-- TAB: DIGITAL --}}
                    @php
                        $metodosDigitalesDisp = [
                            'Yape'          => ['img' => $imagenesMetodo['Yape']          ?? null, 'icon' => 'smartphone',    'label' => 'Yape'                ],
                            'Plin'          => ['img' => $imagenesMetodo['Plin']          ?? null, 'icon' => 'credit-card',   'label' => 'Plin'                ],
                            'Transferencia' => ['img' => $imagenesMetodo['Transferencia'] ?? null, 'icon' => 'trending-up',   'label' => 'Tarjeta BBVA'        ],
                            'Tarjeta BCP'   => ['img' => $imagenesMetodo['Tarjeta BCP']   ?? null, 'icon' => 'credit-card',   'label' => 'Tarjeta BCP'         ],
                            'BancoNacion'   => ['img' => $imagenesMetodo['BancoNacion']   ?? null, 'icon' => 'landmark',      'label' => 'Banco de la Nación'  ],
                            'Otro'          => ['img' => null,                                     'icon' => 'more-horizontal','label' => 'Otro'               ],
                        ];
                    @endphp
                    <div class="cc-tab-content" id="tabDigital">
                        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;">
                            <i data-feather="info" style="width:16px;height:16px;color:#213197;flex-shrink:0;margin-top:2px;"></i>
                            <div style="font-size:.8rem;color:#162166;line-height:1.5;">
                                <strong>¿Para qué sirve esto?</strong><br>
                                Ingresa lo que realmente recibiste en cada app o tarjeta (revisa tu Yape, Plin, estado de cuenta, etc.). El sistema compara ese monto con lo registrado en pagos. Si hay diferencia, significa que algo pudo no registrarse correctamente.
                            </div>
                        </div>
                        @foreach($metodosDigitalesDisp as $metodo => $cfg)
                        @php $montoSistema = (float)($totalesPorMetodo->get($metodo, 0)); @endphp
                        <div class="cc-digital-row">
                            <div class="cc-digital-info">
                                @if($cfg['img'])
                                    <img src="{{ url('images/' . $cfg['img']) }}" alt="{{ $cfg['label'] }}" class="cc-met-logo" style="width:38px;height:38px;border-radius:10px;">
                                @else
                                    <div class="cc-met-logo-placeholder">
                                        <i data-feather="{{ $cfg['icon'] }}" style="width:16px;height:16px;"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="cc-digital-name">{{ $cfg['label'] }}</div>
                                    <div class="cc-digital-sist">Sistema: S/ {{ number_format($montoSistema, 2) }}</div>
                                </div>
                            </div>
                            <div class="cc-digital-right">
                                <div class="cc-digital-input-wrap">
                                    <span>S/</span>
                                    <input
                                        type="number"
                                        name="confirmacion_digital[{{ $metodo }}]"
                                        class="cc-digital-inp"
                                        data-sistema="{{ $montoSistema }}"
                                        data-met="{{ Str::slug($metodo) }}"
                                        value="0.00"
                                        min="0" step="0.01" placeholder="0.00"
                                    >
                                </div>
                                <div class="cc-digital-dif dif-cero" id="digdif_{{ Str::slug($metodo) }}">S/ 0.00</div>
                            </div>
                        </div>
                        @endforeach

                        <div class="cc-digital-total">
                            <div>
                                <div class="cc-digital-total-label">Total Digital Confirmado</div>
                                <div class="cc-digital-total-val" id="totalDigitalConf">S/ 0.00</div>
                            </div>
                            <i data-feather="check-circle" style="width:36px;height:36px;opacity:.6;"></i>
                        </div>
                    </div>

                </div>{{-- /cc-tab-panel --}}

                {{-- Observaciones --}}
                <div class="cc-obs-card">
                    <span class="cc-obs-label">
                        <i data-feather="message-square" style="width:15px;height:15px;vertical-align:middle;margin-right:6px;"></i>
                        Observaciones
                    </span>
                    <textarea
                        name="observaciones"
                        class="cc-obs-textarea"
                        placeholder="Ej.: Depósito al banco, diferencia por redondeo..."
                    ></textarea>
                </div>
            </div>

        </div>{{-- /cc-layout --}}

        {{-- BOTONES --}}
        <div class="cc-footer">
            <a href="{{ route('secretaria.caja.index') }}" class="btn-volver">
                <i data-feather="arrow-left" style="width:16px;height:16px;"></i>
                Volver
            </a>
            <button type="submit" class="btn-confirmar" id="btnConfirmar">
                <i data-feather="lock" style="width:16px;height:16px;"></i>
                Confirmar Cierre
            </button>
        </div>

    </form>

@endsection

@push('scripts')
<script>
    // ── Tabs ───────────────────────────────────────────────────
    function switchTab(tab) {
        const isCash = tab === 'cash';
        document.getElementById('tabCash').classList.toggle('active', isCash);
        document.getElementById('tabDigital').classList.toggle('active', !isCash);

        const btnCash    = document.getElementById('tabBtnCash');
        const btnDigital = document.getElementById('tabBtnDigital');
        btnCash.className    = 'cc-tab-btn' + (isCash    ? ' active-cash'    : '');
        btnDigital.className = 'cc-tab-btn' + (!isCash   ? ' active-digital' : '');

        if (typeof feather !== 'undefined') feather.replace();
    }

document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    const efectivoSistema = {{ $totalEfectivo }};

    // ── Efectivo ───────────────────────────────────────────────
    function recalcular() {
        let total = 0;
        document.querySelectorAll('.denom-qty').forEach(function (inp) {
            const valor  = parseFloat(inp.dataset.valor) || 0;
            const cant   = parseInt(inp.value) || 0;
            const sub    = valor * cant;
            total += sub;

            const key = inp.dataset.valor.replace('.', '_');
            const subEl = document.getElementById('sub_' + key);
            if (subEl) subEl.textContent = 'S/ ' + sub.toFixed(2);
        });

        document.getElementById('totalContado').textContent  = 'S/ ' + total.toFixed(2);
        document.getElementById('resumenConteo').textContent = 'S/ ' + total.toFixed(2);
        document.getElementById('badgeCash').textContent     = 'S/ ' + total.toFixed(2);

        const dif   = total - efectivoSistema;
        const difEl = document.getElementById('resumenDif');
        difEl.textContent = (dif >= 0 ? '+' : '') + 'S/ ' + dif.toFixed(2);
        difEl.className   = 'cc-field-value ' + (dif > 0 ? 'cc-dif-pos' : dif < 0 ? 'cc-dif-neg' : 'cc-dif-zero');

        // no toca el total general — ese usa efectivo sistema, no contado
    }

    document.querySelectorAll('.denom-qty').forEach(function (inp) {
        inp.addEventListener('input', recalcular);
    });

    recalcular();

    // ── Digital ────────────────────────────────────────────────
    function recalcularDigital() {
        let totalConf = 0;
        document.querySelectorAll('.cc-digital-inp').forEach(function (inp) {
            const sistema = parseFloat(inp.dataset.sistema) || 0;
            const conf    = parseFloat(inp.value) || 0;
            const dif     = conf - sistema;
            const key     = inp.dataset.met;       // slug ej: "yape"
            const difEl   = document.getElementById('digdif_' + key);
            totalConf    += conf;

            // actualiza badge diferencia en el tab
            if (difEl) {
                difEl.textContent = (dif >= 0 ? '+' : '') + 'S/ ' + dif.toFixed(2);
                difEl.className   = 'cc-digital-dif ' + (dif > 0 ? 'dif-pos' : dif < 0 ? 'dif-neg' : 'dif-cero');
            }
        });

        // total digital en tab y resumen
        const elConf  = document.getElementById('totalDigitalConf');
        const elRes   = document.getElementById('resumenTotalDigital');
        const badge   = document.getElementById('badgeDigital');
        if (elConf) elConf.textContent = 'S/ ' + totalConf.toFixed(2);
        if (elRes)  elRes.textContent  = 'S/ ' + totalConf.toFixed(2);
        if (badge)  badge.textContent  = 'S/ ' + totalConf.toFixed(2);

        // digital confirmado e diferencia en panel izquierdo
        const totalSistemaDigital = {{ $totalDigital }};
        const difDigital = totalConf - totalSistemaDigital;
        const elConfRes  = document.getElementById('resumenDigitalConf');
        const elDifDig   = document.getElementById('resumenDifDigital');
        if (elConfRes) elConfRes.textContent = 'S/ ' + totalConf.toFixed(2);
        if (elDifDig) {
            elDifDig.textContent = (difDigital >= 0 ? '+' : '') + 'S/ ' + difDigital.toFixed(2);
            elDifDig.className   = 'cc-field-value ' + (difDigital > 0 ? 'cc-dif-pos' : difDigital < 0 ? 'cc-dif-neg' : 'cc-dif-zero');
        }

        // total general = efectivo SISTEMA + digital confirmado
        const genEl = document.getElementById('resumenTotalGeneral');
        if (genEl) genEl.textContent = 'S/ ' + (efectivoSistema + totalConf).toFixed(2);
    }

    document.querySelectorAll('.cc-digital-inp').forEach(function (inp) {
        inp.addEventListener('input', recalcularDigital);
        inp.addEventListener('focus', function () { if (parseFloat(this.value) === 0) this.value = ''; });
        inp.addEventListener('blur',  function () { if (this.value === '') this.value = '0.00'; recalcularDigital(); });
    });

    recalcularDigital();

    // ── Confirmar cierre ───────────────────────────────────────
    document.getElementById('btnConfirmar').addEventListener('click', function (e) {
        if (!confirm('¿Confirmas el cierre de caja? Esta acción no se puede deshacer.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
