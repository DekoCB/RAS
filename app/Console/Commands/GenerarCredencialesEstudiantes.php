<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Crea la cuenta de acceso de los estudiantes que aun no la tienen.
 *
 * En el flujo normal la cuenta nace dentro del asistente de matricula; este
 * comando cubre a los alumnos que entraron por otra via (una carga masiva o
 * una restauracion) y quedaron sin usuario.
 *
 * Usa la misma convencion que el asistente: el DNI es a la vez el usuario y
 * la contrasena temporal, que se guarda cifrada para que secretaria pueda
 * consultarla despues.
 */
class GenerarCredencialesEstudiantes extends Command
{
    protected $signature = 'estudiantes:generar-credenciales
                            {--dry-run : Solo muestra a quien se le crearia la cuenta, sin escribir nada}
                            {--recifrar : Vuelve a cifrar la contrasena temporal de todos, tras cambiar APP_KEY}';

    protected $description = 'Crea usuario y contrasena de los estudiantes que todavia no tienen cuenta';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($this->option('recifrar')) {
            return $this->recifrar($dryRun);
        }

        $pendientes = Estudiante::whereNull('user_id')->orderBy('apellidos')->get();

        if ($pendientes->isEmpty()) {
            $this->info('Todos los estudiantes ya tienen cuenta.');

            return self::SUCCESS;
        }

        $this->info("Estudiantes sin cuenta: {$pendientes->count()}");

        $creados = 0;
        $enlazados = 0;
        $omitidos = [];

        foreach ($pendientes as $estudiante) {
            $dni = trim((string) $estudiante->dni);

            // Sin DNI no hay usuario ni contrasena que generar.
            if ($dni === '') {
                $omitidos[] = $estudiante->apellidos . ', ' . $estudiante->nombres . ' (sin DNI)';
                continue;
            }

            if ($dryRun) {
                $creados++;
                continue;
            }

            DB::transaction(function () use ($estudiante, $dni, &$creados, &$enlazados) {
                // Puede existir ya un usuario con ese DNI (por ejemplo, si el
                // alumno tuvo cuenta antes): se reaprovecha en vez de duplicar.
                $user = User::where('dni', $dni)->orWhere('email', $dni)->first();

                if ($user) {
                    $enlazados++;
                } else {
                    $user = User::create([
                        'name'     => trim($estudiante->nombres . ' ' . $estudiante->apellidos),
                        'email'    => $dni,
                        'dni'      => $dni,
                        'password' => $dni,   // el modelo lo cifra solo
                    ]);
                    $creados++;
                }

                if (!$user->hasRole('Estudiante')) {
                    $user->assignRole('Estudiante');
                }

                $estudiante->update([
                    'user_id'           => $user->id,
                    'password_temporal' => Crypt::encryptString($dni),
                ]);
            });
        }

        if ($dryRun) {
            $this->warn("[simulacion] Se crearian {$creados} cuenta(s). No se escribio nada.");
        } else {
            $this->info("Cuentas creadas: {$creados}");

            if ($enlazados > 0) {
                $this->info("Usuarios ya existentes enlazados: {$enlazados}");
            }
        }

        if ($omitidos) {
            $this->newLine();
            $this->warn('Sin cuenta por falta de DNI (' . count($omitidos) . '):');
            foreach ($omitidos as $quien) {
                $this->line('  - ' . $quien);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Cambiar APP_KEY deja ilegible lo que se cifro con la clave anterior.
     * La contrasena temporal es el DNI del alumno, asi que no hay que
     * descifrar nada: basta con volver a cifrarlo con la clave nueva.
     */
    private function recifrar(bool $dryRun): int
    {
        $conDni = Estudiante::whereNotNull('user_id')
            ->where('dni', '<>', '')
            ->whereNotNull('dni')
            ->get();

        $this->info("Estudiantes a recifrar: {$conDni->count()}");

        if ($dryRun) {
            $this->warn('[simulacion] No se escribio nada.');

            return self::SUCCESS;
        }

        $hechos = 0;

        foreach ($conDni as $estudiante) {
            $estudiante->update([
                'password_temporal' => Crypt::encryptString(trim((string) $estudiante->dni)),
            ]);
            $hechos++;
        }

        $this->info("Contrasenas temporales recifradas: {$hechos}");
        $this->line('La contrasena de cada alumno sigue siendo su DNI; solo cambio el cifrado.');

        return self::SUCCESS;
    }
}
