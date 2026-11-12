<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Horario;
use App\Models\PeriodoAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HorarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:horario.ver')->only(['index']);
    }

    public function index(Request $request)
    {
        [$anios, $anio, $periodos, $periodo] = $this->resolveFiltros($request);

        $horarios = $this->horarioQuery($anio, $periodo, $request->user()->id)
            ->get()
            ->groupBy('dia_semana');

        return view('docente.horarios.index', compact('horarios', 'anio', 'periodo', 'anios', 'periodos'));
    }

    public function excel(Request $request)
    {
        [$anios, $anio, $periodos, $periodo] = $this->resolveFiltros($request);

        $horarios = $this->horarioQuery($anio, $periodo, $request->user()->id)->get();

        $periodoLabel = $periodo ? $periodo->codigo . ' - ' . $periodo->nombre : '-';
        $headers = ['Anio', 'Periodo', 'Dia', 'Hora inicio', 'Hora fin', 'Curso', 'Seccion', 'Nivel/Grado'];
        $rows = [];

        foreach ($horarios as $item) {
            $seccionLabel = trim(
                ($item->seccion?->grado?->nivel ?? '-') . ' ' .
                ($item->seccion?->grado?->nombre ?? '-') . ' - ' .
                ($item->seccion?->nombre ?? '-')
            );

            $rows[] = [
                $anio?->nombre ?? '-',
                $periodoLabel,
                $item->dia_semana ?? '-',
                $item->hora_inicio ?? '-',
                $item->hora_fin ?? '-',
                $item->curso?->nombre ?? '-',
                $item->seccion?->nombre ?? '-',
                $seccionLabel,
            ];
        }

        $docenteNombre = $request->user()->name ?? 'Docente';
        $titulo = 'Horario docente - ' . $docenteNombre;
        if ($anio) {
            $titulo .= ' | ' . $anio->nombre;
        }
        if ($periodo) {
            $titulo .= ' | ' . $periodoLabel;
        }

        $filename = 'HORARIO_DOCENTE_' . now()->format('d-m-Y') . '.xlsx';

        return $this->exportXlsx($titulo, $headers, $rows, $filename);
    }

    private function resolveFiltros(Request $request): array
    {
        $anios = AnioEscolar::orderBy('nombre', 'desc')->get();
        $anioSeleccionado = (int) $request->input('anio_escolar_id');
        $anio = $anios->firstWhere('id', $anioSeleccionado)
            ?? AnioEscolar::where('activo', true)->first()
            ?? $anios->first();

        $periodos = collect();
        $periodo = null;

        if ($anio) {
            $periodos = $anio->periodosAcademicos()->orderBy('orden')->get();
            $periodoId = (int) $request->input('periodo_academico_id');
            $periodo = $periodos->firstWhere('id', $periodoId)
                ?? PeriodoAcademico::actualParaAnio($anio->id);
        }

        return [$anios, $anio, $periodos, $periodo];
    }

    private function horarioQuery(?AnioEscolar $anio, ?PeriodoAcademico $periodo, int $docenteId)
    {
        return Horario::with(['seccion.grado', 'curso'])
            ->when($anio, function ($query) use ($anio) {
                $query->where('anio_escolar_id', $anio->id);
            })
            ->when($periodo && Schema::hasColumn('horarios', 'periodo_academico_id'), function ($query) use ($periodo) {
                $query->where('periodo_academico_id', $periodo->id);
            })
            ->where('docente_id', $docenteId)
            ->orderByRaw("FIELD(dia_semana, 'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')")
            ->orderBy('hora_inicio');
    }

    private function exportXlsx(string $titulo, array $headers, array $rows, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnCount = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells("A1:{$lastColumn}1");
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
                'color' => ['rgb' => '7B1414'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->fromArray($headers, null, 'A3');
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A4');
        }

        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2'],
            ],
        ]);

        $sheet->setAutoFilter("A3:{$lastColumn}3");
        $sheet->freezePane('A4');

        for ($i = 1; $i <= $columnCount; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
