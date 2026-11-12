<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $guarded = [];

    protected $casts = [
        'fecha_apertura'       => 'datetime',
        'fecha_cierre'         => 'datetime',
        'saldo_inicial'        => 'decimal:2',
        'confirmacion_digital' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function denominaciones(): HasMany
    {
        return $this->hasMany(CajaDenominacion::class);
    }

    /**
     * Devuelve los pagos que pertenecen a esta sesión de caja.
     * Se filtran por usuario y momento real de registro.
     */
    public function getPagos()
    {
        $query = Pago::where('usuario_registra_id', $this->usuario_id)
            ->where('created_at', '>=', $this->fecha_apertura);

        if ($this->fecha_cierre) {
            $query->where('created_at', '<=', $this->fecha_cierre);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function totalEfectivo(): float
    {
        return (float) $this->getPagos()
            ->where('metodo', 'Efectivo')
            ->sum('monto');
    }

    public function totalDigital(): float
    {
        return (float) $this->getPagos()
            ->where('metodo', '!=', 'Efectivo')
            ->sum('monto');
    }

    public function totalGeneral(): float
    {
        return $this->totalEfectivo() + $this->totalDigital();
    }

    public function totalContado(): float
    {
        return (float) $this->denominaciones->sum(fn($d) => $d->denominacion * $d->cantidad);
    }

    public function diferencia(): float
    {
        return $this->totalContado() - $this->totalEfectivo();
    }
}
