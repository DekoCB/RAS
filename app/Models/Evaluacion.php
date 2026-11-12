<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-37: examenes mensuales, bimestrales y diagnostico. */
class Evaluacion extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = [
        'anio_escolar_id', 'curso_id', 'seccion_id', 'tipo', 'titulo', 'periodo',
        'fecha', 'puntaje_maximo',
    ];

    protected $casts = [
        'fecha'          => 'date',
        'puntaje_maximo' => 'decimal:2',
    ];

    public const TIPOS = [
        'mensual'     => 'Examen mensual',
        'bimestral'   => 'Examen bimestral',
        'diagnostico' => 'Examen diagnostico',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

    public function resultados()
    {
        return $this->hasMany(EvaluacionResultado::class, 'evaluacion_id');
    }

    /** RF-37: ranking de la evaluacion, de mayor a menor puntaje. */
    public function ranking()
    {
        return $this->resultados()
            ->with('estudiante')
            ->whereNotNull('puntaje')
            ->orderByDesc('puntaje')
            ->get();
    }
}
