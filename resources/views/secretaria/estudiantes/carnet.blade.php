@php
    $codigoEstudiante = trim((string) ($estudiante->codigo ?? ''));
    if ($codigoEstudiante === '' || $codigoEstudiante === '-') {
        $codigoEstudiante = $estudiante->carnet_codigo
            ? trim((string) $estudiante->carnet_codigo)
            : 'ALU-' . str_pad((string) $estudiante->id, 5, '0', STR_PAD_LEFT);
    }
    $nombreCompleto = trim(($estudiante->apellidos ?? '') . ' ' . ($estudiante->nombres ?? ''));
    $grado   = $matricula?->grado?->nombre   ?? '-';
    $seccion = $matricula?->seccion?->nombre ?? '-';
    $anio    = $matricula?->anioEscolar?->nombre ?? date('Y');
@endphp

<div style="padding: 1rem; display:flex; justify-content:center;">
    <div style="
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
        border: 1px solid #e5e7eb;
        width: 340px;
    ">
        {{-- Cabecera --}}
        <div style="
            padding: .65rem 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            border-bottom: 3px solid #213197;
        ">
            <div>
                <div style="font-size:.75rem;font-weight:700;color:#374151;line-height:1.2;">R.A.S</div>
            </div>
            <span style="
                margin-left: auto;
                background: #213197;
                color: #fff;
                font-size: .65rem;
                font-weight: 800;
                padding: .2rem .65rem;
                border-radius: 20px;
                letter-spacing: .04em;
                white-space: nowrap;
            ">ESTUDIANTE</span>
        </div>

        {{-- Cuerpo --}}
        <div style="padding:1rem;display:flex;align-items:center;gap:1rem;">
            <div style="
                flex-shrink:0;
                width:110px;height:110px;
                border:1px solid #e5e7eb;
                border-radius:8px;
                display:flex;align-items:center;justify-content:center;
                background:#fff;
                overflow:hidden;
            ">
                @if ($qr)
                    <img src="{{ $qr }}" alt="QR Carnet" style="width:110px;height:110px;display:block;">
                @else
                    <span style="font-size:.65rem;color:#9ca3af;">Sin QR</span>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.85rem;font-weight:800;color:#111427;line-height:1.25;margin-bottom:.3rem;word-break:break-word;">
                    {{ $nombreCompleto }}
                </div>
                <div style="font-size:.72rem;color:#6b7280;margin-bottom:.2rem;">
                    {{ $grado }}@if($seccion !== '-') &bull; Secc. {{ $seccion }}@endif
                </div>
                <div style="font-size:.68rem;color:#6b7280;margin-bottom:.4rem;">DNI: {{ $estudiante->dni ?? '-' }}</div>
                <span style="
                    font-size:.62rem;
                    font-family:'Courier New',monospace;
                    background:#f3f4f6;
                    color:#374151;
                    padding:.2rem .4rem;
                    border-radius:4px;
                    display:inline-block;
                    word-break:break-all;
                    font-weight:700;
                ">{{ $estudiante->carnet_codigo ?? $codigoEstudiante }}</span>
            </div>
        </div>

        {{-- Pie --}}
        <div style="
            padding:.4rem 1rem;
            background:#f9fafb;
            border-top:1px solid #f3f4f6;
            font-size:.62rem;
            color:#9ca3af;
            text-align:center;
        ">
            Identificación Estudiantil &bull; {{ $anio }} &bull; R.A.S
        </div>
    </div>
</div>
