<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioCategoria extends Model
{
    protected $table = 'inventario_categorias';

    protected $guarded = [];

    public function productos(): HasMany
    {
        return $this->hasMany(InventarioProducto::class, 'categoria_id');
    }
}
