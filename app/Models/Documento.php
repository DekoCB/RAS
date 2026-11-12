<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'modulo', 'categoria', 'titulo', 'descripcion', 'nivel', 'anio', 'archivo',
        'nombre_original', 'docente_id', 'estudiante_id', 'confidencial', 'subido_por',
    ];

    protected $casts = ['confidencial' => 'boolean'];

    public const MODULOS = [
        'coordinacion'  => 'Coordinacion Pedagogica',
        'rrhh'          => 'Recursos Humanos',
        'padres'        => 'Padres de familia',
        'legal'         => 'Documentos legales',
        'defensa_civil' => 'Defensa Civil',
        'inmobiliario'  => 'Inmobiliario',
        'pastoral'      => 'Pastoral',
    ];

    /** RF-41: las carpetas que el documento exige para Coordinacion General. */
    public const CATEGORIAS = [
        'coordinacion' => [
            'planificacion'       => 'Planificacion de actividades',
            'evidencias'          => 'Evidencias fotograficas y de video',
            'utiles'              => 'Lista de utiles de los tres niveles',
            'calendarizacion'     => 'Calendarizacion',
            'cv_docentes'         => 'CV de docentes',
            'cumpleanos'          => 'Cumpleanos de docentes',
            'actas'               => 'Actas de reuniones',
            'programas'           => 'Programas de actividades',
            'reglamento_docentes' => 'Reglamento de docentes',
            'declaraciones'       => 'Declaraciones juradas',
            'horarios'            => 'Horarios de los tres niveles',
            'libretas'            => 'Libretas y boletas de notas',
            'biblioteca_grado'    => 'Biblioteca por grado',
            'fichas_lectura'      => 'Fichas de resumen de lectura',
            'examenes'            => 'Examenes y ranking',
            'listas'              => 'Listas de estudiantes',
            'capacitaciones'      => 'Capacitaciones pedagogicas',
            'pasantias'           => 'Pasantias',
            'convenios'           => 'Convenios',
            'informes_docentes'   => 'Informes de docentes por nivel',
            'compromisos'         => 'Cartas de compromiso del padre',
        ],
        'rrhh' => [
            'cv'                 => 'Curriculum vitae',
            'declaracion_jurada' => 'Declaracion jurada',
            'reglamento'         => 'Reglamento de docentes',
            'acta'               => 'Acta de reunion',
            'informe'            => 'Informe de docente',
            'contrato'           => 'Contrato',
            'otro'               => 'Otro documento',
        ],
        'padres' => [
            'compromiso' => 'Carta de compromiso',
            'acta'       => 'Acta de reunion',
            'comunicado' => 'Comunicado',
            'utiles'     => 'Lista de utiles',
            'otro'       => 'Otro documento',
        ],
        'legal' => [
            'licencia'   => 'Licencia',
            'resolucion' => 'Resolucion',
            'contrato'   => 'Contrato',
            'convenio'   => 'Convenio',
            'otro'       => 'Otro documento legal',
        ],
        'defensa_civil' => [
            'certificado' => 'Certificado de Defensa Civil',
            'plan'        => 'Plan de seguridad',
            'informe'     => 'Informe de inspeccion',
            'otro'        => 'Otro documento',
        ],
        'inmobiliario' => [
            'titulo'   => 'Titulo de propiedad',
            'plano'    => 'Plano',
            'tasacion' => 'Tasacion',
            'otro'     => 'Otro documento',
        ],
        'pastoral' => [
            'test'     => 'Test pastoral',
            'carta_fe' => 'Carta de declaracion de fe',
            'programa' => 'Programa pastoral',
            'otro'     => 'Otro documento',
        ],
    ];

    public static function categoriasDe(string $modulo): array
    {
        return self::CATEGORIAS[$modulo] ?? [];
    }

    public function etiquetaCategoria(): string
    {
        return self::categoriasDe($this->modulo)[$this->categoria] ?? $this->categoria;
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function scopeDelModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }
}
