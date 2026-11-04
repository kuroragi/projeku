<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Detak jantung scheduler (Tahap 0)
Schedule::call(fn () => Log::channel('heartbeat')->info('scheduler ok'))
    ->everyMinute()
    ->name('heartbeat');

// Pengingat email kartu jatuh tempo (Tahap 3)
Schedule::command('kds:send-reminders')->everyMinute();

// Cadangan basis data harian pukul 02:00 WIB (Tahap 5)
Schedule::command('kds:backup-db')->dailyAt('02:00');

// Pemantauan kapasitas storage dan inode berkas harian (Tahap 5)
Schedule::command('kds:monitor-storage')->dailyAt('03:00');
