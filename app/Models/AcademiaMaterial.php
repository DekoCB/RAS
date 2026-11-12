<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-55: materiales de estudio que la academia entrega. */
class AcademiaMaterial extends Model
{
    protected $table = 'academia_materiales';

    protected $fillable = [
        'alumno_id', 'titulo', 'curso', 'fecha_entrega', 'cantidad', 'observacion', 'registrado_por',
    ];

    protected $casts = ['fecha_entrega' => 'date'];

    public function alumno()
    {
        return $this->belongsTo(AcademiaAlumno::class, 'alumno_id');
    }
}
