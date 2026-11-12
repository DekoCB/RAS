<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-65: cartas de compromiso firmadas por el padre de familia. */
class CartaCompromiso extends Model
{
    protected $table = 'cartas_compromiso';

    protected $fillable = [
        'estudiante_id', 'apoderado_id', 'anio', 'titulo', 'compromisos',
        'firmada', 'fecha_firma', 'archivo', 'registrado_por',
    ];

    protected $casts = [
        'firmada'     => 'boolean',
        'fecha_firma' => 'date',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function apoderado()
    {
        return $this->belongsTo(Apoderado::class, 'apoderado_id');
    }
}
