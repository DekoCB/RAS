<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carnets QR &mdash; {{ $tipo }} | R.A.S</title>
    <style>
        /* ─── Reset ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }

        /* ─── Toolbar (oculto al imprimir) ─── */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #1f2937;
            color: #fff;
            padding: .9rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.3);
        }
        .toolbar__left {
            display: flex;
            align-items: center;
            gap: .8rem;
        }
        .toolbar__icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,.12);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .toolbar__icon svg { width: 20px; height: 20px; stroke: #fff; }
        .toolbar__title { font-size: 1rem; font-weight: 700; }
        .toolbar__sub   { font-size: .78rem; opacity: .65; margin-top: 1px; }
        .toolbar__right { display: flex; gap: .6rem; flex-shrink: 0; }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .55rem 1.2rem;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-print:hover { background: #059669; }
        .btn-print svg { width: 18px; height: 18px; stroke: #fff; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(255,255,255,.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 8px;
            padding: .55rem 1.1rem;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-back:hover { background: rgba(255,255,255,.2); color: #fff; text-decoration: none; }
        .btn-back svg { width: 16px; height: 16px; stroke: #fff; }

        .badge-count {
            background: rgba(255,255,255,.15);
            border-radius: 6px;
            padding: .25rem .7rem;
            font-size: .82rem;
            font-weight: 600;
        }

        /* ─── Contenedor ─── */
        .page-wrap {
            padding: 1.5rem;
        }

        /* ─── Grid de carnets ─── */
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        /* ─── Carnet individual ─── */
        .qr-carnet {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            border: 1px solid #e5e7eb;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .qr-carnet__header {
            padding: .6rem .9rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            border-bottom: 2px solid var(--rol-color, #7c3aed);
        }
        .qr-carnet__logo {
            width: 28px; height: 28px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .qr-carnet__school {
            font-size: .68rem;
            font-weight: 700;
            color: #374151;
            line-height: 1.2;
        }
        .qr-carnet__city {
            font-size: .6rem;
            color: #6b7280;
        }
        .qr-carnet__rol-badge {
            margin-left: auto;
            background: var(--rol-color, #7c3aed);
            color: #fff;
            font-size: .6rem;
            font-weight: 800;
            padding: .18rem .55rem;
            border-radius: 20px;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .qr-carnet__body {
            padding: .8rem .9rem;
            display: flex;
            align-items: center;
            gap: .8rem;
        }
        .qr-carnet__qr {
            flex-shrink: 0;
            width: 90px; height: 90px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: #fff;
        }
        .qr-carnet__qr img {
            width: 90px; height: 90px;
            display: block;
        }
        .qr-carnet__qr-placeholder {
            width: 90px; height: 90px;
            background: #f3f4f6;
            display: flex; align-items: center; justify-content: center;
            font-size: .65rem;
            color: #9ca3af;
            font-weight: 600;
        }
        .qr-carnet__info { flex: 1; min-width: 0; }
        .qr-carnet__name {
            font-size: .78rem;
            font-weight: 800;
            color: #111427;
            line-height: 1.25;
            margin-bottom: .3rem;
            word-break: break-word;
        }
        .qr-carnet__sub {
            font-size: .68rem;
            color: #6b7280;
            margin-bottom: .4rem;
        }
        .qr-carnet__code {
            font-size: .62rem;
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            color: #374151;
            padding: .2rem .4rem;
            border-radius: 4px;
            display: inline-block;
            word-break: break-all;
            font-weight: 700;
        }

        .qr-carnet__footer {
            padding: .35rem .9rem;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            font-size: .6rem;
            color: #9ca3af;
            text-align: center;
        }

        /* ─── Colores por rol ─── */
        .rol--Estudiante { --rol-color: #213197; }
        .rol--Docente    { --rol-color: #059669; }
        .rol--Secretaria { --rol-color: #1f2e8e; }
        .rol--Auxiliar   { --rol-color: #1f2e8f; }

        /* ─── Sin resultados ─── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        .empty-state svg { width: 48px; height: 48px; stroke: #d1d5db; margin-bottom: 1rem; }
        .empty-state h3 { font-size: 1.1rem; font-weight: 700; color: #374151; margin-bottom: .4rem; }

        /* ═══════════════════════════════
           ESTILOS DE IMPRESIÓN
           ═══════════════════════════════ */
        @media print {
            body { background: #fff !important; }

            .toolbar { display: none !important; }

            .page-wrap { padding: 0.3cm; }

            .qr-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.4cm;
            }

            .qr-carnet {
                box-shadow: none;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .qr-carnet__qr img {
                width: 80px; height: 80px;
            }
            .qr-carnet__qr {
                width: 80px; height: 80px;
            }
        }

        @page {
            margin: 0.8cm;
        }
    </style>
</head>
<body>

    {{-- ── Toolbar ── --}}
    <div class="toolbar">
        <div class="toolbar__left">
            <div class="toolbar__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 015.25 3h2.25M3 18.75A2.25 2.25 0 005.25 21h2.25M18.75 3A2.25 2.25 0 0121 5.25v2.25M18.75 21A2.25 2.25 0 0021 18.75v-2.25M9 9h.008v.008H9V9zm0 6h.008v.008H9V15zm6-6h.008v.008H15V9zm0 6h.008v.008H15V15z"/></svg>
            </div>
            <div>
                <div class="toolbar__title">Carnets QR &mdash; {{ $tipo }}</div>
                <div class="toolbar__sub">R.A.S</div>
            </div>
            <span class="badge-count">{{ $personas->count() }} {{ $personas->count() === 1 ? 'carnet' : 'carnets' }}</span>
        </div>
        <div class="toolbar__right">
            <a href="javascript:history.back()" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Volver
            </a>
            <button class="btn-print" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Imprimir (Ctrl+P)
            </button>
        </div>
    </div>

    {{-- ── Carnets ── --}}
    <div class="page-wrap">
        @if($personas->isEmpty())
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z"/></svg>
                <h3>No hay usuarios registrados</h3>
                <p>No se encontraron {{ strtolower($tipo) }} con datos para generar carnets QR.</p>
            </div>
        @else
            <div class="qr-grid">
                @foreach($personas as $persona)
                    <div class="qr-carnet rol--{{ $persona['rol'] }}">

                        {{-- Cabecera --}}
                        <div class="qr-carnet__header">
                            <div>
                                <div class="qr-carnet__school">R.A.S</div>
                            </div>
                            <span class="qr-carnet__rol-badge">{{ strtoupper($persona['rol']) }}</span>
                        </div>

                        {{-- Cuerpo: QR + Datos --}}
                        <div class="qr-carnet__body">
                            <div class="qr-carnet__qr">
                                @if($persona['qr'])
                                    <img src="{{ $persona['qr'] }}" alt="QR {{ $persona['codigo'] }}">
                                @else
                                    <div class="qr-carnet__qr-placeholder">QR<br>No disp.</div>
                                @endif
                            </div>
                            <div class="qr-carnet__info">
                                <div class="qr-carnet__name">{{ $persona['nombre'] }}</div>
                                @if(!empty($persona['subtitulo']))
                                    <div class="qr-carnet__sub">{{ $persona['subtitulo'] }}</div>
                                @endif
                                <span class="qr-carnet__code">{{ $persona['codigo'] }}</span>
                            </div>
                        </div>

                        {{-- Pie --}}
                        <div class="qr-carnet__footer">
                            Asistencia &bull; {{ now()->format('Y') }} &bull; R.A.S
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</body>
</html>
