@extends('layouts.mantis')

@section('title', 'Asistencia QR | Docente')
@section('page-title', 'Asistencia (QR)')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
    <style>
        #reader {
            width: 100%;
            max-width: 520px;
            min-height: 380px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            background: #000;
        }
    </style>
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    {{-- Header / acciones --}}
    <div class="card jp-docente-filter mt-2">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Asistencia por QR</h5>
                <p class="jp-docente-filter-sub">
                    Activa la c&aacute;mara y escanea el carnet del alumno para marcarlo como <b>PRESENTE</b>.
                </p>
                <div class="jp-docente-filter-meta">
                    <span class="jp-docente-filter-pill">
                        <i data-feather="camera"></i>
                        C&aacute;mara
                    </span>
                    <span class="jp-docente-filter-pill is-accent">
                        <i data-feather="check-circle"></i>
                        Registro r&aacute;pido
                    </span>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('docente.asistencias.index') }}" class="jp-docente-history-btn"
                    style="text-decoration:none;">
                    <i data-feather="arrow-left"></i>
                    Volver
                </a>

                <a href="{{ route('docente.asistencias.create') }}" class="jp-docente-history-btn"
                    style="text-decoration:none;">
                    <i data-feather="edit-3"></i>
                    Registrar manual
                </a>

                <a href="{{ route('docente.asistencias.historial') }}" class="jp-docente-history-btn"
                    style="text-decoration:none;">
                    <i data-feather="clock"></i>
                    Ver historial
                </a>
            </div>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="row mt-4 g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2 mb-3">
                        <button id="btnStart" class="btn btn-primary">
                            <i data-feather="camera"></i>
                            Activar c&aacute;mara
                        </button>
                        <button id="btnStop" class="btn btn-outline-secondary" disabled>
                            <i data-feather="slash"></i>
                            Detener
                        </button>
                    </div>

                    <div id="reader"></div>

                    <div class="mt-3 small text-muted">
                        Tip: usa Chrome/Edge y permite el acceso a la c&aacute;mara.
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel de ayuda / estado --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Recomendaciones</h6>
                    <ul class="mb-0 text-muted small">
                        <li>Escanea el QR una sola vez por alumno.</li>
                        <li>Si sale “Ya registrado hoy”, es normal (evita duplicados).</li>
                        <li>Solo registrar&aacute; alumnos de secciones donde dictas cursos.</li>
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
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- html5-qrcode --}}
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        let scanner = null;
        let lastScanned = null;

        const btnStart = document.getElementById('btnStart');
        const btnStop = document.getElementById('btnStop');

        function toastSuccess(title, text = '') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title,
                text,
                timer: 1600,
                showConfirmButton: false,
                timerProgressBar: true
            });
        }

        function toastWarn(title, text = '') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title,
                text,
                timer: 2200,
                showConfirmButton: false,
                timerProgressBar: true
            });
        }

        function toastError(title, text = '') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title,
                text,
                timer: 2400,
                showConfirmButton: false,
                timerProgressBar: true
            });
        }

        async function registerAttendance(decodedText) {
            try {
                const res = await fetch("{{ route('docente.asistencias.scan') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        codigo: decodedText
                    })
                });

                const data = await res.json().catch(() => ({}));

                // ✅ OK
                if (res.ok) {
                    toastSuccess("Asistencia registrada", data.alumno ?? ("QR: " + decodedText));
                    return;
                }

                // ✅ Duplicado (ya registrado hoy)
                if (res.status === 409) {
                    toastWarn("Ya registrado hoy", data.alumno ?? ("QR: " + decodedText));
                    lastScanned = null; // permitir reintento
                    return;
                }

                // ✅ Sin permiso
                if (res.status === 403) {
                    toastError("Sin permiso", data.message ?? "No tienes acceso a esta sección.");
                    lastScanned = null;
                    return;
                }

                // ✅ Otros errores
                toastError(data.message ?? "No se pudo registrar", data.alumno ?? ("QR: " + decodedText));
                lastScanned = null;

            } catch (e) {
                console.error(e);
                toastError("Error de conexión", "Intenta nuevamente.");
                lastScanned = null;
            }
        }

        async function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                toastError("Tu navegador no soporta c&aacute;mara", "Usa Chrome/Edge actualizado.");
                return;
            }
            if (scanner) return;

            scanner = new Html5Qrcode("reader");

            try {
                await scanner.start({
                        facingMode: "environment"
                    }, // trasera si existe
                    {
                        fps: 10,
                        qrbox: 260
                    },
                    (decodedText) => {
                        if (decodedText === lastScanned) return; // anti-spam
                        lastScanned = decodedText;
                        registerAttendance(decodedText);
                    }
                );

                btnStart.disabled = true;
                btnStop.disabled = false;

            } catch (e) {
                console.error(e);

                if (e?.name === "NotAllowedError") {
                    toastError("Permiso denegado", "Activa la c&aacute;mara en permisos del navegador.");
                } else if (e?.name === "NotFoundError") {
                    toastError("No se encontr&oacute; c&aacute;mara",
                        "Conecta una c&aacute;mara o revisa el dispositivo.");
                } else {
                    toastError("No se pudo activar la c&aacute;mara", e?.message ?? String(e));
                }

                await stopCamera();
            }
        }

        async function stopCamera() {
            if (!scanner) return;

            try {
                await scanner.stop();
                await scanner.clear();
            } catch (e) {}

            scanner = null;
            lastScanned = null;
            btnStart.disabled = false;
            btnStop.disabled = true;
        }

        btnStart.addEventListener('click', startCamera);
        btnStop.addEventListener('click', stopCamera);
        window.addEventListener('beforeunload', stopCamera);
    </script>
@endpush
