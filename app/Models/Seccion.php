<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Seccion extends Model
{
    protected $table = 'secciones';

    protected $guarded = [];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'seccion_curso')
            ->withPivot(['anio_escolar_id', 'periodo_academico_id', 'docente_id'])
            ->withTimestamps();
    }
}
