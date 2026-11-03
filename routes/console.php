<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Detak jantung scheduler (Tahap 0)
Schedule::call(fn () => Log::info('Detak jantung scheduler berjalan'))->everyMinute();

// Pengingat email kartu jatuh tempo (Tahap 3)
Schedule::command('kds:send-reminders')->everyMinute();
