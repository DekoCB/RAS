<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    protected $guarded = [];

    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function deudas(): BelongsToMany
    {
        return $this->belongsToMany(Deuda::class, 'deuda_pagos')
            ->withPivot(['monto_aplicado'])
            ->withTimestamps();
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_registra_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * Devuelve los métodos de pago decodificados.
     * Soporta el formato JSON nuevo y el string legacy.
     */
    public function getMetodosDecodedAttribute(): array
    {
        if (!$this->metodo) {
            return [];
        }
        $decoded = json_decode($this->metodo, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        // Formato legacy (string simple)
        return [['metodo' => $this->metodo, 'monto' => (float) $this->monto, 'op' => $this->numero_operacion]];
    }

    /**
     * Devuelve una cadena legible de los métodos, ej: "Efectivo + Yape".
     */
    public function getMetodoDisplayAttribute(): string
    {
        if (!$this->metodo) {
            return '-';
        }
        $decoded = json_decode($this->metodo, true);
        if (is_array($decoded)) {
            return implode(' + ', array_column($decoded, 'metodo'));
        }
        return $this->metodo;
    }
}
