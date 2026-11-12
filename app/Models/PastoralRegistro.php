<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODULO 11 - Pastoral (RF-56).
 * El test pastoral y la carta de declaracion de fe nacen en el proceso de
 * admision (RF-15), por eso el registro puede colgar de un postulante o,
 * si el area acompana a un alumno ya matriculado, de un estudiante.
 */
class PastoralRegistro extends Model
{
    protected $table = 'pastoral_registros';

    protected $fillable = [
        'postulante_id', 'estudiante_id', 'tipo', 'fecha', 'responsable',
        'observacion', 'resultado', 'archivo', 'registrado_por',
    ];

    protected $casts = ['fecha' => 'date'];

    public const TIPOS = [
        'test_pastoral'   => 'Test pastoral',
        'carta_fe'        => 'Carta de declaracion de fe',
        'acompanamiento'  => 'Acompanamiento pastoral',
    ];

    public const RESULTADOS = [
        'favorable'         => 'Favorable',
        'con_observaciones' => 'Con observaciones',
        'pendiente'         => 'Pendiente',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function persona(): string
    {
        if ($this->postulante) {
            return $this->postulante->nombreCompleto() . ' (postulante)';
        }

        if ($this->estudiante) {
            return trim($this->estudiante->apellidos . ', ' . $this->estudiante->nombres);
        }

        return 'Sin asignar';
    }
}
