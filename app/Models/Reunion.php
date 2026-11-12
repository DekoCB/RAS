<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reunion extends Model
{
    protected $table = 'reuniones';

    protected $fillable = [
        'tipo', 'titulo', 'agenda', 'fecha', 'lugar', 'responsable', 'nivel', 'grado_id',
        'estado', 'acuerdos', 'acta_archivo', 'asistentes', 'notificada', 'registrado_por',
    ];

    protected $casts = [
        'fecha'      => 'datetime',
        'notificada' => 'boolean',
    ];

    /** RF-43, RF-44 y RF-64. */
    public const TIPOS = [
        'coordinacion_docentes' => 'Coordinacion con docentes',
        'docentes_padres'       => 'Docentes con padres de familia',
        'padres_familia'        => 'Reunion de padres de familia',
    ];

    public const ESTADOS = [
        'programada' => 'Programada',
        'realizada'  => 'Realizada',
        'cancelada'  => 'Cancelada',
    ];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function etiquetaTipo(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
