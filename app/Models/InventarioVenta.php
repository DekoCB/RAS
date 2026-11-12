<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioVenta extends Model
{
    protected $guarded = [];

    protected $table = 'inventario_ventas';

    protected $casts = [
        'fecha_venta' => 'datetime',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(InventarioVentaDetalle::class, 'inventario_venta_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
