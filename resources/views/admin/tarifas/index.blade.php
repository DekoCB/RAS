@extends('layouts.mantis')

@section('title', 'Tarifario | R.A.S')
@section('page-title', 'Tarifario')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        :root {
            --ini-hue: #22329c;  /* amarillo Inicial */
            --pri-hue: #25359c;  /* azul Primaria */
            --sec-hue: #8b5cf6;  /* violeta Secundaria */
        }

        /* ── HERO ── */
        .tar-hero {
            background: linear-gradient(135deg, #223299 0%, #1f2d8b 45%, #1c297d 100%);
            border-radius: 18px; padding: 30px 36px; color: #fff;
            position: relative; overflow: hidden;
            box-shadow: 0 10px 40px rgba(29, 43, 133,.4); margin-bottom: 26px;
        }
        .tar-hero::before {
            content: ''; position: absolute; right: -30px; top: -50px;
            width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(50, 65, 161,.18) 0%, transparent 70%);
            border-radius: 50%;
        }
        .tar-hero-content { position: relative; z-index: 1; }
        .tar-hero-pill {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(50, 65, 161,.22); border: 1px solid rgba(50, 65, 161,.5);
            color: #c2caff; font-size: .72rem; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase;
            padding: 5px 16px; border-radius: 20px; margin-bottom: 12px;
        }
        .tar-hero h4 { font-size: 1.65rem; font-weight: 800; margin: 0 0 6px; color: #fff; }
        .tar-hero p  { color: rgba(255,255,255,.68); font-size: .88rem; margin: 0; }
        .tar-btn-nueva {
            background: rgba(255,255,255,.2); border: 2px solid rgba(255,255,255,.55);
            color: #fff; font-weight: 700; padding: 12px 26px; border-radius: 11px;
            font-size: .88rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,.15); transition: all .22s;
            white-space: nowrap; position: relative; z-index: 1; backdrop-filter: blur(4px);
        }
        .tar-btn-nueva:hover {
            background: rgba(255,255,255,.32); transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0,0,0,.2); color: #fff;
        }

        /* ── FILTRO AÑO ── */
        .tar-filter {
            background: #fff; border-radius: 14px;
            box-shadow: 0 3px 14px rgba(0,0,0,.07);
            padding: 16px 22px; margin-bottom: 22px;
            display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
        }
        .tar-filter label { font-weight: 700; font-size: .85rem; color: #475569; margin: 0; white-space: nowrap; }
        .tar-filter select {
            border: 1.5px solid #e2e8f0; border-radius: 9px;
            padding: 8px 14px; font-size: .875rem; color: #1e293b;
            background: #f8fafc; cursor: pointer; min-width: 140px;
        }
        .tar-filter select:focus { outline: none; border-color: #23339e; }
        .tar-filter-badge {
            background: #edf8ff; border: 1px solid #ccd2fe;
            color: #1d2a82; font-size: .78rem; font-weight: 700;
            padding: 5px 13px; border-radius: 20px;
        }

        /* ── ALERT ── */
        .tar-alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac; border-radius: 11px;
            padding: 14px 20px; color: #166534;
            display: flex; align-items: center; gap: 10px;
            font-weight: 600; margin-bottom: 22px;
        }

        /* ── NIVEL CARDS ── */
        .nivel-section { margin-bottom: 28px; }

        .nivel-header {
            border-radius: 14px 14px 0 0;
            padding: 18px 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nivel-header.gen { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); border-bottom: 2px solid #64748b; }
        .nivel-header.ini { background: linear-gradient(135deg, #c7effe, #b8c1fd); border-bottom: 2px solid #22329c; }
        .nivel-header.pri { background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-bottom: 2px solid #25359c; }
        .nivel-header.sec { background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-bottom: 2px solid #8b5cf6; }

        .nivel-header-left { display: flex; align-items: center; gap: 12px; }
        .nivel-icon {
            width: 42px; height: 42px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .nivel-icon.gen { background: #64748b; }
        .nivel-icon.ini { background: #23339f; }
        .nivel-icon.pri { background: #25359c; }
        .nivel-icon.sec { background: #8b5cf6; }
        .nivel-icon svg { width: 20px; height: 20px; color: #fff; }

        .nivel-title { font-size: 1.1rem; font-weight: 800; margin: 0; }
        .nivel-title.gen { color: #334155; }
        .nivel-title.ini { color: #141e5d; }
        .nivel-title.pri { color: #162166; }
        .nivel-title.sec { color: #3b0764; }

        .nivel-sub { font-size: .78rem; margin: 2px 0 0; }
        .nivel-sub.gen { color: #475569; }
        .nivel-sub.ini { color: #17226a; }
        .nivel-sub.pri { color: #1b2779; }
        .nivel-sub.sec { color: #5b21b6; }

        .nivel-count {
            font-size: .78rem; font-weight: 700; padding: 5px 14px; border-radius: 20px;
        }
        .nivel-count.gen { background: rgba(100,116,139,.15); color: #334155; border: 1px solid #94a3b8; }
        .nivel-count.ini { background: rgba(34, 50, 156,.2); color: #141e5d; border: 1px solid #23339f; }
        .nivel-count.pri { background: rgba(37,53,156,.15); color: #162166; border: 1px solid #bdc6fd; }
        .nivel-count.sec { background: rgba(139,92,246,.15); color: #3b0764; border: 1px solid #c4b5fd; }

        /* ── TABLE ── */
        .nivel-body {
            background: #fff;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .tar-table { width: 100%; border-collapse: collapse; }
        .tar-table thead tr { background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; }
        .tar-table thead th {
            padding: 12px 18px; font-size: .73rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .07em; color: #64748b;
        }
        .tar-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
        .tar-table tbody tr:last-child { border-bottom: none; }
        .tar-table tbody tr:hover { background: #fafafa; }
        .tar-table td { padding: 14px 18px; font-size: .875rem; color: #334155; vertical-align: middle; }

        .tar-concepto {
            display: inline-flex; align-items: center; gap: 7px;
            font-weight: 600; color: #334155;
        }
        .tar-concepto svg { width: 14px; height: 14px; color: #94a3b8; }

        .tar-mes-pill {
            display: inline-block; padding: 3px 11px; border-radius: 20px;
            font-size: .75rem; font-weight: 700;
        }
        .tar-mes-pill.general   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .tar-mes-pill.especial  { background: #c3f0fe; color: #141d5a; border: 1px solid #2d3ca0; }

        .tar-monto {
            font-weight: 800; color: #166534; font-size: .92rem;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            padding: 4px 12px; border-radius: 7px; display: inline-block;
        }
        .tar-badge-si {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff; font-size: .7rem; font-weight: 700;
            padding: 3px 11px; border-radius: 6px;
        }
        .tar-badge-no {
            background: #f1f5f9; color: #94a3b8;
            font-size: .7rem; font-weight: 600; padding: 3px 11px; border-radius: 6px;
        }

        /* ── ACTION BUTTONS ── */
        .tar-btn-edit {
            display: inline-flex; align-items: center; gap: 5px;
            background: linear-gradient(135deg, #23339e, #213196);
            color: #fff; border: none; padding: 6px 13px; border-radius: 7px;
            font-size: .78rem; font-weight: 700; text-decoration: none; transition: all .2s;
            box-shadow: 0 2px 8px rgba(35, 51, 158,.3);
        }
        .tar-btn-edit:hover { transform: translateY(-1px); box-shadow: 0 5px 14px rgba(35, 51, 158,.5); color: #fff; }
        .tar-btn-edit svg { width: 12px; height: 12px; }
        .tar-btn-delete {
            display: inline-flex; align-items: center; gap: 5px;
            background: transparent; color: #dc2626; border: 1.5px solid #fca5a5;
            padding: 6px 13px; border-radius: 7px; font-size: .78rem; font-weight: 700;
            cursor: pointer; transition: all .2s;
        }
        .tar-btn-delete:hover {
            background: #dc2626; color: #fff; border-color: #dc2626;
            transform: translateY(-1px); box-shadow: 0 5px 14px rgba(220,38,38,.3);
        }
        .tar-btn-delete svg { width: 12px; height: 12px; }

        /* ── EMPTY ── */
        .tar-empty-row td {
            text-align: center; padding: 36px 20px;
            color: #94a3b8; font-size: .875rem;
        }
        .tar-empty-row td svg { width: 36px; height: 36px; opacity: .3; display: block; margin: 0 auto 10px; }

        /* ── NO AÑO ── */
        .tar-no-anio {
            background: #edf8ff; border: 1px solid #ccd2fe; border-radius: 14px;
            padding: 40px 20px; text-align: center; color: #17226a;
        }
        .tar-no-anio svg { width: 40px; height: 40px; opacity: .5; margin-bottom: 10px; }
        .tar-no-anio p { margin: 0; font-weight: 600; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- HERO --}}
    <div class="tar-hero">
        <div class="tar-hero-content d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="tar-hero-pill">
                    <i data-feather="dollar-sign"></i>
                    Cobranza
                </div>
                <h4>Tarifario Escolar</h4>
                <p>Configura matrículas, pensiones y conceptos de pago por año escolar y nivel educativo.</p>
            </div>
            <a href="{{ route('admin-sistema.tarifas.create') }}" class="tar-btn-nueva">
                <i data-feather="plus-circle"></i>
                Nueva Tarifa
            </a>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="tar-alert-success">
            <i data-feather="check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTRO AÑO ESCOLAR --}}
    <div class="tar-filter">
        <i data-feather="filter" style="width:16px;height:16px;color:#23339e;flex-shrink:0;"></i>
        <label>Año escolar:</label>
        <form method="GET" action="{{ route('admin-sistema.tarifas.index') }}" id="formAnio">
            <select name="anio_id" onchange="document.getElementById('formAnio').submit()">
                @foreach($anios as $a)
                    <option value="{{ $a->id }}" @selected($anioActivo && $a->id == $anioActivo->id)>
                        {{ $a->nombre }} {{ $a->activo ? '(activo)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        @if($anioActivo)
            <span class="tar-filter-badge">
                {{ $tarifasPorNivel->flatten()->count() }} tarifa(s) en {{ $anioActivo->nombre }}
            </span>
        @endif
    </div>

    @if(!$anioActivo)
        <div class="tar-no-anio">
            <i data-feather="alert-circle"></i>
            <p>No hay ningún año escolar configurado.<br>
               <small>Ve a <strong>Año Escolar</strong> y crea uno primero.</small>
            </p>
        </div>
    @else

        @php
            $nivelConfig = [
                'General'    => ['cls' => 'gen', 'icon' => 'globe',     'sub' => 'Conceptos globales para todos los niveles'],
                'Inicial'    => ['cls' => 'ini', 'icon' => 'sun',       'sub' => '3 años · 4 años · 5 años'],
                'Primaria'   => ['cls' => 'pri', 'icon' => 'book-open', 'sub' => '1° a 6° grado'],
                'Secundaria' => ['cls' => 'sec', 'icon' => 'award',     'sub' => '1° a 5° grado'],
            ];
            $mesesNombres = [
                0=>'Todos los meses',1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',
                10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
            ];
        @endphp

        {{-- REGLAMENTO INTERNO: oportunidades de pago, cronograma y mora --}}
        @php
            $mesesCorto = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Oct',11=>'Nov',12=>'Dic'];
            $fmt = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d') : '';
        @endphp
        <div class="nivel-section">
            <div class="nivel-header gen">
                <div class="nivel-header-left">
                    <div class="nivel-icon gen" style="background:#475569;">
                        <i data-feather="book"></i>
                    </div>
                    <div>
                        <div class="nivel-title gen">Reglamento Interno {{ $anioActivo->nombre }}</div>
                        <div class="nivel-sub gen">Oportunidades de pago (Art. 54), cronograma de pensiones (Art. 61) e interés moratorio (Art. 69)</div>
                    </div>
                </div>
                <span class="nivel-count gen">{{ $cronograma->count() }} vencimiento(s)</span>
            </div>
            <div class="nivel-body" style="padding:22px;">
                @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 small">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('admin-sistema.tarifas.politicas.update', $anioActivo) }}" class="row g-3">
                    @csrf @method('PUT')

                    <div class="col-12"><strong style="font-size:.8rem;letter-spacing:.06em;color:#64748b;">PENSIÓN (ART. 61)</strong></div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">N° de cuotas</label>
                        <input type="number" name="pension_cuotas" class="form-control form-control-sm" min="1" max="12"
                               value="{{ old('pension_cuotas', $politica->pension_cuotas ?? 10) }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Desde el mes</label>
                        <select name="pension_mes_inicio" class="form-select form-select-sm">
                            @foreach($mesesNombres as $m => $n) @if($m > 0)
                                <option value="{{ $m }}" @selected(old('pension_mes_inicio', $politica->pension_mes_inicio ?? 3) == $m)>{{ $n }}</option>
                            @endif @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Hasta el mes</label>
                        <select name="pension_mes_fin" class="form-select form-select-sm">
                            @foreach($mesesNombres as $m => $n) @if($m > 0)
                                <option value="{{ $m }}" @selected(old('pension_mes_fin', $politica->pension_mes_fin ?? 12) == $m)>{{ $n }}</option>
                            @endif @endforeach
                        </select>
                    </div>

                    <div class="col-12 mt-4"><strong style="font-size:.8rem;letter-spacing:.06em;color:#64748b;">CRONOGRAMA DE VENCIMIENTO DE PENSIONES</strong>
                        <div class="small text-muted">Si un mes queda vacío se usa la regla general: último día hábil del mes (diciembre, el 15).</div>
                    </div>
                    @foreach($mesesCorto as $m => $n)
                        <div class="col-6 col-md-2 col-lg-1" style="min-width:120px;">
                            <label class="form-label small fw-bold mb-1">{{ $n }}</label>
                            <input type="date" name="cronograma[{{ $m }}]" class="form-control form-control-sm"
                                   value="{{ old("cronograma.$m", $fmt($cronograma[$m]->fecha_vencimiento ?? null)) }}">
                        </div>
                    @endforeach

                    <div class="col-12 mt-4"><strong style="font-size:.8rem;letter-spacing:.06em;color:#64748b;">OPORTUNIDAD DE PAGO</strong></div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Matrícula desde</label>
                        <input type="date" name="matricula_desde" class="form-control form-control-sm" value="{{ old('matricula_desde', $fmt($politica->matricula_desde ?? null)) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Matrícula hasta</label>
                        <input type="date" name="matricula_hasta" class="form-control form-control-sm" value="{{ old('matricula_hasta', $fmt($politica->matricula_hasta ?? null)) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Cuota de ingreso desde</label>
                        <input type="date" name="cuota_ingreso_desde" class="form-control form-control-sm" value="{{ old('cuota_ingreso_desde', $fmt($politica->cuota_ingreso_desde ?? null)) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Cuota de ingreso hasta</label>
                        <input type="date" name="cuota_ingreso_hasta" class="form-control form-control-sm" value="{{ old('cuota_ingreso_hasta', $fmt($politica->cuota_ingreso_hasta ?? null)) }}">
                    </div>

                    <div class="col-12 mt-4"><strong style="font-size:.8rem;letter-spacing:.06em;color:#64748b;">INTERÉS MORATORIO (ART. 69)</strong>
                        <div class="small text-muted">Interés diario = pensión × tasa máxima BCR × porcentaje ÷ divisor. Sin tasa registrada el sistema no calcula mora.</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Porcentaje (%)</label>
                        <input type="number" step="0.01" name="mora_porcentaje" class="form-control form-control-sm" value="{{ old('mora_porcentaje', $politica->mora_porcentaje ?? 15) }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Tasa máx. BCR anual (%)</label>
                        <input type="number" step="0.01" name="mora_tasa_anual" class="form-control form-control-sm" placeholder="Pendiente" value="{{ old('mora_tasa_anual', $politica->mora_tasa_anual ?? '') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Divisor (días)</label>
                        <input type="number" name="mora_divisor" class="form-control form-control-sm" value="{{ old('mora_divisor', $politica->mora_divisor ?? 360) }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">Observaciones</label>
                        <input type="text" name="observaciones" class="form-control form-control-sm" value="{{ old('observaciones', $politica->observaciones ?? '') }}">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="tar-btn-edit" style="padding:9px 20px;font-size:.85rem;">
                            <i data-feather="save"></i> Guardar políticas
                        </button>
                    </div>
                </form>
            </div>
        </div>


        @foreach($niveles as $nivel)
            @php
                $cfg  = $nivelConfig[$nivel];
                $cls  = $cfg['cls'];
                $rows = $tarifasPorNivel[$nivel] ?? collect();
            @endphp

            <div class="nivel-section">
                {{-- Cabecera del nivel --}}
                <div class="nivel-header {{ $cls }}">
                    <div class="nivel-header-left">
                        <div class="nivel-icon {{ $cls }}">
                            <i data-feather="{{ $cfg['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="nivel-title {{ $cls }}">{{ $nivel }}</div>
                            <div class="nivel-sub {{ $cls }}">{{ $cfg['sub'] }}</div>
                        </div>
                    </div>
                    <span class="nivel-count {{ $cls }}">{{ $rows->count() }} tarifa(s)</span>
                </div>

                {{-- Tabla del nivel --}}
                <div class="nivel-body">
                    <div class="table-responsive">
                        <table class="tar-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Concepto de pago</th>
                                    <th>Aplica en</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th class="text-end" style="padding-right:22px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $t)
                                    <tr>
                                        <td style="width:54px;">
                                            <span style="background:#f1f5f9;color:#64748b;font-weight:700;
                                                font-size:.77rem;padding:3px 9px;border-radius:6px;
                                                font-family:monospace;">{{ $t->id }}</span>
                                        </td>
                                        <td>
                                            <span class="tar-concepto">
                                                <i data-feather="tag"></i>
                                                {{ $t->conceptoPago->nombre ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($t->mes == 0)
                                                <span class="tar-mes-pill general">
                                                    <i data-feather="repeat" style="width:11px;height:11px;vertical-align:-1px;"></i>
                                                    Todos los meses
                                                </span>
                                            @else
                                                <span class="tar-mes-pill especial">
                                                    <i data-feather="star" style="width:11px;height:11px;vertical-align:-1px;"></i>
                                                    Solo {{ $mesesNombres[$t->mes] ?? "Mes {$t->mes}" }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="tar-monto">S/ {{ number_format($t->monto, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($t->activo)
                                                <span class="tar-badge-si">Activo</span>
                                            @else
                                                <span class="tar-badge-no">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-end" style="padding-right:18px;">
                                            <div class="d-inline-flex gap-2 align-items-center">
                                                <a href="{{ route('admin-sistema.tarifas.edit', $t) }}" class="tar-btn-edit">
                                                    <i data-feather="edit-2"></i> Editar
                                                </a>
                                                <form action="{{ route('admin-sistema.tarifas.destroy', $t) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar esta tarifa?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="tar-btn-delete">
                                                        <i data-feather="trash-2"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="tar-empty-row">
                                        <td colspan="6">
                                            <i data-feather="inbox"></i>
                                            Sin tarifas configuradas para {{ $nivel }} en {{ $anioActivo->nombre }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

    @endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
