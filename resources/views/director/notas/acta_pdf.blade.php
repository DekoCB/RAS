<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Cierre de Notas</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; background: #fff; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #1c2a81; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 18px; color: #1c2a81; font-weight: bold; }
        .header h2 { font-size: 14px; color: #333; font-weight: normal; margin-top: 4px; }
        .header .subtitle { font-size: 11px; color: #666; margin-top: 3px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 16px; padding: 12px; background: #f5f8fd; border: 1px solid #dce6f5; border-radius: 6px; }
        .info-item { font-size: 11px; }
        .info-label { font-weight: bold; color: #1c2a81; }

        .section-title { font-size: 13px; font-weight: bold; color: #1c2a81; border-left: 4px solid #1c2a81; padding-left: 8px; margin: 16px 0 8px; }

        .kpi-row { display: flex; gap: 12px; margin-bottom: 16px; }
        .kpi { flex: 1; text-align: center; padding: 8px; border: 1px solid #dce6f5; border-radius: 6px; background: #f8faff; }
        .kpi-num { font-size: 20px; font-weight: bold; }
        .kpi-lbl { font-size: 9px; color: #666; text-transform: uppercase; }
        .kpi.esp  .kpi-num { color: #1c2a81; }
        .kpi.reg  .kpi-num { color: #2e7d32; }
        .kpi.falt .kpi-num { color: #1d2b84; }
        .kpi.pct  .kpi-num { color: #22329c; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        th { background: #1c2a81; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .03em; }
        td { padding: 5px 8px; border-bottom: 1px solid #e3eaf4; }
        tr:nth-child(even) td { background: #f8faff; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 5px; font-size: 9px; font-weight: bold; }
        .badge-verde    { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-amarillo { background: #e1f6ff; color: #22329c; border: 1px solid #b4beff; }
        .badge-rojo     { background: #ebf2ff; color: #1d2b84; border: 1px solid #bcc3ef; }

        .estado-cerrado { background: #e8f5e9; border: 1px solid #a5d6a7; padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-size: 11px; }
        .estado-abierto { background: #e1f6ff; border: 1px solid #b4beff; padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-size: 11px; }

        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #dce6f5; text-align: center; font-size: 10px; color: #888; }
        .firma-box { margin-top: 40px; display: flex; justify-content: space-around; }
        .firma { text-align: center; }
        .firma-line { border-top: 1px solid #333; width: 180px; margin: 0 auto 5px; }
        .firma-name { font-size: 11px; font-weight: bold; }
        .firma-cargo { font-size: 10px; color: #666; }

        @media print {
            body { padding: 10px; }
            .pagebreak { page-break-before: always; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>R.A.S</h1>
        <h2>ACTA DE CIERRE DE NOTAS</h2>
        <div class="subtitle">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="info-grid">
        <div class="info-item"><span class="info-label">Año escolar:</span> {{ $anio?->nombre }}</div>
        <div class="info-item"><span class="info-label">Periodo académico:</span> {{ $periodo?->nombre }}</div>
        <div class="info-item"><span class="info-label">Evaluación:</span> {{ $data['periodo'] }}</div>
        <div class="info-item"><span class="info-label">Alcance:</span> {{ ucfirst($data['alcance']) }}</div>
    </div>

    @if($cierre)
        <div class="estado-cerrado">
            <strong>🔒 ESTADO: CERRADO</strong><br>
            Cerrado el {{ \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') }}
            por <strong>{{ $cierre->cerradoPor?->name ?? 'N/A' }}</strong>
            @if($cierre->es_forzado) — <strong style="color:#213094;">CIERRE FORZADO</strong> @endif
            @if($cierre->motivo) <br>Motivo: {{ $cierre->motivo }} @endif
        </div>
    @else
        <div class="estado-abierto">
            <strong>⚠️ ESTADO: ABIERTO (sin cierre registrado)</strong><br>
            Este reporte es un borrador del estado actual.
        </div>
    @endif

    <div class="kpi-row">
        <div class="kpi esp">
            <div class="kpi-num">{{ $stats['total_esperadas'] }}</div>
            <div class="kpi-lbl">Esperadas</div>
        </div>
        <div class="kpi reg">
            <div class="kpi-num">{{ $stats['total_registradas'] }}</div>
            <div class="kpi-lbl">Registradas</div>
        </div>
        <div class="kpi falt">
            <div class="kpi-num">{{ $stats['total_faltantes'] }}</div>
            <div class="kpi-lbl">Faltantes</div>
        </div>
        <div class="kpi pct">
            <div class="kpi-num">{{ $stats['porcentaje_completitud'] }}%</div>
            <div class="kpi-lbl">Completitud</div>
        </div>
    </div>

    <div class="section-title">Detalle por Sección / Curso</div>
    <table>
        <thead>
            <tr>
                <th>Sección</th>
                <th>Curso</th>
                <th>Docente</th>
                <th style="text-align:center;">Esperadas</th>
                <th style="text-align:center;">Registradas</th>
                <th style="text-align:center;">Faltantes</th>
                <th style="text-align:center;">% Completitud</th>
                <th style="text-align:center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stats['detalle'] as $d)
                <tr>
                    <td>{{ $d['seccion'] }}</td>
                    <td>{{ $d['curso'] }}</td>
                    <td>{{ $d['docente'] }}</td>
                    <td style="text-align:center;">{{ $d['esperadas'] }}</td>
                    <td style="text-align:center;">{{ $d['registradas'] }}</td>
                    <td style="text-align:center;">{{ $d['faltantes'] ?: '—' }}</td>
                    <td style="text-align:center;">{{ $d['porcentaje'] }}%</td>
                    <td style="text-align:center;">
                        <span class="badge badge-{{ $d['estado'] }}">
                            {{ ['verde'=>'Completo','amarillo'=>'Casi listo','rojo'=>'Faltantes'][$d['estado']] ?? $d['estado'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#888;">Sin registros</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($faltantes))
        <div class="section-title" style="color:#1d2b84;border-color:#1d2b84;">Notas Faltantes ({{ count($faltantes) }})</div>
        <table>
            <thead>
                <tr>
                    <th>Sección</th>
                    <th>Curso</th>
                    <th>Alumno</th>
                    <th>Docente responsable</th>
                </tr>
            </thead>
            <tbody>
                @foreach($faltantes as $f)
                    <tr>
                        <td>{{ $f['seccion'] }}</td>
                        <td>{{ $f['curso'] }}</td>
                        <td>{{ $f['estudiante'] }}</td>
                        <td>{{ $f['docente'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="firma-box">
        <div class="firma">
            <div class="firma-line"></div>
            <div class="firma-name">Director(a)</div>
            <div class="firma-cargo">R.A.S</div>
        </div>
        <div class="firma">
            <div class="firma-line"></div>
            <div class="firma-name">Secretaría Académica</div>
            <div class="firma-cargo">R.A.S</div>
        </div>
    </div>

    <div class="footer">
        R.A.S &mdash; Acta generada automáticamente &mdash; {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
