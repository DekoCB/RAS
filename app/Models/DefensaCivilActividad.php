<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-71: modulo de Defensa Civil. */
class DefensaCivilActividad extends Model
{
    protected $table = 'defensa_civil_actividades';

    protected $fillable = [
        'sede_id', 'tipo', 'titulo', 'fecha', 'responsable', 'participantes',
        'resultado', 'observacion', 'proxima_fecha', 'evidencia',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'proxima_fecha' => 'date',
    ];

    public const TIPOS = [
        'simulacro'    => 'Simulacro',
        'inspeccion'   => 'Inspeccion',
        'senalizacion' => 'Senalizacion',
        'extintores'   => 'Extintores',
    ];

    public const RESULTADOS = [
        'conforme'  => 'Conforme',
        'observado' => 'Observado',
        'pendiente' => 'Pendiente',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
