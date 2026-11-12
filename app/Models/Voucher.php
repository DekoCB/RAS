<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $guarded = [];

    public function deuda(): BelongsTo
    {
        return $this->belongsTo(Deuda::class);
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function usuarioValida(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_valida_id');
    }
}
