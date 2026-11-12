<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Deuda;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Incidencia;
use App\Models\InventarioVenta;
use App\Models\InventarioVentaDetalle;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteGlobalController extends Controller
{
    private const MESES = [
        1 => ['nombre' => 'ENERO', 'abreviatura' => 'ENE'],
        2 => ['nombre' => 'FEBRERO', 'abreviatura' => 'FEB'],
        3 => ['nombre' => 'MARZO', 'abreviatura' => 'MAR'],
        4 => ['nombre' => 'ABRIL', 'abreviatura' => 'ABR'],
        5 => ['nombre' => 'MAYO', 'abreviatura' => 'MAY'],
        6 => ['nombre' => 'JUNIO', 'abreviatura' => 'JUN'],
        7 => ['nombre' => 'JULIO', 'abreviatura' => 'JUL'],
        8 => ['nombre' => 'AGOSTO', 'abreviatura' => 'AGO'],
        9 => ['nombre' => 'SEPTIEMBRE', 'abreviatura' => 'SET'],
        10 => ['nombre' => 'OCTUBRE', 'abreviatura' => 'OCT'],
        11 => ['nombre' => 'NOVIEMBRE', 'abreviatura' => 'NOV'],
        12 => ['nombre' => 'DICIEMBRE', 'abreviatura' => 'DIC'],
    ];

    private const TIPOS_REPORTE_VALIDOS = ['general', 'periodo', 'rango'];

    private const CELL_COLORS = [
        'verde' => ['fill' => 'C6EFCE', 'font' => '375623'],
        'ambar' => ['fill' => 'FFEB9C', 'font' => '9C6500'],
        'rojo'  => ['fill' => 'FFC7CE', 'font' => '9C0006'],
    ];

    private const COLUMNA_MATRICULA = 'MATRICULA';
    private const COLUMNA_OBS_MATRICULA = 'OBS.';

    public function index(Request $request)
    {
        return view('dashboards.admin', $this->buildReportesData($request));
    }

    public function reportes(Request $request)
    {
        return view('admin.reportes_globales.index', $this->buildReportesData($request));
    }

    public function exportarPagosExcel(Request $request)
    {
        [, $anio] = $this->resolveAnio($request);
        if (!$anio) {
            return redirect()
                ->route('admin-sistema.reportes.index')
                ->with('error', 'No hay anio escolar disponible para exportar.');
        }

        $filtros = $this->normalizarFiltrosExcel($request, (int) $anio->id);
        $dataset = $this->construirDatasetExcelPagos($anio, $filtros);

        $spreadsheet = new Spreadsheet();
        $hojas = $dataset['hojas'];
        $titulosUsados = [];

        if (empty($hojas)) {
            $hojas = [[
                'titulo' => 'Sin datos',
                'filas' => [],
                'seccion_label' => 'Sin seccion',
            ]];
        }

        foreach (array_values($hojas) as $indice => $hojaData) {
            $sheet = $indice === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $tituloHoja = $this->normalizarTituloHoja((string) ($hojaData['titulo'] ?? 'Reporte'), $titulosUsados);
            $titulosUsados[] = $tituloHoja;
            $sheet->setTitle($tituloHoja);
            $sheet->getSheetView()->setShowZeros(true);

            $headers = array_merge(['Nro', 'DNI', 'Alumno'], $dataset['columnas']);
            $columnCount = count($headers);
            $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

            $sheet->setCellValue('A1', 'RELACION DE PAGOS - ' . ($anio->nombre ?? date('Y')));
            $sheet->mergeCells("A1:{$lastColumn}1");
            $sheet->setCellValue('A2', 'Seccion: ' . (string) ($hojaData['seccion_label'] ?? 'General'));
            $sheet->mergeCells("A2:{$lastColumn}2");
            $sheet->setCellValue('A3', 'Filtro: ' . $dataset['filtro_label']);
            $sheet->mergeCells("A3:{$lastColumn}3");

            $sheet->fromArray($headers, null, 'A5');
            $filas        = $hojaData['filas'] ?? [];
            $filasColores = $hojaData['filas_colores'] ?? [];
            if (!empty($filas)) {
                $sheet->fromArray($filas, null, 'A6');
            }

            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'A00000'],
                ],
            ]);

            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);

            $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);

            $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F2F2F2'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                ],
            ]);

            $dataStart = 6;
            $dataEnd = 5 + count($filas);
            if ($dataEnd >= $dataStart) {
                for ($i = 4; $i <= $columnCount; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $header = (string) ($headers[$i - 1] ?? '');
                    if ($this->esColumnaTextoReporte($header)) {
                        for ($row = $dataStart; $row <= $dataEnd; $row++) {
                            $cellRef = "{$col}{$row}";
                            $value = $sheet->getCell($cellRef)->getValue();
                            $sheet->setCellValueExplicit($cellRef, $value === null ? '' : (string) $value, DataType::TYPE_STRING);
                            if ($header === self::COLUMNA_OBS_MATRICULA) {
                                $sheet->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            }
                        }
                        continue;
                    }

                    for ($row = $dataStart; $row <= $dataEnd; $row++) {
                        $cellRef = "{$col}{$row}";
                        $value = $sheet->getCell($cellRef)->getValue();
                        if ($value === null || $value === '') {
                            $sheet->setCellValueExplicit($cellRef, 0, DataType::TYPE_NUMERIC);
                        }
                    }
                }

                $sheet->getStyle("A{$dataStart}:{$lastColumn}{$dataEnd}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                for ($i = 4; $i <= $columnCount; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $header = (string) ($headers[$i - 1] ?? '');
                    if ($this->esColumnaTextoReporte($header)) {
                        continue;
                    }
                    $sheet->getStyle("{$col}{$dataStart}:{$col}{$dataEnd}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // Apply cell colors (verde/ambar/rojo)
                foreach ($filasColores as $rowIdx => $colores) {
                    $excelRow = $dataStart + $rowIdx;
                    foreach ($colores as $colIdx => $color) {
                        if ($color === null || !isset(self::CELL_COLORS[$color])) continue;
                        $col     = Coordinate::stringFromColumnIndex($colIdx + 4);
                        $cellRef = "{$col}{$excelRow}";
                        $sheet->getStyle($cellRef)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::CELL_COLORS[$color]['fill']]],
                            'font' => ['color' => ['rgb' => self::CELL_COLORS[$color]['font']]],
                        ]);
                    }
                }
            }

            $sheet->setAutoFilter("A5:{$lastColumn}5");
            $sheet->freezePane('D6');

            $this->ajustarAnchosColumnasReporte($sheet, $headers, $columnCount);
            $sheet->getRowDimension(1)->setRowHeight(26);
            $sheet->getRowDimension(5)->setRowHeight(24);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'REPORTE_GENERAL_PAGOS_' . now()->format('d-m-Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportarPagosExcelUnica(Request $request)
    {
        [, $anio] = $this->resolveAnio($request);
        if (!$anio) {
            return redirect()
                ->route('admin-sistema.reportes.index')
                ->with('error', 'No hay anio escolar disponible para exportar.');
        }

        $filtros = $this->normalizarFiltrosExcel($request, (int) $anio->id);
        $dataset = $this->construirDatasetExcelPagos($anio, $filtros);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Todos los alumnos');
        $sheet->getSheetView()->setShowZeros(true);

        // Cabeceras: Nro, DNI, Alumno + columnas de pago (sin Sección: los separadores la reemplazan)
        $headers = array_merge(['Nro', 'DNI', 'Alumno'], $dataset['columnas']);
        $columnCount = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

        // Título
        $sheet->setCellValue('A1', 'RELACION DE PAGOS - ' . ($anio->nombre ?? date('Y')));
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A2', 'Todos los alumnos — Hoja única');
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A3', 'Filtro: ' . $dataset['filtro_label']);
        $sheet->mergeCells("A3:{$lastColumn}3");

        // Cabeceras columnas en fila 5
        $sheet->fromArray($headers, null, 'A5');

        // Ordenar secciones por nivel (Inicial → Primaria → Secundaria), grado y sección
        $hojas = $dataset['hojas'];
        usort($hojas, fn ($a, $b) => strcmp(
            (string) ($a['sort_key'] ?? '9-999-Z'),
            (string) ($b['sort_key'] ?? '9-999-Z')
        ));

        // ── Estilos fila título ──────────────────────────────────────────
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'A00000']],
        ]);
        $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Estilos fila cabecera ────────────────────────────────────────
        $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Escribir filas con separadores por sección ───────────────────
        $currentRow = 6;
        $contador   = 1;

        foreach ($hojas as $hojaData) {
            $seccionLabel = (string) ($hojaData['seccion_label'] ?? 'Sin sección');
            $filas        = $hojaData['filas'] ?? [];
            $filasColores = $hojaData['filas_colores'] ?? [];
            if (empty($filas)) {
                continue;
            }

            // Fila separadora de sección (encabezado oscuro)
            $textoSep = '  ' . strtoupper($seccionLabel) . '   (' . count($filas) . ' alumno' . (count($filas) !== 1 ? 's' : '') . ')';
            $sheet->setCellValue("A{$currentRow}", $textoSep);
            $sheet->mergeCells("A{$currentRow}:{$lastColumn}{$currentRow}");
            $sheet->getStyle("A{$currentRow}:{$lastColumn}{$currentRow}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;

            // Filas de alumnos de esa sección
            foreach ($filas as $filaIdx => $fila) {
                // fila = [nro_seccion, dni, alumno, ...valores_pago]
                $rowData = array_merge([$contador, $fila[1], $fila[2]], array_slice($fila, 3));
                $sheet->fromArray($rowData, null, "A{$currentRow}");

                // DNI como texto para preservar ceros a la izquierda
                $sheet->setCellValueExplicit("B{$currentRow}", (string) $fila[1], DataType::TYPE_STRING);

                // Celdas de pago (col D en adelante): rellenar 0, formato numérico y colores
                $coloresRow = $filasColores[$filaIdx] ?? [];
                for ($i = 4; $i <= $columnCount; $i++) {
                    $col     = Coordinate::stringFromColumnIndex($i);
                    $cellRef = "{$col}{$currentRow}";
                    $header  = (string) ($headers[$i - 1] ?? '');

                    if ($this->esColumnaTextoReporte($header)) {
                        $value = $sheet->getCell($cellRef)->getValue();
                        $sheet->setCellValueExplicit($cellRef, $value === null ? '' : (string) $value, DataType::TYPE_STRING);
                        if ($header === self::COLUMNA_OBS_MATRICULA) {
                            $sheet->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                        continue;
                    }

                    $value   = $sheet->getCell($cellRef)->getValue();
                    if ($value === null || $value === '') {
                        $sheet->setCellValueExplicit($cellRef, 0, DataType::TYPE_NUMERIC);
                    }
                    $sheet->getStyle($cellRef)->getNumberFormat()->setFormatCode('#,##0.00');

                    $color = $coloresRow[$i - 4] ?? null;
                    if ($color !== null && isset(self::CELL_COLORS[$color])) {
                        $sheet->getStyle($cellRef)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::CELL_COLORS[$color]['fill']]],
                            'font' => ['color' => ['rgb' => self::CELL_COLORS[$color]['font']]],
                        ]);
                    }
                }

                $sheet->getStyle("A{$currentRow}:{$lastColumn}{$currentRow}")->applyFromArray([
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $contador++;
                $currentRow++;
            }
        }

        $sheet->setAutoFilter("A5:{$lastColumn}5");
        $sheet->freezePane('D6');

        $this->ajustarAnchosColumnasReporte($sheet, $headers, $columnCount);
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(5)->setRowHeight(24);

        // ── Hoja UNIFORMES ───────────────────────────────────────────────
        $rangoInicioUnif = $anio->fecha_inicio ? Carbon::parse($anio->fecha_inicio)->startOfDay() : null;
        $rangoFinUnif    = $anio->fecha_fin    ? Carbon::parse($anio->fecha_fin)->endOfDay()      : null;

        // 1. Ventas de uniformes agrupadas por estudiante + producto (precio total y monto pagado)
        $ventasUnifRaw = DB::table('inventario_venta_detalles as ivd')
            ->join('inventario_ventas as iv', 'iv.id', '=', 'ivd.inventario_venta_id')
            ->join('inventario_productos as ip', 'ip.id', '=', 'ivd.inventario_producto_id')
            ->join('inventario_categorias as ic', 'ic.id', '=', 'ip.categoria_id')
            ->where('ic.slug', 'uniforme')
            ->whereNotNull('iv.estudiante_id')
            ->when($rangoInicioUnif && $rangoFinUnif, fn ($q) => $q->whereBetween('iv.fecha_venta', [$rangoInicioUnif, $rangoFinUnif]))
            ->select([
                'iv.estudiante_id',
                'ip.nombre as producto',
                DB::raw('SUM(ivd.subtotal) as total_venta'),
                DB::raw('SUM(iv.monto_pagado) as total_pagado'),
            ])
            ->groupBy('iv.estudiante_id', 'ip.nombre')
            ->get();

        // Lookup: [estudiante_id][producto] = ['total' => X, 'pagado' => Y]
        $ventasUnifMap = [];
        foreach ($ventasUnifRaw as $v) {
            $ventasUnifMap[$v->estudiante_id][$v->producto] = [
                'total'  => (float) $v->total_venta,
                'pagado' => (float) $v->total_pagado,
            ];
        }

        // 2. Columnas de productos: los que tienen ventas, ordenados por nombre
        $productosUnifCols = $ventasUnifRaw->pluck('producto')->unique()->sort()->values()->toArray();

        // Si no hay ventas aún, mostrar todos los productos de uniforme activos
        if (empty($productosUnifCols)) {
            $productosUnifCols = DB::table('inventario_productos as ip')
                ->join('inventario_categorias as ic', 'ic.id', '=', 'ip.categoria_id')
                ->where('ic.slug', 'uniforme')
                ->where('ip.activo', 1)
                ->orderBy('ip.nombre')
                ->pluck('ip.nombre')
                ->toArray();
        }

        // 3. Obtener todos los alumnos matriculados en este año, agrupados por sección
        $seccionesUnif = DB::table('secciones as sec')
            ->join('grados as g', 'g.id', '=', 'sec.grado_id')
            ->where('sec.anio_escolar_id', $anio->id)
            ->select([
                'sec.id as seccion_id',
                'sec.nombre as seccion_nombre',
                'g.nombre as grado_nombre',
                'g.nivel',
                DB::raw("CONCAT(g.nombre, ' - ', sec.nombre) as seccion_label"),
                DB::raw("CASE g.nivel WHEN 'Inicial' THEN 1 WHEN 'Primaria' THEN 2 WHEN 'Secundaria' THEN 3 ELSE 9 END as nivel_orden"),
            ])
            ->orderByRaw('nivel_orden, g.nombre, sec.nombre')
            ->get();

        $alumnosPorSeccion = DB::table('matriculas as m')
            ->join('estudiantes as est', 'est.id', '=', 'm.estudiante_id')
            ->where('m.anio_escolar_id', $anio->id)
            ->whereIn('m.estado', ['activa', 'registrada'])
            ->select(['m.seccion_id', 'est.id as estudiante_id', 'est.apellidos', 'est.nombres', 'est.dni'])
            ->orderBy('est.apellidos')
            ->orderBy('est.nombres')
            ->get()
            ->groupBy('seccion_id');

        // 4. Construir la hoja
        $sheetUnif = $spreadsheet->createSheet();
        $sheetUnif->setTitle('Uniformes');

        // Columnas: Nro | DNI | Alumno | [productos...] | TOTAL PAGADO | SALDO
        $headersUnif  = array_merge(['Nro', 'DNI', 'Alumno'], $productosUnifCols, ['T. PAGADO', 'SALDO']);
        $colCountUnif = count($headersUnif);
        $lastColUnif  = Coordinate::stringFromColumnIndex($colCountUnif);
        $colSaldoIdx  = $colCountUnif;       // índice 1-based de SALDO
        $colPagadoIdx = $colCountUnif - 1;   // índice 1-based de T. PAGADO
        $colProdStart = 4;                   // índice 1-based donde empiezan los productos
        $colProdEnd   = $colCountUnif - 2;   // último índice de producto

        // Título
        $sheetUnif->setCellValue('A1', 'UNIFORMES - ' . ($anio->nombre ?? date('Y')));
        $sheetUnif->mergeCells("A1:{$lastColUnif}1");
        $sheetUnif->getStyle("A1:{$lastColUnif}1")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'A00000']],
        ]);
        $sheetUnif->getRowDimension(1)->setRowHeight(26);

        $sheetUnif->setCellValue('A2', 'Todos los alumnos — compras de uniformes');
        $sheetUnif->mergeCells("A2:{$lastColUnif}2");
        $sheetUnif->getStyle("A2:{$lastColUnif}2")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Cabeceras en fila 4
        $sheetUnif->fromArray($headersUnif, null, 'A4');
        $sheetUnif->getRowDimension(4)->setRowHeight(28);
        $sheetUnif->getStyle("A4:{$lastColUnif}4")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);
        // Columna TOTAL con color especial
        $sheetUnif->getStyle("{$lastColUnif}4")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1F2937']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        ]);

        $rowUnif   = 5;
        $nroUnif   = 1;
        $totalGral = 0.0;

        foreach ($seccionesUnif as $seccion) {
            $alumnos = $alumnosPorSeccion[$seccion->seccion_id] ?? collect();
            if ($alumnos->isEmpty()) continue;

            // Fila separadora de sección
            $textoSep = '  ' . strtoupper($seccion->seccion_label) . '   (' . $alumnos->count() . ' alumno' . ($alumnos->count() !== 1 ? 's' : '') . ')';
            $sheetUnif->setCellValue("A{$rowUnif}", $textoSep);
            $sheetUnif->mergeCells("A{$rowUnif}:{$lastColUnif}{$rowUnif}");
            $sheetUnif->getStyle("A{$rowUnif}:{$lastColUnif}{$rowUnif}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheetUnif->getRowDimension($rowUnif)->setRowHeight(18);
            $rowUnif++;

            foreach ($alumnos as $alumno) {
                $compras      = $ventasUnifMap[$alumno->estudiante_id] ?? [];
                $totalPagAlum = 0.0;
                $totalVtaAlum = 0.0;
                foreach ($compras as $datos) {
                    $totalPagAlum += $datos['pagado'];
                    $totalVtaAlum += $datos['total'];
                }
                $saldoAlum  = round($totalVtaAlum - $totalPagAlum, 2);
                $totalGral += $totalPagAlum;

                // Fila: Nro, DNI, Nombre + monto_pagado por producto + T.PAGADO + SALDO
                $rowData = [$nroUnif, $alumno->dni ?? '', trim($alumno->apellidos . ' ' . $alumno->nombres)];
                foreach ($productosUnifCols as $prod) {
                    $rowData[] = isset($compras[$prod]) ? $compras[$prod]['pagado'] : 0;
                }
                $rowData[] = $totalPagAlum;
                $rowData[] = $saldoAlum;

                $sheetUnif->fromArray($rowData, null, "A{$rowUnif}");

                // DNI como texto
                $sheetUnif->setCellValueExplicit("B{$rowUnif}", (string) ($alumno->dni ?? ''), DataType::TYPE_STRING);

                // Formato numérico + colores para columnas de productos
                for ($ci = $colProdStart; $ci <= $colProdEnd; $ci++) {
                    $col   = Coordinate::stringFromColumnIndex($ci);
                    $prod  = $productosUnifCols[$ci - $colProdStart];
                    $sheetUnif->getStyle("{$col}{$rowUnif}")->getNumberFormat()->setFormatCode('#,##0.00');

                    if (isset($compras[$prod])) {
                        $pagado = $compras[$prod]['pagado'];
                        $total  = $compras[$prod]['total'];
                        if ($pagado >= $total) {
                            // Verde: totalmente pagado
                            $sheetUnif->getStyle("{$col}{$rowUnif}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C6EFCE']],
                                'font' => ['color' => ['rgb' => '375623']],
                            ]);
                        } elseif ($pagado > 0) {
                            // Amarillo: pago parcial
                            $sheetUnif->getStyle("{$col}{$rowUnif}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFEB9C']],
                                'font' => ['color' => ['rgb' => '9C5700']],
                            ]);
                        }
                    }
                }

                // Columna T. PAGADO
                $colPagado = Coordinate::stringFromColumnIndex($colPagadoIdx);
                $sheetUnif->getStyle("{$colPagado}{$rowUnif}")->getNumberFormat()->setFormatCode('#,##0.00');
                if ($totalPagAlum > 0) {
                    $sheetUnif->getStyle("{$colPagado}{$rowUnif}")->applyFromArray([
                        'font' => ['bold' => true],
                    ]);
                }

                // Columna SALDO
                $colSaldo = Coordinate::stringFromColumnIndex($colSaldoIdx);
                $sheetUnif->getStyle("{$colSaldo}{$rowUnif}")->getNumberFormat()->setFormatCode('#,##0.00');
                if ($saldoAlum > 0) {
                    $sheetUnif->getStyle("{$colSaldo}{$rowUnif}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFCCCC']],
                        'font' => ['bold' => true, 'color' => ['rgb' => 'A00000']],
                    ]);
                }

                $sheetUnif->getStyle("A{$rowUnif}:{$lastColUnif}{$rowUnif}")->applyFromArray([
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E8E8E8']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $nroUnif++;
                $rowUnif++;
            }
        }

        // Fila de total general
        $rowUnif++;
        $colPagadoStr = Coordinate::stringFromColumnIndex($colPagadoIdx);
        $colSaldoStr  = Coordinate::stringFromColumnIndex($colSaldoIdx);
        $colAntesStr  = Coordinate::stringFromColumnIndex($colPagadoIdx - 1);
        $sheetUnif->setCellValue("{$colAntesStr}{$rowUnif}", 'TOTAL GENERAL');
        $sheetUnif->setCellValue("{$colPagadoStr}{$rowUnif}", $totalGral);
        $sheetUnif->getStyle("{$colAntesStr}{$rowUnif}:{$colSaldoStr}{$rowUnif}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1F2937']],
        ]);
        $sheetUnif->getStyle("{$colPagadoStr}{$rowUnif}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Anchos de columnas
        $sheetUnif->getColumnDimension('A')->setWidth(6);
        $sheetUnif->getColumnDimension('B')->setWidth(13);
        $sheetUnif->getColumnDimension('C')->setWidth(32);
        for ($ci = $colProdStart; $ci <= $colProdEnd; $ci++) {
            $sheetUnif->getColumnDimension(Coordinate::stringFromColumnIndex($ci))->setWidth(13);
        }
        $sheetUnif->getColumnDimension(Coordinate::stringFromColumnIndex($colPagadoIdx))->setWidth(13);
        $sheetUnif->getColumnDimension(Coordinate::stringFromColumnIndex($colSaldoIdx))->setWidth(13);

        $sheetUnif->setAutoFilter("A4:{$lastColUnif}4");
        $sheetUnif->freezePane('D5');

        // Volver a la primera hoja activa
        $spreadsheet->setActiveSheetIndex(0);

        $writer   = new Xlsx($spreadsheet);
        $filename = 'REPORTE_PAGOS_DETALLE_' . now()->format('d-m-Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildReportesData(Request $request): array
    {
        [$anios, $anio] = $this->resolveAnio($request);

        $anioId = $anio?->id;
        $rangoInicio = $anio?->fecha_inicio ? Carbon::parse($anio->fecha_inicio)->startOfDay() : null;
        $rangoFin = $anio?->fecha_fin ? Carbon::parse($anio->fecha_fin)->endOfDay() : null;

        $estudiantesTotal = Estudiante::count();
        $estudiantesActivos = Estudiante::where('estado', 'activo')->count();
        $docentesTotal = Docente::count();
        $docentesActivos = Docente::where('estado', 'activo')->count();
        $seccionesTotal = Seccion::when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))->count();
        $cursosTotal = Curso::count();

        $matriculasBase = Matricula::query();
        if ($anioId) {
            $matriculasBase->where('anio_escolar_id', $anioId);
        }
        $matriculasTotal = (clone $matriculasBase)->count();
        $matriculasRegistradas = (clone $matriculasBase)->where('estado', 'registrada')->count();
        $matriculasRecientes = (clone $matriculasBase)
            ->with(['estudiante', 'grado', 'seccion'])
            ->orderByRaw('COALESCE(fecha_matricula, created_at) desc')
            ->limit(6)
            ->get();

        $pagosBase = Pago::query();
        if ($anioId) {
            $pagosBase->where('anio_escolar_id', $anioId);
        }
        $pagosTotal = (clone $pagosBase)->count();
        $pagosMonto = (clone $pagosBase)->sum('monto');
        $pagosPorEstado = (clone $pagosBase)
            ->selectRaw('estado, count(*) as total, sum(monto) as monto')
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();
        $pagosRecientes = (clone $pagosBase)
            ->orderByDesc('fecha_pago')
            ->limit(6)
            ->get();

        $deudasPendientes = Deuda::when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))
            ->where('estado', 'pendiente')
            ->sum('saldo');

        $ventasBase = InventarioVenta::query();
        if ($rangoInicio && $rangoFin) {
            $ventasBase->whereBetween('fecha_venta', [$rangoInicio, $rangoFin]);
        }
        $ventasTotal = (clone $ventasBase)->count();
        $ventasMonto = (clone $ventasBase)->sum('total');
        $ventasRecientes = (clone $ventasBase)
            ->orderByDesc('fecha_venta')
            ->limit(6)
            ->get();

        $productosTop = InventarioVentaDetalle::query()
            ->select('inventario_producto_id', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(subtotal) as total_venta'))
            ->join('inventario_ventas', 'inventario_ventas.id', '=', 'inventario_venta_detalles.inventario_venta_id')
            ->when($rangoInicio && $rangoFin, fn($q) => $q->whereBetween('inventario_ventas.fecha_venta', [$rangoInicio, $rangoFin]))
            ->groupBy('inventario_producto_id')
            ->orderByDesc('total_venta')
            ->with('producto')
            ->limit(6)
            ->get();

        $incidenciasBase = Incidencia::query();
        if ($anioId) {
            $incidenciasBase->where('anio_escolar_id', $anioId);
        }
        $incidenciasTotal = (clone $incidenciasBase)->count();
        $incidenciasAbiertas = (clone $incidenciasBase)->where('estado', 'abierta')->count();
        $incidenciasRecientes = (clone $incidenciasBase)
            ->with('estudiante')
            ->orderByDesc('fecha_reporte')
            ->limit(6)
            ->get();
        $incidenciasPorTipo = (clone $incidenciasBase)
            ->selectRaw("COALESCE(tipo, 'Sin tipo') as tipo, count(*) as total")
            ->groupBy('tipo')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $asistenciasBase = Asistencia::query();
        if ($anioId) {
            $asistenciasBase->where('anio_escolar_id', $anioId);
        }
        $asistenciasTotal = (clone $asistenciasBase)->count();
        $asistenciasUltimos7 = (clone $asistenciasBase)
            ->where('fecha', '>=', Carbon::now()->subDays(6)->toDateString())
            ->count();
        $asistenciasPorDia = (clone $asistenciasBase)
            ->selectRaw('fecha, count(*) as total')
            ->groupBy('fecha')
            ->orderByDesc('fecha')
            ->limit(7)
            ->get();

        $notasBase = Nota::query();
        if ($anioId) {
            $notasBase->where('anio_escolar_id', $anioId);
        }
        $notasTotal = (clone $notasBase)->count();
        $notasAbiertas = (clone $notasBase)->where('estado', 'abierta')->count();
        $notasCerradas = (clone $notasBase)->where('estado', 'cerrada')->count();
        $notasPorPeriodo = (clone $notasBase)
            ->selectRaw('periodo, count(*) as total, avg(valor) as promedio, sum(case when valor >= 11 then 1 else 0 end) as aprobados')
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $filtrosExcel = $this->normalizarFiltrosExcel($request, $anioId ? (int) $anioId : null);
        $catalogoExcel = $this->catalogoFiltrosExcel($anioId ? (int) $anioId : null, $filtrosExcel);

        return [
            'anios' => $anios,
            'anio' => $anio,
            'anioId' => $anioId,
            'estudiantesTotal' => $estudiantesTotal,
            'estudiantesActivos' => $estudiantesActivos,
            'docentesTotal' => $docentesTotal,
            'docentesActivos' => $docentesActivos,
            'seccionesTotal' => $seccionesTotal,
            'cursosTotal' => $cursosTotal,
            'matriculasTotal' => $matriculasTotal,
            'matriculasRegistradas' => $matriculasRegistradas,
            'matriculasRecientes' => $matriculasRecientes,
            'pagosTotal' => $pagosTotal,
            'pagosMonto' => $pagosMonto,
            'pagosPorEstado' => $pagosPorEstado,
            'pagosRecientes' => $pagosRecientes,
            'deudasPendientes' => $deudasPendientes,
            'ventasTotal' => $ventasTotal,
            'ventasMonto' => $ventasMonto,
            'ventasRecientes' => $ventasRecientes,
            'productosTop' => $productosTop,
            'incidenciasTotal' => $incidenciasTotal,
            'incidenciasAbiertas' => $incidenciasAbiertas,
            'incidenciasRecientes' => $incidenciasRecientes,
            'incidenciasPorTipo' => $incidenciasPorTipo,
            'asistenciasTotal' => $asistenciasTotal,
            'asistenciasUltimos7' => $asistenciasUltimos7,
            'asistenciasPorDia' => $asistenciasPorDia,
            'notasTotal' => $notasTotal,
            'notasAbiertas' => $notasAbiertas,
            'notasCerradas' => $notasCerradas,
            'notasPorPeriodo' => $notasPorPeriodo,
            'filtrosExcel' => $filtrosExcel,
            'catalogoExcel' => $catalogoExcel,
            'mesesExcel' => self::MESES,
        ];
    }

    private function resolveAnio(Request $request): array
    {
        $anios = AnioEscolar::orderBy('fecha_inicio', 'desc')->get();
        $anioSeleccionado = $request->input('anio_escolar_id');
        $anioActivo = $anios->firstWhere('activo', true);
        $anio = $anioSeleccionado
            ? $anios->firstWhere('id', (int) $anioSeleccionado)
            : ($anioActivo ?? $anios->first());

        return [$anios, $anio];
    }

    private function normalizarFiltrosExcel(Request $request, ?int $anioId): array
    {
        $tipoReporte = (string) $request->input('tipo_reporte', 'general');
        if (!in_array($tipoReporte, self::TIPOS_REPORTE_VALIDOS, true)) {
            $tipoReporte = 'general';
        }

        $periodoId = $request->filled('periodo_academico_id') ? (int) $request->input('periodo_academico_id') : null;
        $nivel = trim((string) $request->input('nivel', ''));
        $gradoId = $request->filled('grado_id') ? (int) $request->input('grado_id') : null;
        $seccionId = $request->filled('seccion_id') ? (int) $request->input('seccion_id') : null;
        $mesDesde = $request->filled('mes_desde') ? (int) $request->input('mes_desde') : null;
        $mesHasta = $request->filled('mes_hasta') ? (int) $request->input('mes_hasta') : null;

        if ($mesDesde !== null && ($mesDesde < 1 || $mesDesde > 12)) {
            $mesDesde = null;
        }
        if ($mesHasta !== null && ($mesHasta < 1 || $mesHasta > 12)) {
            $mesHasta = null;
        }
        if ($mesDesde !== null && $mesHasta !== null && $mesHasta < $mesDesde) {
            [$mesDesde, $mesHasta] = [$mesHasta, $mesDesde];
        }

        return [
            'anio_escolar_id' => $anioId,
            'tipo_reporte' => $tipoReporte,
            'periodo_academico_id' => $periodoId,
            'nivel' => $nivel !== '' ? $nivel : null,
            'grado_id' => $gradoId,
            'seccion_id' => $seccionId,
            'mes_desde' => $mesDesde,
            'mes_hasta' => $mesHasta,
        ];
    }

    private function catalogoFiltrosExcel(?int $anioId, array $filtros): array
    {
        $periodos = PeriodoAcademico::query()
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId))
            ->orderBy('orden')
            ->get(['id', 'nombre', 'codigo', 'anio_escolar_id', 'es_vacacional']);

        $niveles = Grado::query()
            ->select('nivel')
            ->whereNotNull('nivel')
            ->when($anioId, function ($q) use ($anioId) {
                $q->whereHas('secciones', fn ($sq) => $sq->where('anio_escolar_id', $anioId));
            })
            ->distinct()
            ->orderBy('nivel')
            ->pluck('nivel')
            ->values();

        $grados = Grado::query()
            ->select('id', 'nombre', 'nivel')
            ->when($filtros['nivel'], fn ($q, $nivel) => $q->where('nivel', $nivel))
            ->when($anioId, function ($q) use ($anioId) {
                $q->whereHas('secciones', fn ($sq) => $sq->where('anio_escolar_id', $anioId));
            })
            ->orderBy('nivel')
            ->orderBy('nombre')
            ->get();

        $secciones = Seccion::query()
            ->with(['grado:id,nombre,nivel'])
            ->select('id', 'nombre', 'grado_id', 'anio_escolar_id')
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId))
            ->when($filtros['grado_id'], fn ($q, $gradoId) => $q->where('grado_id', $gradoId))
            ->when($filtros['nivel'], function ($q, $nivel) {
                $q->whereHas('grado', fn ($gq) => $gq->where('nivel', $nivel));
            })
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        return [
            'periodos' => $periodos,
            'niveles' => $niveles,
            'grados' => $grados,
            'secciones' => $secciones,
        ];
    }

    private function construirDatasetExcelPagos(AnioEscolar $anio, array $filtros): array
    {
        $periodo = null;
        if (!empty($filtros['periodo_academico_id'])) {
            $periodo = PeriodoAcademico::query()
                ->where('anio_escolar_id', $anio->id)
                ->where('id', $filtros['periodo_academico_id'])
                ->first();
        }

        $mesesSeleccionados = $this->resolverMesesSeleccionados($filtros, $periodo);
        $matriculas = $this->consultarMatriculasFiltradas((int) $anio->id, $filtros);

        if ($matriculas->isEmpty()) {
            return [
                'columnas' => $this->construirColumnasReporte([], $mesesSeleccionados),
                'hojas' => [],
                'filtro_label' => $this->labelFiltroExcel($filtros, $periodo, $mesesSeleccionados),
            ];
        }

        $matriculaIds = $matriculas->pluck('id')->values()->all();

        $deudas = Deuda::query()
            ->with(['concepto:id,nombre'])
            ->whereIn('matricula_id', $matriculaIds)
            ->orderBy('id')
            ->get(['id', 'matricula_id', 'periodo', 'concepto_pago_id', 'fecha_vencimiento', 'monto']);

        $deudaIds = $deudas->pluck('id')->values()->all();
        $pagadoPorDeuda = empty($deudaIds)
            ? collect()
            : DB::table('deuda_pagos')
                ->select('deuda_id', DB::raw('SUM(monto_aplicado) as total_pagado'))
                ->whereIn('deuda_id', $deudaIds)
                ->groupBy('deuda_id')
                ->pluck('total_pagado', 'deuda_id');

        $deudasPorMatricula = $deudas->groupBy('matricula_id');
        $extras = [];

        foreach ($deudas as $deuda) {
            $clasificacion = $this->clasificarDeuda($deuda);
            if ($clasificacion['tipo'] === 'extra' && !empty($clasificacion['label'])) {
                $extras[$clasificacion['label']] = true;
            }
        }

        $extrasOrdenados = array_keys($extras);
        sort($extrasOrdenados, SORT_NATURAL | SORT_FLAG_CASE);

        $columnas = $this->construirColumnasReporte($extrasOrdenados, $mesesSeleccionados);
        $observacionesMatricula = $this->resolverObservacionesMatricula((int) $anio->id);
        $hojas = [];
        $contadorPorSeccion = [];

        foreach ($matriculas as $matricula) {
            $seccionId = (int) ($matricula->seccion_id ?? 0);
            $seccionLabel = $this->labelSeccion($matricula);
            $claveHoja = $seccionId > 0 ? (string) $seccionId : 'sin-seccion';

            if (!isset($hojas[$claveHoja])) {
                $nivelOrden = match (strtolower(trim((string) ($matricula->grado->nivel ?? '')))) {
                    'inicial'    => 1,
                    'primaria'   => 2,
                    'secundaria' => 3,
                    default      => 9,
                };
                $gradoOrden  = (int) ($matricula->grado->orden ?? 99);
                $seccionNombre = trim((string) ($matricula->seccion->nombre ?? 'Z'));
                $hojas[$claveHoja] = [
                    'titulo'       => $seccionLabel,
                    'seccion_label' => $seccionLabel,
                    'filas'        => [],
                    'sort_key'     => sprintf('%d-%03d-%s', $nivelOrden, $gradoOrden, $seccionNombre),
                ];
                $contadorPorSeccion[$claveHoja] = 1;
            }

            $valores = [];
            $deudaMontos = []; // total owed per column
            foreach ($columnas as $columna) {
                $valores[$columna] = $this->esColumnaTextoReporte($columna) ? '' : 0.0;
                $deudaMontos[$columna] = 0.0;
            }
            $valores[self::COLUMNA_OBS_MATRICULA] = $observacionesMatricula[$matricula->id] ?? '';

            foreach (($deudasPorMatricula[$matricula->id] ?? collect()) as $deuda) {
                $pagado = round((float) ($pagadoPorDeuda[$deuda->id] ?? 0), 2);
                $monto  = round((float) ($deuda->monto ?? 0), 2);

                $clasificacion = $this->clasificarDeuda($deuda);

                if ($clasificacion['tipo'] === 'matricula') {
                    $deudaMontos[self::COLUMNA_MATRICULA] += $monto;
                    if ($pagado > 0) $valores[self::COLUMNA_MATRICULA] += $pagado;
                    continue;
                }

                if ($clasificacion['tipo'] === 'pension') {
                    $mes = $clasificacion['mes'] ?? $this->mesDesdeFecha($deuda->fecha_vencimiento);
                    if ($mes === null || !in_array($mes, $mesesSeleccionados, true)) {
                        continue;
                    }
                    $abreviatura = self::MESES[$mes]['abreviatura'] ?? null;
                    if (!$abreviatura) continue;

                    $key = 'P.' . $abreviatura;
                    if (array_key_exists($key, $valores)) {
                        $deudaMontos[$key] += $monto;
                        if ($pagado > 0) $valores[$key] += $pagado;
                    }
                    continue;
                }

                if ($clasificacion['tipo'] === 'extra' && !empty($clasificacion['label'])) {
                    $label = $clasificacion['label'];
                    if (array_key_exists($label, $valores)) {
                        $deudaMontos[$label] += $monto;
                        if ($pagado > 0) $valores[$label] += $pagado;
                    }
                }
            }

            foreach ($valores as $key => $valor) {
                if ($this->esColumnaTextoReporte($key)) {
                    continue;
                }
                $valores[$key] = round((float) $valor, 2);
            }

            // Compute cell colors: verde=paid, ambar=partial, rojo=unpaid, null=no debt
            $colores = [];
            foreach ($columnas as $col) {
                if ($this->esColumnaTextoReporte($col)) {
                    $colores[] = null;
                    continue;
                }
                $owed = $deudaMontos[$col] ?? 0.0;
                $paid = $valores[$col]     ?? 0.0;
                if ($owed <= 0) {
                    $colores[] = null;
                } elseif ($paid >= $owed - 0.01) {
                    $colores[] = 'verde';
                } elseif ($paid > 0) {
                    $colores[] = 'ambar';
                } else {
                    $colores[] = 'rojo';
                }
            }

            $hojas[$claveHoja]['filas'][]        = array_merge(
                [
                    $contadorPorSeccion[$claveHoja],
                    (string) ($matricula->estudiante->dni ?? '-'),
                    trim((string) (($matricula->estudiante->apellidos ?? '') . ' ' . ($matricula->estudiante->nombres ?? ''))),
                ],
                array_values($valores)
            );
            $hojas[$claveHoja]['filas_colores'][] = $colores;

            $contadorPorSeccion[$claveHoja]++;
        }

        return [
            'columnas' => $columnas,
            'hojas' => array_values($hojas),
            'filtro_label' => $this->labelFiltroExcel($filtros, $periodo, $mesesSeleccionados),
        ];
    }

    private function consultarMatriculasFiltradas(int $anioId, array $filtros)
    {
        $query = Matricula::query()
            ->with([
                'estudiante:id,dni,nombres,apellidos',
                'grado:id,nombre,nivel,orden',
                'seccion:id,nombre,grado_id,anio_escolar_id',
            ])
            ->where('anio_escolar_id', $anioId)
            ->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA]);

        if (!empty($filtros['nivel'])) {
            $nivel = $filtros['nivel'];
            $query->whereHas('grado', fn ($gq) => $gq->where('nivel', $nivel));
        }

        if (!empty($filtros['grado_id'])) {
            $query->where('grado_id', $filtros['grado_id']);
        }

        if (!empty($filtros['seccion_id'])) {
            $query->where('seccion_id', $filtros['seccion_id']);
        }

        return $query->get()->sortBy(function ($matricula) {
            return sprintf(
                '%06d-%010d',
                (int) ($matricula->seccion_id ?? 0),
                (int) ($matricula->id ?? 0)
            );
        })->values();
    }

    private function construirColumnasReporte(array $extrasOrdenados, array $mesesSeleccionados): array
    {
        $columnas = [self::COLUMNA_MATRICULA, self::COLUMNA_OBS_MATRICULA];

        foreach ($extrasOrdenados as $extra) {
            if (!in_array($extra, [self::COLUMNA_MATRICULA, self::COLUMNA_OBS_MATRICULA], true)) {
                $columnas[] = $extra;
            }
        }

        foreach ($mesesSeleccionados as $mes) {
            $abreviatura = self::MESES[$mes]['abreviatura'] ?? null;
            if (!$abreviatura) {
                continue;
            }
            $columnas[] = 'P.' . $abreviatura;
        }

        return $columnas;
    }

    private function esColumnaTextoReporte(string $columna): bool
    {
        return $columna === self::COLUMNA_OBS_MATRICULA;
    }

    private function ajustarAnchosColumnasReporte($sheet, array $headers, int $columnCount): void
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $header = (string) ($headers[$i - 1] ?? '');
            $dimension = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i));

            if ($header === self::COLUMNA_OBS_MATRICULA) {
                $dimension->setAutoSize(false);
                $dimension->setWidth(6.5);
                continue;
            }

            $dimension->setAutoSize(true);
        }
    }

    private function resolverObservacionesMatricula(int $anioId): array
    {
        $matriculas = Matricula::query()
            ->with([
                'descuentos:id,matricula_id,tipo',
                'estudiante:id,fecha_nacimiento',
                'estudiante.apoderados' => fn ($q) => $q
                    ->wherePivot('principal', true)
                    ->select('apoderados.id'),
            ])
            ->where('anio_escolar_id', $anioId)
            ->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])
            ->get(['id', 'estudiante_id', 'observacion']);

        $familias = [];

        foreach ($matriculas as $matricula) {
            $apoderadoId = $matricula->estudiante?->apoderados->first()?->id;
            if ($apoderadoId) {
                $familias[$apoderadoId][] = $matricula;
            }
        }

        $observaciones = [];

        foreach ($matriculas as $matricula) {
            $observacion = $this->normalizarObservacionHermano($matricula->observacion ?? null);
            if ($observacion !== null) {
                $observaciones[$matricula->id] = $observacion;
            }
        }

        foreach ($familias as $matriculasFamilia) {
            if (count($matriculasFamilia) <= 1) {
                continue;
            }

            $grupo = collect($matriculasFamilia)
                ->sortBy(function (Matricula $matricula) {
                    $fechaNacimiento = (string) ($matricula->estudiante?->fecha_nacimiento ?? '1900-01-01');
                    return $fechaNacimiento . '-' . str_pad((string) $matricula->id, 10, '0', STR_PAD_LEFT);
                })
                ->values();
            $cantidad = $grupo->count();

            $matriculaMenor = $grupo->first(function (Matricula $matricula) use ($observaciones) {
                return ($observaciones[$matricula->id] ?? null) === 'H-';
            });

            if (!$matriculaMenor) {
                $matriculaMenor = $grupo->first(function (Matricula $matricula) {
                    return $matricula->descuentos->contains(fn ($descuento) => $descuento->tipo === 'hermano_menor');
                });
            }

            if (!$matriculaMenor) {
                $matriculaMenor = $grupo->last();
            }

            if (!$matriculaMenor) {
                continue;
            }

            $matriculaMayor = $grupo->first(function (Matricula $matricula) use ($observaciones) {
                return ($observaciones[$matricula->id] ?? null) === 'H+';
            });

            if (!$matriculaMayor) {
                $matriculaMayor = $grupo->first(function (Matricula $matricula) use ($matriculaMenor) {
                    return (int) $matricula->id !== (int) $matriculaMenor->id;
                }) ?? $grupo->first();
            }

            foreach ($grupo as $matricula) {
                if (isset($observaciones[$matricula->id])) {
                    continue;
                }

                if ($cantidad > 2) {
                    $observaciones[$matricula->id] = match (true) {
                        (int) $matricula->id === (int) $matriculaMenor->id => 'H-',
                        (int) $matricula->id === (int) $matriculaMayor->id => 'H+',
                        default => 'H',
                    };
                    continue;
                }

                $observaciones[$matricula->id] = (int) $matricula->id === (int) $matriculaMenor->id
                    ? 'H-'
                    : 'H+';
            }
        }

        return $observaciones;
    }

    private function normalizarObservacionHermano(?string $observacion): ?string
    {
        $observacion = Str::upper(trim((string) $observacion));

        if ($observacion === '') {
            return null;
        }

        if (str_contains($observacion, 'NH')) {
            return ''; // excluir de detección automática de hermanos
        }

        if (str_contains($observacion, 'H-')) {
            return 'H-';
        }

        if (str_contains($observacion, 'H+')) {
            return 'H+';
        }

        return preg_match('/(^|[^A-Z0-9])H($|[^A-Z0-9])/', $observacion) ? 'H' : null;
    }

    private function resolverMesesSeleccionados(array $filtros, ?PeriodoAcademico $periodo): array
    {
        $tipo = (string) ($filtros['tipo_reporte'] ?? 'general');

        if ($tipo === 'periodo' && $periodo) {
            if (method_exists($periodo, 'mesesGenerablesPensiones')) {
                $meses = $periodo->mesesGenerablesPensiones();
            } else {
                [$inicio, $fin] = $periodo->rangoMesesPensiones();
                $meses = range($inicio, $fin);
            }
        } elseif ($tipo === 'rango' && !empty($filtros['mes_desde']) && !empty($filtros['mes_hasta'])) {
            $inicio = max(1, min(12, (int) $filtros['mes_desde']));
            $fin = max(1, min(12, (int) $filtros['mes_hasta']));
            if ($fin < $inicio) {
                [$inicio, $fin] = [$fin, $inicio];
            }
            $meses = range($inicio, $fin);
        } else {
            $meses = range(1, 12);
        }

        $meses = array_values(array_filter(array_map('intval', $meses), fn (int $m) => $m >= 1 && $m <= 12));
        $meses = array_values(array_unique($meses));
        sort($meses);

        return $meses;
    }

    private function clasificarDeuda(Deuda $deuda): array
    {
        $periodoRaw = trim((string) ($deuda->periodo ?? ''));
        $conceptoRaw = trim((string) ($deuda->concepto->nombre ?? ''));
        $periodo = Str::upper(Str::ascii($periodoRaw));
        $concepto = Str::upper(Str::ascii($conceptoRaw));

        if ($this->esDeudaMatricula($periodo, $concepto)) {
            return ['tipo' => 'matricula'];
        }

        $mesPeriodo = $this->mesDesdeTexto($periodo);

        if (Str::contains($periodo, 'PENSION') || Str::contains($concepto, 'PENSION')) {
            return ['tipo' => 'pension', 'mes' => $mesPeriodo];
        }

        // Normalize all libros variants (e.g. "Libros 2026", "Libros Primaria", "Producto: Libros Secundaria")
        $periodoSinPrefijo = Str::upper(trim((string) preg_replace('/^Producto:\s*/iu', '', $periodoRaw)));
        if (Str::startsWith($periodoSinPrefijo, 'LIBROS') || Str::contains($concepto, 'LIBROS')) {
            return ['tipo' => 'extra', 'label' => 'LIBROS'];
        }

        $extra = $periodoRaw !== '' ? $periodoRaw : ($conceptoRaw !== '' ? $conceptoRaw : 'Otros');
        $extra = preg_replace('/^Producto:\s*/iu', '', $extra);
        $extra = trim((string) $extra);
        if ($extra === '') {
            $extra = 'Otros';
        }

        return [
            'tipo' => 'extra',
            'label' => Str::upper($extra),
        ];
    }

    private function esDeudaMatricula(string $periodo, string $concepto): bool
    {
        if ($periodo !== '' && !Str::startsWith($periodo, 'PRODUCTO') && Str::contains($periodo, 'MATRICULA')) {
            return true;
        }

        return $periodo === '' && Str::contains($concepto, 'MATRICULA');
    }

    private function mesDesdeTexto(string $texto): ?int
    {
        $texto = Str::upper(Str::ascii($texto));
        if ($texto === '') {
            return null;
        }

        foreach (self::MESES as $numero => $meta) {
            $nombre = Str::upper(Str::ascii((string) ($meta['nombre'] ?? '')));
            if ($nombre !== '' && preg_match('/\b' . preg_quote($nombre, '/') . '\b/u', $texto)) {
                return $numero;
            }
        }

        if (preg_match('/\b(20\d{2})[-\/](0?[1-9]|1[0-2])\b/', $texto, $coincidencias)) {
            return (int) $coincidencias[2];
        }

        return null;
    }

    private function mesDesdeFecha($fecha): ?int
    {
        if (empty($fecha)) {
            return null;
        }

        try {
            return (int) Carbon::parse($fecha)->format('n');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function labelSeccion(Matricula $matricula): string
    {
        $nivel = trim((string) ($matricula->grado->nivel ?? ''));
        $grado = trim((string) ($matricula->grado->nombre ?? ''));
        $seccion = trim((string) ($matricula->seccion->nombre ?? 'Sin seccion'));

        $base = $grado !== '' ? $grado : $nivel;

        if ($base === '') {
            return $seccion;
        }

        return $base . ' - ' . $seccion;
    }

    private function labelFiltroExcel(array $filtros, ?PeriodoAcademico $periodo, array $mesesSeleccionados): string
    {
        $partes = [];
        $tipo = (string) ($filtros['tipo_reporte'] ?? 'general');

        if ($tipo === 'periodo' && $periodo) {
            $partes[] = 'Modo: Periodo (' . ($periodo->codigo ?: $periodo->nombre) . ')';
        } elseif ($tipo === 'rango') {
            $partes[] = 'Modo: Rango de meses';
        } else {
            $partes[] = 'Modo: General';
        }

        if (!empty($filtros['nivel'])) {
            $partes[] = 'Nivel: ' . $filtros['nivel'];
        }
        if (!empty($filtros['grado_id'])) {
            $grado = Grado::find($filtros['grado_id']);
            if ($grado) {
                $partes[] = 'Grado: ' . trim(($grado->nivel ?? '') . ' ' . $grado->nombre);
            }
        }
        if (!empty($filtros['seccion_id'])) {
            $seccion = Seccion::find($filtros['seccion_id']);
            if ($seccion) {
                $partes[] = 'Seccion: ' . $seccion->nombre;
            }
        }

        if (!empty($mesesSeleccionados)) {
            $inicio = self::MESES[$mesesSeleccionados[0]]['abreviatura'] ?? '';
            $fin = self::MESES[$mesesSeleccionados[count($mesesSeleccionados) - 1]]['abreviatura'] ?? '';
            if ($inicio !== '' && $fin !== '') {
                $partes[] = 'Meses: ' . $inicio . ' a ' . $fin;
            }
        }

        return implode(' | ', $partes);
    }

    private function normalizarTituloHoja(string $titulo, array $titulosUsados): string
    {
        $limpio = preg_replace('/[\\\\\\/*\\[\\]:?]/', '-', trim($titulo));
        $limpio = $limpio !== '' ? $limpio : 'Reporte';
        $base = mb_substr($limpio, 0, 31);
        $resultado = $base;
        $contador = 2;

        while (in_array($resultado, $titulosUsados, true)) {
            $sufijo = ' ' . $contador;
            $maxBase = 31 - mb_strlen($sufijo);
            $resultado = mb_substr($base, 0, max(1, $maxBase)) . $sufijo;
            $contador++;
        }

        return $resultado;
    }
}
