<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kds:create-admin {email} {name} {--print-link : Cetak tautan penetapan kata sandi ke terminal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat pengguna admin pertama dengan kata sandi acak dan mengirim/mencetak tautan aktivasi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->argument('name');

        if (User::where('email', $email)->exists()) {
            $this->error("Pengguna dengan email [{$email}] sudah terdaftar.");
            return Command::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(32),
            'is_admin' => true,
            'notify_email' => true,
        ]);

        $token = Password::broker()->createToken($user);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ], false));

        if ($this->option('print-link')) {
            $this->info("Admin [{$name}] berhasil dibuat.");
            $this->line("Tautan penetapan kata sandi: {$resetUrl}");
        } else {
            // Kirim email notifikasi reset
            $user->sendPasswordResetNotification($token);
            $this->info("Admin [{$name}] berhasil dibuat dan email undangan telah dikirim ke [{$email}].");
        }

        return Command::SUCCESS;
    }
}

