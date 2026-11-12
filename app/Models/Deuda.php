<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deuda extends Model
{
    protected $guarded = [];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoPago::class, 'concepto_pago_id');
    }

    public function pagos(): BelongsToMany
    {
        return $this->belongsToMany(Pago::class, 'deuda_pagos')
            ->withPivot(['monto_aplicado'])
            ->withTimestamps();
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    /** Días transcurridos desde el vencimiento (0 si aún no vence o ya está pagada). */
    public function diasAtraso(?\DateTimeInterface $hoy = null): int
    {
        if (!$this->fecha_vencimiento || (float) $this->saldo <= 0 || $this->estado === 'pagado') {
            return 0;
        }

        $vence = \Illuminate\Support\Carbon::parse($this->fecha_vencimiento)->startOfDay();
        $hoy   = \Illuminate\Support\Carbon::parse($hoy ?? now())->startOfDay();

        return $vence->lt($hoy) ? $vence->diffInDays($hoy) : 0;
    }

    /**
     * Art. 69 del Reglamento Interno: interés moratorio sobre pensiones vencidas.
     * Solo aplica a pensiones y devuelve 0 mientras no se registre la tasa del BCR
     * en la política de pago del año.
     */
    public function moraCalculada(?\DateTimeInterface $hoy = null): float
    {
        $tipo = $this->concepto?->tipo;
        if (!in_array($tipo, ['mensual', 'pension'], true)) {
            return 0.0;
        }

        $politica = PoliticaPago::paraAnio($this->anio_escolar_id);
        if (!$politica) {
            return 0.0;
        }

        return $politica->moraAcumulada((float) $this->saldo, $this->diasAtraso($hoy));
    }
}
