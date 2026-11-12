<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 20)->nullable()->after('email');
        });

        $this->syncFromDocentes();
        $this->syncFromEstudiantes();
        $this->syncFromKnownUsers();
        $this->syncFromNumericEmails();
        $this->syncFallbackForRemainingUsers();

        Schema::table('users', function (Blueprint $table) {
            $table->unique('dni');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_dni_unique');
            $table->dropColumn('dni');
        });
    }

    private function syncFromDocentes(): void
    {
        DB::table('docentes')
            ->whereNotNull('user_id')
            ->whereNotNull('dni')
            ->select('id', 'user_id', 'dni')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $this->assignDniAndPassword((int) $row->user_id, (string) $row->dni);
                }
            });
    }

    private function syncFromEstudiantes(): void
    {
        DB::table('estudiantes')
            ->whereNotNull('user_id')
            ->whereNotNull('dni')
            ->select('id', 'user_id', 'dni')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $this->assignDniAndPassword((int) $row->user_id, (string) $row->dni);
                }
            });
    }

    private function syncFromKnownUsers(): void
    {
        $knownDniByEmail = [
            'jeanpiaget_oxa@hotmail.com' => '20099909',
            'mirellacantalicioperez@gmail.com' => '71202328',
            'katissazehnder15@gmail.com' => '77498515',
        ];

        foreach ($knownDniByEmail as $email => $dni) {
            $userId = DB::table('users')->where('email', $email)->value('id');
            if ($userId) {
                $this->assignDniAndPassword((int) $userId, $dni);
            }
        }
    }

    private function syncFromNumericEmails(): void
    {
        $users = DB::table('users')
            ->whereNull('dni')
            ->select('id', 'email')
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            $this->assignDniAndPassword((int) $user->id, (string) $user->email);
        }
    }

    private function syncFallbackForRemainingUsers(): void
    {
        $userIds = DB::table('users')
            ->whereNull('dni')
            ->orderBy('id')
            ->pluck('id');

        foreach ($userIds as $userId) {
            $base = 90000000 + (int) $userId;
            $dni = (string) $base;

            while (DB::table('users')->where('dni', $dni)->exists()) {
                $base++;
                $dni = (string) $base;
            }

            $this->assignDniAndPassword((int) $userId, $dni);
        }
    }

    private function assignDniAndPassword(int $userId, string $dni): void
    {
        $dni = $this->normalizeDni($dni);
        if ($dni === '') {
            return;
        }

        $alreadyUsed = DB::table('users')
            ->where('dni', $dni)
            ->where('id', '<>', $userId)
            ->exists();

        if ($alreadyUsed) {
            return;
        }

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'dni' => $dni,
                'password' => Hash::make($dni),
                'updated_at' => now(),
            ]);
    }

    private function normalizeDni(string $dni): string
    {
        return preg_replace('/\D+/', '', trim($dni)) ?? '';
    }
};

