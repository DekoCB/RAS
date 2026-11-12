<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Horario;
use App\Models\HorarioEscolar;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HorarioEscolarController extends Controller
{
    public function index(Request $request)
    {
        $nivel = $request->input('nivel');
        $paso = (int) $request->input('paso', 1);
        if (!in_array($paso, [1, 2], true)) {
            $paso = 1;
        }
        $query = HorarioEscolar::query()
            ->orderByRaw("FIELD(nivel, 'Inicial','Primaria','Secundaria')")
            ->orderBy('nivel');

        $horarios = $nivel
            ? $query->where('nivel', $nivel)->get()
            : $query->get();

        $anios = collect();
        $anio = null;
        $periodos = collect();
        $periodo = null;
        $secciones = collect();
        $seccion = null;
        $cursos = collect();
        $horariosAsignados = collect();
        $docentes = collect();

        if ($nivel) {
            $docentes = User::role('Docente')
                ->whereHas('docente', fn($q) => $q->where('estado', 'activo'))
                ->orderBy('name')->get();
            $anios = AnioEscolar::orderBy('nombre', 'desc')->get();
            $anioSeleccionado = $request->input('anio_escolar_id');
            $anio = $anios->firstWhere('id', (int) $anioSeleccionado)
                ?? AnioEscolar::where('activo', true)->first()
                ?? $anios->first();

            if ($anio) {
                $periodos = $anio->periodosAcademicos()->orderBy('orden')->get();
                $periodoId = (int) $request->input('periodo_academico_id');
                $periodo = $periodos->firstWhere('id', $periodoId)
                    ?? PeriodoAcademico::actualParaAnio($anio->id);

                $secciones = Seccion::with('grado')
                    ->where('anio_escolar_id', $anio->id)
                    ->whereHas('grado', function ($query) use ($nivel) {
                        $query->where('nivel', $nivel);
                    })
                    ->orderBy('grado_id')
                    ->orderBy('nombre')
                    ->get();

                $seccionId = (int) $request->input('seccion_id');
                if ($seccionId) {
                    $seccion = $secciones->firstWhere('id', $seccionId)
                        ?? Seccion::with('grado')
                            ->where('anio_escolar_id', $anio->id)
                            ->where('id', $seccionId)
                            ->first();
                }

                if ($seccion && $periodo) {
                    $cursos = $seccion->cursos()
                        ->wherePivot('anio_escolar_id', $anio->id)
                        ->wherePivot('periodo_academico_id', $periodo->id)
                        ->orderBy('cursos.nombre')
                        ->get();

                    $horariosAsignados = Horario::with('curso')
                        ->where('anio_escolar_id', $anio->id)
                        ->where('seccion_id', $seccion->id)
                        ->when($periodo, function ($query) use ($periodo) {
                            $query->where('periodo_academico_id', $periodo->id);
                        })
                        ->get()
                        ->keyBy(function ($item) {
                            $inicio = Carbon::parse($item->hora_inicio)->format('H:i');
                            return $item->dia_semana . '|' . $inicio;
                        });
                }
            }
        }

        return view('director.horarios.index', compact(
            'horarios',
            'nivel',
            'paso',
            'anios',
            'anio',
            'periodos',
            'periodo',
            'secciones',
            'seccion',
            'cursos',
            'horariosAsignados',
            'docentes'
        ));
    }

    public function update(Request $request, HorarioEscolar $horario)
    {
        $data = $request->validate([
            'hora_inicio' => ['required', 'date_format:H:i'],
            'duracion_clase_min' => ['required', 'integer', 'min:30', 'max:180'],
            'recreo_min' => ['required', 'integer', 'min:0', 'max:120'],
            'clases_por_dia' => ['required', 'integer', 'min:1', 'max:8'],
            'recreo_despues_de' => ['nullable', 'integer', 'min:1', 'max:7'],
        ]);

        if ((int) $data['recreo_min'] <= 0) {
            $data['recreo_despues_de'] = null;
        } else {
            $maxRecreo = max((int) $data['clases_por_dia'] - 1, 1);
            if (empty($data['recreo_despues_de']) || (int) $data['recreo_despues_de'] > $maxRecreo) {
                return back()->withErrors([
                    'recreo_despues_de' => 'El recreo debe estar entre la clase 1 y ' . $maxRecreo . '.',
                ])->withInput();
            }
        }

        $horario->update($data);

        $prefix = $this->routePrefix();
        return redirect()
            ->route($prefix . 'horarios.index', ['nivel' => $horario->nivel])
            ->with('status', 'Horario actualizado.');
    }

    public function programar(Request $request)
    {
        $data = $request->validate([
            'nivel' => ['required', 'in:Inicial,Primaria,Secundaria'],
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id' => ['required', 'exists:periodos_academicos,id'],
            'seccion_id' => ['required', 'exists:secciones,id'],
            'horarios' => ['array'],
            'horarios.*' => ['array'],
            'horarios.*.*' => ['nullable', 'integer', 'exists:cursos,id'],
            'docentes' => ['array'],
            'docentes.*' => ['array'],
            'docentes.*.*' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $anio = AnioEscolar::findOrFail($data['anio_escolar_id']);
        $periodo = PeriodoAcademico::where('id', $data['periodo_academico_id'])
            ->where('anio_escolar_id', $anio->id)
            ->firstOrFail();
        $seccion = Seccion::with('grado')
            ->where('anio_escolar_id', $anio->id)
            ->where('id', $data['seccion_id'])
            ->firstOrFail();

        if ($seccion->grado?->nivel && $seccion->grado->nivel !== $data['nivel']) {
            return back()->withErrors([
                'seccion_id' => 'La secci&oacute;n seleccionada no corresponde al nivel.',
            ])->withInput();
        }

        $horarioNivel = HorarioEscolar::where('nivel', $data['nivel'])->firstOrFail();

        $inicio = Carbon::parse($horarioNivel->hora_inicio);
        $duracion = (int) $horarioNivel->duracion_clase_min;
        $recreo = (int) $horarioNivel->recreo_min;
        $clases = (int) $horarioNivel->clases_por_dia;
        $recreoDespues = (int) ($horarioNivel->recreo_despues_de ?? 0);
        $slots = [];
        $cursor = $inicio->copy();

        for ($i = 1; $i <= $clases; $i++) {
            $finClase = $cursor->copy()->addMinutes($duracion);
            $slots[] = [
                'tipo' => 'Clase ' . $i,
                'inicio' => $cursor->format('H:i'),
                'fin' => $finClase->format('H:i'),
            ];
            $cursor = $finClase;

            if ($recreo > 0 && $recreoDespues === $i) {
                $finRecreo = $cursor->copy()->addMinutes($recreo);
                $slots[] = [
                    'tipo' => 'Recreo',
                    'inicio' => $cursor->format('H:i'),
                    'fin' => $finRecreo->format('H:i'),
                ];
                $cursor = $finRecreo;
            }
        }

        $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

        $cursos = $seccion->cursos()
            ->wherePivot('anio_escolar_id', $anio->id)
            ->wherePivot('periodo_academico_id', $periodo->id)
            ->get();

        $docentesPorCurso = $cursos->mapWithKeys(function ($curso) {
            return [$curso->id => $curso->pivot?->docente_id];
        })->all();

        $cursoIds = $cursos->pluck('id')->all();
        $docentesPermitidos = User::role('Docente')
            ->whereHas('docente', fn($q) => $q->where('estado', 'activo'))
            ->pluck('id')->all();

        foreach ($slots as $slot) {
            if ($slot['tipo'] === 'Recreo') {
                continue;
            }

            foreach ($dias as $dia) {
                $cursoId = Arr::get($data, "horarios.$dia.{$slot['inicio']}");
                if ($cursoId && !in_array((int) $cursoId, $cursoIds, true)) {
                    $cursoId = null;
                }

                $docenteIdInput = Arr::get($data, "docentes.$dia.{$slot['inicio']}");
                if ($docenteIdInput && !in_array((int) $docenteIdInput, $docentesPermitidos, true)) {
                    $docenteIdInput = null;
                }

                $where = [
                    'anio_escolar_id' => $anio->id,
                    'periodo_academico_id' => $periodo->id,
                    'seccion_id' => $seccion->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => $slot['inicio'],
                ];

                if ($cursoId) {
                    $docenteAsignado = $docentesPorCurso[$cursoId] ?? null;
                    $docenteId = $docenteIdInput ?: $docenteAsignado;
                    Horario::updateOrCreate($where, [
                        'hora_fin' => $slot['fin'],
                        'curso_id' => $cursoId,
                        'docente_id' => $docenteId,
                    ]);

                    if (!$docenteAsignado && $docenteId) {
                        $seccion->cursos()->updateExistingPivot($cursoId, [
                            'docente_id' => $docenteId,
                            'anio_escolar_id' => $anio->id,
                            'periodo_academico_id' => $periodo->id,
                        ]);
                    }
                } else {
                    Horario::where($where)->delete();
                }
            }
        }

        $prefix = $this->routePrefix();
        return redirect()
            ->route($prefix . 'horarios.index', [
                'nivel' => $data['nivel'],
                'paso' => 2,
                'anio_escolar_id' => $anio->id,
                'periodo_academico_id' => $periodo->id,
                'seccion_id' => $seccion->id,
            ])
            ->with('status', 'Horario guardado correctamente.');
    }

    public function lista(Request $request)
    {
        $anios = AnioEscolar::orderBy('nombre', 'desc')->get();

        $anioSeleccionado = $request->input('anio_escolar_id');
        $anio = $anios->firstWhere('id', (int) $anioSeleccionado)
            ?? AnioEscolar::where('activo', true)->first()
            ?? $anios->first();

        $periodos = collect();
        $periodo = null;
        $nivel = $request->input('nivel');
        $secciones = collect();
        $seccion = null;
        $horarios = collect();

        if ($anio) {
            $periodos = $anio->periodosAcademicos()->orderBy('orden')->get();
            $periodoId = (int) $request->input('periodo_academico_id');
            $periodo = $periodos->firstWhere('id', $periodoId)
                ?? PeriodoAcademico::actualParaAnio($anio->id);

            $seccionesQuery = Seccion::with('grado')
                ->where('anio_escolar_id', $anio->id);

            if ($nivel) {
                $seccionesQuery->whereHas('grado', function ($query) use ($nivel) {
                    $query->where('nivel', $nivel);
                });
            }

            $secciones = $seccionesQuery
                ->orderBy('grado_id')
                ->orderBy('nombre')
                ->get();

            $seccionId = (int) $request->input('seccion_id');
            if ($seccionId) {
                $seccion = $secciones->firstWhere('id', $seccionId)
                    ?? Seccion::with('grado')
                        ->where('anio_escolar_id', $anio->id)
                        ->where('id', $seccionId)
                        ->first();
            }

            $horarios = Horario::with(['seccion.grado', 'curso', 'docente'])
                ->when($anio, function ($query) use ($anio) {
                    $query->where('anio_escolar_id', $anio->id);
                })
                ->when($periodo, function ($query) use ($periodo) {
                    $query->where('periodo_academico_id', $periodo->id);
                })
                ->when($seccion, function ($query) use ($seccion) {
                    $query->where('seccion_id', $seccion->id);
                })
                ->orderBy('seccion_id')
                ->orderByRaw("FIELD(dia_semana, 'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')")
                ->orderBy('hora_inicio')
                ->get();
        }

        $horariosEscolares = HorarioEscolar::all()->keyBy('nivel');
        $seccionesConHorario = $horarios->pluck('seccion_id')->unique()->count();

        return view('director.horarios.lista', compact(
            'anios',
            'anio',
            'periodos',
            'periodo',
            'nivel',
            'secciones',
            'seccion',
            'horarios',
            'seccionesConHorario',
            'horariosEscolares'
        ));
    }

    public function listaExcel(Request $request)
    {
        $anios = AnioEscolar::orderBy('nombre', 'desc')->get();

        $anioSeleccionado = $request->input('anio_escolar_id');
        $anio = $anios->firstWhere('id', (int) $anioSeleccionado)
            ?? AnioEscolar::where('activo', true)->first()
            ?? $anios->first();

        $periodos = collect();
        $periodo = null;
        $nivel = $request->input('nivel');
        $seccion = null;

        if ($anio) {
            $periodos = $anio->periodosAcademicos()->orderBy('orden')->get();
            $periodoId = (int) $request->input('periodo_academico_id');
            $periodo = $periodos->firstWhere('id', $periodoId)
                ?? PeriodoAcademico::actualParaAnio($anio->id);
        }

        $seccionId = (int) $request->input('seccion_id');
        if ($seccionId && $anio) {
            $seccion = Seccion::with('grado')
                ->where('anio_escolar_id', $anio->id)
                ->where('id', $seccionId)
                ->first();
        }

        $horarios = Horario::with(['seccion.grado', 'curso', 'docente', 'periodoAcademico'])
            ->when($anio, function ($query) use ($anio) {
                $query->where('anio_escolar_id', $anio->id);
            })
            ->when($periodo, function ($query) use ($periodo) {
                $query->where('periodo_academico_id', $periodo->id);
            })
            ->when($seccion, function ($query) use ($seccion) {
                $query->where('seccion_id', $seccion->id);
            })
            ->when($nivel, function ($query) use ($nivel) {
                $query->whereHas('seccion.grado', function ($sub) use ($nivel) {
                    $sub->where('nivel', $nivel);
                });
            })
            ->orderBy('seccion_id')
            ->orderByRaw("FIELD(dia_semana, 'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')")
            ->orderBy('hora_inicio')
            ->get();

        $titulo = 'Horarios';
        if ($anio) {
            $titulo .= ' ' . $anio->nombre;
        }
        if ($periodo) {
            $titulo .= ' - ' . $periodo->codigo . ' ' . $periodo->nombre;
        }
        if ($seccion) {
            $titulo .= ' - ' . ($seccion->grado?->nombre ?? '') . ' ' . $seccion->nombre;
        }

        $headers = ['Anio', 'Periodo', 'Seccion', 'Dia', 'Hora inicio', 'Hora fin', 'Curso', 'Docente'];
        $rows = [];

        foreach ($horarios as $item) {
            $seccionLabel = trim(
                ($item->seccion?->grado?->nivel ?? '-') . ' ' .
                ($item->seccion?->grado?->nombre ?? '-') . ' - ' .
                ($item->seccion?->nombre ?? '-')
            );
            $periodoLabel = $item->periodoAcademico
                ? ($item->periodoAcademico->codigo . ' - ' . $item->periodoAcademico->nombre)
                : ($periodo ? $periodo->codigo . ' - ' . $periodo->nombre : '-');

            $rows[] = [
                $anio?->nombre ?? '-',
                $periodoLabel,
                $seccionLabel,
                $item->dia_semana ?? '-',
                $item->hora_inicio ?? '-',
                $item->hora_fin ?? '-',
                $item->curso?->nombre ?? '-',
                $item->docente?->name ?? 'Sin asignar',
            ];
        }

        $filename = 'HORARIOS_ESCOLARES_' . now()->format('d-m-Y') . '.xlsx';

        return $this->exportXlsx($titulo, $headers, $rows, $filename);
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
                'color' => ['rgb' => '00FF00'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '000000'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

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

    private function routePrefix(): string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'director.';
    }
}
