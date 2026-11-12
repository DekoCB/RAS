<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademiaAsistencia extends Model
{
    protected $table = 'academia_asistencias';

    protected $fillable = ['alumno_id', 'fecha', 'estado', 'observacion', 'registrado_por'];

    protected $casts = ['fecha' => 'date'];

    public const ESTADOS = [
        'presente'    => 'Presente',
        'tardanza'    => 'Tardanza',
        'falta'       => 'Falta',
        'justificado' => 'Justificado',
    ];

    public function alumno()
    {
        return $this->belongsTo(AcademiaAlumno::class, 'alumno_id');
    }
}
