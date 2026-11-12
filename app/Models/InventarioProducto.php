<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioProducto extends Model
{
    protected $guarded = [];

    protected $table = 'inventario_productos';

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'inventario_producto_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(InventarioCategoria::class, 'categoria_id');
    }
}
