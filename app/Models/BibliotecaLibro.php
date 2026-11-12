<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibliotecaLibro extends Model
{
    protected $table = 'biblioteca_libros';

    protected $fillable = [
        'titulo','autor','editorial','codigo','nivel','grado_id','ejemplares','activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function lecturas()
    {
        return $this->hasMany(BibliotecaLectura::class, 'libro_id');
    }
}
