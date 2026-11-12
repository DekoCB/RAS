<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionResultado extends Model
{
    protected $table = 'evaluacion_resultados';

    protected $fillable = ['evaluacion_id', 'estudiante_id', 'puntaje', 'observacion'];

    protected $casts = ['puntaje' => 'decimal:2'];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
