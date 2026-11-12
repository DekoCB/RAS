<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaConceptoDetalle extends Model
{
    protected $table = 'nota_concepto_detalles';

    protected $fillable = [
        'nota_concepto_id','estudiante_id','nota','docente_id'
    ];

    public function concepto()
    {
        return $this->belongsTo(NotaConcepto::class, 'nota_concepto_id');
    }
}