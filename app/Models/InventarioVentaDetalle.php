<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioVentaDetalle extends Model
{
    protected $guarded = [];

    protected $table = 'inventario_venta_detalles';

    public function venta(): BelongsTo
    {
        return $this->belongsTo(InventarioVenta::class, 'inventario_venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(InventarioProducto::class, 'inventario_producto_id');
    }
}
