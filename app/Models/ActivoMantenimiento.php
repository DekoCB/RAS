<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivoMantenimiento extends Model
{
    protected $table = 'activo_mantenimientos';

    protected $fillable = ['activo_id', 'fecha', 'tipo', 'responsable', 'costo', 'detalle'];

    protected $casts = [
        'fecha' => 'date',
        'costo' => 'decimal:2',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }
}
