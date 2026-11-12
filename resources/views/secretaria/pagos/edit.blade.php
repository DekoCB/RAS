@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Editar Pago #' . $pago->id . ' | Secretaria')
@section('page-title', 'Editar Pago')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
<style>
/* ── Layout ─────────────────────────────────── */
.ep-wrap { max-width: 860px; margin: 0 auto; padding-bottom: 2rem; }

/* ── Header card ─────────────────────────────── */
.ep-header {
    background: linear-gradient(120deg,#192151 0%,#1f2e8d 60%,#213197 100%);
    border-radius: 18px; padding: 1.4rem 1.8rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap;
    box-shadow: 0 10px 28px rgba(31,46,141,.28);
    margin-bottom: 1.5rem;
}
.ep-header-left h4 {
    color:#fff; font-weight:800; font-size:1.15rem; margin:0 0 .2rem;
    display:flex; align-items:center; gap:.5rem;
}
.ep-header-left h4 i { width:18px; height:18px; }
.ep-header-left p { color:rgba(255,255,255,.75); font-size:.82rem; margin:0; }
.ep-badge-estado {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.38rem 1rem; border-radius:999px;
    font-size:.8rem; font-weight:800; letter-spacing:.03em; text-transform:uppercase;
}
.ep-badge-confirmado { background:#dcfce7; color:#15803d; }
.ep-badge-pendiente  { background:#c3f0fe; color:#192572; }
.ep-badge-anulado    { background:#e2eefe; color:#1c297e; }

/* ── Card ─────────────────────────────────────── */
.ep-card {
    background:#fff; border-radius:16px;
    border:1px solid #e8f0fe;
    box-shadow:0 3px 16px rgba(31,46,141,.07);
    overflow:hidden; margin-bottom:1.2rem;
}
.ep-card-header {
    padding:.85rem 1.3rem;
    border-bottom:1px solid #e8f0fe;
    display:flex; align-items:center; gap:.5rem;
    background:#f8fbff;
}
.ep-card-header h6 {
    margin:0; font-weight:800; font-size:.9rem; color:#192151;
    display:flex; align-items:center; gap:.45rem;
}
.ep-card-header h6 i { width:16px; height:16px; color:#1f2e8d; }
.ep-card-body { padding:1.3rem; }

/* ── Alumno row ─────────────────────────────── */
.ep-alumno {
    display:flex; align-items:center; gap:.9rem;
    background:#f0f4ff; border-radius:12px; padding:.9rem 1.1rem;
}
.ep-alumno-avatar {
    width:42px; height:42px; border-radius:50%;
    background:linear-gradient(135deg,#1f2e8d,#3d4a9e);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.95rem; flex-shrink:0;
}
.ep-alumno-name { font-weight:700; color:#1e293b; font-size:.92rem; }
.ep-alumno-meta { font-size:.78rem; color:#64748b; }

/* ── Deuda table ─────────────────────────────── */
.ep-table { width:100%; border-collapse:collapse; }
.ep-table thead th {
    background:#f0f4ff; color:#192151;
    font-size:.72rem; font-weight:800; text-transform:uppercase;
    letter-spacing:.05em; padding:.65rem 1rem;
    border-bottom:1.5px solid #dbeafe;
}
.ep-table tbody td {
    padding:.7rem 1rem; border-bottom:1px solid #f0f4ff;
    font-size:.87rem; color:#334155; vertical-align:middle;
}
.ep-table tbody tr:last-child td { border-bottom:0; }
.ep-table tfoot td {
    padding:.75rem 1rem; font-weight:800; font-size:.9rem;
    color:#192151; border-top:2px solid #dbeafe; background:#f8fbff;
}
.ep-monto-input {
    width:110px; padding:.35rem .6rem;
    border:1.5px solid #bfdbfe; border-radius:8px;
    font-size:.87rem; text-align:right; font-weight:600; color:#192151;
    background:#fff; transition:border-color .18s;
    -moz-appearance:textfield;
}
.ep-monto-input:focus { border-color:#1f2e8d; outline:none; box-shadow:0 0 0 3px rgba(31,46,141,.12); }
.ep-monto-input::-webkit-inner-spin-button,
.ep-monto-input::-webkit-outer-spin-button { -webkit-appearance:none; }

/* ── Vouchers ─────────────────────────────────── */
.ep-voucher-grid { display:flex; flex-wrap:wrap; gap:.9rem; }
.ep-voucher-item {
    border:1.5px solid #dbeafe; border-radius:12px;
    overflow:hidden; width:160px; background:#f8fbff;
    transition:box-shadow .18s;
}
.ep-voucher-item:hover { box-shadow:0 4px 16px rgba(31,46,141,.14); }
.ep-voucher-thumb {
    width:100%; height:110px; object-fit:cover; display:block; cursor:pointer;
    background:#e0e7ff;
}
.ep-voucher-pdf {
    width:100%; height:110px; display:flex; align-items:center; justify-content:center;
    flex-direction:column; gap:.4rem; background:#e2eefe; color:#1c297e;
    text-decoration:none;
}
.ep-voucher-pdf i { width:32px; height:32px; }
.ep-voucher-pdf span { font-size:.72rem; font-weight:700; }
.ep-voucher-foot {
    padding:.5rem .7rem;
    border-top:1px solid #dbeafe;
    display:flex; align-items:center; justify-content:space-between; gap:.4rem;
}
.ep-voucher-foot-info { font-size:.7rem; color:#64748b; line-height:1.3; }
.ep-voucher-del {
    display:inline-flex; align-items:center; justify-content:center;
    width:26px; height:26px; border-radius:7px;
    background:#e2eefe; color:#1c297e; border:0; cursor:pointer;
    transition:background .18s; flex-shrink:0;
}
.ep-voucher-del:hover { background:#cae0fe; }
.ep-voucher-del i { width:12px; height:12px; }
.ep-upload-zone {
    border:2px dashed #bfdbfe; border-radius:12px;
    padding:1.4rem; text-align:center; cursor:pointer;
    background:#f8fbff; transition:all .2s;
}
.ep-upload-zone:hover, .ep-upload-zone.is-drag { border-color:#1f2e8d; background:#eff6ff; }
.ep-upload-zone i { width:32px; height:32px; color:#bdc6fd; margin-bottom:.5rem; }
.ep-upload-zone p { color:#64748b; font-size:.84rem; margin:0; }
.ep-upload-zone strong { color:#1f2e8d; }
.ep-upload-preview {
    display:none; margin-top:.75rem;
    border:1.5px solid #bfdbfe; border-radius:10px; padding:.6rem;
    display:flex; align-items:center; gap:.6rem;
}
.ep-upload-preview img { width:48px; height:48px; object-fit:cover; border-radius:6px; }
.ep-upload-preview .ep-up-name { font-size:.82rem; font-weight:600; color:#192151; }
.ep-upload-preview .ep-up-size { font-size:.74rem; color:#94a3b8; }

/* ── Form fields ─────────────────────────────── */
.ep-form-label { font-weight:700; font-size:.83rem; color:#192151; margin-bottom:.35rem; display:block; }
.ep-form-control {
    border:1.5px solid #bfdbfe; border-radius:10px; padding:.5rem .8rem;
    font-size:.87rem; color:#1e293b; transition:border-color .18s; width:100%;
}
.ep-form-control:focus { border-color:#1f2e8d; outline:none; box-shadow:0 0 0 3px rgba(31,46,141,.1); }

/* ── Buttons ─────────────────────────────────── */
.ep-btn-save {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem 1.5rem; border-radius:12px;
    background:linear-gradient(135deg,#1f2e8d,#213197);
    color:#fff; font-weight:700; font-size:.9rem;
    border:0; cursor:pointer; transition:all .2s;
}
.ep-btn-save:hover { opacity:.92; transform:translateY(-1px); }
.ep-btn-save i { width:16px; height:16px; }
.ep-btn-back {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem 1.2rem; border-radius:12px;
    background:#f1f5f9; color:#475569; font-weight:700; font-size:.9rem;
    border:1.5px solid #e2e8f0; text-decoration:none; transition:all .2s;
}
.ep-btn-back:hover { background:#e2e8f0; color:#334155; }
.ep-btn-back i { width:15px; height:15px; }

/* ── Total display ────────────────────────────── */
.ep-total-display {
    background:linear-gradient(135deg,#1f2e8d,#213197);
    color:#fff; border-radius:10px;
    padding:.5rem 1rem; font-size:1rem; font-weight:800;
    display:inline-flex; align-items:center; gap:.4rem;
}

/* ── Alert ────────────────────────────────────── */
.ep-alert-success {
    display:flex; align-items:center; gap:.75rem;
    background:#dcfce7; border:1px solid #86efac; border-radius:12px;
    padding:.9rem 1.2rem; color:#166534; font-size:.88rem; font-weight:600;
    margin-bottom:1.2rem;
}
.ep-alert-success i { width:18px; height:18px; flex-shrink:0; }
.ep-alert-error {
    display:flex; align-items:flex-start; gap:.75rem;
    background:#fee2e2; border:1px solid #fca5a5; border-radius:12px;
    padding:.9rem 1.2rem; color:#991b1b; font-size:.87rem;
    margin-bottom:1.2rem;
}
.ep-alert-error i { width:18px; height:18px; flex-shrink:0; margin-top:.1rem; }

/* ── Lightbox ─────────────────────────────────── */
.ep-lightbox-overlay {
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(0,0,0,.85); align-items:center; justify-content:center;
}
.ep-lightbox-overlay.is-open { display:flex; }
.ep-lightbox-overlay img {
    max-width:92vw; max-height:88vh; border-radius:10px;
    box-shadow:0 20px 60px rgba(0,0,0,.5);
}
.ep-lightbox-close {
    position:absolute; top:1rem; right:1rem;
    background:rgba(255,255,255,.15); border:0; color:#fff;
    width:38px; height:38px; border-radius:50%; cursor:pointer; font-size:1.3rem;
    display:flex; align-items:center; justify-content:center;
    transition:background .18s;
}
.ep-lightbox-close:hover { background:rgba(255,255,255,.28); }
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
@php
    $primeraDeuda = $pago->deudas->first();
    $estudiante   = $primeraDeuda?->matricula?->estudiante;
    $nombreAlumno = $estudiante
        ? $estudiante->apellidos . ', ' . $estudiante->nombres
        : 'Alumno desconocido';
    $dni = $estudiante?->dni ?? '-';
    $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $estadoBadge = match($pago->estado) {
        'confirmado' => 'ep-badge-confirmado',
        'anulado'    => 'ep-badge-anulado',
        default      => 'ep-badge-pendiente',
    };
@endphp

<div class="ep-wrap mt-3">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="ep-header">
        <div class="ep-header-left">
            <h4><i data-feather="edit-3"></i> Pago #{{ $pago->id }}</h4>
            <p>Registrado por <strong>{{ $pago->registradoPor?->name ?? '-' }}</strong>
               · {{ \Carbon\Carbon::parse($pago->created_at)->format('d/m/Y H:i') }}</p>
        </div>
        <span class="ep-badge-estado {{ $estadoBadge }}">
            <i data-feather="{{ $pago->estado === 'confirmado' ? 'check-circle' : ($pago->estado === 'anulado' ? 'x-circle' : 'clock') }}"
               style="width:13px;height:13px;"></i>
            {{ ucfirst($pago->estado) }}
        </span>
    </div>

    {{-- ── Alerts ───────────────────────────────────────────── --}}
    @if (session('status'))
        <div class="ep-alert-success">
            <i data-feather="check-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="ep-alert-error">
            <i data-feather="alert-circle"></i>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Formulario principal ────────────────────────────── --}}
    <form method="POST" action="{{ route($rp . 'pagos.update', $pago) }}"
          enctype="multipart/form-data" id="formEditPago">
        @csrf
        @method('PUT')

        {{-- ── Card: Alumno ──────────────────────────────────── --}}
        <div class="ep-card">
            <div class="ep-card-header">
                <h6><i data-feather="user"></i> Alumno</h6>
            </div>
            <div class="ep-card-body">
                <div class="ep-alumno">
                    <div class="ep-alumno-avatar">{{ strtoupper(substr($nombreAlumno, 0, 1)) }}</div>
                    <div>
                        <div class="ep-alumno-name">{{ $nombreAlumno }}</div>
                        <div class="ep-alumno-meta">DNI: {{ $dni }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card: Deudas con montos editables ──────────────── --}}
        <div class="ep-card">
            <div class="ep-card-header">
                <h6><i data-feather="list"></i> Conceptos aplicados
                    <span class="text-muted fw-normal ms-1" style="font-size:.78rem;">(puedes modificar el monto por concepto)</span>
                </h6>
            </div>
            <div class="ep-card-body" style="padding:0;">
                <table class="ep-table">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Período</th>
                            <th class="text-end">Monto aplicado (S/)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pago->deudas as $deuda)
                            @php
                                $periodo = $deuda->periodo;
                                $periodoLabel = ($periodo && $periodo <= 12)
                                    ? $meses[$periodo]
                                    : ($periodo ?? '-');
                                $montoActual = number_format((float) $deuda->pivot->monto_aplicado, 2, '.', '');
                            @endphp
                            <tr>
                                <td>{{ $deuda->concepto?->nombre ?? '-' }}</td>
                                <td>{{ $periodoLabel }}</td>
                                <td class="text-end">
                                    <input type="number"
                                           name="montos_deuda[{{ $deuda->id }}]"
                                           value="{{ $montoActual }}"
                                           class="ep-monto-input monto-deuda-input"
                                           step="0.01" min="0" max="99999.99"
                                           data-original="{{ $montoActual }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">Total</td>
                            <td class="text-end">
                                <span class="ep-total-display" id="totalDisplay">
                                    S/ {{ number_format((float) $pago->monto, 2) }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Card: Datos del pago ─────────────────────────── --}}
        <div class="ep-card">
            <div class="ep-card-header">
                <h6><i data-feather="settings"></i> Datos del pago</h6>
            </div>
            <div class="ep-card-body">
                @php
                    // Obtener método simple actual (puede ser JSON o string legacy)
                    $metodosDecoded = $pago->metodosDecoded;
                    $metodoSimple   = count($metodosDecoded) === 1
                        ? $metodosDecoded[0]['metodo']
                        : ($pago->metodo && !str_starts_with(trim($pago->metodo), '[') ? $pago->metodo : '');
                    $opActual = $pago->numero_operacion
                        ?? ($metodosDecoded[0]['op'] ?? '');
                    $metodosConOp = ['Yape', 'Plin', 'Transferencia'];
                    $mostrarOp = in_array(old('metodo', $metodoSimple), $metodosConOp);
                @endphp
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="ep-form-label">Fecha de pago *</label>
                        <input type="date" name="fecha_pago" class="ep-form-control" required
                               value="{{ old('fecha_pago', \Carbon\Carbon::parse($pago->fecha_pago)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="ep-form-label">Método de pago</label>
                        <select name="metodo" class="ep-form-control" id="metodoSelect">
                            <option value="">— Seleccione —</option>
                            @foreach (['Efectivo', 'Yape', 'Plin', 'Transferencia'] as $met)
                                <option value="{{ $met }}"
                                    @selected(old('metodo', $metodoSimple) === $met)>
                                    {{ $met }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ep-form-label">Estado *</label>
                        <select name="estado" class="ep-form-control" required>
                            <option value="confirmado" @selected(old('estado', $pago->estado) === 'confirmado')>Confirmado</option>
                            <option value="pendiente"  @selected(old('estado', $pago->estado) === 'pendiente')>Pendiente</option>
                            <option value="anulado"    @selected(old('estado', $pago->estado) === 'anulado')>Anulado</option>
                        </select>
                    </div>

                    {{-- Número de operación (visible solo para Yape / Plin / Transferencia) --}}
                    <div class="col-md-6" id="wrapNumOp" style="{{ $mostrarOp ? '' : 'display:none;' }}">
                        <label class="ep-form-label">
                            <i data-feather="hash" style="width:13px;height:13px;"></i>
                            Número de operación
                            <span id="opRequerido" class="text-danger ms-1"
                                  style="{{ $mostrarOp ? '' : 'display:none;' }}">*</span>
                        </label>
                        <input type="text"
                               name="numero_operacion"
                               id="numeroOperacion"
                               class="ep-form-control"
                               placeholder="Ej: 123456789"
                               maxlength="60"
                               value="{{ old('numero_operacion', $opActual) }}"
                               {{ $mostrarOp ? 'required' : '' }}>
                        <div class="mt-1" style="font-size:.74rem;color:#64748b;">
                            Número de transacción del comprobante digital.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="ep-form-label">Comentario</label>
                        <input type="text" name="comentario" class="ep-form-control"
                               placeholder="Observación opcional"
                               value="{{ old('comentario', $pago->comentario) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card: Vouchers / Comprobantes ───────────────────── --}}
        <div class="ep-card">
            <div class="ep-card-header">
                <h6><i data-feather="image"></i> Comprobantes / Vouchers</h6>
            </div>
            <div class="ep-card-body">

                {{-- Vouchers existentes --}}
                @if ($pago->vouchers->count() > 0)
                    <div class="ep-voucher-grid mb-3">
                        @foreach ($pago->vouchers as $voucher)
                            @php
                                $ext = strtolower(pathinfo($voucher->archivo_path, PATHINFO_EXTENSION));
                                $url = asset('storage/' . $voucher->archivo_path);
                                $esImagen = in_array($ext, ['jpg','jpeg','png','webp']);
                            @endphp
                            <div class="ep-voucher-item">
                                @if ($esImagen)
                                    <img src="{{ $url }}"
                                         class="ep-voucher-thumb"
                                         alt="Voucher"
                                         onclick="abrirLightbox('{{ $url }}')">
                                @else
                                    <a href="{{ $url }}" target="_blank" class="ep-voucher-pdf">
                                        <i data-feather="file-text"></i>
                                        <span>{{ strtoupper($ext) }}</span>
                                    </a>
                                @endif
                                <div class="ep-voucher-foot">
                                    <div class="ep-voucher-foot-info">
                                        <div>{{ $voucher->created_at->format('d/m/Y') }}</div>
                                        <div>
                                            <span class="badge {{ $voucher->estado === 'validado' ? 'bg-success' : 'bg-warning text-dark' }}"
                                                  style="font-size:.65rem;">
                                                {{ ucfirst($voucher->estado) }}
                                            </span>
                                        </div>
                                    </div>
                                    {{-- Botón eliminar usando JS para evitar forms anidados --}}
                                    <button type="button"
                                            class="ep-voucher-del"
                                            title="Eliminar comprobante"
                                            onclick="eliminarVoucher('{{ route($rp . 'pagos.vouchers.destroy', [$pago, $voucher]) }}')">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        <i data-feather="info" style="width:14px;height:14px;"></i>
                        No hay comprobantes adjuntos a este pago.
                    </p>
                @endif

                {{-- Upload zona --}}
                <label class="ep-form-label">Agregar nuevo comprobante</label>
                <div class="ep-upload-zone" id="uploadZone" onclick="document.getElementById('voucherInput').click()">
                    <i data-feather="upload-cloud"></i>
                    <p><strong>Haz clic para seleccionar</strong> o arrastra aquí</p>
                    <p style="font-size:.76rem;color:#94a3b8;margin-top:.25rem;">JPG, PNG o PDF · máx. 5 MB</p>
                </div>
                <input type="file" name="voucher_archivo" id="voucherInput"
                       accept=".jpg,.jpeg,.png,.pdf" style="display:none">

                <div class="ep-upload-preview mt-2" id="uploadPreview" style="display:none;">
                    <img id="previewImg" src="" alt="" style="display:none;">
                    <div id="previewIcon" style="display:none;width:48px;height:48px;background:#e2eefe;border-radius:6px;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-feather="file-text" style="color:#1c297e;width:22px;height:22px;"></i>
                    </div>
                    <div>
                        <div class="ep-up-name" id="previewName"></div>
                        <div class="ep-up-size" id="previewSize"></div>
                    </div>
                    <button type="button" onclick="limpiarPreview()"
                            style="margin-left:auto;background:none;border:0;color:#94a3b8;cursor:pointer;font-size:1.1rem;">✕</button>
                </div>
            </div>
        </div>

        {{-- ── Acciones ─────────────────────────────────────── --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route($rp . 'pagos.index') }}" class="ep-btn-back">
                <i data-feather="arrow-left"></i> Volver
            </a>
            <button type="submit" class="ep-btn-save">
                <i data-feather="save"></i> Guardar cambios
            </button>
            <a href="{{ route($rp . 'pagos.voucher', $pago) }}" target="_blank"
               class="ep-btn-back" style="color:#15803d;border-color:#86efac;background:#dcfce7;">
                <i data-feather="printer"></i> Imprimir recibo
            </a>
        </div>

    </form>
</div>

{{-- ── Lightbox ─────────────────────────────────────────────────── --}}
<div class="ep-lightbox-overlay" id="lightboxOverlay" onclick="cerrarLightbox()">
    <button class="ep-lightbox-close" onclick="cerrarLightbox()">✕</button>
    <img src="" id="lightboxImg" alt="Voucher" onclick="event.stopPropagation()">
</div>

@push('scripts')
<script>
feather.replace();

// ── Eliminar voucher (sin forms anidados) ──────────────────────
function eliminarVoucher(url) {
    if (!confirm('¿Eliminar este comprobante?')) return;

    // Crear form temporal en document.body (fuera del form principal)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';
    form.innerHTML =
        '<input type="hidden" name="_token" value="' +
        document.querySelector('meta[name="csrf-token"]').content + '">' +
        '<input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form);
    form.submit();
}

// ── Número de operación (Yape / Plin / Transferencia) ──────────
const metodoSelect  = document.getElementById('metodoSelect');
const wrapNumOp     = document.getElementById('wrapNumOp');
const opRequerido   = document.getElementById('opRequerido');
const numOpInput    = document.getElementById('numeroOperacion');
const metodosConOp  = ['Yape', 'Plin', 'Transferencia'];

function toggleNumOp() {
    const necesita = metodosConOp.includes(metodoSelect.value);
    wrapNumOp.style.display  = necesita ? '' : 'none';
    opRequerido.style.display = necesita ? '' : 'none';
    numOpInput.required = necesita;
    if (!necesita) numOpInput.value = '';
}
metodoSelect.addEventListener('change', toggleNumOp);

// ── Recálculo de total ─────────────────────────────────────────
const montoInputs = document.querySelectorAll('.monto-deuda-input');
const totalDisplay = document.getElementById('totalDisplay');

function recalcularTotal() {
    let total = 0;
    montoInputs.forEach(function(input) {
        const val = parseFloat(input.value) || 0;
        total += val;
    });
    totalDisplay.textContent = 'S/ ' + total.toFixed(2);
}

montoInputs.forEach(function(input) {
    input.addEventListener('input', recalcularTotal);
});

// ── Upload preview ─────────────────────────────────────────────
const voucherInput = document.getElementById('voucherInput');
const uploadZone   = document.getElementById('uploadZone');
const uploadPreview= document.getElementById('uploadPreview');
const previewImg   = document.getElementById('previewImg');
const previewIcon  = document.getElementById('previewIcon');
const previewName  = document.getElementById('previewName');
const previewSize  = document.getElementById('previewSize');

voucherInput.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    mostrarPreview(file);
});

// Drag & Drop
uploadZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('is-drag');
});
uploadZone.addEventListener('dragleave', function() {
    this.classList.remove('is-drag');
});
uploadZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('is-drag');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        voucherInput.files = dt.files;
        mostrarPreview(file);
    }
});

function mostrarPreview(file) {
    previewName.textContent = file.name;
    previewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
    uploadPreview.style.display = 'flex';

    const esImagen = file.type.startsWith('image/');
    if (esImagen) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            previewIcon.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        previewImg.style.display = 'none';
        previewIcon.style.display = 'flex';
        feather.replace();
    }
}

function limpiarPreview() {
    voucherInput.value = '';
    uploadPreview.style.display = 'none';
    previewImg.src = '';
}

// ── Lightbox ──────────────────────────────────────────────────
function abrirLightbox(url) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightboxOverlay').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}
function cerrarLightbox() {
    document.getElementById('lightboxOverlay').classList.remove('is-open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarLightbox();
});
</script>
@endpush

@endsection
