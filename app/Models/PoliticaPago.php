<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reglas de cobranza del Reglamento Interno para un anio escolar:
 * oportunidades de pago (Art. 54 y anexos), cuotas de pension (Art. 61)
 * y parametros del interes moratorio (Art. 69).
 */
class PoliticaPago extends Model
{
    protected $table = 'politicas_pago';

    protected $fillable = [
        'anio_escolar_id', 'pension_cuotas', 'pension_mes_inicio', 'pension_mes_fin',
        'matricula_desde', 'matricula_hasta', 'cuota_ingreso_desde', 'cuota_ingreso_hasta',
        'mora_porcentaje', 'mora_tasa_anual', 'mora_divisor', 'observaciones',
    ];

    protected $casts = [
        'matricula_desde'     => 'date',
        'matricula_hasta'     => 'date',
        'cuota_ingreso_desde' => 'date',
        'cuota_ingreso_hasta' => 'date',
        'mora_porcentaje'     => 'float',
        'mora_tasa_anual'     => 'float',
    ];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public static function paraAnio(?int $anioEscolarId): ?self
    {
        return $anioEscolarId ? static::where('anio_escolar_id', $anioEscolarId)->first() : null;
    }

    /**
     * Art. 69: interes moratorio diario = 15% de (pension x tasa maxima BCR) / 360.
     * Devuelve 0 mientras no se registre la tasa del BCR.
     */
    public function moraDiaria(float $monto): float
    {
        if (!$this->mora_tasa_anual || $this->mora_divisor <= 0) {
            return 0.0;
        }

        $interesAnual = $monto * ($this->mora_tasa_anual / 100);

        return round($interesAnual * ($this->mora_porcentaje / 100) / $this->mora_divisor, 4);
    }

    public function moraAcumulada(float $monto, int $diasAtraso): float
    {
        if ($diasAtraso <= 0) {
            return 0.0;
        }

        return round($this->moraDiaria($monto) * $diasAtraso, 2);
    }
}
