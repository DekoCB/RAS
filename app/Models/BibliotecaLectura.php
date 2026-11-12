<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibliotecaLectura extends Model
{
    protected $table = 'biblioteca_lecturas';

    protected $fillable = [
        'estudiante_id','libro_id','titulo','autor','editorial',
        'tiempo_lectura','resumen','fecha_inicio','fecha_fin','estado','registrado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function libro()
    {
        return $this->belongsTo(BibliotecaLibro::class, 'libro_id');
    }

    /** La ficha de resumen (RF-46) esta completa cuando tiene los cuatro campos. */
    public function fichaCompleta(): bool
    {
        return filled($this->autor) && filled($this->editorial)
            && filled($this->tiempo_lectura) && filled($this->resumen);
    }
}
