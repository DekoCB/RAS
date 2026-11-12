<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    protected $fillable = [
        'anio_escolar_id','concepto_pago_id','nivel',
        'tipo','aplica_a','porcentaje','monto_fijo','dia_limite',
        'activo','creado_por'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'porcentaje' => 'decimal:2',
        'monto_fijo' => 'decimal:2',
    ];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function conceptoPago()
    {
        return $this->belongsTo(ConceptoPago::class, 'concepto_pago_id');
    }
}