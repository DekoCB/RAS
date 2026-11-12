<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    protected $fillable = [
        'anio_escolar_id', 'concepto_pago_id', 'nivel', 'mes', 'monto',
        'vigente_desde', 'vigente_hasta', 'activo', 'creado_por', 'aprobado_por'
    ];

    public function anioEscolar() { return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id'); }
    public function conceptoPago() { return $this->belongsTo(ConceptoPago::class, 'concepto_pago_id'); }
}