<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\IngresoEgreso;
use App\Models\InventarioVenta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinanzasController extends Controller
{
    private const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    private const CATEGORIAS_VALIDAS = ['todas', 'matriculas', 'pensiones', 'ventas'];
    private const ROLES_PERSONAL_ADMINISTRATIVO = ['Director', 'Secretaria', 'Auxiliar'];

    private const EGRESO_TIPOS = [
        'pago_docente' => 'Pago de Docente',
        'salario_administrativo' => 'Salario Administrativo',
        'alquiler_local' => 'Alquiler Local',
        'servicios_basicos' => 'Servicios (Luz/Agua/Net)',
        'materiales_belleza' => 'Actividades Escolares',
        'insumos_productos' => 'Insumos / Productos',
        'equipos_herramientas' => 'Equipos y Herramientas',
        'marketing_publicidad' => 'Marketing / Publicidad',
        'limpieza_mantenimiento' => 'Limpieza y Mantenimiento',
        'impuestos_tributos' => 'Impuestos / Tributos',
        'papeleria_utiles' => 'Papeleria / Utiles',
        'alimentacion_personal' => 'Alimentacion Personal',
        'transporte_movilidad' => 'Transporte / Movilidad',
        'capacitacion_docente' => 'Capacitacion Docente',
        'uniformes_vestimenta' => 'Uniformes / Vestimenta',
        'seguro_sctr' => 'Seguro / SCTR',
        'otros_gastos' => 'Otros Gastos',
    ];

    private const EGRESO_CATEGORIAS = [
        'sin_categoria' => 'Sin categoria',
        'salarios' => 'Salarios',
        'servicios' => 'Servicios',
        'operativo' => 'Operativo',
        'materiales' => 'Materiales',
        'impuestos' => 'Impuestos',
        'marketing' => 'Marketing',
        'logistica' => 'Logistica',
        'otros' => 'Otros',
    ];

    private const EGRESO_METODOS = ['Efectivo', 'Yape', 'Plin', 'Transferencia', 'Tarjeta'];
    private const EGRESO_TIPOS_COMPROBANTE = ['Recibo', 'Boleta', 'Factura', 'Voucher', 'Sin comprobante'];

    public function ingresos(Request $request)
    {
        [$anios, $anio, $anioId] = $this->resolverAnio($request);
        $filtros = $this->normalizarFiltrosIngresos($request, $anio);
        $dataset = $this->construirDatasetIngresos($anioId, $filtros);

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $itemsPagina = $dataset['movimientos']->slice(($page - 1) * $perPage, $perPage)->values();
        $movimientosPaginados = new LengthAwarePaginator(
            $itemsPagina,
            $dataset['movimientos']->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.finanzas.ingresos', [
            'anios' => $anios,
            'anio' => $anio,
            'anioId' => $anioId,
            'mesesCatalogo' => self::MESES,
            'filtros' => $filtros,
            'totales' => $dataset['totales'],
            'resumenMensual' => $dataset['resumen_mensual'],
            'movimientos' => $movimientosPaginados,
            'totalMovimientos' => $dataset['movimientos']->count(),
        ]);
    }

    public function exportarIngresosExcel(Request $request)
    {
        [, $anio, $anioId] = $this->resolverAnio($request);
        $filtros = $this->normalizarFiltrosIngresos($request, $anio);
        $dataset = $this->construirDatasetIngresos($anioId, $filtros);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ingresos');
        $sheet->getSheetView()->setShowZeros(true);

        $lastColumn = 'G';
        $sheet->setCellValue('A1', 'REPORTE DE INGRESOS CONFIRMADOS');
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A2', 'Anio: ' . ($anio?->nombre ?? '-'));
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A3', 'Filtro: ' . $this->labelFiltrosExport($filtros));
        $sheet->mergeCells("A3:{$lastColumn}3");

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '9A0000']],
        ]);
        $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $row = 5;
        $sheet->setCellValue("A{$row}", 'RESUMEN POR CATEGORIA');
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '7B1414']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDF2F2']],
        ]);

        $row++;
        $sheet->fromArray(['Categoria', 'Monto (S/)'], null, "A{$row}");
        $sheet->fromArray(
            [
                ['Matriculas', (float) $dataset['totales']['matriculas']],
                ['Pensiones', (float) $dataset['totales']['pensiones']],
                ['Ventas', (float) $dataset['totales']['ventas']],
                ['Total general', (float) $dataset['totales']['general']],
            ],
            null,
            "A" . ($row + 1)
        );
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($this->styleHeaderGrey());
        $sheet->getStyle("B" . ($row + 1) . ":B" . ($row + 4))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A{$row}:B" . ($row + 4))->applyFromArray($this->styleTableBorders());

        $row += 6;
        $sheet->setCellValue("A{$row}", 'RESUMEN MENSUAL');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '7B1414']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDF2F2']],
        ]);

        $row++;
        $sheet->fromArray(['Mes', 'Matriculas', 'Pensiones', 'Ventas', 'Total'], null, "A{$row}");
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($this->styleHeaderGrey());

        $inicioResumenMensual = $row + 1;
        foreach ($dataset['resumen_mensual'] as $resumen) {
            $sheet->fromArray([
                (string) ($resumen['mes_label'] ?? '-'),
                (float) ($resumen['matriculas'] ?? 0),
                (float) ($resumen['pensiones'] ?? 0),
                (float) ($resumen['ventas'] ?? 0),
                (float) ($resumen['total'] ?? 0),
            ], null, "A{$inicioResumenMensual}");
            $inicioResumenMensual++;
        }

        $finResumenMensual = max($row, $inicioResumenMensual - 1);
        $sheet->getStyle("A{$row}:E{$finResumenMensual}")->applyFromArray($this->styleTableBorders());
        if ($finResumenMensual > $row) {
            $sheet->getStyle("B" . ($row + 1) . ":E{$finResumenMensual}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        $row = $finResumenMensual + 2;
        $sheet->setCellValue("A{$row}", 'DETALLE DE MOVIMIENTOS');
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '7B1414']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDF2F2']],
        ]);

        $row++;
        $headersDetalle = ['Fecha', 'Categoria', 'Concepto', 'Alumno / Comprador', 'Metodo', 'Fuente', 'Monto (S/)'];
        $sheet->fromArray($headersDetalle, null, "A{$row}");
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->styleHeaderGrey());

        $rowDetalle = $row + 1;
        foreach ($dataset['movimientos'] as $mov) {
            $fecha = '-';
            try {
                $fecha = !empty($mov['fecha']) ? Carbon::parse((string) $mov['fecha'])->format('d/m/Y H:i') : '-';
            } catch (\Throwable $e) {
                $fecha = (string) ($mov['fecha'] ?? '-');
            }

            $sheet->fromArray([
                $fecha,
                (string) ($mov['categoria_label'] ?? '-'),
                (string) ($mov['concepto'] ?? '-'),
                (string) ($mov['persona'] ?? '-'),
                (string) ($mov['metodo'] ?? '-'),
                (string) ($mov['fuente'] ?? '-'),
                (float) ($mov['monto'] ?? 0),
            ], null, "A{$rowDetalle}");
            $rowDetalle++;
        }

        $finDetalle = max($row, $rowDetalle - 1);
        $sheet->getStyle("A{$row}:G{$finDetalle}")->applyFromArray($this->styleTableBorders());
        if ($finDetalle > $row) {
            $sheet->getStyle("G" . ($row + 1) . ":G{$finDetalle}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        for ($i = 1; $i <= Coordinate::columnIndexFromString($lastColumn); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $sheet->freezePane('A' . ($row + 1));
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle("A1:G{$finDetalle}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $writer = new Xlsx($spreadsheet);
        $filename = 'REPORTE_INGRESOS_' . now()->format('d-m-Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function egresos(Request $request)
    {
        [$anios, $anio, $anioId] = $this->resolverAnio($request);
        $filtros = $this->normalizarFiltrosEgresos($request, $anio);
        $dataset = $this->construirDatasetEgresos($anioId, $filtros);

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $itemsPagina = $dataset['movimientos']->slice(($page - 1) * $perPage, $perPage)->values();
        $movimientosPaginados = new LengthAwarePaginator(
            $itemsPagina,
            $dataset['movimientos']->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.finanzas.egresos', [
            'anios' => $anios,
            'anio' => $anio,
            'anioId' => $anioId,
            'filtros' => $filtros,
            'kpis' => $dataset['kpis'],
            'movimientos' => $movimientosPaginados,
            'totalMovimientos' => $dataset['movimientos']->count(),
            'tiposEgreso' => self::EGRESO_TIPOS,
            'categoriasEgreso' => self::EGRESO_CATEGORIAS,
            'metodosEgreso' => self::EGRESO_METODOS,
            'tiposComprobanteEgreso' => self::EGRESO_TIPOS_COMPROBANTE,
            'tipoCategoriaMap' => $this->mapaCategoriaPorTipoEgreso(),
            'docentesEgreso' => $this->listarDocentesParaEgreso(),
            'administrativosEgreso' => $this->listarAdministrativosParaEgreso(),
        ]);
    }

    public function registrarEgreso(Request $request)
    {
        [, $anio, $anioId] = $this->resolverAnio($request);

        $data = $request->validate([
            'anio_escolar_id' => ['nullable', 'integer', 'exists:anios_escolares,id'],
            'subtipo' => ['required', 'string', Rule::in(array_keys(self::EGRESO_TIPOS))],
            'concepto' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', Rule::in(array_keys(self::EGRESO_CATEGORIAS))],
            'metodo_pago' => ['required', 'string', Rule::in(self::EGRESO_METODOS)],
            'tipo_comprobante' => ['required', 'string', Rule::in(self::EGRESO_TIPOS_COMPROBANTE)],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'fecha' => ['required', 'date'],
            'nro_comprobante' => ['nullable', 'string', 'max:100'],
            'docente_curso' => ['nullable', 'required_if:subtipo,pago_docente,salario_administrativo', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:1200'],
            'redirect_fecha_inicio' => ['nullable', 'date'],
            'redirect_fecha_fin' => ['nullable', 'date'],
            'redirect_categoria' => ['nullable', 'string'],
            'redirect_buscar' => ['nullable', 'string', 'max:120'],
        ], [
            'docente_curso.required_if' => 'Para pago de docente o salario administrativo debes seleccionar personal.',
        ]);

        $anioRegistro = $data['anio_escolar_id'] ?? $anioId ?? $anio?->id;
        $nroComprobante = $this->normalizarTextoNullable($data['nro_comprobante'] ?? null, 100);
        $docenteCurso = $this->normalizarTextoNullable($data['docente_curso'] ?? null, 255);
        $observaciones = $this->normalizarTextoNullable($data['observaciones'] ?? null, 1200);

        IngresoEgreso::create([
            'tipo' => 'egreso',
            'subtipo' => $this->normalizarTipoEgreso((string) $data['subtipo']),
            'categoria' => $this->normalizarCategoriaEgreso((string) ($data['categoria'] ?? 'sin_categoria')),
            'concepto' => trim((string) $data['concepto']),
            'monto' => round((float) $data['monto'], 2),
            'fecha' => Carbon::parse((string) $data['fecha'])->toDateString(),
            'usuario_id' => $request->user()?->id,
            'anio_escolar_id' => $anioRegistro,
            'metodo_pago' => $this->normalizarMetodoEgreso((string) $data['metodo_pago']),
            'tipo_comprobante' => $this->normalizarTipoComprobanteEgreso((string) $data['tipo_comprobante']),
            'nro_comprobante' => $nroComprobante,
            'referencia' => $nroComprobante,
            'docente_curso' => $docenteCurso,
            'observaciones' => $observaciones,
            'estado' => 'registrado',
        ]);

        $query = array_filter([
            'anio_escolar_id' => $anioRegistro,
            'fecha_inicio' => $data['redirect_fecha_inicio'] ?? null,
            'fecha_fin' => $data['redirect_fecha_fin'] ?? null,
            'categoria' => $data['redirect_categoria'] ?? null,
            'buscar' => $data['redirect_buscar'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        return redirect()
            ->route('admin-sistema.finanzas.egresos', $query)
            ->with('success', 'Egreso registrado correctamente.');
    }

    public function exportarEgresosCsv(Request $request)
    {
        [, $anio, $anioId] = $this->resolverAnio($request);
        $filtros = $this->normalizarFiltrosEgresos($request, $anio);
        $dataset = $this->construirDatasetEgresos($anioId, $filtros);

        $filename = 'REPORTE_EGRESOS_' . now()->format('d-m-Y') . '.csv';

        return response()->streamDownload(function () use ($dataset) {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, [
                'Fecha',
                'Concepto',
                'Categoria',
                'Tipo',
                'Docente / Curso',
                'Monto',
                'Metodo',
                'Tipo comprobante',
                'Nro comprobante',
                'Estado',
            ]);

            foreach ($dataset['movimientos'] as $mov) {
                $fecha = '-';
                try {
                    $fecha = !empty($mov['fecha']) ? Carbon::parse((string) $mov['fecha'])->format('d/m/Y') : '-';
                } catch (\Throwable $e) {
                    $fecha = (string) ($mov['fecha'] ?? '-');
                }

                fputcsv($output, [
                    $fecha,
                    (string) ($mov['concepto'] ?? '-'),
                    (string) ($mov['categoria_label'] ?? '-'),
                    (string) ($mov['tipo_label'] ?? '-'),
                    (string) ($mov['docente_curso'] ?? '-'),
                    number_format((float) ($mov['monto'] ?? 0), 2, '.', ''),
                    (string) ($mov['metodo_pago'] ?? '-'),
                    (string) ($mov['tipo_comprobante'] ?? '-'),
                    (string) ($mov['nro_comprobante'] ?? '-'),
                    (string) ($mov['estado_label'] ?? '-'),
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function anularEgreso(Request $request, IngresoEgreso $egreso)
    {
        if ((string) $egreso->tipo !== 'egreso') {
            abort(404);
        }

        $data = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'min:6', 'max:255'],
        ]);

        if ((string) $egreso->estado === 'anulado') {
            return redirect()
                ->back()
                ->with('warning', 'El egreso ya estaba anulado.');
        }

        $egreso->fill([
            'estado' => 'anulado',
            'anulado_motivo' => trim((string) $data['motivo_anulacion']),
            'anulado_por' => $request->user()?->id,
            'anulado_en' => now(),
        ])->save();

        return redirect()
            ->back()
            ->with('success', 'Egreso anulado correctamente.');
    }

    public function flujoCaja(Request $request)
    {
        [$anios, $anio, $anioId] = $this->resolverAnio($request);

        $mesActual  = (int) $request->input('mes', now()->month);
        $anioNum    = (int) $request->input('anio_num', now()->year);
        if ($mesActual < 1 || $mesActual > 12) $mesActual = now()->month;

        /* ── Ingresos por pagos del anio escolar ── */
        $pagosBase = \App\Models\Pago::query()
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId));

        $ingresosConfirmados = (clone $pagosBase)->where('estado', 'confirmado')->sum('monto');
        $ingresosPendientes  = (clone $pagosBase)->where('estado', 'pendiente')->sum('monto');
        $pagosCount          = (clone $pagosBase)->where('estado', 'confirmado')->count();

        /* ── Egresos registrados ── */
        $egresosBase = \App\Models\IngresoEgreso::where('tipo', 'egreso')
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId));

        $egresosMonto = (clone $egresosBase)->where('estado', 'registrado')->sum('monto');
        $egresosCount = (clone $egresosBase)->where('estado', 'registrado')->count();

        /* ── Ventas de inventario ── */
        $ventasMonto = \App\Models\InventarioVenta::sum('total');

        /* ── Deudas ── */
        $deudasPendientes = \App\Models\Deuda::query()
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId))
            ->where('estado', 'pendiente')
            ->sum('saldo');

        $deudasParciales = \App\Models\Deuda::query()
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId))
            ->where('estado', 'parcial')
            ->sum('saldo');

        /* ── Balance neto ── */
        $totalIngresos = (float)$ingresosConfirmados + (float)$ventasMonto;
        $balanceNeto   = $totalIngresos - (float)$egresosMonto;
        $totalDeudas   = (float)$deudasPendientes + (float)$deudasParciales;
        $tasaCobranza  = ($totalIngresos + $totalDeudas) > 0
            ? round($totalIngresos / ($totalIngresos + $totalDeudas) * 100, 1)
            : 0;

        /* ── Ingresos diarios del mes seleccionado ── */
        $ingresosDiarios = (clone $pagosBase)
            ->where('estado', 'confirmado')
            ->whereMonth('fecha_pago', $mesActual)
            ->whereYear('fecha_pago', $anioNum)
            ->selectRaw('DATE(fecha_pago) as dia, SUM(monto) as total, COUNT(*) as cant')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        /* ── Egresos diarios del mes seleccionado ── */
        $egresosDiarios = (clone $egresosBase)
            ->where('estado', 'registrado')
            ->whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioNum)
            ->selectRaw('DATE(fecha) as dia, SUM(monto) as total, COUNT(*) as cant')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        /* ── Pagos por método ── */
        $pagosPorMetodo = (clone $pagosBase)
            ->where('estado', 'confirmado')
            ->selectRaw('metodo, SUM(monto) as total, COUNT(*) as cant')
            ->groupBy('metodo')
            ->orderByDesc('total')
            ->get();

        /* ── Deudas por categoría de periodo ── */
        $deudasPorCategoria = \App\Models\Deuda::query()
            ->when($anioId, fn ($q) => $q->where('anio_escolar_id', $anioId))
            ->where('estado', 'pendiente')
            ->selectRaw("
                CASE
                    WHEN periodo LIKE 'Pensión%'   THEN 'Pensiones'
                    WHEN periodo LIKE 'Matricula%' THEN 'Matrícula'
                    ELSE 'Otros'
                END as categoria,
                COUNT(*) as c,
                SUM(saldo) as monto
            ")
            ->groupBy('categoria')
            ->orderByDesc('monto')
            ->get();

        /* ── Egresos por categoría ── */
        $egresosPorCategoria = (clone $egresosBase)
            ->where('estado', 'registrado')
            ->selectRaw('COALESCE(subtipo, "otros_gastos") as subtipo, SUM(monto) as total')
            ->groupBy('subtipo')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        /* ── Movimientos recientes del mes ── */
        $movimientosRecientes = (clone $pagosBase)
            ->where('estado', 'confirmado')
            ->whereMonth('fecha_pago', $mesActual)
            ->whereYear('fecha_pago', $anioNum)
            ->orderByDesc('fecha_pago')
            ->limit(10)
            ->get();

        /* ── Resumen mensual: ingresos mes a mes ── */
        $resumenMensual = (clone $pagosBase)
            ->where('estado', 'confirmado')
            ->selectRaw('MONTH(fecha_pago) as mes, SUM(monto) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        return view('admin.finanzas.flujo-caja', [
            'anios'               => $anios,
            'anio'                => $anio,
            'anioId'              => $anioId,
            'mesActual'           => $mesActual,
            'anioNum'             => $anioNum,
            'ingresosConfirmados' => $ingresosConfirmados,
            'ingresosPendientes'  => $ingresosPendientes,
            'pagosCount'          => $pagosCount,
            'egresosMonto'        => $egresosMonto,
            'egresosCount'        => $egresosCount,
            'ventasMonto'         => $ventasMonto,
            'deudasPendientes'    => $deudasPendientes,
            'deudasParciales'     => $deudasParciales,
            'totalIngresos'       => $totalIngresos,
            'balanceNeto'         => $balanceNeto,
            'totalDeudas'         => $totalDeudas,
            'tasaCobranza'        => $tasaCobranza,
            'ingresosDiarios'     => $ingresosDiarios,
            'egresosDiarios'      => $egresosDiarios,
            'pagosPorMetodo'      => $pagosPorMetodo,
            'deudasPorCategoria'  => $deudasPorCategoria,
            'egresosPorCategoria' => $egresosPorCategoria,
            'movimientosRecientes'=> $movimientosRecientes,
            'resumenMensual'      => $resumenMensual,
            'meses'               => self::MESES,
        ]);
    }

    private function resolverAnio(Request $request): array
    {
        $anios = AnioEscolar::orderBy('fecha_inicio', 'desc')->get();
        $anioActivo = $anios->firstWhere('activo', true);
        $anioSolicitado = $request->filled('anio_escolar_id') ? (int) $request->input('anio_escolar_id') : null;
        $anio = $anioSolicitado
            ? $anios->firstWhere('id', $anioSolicitado)
            : ($anioActivo ?? $anios->first());

        return [$anios, $anio, $anio?->id];
    }

    private function normalizarFiltrosIngresos(Request $request, ?AnioEscolar $anio): array
    {
        $fechaInicioInput = trim((string) $request->input('fecha_inicio', ''));
        $fechaFinInput = trim((string) $request->input('fecha_fin', ''));

        if ($fechaInicioInput === '' && $anio?->fecha_inicio) {
            $fechaInicioInput = Carbon::parse($anio->fecha_inicio)->format('Y-m-d');
        }
        if ($fechaFinInput === '' && $anio?->fecha_fin) {
            $fechaFinInput = Carbon::parse($anio->fecha_fin)->format('Y-m-d');
        }

        $fechaInicio = $fechaInicioInput !== '' ? Carbon::parse($fechaInicioInput)->startOfDay() : null;
        $fechaFin = $fechaFinInput !== '' ? Carbon::parse($fechaFinInput)->endOfDay() : null;

        if ($fechaInicio && $fechaFin && $fechaFin->lt($fechaInicio)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
            $fechaInicioInput = $fechaInicio->format('Y-m-d');
            $fechaFinInput = $fechaFin->format('Y-m-d');
        }

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

        $categoria = Str::lower(trim((string) $request->input('categoria', 'todas')));
        if (!in_array($categoria, self::CATEGORIAS_VALIDAS, true)) {
            $categoria = 'todas';
        }

        return [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_inicio_input' => $fechaInicioInput,
            'fecha_fin_input' => $fechaFinInput,
            'mes_desde' => $mesDesde,
            'mes_hasta' => $mesHasta,
            'categoria' => $categoria,
        ];
    }

    private function construirDatasetIngresos(?int $anioId, array $filtros): array
    {
        $aplicacionesPago = DB::table('deuda_pagos as dp')
            ->join('deudas as d', 'd.id', '=', 'dp.deuda_id')
            ->join('pagos as p', 'p.id', '=', 'dp.pago_id')
            ->leftJoin('conceptos_pago as cp', 'cp.id', '=', 'd.concepto_pago_id')
            ->leftJoin('matriculas as m', 'm.id', '=', 'd.matricula_id')
            ->leftJoin('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
            ->where('dp.monto_aplicado', '>', 0)
            ->where('p.estado', 'confirmado')
            ->when($anioId, fn ($q) => $q->where('p.anio_escolar_id', $anioId))
            ->when($filtros['fecha_inicio'], fn ($q, $fi) => $q->whereDate('p.fecha_pago', '>=', $fi->toDateString()))
            ->when($filtros['fecha_fin'], fn ($q, $ff) => $q->whereDate('p.fecha_pago', '<=', $ff->toDateString()))
            ->when($filtros['mes_desde'], fn ($q, $mes) => $q->whereMonth('p.fecha_pago', '>=', $mes))
            ->when($filtros['mes_hasta'], fn ($q, $mes) => $q->whereMonth('p.fecha_pago', '<=', $mes))
            ->orderByDesc('p.fecha_pago')
            ->orderByDesc('p.id')
            ->get([
                'p.id as pago_id',
                'p.fecha_pago',
                'p.metodo',
                'd.periodo',
                'cp.nombre as concepto_nombre',
                'e.dni as estudiante_dni',
                'e.nombres as estudiante_nombres',
                'e.apellidos as estudiante_apellidos',
                'dp.monto_aplicado',
            ]);

        $movimientosPagos = $aplicacionesPago->map(function ($row) {
            $categoria = $this->clasificarIngresoDesdeDeuda(
                (string) ($row->periodo ?? ''),
                (string) ($row->concepto_nombre ?? '')
            );

            $dniEstudiante = trim((string) ($row->estudiante_dni ?? ''));
            $nombreEstudiante = trim((string) ($row->estudiante_apellidos ?? '') . ' ' . (string) ($row->estudiante_nombres ?? ''));
            $persona = $nombreEstudiante !== '' ? $nombreEstudiante : '-';
            if ($nombreEstudiante !== '' && $dniEstudiante !== '') {
                $persona = 'DNI ' . $dniEstudiante . ' - ' . $nombreEstudiante;
            }

            return [
                'fecha' => (string) ($row->fecha_pago ?? ''),
                'categoria' => $categoria,
                'categoria_label' => $this->labelCategoriaIngreso($categoria),
                'concepto' => (string) ($row->periodo ?: ($row->concepto_nombre ?: 'Pago')),
                'persona' => $persona,
                'metodo' => $this->normalizarMetodoPago((string) ($row->metodo ?? '')),
                'fuente' => 'Pagos',
                'monto' => (float) ($row->monto_aplicado ?? 0),
                'orden' => sprintf('%s-%010d-p', (string) ($row->fecha_pago ?? ''), (int) ($row->pago_id ?? 0)),
            ];
        });

        $ventasInventario = InventarioVenta::query()
            ->with('estudiante:id,dni,nombres,apellidos')
            ->whereIn('estado', ['pagado', 'confirmado'])
            ->when($filtros['fecha_inicio'], fn ($q, $fi) => $q->whereDate('fecha_venta', '>=', $fi->toDateString()))
            ->when($filtros['fecha_fin'], fn ($q, $ff) => $q->whereDate('fecha_venta', '<=', $ff->toDateString()))
            ->when($filtros['mes_desde'], fn ($q, $mes) => $q->whereMonth('fecha_venta', '>=', $mes))
            ->when($filtros['mes_hasta'], fn ($q, $mes) => $q->whereMonth('fecha_venta', '<=', $mes))
            ->orderByDesc('fecha_venta')
            ->orderByDesc('id')
            ->get();

        $movimientosVentas = $ventasInventario->map(function (InventarioVenta $venta) {
            $persona = '-';
            if ($venta->estudiante) {
                $nombreEstudiante = trim((string) $venta->estudiante->apellidos . ' ' . (string) $venta->estudiante->nombres);
                $dniEstudiante = trim((string) ($venta->estudiante->dni ?? ''));
                $persona = $nombreEstudiante !== '' ? $nombreEstudiante : '-';
                if ($nombreEstudiante !== '' && $dniEstudiante !== '') {
                    $persona = 'DNI ' . $dniEstudiante . ' - ' . $nombreEstudiante;
                }
            } elseif (!empty($venta->comprador_nombre)) {
                $persona = (string) $venta->comprador_nombre;
            }

            return [
                'fecha' => (string) optional($venta->fecha_venta)->format('Y-m-d H:i:s'),
                'categoria' => 'ventas',
                'categoria_label' => $this->labelCategoriaIngreso('ventas'),
                'concepto' => !empty($venta->voucher_codigo) ? ('Venta #' . $venta->voucher_codigo) : 'Venta de productos',
                'persona' => $persona,
                'metodo' => $this->normalizarMetodoPago((string) ($venta->metodo_pago ?? '')),
                'fuente' => 'Productos',
                'monto' => (float) ($venta->total ?? 0),
                'orden' => sprintf('%s-%010d-v', (string) optional($venta->fecha_venta)->format('Y-m-d H:i:s'), (int) $venta->id),
            ];
        });

        $movimientosBase = $movimientosPagos
            ->concat($movimientosVentas)
            ->sortByDesc('orden')
            ->values();

        $movimientosFiltrados = $movimientosBase;
        if ($filtros['categoria'] !== 'todas') {
            $movimientosFiltrados = $movimientosFiltrados
                ->where('categoria', $filtros['categoria'])
                ->values();
        }

        $totales = [
            'matriculas' => round((float) $movimientosFiltrados->where('categoria', 'matriculas')->sum('monto'), 2),
            'pensiones' => round((float) $movimientosFiltrados->where('categoria', 'pensiones')->sum('monto'), 2),
            'ventas' => round((float) $movimientosFiltrados->where('categoria', 'ventas')->sum('monto'), 2),
        ];
        $totales['general'] = round($totales['matriculas'] + $totales['pensiones'] + $totales['ventas'], 2);

        return [
            'totales' => $totales,
            'resumen_mensual' => $this->construirResumenMensual($movimientosFiltrados, $filtros),
            'movimientos' => $movimientosFiltrados,
        ];
    }

    private function construirResumenMensual(Collection $movimientos, array $filtros): array
    {
        $mesInicio = $filtros['mes_desde'] ?: 1;
        $mesFin = $filtros['mes_hasta'] ?: 12;
        if ($mesFin < $mesInicio) {
            [$mesInicio, $mesFin] = [$mesFin, $mesInicio];
        }

        $resumen = [];
        for ($mes = $mesInicio; $mes <= $mesFin; $mes++) {
            $resumen[$mes] = [
                'mes' => $mes,
                'mes_label' => self::MESES[$mes] ?? ('Mes ' . $mes),
                'matriculas' => 0.0,
                'pensiones' => 0.0,
                'ventas' => 0.0,
                'total' => 0.0,
            ];
        }

        foreach ($movimientos as $mov) {
            $mes = null;
            try {
                $mes = !empty($mov['fecha']) ? (int) Carbon::parse((string) $mov['fecha'])->format('n') : null;
            } catch (\Throwable $e) {
                $mes = null;
            }

            if ($mes === null || !isset($resumen[$mes])) {
                continue;
            }

            $categoria = (string) ($mov['categoria'] ?? '');
            $monto = (float) ($mov['monto'] ?? 0);

            if ($categoria === 'matriculas') {
                $resumen[$mes]['matriculas'] += $monto;
            } elseif ($categoria === 'pensiones') {
                $resumen[$mes]['pensiones'] += $monto;
            } else {
                $resumen[$mes]['ventas'] += $monto;
            }

            $resumen[$mes]['total'] += $monto;
        }

        foreach ($resumen as &$fila) {
            $fila['matriculas'] = round((float) $fila['matriculas'], 2);
            $fila['pensiones'] = round((float) $fila['pensiones'], 2);
            $fila['ventas'] = round((float) $fila['ventas'], 2);
            $fila['total'] = round((float) $fila['total'], 2);
        }
        unset($fila);

        return array_values($resumen);
    }

    private function clasificarIngresoDesdeDeuda(string $periodo, string $concepto): string
    {
        $periodoNorm = Str::upper(Str::ascii(trim($periodo)));
        $conceptoNorm = Str::upper(Str::ascii(trim($concepto)));
        $texto = trim($periodoNorm . ' ' . $conceptoNorm);

        if (Str::startsWith($periodoNorm, 'PRODUCTO')) {
            return 'ventas';
        }

        if (Str::contains($texto, 'PENSION') || Str::contains($texto, 'VACACIONAL')) {
            return 'pensiones';
        }

        if (Str::contains($texto, 'MATRICULA')) {
            return 'matriculas';
        }

        return 'ventas';
    }

    private function labelCategoriaIngreso(string $categoria): string
    {
        return match ($categoria) {
            'matriculas' => 'Matriculas',
            'pensiones' => 'Pensiones',
            default => 'Ventas',
        };
    }

    private function normalizarMetodoPago(string $metodo): string
    {
        $metodo = trim($metodo);
        if ($metodo === '') {
            return '-';
        }

        $metodoNorm = Str::lower(Str::ascii($metodo));

        return match ($metodoNorm) {
            'efectivo' => 'Efectivo',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transferencia', 'transferencia bancaria' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            default => Str::title($metodo),
        };
    }

    private function labelFiltrosExport(array $filtros): string
    {
        $partes = [];
        $partes[] = 'Categoria: ' . ($filtros['categoria'] === 'todas' ? 'Todas' : $this->labelCategoriaIngreso($filtros['categoria']));
        $partes[] = 'Fechas: ' . (($filtros['fecha_inicio_input'] !== '' ? $filtros['fecha_inicio_input'] : '-') . ' a ' . ($filtros['fecha_fin_input'] !== '' ? $filtros['fecha_fin_input'] : '-'));

        if ($filtros['mes_desde'] || $filtros['mes_hasta']) {
            $mesDesde = $filtros['mes_desde'] ? (self::MESES[$filtros['mes_desde']] ?? '-') : '-';
            $mesHasta = $filtros['mes_hasta'] ? (self::MESES[$filtros['mes_hasta']] ?? '-') : '-';
            $partes[] = 'Meses: ' . $mesDesde . ' a ' . $mesHasta;
        }

        return implode(' | ', $partes);
    }

    private function normalizarFiltrosEgresos(Request $request, ?AnioEscolar $anio): array
    {
        $fechaInicioInput = trim((string) $request->input('fecha_inicio', ''));
        $fechaFinInput = trim((string) $request->input('fecha_fin', ''));

        if ($fechaInicioInput === '' && $anio?->fecha_inicio) {
            $fechaInicioInput = Carbon::parse($anio->fecha_inicio)->format('Y-m-d');
        }
        if ($fechaFinInput === '' && $anio?->fecha_fin) {
            $fechaFinInput = Carbon::parse($anio->fecha_fin)->format('Y-m-d');
        }

        $fechaInicio = null;
        $fechaFin = null;

        if ($fechaInicioInput !== '') {
            try {
                $fechaInicio = Carbon::parse($fechaInicioInput)->startOfDay();
                $fechaInicioInput = $fechaInicio->format('Y-m-d');
            } catch (\Throwable $e) {
                $fechaInicioInput = '';
            }
        }
        if ($fechaFinInput !== '') {
            try {
                $fechaFin = Carbon::parse($fechaFinInput)->endOfDay();
                $fechaFinInput = $fechaFin->format('Y-m-d');
            } catch (\Throwable $e) {
                $fechaFinInput = '';
            }
        }

        if ($fechaInicio && $fechaFin && $fechaFin->lt($fechaInicio)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
            $fechaInicioInput = $fechaInicio->format('Y-m-d');
            $fechaFinInput = $fechaFin->format('Y-m-d');
        }

        $categoria = Str::lower(trim((string) $request->input('categoria', 'todas')));
        $categoriasValidas = array_merge(['todas'], array_keys(self::EGRESO_CATEGORIAS));
        if (!in_array($categoria, $categoriasValidas, true)) {
            $categoria = 'todas';
        }

        $buscar = trim((string) $request->input('buscar', ''));
        if (mb_strlen($buscar) > 120) {
            $buscar = mb_substr($buscar, 0, 120);
        }

        return [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_inicio_input' => $fechaInicioInput,
            'fecha_fin_input' => $fechaFinInput,
            'categoria' => $categoria,
            'buscar' => $buscar,
        ];
    }

    private function construirDatasetEgresos(?int $anioId, array $filtros): array
    {
        $movimientos = IngresoEgreso::query()
            ->where('tipo', 'egreso')
            ->when($anioId, fn ($q, $id) => $q->where('anio_escolar_id', $id))
            ->when($filtros['fecha_inicio'], fn ($q, $fi) => $q->whereDate('fecha', '>=', $fi->toDateString()))
            ->when($filtros['fecha_fin'], fn ($q, $ff) => $q->whereDate('fecha', '<=', $ff->toDateString()))
            ->when(
                $filtros['categoria'] !== 'todas',
                fn ($q) => $q->where('categoria', $filtros['categoria'])
            )
            ->when($filtros['buscar'] !== '', function ($q) use ($filtros) {
                $buscar = $filtros['buscar'];
                $q->where(function ($subQ) use ($buscar) {
                    $subQ->where('concepto', 'like', '%' . $buscar . '%')
                        ->orWhere('nro_comprobante', 'like', '%' . $buscar . '%')
                        ->orWhere('referencia', 'like', '%' . $buscar . '%')
                        ->orWhere('docente_curso', 'like', '%' . $buscar . '%');
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->map(function (IngresoEgreso $item) {
                $categoria = $this->normalizarCategoriaEgreso((string) ($item->categoria ?? 'sin_categoria'));
                $tipo = $this->normalizarTipoEgreso((string) ($item->subtipo ?? 'otros_gastos'));
                $estado = Str::lower(trim((string) ($item->estado ?? 'registrado')));
                $estado = $estado === 'anulado' ? 'anulado' : 'registrado';
                $nroComprobante = trim((string) ($item->nro_comprobante ?: $item->referencia ?: ''));

                return [
                    'id' => (int) $item->id,
                    'fecha' => (string) optional($item->fecha)->format('Y-m-d'),
                    'concepto' => (string) ($item->concepto ?? '-'),
                    'categoria' => $categoria,
                    'categoria_label' => $this->labelCategoriaEgreso($categoria),
                    'tipo' => $tipo,
                    'tipo_label' => $this->labelTipoEgreso($tipo),
                    'docente_curso' => trim((string) ($item->docente_curso ?? '')) ?: '-',
                    'monto' => (float) ($item->monto ?? 0),
                    'metodo_pago' => $this->normalizarMetodoPago((string) ($item->metodo_pago ?? '')),
                    'tipo_comprobante' => $this->normalizarTipoComprobanteEgreso((string) ($item->tipo_comprobante ?? 'Recibo')),
                    'nro_comprobante' => $nroComprobante !== '' ? $nroComprobante : '-',
                    'estado' => $estado,
                    'estado_label' => $this->labelEstadoEgreso($estado),
                    'anulado_motivo' => trim((string) ($item->anulado_motivo ?? '')),
                ];
            })
            ->values();

        $movimientosActivos = $movimientos->where('estado', 'registrado')->values();
        $totalPeriodo = round((float) $movimientosActivos->sum('monto'), 2);
        $mayorGasto = round((float) ($movimientosActivos->max('monto') ?? 0), 2);

        $hoy = now();
        $totalMes = round((float) $movimientosActivos->filter(function (array $mov) use ($hoy) {
            if (empty($mov['fecha'])) {
                return false;
            }

            try {
                $fecha = Carbon::parse((string) $mov['fecha']);
            } catch (\Throwable $e) {
                return false;
            }

            return (int) $fecha->format('n') === (int) $hoy->format('n')
                && (int) $fecha->format('Y') === (int) $hoy->format('Y');
        })->sum('monto'), 2);

        $diasPeriodo = max(1, (int) $hoy->daysInMonth);
        if ($filtros['fecha_inicio'] && $filtros['fecha_fin']) {
            $diasPeriodo = max(1, $filtros['fecha_inicio']->diffInDays($filtros['fecha_fin']) + 1);
        } elseif ($filtros['fecha_inicio']) {
            $diasPeriodo = max(1, $filtros['fecha_inicio']->diffInDays($hoy) + 1);
        }

        return [
            'kpis' => [
                'total_periodo' => $totalPeriodo,
                'transacciones' => (int) $movimientosActivos->count(),
                'este_mes' => $totalMes,
                'promedio_dia' => round($totalPeriodo / max(1, $diasPeriodo), 2),
                'mayor_gasto' => $mayorGasto,
            ],
            'movimientos' => $movimientos,
        ];
    }

    private function mapaCategoriaPorTipoEgreso(): array
    {
        return [
            'pago_docente' => 'salarios',
            'salario_administrativo' => 'salarios',
            'alquiler_local' => 'servicios',
            'servicios_basicos' => 'servicios',
            'materiales_belleza' => 'materiales',
            'insumos_productos' => 'materiales',
            'equipos_herramientas' => 'operativo',
            'marketing_publicidad' => 'marketing',
            'limpieza_mantenimiento' => 'operativo',
            'impuestos_tributos' => 'impuestos',
            'papeleria_utiles' => 'operativo',
            'alimentacion_personal' => 'logistica',
            'transporte_movilidad' => 'logistica',
            'capacitacion_docente' => 'salarios',
            'uniformes_vestimenta' => 'materiales',
            'seguro_sctr' => 'impuestos',
            'otros_gastos' => 'otros',
        ];
    }

    private function labelCategoriaEgreso(string $categoria): string
    {
        return self::EGRESO_CATEGORIAS[$categoria] ?? self::EGRESO_CATEGORIAS['sin_categoria'];
    }

    private function labelTipoEgreso(string $tipo): string
    {
        return self::EGRESO_TIPOS[$tipo] ?? self::EGRESO_TIPOS['otros_gastos'];
    }

    private function labelEstadoEgreso(string $estado): string
    {
        return $estado === 'anulado' ? 'Anulado' : 'Registrado';
    }

    private function normalizarTipoEgreso(string $tipo): string
    {
        return array_key_exists($tipo, self::EGRESO_TIPOS) ? $tipo : 'otros_gastos';
    }

    private function normalizarCategoriaEgreso(string $categoria): string
    {
        return array_key_exists($categoria, self::EGRESO_CATEGORIAS) ? $categoria : 'sin_categoria';
    }

    private function normalizarMetodoEgreso(string $metodo): string
    {
        $metodoNorm = Str::lower(Str::ascii(trim($metodo)));

        return match ($metodoNorm) {
            'efectivo' => 'Efectivo',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transferencia', 'transferencia bancaria' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            default => 'Efectivo',
        };
    }

    private function normalizarTipoComprobanteEgreso(string $tipoComprobante): string
    {
        $tipoComprobante = trim($tipoComprobante);
        if (in_array($tipoComprobante, self::EGRESO_TIPOS_COMPROBANTE, true)) {
            return $tipoComprobante;
        }

        $normalizado = Str::lower(Str::ascii($tipoComprobante));
        return match ($normalizado) {
            'boleta' => 'Boleta',
            'factura' => 'Factura',
            'voucher' => 'Voucher',
            'sin comprobante' => 'Sin comprobante',
            default => 'Recibo',
        };
    }

    private function normalizarTextoNullable(mixed $texto, int $limite): ?string
    {
        $valor = trim((string) ($texto ?? ''));
        if ($valor === '') {
            return null;
        }

        if (mb_strlen($valor) > $limite) {
            $valor = mb_substr($valor, 0, $limite);
        }

        return $valor;
    }

    private function listarDocentesParaEgreso(): array
    {
        return User::query()
            ->role('Docente')
            ->orderBy('name')
            ->pluck('name')
            ->map(static fn ($name) => trim((string) $name))
            ->filter(static fn (string $name) => $name !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function listarAdministrativosParaEgreso(): array
    {
        return User::query()
            ->whereHas('roles', static function ($query): void {
                $query->whereIn('name', self::ROLES_PERSONAL_ADMINISTRATIVO);
            })
            ->orderBy('name')
            ->pluck('name')
            ->map(static fn ($name) => trim((string) $name))
            ->filter(static fn (string $name) => $name !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function styleHeaderGrey(): array
    {
        return [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];
    }

    private function styleTableBorders(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    }
}
