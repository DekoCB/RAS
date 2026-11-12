<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnioEscolar extends Model
{
    protected $table = 'anios_escolares';

    protected $guarded = [];

    protected $casts = [
        'activo'           => 'boolean',
        'admision_abierta' => 'boolean',
        'admision_cierre'  => 'date',
    ];

    /**
     * Anio cuya campana de admision esta publicada en el sitio web.
     * La fecha de cierre, si existe, la caduca sola.
     */
    public static function campanaAdmision(): ?self
    {
        return static::where('admision_abierta', true)
            ->where(fn ($q) => $q->whereNull('admision_cierre')
                ->orWhereDate('admision_cierre', '>=', now()->toDateString()))
            ->orderByDesc('nombre')
            ->first();
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function deudas(): HasMany
    {
        return $this->hasMany(Deuda::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function bimestres(): HasMany
    {
        return $this->hasMany(Bimestre::class);
    }

    public function periodosAcademicos(): HasMany
    {
        return $this->hasMany(PeriodoAcademico::class);
    }

    public function politicaPago()
    {
        return $this->hasOne(PoliticaPago::class);
    }

    public function cronogramaPensiones(): HasMany
    {
        return $this->hasMany(CronogramaPension::class)->orderBy('mes');
    }
}
