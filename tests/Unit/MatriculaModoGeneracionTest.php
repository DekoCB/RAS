<?php

namespace Tests\Unit;

use App\Http\Controllers\Secretaria\MatriculaController;
use PHPUnit\Framework\TestCase;

class MatriculaModoGeneracionTest extends TestCase
{
    public function test_modo_todo_se_conserva(): void
    {
        $this->assertSame('todo', MatriculaController::normalizarModoPensiones('todo', false, false));
    }

    public function test_modo_n_meses_se_conserva(): void
    {
        $this->assertSame('n_meses', MatriculaController::normalizarModoPensiones('n_meses', false, true));
    }

    public function test_modo_rango_se_conserva(): void
    {
        $this->assertSame('rango', MatriculaController::normalizarModoPensiones('rango', false, true));
    }

    public function test_modo_solo_matricula_requiere_confirmacion_explicita(): void
    {
        $this->assertSame('solo_matricula', MatriculaController::normalizarModoPensiones('solo_matricula', true, false));
        $this->assertSame('todo', MatriculaController::normalizarModoPensiones('solo_matricula', false, false));
        $this->assertSame('todo', MatriculaController::normalizarModoPensiones('solo_matricula', true, true));
    }
}

