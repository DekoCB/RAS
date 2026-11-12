<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AsistenciaDocenteReporteController extends Controller
{
    public function indexDirector(Request $request)
    {
        return $this->renderIndex($request, 'director');
    }

    public function exportDirector(Request $request)
    {
        return $this->exportExcel($request, 'director');
    }

    public function indexAdmin(Request $request)
    {
        return $this->renderIndex($request, 'admin');
    }

    public function exportAdmin(Request $request)
    {
        return $this->exportExcel($request, 'admin');
    }

    private function renderIndex(Request $request, string $panel)
    {
        [$filters, $anios] = $this->resolveFilters($request);
        $baseQuery = $this->buildGroupedQuery($filters);

        $asistencias = (clone $baseQuery)
            ->orderByDesc('fecha')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(20)
            ->withQueryString();

        $asistencias->getCollection()->transform(function ($row) {
            $estado = $this->resolveEstado($row);

            $row->fecha_label = $this->formatDate($row->fecha);
            $row->entrada_label = $this->formatTime($row->entrada_at);
            $row->salida_label = $this->formatTime($row->salida_at);
            $row->estado = $estado;
            $row->estado_label = $this->estadoLabel($estado);
            $row->codigo_label = trim((string) ($row->codigo ?? '')) !== ''
                ? $row->codigo
                : ($row->carnet_codigo ?: '-');

            return $row;
        });

        $resumen = $this->buildResumen($baseQuery);
        $config = $this->panelConfig($panel);

        return view('shared.asistencias_docentes.reporte', [
            'panel' => $panel,
            'sidebarPartial' => $config['sidebar'],
            'indexRoute' => $config['index_route'],
            'exportRoute' => $config['export_route'],
            'panelLabel' => $config['label'],
            'asistencias' => $asistencias,
            'filters' => $filters,
            'anios' => $anios,
            'resumen' => $resumen,
        ]);
    }

    private function exportExcel(Request $request, string $panel)
    {
        [$filters, $anios] = $this->resolveFilters($request);
        $config = $this->panelConfig($panel);

        $rows = $this->buildGroupedQuery($filters)
            ->orderByDesc('fecha')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencia Docentes');

        $headers = [
            'Fecha',
            'Docente',
            'DNI',
            'Codigo',
            'Hora entrada',
            'Hora salida',
            'Estado',
            'Observacion',
        ];

        $columnCount = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $filtrosLabel = $this->buildFilterLabel($filters, $anios);

        $sheet->setCellValue('A1', 'REPORTE DE ASISTENCIAS DOCENTES');
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A2', 'Panel: ' . $config['label'] . ' | Generado: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A3', $filtrosLabel);
        $sheet->mergeCells("A3:{$lastColumn}3");

        $sheet->fromArray($headers, null, 'A5');

        $currentRow = 6;
        foreach ($rows as $row) {
            $estado = $this->resolveEstado($row);

            $sheet->fromArray([
                $this->formatDate($row->fecha),
                trim((string) ($row->apellidos . ' ' . $row->nombres)),
                $row->dni ?: '-',
                trim((string) ($row->codigo ?? '')) !== '' ? $row->codigo : ($row->carnet_codigo ?: '-'),
                $this->formatTime($row->entrada_at) ?: '-',
                $this->formatTime($row->salida_at) ?: '-',
                $this->estadoLabel($estado),
                $this->estadoObservacion($estado),
            ], null, "A{$currentRow}");

            $sheet->getStyle("A{$currentRow}:{$lastColumn}{$currentRow}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => $currentRow % 2 === 0 ? 'FFFFFF' : 'FFF7ED'],
                ],
            ]);

            $statusCell = "G{$currentRow}";
            $statusStyle = match ($estado) {
                'completo' => ['bg' => 'DCFCE7', 'text' => '166534'],
                'sin_salida' => ['bg' => 'FEF3C7', 'text' => '92400E'],
                'sin_entrada' => ['bg' => 'FEE2E2', 'text' => '991B1B'],
                default => ['bg' => 'E5E7EB', 'text' => '374151'],
            };

            $sheet->getStyle($statusCell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => $statusStyle['text']],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => $statusStyle['bg']],
                ],
            ]);

            $currentRow++;
        }

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '8B0F14'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '111827'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FDE68A'],
            ],
        ]);

        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '6B7280'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFBEB'],
            ],
        ]);

        $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'B91C1C'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '991B1B'],
                ],
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(22);

        $sheet->setAutoFilter("A5:{$lastColumn}5");
        $sheet->freezePane('A6');

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(45);

        $dataEndRow = max(6, $currentRow - 1);
        $sheet->getStyle("A6:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E6:F{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H6:H{$dataEndRow}")->getAlignment()->setWrapText(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'REPORTE_ASISTENCIA_DOCENTES_' . strtoupper($config['filename_prefix']) . '_' . now()->format('d-m-Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $anios = AnioEscolar::orderBy('nombre', 'desc')->get(['id', 'nombre', 'activo']);

        $anioInput = trim((string) $request->input('anio_escolar_id', ''));
        $anioId = null;
        $selectedAnio = '';

        if ($request->has('anio_escolar_id')) {
            if ($anioInput !== '') {
                $anioId = (int) $anioInput;
                if (!$anios->pluck('id')->contains($anioId)) {
                    $anioId = null;
                    $selectedAnio = '';
                } else {
                    $selectedAnio = (string) $anioId;
                }
            }
        } else {
            $anioDefecto = $anios->firstWhere('activo', true)?->id ?? $anios->first()?->id;
            if ($anioDefecto) {
                $anioId = (int) $anioDefecto;
                $selectedAnio = (string) $anioDefecto;
            }
        }

        $desde = $this->parseDate($request->input('desde'));
        $hasta = $this->parseDate($request->input('hasta'));
        if ($desde && $hasta && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [[
            'anio_escolar_id' => $anioId,
            'anio_escolar_selected' => $selectedAnio,
            'desde' => $desde,
            'hasta' => $hasta,
            'q' => trim((string) $request->input('q', '')),
        ], $anios];
    }

    private function buildGroupedQuery(array $filters): Builder
    {
        return DB::table('asistencias_docentes as ad')
            ->join('docentes as d', 'd.id', '=', 'ad.docente_id')
            ->select([
                'ad.docente_id',
                'ad.fecha',
                'd.nombres',
                'd.apellidos',
                'd.dni',
                'd.codigo',
                'd.carnet_codigo',
            ])
            ->selectRaw("MIN(CASE WHEN ad.tipo = 'ENTRADA' THEN ad.created_at END) as entrada_at")
            ->selectRaw("MAX(CASE WHEN ad.tipo = 'SALIDA' THEN ad.created_at END) as salida_at")
            ->when($filters['anio_escolar_id'], function ($query, $anioId) {
                $query->where('ad.anio_escolar_id', $anioId);
            })
            ->when($filters['desde'], function ($query, $desde) {
                $query->whereDate('ad.fecha', '>=', $desde);
            })
            ->when($filters['hasta'], function ($query, $hasta) {
                $query->whereDate('ad.fecha', '<=', $hasta);
            })
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = '%' . $filters['q'] . '%';
                $query->where(function ($sub) use ($search) {
                    $sub->where('d.nombres', 'like', $search)
                        ->orWhere('d.apellidos', 'like', $search)
                        ->orWhere('d.dni', 'like', $search)
                        ->orWhere('d.codigo', 'like', $search)
                        ->orWhere('d.carnet_codigo', 'like', $search);
                });
            })
            ->groupBy(
                'ad.docente_id',
                'ad.fecha',
                'd.nombres',
                'd.apellidos',
                'd.dni',
                'd.codigo',
                'd.carnet_codigo'
            );
    }

    private function buildResumen(Builder $baseQuery): array
    {
        $summary = DB::query()
            ->fromSub(clone $baseQuery, 'r')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN entrada_at IS NOT NULL AND salida_at IS NOT NULL THEN 1 ELSE 0 END) as completos')
            ->selectRaw('SUM(CASE WHEN entrada_at IS NOT NULL AND salida_at IS NULL THEN 1 ELSE 0 END) as sin_salida')
            ->selectRaw('SUM(CASE WHEN entrada_at IS NULL AND salida_at IS NOT NULL THEN 1 ELSE 0 END) as sin_entrada')
            ->first();

        return [
            'total' => (int) ($summary->total ?? 0),
            'completos' => (int) ($summary->completos ?? 0),
            'sin_salida' => (int) ($summary->sin_salida ?? 0),
            'sin_entrada' => (int) ($summary->sin_entrada ?? 0),
        ];
    }

    private function parseDate($value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatDate($value): string
    {
        if (!$value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function formatTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value, 'UTC')
                ->setTimezone('America/Lima')
                ->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveEstado(object $row): string
    {
        if (!empty($row->entrada_at) && !empty($row->salida_at)) {
            return 'completo';
        }

        if (!empty($row->entrada_at)) {
            return 'sin_salida';
        }

        if (!empty($row->salida_at)) {
            return 'sin_entrada';
        }

        return 'sin_marcas';
    }

    private function estadoLabel(string $estado): string
    {
        return match ($estado) {
            'completo' => 'Completo',
            'sin_salida' => 'Sin salida',
            'sin_entrada' => 'Sin entrada',
            default => 'Sin marcas',
        };
    }

    private function estadoObservacion(string $estado): string
    {
        return match ($estado) {
            'completo' => 'Entrada y salida registradas correctamente.',
            'sin_salida' => 'Falta registrar la salida del docente.',
            'sin_entrada' => 'Falta registrar la entrada del docente.',
            default => 'Sin registros para evaluar.',
        };
    }

    private function panelConfig(string $panel): array
    {
        if ($panel === 'director') {
            return [
                'label' => 'Director',
                'sidebar' => 'director.partials.sidebar',
                'index_route' => 'director.asistencias-docentes.index',
                'export_route' => 'director.asistencias-docentes.excel',
                'filename_prefix' => 'director',
            ];
        }

        return [
            'label' => 'SuperAdmin',
            'sidebar' => 'admin.partials.sidebar',
            'index_route' => 'admin-sistema.asistencias-docentes.index',
            'export_route' => 'admin-sistema.asistencias-docentes.excel',
            'filename_prefix' => 'superadmin',
        ];
    }

    private function buildFilterLabel(array $filters, $anios): string
    {
        $anioLabel = 'Todos';
        if (!empty($filters['anio_escolar_id'])) {
            $anio = $anios->firstWhere('id', (int) $filters['anio_escolar_id']);
            if ($anio) {
                $anioLabel = $anio->nombre;
            }
        }

        $partes = ['Anio escolar: ' . $anioLabel];
        if ($filters['desde']) {
            $partes[] = 'Desde: ' . $this->formatDate($filters['desde']);
        }
        if ($filters['hasta']) {
            $partes[] = 'Hasta: ' . $this->formatDate($filters['hasta']);
        }
        if ($filters['q'] !== '') {
            $partes[] = 'Busqueda: ' . $filters['q'];
        }

        return implode(' | ', $partes);
    }
}
