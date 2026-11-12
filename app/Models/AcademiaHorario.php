<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-52 y RF-53: la academia funciona de 4:00 a 5:30 p.m., de lunes a viernes. */
class AcademiaHorario extends Model
{
    protected $table = 'academia_horarios';

    protected $fillable = ['area', 'curso', 'dia', 'hora_inicio', 'hora_fin', 'docente', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
    ];

    public function etiquetaDia(): string
    {
        return self::DIAS[$this->dia] ?? (string) $this->dia;
    }
}
