<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Art. 61 del Reglamento Interno: fecha de vencimiento de cada cuota
 * de pension. Cuando un mes no esta en la tabla se aplica la regla
 * general del cronograma (ultimo dia habil del mes; diciembre el 15).
 */
class CronogramaPension extends Model
{
    protected $table = 'cronograma_pensiones';

    protected $fillable = ['anio_escolar_id', 'mes', 'fecha_vencimiento'];

    protected $casts = ['fecha_vencimiento' => 'date'];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public static function fechaVencimiento(?int $anioEscolarId, int $anio, int $mes): Carbon
    {
        if ($anioEscolarId) {
            $fila = static::where('anio_escolar_id', $anioEscolarId)->where('mes', $mes)->first();
            if ($fila) {
                return $fila->fecha_vencimiento->copy();
            }
        }

        return static::fechaPorDefecto($anio, $mes);
    }

    public static function fechaPorDefecto(int $anio, int $mes): Carbon
    {
        if ($mes === 12) {
            return Carbon::create($anio, 12, 15);
        }

        $fecha = Carbon::create($anio, $mes, 1)->endOfMonth()->startOfDay();
        while ($fecha->isWeekend()) {
            $fecha->subDay();
        }

        return $fecha;
    }
}
