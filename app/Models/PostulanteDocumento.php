<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostulanteDocumento extends Model
{
    protected $table = 'postulante_documentos';

    protected $fillable = ['postulante_id','tipo','archivo','nombre_original','subido_por'];

    /** RF-14: documentos exigidos por el reglamento. */
    public const OBLIGATORIOS = [
        'dni_padre'            => 'Copia de DNI del padre',
        'dni_madre'            => 'Copia de DNI de la madre',
        'dni_postulante'       => 'Copia de DNI del postulante',
        'boleta_notas'         => 'Boleta de calificaciones',
        'constancia_no_adeudo' => 'Constancia de no adeudo',
    ];

    public const OPCIONALES = [
        'carta_fe'  => 'Carta de declaracion de fe',
        'test_pastoral' => 'Test pastoral',
        'otro'      => 'Otro documento',
    ];

    public static function tipos(): array
    {
        return self::OBLIGATORIOS + self::OPCIONALES;
    }

    public function etiqueta(): string
    {
        return self::tipos()[$this->tipo] ?? $this->tipo;
    }

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }
}
