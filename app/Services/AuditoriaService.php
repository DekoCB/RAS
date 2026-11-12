<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use Illuminate\Http\Request;

class AuditoriaService
{
    public static function log(?int $userId, string $accion, string $tabla, int $registroId, ?array $cambios = null, ?Request $request = null): void
    {
        AuditoriaLog::create([
            'user_id' => $userId,
            'accion' => $accion,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'cambios' => $cambios,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'fecha' => now(),
        ]);
    }
}
