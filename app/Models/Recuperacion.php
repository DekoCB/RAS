<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-38: recuperacion, refuerzo y nivelacion academica. */
class Recuperacion extends Model
{
    protected $table = 'recuperaciones';

    protected $fillable = [
        'anio_escolar_id', 'estudiante_id', 'curso_id', 'tipo', 'periodo', 'fecha',
        'nota_original', 'nota_final', 'costo', 'pagado', 'observacion', 'registrado_por',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'nota_original' => 'decimal:2',
        'nota_final'    => 'decimal:2',
        'costo'         => 'decimal:2',
        'pagado'        => 'boolean',
    ];

    public const TIPOS = [
        'recuperacion' => 'Recuperacion',
        'refuerzo'     => 'Refuerzo',
        'nivelacion'   => 'Nivelacion',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
}
