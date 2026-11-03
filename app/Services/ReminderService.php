<?php

namespace App\Services;

use App\Mail\CardReminderDigestMail;
use App\Models\Card;
use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderService
{
    /**
     * Menyinkronkan jadwal pengingat untuk sebuah kartu:
     * - Dibatalkan jika kartu tidak punya tenggat, masuk kolom is_done, atau berada di tempat sampah.
     * - Dibuat ulang untuk penerima (semua penanggung jawab yang notify_email = true).
     * - Jadwal bawaan: H-1 dan H-0 pukul 08:00 WIB (Asia/Jakarta).
     */
    public static function syncCardReminders(Card $card): void
    {
        // Pastikan relasi kolom termuat
        if (! $card->relationLoaded('column')) {
            $card->load('column');
        }

        $isDone = $card->column ? (bool) $card->column->is_done : false;
        $isTrashed = $card->trashed();
        $hasDueDate = ! empty($card->due_at);

        // Jika kartu selesai, dihapus, atau tidak bertenggat -> batalkan semua pengingat belum terkirim
        if (! $hasDueDate || $isDone || $isTrashed) {
            self::cancelCardReminders($card);
            return;
        }

        // Hapus pengingat lama yang belum terkirim
        self::cancelCardReminders($card);

        // Ambil penanggung jawab aktif yang mengaktifkan notify_email
        $assignees = $card->assignees()
            ->where('notify_email', true)
            ->whereNull('deactivated_at')
            ->get();

        if ($assignees->isEmpty()) {
            return;
        }

        $timezone = config('kds.display_timezone', 'Asia/Jakarta');
        $dueInTz = Carbon::parse($card->due_at)->setTimezone($timezone);

        // 1. Pagi H-1 pukul 08:00 WIB
        $remindHMinus1 = $dueInTz->copy()->subDay()->setTime(8, 0, 0)->utc();

        // 2. Pagi Hari-H (H-0) pukul 08:00 WIB
        $remindH0 = $dueInTz->copy()->setTime(8, 0, 0)->utc();

        $triggerTimes = collect([$remindHMinus1, $remindH0])
            ->filter(fn (Carbon $t) => $t->isFuture())
            ->unique(fn (Carbon $t) => $t->timestamp);

        foreach ($triggerTimes as $triggerTime) {
            foreach ($assignees as $assignee) {
                Reminder::create([
                    'project_id' => $card->project_id,
                    'card_id' => $card->id,
                    'user_id' => $assignee->id,
                    'remind_at' => $triggerTime,
                    'sent_at' => null,
                ]);
            }
        }
    }

    /**
     * Membatalkan seluruh pengingat kartu yang belum terkirim.
     */
    public static function cancelCardReminders(Card $card): void
    {
        Reminder::where('card_id', $card->id)
            ->whereNull('sent_at')
            ->delete();
    }

    /**
     * Memproses dan mengirim seluruh pengingat yang telah jatuh tempo:
     * - Menggabungkan kartu dalam satu email per pengguna (daftar kartu).
     * - Menerapkan jeda antarpesan (30 detik via antrean).
     * - Menghormati batas plafon harian (default 50 email per hari).
     *
     * @return int Jumlah email yang berhasil dikirim/diantrekan
     */
    public static function sendDueReminders(): int
    {
        $dailyCap = (int) config('kds.mail_daily_cap', 50);

        // Hitung email yang sudah terkirim hari ini (berdasarkan sent_at hari ini)
        $todaySentCount = DB::table('reminders')
            ->whereNotNull('sent_at')
            ->whereDate('sent_at', Carbon::today())
            ->distinct('user_id')
            ->count();

        if ($todaySentCount >= $dailyCap) {
            Log::warning("Plafon harian pengiriman email telah tercapai ({$todaySentCount}/{$dailyCap}). Pengingat berikutnya ditunda.");
            return 0;
        }

        // Ambil pengingat yang jatuh tempo dan belum terkirim
        $dueReminders = Reminder::whereNull('sent_at')
            ->where('remind_at', '<=', now())
            ->with(['card' => function ($q) {
                $q->with(['project', 'column']);
            }, 'user'])
            ->get();

        if ($dueReminders->isEmpty()) {
            return 0;
        }

        // Filter pengingat yang valid (kartu belum selesai, pengguna aktif & mengaktifkan email)
        $validReminders = $dueReminders->filter(function (Reminder $r) {
            if (! $r->user || ! $r->user->isActive() || ! $r->user->notify_email) {
                return false;
            }
            if (! $r->card || $r->card->trashed()) {
                return false;
            }
            if ($r->card->column && $r->card->column->is_done) {
                return false;
            }
            return true;
        });

        // Kelompokkan pengingat berdasarkan user_id (1 email per pengguna)
        $groupedByUser = $validReminders->groupBy('user_id');

        $emailsSent = 0;
        $delaySeconds = 0;

        foreach ($groupedByUser as $userId => $userReminders) {
            if (($todaySentCount + $emailsSent) >= $dailyCap) {
                Log::warning("Plafon harian pengiriman email terlampaui saat memproses batch. Sisa pengingat ditunda.");
                break;
            }

            $user = $userReminders->first()->user;
            $cards = $userReminders->pluck('card')->unique('id')->values();

            // Kirim email ringkasan tugas jatuh tempo dengan delay 30 detik antar email
            if ($delaySeconds > 0) {
                Mail::to($user->email)->later(now()->addSeconds($delaySeconds), new CardReminderDigestMail($user, $cards));
            } else {
                Mail::to($user->email)->send(new CardReminderDigestMail($user, $cards));
            }

            // Tandai sent_at pada seluruh pengingat yang masuk dalam email ini
            $reminderIds = $userReminders->pluck('id');
            Reminder::whereIn('id', $reminderIds)->update(['sent_at' => now()]);

            $emailsSent++;
            $delaySeconds += 30; // Jeda 30 detik antar pesan berikutnya
        }

        return $emailsSent;
    }
}

