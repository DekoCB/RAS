<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sedes';

    protected $fillable = [
        'nombre','direccion','referencia','telefono','niveles',
        'imagen_portada','mapa_url','orden','activo',
    ];

    protected $casts = [
        'niveles' => 'array',
        'activo'  => 'boolean',
    ];

    public function documentos()
    {
        return $this->hasMany(SedeDocumento::class, 'sede_id');
    }

    public function atiendeNivel(string $nivel): bool
    {
        return in_array($nivel, $this->niveles ?? [], true);
    }

    /** "Primaria y Secundaria" a partir de los niveles marcados. */
    public function etiquetaNiveles(): string
    {
        $niveles = array_values($this->niveles ?? []);

        if (count($niveles) < 2) {
            return $niveles[0] ?? '';
        }

        $ultimo = array_pop($niveles);

        return implode(', ', $niveles) . ' y ' . $ultimo;
    }

    /** Como se nombra el local en el sitio publico: "Local 1 - Inicial". */
    public function etiquetaPublica(): string
    {
        $niveles = $this->etiquetaNiveles();

        return $niveles === '' ? $this->nombre : $this->nombre . ' - ' . $niveles;
    }
}
