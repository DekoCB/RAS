<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioInstitucional extends Model
{
    protected $table = 'horarios_institucionales';

    protected $fillable = [
        'nivel','grado_id','hora_ingreso','tolerancia_minutos','hora_salida','espera_maxima_minutos',
        'almuerzo_inicio','almuerzo_fin','activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    /**
     * Horario aplicable a un grado: la regla del grado gana sobre la del nivel,
     * y los campos que el grado deja vacios se completan con los del nivel.
     */
    public static function paraGrado(Grado $grado): ?array
    {
        $nivelRegla = static::where('nivel', $grado->nivel)->whereNull('grado_id')->where('activo', true)->first();
        $gradoRegla = static::where('grado_id', $grado->id)->where('activo', true)->first();

        if (!$nivelRegla && !$gradoRegla) {
            return null;
        }

        $campos = ['hora_ingreso','tolerancia_minutos','hora_salida','espera_maxima_minutos','almuerzo_inicio','almuerzo_fin'];
        $salida = [];
        foreach ($campos as $campo) {
            $salida[$campo] = $gradoRegla?->$campo ?? $nivelRegla?->$campo;
        }

        return $salida;
    }
}
