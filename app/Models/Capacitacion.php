<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capacitacion extends Model
{
    protected $table = 'capacitaciones';

    protected $fillable = [
        'dirigido_a', 'tipo', 'titulo', 'descripcion', 'fecha_inicio', 'fecha_fin',
        'lugar', 'facilitador', 'horas', 'evidencia', 'registrado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    /** RF-60 (docentes y pasantias) y RF-66 (padres de familia). */
    public const DIRIGIDO = [
        'docentes' => 'Docentes',
        'padres'   => 'Padres de familia',
    ];

    public const TIPOS = [
        'capacitacion' => 'Capacitacion',
        'pasantia'     => 'Pasantia',
    ];
}
