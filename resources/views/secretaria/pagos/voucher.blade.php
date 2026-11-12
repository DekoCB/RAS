<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher de Pago - {{ $pago->id }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm 297mm;
            /* Fija un tamaño vertical largo para que no corte, o 'auto' si el navegador lo soporta */
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0 auto;
            /* Centrar en la hoja */
            padding: 2px;
            width: 76mm;
            /* Ajustar casi al borde (80mm - margenes minimos) */
            max-width: 76mm;
            color: #000;
        }

        @media print {
            body {
                width: 76mm;
                max-width: 76mm;
                margin: 0 auto;
            }

            .no-print {
                display: none;
            }
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .logo {
            /* El escudo es un SVG cuadrado sin ancho intrinseco: se fijan las
               dos medidas para que no dependa del navegador al imprimir. */
            width: 60px;
            height: 60px;
            object-fit: contain;
            display: block;
            margin: 0 auto 5px;
            filter: grayscale(100%);
            /* La impresora de tickets es monocroma */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .school-name {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .total-row {
            font-size: 14px;
            margin-top: 5px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

@php
    $rp = auth()->check() && auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
@endphp
@php
    $afterPrintUrl = session('voucher_after_print_url') ?? route($rp . 'pagos.create');
    $desdeWizard   = session()->has('voucher_after_print_url');
@endphp
<body onload="window.print()">

    <script>
        window.onafterprint = function () {
            window.location.href = '{{ $afterPrintUrl }}';
        };
    </script>

    @if($desdeWizard)
    <div class="no-print" style="text-align:center;margin:8px 0 4px;font-size:11px;font-family:Arial,sans-serif;">
        <a href="{{ $afterPrintUrl }}"
           style="display:inline-block;padding:5px 14px;background:#1e293b;color:#fff;border-radius:6px;text-decoration:none;font-weight:700;font-size:11px;">
            ← Ir a Matrículas
        </a>
    </div>
    @endif

    <div class="header text-center">
        <x-brand-logo class="logo" alt="R.A.S" />
        <div class="school-name">R.A.S</div>
        <div>RUC: 20123456789</div>
        <div>Telf: (063) 123-456</div>
    </div>

    <div class="text-center bold uppercase" style="margin: 10px 0;">
        Comprobante de Pago
    </div>

    <div class="info-group">
        <div class="info-row">
            <span>Ticket:</span>
            <span class="bold">#{{ str_pad($pago->id, 8, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span>Fecha:</span>
            <span>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Cajero:</span>
            <span class="uppercase">{{ $pago->registradoPor->name ?? 'Sistema' }}</span>
        </div>
    </div>

    <div class="separator"></div>

    @php
        $deudasVoucher = $pago->deudas
            ->sortBy(function ($deudaItem) {
                $fecha = $deudaItem->fecha_vencimiento ? (string) $deudaItem->fecha_vencimiento : '0000-00-00';
                $flagSinVencimiento = $deudaItem->fecha_vencimiento ? 1 : 0;

                return sprintf('%d-%s-%010d', $flagSinVencimiento, $fecha, (int) $deudaItem->id);
            })
            ->values();

        $totalPagadoReal = (float) $deudasVoucher->sum(function ($deudaItem) {
            return (float) ($deudaItem->pivot->monto_aplicado ?? 0);
        });

        // Obtener estudiante desde la primera deuda asociada al pago
        $estudiante = null;
        $gradoSeccion = '';
        $deuda = $deudasVoucher->first();
        if ($deuda && $deuda->matricula) {
            $estudiante = $deuda->matricula->estudiante;
            if ($deuda->matricula->grado) {
                $gradoSeccion = $deuda->matricula->grado->nombre . ' - ' . ($deuda->matricula->seccion->nombre ?? '?');
            }
        }
    @endphp

    @if($estudiante)
        <div class="info-group">
            <div class="bold">Alumno:</div>
            <div class="uppercase">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</div>
            <div class="info-row">
                <span>DNI: {{ $estudiante->dni }}</span>
            </div>
            @if($gradoSeccion)
                <div class="info-row">
                    <span>Grado: {{ $gradoSeccion }}</span>
                </div>
            @endif
        </div>
        <div class="separator"></div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;">Cant</th>
                <th style="width: 65%;">Descripcion</th>
                <th class="text-right" style="width: 25%;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deudasVoucher as $deudaItem)
                @php
                    $descripcion = trim((string) ($deudaItem->periodo ?? ''));
                    if ($descripcion === '') {
                        $descripcion = $deudaItem->concepto->nombre ?? 'Pago';
                    }
                    $conceptoNombre = $deudaItem->concepto->nombre ?? 'Pago';
                @endphp
                <tr>
                    <td>1</td>
                    <td>
                        <div class="uppercase">{{ $descripcion }}</div>
                        @if(strtolower($descripcion) !== strtolower($conceptoNombre))
                            <div style="font-size: 10px; color: #555;">{{ $conceptoNombre }}</div>
                        @endif
                    </td>
                    <td class="text-right">S/ {{ number_format((float) ($deudaItem->pivot->monto_aplicado ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>

    <div class="info-row total-row bold">
        <span>TOTAL PAGADO:</span>
        <span>S/ {{ number_format($totalPagadoReal > 0 ? $totalPagadoReal : (float) $pago->monto, 2) }}</span>
    </div>

    @php
        $metodoLabels = [
            'Efectivo'      => 'Efectivo',
            'Yape'          => 'Yape',
            'Plin'          => 'Plin',
            'Transferencia' => 'Tarjeta BBVA',
            'Tarjeta'       => 'Tarjeta BCP',
            'BancoNacion'   => 'Banco de la Nación',
            'Otro'          => 'Otro',
        ];
        $metodos = $pago->metodos_decoded;
        $displayResumen = collect($metodos)->map(fn($m) => $metodoLabels[$m['metodo']] ?? $m['metodo'])->implode(' + ');
    @endphp
    <div class="info-row" style="margin-top: 5px;">
        <span>Metodo Pago:</span>
        <span class="uppercase">{{ $displayResumen ?: $pago->metodo_display }}</span>
    </div>
    @foreach($metodos as $met)
    <div class="info-row" style="font-size:11px;">
        <span>{{ $metodoLabels[$met['metodo']] ?? $met['metodo'] }}:</span>
        <span>S/ {{ number_format($met['monto'], 2) }}{{ !empty($met['op']) ? ' · Op. '.$met['op'] : '' }}</span>
    </div>
    @endforeach

    <div class="footer">
        <div>¡Gracias por su pago!</div>
        <div style="margin-top: 5px;">Valores: Estudio - Trabajo - Disciplina</div>
        <div class="separator"></div>
        <div>* NO SE ACEPTAN DEVOLUCIONES *</div>
        <div>Copia Estudiante</div>
    </div>

</body>

</html>
