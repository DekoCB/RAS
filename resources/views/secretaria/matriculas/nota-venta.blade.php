@php
    $numeroNota  = str_pad($matricula->id, 4, '0', STR_PAD_LEFT);
    $fechaEmision = $matricula->fecha_matricula
        ? \Carbon\Carbon::parse($matricula->fecha_matricula)->format('d/m/Y')
        : now()->format('d/m/Y');

    $nombreAlumno   = trim(($matricula->estudiante?->nombres ?? '') . ' ' . ($matricula->estudiante?->apellidos ?? ''));
    $nombreAlumno   = $nombreAlumno ?: '—';
    $nombreApoderado = trim(($apoderado?->apellidos ?? '') . ' ' . ($apoderado?->nombres ?? ''));
    $nombreApoderado = $nombreApoderado ?: '—';

    $gradoSeccion  = ($matricula->grado?->nombre ?? '-') . ' — ' . ($matricula->seccion?->nombre ?? '-');
    $periodoLabel  = $matricula->periodoAcademico?->codigo ?? $matricula->anioEscolar?->nombre ?? date('Y');
    if ($matricula->periodoAcademico?->nombre) {
        $periodoLabel .= ' — ' . $matricula->periodoAcademico->nombre;
    }

    // ── WhatsApp ───────────────────────────────────────────
    $telRaw    = $apoderado?->telefono ?? '';
    $telLimpio = preg_replace('/\D/', '', $telRaw);
    $telWa     = strlen($telLimpio) === 9 ? '51' . $telLimpio : $telLimpio;

    $waMsg  = "🧾 *Nota de Venta N° {$numeroNota} - R.A.S*\n\n";
    $waMsg .= "👤 *Alumno:* {$nombreAlumno}\n";
    $waMsg .= "🎓 *Grado y Sección:* {$gradoSeccion}\n";
    $waMsg .= "📅 *Periodo:* {$periodoLabel}\n";
    $waMsg .= "📆 *Fecha:* {$fechaEmision}\n\n";
    $waMsg .= "📋 *Detalle de Pago:*\n";
    foreach ($deudasMatricula as $deuda) {
        $nota = ($deuda->concepto?->tipo === 'gastos_internos' && $matricula->descuentos->where('tipo','hermano_menor')->isNotEmpty())
            ? ' (dto. 5% hijo menor)' : '';
        $waMsg .= "   • " . ($deuda->concepto?->nombre ?? $deuda->periodo) . $nota . ": S/ " . number_format((float)$deuda->monto, 2) . "\n";
    }
    foreach ($matricula->cargos as $cargo) {
        $waMsg .= "   • " . $cargo->concepto . ": S/ " . number_format((float)$cargo->subtotal, 2) . "\n";
    }
    $descuentosWa = $matricula->descuentos->where('tipo', '!=', 'hermano_menor');
    if ($descuentosWa->isNotEmpty()) {
        $waMsg .= "\n🏷 *Descuentos:*\n";
        foreach ($descuentosWa as $d) {
            $waMsg .= "   • " . ($d->descripcion ?? $d->tipo) . ": -S/ " . number_format((float)$d->monto, 2) . "\n";
        }
    }
    $waMsg .= "\n💰 *Total: S/ " . number_format($totalFinal, 2) . "*\n";
    if ($pago) {
        $waMsg .= "💳 *Método de pago:* " . ($pago->metodo ?? '-') . "\n";
    }
    $waMsg .= "\nAnte cualquier consulta comuníquese con secretaría.\n📍 *R.A.S*";

    $waLink        = 'https://wa.me/' . $telWa . '?text=' . rawurlencode($waMsg);
    $tieneTelefono = strlen($telWa) >= 9;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Venta N° {{ $numeroNota }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            background: #eef0f3;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.75rem 1rem 3rem;
        }

        /* ── Barra de herramientas ─────────────────────────── */
        .toolbar {
            width: 100%;
            max-width: 820px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .6rem;
            margin-bottom: .85rem;
        }

        .toolbar__info {
            font-size: .82rem;
            color: #64748b;
            font-weight: 600;
        }

        .toolbar__btns { display: flex; gap: .5rem; flex-wrap: wrap; }

        .tbtn {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            padding: .42rem .9rem;
            border-radius: 8px;
            font-size: .79rem;
            font-weight: 700;
            text-decoration: none;
            border: 0;
            cursor: pointer;
            transition: all .15s ease;
            white-space: nowrap;
        }
        .tbtn svg { width: 14px; height: 14px; }

        .tbtn--back  { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
        .tbtn--back:hover { background:#e2e8f0; }

        .tbtn--print { background:#1e293b; color:#fff; }
        .tbtn--print:hover { background:#0f132a; color:#fff; }

        .tbtn--img   { background:#475569; color:#fff; }
        .tbtn--img:hover { background:#334155; color:#fff; }
        .tbtn--img.loading { opacity:.7; cursor:wait; }

        .tbtn--wa    { background:#25d366; color:#fff; }
        .tbtn--wa:hover { background:#1ebe5d; color:#fff; }
        .tbtn--wa--off { background:#e2e8f0; color:#94a3b8; cursor:not-allowed; }

        /* ── Tip ──────────────────────────────────────────── */
        .wa-hint {
            width:100%; max-width:820px;
            background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;
            padding:.45rem .9rem;
            font-size:.75rem; color:#15803d; font-weight:600;
            margin-bottom:.65rem;
            display:flex; align-items:center; gap:.4rem;
        }
        .wa-hint svg { width:13px; height:13px; flex-shrink:0; }

        /* ── Documento ────────────────────────────────────── */
        .doc {
            width: 100%;
            max-width: 820px;
            background: #fff;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 20px rgba(0,0,0,.10);
        }

        /* Franja superior */
        .doc__topbar {
            height: 5px;
            background: #1e293b;
        }

        /* Cabecera: logo + título */
        .doc__head {
            display: grid;
            grid-template-columns: 160px 1fr;
            border-bottom: 1.5px solid #e5e7eb;
        }

        .doc__logo-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            border-right: 1px solid #e5e7eb;
        }

        .doc__logo-placeholder {
            width: 90px; height: 64px;
            border: 1.5px dashed #d1d5db;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: .68rem;
            text-align: center;
        }

        .doc__title-cell {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            padding: 1.2rem 1.4rem;
            gap: .35rem;
        }

        .doc__title-cell h1 {
            font-size: 1.55rem;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #1e293b;
        }

        .doc__nota-num {
            font-size: .92rem;
            font-weight: 700;
            color: #475569;
            letter-spacing: .04em;
        }

        .doc__nota-num span {
            display: inline-block;
            background: #1e293b;
            color: #fff;
            padding: .2rem .65rem;
            border-radius: 4px;
            font-weight: 800;
            font-size: .88rem;
            letter-spacing: .06em;
            margin-left: .3rem;
        }

        /* Datos empresa */
        .doc__empresa {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .doc__empresa-block {
            padding: .65rem 1.2rem;
            font-size: .8rem;
        }

        .doc__empresa-block + .doc__empresa-block {
            border-left: 1px solid #e5e7eb;
        }

        .doc__empresa-row {
            display: flex;
            gap: .4rem;
            padding: .12rem 0;
            line-height: 1.5;
        }

        .doc__empresa-row .lbl {
            color: #6b7280;
            font-weight: 700;
            white-space: nowrap;
            min-width: 60px;
        }

        .doc__empresa-row .val {
            color: #1e293b;
        }

        /* Sección: etiqueta */
        .doc__section {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .42rem 1.2rem;
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .doc__section-text {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #374151;
        }

        .doc__section-line {
            flex: 1;
            height: 1px;
            background: #d1d5db;
        }

        /* Datos cliente */
        .doc__cliente {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #e5e7eb;
        }

        .doc__cliente-block {
            padding: .75rem 1.2rem;
            font-size: .8rem;
        }

        .doc__cliente-block + .doc__cliente-block {
            border-left: 1px solid #e5e7eb;
        }

        /* Tabla */
        .doc__table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }

        .doc__table thead tr {
            background: #1e293b;
            color: #fff;
        }

        .doc__table thead th {
            padding: .55rem 1.2rem;
            font-weight: 700;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            text-align: left;
        }
        .doc__table thead th.r { text-align: right; }
        .doc__table thead th.c { text-align: center; }

        .doc__table tbody td {
            padding: .55rem 1.2rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }
        .doc__table tbody td.r { text-align: right; }
        .doc__table tbody td.c { text-align: center; }
        .doc__table tbody td.fw { font-weight: 700; }

        .doc__table tbody tr:last-child td { border-bottom: 0; }
        .doc__table .empty td {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 1.5rem;
        }

        /* Descuentos */
        .doc__descuentos {
            border-top: 1px dashed #d1d5db;
        }

        .doc__descuentos .doc__table tbody td { color: #15803d; }

        /* Totales */
        .doc__totals {
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid #e5e7eb;
        }

        .doc__totals-inner {
            width: 260px;
            padding: .7rem 1.2rem;
        }

        .doc__total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .2rem 0;
            font-size: .8rem;
            color: #6b7280;
        }

        .doc__total-row.desc { color: #16a34a; }

        .doc__total-row.final {
            border-top: 2px solid #1e293b;
            margin-top: .45rem;
            padding-top: .5rem;
            font-size: 1rem;
            font-weight: 900;
            color: #1e293b;
        }

        /* Footer */
        .doc__footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1.5px solid #1e293b;
            background: #f9fafb;
            font-size: .79rem;
        }

        .doc__footer-block {
            padding: .65rem 1.2rem;
        }

        .doc__footer-block + .doc__footer-block {
            border-left: 1px solid #e5e7eb;
        }

        .doc__footer-block .lbl {
            font-weight: 700;
            color: #374151;
        }

        /* Firma */
        .doc__firma {
            display: flex;
            justify-content: flex-end;
            padding: 1.75rem 1.4rem .9rem;
        }

        .doc__firma-box {
            text-align: center;
            width: 180px;
        }

        .doc__firma-line {
            border-top: 1px solid #9ca3af;
            margin-bottom: .35rem;
        }

        .doc__firma-label {
            font-size: .7rem;
            color: #9ca3af;
            letter-spacing: .03em;
        }

        /* ── Print ────────────────────────────────────────── */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar, .wa-hint { display: none !important; }
            .doc { box-shadow: none; border: none; max-width: 100%; }
            .doc__topbar { display: none; }
            .doc__table tbody tr:last-child td { border-bottom: 1px solid #f3f4f6; }
        }
    </style>
</head>
<body>

    {{-- Banner: matrícula recién finalizada --}}
    @if(session('matricula_recien_finalizada'))
    <div style="width:100%;max-width:820px;margin-bottom:.8rem;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:.75rem 1.1rem;display:flex;align-items:center;justify-content:space-between;gap:.8rem;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="width:22px;height:22px;flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <div>
                <div style="font-weight:800;color:#15803d;font-size:.95rem;">¡Matrícula registrada correctamente!</div>
                <div style="font-size:.78rem;color:#166534;">A continuación puedes imprimir o descargar el comprobante.</div>
            </div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <button onclick="window.print()" style="padding:.38rem .9rem;background:#16a34a;color:#fff;border:none;border-radius:7px;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:.35rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir / PDF
            </button>
            <a href="{{ route(request()->user()?->hasRole('SuperAdmin') ? 'admin-sistema.matriculas.index' : 'secretaria.matriculas.index') }}"
               style="padding:.38rem .9rem;background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:7px;font-weight:700;font-size:.8rem;text-decoration:none;display:flex;align-items:center;gap:.35rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="15 18 9 12 15 6"/></svg>
                Ir a Matrículas
            </a>
        </div>
    </div>
    @endif

    {{-- Tip --}}
    <div class="wa-hint">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Para enviar por WhatsApp: descarga la imagen y adjúntala en el chat del apoderado.
    </div>

    {{-- Toolbar --}}
    <div class="toolbar">
        <div class="toolbar__info">Nota de Venta N° {{ $numeroNota }} &nbsp;·&nbsp; {{ $fechaEmision }}</div>
        <div class="toolbar__btns">
            <a href="javascript:history.back()" class="tbtn tbtn--back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Volver
            </a>
            <button type="button" class="tbtn tbtn--print" onclick="window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Imprimir / PDF
            </button>
            <button type="button" id="btn-img" class="tbtn tbtn--img">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Descargar imagen
            </button>
            @if ($tieneTelefono)
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="tbtn tbtn--wa">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
            @else
                <button class="tbtn tbtn--wa tbtn--wa--off" disabled title="Sin teléfono registrado">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Sin teléfono
                </button>
            @endif
        </div>
    </div>

    {{-- Documento ─────────────────────────────────────────────── --}}
    <div class="doc" id="doc-nota">

        <div class="doc__topbar"></div>

        {{-- Cabecera --}}
        <div class="doc__head">
            <div class="doc__logo-cell">
                <div class="doc__logo-placeholder">Logo<br>Institución</div>
            </div>
            <div class="doc__title-cell">
                <h1>Nota de Venta</h1>
                <div class="doc__nota-num">N° <span>{{ $numeroNota }}</span></div>
            </div>
        </div>

        {{-- Datos institución + fecha --}}
        <div class="doc__empresa">
            <div class="doc__empresa-block">
                <div class="doc__empresa-row"><span class="lbl">Institución:</span><span class="val">R.A.S</span></div>
                <div class="doc__empresa-row"><span class="lbl">Correo:</span><span class="val">secretaria@ras.edu.pe</span></div>
                <div class="doc__empresa-row"><span class="lbl">RUC:</span><span class="val">—</span></div>
            </div>
            <div class="doc__empresa-block">
                <div class="doc__empresa-row"><span class="lbl">Fecha:</span><span class="val">{{ $fechaEmision }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">Periodo:</span><span class="val">{{ $periodoLabel }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">Estado:</span><span class="val">{{ $matricula->estado ?? '—' }}</span></div>
            </div>
        </div>

        {{-- Datos del cliente --}}
        <div class="doc__section">
            <span class="doc__section-text">Datos del Cliente</span>
            <div class="doc__section-line"></div>
        </div>
        <div class="doc__cliente">
            <div class="doc__cliente-block">
                <div class="doc__empresa-row"><span class="lbl">Apoderado:</span><span class="val">{{ $nombreApoderado }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">DNI:</span><span class="val">{{ $apoderado?->dni ?? '—' }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">Teléfono:</span><span class="val">{{ $apoderado?->telefono ?? '—' }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">Email:</span><span class="val">{{ $apoderado?->email ?? '—' }}</span></div>
            </div>
            <div class="doc__cliente-block">
                <div class="doc__empresa-row"><span class="lbl">Alumno:</span><span class="val">{{ $nombreAlumno }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">DNI:</span><span class="val">{{ $matricula->estudiante?->dni ?? '—' }}</span></div>
                <div class="doc__empresa-row"><span class="lbl">Grado:</span><span class="val">{{ $gradoSeccion }}</span></div>
            </div>
        </div>

        {{-- Detalle de pago --}}
        <div class="doc__section">
            <span class="doc__section-text">Detalle de Pago</span>
            <div class="doc__section-line"></div>
        </div>
        <table class="doc__table">
            <thead>
                <tr>
                    <th>Descripción / Concepto</th>
                    <th class="c" style="width:75px;">Cant.</th>
                    <th class="r" style="width:115px;">Precio Unit.</th>
                    <th class="r" style="width:115px;">Importe</th>
                </tr>
            </thead>
            <tbody>
                {{-- Conceptos fijos: Matrícula y Matrícula (Gastos Internos) --}}
                @foreach ($deudasMatricula as $deuda)
                    @php
                        $esGastos  = $deuda->concepto?->tipo === 'gastos_internos';
                        $tieneDesc = $esGastos && $matricula->descuentos->where('tipo', 'hermano_menor')->isNotEmpty();
                    @endphp
                    <tr>
                        <td>
                            {{ $deuda->concepto?->nombre ?? $deuda->periodo }}
                            @if ($tieneDesc)
                                <span style="font-size:.7rem;color:#15803d;font-weight:700;margin-left:.4rem;">✓ Dto. 5% hijo menor</span>
                            @endif
                        </td>
                        <td class="c">1</td>
                        <td class="r">S/ {{ number_format((float)$deuda->monto, 2) }}</td>
                        <td class="r fw">S/ {{ number_format((float)$deuda->monto, 2) }}</td>
                    </tr>
                @endforeach

                {{-- Cargos extras: Libros, Uniformes, etc. --}}
                @foreach ($matricula->cargos as $cargo)
                    <tr>
                        <td>{{ $cargo->concepto }}</td>
                        <td class="c">{{ $cargo->cantidad }}</td>
                        <td class="r">S/ {{ number_format((float)$cargo->precio_unitario, 2) }}</td>
                        <td class="r fw">S/ {{ number_format((float)$cargo->subtotal, 2) }}</td>
                    </tr>
                @endforeach

                @if ($deudasMatricula->isEmpty() && $matricula->cargos->isEmpty())
                    <tr class="empty"><td colspan="4">Sin cargos registrados.</td></tr>
                @endif
            </tbody>
        </table>

        @php
            // hermano_menor ya está baked-in en el monto de la deuda → no mostrar como descuento aparte
            $descuentosVisibles = $matricula->descuentos->where('tipo', '!=', 'hermano_menor');
        @endphp
        @if ($descuentosVisibles->isNotEmpty())
            <div class="doc__descuentos">
                <div class="doc__section" style="margin-top:0; border-top:0;">
                    <span class="doc__section-text" style="color:#15803d;">Descuentos</span>
                    <div class="doc__section-line"></div>
                </div>
                <table class="doc__table">
                    <tbody>
                        @foreach ($descuentosVisibles as $d)
                            <tr>
                                <td>{{ $d->descripcion ?? $d->tipo }}</td>
                                <td class="c">—</td>
                                <td class="r">—</td>
                                <td class="r fw" style="color:#15803d;">- S/ {{ number_format((float)$d->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Totales --}}
        <div class="doc__totals">
            <div class="doc__totals-inner">
                <div class="doc__total-row">
                    <span>Subtotal</span>
                    <span>S/ {{ number_format($totalCargos, 2) }}</span>
                </div>
                @if ($totalDescuentos > 0)
                    <div class="doc__total-row desc">
                        <span>Descuentos</span>
                        <span>— S/ {{ number_format($totalDescuentos, 2) }}</span>
                    </div>
                @endif
                <div class="doc__total-row final">
                    <span>TOTAL</span>
                    <span>S/ {{ number_format($totalFinal, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="doc__footer">
            <div class="doc__footer-block">
                <span class="lbl">Forma de pago: </span>
                @if($pago)
                    @php
                        $decoded = json_decode($pago->metodo, true);
                        if (is_array($decoded)) {
                            $metodoDisplay = collect($decoded)->map(fn($m) => $m['metodo'] . ' S/' . number_format($m['monto'],2))->implode(' + ');
                        } else {
                            $metodoDisplay = $pago->metodo ?? 'No especificado';
                        }
                    @endphp
                    {{ $metodoDisplay }}
                @else
                    No especificado
                @endif
            </div>
            <div class="doc__footer-block">
                <span class="lbl">Fecha de pago: </span>{{ $pago?->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : '—' }}
            </div>
        </div>

        {{-- Firma --}}
        <div class="doc__firma">
            <div class="doc__firma-box">
                <div class="doc__firma-line"></div>
                <div class="doc__firma-label">Firma y sello — Secretaría</div>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
            integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        @if(session('matricula_recien_finalizada'))
        // Auto-imprimir al cargar si viene del wizard
        window.addEventListener('load', () => setTimeout(() => window.print(), 600));
        @endif

        document.getElementById('btn-img')?.addEventListener('click', function () {
            const btn  = this;
            const doc  = document.getElementById('doc-nota');
            if (!doc) return;

            btn.classList.add('loading');
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Generando...`;

            html2canvas(doc, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
            }).then(function (canvas) {
                const link = document.createElement('a');
                link.download = 'nota-venta-{{ $numeroNota }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(function () {
                alert('No se pudo generar la imagen. Use "Imprimir / PDF" como alternativa.');
            }).finally(function () {
                btn.classList.remove('loading');
                btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg> Descargar imagen`;
            });
        });
    </script>

</body>
</html>
