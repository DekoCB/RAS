<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curso extends Model
{
    protected $guarded = [];

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function secciones(): BelongsToMany
    {
        return $this->belongsToMany(Seccion::class, 'seccion_curso')
            ->withPivot(['anio_escolar_id', 'periodo_academico_id', 'docente_id'])
            ->withTimestamps();
    }
}
