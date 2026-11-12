<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitucionValor extends Model
{
    protected $table = 'institucion_valores';

    protected $fillable = ['nombre','descripcion','orden','activo'];

    protected $casts = ['activo' => 'boolean'];
}
