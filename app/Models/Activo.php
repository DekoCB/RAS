<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF-70: modulo inmobiliario y de operaciones de infraestructura. */
class Activo extends Model
{
    protected $table = 'activos';

    protected $fillable = [
        'codigo', 'nombre', 'categoria', 'sede_id', 'ubicacion',
        'fecha_adquisicion', 'valor', 'estado', 'observacion',
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'valor'             => 'decimal:2',
    ];

    public const CATEGORIAS = [
        'inmueble'   => 'Inmueble',
        'mobiliario' => 'Mobiliario',
        'equipo'     => 'Equipo',
        'vehiculo'   => 'Vehiculo',
    ];

    public const ESTADOS = [
        'operativo'     => 'Operativo',
        'mantenimiento' => 'En mantenimiento',
        'baja'          => 'De baja',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function mantenimientos()
    {
        return $this->hasMany(ActivoMantenimiento::class, 'activo_id');
    }
}
