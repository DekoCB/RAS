<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\PeriodoAcademico;
use App\Models\Tarifa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PeriodoController extends Controller
{
    public function index()
    {
        $anioActivo = AnioEscolar::where('activo', true)->first();
        $periodos   = $anioActivo
            ? PeriodoAcademico::where('anio_escolar_id', $anioActivo->id)->orderBy('orden')->get()
            : collect();

        // Tarifa vacacional por nivel (para mostrarla en la tabla)
        $conceptoVacacional = ConceptoPago::where('tipo', 'vacacional')->first();
        $tarifasVacacional  = $conceptoVacacional && $anioActivo
            ? Tarifa::where('anio_escolar_id', $anioActivo->id)
                ->where('concepto_pago_id', $conceptoVacacional->id)
                ->where('activo', 1)
                ->get()
                ->keyBy('nivel')
            : collect();

        return view('admin.periodos.index', compact('anioActivo', 'periodos', 'tarifasVacacional', 'conceptoVacacional'));
    }

    public function edit(PeriodoAcademico $periodo)
    {
        return view('admin.periodos.edit', compact('periodo'));
    }

    public function update(Request $request, PeriodoAcademico $periodo)
    {
        $data = $request->validate([
            'fecha_inicio' => 'nullable|date|required_with:fecha_fin',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio|required_with:fecha_inicio',
            'meses_excluidos' => 'nullable|array',
            'meses_excluidos.*' => 'integer|min:1|max:12',
            'activo' => 'nullable|boolean',
        ]);

        $periodo->loadMissing('anioEscolar');

        if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
            $fechaInicioCarbon = Carbon::parse($data['fecha_inicio'])->startOfDay();
            $fechaFinCarbon = Carbon::parse($data['fecha_fin'])->startOfDay();
        } else {
            [$fechaInicioCarbon, $fechaFinCarbon] = $periodo->rangoFechasPorDefecto();
        }

        if ($fechaFinCarbon->lt($fechaInicioCarbon)) {
            $fechaFinCarbon = $fechaInicioCarbon->copy();
        }

        $anioInicio = $periodo->anioEscolar?->fecha_inicio
            ? Carbon::parse($periodo->anioEscolar->fecha_inicio)->startOfDay()
            : null;
        $anioFin = $periodo->anioEscolar?->fecha_fin
            ? Carbon::parse($periodo->anioEscolar->fecha_fin)->startOfDay()
            : null;

        if ($anioInicio && $fechaInicioCarbon->lt($anioInicio)) {
            return back()->withInput()->withErrors([
                'fecha_inicio' => 'La fecha de inicio del periodo no puede ser menor al inicio del ano escolar.',
            ]);
        }

        if ($anioFin && $fechaFinCarbon->gt($anioFin)) {
            return back()->withInput()->withErrors([
                'fecha_fin' => 'La fecha de fin del periodo no puede superar el fin del ano escolar.',
            ]);
        }

        $errorTraslape = $this->validarTraslapeConOtrosPeriodos($periodo, $fechaInicioCarbon, $fechaFinCarbon);
        if ($errorTraslape !== null) {
            return back()->withInput()->withErrors([
                'fecha_inicio' => $errorTraslape,
            ]);
        }

        $fechaInicio = $fechaInicioCarbon->toDateString();
        $fechaFin = $fechaFinCarbon->toDateString();

        $mesesExcluidos = collect($data['meses_excluidos'] ?? [])
            ->map(fn ($mes) => (int) $mes)
            ->filter(fn (int $mes) => $mes >= 1 && $mes <= 12)
            ->unique()
            ->sort()
            ->values();

        if (!$periodo->es_vacacional) {
            $mesInicio = (int) Carbon::parse($fechaInicio)->format('n');
            $mesFin = (int) Carbon::parse($fechaFin)->format('n');
            if ($mesFin < $mesInicio) {
                $mesFin = $mesInicio;
            }

            $mesesEnRango = range($mesInicio, $mesFin);
            $lookupMesesEnRango = array_fill_keys($mesesEnRango, true);

            $mesesExcluidos = $mesesExcluidos
                ->filter(fn (int $mes) => isset($lookupMesesEnRango[$mes]))
                ->values();

            if (count($mesesEnRango) === $mesesExcluidos->count()) {
                return back()->withInput()->withErrors([
                    'meses_excluidos' => 'Debe quedar al menos 1 mes habilitado para generar pensiones.',
                ]);
            }
        } else {
            $mesesExcluidos = collect();
        }

        $periodo->update([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'meses_excluidos' => $mesesExcluidos->all(),
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()->route('admin-sistema.periodos.index')
            ->with('success', "Periodo '{$periodo->nombre}' actualizado correctamente.");
    }

    private function validarTraslapeConOtrosPeriodos(PeriodoAcademico $periodo, Carbon $inicio, Carbon $fin): ?string
    {
        $otros = PeriodoAcademico::where('anio_escolar_id', $periodo->anio_escolar_id)
            ->where('id', '!=', $periodo->id)
            ->orderBy('orden')
            ->get();

        foreach ($otros as $otro) {
            [$inicioOtro, $finOtro] = $otro->rangoFechasEfectivas();

            if ($this->rangosSeTraslapan($inicio, $fin, $inicioOtro, $finOtro)) {
                return sprintf(
                    'El rango se traslapa con %s (%s) [%s - %s].',
                    $otro->nombre,
                    $otro->codigo,
                    $inicioOtro->format('d/m/Y'),
                    $finOtro->format('d/m/Y')
                );
            }
        }

        return null;
    }

    private function rangosSeTraslapan(Carbon $inicioA, Carbon $finA, Carbon $inicioB, Carbon $finB): bool
    {
        return $inicioA->lte($finB) && $inicioB->lte($finA);
    }

    /**
     * Formulario para configurar tarifa vacacional por nivel.
     */
    public function tarifaVacacional()
    {
        $anioActivo = AnioEscolar::where('activo', true)->first();
        if (!$anioActivo) {
            return redirect()->route('admin-sistema.periodos.index')
                ->with('error', 'No hay ano escolar activo.');
        }

        $conceptoVacacional = ConceptoPago::firstOrCreate(
            ['tipo' => 'vacacional'],
            ['nombre' => 'Vacacional', 'monto_base' => 0, 'activo' => true]
        );

        $niveles = ['Inicial', 'Primaria', 'Secundaria'];

        $tarifas = Tarifa::where('anio_escolar_id', $anioActivo->id)
            ->where('concepto_pago_id', $conceptoVacacional->id)
            ->get()
            ->keyBy('nivel');

        return view('admin.periodos.tarifa-vacacional', compact('anioActivo', 'conceptoVacacional', 'niveles', 'tarifas'));
    }

    /**
     * Guarda/actualiza las tarifas vacacional por nivel.
     */
    public function guardarTarifaVacacional(Request $request)
    {
        $request->validate([
            'montos'   => 'required|array',
            'montos.*' => 'required|numeric|min:0',
        ]);

        $anioActivo = AnioEscolar::where('activo', true)->firstOrFail();

        $conceptoVacacional = ConceptoPago::firstOrCreate(
            ['tipo' => 'vacacional'],
            ['nombre' => 'Vacacional', 'monto_base' => 0, 'activo' => true]
        );

        foreach ($request->montos as $nivel => $monto) {
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anioActivo->id,
                    'concepto_pago_id' => $conceptoVacacional->id,
                    'nivel'            => $nivel,
                ],
                [
                    'monto'       => $monto,
                    'activo'      => true,
                    'creado_por'  => Auth::id(),
                ]
            );
        }

        return redirect()->route('admin-sistema.periodos.index')
            ->with('success', 'Tarifas de Vacacional actualizadas correctamente.');
    }
}
