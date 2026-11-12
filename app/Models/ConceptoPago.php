<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConceptoPago extends Model
{
    protected $table = 'conceptos_pago';

    protected $guarded = [];

    public function deudas(): HasMany
    {
        return $this->hasMany(Deuda::class);
    }
}
