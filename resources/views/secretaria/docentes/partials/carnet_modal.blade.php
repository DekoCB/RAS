@php
    $anio = now()->year;
    $nombre = trim(($docente->apellidos ?? '') . ' ' . ($docente->nombres ?? '')) ?: '-';
@endphp

<style>
    .jp-carnet2{
        width: 360px;
        margin: 0 auto;
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(229,231,235,.9);
        box-shadow: 0 12px 30px rgba(17, 20, 39, .10);
        overflow: hidden;
        position: relative;
    }

    /* circulo decorativo inferior izq */
    .jp-carnet2::after{
        content: "";
        position: absolute;
        left: -40px;
        bottom: -40px;
        width: 160px;
        height: 160px;
        background: rgba(213, 240, 255, .7); /* beige suave */
        border-radius: 999px;
        z-index: 0;
    }

    .jp-carnet2-inner{
        position: relative;
        z-index: 1;
        padding: 18px 18px 16px;
    }

    .jp-carnet2-top{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap: 12px;
    }

    .jp-carnet2-brand{
        display:flex;
        align-items:center;
        gap: 10px;
        min-width: 0;
    }
    .jp-carnet2-brand img{
        width: 44px;
        height: 44px;
        object-fit: contain;
        flex: 0 0 auto;
    }
    .jp-carnet2-brand .t1{
        font-weight: 700;
        color:#6B7280;
        line-height: 1.1;
    }
    .jp-carnet2-brand .t2{
        color:#9CA3AF;
        font-size: 12px;
        margin-top: 2px;
    }

    .jp-carnet2-chip{
        border: 1px solid #E5E7EB;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        background:#fff;
        box-shadow: 0 8px 18px rgba(17,20,39,.06);
        white-space: nowrap;
    }

    .jp-carnet2-mid{
        margin-top: 18px;
        display:grid;
        grid-template-columns: 64px 1fr;
        gap: 10px;
        align-items:center;
    }

    .jp-carnet2-photo-label{
        font-size: 11px;
        letter-spacing: .12em;
        color:#9CA3AF;
        text-transform: uppercase;
        text-align:center;
    }

    /* placeholder de foto */
    .jp-carnet2-photo{
        width: 56px;
        height: 56px;
        border-radius: 16px;
        border: 1px dashed #E5E7EB;
        background: #F9FAFB;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#9CA3AF;
        margin: 4px auto 0;
        font-size: 11px;
    }

    .jp-carnet2-role{
        font-size: 11px;
        letter-spacing: .12em;
        color:#9CA3AF;
        text-transform: uppercase;
        text-align:center;
        margin-top: 4px;
    }
    .jp-carnet2-name{
        text-align:center;
        font-weight: 700;
        color:#6B7280;
        margin-top: 2px;
        font-size: 16px;
        line-height: 1.2;
        padding: 0 8px;
        overflow-wrap: anywhere; /* evita cortes feos */
    }

    .jp-carnet2-grid2{
        margin-top: 16px;
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        text-align:center;
    }

    .jp-carnet2-field .t{
        font-size: 11px;
        letter-spacing: .12em;
        color:#9CA3AF;
        text-transform: uppercase;
    }
    .jp-carnet2-field .v{
        margin-top: 3px;
        font-weight: 700;
        color:#6B7280;
    }

    .jp-carnet2-codebox{
        margin-top: 14px;
        border: 1px solid rgba(38, 54, 159,.45);
        background: rgba(237, 248, 255,.8);
        border-radius: 14px;
        padding: 10px 12px;
        text-align:center;
    }
    .jp-carnet2-codebox .t{
        font-size: 11px;
        letter-spacing: .12em;
        color:#9CA3AF;
        text-transform: uppercase;
    }
    .jp-carnet2-codebox .v{
        margin-top: 4px;
        font-weight: 800;
        color:#6B7280;
        overflow-wrap: anywhere;
    }

    .jp-carnet2-qrwrap{
        margin-top: 14px;
        display:flex;
        justify-content:center;
    }
    .jp-carnet2-qrbox{
        background:#fff;
        border-radius: 18px;
        box-shadow: 0 12px 26px rgba(17,20,39,.10);
        padding: 14px;
    }

    .jp-carnet2-footer{
        margin-top: 10px;
        text-align:center;
        font-size: 11px;
        letter-spacing: .12em;
        color:#9CA3AF;
        text-transform: uppercase;
    }
    .jp-carnet2-valid{
        margin-top: 4px;
        text-align:center;
        font-size: 12px;
        color:#D1D5DB;
    }
</style>

<div class="jp-carnet2">
    <div class="jp-carnet2-inner">

        <div class="jp-carnet2-top">
            <div class="jp-carnet2-brand">
                <div>
                    <div class="t1">R.A.S</div>
                </div>
            </div>
            <span class="jp-carnet2-chip">Carnet</span>
        </div>

        <div class="jp-carnet2-mid">
            <div>
                <div class="jp-carnet2-photo-label">FOTO</div>
                <div class="jp-carnet2-photo">—</div>
            </div>

            <div>
                <div class="jp-carnet2-role">DOCENTE</div>
                <div class="jp-carnet2-name">{{ $nombre }}</div>
            </div>
        </div>

        <div class="jp-carnet2-grid2">
            <div class="jp-carnet2-field">
                <div class="t">DNI</div>
                <div class="v">{{ $docente->dni ?? '-' }}</div>
            </div>

            <div class="jp-carnet2-field">
                <div class="t">Código</div>
                <div class="v">{{ $docente->codigo ?? '-' }}</div>
            </div>
        </div>

        <div class="jp-carnet2-codebox">
            <div class="t">Código carnet</div>
            <div class="v">{{ $docente->carnet_codigo }}</div>
        </div>

        <div class="jp-carnet2-qrwrap">
            <div class="jp-carnet2-qrbox">
                {!! QrCode::size(170)->margin(1)->generate($docente->carnet_codigo) !!}
            </div>
        </div>

        <div class="jp-carnet2-footer">IDENTIFICACIÓN DOCENTE</div>
        <div class="jp-carnet2-valid">Validez: {{ $anio }}</div>

    </div>
</div>