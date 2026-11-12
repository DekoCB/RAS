<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\CronogramaPension;
use App\Models\PoliticaPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de cobranza del Reglamento Interno por año escolar:
 * oportunidades de pago, cronograma de pensiones (Art. 61) y
 * parámetros del interés moratorio (Art. 69). Se editan desde el Tarifario.
 */
class PoliticaPagoController extends Controller
{
    public function update(Request $request, AnioEscolar $anio)
    {
        $data = $request->validate([
            'pension_cuotas'      => ['required', 'integer', 'min:1', 'max:12'],
            'pension_mes_inicio'  => ['required', 'integer', 'min:1', 'max:12'],
            'pension_mes_fin'     => ['required', 'integer', 'min:1', 'max:12', 'gte:pension_mes_inicio'],
            'matricula_desde'     => ['nullable', 'date'],
            'matricula_hasta'     => ['nullable', 'date', 'after_or_equal:matricula_desde'],
            'cuota_ingreso_desde' => ['nullable', 'date'],
            'cuota_ingreso_hasta' => ['nullable', 'date', 'after_or_equal:cuota_ingreso_desde'],
            'mora_porcentaje'     => ['required', 'numeric', 'min:0', 'max:100'],
            'mora_tasa_anual'     => ['nullable', 'numeric', 'min:0', 'max:999'],
            'mora_divisor'        => ['required', 'integer', 'min:1', 'max:366'],
            'observaciones'       => ['nullable', 'string', 'max:2000'],
            'cronograma'          => ['nullable', 'array'],
            'cronograma.*'        => ['nullable', 'date'],
        ]);

        $cronograma = $data['cronograma'] ?? [];
        unset($data['cronograma']);

        DB::transaction(function () use ($anio, $data, $cronograma) {
            PoliticaPago::updateOrCreate(['anio_escolar_id' => $anio->id], $data);

            foreach ($cronograma as $mes => $fecha) {
                $mes = (int) $mes;
                if ($mes < 1 || $mes > 12) {
                    continue;
                }

                if ($fecha) {
                    CronogramaPension::updateOrCreate(
                        ['anio_escolar_id' => $anio->id, 'mes' => $mes],
                        ['fecha_vencimiento' => $fecha]
                    );
                } else {
                    CronogramaPension::where('anio_escolar_id', $anio->id)->where('mes', $mes)->delete();
                }
            }
        });

        return redirect()
            ->route('admin-sistema.tarifas.index', ['anio_id' => $anio->id])
            ->with('success', "Políticas de pago del año {$anio->nombre} guardadas.");
    }
}
