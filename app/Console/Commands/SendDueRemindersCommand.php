<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendDueRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kds:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa dan mengirim pengingat kartu yang jatuh tempo dalam ringkasan email harian';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa pengingat kartu jatuh tempo...');

        $sentCount = ReminderService::sendDueReminders();

        $this->info("Proses selesai. {$sentCount} email pengingat berhasil dikirim/diantrekan.");

        return Command::SUCCESS;
    }
}

