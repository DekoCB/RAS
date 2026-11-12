<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademiaEvaluacion extends Model
{
    protected $table = 'academia_evaluaciones';

    protected $fillable = [
        'alumno_id', 'titulo', 'curso', 'fecha', 'puntaje', 'puntaje_maximo', 'observacion',
    ];

    protected $casts = [
        'fecha'          => 'date',
        'puntaje'        => 'decimal:2',
        'puntaje_maximo' => 'decimal:2',
    ];

    public function alumno()
    {
        return $this->belongsTo(AcademiaAlumno::class, 'alumno_id');
    }
}
