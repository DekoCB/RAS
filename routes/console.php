<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Limpieza automática cada 4 horas de matrículas EN_PROCESO abandonadas
Schedule::command('matriculas:limpiar-abandonadas --horas=2')
    ->everyFourHours()
    ->withoutOverlapping()
    ->runInBackground();
