<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ============================================================
// ⏰ LEMBRETES DE EVENTOS
// ============================================================
Schedule::command('eventos:lembretes --tipo=24h')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->description('Lembretes de eventos 24h antes');

Schedule::command('eventos:lembretes --tipo=1h')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->description('Lembretes de eventos 1h antes');


// ============================================================
// 🎂 PARABÉNS AOS ANIVERSARIANTES
// ============================================================
Schedule::command('egressos:parabens')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->description('Parabéns aos egressos aniversariantes');