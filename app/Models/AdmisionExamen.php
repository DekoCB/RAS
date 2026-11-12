<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmisionExamen extends Model
{
    protected $table = 'admision_examenes';

    protected $fillable = [
        'anio_escolar_id','nivel','titulo','descripcion',
        'puntaje_maximo','puntaje_minimo','activo',
    ];

    protected $casts = [
        'activo'         => 'boolean',
        'puntaje_maximo' => 'decimal:2',
        'puntaje_minimo' => 'decimal:2',
    ];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function aplicaciones()
    {
        return $this->hasMany(PostulanteExamen::class, 'examen_id');
    }
}
