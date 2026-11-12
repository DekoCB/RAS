<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODULO 9 - Psicologia (RF-50, RF-51).
 * El contenido es sensible: RNF-05 exige proteger su confidencialidad,
 * por eso el acceso se restringe por permiso y cada atencion se marca.
 */
class PsicologiaAtencion extends Model
{
    protected $table = 'psicologia_atenciones';

    protected $fillable = [
        'estudiante_id', 'area', 'motivo', 'fecha', 'responsable', 'estado',
        'observacion', 'acuerdos', 'proxima_cita', 'confidencial', 'registrado_por',
    ];

    protected $casts = [
        'fecha'        => 'date',
        'proxima_cita' => 'date',
        'confidencial' => 'boolean',
    ];

    /** RF-50: las siete areas de trabajo del departamento. */
    public const AREAS = [
        'evaluacion'   => 'Evaluacion psicopedagogica',
        'prevencion'   => 'Prevencion y promocion de la salud mental',
        'docentes'     => 'Orientacion y asesoria a docentes',
        'familias'     => 'Trabajo con familias',
        'vocacional'   => 'Orientacion vocacional y personal',
        'inclusion'    => 'Inclusion y atencion a la diversidad',
        'institucional' => 'Gestion institucional',
    ];

    public const ESTADOS = [
        'abierta'        => 'Abierta',
        'en_seguimiento' => 'En seguimiento',
        'cerrada'        => 'Cerrada',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function etiquetaArea(): string
    {
        return self::AREAS[$this->area] ?? $this->area;
    }
}
