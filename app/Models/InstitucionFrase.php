<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitucionFrase extends Model
{
    protected $table = 'institucion_frases';

    protected $fillable = ['frase','area','orden','activo'];

    protected $casts = ['activo' => 'boolean'];
}
