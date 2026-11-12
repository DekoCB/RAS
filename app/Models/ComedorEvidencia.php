<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComedorEvidencia extends Model
{
    protected $table = 'comedor_evidencias';

    protected $fillable = ['fecha','tipo','archivo','descripcion','subido_por'];

    protected $casts = ['fecha' => 'date'];
}
