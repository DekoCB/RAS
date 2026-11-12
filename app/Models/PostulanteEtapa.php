<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostulanteEtapa extends Model
{
    protected $table = 'postulante_etapas';

    protected $fillable = [
        'postulante_id','etapa','estado','fecha_programada',
        'fecha_realizada','responsable','observacion',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_realizada'  => 'datetime',
    ];

    public const ESTADOS = [
        'pendiente'  => 'Pendiente',
        'programada' => 'Programada',
        'completada' => 'Completada',
        'no_asistio' => 'No asistio',
    ];

    public function etiqueta(): string
    {
        return Postulante::ETAPAS[$this->etapa] ?? $this->etapa;
    }

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }
}
