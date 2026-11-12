<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaDescuento extends Model
{
    protected $guarded = [];

    protected $table = 'matricula_descuentos';

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }
}
