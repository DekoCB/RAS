<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    protected $guarded = [];

    protected $table = 'inventario_movimientos';

    public function producto(): BelongsTo
    {
        return $this->belongsTo(InventarioProducto::class, 'inventario_producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
