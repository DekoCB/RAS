<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaDenominacion extends Model
{
    protected $guarded = [];

    protected $table = 'caja_denominaciones';

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }
}
