<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Mode demo (DEMO_MODE=true di .env): data dikembalikan ke data contoh setiap malam,
| supaya demo yang dicoba calon klien selalu rapi dan tanggalnya selalu "hari ini".
*/
Artisan::command('demo:reset', function () {
    if (! config('app.demo_mode') && ! $this->confirm('DEMO_MODE tidak aktif. Tetap hapus semua data dan isi ulang data contoh?')) {
        return;
    }

    $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    $this->info('Data demo sudah direset.');
})->purpose('Hapus semua data dan isi ulang data contoh (untuk demo online)');

Schedule::command('demo:reset')
    ->dailyAt('02:00')
    ->when(fn () => config('app.demo_mode'));
