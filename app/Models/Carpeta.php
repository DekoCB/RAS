<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carpeta extends Model
{
    protected $table = 'carpetas';

    protected $fillable = ['nombre', 'parent_id', 'creado_por'];

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subcarpetas(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('nombre');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'carpeta_id')->orderBy('nombre');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /** Ruta desde la raiz hasta esta carpeta, para las migas de pan. */
    public function cadena(): array
    {
        $cadena = [];
        $actual = $this;

        // Se sube por los padres; el tope evita un bucle si los datos
        // quedaran inconsistentes.
        for ($i = 0; $actual && $i < 50; $i++) {
            array_unshift($cadena, $actual);
            $actual = $actual->padre;
        }

        return $cadena;
    }

    /** Todo lo que cuelga de esta carpeta, ella incluida. */
    public function ramaIds(): array
    {
        $ids = [$this->id];

        foreach ($this->subcarpetas as $hija) {
            $ids = array_merge($ids, $hija->ramaIds());
        }

        return $ids;
    }

    /** Cuenta archivos y carpetas de toda la rama, para avisar antes de borrar. */
    public function contenidoTotal(): array
    {
        $ids = $this->ramaIds();

        return [
            'carpetas' => count($ids) - 1,
            'archivos' => Archivo::whereIn('carpeta_id', $ids)->count(),
        ];
    }
}
