<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-35: instrumento que describe niveles de desempeno por criterio. */
class Rubrica extends Model
{
    protected $table = 'rubricas';

    protected $fillable = [
        'anio_escolar_id', 'curso_id', 'nivel', 'titulo', 'descripcion',
        'criterios', 'activo', 'creado_por',
    ];

    protected $casts = [
        'criterios' => 'array',
        'activo'    => 'boolean',
    ];

    /** RF-33: escala de niveles de logro que usa Secundaria. */
    public const NIVELES_LOGRO = [
        1 => 'Nivel 1 - En inicio',
        2 => 'Nivel 2 - En proceso',
        3 => 'Nivel 3 - Logro esperado',
        4 => 'Nivel 4 - Logro destacado',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
}
