<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-34: en Inicial la evaluacion es descriptiva, no numerica. */
class InformeDescriptivo extends Model
{
    protected $table = 'informes_descriptivos';

    protected $fillable = [
        'anio_escolar_id', 'estudiante_id', 'docente_id', 'tipo', 'periodo',
        'logros', 'dificultades', 'recomendaciones',
    ];

    public const TIPOS = [
        'mensual'   => 'Mensual',
        'bimestral' => 'Bimestral',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
}
