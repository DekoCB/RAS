@extends('layouts.mantis')

@section('title', 'Asistencia Docentes | Secretaria')
@section('page-title', 'Asistencia Docentes (QR)')
@php
    $isAdmin     = auth()->user()->hasRole('SuperAdmin');
    $isDirector  = auth()->user()->hasRole('Director');
@endphp
@section('body-class', $isAdmin ? 'role-superadmin' : ($isDirector ? 'role-director' : 'role-secretaria'))

@push('styles')
    @if($isAdmin)
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @elseif($isDirector)
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/qr-asistencias.css') }}">
@endpush

@section('sidebar')
    @if($isAdmin)
        @include('admin.partials.sidebar')
    @elseif($isDirector)
        @include('director.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
<div class="qr-page mt-2">

    {{-- ── Hero Header ── --}}
    <div class="qr-hero">
        <div class="qr-hero__left">
            <div class="qr-hero__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 015.25 3h2.25M3 18.75A2.25 2.25 0 005.25 21h2.25M18.75 3A2.25 2.25 0 0121 5.25v2.25M18.75 21A2.25 2.25 0 0021 18.75v-2.25M9 9h.008v.008H9V9zm0 6h.008v.008H9V15zm6-6h.008v.008H15V9zm0 6h.008v.008H15V15z"/>
                </svg>
            </div>
            <div>
                <h1 class="qr-hero__title">Asistencia Docentes &mdash; QR</h1>
                <p class="qr-hero__sub">Escanea el carnet para registrar <strong>ENTRADA</strong> o <strong>SALIDA</strong></p>
                <div class="qr-hero__pills">
                    <span class="qr-hero__pill">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        Hoy {{ now()->format('d/m/Y') }}
                    </span>
                    <span class="qr-hero__pill">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                        C&aacute;mara QR
                    </span>
                    <span class="qr-hero__pill">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        {{ $isAdmin ? 'Super Admin' : ($isDirector ? 'Director' : 'Secretaria') }}
                    </span>
                </div>
            </div>
        </div>
        <a href="{{ $isAdmin ? route('admin-sistema.qr-docentes.historial') : ($isDirector ? route('director.qr-docentes.historial') : route('secretaria.asistencias-docentes.historial')) }}" class="qr-hero__btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Ver historial
        </a>
    </div>

    {{-- ── Info Chips ── --}}
    <div class="qr-chips">
        <div class="qr-chip qr-chip--green">
            <div class="qr-chip__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
            </div>
            <div>
                <div class="qr-chip__label">Modalidad</div>
                <div class="qr-chip__value">ENTRADA / SALIDA</div>
            </div>
        </div>
        <div class="qr-chip qr-chip--blue">
            <div class="qr-chip__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="qr-chip__label">Registro</div>
                <div class="qr-chip__value">Autom&aacute;tico</div>
            </div>
        </div>
        <div class="qr-chip qr-chip--cyan">
            <div class="qr-chip__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <div class="qr-chip__label">Duplicados</div>
                <div class="qr-chip__value">Bloqueados</div>
            </div>
        </div>
        <a href="{{ $isAdmin ? route('admin-sistema.imprimir-qr.index') : ($isDirector ? route('director.imprimir-qr.index') : route('secretaria.imprimir-qr.index')) }}" class="qr-chip qr-chip--purple" style="text-decoration:none;cursor:pointer;" title="Imprimir carnets QR para todos los usuarios">
            <div class="qr-chip__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
            </div>
            <div>
                <div class="qr-chip__label">Imprimir QR</div>
                <div class="qr-chip__value">Carnets para usuarios</div>
            </div>
        </a>
    </div>

    {{-- ── Main Scanner Card ── --}}
    <div class="qr-scanner-card">
        <div class="qr-scanner-card__header">
            <h2 class="qr-scanner-card__title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                Lector QR
            </h2>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- Toggle tipo --}}
                <div class="qr-type-toggle">
                    <input type="radio" id="opt-entrada" name="tipo_qr" value="ENTRADA" checked>
                    <label for="opt-entrada">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        ENTRADA
                    </label>
                    <input type="radio" id="opt-salida" name="tipo_qr" value="SALIDA">
                    <label for="opt-salida">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25"/></svg>
                        SALIDA
                    </label>
                </div>

                {{-- Cam buttons --}}
                <div class="qr-cam-btns">
                    <button id="btnStart" type="button" class="qr-btn-start">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>
                        Activar c&aacute;mara
                    </button>
                    <button id="btnStop" type="button" class="qr-btn-stop" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z"/></svg>
                        Detener
                    </button>
                </div>
            </div>
        </div>

        <div class="qr-scanner-card__body">
            {{-- Camera area --}}
            <div class="qr-reader-wrap" id="readerWrap">
                <div id="reader"></div>
                <div class="qr-reader-placeholder" id="readerPlaceholder">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 015.25 3h2.25M3 18.75A2.25 2.25 0 005.25 21h2.25M18.75 3A2.25 2.25 0 0121 5.25v2.25M18.75 21A2.25 2.25 0 0021 18.75v-2.25M9 9h.008v.008H9V9zm0 6h.008v.008H9V15zm6-6h.008v.008H15V9zm0 6h.008v.008H15V15z"/>
                    </svg>
                    <span>Presiona <strong style="color:#e5e7eb">Activar c&aacute;mara</strong> para iniciar el escaneo</span>
                    <span style="font-size:.72rem;opacity:.45">Usa Chrome o Edge &middot; Permite acceso a la c&aacute;mara</span>
                </div>
                <div class="qr-scan-line" id="scanLine"></div>
                <div class="qr-scan-ring" id="scanRing"></div>
                <div class="qr-cam-status" id="camStatus">
                    <span style="display:inline-block;width:7px;height:7px;background:#10b981;border-radius:50%;margin-right:5px;vertical-align:middle;"></span>
                    Escaneando...
                </div>
            </div>

            {{-- Result panel --}}
            <div class="qr-result" id="resultPanel">
                <div class="qr-result__icon" id="resultIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="qr-result__name" id="resultName">-</div>
                    <div class="qr-result__msg"  id="resultMsg">-</div>
                    <div class="qr-result__time" id="resultTime">-</div>
                </div>
            </div>

            {{-- Recent log --}}
            <div id="logWrap" style="display:none; margin-top:1rem;">
                <div style="font-size:.78rem;font-weight:700;color:#374151;margin-bottom:.4rem;display:flex;align-items:center;gap:.35rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;stroke:#7c3aed"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    Ultimos registros de esta sesion
                </div>
                <ul class="qr-log" id="logList"></ul>
            </div>
        </div>
    </div>

    {{-- ── Rules ── --}}
    <div class="qr-rules">
        <div class="qr-rules__title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            Reglas del sistema
        </div>
        <ul>
            <li>No se permiten registros duplicados del mismo tipo (ENTRADA/SALIDA) en el mismo dia.</li>
            <li>El docente debe estar en estado <strong>activo</strong> para registrar asistencia.</li>
            <li>Tambien se registra asistencia de Auxiliares, Secretarias y Directores por este lector.</li>
        </ul>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        let scanner    = null;
        let lastScanned = null;

        const btnStart      = document.getElementById('btnStart');
        const btnStop       = document.getElementById('btnStop');
        const scanLine      = document.getElementById('scanLine');
        const scanRing      = document.getElementById('scanRing');
        const camStatus     = document.getElementById('camStatus');
        const placeholder   = document.getElementById('readerPlaceholder');
        const resultPanel   = document.getElementById('resultPanel');
        const resultIcon    = document.getElementById('resultIcon');
        const resultName    = document.getElementById('resultName');
        const resultMsg     = document.getElementById('resultMsg');
        const resultTime    = document.getElementById('resultTime');
        const logWrap       = document.getElementById('logWrap');
        const logList       = document.getElementById('logList');

        function getTipo() {
            return document.querySelector('input[name="tipo_qr"]:checked')?.value ?? 'ENTRADA';
        }

        function nowStr() {
            const now = new Date();
            return now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }

        /* ── Inline result panel ── */
        function showResult(type, name, msg) {
            // type: 'success' | 'warning' | 'danger'
            resultPanel.className = 'qr-result visible ' + type;

            const icons = {
                success: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>`,
                danger:  `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            };
            resultIcon.innerHTML = icons[type] ?? icons.warning;
            resultName.textContent = name ?? '—';
            resultMsg.textContent  = msg ?? '';
            resultTime.textContent = 'Registrado a las ' + nowStr();
        }

        /* ── Log entry ── */
        function addLog(tipo, nombre, estado, ok) {
            logWrap.style.display = 'block';
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="badge-tipo ${tipo.toLowerCase()}">${tipo}</span>
                <span class="badge-tipo ${ok ? 'ok' : 'err'}">${ok ? 'OK' : 'ERROR'}</span>
                <span class="nombre">${nombre}</span>
                <span class="hora">${nowStr()}</span>
            `;
            logList.prepend(li);
            // keep max 10
            while (logList.children.length > 10) logList.removeChild(logList.lastChild);
        }

        /* ── Toast (small, non-blocking) ── */
        function toast(icon, title, text = '') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title,
                text,
                timer: icon === 'success' ? 1800 : 2500,
                showConfirmButton: false,
                timerProgressBar: true,
                customClass: { popup: 'swal2-sm-popup' }
            });
        }

        /* ── Register scan ── */
        async function registrar(decodedText) {
            const tipo = getTipo();
            try {
                const res = await fetch("{{ $isAdmin ? route('admin-sistema.qr-docentes.scan') : ($isDirector ? route('director.qr-docentes.scan') : route('secretaria.asistencias-docentes.scan')) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ codigo: decodedText, tipo })
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw data;

                const nombre = data.docente ?? ('QR: ' + decodedText);
                showResult('success', nombre, data.message ?? 'Asistencia registrada');
                addLog(tipo, nombre, data.message, true);
                toast('success', data.message ?? 'Registrado', nombre);

            } catch (err) {
                const nombre = err.docente ?? ('QR: ' + decodedText);
                showResult('warning', nombre, err.message ?? 'No se pudo registrar');
                addLog(tipo, nombre, err.message, false);
                toast('warning', err.message ?? 'No se pudo registrar', nombre);
                lastScanned = null;
            }
        }

        /* ── Camera controls ── */
        function setCameraUI(active) {
            btnStart.disabled = active;
            btnStop.disabled  = !active;
            scanLine.classList.toggle('active', active);
            scanRing.classList.toggle('active', active);
            camStatus.classList.toggle('active', active);
            placeholder.classList.toggle('hidden', active);
        }

        async function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                toast('warning', 'Tu navegador no soporta cámara', 'Usa Chrome/Edge actualizado.');
                return;
            }
            if (scanner) return;

            scanner = new Html5Qrcode("reader");

            try {
                await scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 260 },
                    (decodedText) => {
                        if (decodedText === lastScanned) return;
                        lastScanned = decodedText;
                        registrar(decodedText);
                    }
                );
                setCameraUI(true);
            } catch (e) {
                toast('warning', 'No se pudo activar la cámara', e?.message ?? String(e));
                await stopCamera();
            }
        }

        async function stopCamera() {
            if (!scanner) return;
            try { await scanner.stop(); await scanner.clear(); } catch (e) {}
            scanner = null;
            lastScanned = null;
            setCameraUI(false);
        }

        btnStart.addEventListener('click', startCamera);
        btnStop.addEventListener('click', stopCamera);
        window.addEventListener('beforeunload', stopCamera);

        // Reset lastScanned when tipo changes so same QR can re-register after switching
        document.querySelectorAll('input[name="tipo_qr"]').forEach(r => {
            r.addEventListener('change', () => { lastScanned = null; });
        });
    </script>
@endpush
