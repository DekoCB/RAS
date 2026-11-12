<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComedorRegistro extends Model
{
    protected $table = 'comedor_registros';

    protected $fillable = [
        'fecha','estudiante_id','docente_id','origen','hora_recepcion',
        'monto','pagado','observacion','registrado_por',
    ];

    protected $casts = [
        'fecha'  => 'date',
        'pagado' => 'boolean',
        'monto'  => 'decimal:2',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function scopeDelDia($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function nombrePersona(): string
    {
        $p = $this->estudiante ?? $this->docente;

        return $p ? trim($p->apellidos . ', ' . $p->nombres) : '--';
    }
}
