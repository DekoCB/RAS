<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = 'institucion';

    protected $fillable = [
        'nombre','sigla','lema','vision','mision','ciudad',
        'telefono_central','telefono_secundaria','telefono_adicional','email',
        'codigos_modulares','himno_titulo','himno_autor','himno_audio','video_institucional',
    ];

    protected $casts = [
        'codigos_modulares' => 'array',
    ];

    /** Fila unica de configuracion; se crea vacia si aun no existe. */
    public static function actual(): self
    {
        return static::firstOrCreate([], ['nombre' => 'Institucion Educativa']);
    }

    public function valores()
    {
        return InstitucionValor::where('activo', true)->orderBy('orden');
    }

    public function frases()
    {
        return InstitucionFrase::where('activo', true)->orderBy('orden');
    }
}
