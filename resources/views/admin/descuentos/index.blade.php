@extends('layouts.mantis')

@section('title', 'Descuentos | R.A.S')
@section('page-title', 'Descuentos')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        :root {
            --dsc-or1: #223299;
            --dsc-or2: #1f2d8b;
            --dsc-or3: #1c297d;
        }

        /* ── HERO ── */
        .dsc-hero {
            background: linear-gradient(135deg, #223299 0%, #1f2d8b 45%, #1c297d 100%);
            border-radius: 18px; padding: 30px 36px; color: #fff;
            position: relative; overflow: hidden;
            box-shadow: 0 10px 40px rgba(29, 43, 133,.4); margin-bottom: 26px;
        }
        .dsc-hero::before {
            content: ''; position: absolute; right: -30px; top: -50px;
            width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(50, 65, 161,.18) 0%, transparent 70%);
            border-radius: 50%;
        }
        .dsc-hero::after {
            content: ''; position: absolute; right: 80px; bottom: -70px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
            border-radius: 50%;
        }
        .dsc-hero-content { position: relative; z-index: 1; }
        .dsc-hero-pill {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(50, 65, 161,.22); border: 1px solid rgba(50, 65, 161,.5);
            color: #c2caff; font-size: .72rem; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase;
            padding: 5px 16px; border-radius: 20px; margin-bottom: 12px;
        }
        .dsc-hero-pill svg { width: 13px; height: 13px; }
        .dsc-hero h4 { font-size: 1.65rem; font-weight: 800; margin: 0 0 6px; color: #fff; }
        .dsc-hero p  { color: rgba(255,255,255,.68); font-size: .88rem; margin: 0; }
        .dsc-btn-nueva {
            background: rgba(255,255,255,.2);
            border: 2px solid rgba(255,255,255,.55); color: #fff; font-weight: 700;
            padding: 12px 26px; border-radius: 11px; font-size: .88rem;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,.15); transition: all .22s;
            white-space: nowrap; position: relative; z-index: 1; backdrop-filter: blur(4px);
        }
        .dsc-btn-nueva:hover {
            background: rgba(255,255,255,.32); transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0,0,0,.2); color: #fff;
        }
        .dsc-btn-nueva svg { width: 16px; height: 16px; }

        /* ── STATS ── */
        .dsc-stat {
            background: #fff; border-radius: 14px; padding: 20px 22px;
            box-shadow: 0 3px 14px rgba(0,0,0,.08);
            border-left: 5px solid #202f91;
            display: flex; align-items: center; gap: 16px;
            transition: transform .2s, box-shadow .2s;
        }
        .dsc-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
        .dsc-stat.s2 { border-left-color: #16a34a; }
        .dsc-stat.s3 { border-left-color: #213196; }
        .dsc-stat.s4 { border-left-color: #1d2a82; }
        .dsc-stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(32, 47, 145,.12); flex-shrink: 0;
        }
        .dsc-stat.s2 .dsc-stat-icon { background: rgba(22,163,74,.1); }
        .dsc-stat.s3 .dsc-stat-icon { background: rgba(33, 49, 150,.12); }
        .dsc-stat.s4 .dsc-stat-icon { background: rgba(29, 42, 130,.12); }
        .dsc-stat-icon svg { width: 22px; height: 22px; color: #202f91; }
        .dsc-stat.s2 .dsc-stat-icon svg { color: #16a34a; }
        .dsc-stat.s3 .dsc-stat-icon svg { color: #213196; }
        .dsc-stat.s4 .dsc-stat-icon svg { color: #1d2a82; }
        .dsc-stat-num   { font-size: 1.7rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .dsc-stat-label { font-size: .77rem; color: #64748b; margin-top: 4px; font-weight: 500; }

        /* ── TABLE CARD ── */
        .dsc-table-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 3px 18px rgba(0,0,0,.09);
            overflow: hidden; margin-top: 26px;
        }
        .dsc-table-header {
            background: linear-gradient(135deg, #202f91 0%, #1c297d 100%);
            padding: 18px 26px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .dsc-table-header h6 {
            color: #fff; margin: 0; font-weight: 700; font-size: 1rem;
            display: flex; align-items: center; gap: 9px;
        }
        .dsc-table-header h6 svg { width: 17px; height: 17px; color: #c2caff; }
        .dsc-total-badge {
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
            color: rgba(255,255,255,.9); font-size: .78rem; font-weight: 700;
            padding: 4px 14px; border-radius: 20px;
        }

        /* ── TABLE ── */
        .dsc-table { width: 100%; border-collapse: collapse; }
        .dsc-table thead tr { background: #edf7ff; border-bottom: 2px solid #ccd2fe; }
        .dsc-table thead th {
            padding: 13px 16px; font-size: .74rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .07em; color: #18246e; white-space: nowrap;
        }
        .dsc-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
        .dsc-table tbody tr:last-child { border-bottom: none; }
        .dsc-table tbody tr:hover { background: #f0f9ff; }
        .dsc-table td { padding: 13px 16px; font-size: .86rem; color: #334155; vertical-align: middle; }

        .dsc-id-badge {
            background: #f1f5f9; color: #64748b;
            font-weight: 700; font-size: .77rem;
            padding: 3px 10px; border-radius: 6px; font-family: monospace;
        }
        .dsc-anio { font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 5px; }
        .dsc-anio svg { width: 12px; height: 12px; color: #94a3b8; }
        .dsc-nivel-pill {
            display: inline-block; padding: 3px 11px; border-radius: 20px;
            font-size: .75rem; font-weight: 700;
        }
        .dsc-nivel-pill.inicial    { background: #c7effe; color: #17226a; border: 1px solid #b8c1fd; }
        .dsc-nivel-pill.primaria   { background: #dbeafe; color: #1b2779; border: 1px solid #bfdbfe; }
        .dsc-nivel-pill.secundaria { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
        .dsc-nivel-pill.todos      { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        .dsc-tipo-pill {
            display: inline-block; padding: 3px 10px; border-radius: 6px;
            font-size: .72rem; font-weight: 800; letter-spacing: .04em;
        }
        .dsc-tipo-pill.hermanos     { background: #edf8ff; color: #1d2a82; border: 1px solid #ccd2fe; }
        .dsc-tipo-pill.pronto_pago  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .dsc-tipo-pill.beca         { background: #eff6ff; color: #1f2e8d; border: 1px solid #bfdbfe; }
        .dsc-tipo-pill.otro         { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

        .dsc-aplica-pill {
            display: inline-block; padding: 3px 10px; border-radius: 6px;
            font-size: .72rem; font-weight: 700;
            background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;
        }
        .dsc-valor {
            font-weight: 800; color: #166534; font-size: .9rem;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            padding: 3px 10px; border-radius: 6px; display: inline-block;
        }
        .dsc-badge-si {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff; font-size: .72rem; font-weight: 700;
            padding: 4px 11px; border-radius: 6px;
        }
        .dsc-badge-no {
            background: #f1f5f9; color: #94a3b8;
            font-size: .72rem; font-weight: 600; padding: 4px 11px; border-radius: 6px;
        }

        /* ── ACTION BUTTONS ── */
        .dsc-btn-edit {
            display: inline-flex; align-items: center; gap: 5px;
            background: linear-gradient(135deg, #23339e, #213196);
            color: #fff; border: none; padding: 7px 14px; border-radius: 8px;
            font-size: .8rem; font-weight: 700; text-decoration: none; transition: all .2s;
            box-shadow: 0 2px 8px rgba(35, 51, 158,.35);
        }
        .dsc-btn-edit:hover {
            transform: translateY(-1px); box-shadow: 0 5px 14px rgba(35, 51, 158,.5); color: #fff;
        }
        .dsc-btn-edit svg { width: 13px; height: 13px; }
        .dsc-btn-delete {
            display: inline-flex; align-items: center; gap: 5px;
            background: transparent; color: #dc2626; border: 1.5px solid #fca5a5;
            padding: 7px 14px; border-radius: 8px; font-size: .8rem; font-weight: 700;
            cursor: pointer; transition: all .2s;
        }
        .dsc-btn-delete:hover {
            background: #dc2626; color: #fff; border-color: #dc2626;
            transform: translateY(-1px); box-shadow: 0 5px 14px rgba(220,38,38,.35);
        }
        .dsc-btn-delete svg { width: 13px; height: 13px; }

        /* ── ALERT ── */
        .dsc-alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac; border-radius: 11px;
            padding: 14px 20px; color: #166534;
            display: flex; align-items: center; gap: 10px;
            font-weight: 600; margin-bottom: 22px;
        }
        .dsc-alert-success svg { width: 18px; height: 18px; color: #16a34a; flex-shrink: 0; }

        /* ── EMPTY ── */
        .dsc-empty { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .dsc-empty svg { width: 52px; height: 52px; margin-bottom: 14px; opacity: .35; display: block; margin-inline: auto; }
        .dsc-empty p { font-size: .9rem; margin: 0; }

        .dsc-pagination { padding: 16px 24px; border-top: 1px solid #f1f5f9; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- ── HERO ── --}}
    <div class="dsc-hero">
        <div class="dsc-hero-content d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="dsc-hero-pill">
                    <i data-feather="percent"></i>
                    Cobranza
                </div>
                <h4>Descuentos y Promociones</h4>
                <p>Configura reglas como hermanos, pronto pago, becas, etc.</p>
            </div>
            <a href="{{ route('admin-sistema.descuentos.create') }}" class="dsc-btn-nueva">
                <i data-feather="plus-circle"></i>
                Nuevo Descuento
            </a>
        </div>
    </div>

    {{-- ── ALERT ── --}}
    @if(session('success'))
        <div class="dsc-alert-success">
            <i data-feather="check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── STATS ── --}}
    <div class="row g-3 mb-0">
        <div class="col-6 col-lg-3">
            <div class="dsc-stat">
                <div class="dsc-stat-icon"><i data-feather="layers"></i></div>
                <div>
                    <div class="dsc-stat-num">{{ $descuentos->total() }}</div>
                    <div class="dsc-stat-label">Total descuentos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dsc-stat s2">
                <div class="dsc-stat-icon"><i data-feather="check-circle"></i></div>
                <div>
                    <div class="dsc-stat-num">{{ $descuentos->where('activo', true)->count() }}</div>
                    <div class="dsc-stat-label">Activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dsc-stat s3">
                <div class="dsc-stat-icon"><i data-feather="users"></i></div>
                <div>
                    <div class="dsc-stat-num">{{ $descuentos->where('tipo','HERMANOS')->count() }}</div>
                    <div class="dsc-stat-label">Hermanos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dsc-stat s4">
                <div class="dsc-stat-icon"><i data-feather="award"></i></div>
                <div>
                    <div class="dsc-stat-num">{{ $descuentos->whereIn('tipo',['BECA','PRONTO_PAGO'])->count() }}</div>
                    <div class="dsc-stat-label">Becas / Pronto pago</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLE CARD ── --}}
    <div class="dsc-table-card">
        <div class="dsc-table-header">
            <h6><i data-feather="list"></i> Lista de descuentos</h6>
            <span class="dsc-total-badge">{{ $descuentos->total() }} registro(s)</span>
        </div>

        <div class="table-responsive">
            <table class="dsc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Año</th>
                        <th>Nivel</th>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Aplica a</th>
                        <th>Valor</th>
                        <th>Activo</th>
                        <th class="text-end" style="padding-right:22px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($descuentos as $d)
                        <tr>
                            <td><span class="dsc-id-badge">{{ $d->id }}</span></td>
                            <td>
                                <span class="dsc-anio">
                                    <i data-feather="calendar"></i>
                                    {{ $d->anioEscolar->nombre ?? 'Todos' }}
                                </span>
                            </td>
                            <td>
                                @php $nv = strtolower($d->nivel ?? 'todos'); @endphp
                                <span class="dsc-nivel-pill {{ $nv }}">{{ $d->nivel ?? 'Todos' }}</span>
                            </td>
                            <td>{{ $d->conceptoPago->nombre ?? '-' }}</td>
                            <td>
                                @php $tp = strtolower(str_replace('_','-',$d->tipo)); @endphp
                                <span class="dsc-tipo-pill {{ strtolower(str_replace('_','_',$d->tipo)) }}">
                                    {{ $d->tipo }}
                                </span>
                            </td>
                            <td>
                                @if($d->aplica_a)
                                    <span class="dsc-aplica-pill">{{ $d->aplica_a }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(!is_null($d->porcentaje))
                                    <span class="dsc-valor">{{ number_format($d->porcentaje, 2) }}%</span>
                                @elseif(!is_null($d->monto_fijo))
                                    <span class="dsc-valor">S/ {{ number_format($d->monto_fijo, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                                @if($d->dia_limite)
                                    <br><small class="text-muted" style="font-size:.72rem;">día {{ $d->dia_limite }}</small>
                                @endif
                            </td>
                            <td>
                                @if($d->activo)
                                    <span class="dsc-badge-si">Activo</span>
                                @else
                                    <span class="dsc-badge-no">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end" style="padding-right:18px;">
                                <div class="d-inline-flex gap-2 align-items-center">
                                    <a href="{{ route('admin-sistema.descuentos.edit', $d) }}" class="dsc-btn-edit">
                                        <i data-feather="edit-2"></i> Editar
                                    </a>
                                    <form action="{{ route('admin-sistema.descuentos.destroy', $d) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('¿Eliminar este descuento?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dsc-btn-delete">
                                            <i data-feather="trash-2"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="dsc-empty">
                                    <i data-feather="tag"></i>
                                    <p>No hay descuentos registrados todavía.<br>
                                       <small>Haz clic en "Nuevo Descuento" para agregar el primero.</small>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="dsc-pagination">
            {{ $descuentos->links() }}
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
