<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostulantePago extends Model
{
    protected $table = 'postulante_pagos';

    protected $fillable = [
        'postulante_id','monto','metodo','numero_operacion','fecha',
        'voucher','estado','observacion','registrado_por','validado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    /** RF-21: todos los cobros deben poder pagarse con Yape. */
    public const METODOS = [
        'yape'          => 'Yape',
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }
}
