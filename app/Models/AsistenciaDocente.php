<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaDocente extends Model
{
    protected $table = 'asistencias_docentes';

    protected $fillable = [
        'anio_escolar_id',
        'docente_id',
        'fecha',
        'tipo',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}