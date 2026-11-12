<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Matricula extends Model
{
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_REGISTRADA = 'registrada';
    const ESTADO_ACTIVA     = 'activa';
    const ESTADO_RETIRADA   = 'retirada';

    protected $guarded = [];

    protected $casts = [
        'fecha_matricula' => 'date',
        'fecha_retiro'    => 'date',
        'repitente'       => 'boolean',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    /** @return BelongsTo<Estudiante, self> */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function deudas(): HasMany
    {
        return $this->hasMany(Deuda::class);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(MatriculaCargo::class);
    }

    public function descuentos(): HasMany
    {
        return $this->hasMany(MatriculaDescuento::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}
