@extends('layouts.mantis')

@section('title', 'Asistencia QR | Auxiliar')
@section('page-title', 'Asistencia (QR)')
@section('body-class', 'role-auxiliar')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/auxiliar-dashboard.css') }}?v={{ time() }}">
    <style>
        #reader {
            width: 100%; max-width: 520px; min-height: 380px;
            border: 1px solid #e5e7eb; border-radius: 14px;
            overflow: hidden; background: #000;
        }
    </style>
@endpush

@section('sidebar')
    @include('auxiliar.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-docente-filter mt-2">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Asistencia por QR</h5>
                <p class="jp-docente-filter-sub">
                    Activa la c&aacute;mara y escanea el carnet del alumno para marcarlo como <b>PRESENTE</b>.
                </p>
                <div class="jp-docente-filter-meta">
                    <span class="jp-docente-filter-pill">
                        <i data-feather="camera"></i> C&aacute;mara
                    </span>
                    <span class="jp-docente-filter-pill is-accent">
                        <i data-feather="check-circle"></i> Registro r&aacute;pido
                    </span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('auxiliar.asistencias.index') }}" class="jp-docente-history-btn">
                    <i data-feather="arrow-left"></i> Volver
                </a>
                <a href="{{ route('auxiliar.asistencias.create') }}" class="jp-docente-history-btn">
                    <i data-feather="edit-3"></i> Registrar manual
                </a>
                <a href="{{ route('auxiliar.asistencias.historial') }}" class="jp-docente-history-btn">
                    <i data-feather="clock"></i> Historial
                </a>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2 mb-3">
                        <button id="btnStart" class="btn btn-primary">
                            <i data-feather="camera"></i> Activar c&aacute;mara
                        </button>
                        <button id="btnStop" class="btn btn-outline-secondary" disabled>
                            <i data-feather="slash"></i> Detener
                        </button>
                    </div>
                    <div id="reader"></div>
                    <div class="mt-3 small text-muted">
                        Tip: usa Chrome/Edge y permite el acceso a la c&aacute;mara.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Recomendaciones</h6>
                    <ul class="mb-0 text-muted small">
                        <li>Escanea el QR una sola vez por alumno.</li>
                        <li>Si sale "Ya registrado hoy", es normal (evita duplicados).</li>
                        <li>Puedes registrar asistencia en cualquier secci&oacute;n.</li>
                    </ul>
                    <div class="alert alert-info mt-3 mb-0">
                        <i data-feather="info" class="me-2"></i>
                        El sistema registra en la fecha de <b>hoy</b> como <b>PRESENTE</b>.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let scanner = null;
        let lastScanned = null;
        const btnStart = document.getElementById('btnStart');
        const btnStop  = document.getElementById('btnStop');

        const toast = (icon, title, text = '') => Swal.fire({
            toast: true, position: 'top-end', icon, title, text,
            timer: icon === 'success' ? 1600 : 2400,
            showConfirmButton: false, timerProgressBar: true
        });

        async function registerAttendance(decodedText) {
            try {
                const res = await fetch("{{ route('auxiliar.asistencias.scan') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ codigo: decodedText })
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok) { toast('success', 'Asistencia registrada', data.alumno ?? ''); return; }
                if (res.status === 409) { toast('warning', 'Ya registrado hoy', data.alumno ?? ''); lastScanned = null; return; }
                toast('error', data.message ?? 'No se pudo registrar', '');
                lastScanned = null;
            } catch (e) {
                toast('error', 'Error de conexión', 'Intenta nuevamente.');
                lastScanned = null;
            }
        }

        async function startCamera() {
            if (scanner) return;
            scanner = new Html5Qrcode("reader");
            try {
                await scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 260 },
                    (decodedText) => {
                        if (decodedText === lastScanned) return;
                        lastScanned = decodedText;
                        registerAttendance(decodedText);
                    }
                );
                btnStart.disabled = true; btnStop.disabled = false;
            } catch (e) {
                toast('error', 'No se pudo activar la cámara', e?.message ?? String(e));
                await stopCamera();
            }
        }

        async function stopCamera() {
            if (!scanner) return;
            try { await scanner.stop(); await scanner.clear(); } catch (e) {}
            scanner = null; lastScanned = null;
            btnStart.disabled = false; btnStop.disabled = true;
        }

        btnStart.addEventListener('click', startCamera);
        btnStop.addEventListener('click', stopCamera);
        window.addEventListener('beforeunload', stopCamera);
    </script>
@endpush
