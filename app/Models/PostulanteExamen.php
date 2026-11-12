<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostulanteExamen extends Model
{
    protected $table = 'postulante_examenes';

    protected $fillable = [
        'postulante_id','examen_id','fecha','puntaje','observacion','evaluado_por',
    ];

    protected $casts = [
        'fecha'   => 'datetime',
        'puntaje' => 'decimal:2',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function examen()
    {
        return $this->belongsTo(AdmisionExamen::class, 'examen_id');
    }

    /** Aprobado cuando alcanza el puntaje minimo definido para su nivel. */
    public function aprobado(): ?bool
    {
        if ($this->puntaje === null || !$this->examen) {
            return null;
        }

        return (float) $this->puntaje >= (float) $this->examen->puntaje_minimo;
    }
}
