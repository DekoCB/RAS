<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    protected $table = 'postulantes';

    protected $fillable = [
        'codigo', 'anio_escolar_id', 'sede_id', 'nivel', 'grado_id',
        'nombres', 'apellidos', 'dni', 'fecha_nacimiento', 'sexo',
        'direccion', 'colegio_procedencia',
        'apoderado_nombres', 'apoderado_apellidos', 'apoderado_dni',
        'apoderado_parentesco', 'apoderado_fecha_nacimiento', 'apoderado_sexo',
        'apoderado_estado_civil', 'apoderado_ciudad', 'apoderado_direccion',
        'apoderado_telefono', 'apoderado_email',
        'apoderado2_parentesco', 'apoderado2_dni', 'apoderado2_nombres', 'apoderado2_apellidos',
        'apoderado2_fecha_nacimiento', 'apoderado2_sexo', 'apoderado2_estado_civil',
        'apoderado2_ciudad', 'apoderado2_direccion', 'apoderado2_telefono', 'apoderado2_email',
        'estado', 'paso_actual', 'observacion',
        'resultado_publicado_at', 'resultado_canal', 'registrado_por', 'matricula_id',
        'origen', 'token_seguimiento', 'preinscrito_at', 'acepta_terminos',
    ];

    protected $casts = [
        'fecha_nacimiento'           => 'date',
        'apoderado_fecha_nacimiento' => 'date',
        'apoderado2_fecha_nacimiento' => 'date',
        'resultado_publicado_at' => 'datetime',
        'preinscrito_at' => 'datetime',
        'acepta_terminos' => 'boolean',
    ];

    /** Etapas formales del proceso (RF-15, RF-16, RF-19). */
    public const ETAPAS = [
        'informacion_padres'      => 'Informacion a los padres de familia',
        'entrevista_presentacion' => 'Entrevista de presentacion y preguntas',
        'cronograma_compromisos'  => 'Entrega de cronograma y compromisos',
        'postulacion'             => 'Postulacion',
        'entrevista_pastoral'     => 'Entrevista pastoral (test y carta de fe)',
        'socializacion'           => 'Socializacion del sistema pedagogico',
        'examen'                  => 'Examen de admision',
    ];

    public const ESTADOS = [
        'preinscrito' => 'Pre-inscrito (web)',
        'borrador'    => 'Borrador',
        'en_proceso'  => 'En proceso',
        'admitido'    => 'Admitido',
        'no_admitido' => 'No admitido',
        'desistido'   => 'Desistio',
    ];

    public const TOTAL_PASOS = 5;

    /** De donde salio el expediente. */
    public const ORIGENES = [
        'presencial' => 'Registrado en secretaria',
        'web'        => 'Pre-inscripcion web',
    ];

    /** Estados en los que el asistente de 5 pasos todavia se puede retomar. */
    public const ESTADOS_EN_CAPTURA = ['borrador', 'preinscrito'];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function documentos()
    {
        return $this->hasMany(PostulanteDocumento::class, 'postulante_id');
    }

    public function etapas()
    {
        return $this->hasMany(PostulanteEtapa::class, 'postulante_id');
    }

    public function pagos()
    {
        return $this->hasMany(PostulantePago::class, 'postulante_id');
    }

    public function examenes()
    {
        return $this->hasMany(PostulanteExamen::class, 'postulante_id');
    }

    public function nombreCompleto(): string
    {
        return trim($this->apellidos . ', ' . $this->nombres);
    }

    /** Matricula generada a partir de este expediente (solo admitidos). */
    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    /** Un admitido puede pasar a matricula mientras no tenga una enlazada. */
    public function puedeMatricularse(): bool
    {
        return $this->estado === 'admitido' && !$this->matricula_id;
    }

    /**
     * Datos del expediente en el formato del Paso 2 del asistente de matricula,
     * para que la secretaria no vuelva a digitarlos.
     */
    public function datosParaMatricula(): array
    {
        $tieneSegundo = trim((string) $this->apoderado2_nombres) !== '';

        return [
            'tiene_segundo_apoderado' => $tieneSegundo ? '1' : '0',
        ] + $this->apoderadoParaMatricula('apoderado', 'apoderado')
          + ($tieneSegundo ? $this->apoderadoParaMatricula('apoderado2', 'apoderado2') : []) + [
            'situacion_alumno'        => 'Nuevo ingreso',
            'institucion_procedencia' => $this->colegio_procedencia,
            'alumno_dni'              => preg_replace('/\D+/', '', (string) $this->dni),
            'alumno_nombres'          => $this->nombres,
            'alumno_apellidos'        => $this->apellidos,
            'alumno_nacimiento'       => $this->fecha_nacimiento?->toDateString(),
            'alumno_sexo'             => ['M' => 'Masculino', 'F' => 'Femenino'][$this->sexo] ?? '',
            'alumno_direccion'        => $this->direccion,
        ];
    }

    /** Campos de un apoderado del expediente en el formato del Paso 2 de matricula. */
    private function apoderadoParaMatricula(string $origen, string $destino): array
    {
        $parentesco = mb_strtolower((string) $this->{"{$origen}_parentesco"});
        $relacion = match (true) {
            str_contains($parentesco, 'madre'), str_contains($parentesco, 'mam') => 'Madre',
            str_contains($parentesco, 'padre'), str_contains($parentesco, 'pap') => 'Padre',
            $parentesco !== '' => 'Tutor',
            default => '',
        };

        return [
            "{$destino}_relacion"     => $relacion,
            "{$destino}_dni"          => preg_replace('/\D+/', '', (string) $this->{"{$origen}_dni"}),
            "{$destino}_nombres"      => $this->{"{$origen}_nombres"},
            "{$destino}_apellidos"    => $this->{"{$origen}_apellidos"},
            "{$destino}_nacimiento"   => $this->{"{$origen}_fecha_nacimiento"}?->toDateString(),
            "{$destino}_sexo"         => ['M' => 'Masculino', 'F' => 'Femenino'][$this->{"{$origen}_sexo"}] ?? '',
            "{$destino}_estado_civil" => $this->{"{$origen}_estado_civil"},
            "{$destino}_ciudad"       => $this->{"{$origen}_ciudad"},
            "{$destino}_direccion"    => $this->{"{$origen}_direccion"},
            "{$destino}_telefono"     => $this->{"{$origen}_telefono"},
            "{$destino}_email"        => $this->{"{$origen}_email"},
        ];
    }

    /** El derecho de admision esta cubierto cuando hay un pago validado. */
    public function pagoValidado(): bool
    {
        return $this->pagos->contains(fn ($p) => $p->estado === 'validado');
    }

    /** RF-14: los cuatro documentos que el reglamento exige. */
    public function documentosFaltantes(): array
    {
        $cargados = $this->documentos->pluck('tipo')->all();

        return array_values(array_diff(
            array_keys(PostulanteDocumento::OBLIGATORIOS),
            $cargados
        ));
    }

    public function genera(string $etapa): ?PostulanteEtapa
    {
        return $this->etapas->firstWhere('etapa', $etapa);
    }

    /**
     * "Inicial 2 anos" en vez de "Inicial - Inicial 2 anos": el nombre del
     * grado ya suele traer el nivel dentro.
     */
    public function nivelGrado(): string
    {
        $grado = $this->grado?->nombre;

        if (!$grado) {
            return (string) $this->nivel;
        }

        return str_contains(mb_strtolower($grado), mb_strtolower((string) $this->nivel))
            ? $grado
            : $this->nivel . ' - ' . $grado;
    }

    public function esWeb(): bool
    {
        return $this->origen === 'web';
    }

    /** El expediente sigue en captura: secretaria puede retomar el asistente. */
    public function enCaptura(): bool
    {
        return in_array($this->estado, self::ESTADOS_EN_CAPTURA, true);
    }

    /** Genera el siguiente codigo correlativo del anio. */
    public static function siguienteCodigo(int $anioEscolarId, string $anio): string
    {
        $ultimo = static::where('anio_escolar_id', $anioEscolarId)->max('id');

        return sprintf('ADM-%s-%04d', $anio, ($ultimo ?? 0) + 1);
    }
}
