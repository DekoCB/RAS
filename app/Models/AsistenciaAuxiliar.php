<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaAuxiliar extends Model
{
    protected $table = 'asistencias_auxiliar';
    protected $guarded = [];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }
}
