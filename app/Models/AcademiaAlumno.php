<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODULO 10 - Real Academia (RF-52 a RF-55).
 *
 * Un alumno regular es estudiante del colegio y se enlaza a `estudiantes`;
 * un estudiante libre es externo y guarda aqui sus propios datos. RF-54
 * diferencia la pension entre ambos.
 */
class AcademiaAlumno extends Model
{
    protected $table = 'academia_alumnos';

    protected $fillable = [
        'anio_escolar_id', 'tipo', 'estudiante_id', 'nombres', 'apellidos', 'dni',
        'telefono', 'area', 'pension', 'universidad_destino', 'ingreso_universidad',
        'evidencia_ingreso', 'activo',
    ];

    protected $casts = [
        'pension'             => 'decimal:2',
        'ingreso_universidad' => 'boolean',
        'activo'              => 'boolean',
    ];

    public const TIPOS = [
        'regular' => 'Alumno del colegio',
        'libre'   => 'Estudiante libre',
    ];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function asistencias()
    {
        return $this->hasMany(AcademiaAsistencia::class, 'alumno_id');
    }

    public function evaluaciones()
    {
        return $this->hasMany(AcademiaEvaluacion::class, 'alumno_id');
    }

    public function materiales()
    {
        return $this->hasMany(AcademiaMaterial::class, 'alumno_id');
    }

    /** El nombre sale del estudiante enlazado o de los datos propios. */
    public function nombreCompleto(): string
    {
        if ($this->estudiante) {
            return trim($this->estudiante->apellidos . ', ' . $this->estudiante->nombres);
        }

        return trim(($this->apellidos ?? '') . ', ' . ($this->nombres ?? '')) ?: 'Sin nombre';
    }

    public function documentoIdentidad(): ?string
    {
        return $this->estudiante->dni ?? $this->dni;
    }
}
