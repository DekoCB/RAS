<?php

namespace App\Support;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Str;

class CarnetCode
{
    public static function normalize(?string $value): string
    {
        $value = rawurldecode((string) $value);

        if ($value === '') {
            return '';
        }

        $value = str_replace(
            ["\u{00A0}", "\u{200B}", "\u{200C}", "\u{200D}", "\u{FEFF}", '–', '—', '−'],
            ['', '', '', '', '', '-', '-', '-'],
            $value
        );

        $value = preg_replace('/[\r\n\t ]+/u', '', $value) ?? $value;
        $value = preg_replace('/[[:cntrl:]]/u', '', $value) ?? $value;

        return Str::upper(trim($value));
    }

    public static function isSame(?string $left, ?string $right): bool
    {
        $left = self::normalize($left);
        $right = self::normalize($right);

        return $left !== '' && $left === $right;
    }

    public static function findDocente(string $value): ?Docente
    {
        $code = self::normalize($value);

        if ($code === '') {
            return null;
        }

        return self::findInModel(Docente::class, $code);
    }

    public static function findUser(string $value): ?User
    {
        $code = self::normalize($value);

        if ($code === '') {
            return null;
        }

        return self::findInModel(User::class, $code);
    }

    public static function findEstudiante(string $value): ?Estudiante
    {
        $code = self::normalize($value);

        if ($code === '') {
            return null;
        }

        return self::findInModel(Estudiante::class, $code);
    }

    public static function generateDocenteCode(Docente $docente): string
    {
        $preferred = self::stableCode('QR-DOC', $docente->dni);

        if ($preferred !== null && !self::codeExists(Docente::class, $preferred, $docente->id)) {
            return $preferred;
        }

        return self::randomUniqueCode('QR-DOC', Docente::class, $docente->id);
    }

    public static function generateUserCode(User $user): string
    {
        $prefix = self::userPrefix($user);
        $preferred = self::stableCode($prefix, $user->dni);

        if ($preferred !== null && !self::codeExists(User::class, $preferred, $user->id)) {
            return $preferred;
        }

        return self::randomUniqueCode($prefix, User::class, $user->id);
    }

    private static function findInModel(string $modelClass, string $code)
    {
        $record = $modelClass::query()
            ->whereRaw('UPPER(TRIM(carnet_codigo)) = ?', [$code])
            ->first();

        if ($record) {
            return $record;
        }

        return $modelClass::query()
            ->whereNotNull('carnet_codigo')
            ->get()
            ->first(fn ($model) => self::isSame($model->carnet_codigo, $code));
    }

    private static function stableCode(string $prefix, ?string $dni): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $dni) ?? '';

        if ($digits === '') {
            return null;
        }

        return sprintf('%s-%s-%s', $prefix, now()->format('Y'), $digits);
    }

    private static function userPrefix(User $user): string
    {
        if ($user->hasRole('Auxiliar')) {
            return 'QR-AUX';
        }

        if ($user->hasRole('Secretaria')) {
            return 'QR-SEC';
        }

        if ($user->hasRole('Director')) {
            return 'QR-DIR';
        }

        if ($user->hasRole('SuperAdmin')) {
            return 'QR-ADM';
        }

        return 'QR-USR';
    }

    private static function randomUniqueCode(string $prefix, string $modelClass, ?int $ignoreId = null): string
    {
        do {
            $code = sprintf('%s-%s-%s', $prefix, now()->format('Y'), Str::upper(Str::random(8)));
        } while (self::codeExists($modelClass, $code, $ignoreId));

        return $code;
    }

    private static function codeExists(string $modelClass, string $code, ?int $ignoreId = null): bool
    {
        return $modelClass::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->whereRaw('UPPER(TRIM(carnet_codigo)) = ?', [self::normalize($code)])
            ->exists();
    }
}
