<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaConcepto extends Model
{
    protected $table = 'nota_conceptos';

    protected $fillable = [
        'anio_escolar_id','periodo_academico_id','curso_id','seccion_id','docente_id',
        'periodo','nombre','porcentaje'
    ];

    public function detalles()
    {
        return $this->hasMany(NotaConceptoDetalle::class, 'nota_concepto_id');
    }
}