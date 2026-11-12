<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-39: metodos de aprendizaje y proyectos pedagogicos por curso o nivel. */
class ProyectoPedagogico extends Model
{
    protected $table = 'proyectos_pedagogicos';

    protected $fillable = [
        'anio_escolar_id', 'titulo', 'tipo', 'nivel', 'curso_id', 'descripcion',
        'fecha_inicio', 'fecha_fin', 'responsable', 'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public const TIPOS = [
        'proyecto' => 'Proyecto pedagogico',
        'metodo'   => 'Metodo de aprendizaje',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
}
