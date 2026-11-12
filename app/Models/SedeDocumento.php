<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SedeDocumento extends Model
{
    protected $table = 'sede_documentos';

    protected $fillable = [
        'sede_id','tipo','titulo','anio','nivel','archivo','publicado','subido_por',
    ];

    protected $casts = ['publicado' => 'boolean'];

    public const TIPOS = [
        'reglamento_admision'  => 'Reglamento de Admision',
        'informacion_general'  => 'Informacion General',
        'reglamento_interno'   => 'Reglamento Interno',
        'informativo'          => 'Informativo',
        'utiles'               => 'Lista de utiles',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
