<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnioEscolarController extends Controller
{
    public function index()
    {
        $anios = AnioEscolar::with(['bimestres', 'periodosAcademicos'])
            ->orderByDesc('fecha_inicio')
            ->get();

        return view('admin.anios_escolares.index', compact('anios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:anios_escolares,nombre'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'periodo_0_inicio' => ['required', 'date'],
            'periodo_0_fin' => ['required', 'date', 'after_or_equal:periodo_0_inicio'],
            'periodo_1_inicio' => ['required', 'date'],
            'periodo_1_fin' => ['required', 'date', 'after_or_equal:periodo_1_inicio'],
            'periodo_2_inicio' => ['required', 'date'],
            'periodo_2_fin' => ['required', 'date', 'after_or_equal:periodo_2_inicio'],
            'bimestre_1_inicio' => ['required', 'date'],
            'bimestre_1_fin' => ['required', 'date', 'after_or_equal:bimestre_1_inicio'],
            'bimestre_2_inicio' => ['required', 'date'],
            'bimestre_2_fin' => ['required', 'date', 'after_or_equal:bimestre_2_inicio'],
            'bimestre_3_inicio' => ['required', 'date'],
            'bimestre_3_fin' => ['required', 'date', 'after_or_equal:bimestre_3_inicio'],
            'bimestre_4_inicio' => ['required', 'date'],
            'bimestre_4_fin' => ['required', 'date', 'after_or_equal:bimestre_4_inicio'],
        ]);

        $errores = $this->validarRangosPeriodos($data);
        if (!empty($errores)) {
            return back()->withInput()->withErrors($errores);
        }

        DB::transaction(function () use ($data) {
            $anio = AnioEscolar::create([
                'nombre' => $data['nombre'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'activo' => false,
            ]);

            $codigoBase = trim((string) $anio->nombre);
            $hoy = Carbon::today();
            $periodos = [
                [
                    'codigo' => $codigoBase . '-0',
                    'nombre' => 'Vacacional',
                    'fecha_inicio' => $data['periodo_0_inicio'],
                    'fecha_fin' => $data['periodo_0_fin'],
                    'es_vacacional' => true,
                    'orden' => 0,
                ],
                [
                    'codigo' => $codigoBase . '-1',
                    'nombre' => 'Periodo 1',
                    'fecha_inicio' => $data['periodo_1_inicio'],
                    'fecha_fin' => $data['periodo_1_fin'],
                    'es_vacacional' => false,
                    'orden' => 1,
                ],
                [
                    'codigo' => $codigoBase . '-2',
                    'nombre' => 'Periodo 2',
                    'fecha_inicio' => $data['periodo_2_inicio'],
                    'fecha_fin' => $data['periodo_2_fin'],
                    'es_vacacional' => false,
                    'orden' => 2,
                ],
            ];

            $activoAsignado = false;
            foreach ($periodos as &$periodo) {
                $inicio = Carbon::parse($periodo['fecha_inicio']);
                $fin = Carbon::parse($periodo['fecha_fin']);
                $esActivo = !$activoAsignado && $hoy->between($inicio, $fin);
                $periodo['activo'] = $esActivo;
                $activoAsignado = $activoAsignado || $esActivo;
            }
            unset($periodo);

            if (!$activoAsignado && isset($periodos[1])) {
                $periodos[1]['activo'] = true;
            }

            $anio->periodosAcademicos()->createMany($periodos);

            $bimestres = [
                ['nombre' => 'Bimestre 1', 'fecha_inicio' => $data['bimestre_1_inicio'], 'fecha_fin' => $data['bimestre_1_fin']],
                ['nombre' => 'Bimestre 2', 'fecha_inicio' => $data['bimestre_2_inicio'], 'fecha_fin' => $data['bimestre_2_fin']],
                ['nombre' => 'Bimestre 3', 'fecha_inicio' => $data['bimestre_3_inicio'], 'fecha_fin' => $data['bimestre_3_fin']],
                ['nombre' => 'Bimestre 4', 'fecha_inicio' => $data['bimestre_4_inicio'], 'fecha_fin' => $data['bimestre_4_fin']],
            ];

            foreach ($bimestres as $bimestre) {
                $anio->bimestres()->create($bimestre);
            }
        });

        return redirect()
            ->route('admin-sistema.anios-escolares.index')
            ->with('status', 'Ano escolar creado.');
    }

    public function activar(AnioEscolar $anioEscolar)
    {
        DB::transaction(function () use ($anioEscolar) {
            AnioEscolar::where('activo', true)->update(['activo' => false]);
            $anioEscolar->update(['activo' => true]);
        });

        return redirect()
            ->route('admin-sistema.anios-escolares.index')
            ->with('status', 'Ano escolar activado.');
    }

    /**
     * Abre o cierra la campana de admision publicada en el sitio web.
     * Solo un anio puede estar abierto a la vez: la portada apunta a uno.
     */
    public function campanaAdmision(Request $request, AnioEscolar $anioEscolar)
    {
        $data = $request->validate([
            'admision_abierta' => ['required', 'boolean'],
            'admision_cierre'  => ['nullable', 'date', 'after_or_equal:today'],
            'admision_mensaje' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($anioEscolar, $data) {
            if ($data['admision_abierta']) {
                AnioEscolar::where('id', '!=', $anioEscolar->id)
                    ->where('admision_abierta', true)
                    ->update(['admision_abierta' => false]);
            }

            $anioEscolar->update([
                'admision_abierta' => (bool) $data['admision_abierta'],
                'admision_cierre'  => $data['admision_cierre'] ?? null,
                'admision_mensaje' => $data['admision_mensaje'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin-sistema.anios-escolares.index')
            ->with('status', $data['admision_abierta']
                ? "Pre-inscripcion web abierta para el {$anioEscolar->nombre}."
                : "Pre-inscripcion web cerrada para el {$anioEscolar->nombre}.");
    }

    private function validarRangosPeriodos(array $data): array
    {
        $errores = [];

        $anioInicio = Carbon::parse($data['fecha_inicio'])->startOfDay();
        $anioFin = Carbon::parse($data['fecha_fin'])->startOfDay();

        $periodos = [
            [
                'key' => 'periodo_0',
                'label' => 'Periodo 0 (Vacacional)',
                'inicio' => Carbon::parse($data['periodo_0_inicio'])->startOfDay(),
                'fin' => Carbon::parse($data['periodo_0_fin'])->startOfDay(),
            ],
            [
                'key' => 'periodo_1',
                'label' => 'Periodo 1',
                'inicio' => Carbon::parse($data['periodo_1_inicio'])->startOfDay(),
                'fin' => Carbon::parse($data['periodo_1_fin'])->startOfDay(),
            ],
            [
                'key' => 'periodo_2',
                'label' => 'Periodo 2',
                'inicio' => Carbon::parse($data['periodo_2_inicio'])->startOfDay(),
                'fin' => Carbon::parse($data['periodo_2_fin'])->startOfDay(),
            ],
        ];

        foreach ($periodos as $periodo) {
            if ($periodo['inicio']->lt($anioInicio)) {
                $errores[$periodo['key'] . '_inicio'] = "{$periodo['label']} inicia antes del ano escolar.";
            }

            if ($periodo['fin']->gt($anioFin)) {
                $errores[$periodo['key'] . '_fin'] = "{$periodo['label']} termina despues del ano escolar.";
            }
        }

        for ($i = 0; $i < count($periodos); $i++) {
            for ($j = $i + 1; $j < count($periodos); $j++) {
                $a = $periodos[$i];
                $b = $periodos[$j];

                if ($this->rangosSeTraslapan($a['inicio'], $a['fin'], $b['inicio'], $b['fin'])) {
                    $errores[$b['key'] . '_inicio'] = "{$b['label']} se traslapa con {$a['label']}.";
                }
            }
        }

        if ($periodos[0]['inicio']->gt($periodos[1]['inicio'])) {
            $errores['periodo_1_inicio'] = 'Periodo 1 debe iniciar despues de Periodo 0.';
        }

        if ($periodos[1]['inicio']->gt($periodos[2]['inicio'])) {
            $errores['periodo_2_inicio'] = 'Periodo 2 debe iniciar despues de Periodo 1.';
        }

        return $errores;
    }

    private function rangosSeTraslapan(Carbon $inicioA, Carbon $finA, Carbon $inicioB, Carbon $finB): bool
    {
        return $inicioA->lte($finB) && $inicioB->lte($finA);
    }
}
