<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archivo extends Model
{
    protected $table = 'archivos';

    protected $fillable = [
        'carpeta_id', 'nombre', 'ruta', 'extension', 'mime', 'tamano', 'subido_por',
    ];

    protected $casts = ['tamano' => 'integer'];

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class, 'carpeta_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /** "1.4 MB" en vez de 1468006. */
    public function pesoLegible(): string
    {
        $bytes = (int) $this->tamano;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        foreach (['KB', 'MB', 'GB'] as $unidad) {
            $bytes /= 1024;

            if ($bytes < 1024) {
                return round($bytes, $bytes < 10 ? 1 : 0) . ' ' . $unidad;
            }
        }

        return round($bytes, 1) . ' TB';
    }

    /** Familia del archivo, para el icono y el color de la fila. */
    public function tipo(): string
    {
        return match (strtolower((string) $this->extension)) {
            'pdf' => 'pdf',
            'doc', 'docx', 'odt', 'rtf', 'txt' => 'documento',
            'xls', 'xlsx', 'csv', 'ods' => 'hoja',
            'ppt', 'pptx', 'odp' => 'presentacion',
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg', 'heic' => 'imagen',
            'mp4', 'avi', 'mov', 'mkv', 'webm' => 'video',
            'mp3', 'wav', 'ogg', 'm4a' => 'audio',
            'zip', 'rar', '7z' => 'comprimido',
            default => 'otro',
        };
    }

    public function esImagen(): bool
    {
        return $this->tipo() === 'imagen';
    }

    /** Se puede abrir en el navegador sin descargarlo. */
    public function seVePorDentro(): bool
    {
        return in_array($this->tipo(), ['pdf', 'imagen', 'video', 'audio'], true);
    }
}
