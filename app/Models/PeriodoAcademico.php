<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PeriodoAcademico extends Model
{
    protected $table = 'periodos_academicos';

    protected $guarded = [];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'meses_excluidos' => 'array',
        'es_vacacional' => 'boolean',
        'activo' => 'boolean',
    ];

    public const RANGO_POR_DEFECTO_VACACIONAL = [1, 2];
    public const RANGO_POR_DEFECTO_PERIODO_1 = [3, 7];
    public const RANGO_POR_DEFECTO_PERIODO_2 = [8, 12];
    public const RANGO_POR_DEFECTO_GENERAL = [3, 12];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class);
    }

    public function deudas(): HasMany
    {
        return $this->hasMany(Deuda::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function anioCalendario(): int
    {
        $nombreAnio = (string) ($this->anioEscolar?->nombre ?? '');
        if (preg_match('/\d{4}/', $nombreAnio, $coincidencias)) {
            $anio = (int) $coincidencias[0];
            if ($anio >= 2000) {
                return $anio;
            }
        }

        return (int) now()->format('Y');
    }

    public function rangoMesesPorDefecto(): array
    {
        return static::rangoMesesPorDefectoPara(
            (int) $this->orden,
            (bool) $this->es_vacacional,
            (string) ($this->codigo ?? '')
        );
    }

    public static function rangoMesesPorDefectoPara(int $orden, bool $esVacacional, string $codigo = ''): array
    {
        $codigo = trim($codigo);

        if ($esVacacional || $orden === 0 || Str::endsWith($codigo, '-0')) {
            return self::RANGO_POR_DEFECTO_VACACIONAL;
        }

        if ($orden === 1 || Str::endsWith($codigo, '-1')) {
            return self::RANGO_POR_DEFECTO_PERIODO_1;
        }

        if ($orden === 2 || Str::endsWith($codigo, '-2')) {
            return self::RANGO_POR_DEFECTO_PERIODO_2;
        }

        return self::RANGO_POR_DEFECTO_GENERAL;
    }

    public function rangoFechasEfectivas(): array
    {
        if ($this->fecha_inicio && $this->fecha_fin) {
            $inicio = Carbon::parse($this->fecha_inicio)->startOfDay();
            $fin = Carbon::parse($this->fecha_fin)->startOfDay();

            if ($fin->lt($inicio)) {
                $fin = $inicio->copy();
            }

            return [$inicio, $fin];
        }

        return $this->rangoFechasPorDefecto();
    }

    public function rangoFechasPorDefecto(): array
    {
        [$mesInicio, $mesFin] = $this->rangoMesesPorDefecto();
        $anio = $this->anioCalendario();
        $inicio = Carbon::create($anio, $mesInicio, 1)->startOfDay();
        $fin = Carbon::create($anio, $mesFin, 1)->endOfMonth()->startOfDay();

        if ($fin->lt($inicio)) {
            $fin = $inicio->copy();
        }

        return [$inicio, $fin];
    }

    /**
     * Retorna [mes_inicio, mes_fin] para generar las pensiones mensuales.
     * Si el periodo tiene fecha_inicio/fecha_fin se usan esos meses;
     * de lo contrario se devuelven los valores por defecto (marzo-diciembre).
     */
    public function rangoMesesPensiones(): array
    {
        if ($this->fecha_inicio && $this->fecha_fin) {
            $inicio = (int) Carbon::parse($this->fecha_inicio)->format('n');
            $fin = (int) Carbon::parse($this->fecha_fin)->format('n');
        } else {
            [$inicio, $fin] = $this->rangoMesesPorDefecto();
        }

        if ($fin < $inicio) {
            $fin = $inicio;
        }

        return [$inicio, $fin];
    }

    /**
     * Meses excluidos del cobro mensual para este periodo (solo meses validos del rango).
     */
    public function mesesExcluidosPensiones(): array
    {
        if ($this->es_vacacional) {
            return [];
        }

        [$mesInicio, $mesFin] = $this->rangoMesesPensiones();

        $meses = collect($this->meses_excluidos ?? [])
            ->map(fn ($mes) => (int) $mes)
            ->filter(fn (int $mes) => $mes >= 1 && $mes <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return array_values(array_filter(
            $meses,
            fn (int $mes) => $mes >= $mesInicio && $mes <= $mesFin
        ));
    }

    /**
     * Meses que efectivamente se deben generar para pensiones.
     * Respeta rango (por periodo u override) y excluye los meses bloqueados.
     */
    public function mesesGenerablesPensiones(?int $mesInicioOverride = null, ?int $mesFinOverride = null): array
    {
        if ($mesInicioOverride !== null && $mesFinOverride !== null) {
            $mesInicio = max(1, min(12, $mesInicioOverride));
            $mesFin = max(1, min(12, $mesFinOverride));
            if ($mesFin < $mesInicio) {
                $mesFin = $mesInicio;
            }
        } else {
            [$mesInicio, $mesFin] = $this->rangoMesesPensiones();
        }

        if ($mesFin < $mesInicio) {
            return [];
        }

        $base = range($mesInicio, $mesFin);
        $excluidos = $this->mesesExcluidosPensiones();

        if (empty($excluidos)) {
            return $base;
        }

        $lookupExcluidos = array_fill_keys($excluidos, true);

        return array_values(array_filter(
            $base,
            fn (int $mes) => !isset($lookupExcluidos[$mes])
        ));
    }

    /**
     * Meses generables desde este periodo hasta fin del anio escolar.
     * Ejemplo: si es periodo 1 (mar-jun) y hay periodo 2 (ago-dic), retorna mar..jun + ago..dic.
     */
    public function mesesGenerablesDesdePeriodo(?int $mesInicioOverride = null, ?int $mesFinOverride = null): array
    {
        if ($this->es_vacacional) {
            return $this->mesesGenerablesPensiones($mesInicioOverride, $mesFinOverride);
        }

        $periodosAcademicos = static::where('anio_escolar_id', $this->anio_escolar_id)
            ->where('es_vacacional', false)
            ->where('orden', '>=', $this->orden)
            ->orderBy('orden')
            ->get();

        if ($periodosAcademicos->isEmpty()) {
            $periodosAcademicos = collect([$this]);
        }

        $meses = [];
        $todosExcluidos = [];
        foreach ($periodosAcademicos as $periodo) {
            $meses = array_merge($meses, $periodo->mesesGenerablesPensiones());
            $todosExcluidos = array_merge($todosExcluidos, $periodo->mesesExcluidosPensiones());
        }

        $meses = array_values(array_unique(array_map('intval', $meses)));
        sort($meses);

        if (empty($meses)) {
            return [];
        }

        // Rellenar huecos entre el primer y último mes combinado (ej: jun→ago genera julio).
        // Los meses explícitamente excluidos en algún periodo siguen siendo respetados.
        $minMes = min($meses);
        $maxMes = max($meses);
        if ($maxMes > $minMes) {
            $lookupExcluidos = array_fill_keys(array_unique(array_map('intval', $todosExcluidos)), true);
            $meses = array_values(array_filter(
                range($minMes, $maxMes),
                fn (int $m) => !isset($lookupExcluidos[$m])
            ));
        }

        if ($mesInicioOverride !== null && $mesFinOverride !== null) {
            $mesInicio = max(1, min(12, $mesInicioOverride));
            $mesFin = max(1, min(12, $mesFinOverride));
            if ($mesFin < $mesInicio) {
                $mesFin = $mesInicio;
            }

            $meses = array_values(array_filter(
                $meses,
                fn (int $mes) => $mes >= $mesInicio && $mes <= $mesFin
            ));
        }

        return $meses;
    }

    public static function actualParaAnio(?int $anioId, ?string $fecha = null): ?self
    {
        if (!$anioId) {
            return null;
        }

        $fechaRef = $fecha ? Carbon::parse($fecha) : Carbon::today();

        $query = static::where('anio_escolar_id', $anioId);

        $porFecha = (clone $query)
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_fin')
            ->whereDate('fecha_inicio', '<=', $fechaRef)
            ->whereDate('fecha_fin', '>=', $fechaRef)
            ->orderBy('orden')
            ->first();

        if ($porFecha) {
            return $porFecha;
        }

        $activo = (clone $query)
            ->where('activo', true)
            ->orderBy('orden')
            ->first();

        return $activo ?: (clone $query)->orderBy('orden')->first();
    }
}
