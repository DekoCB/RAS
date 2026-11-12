<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aula extends Model
{
    protected $guarded = [];

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class);
    }
}
