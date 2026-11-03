<?php

namespace Tests\Feature;

use App\Mail\CardReminderDigestMail;
use App\Models\Card;
use App\Models\Column;
use App\Models\Project;
use App\Models\Reminder;
use App\Models\User;
use App\Services\ProjectAccess;
use App\Services\ReminderService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase3ReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected User $userNoEmail;

    protected Project $project;
    protected Column $columnTodo;
    protected Column $columnDone;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->seed(RolePermissionSeeder::class);

        // User 1: Aktif & notifikasi email aktif
        $this->user1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123456'),
            'notify_email' => true,
        ]);

        // User 2: Aktif & notifikasi email aktif
        $this->user2 = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password123456'),
            'notify_email' => true,
        ]);

        // User No Email: Pengguna yang mematikan notifikasi email
        $this->userNoEmail = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123456'),
            'notify_email' => false,
        ]);

        $this->project = Project::create([
            'name' => 'Projek Pengingat',
        ]);

        ProjectAccess::assignRole($this->project, $this->user1, 'owner');
        ProjectAccess::assignRole($this->project, $this->user2, 'editor');
        ProjectAccess::assignRole($this->project, $this->userNoEmail, 'editor');

        $this->columnTodo = $this->project->columns()->create([
            'name' => 'Dalam Pengerjaan',
            'position' => 1000,
            'is_done' => false,
        ]);

        $this->columnDone = $this->project->columns()->create([
            'name' => 'Selesai',
            'position' => 2000,
            'is_done' => true,
        ]);
    }

    /** 1. Pembuatan kartu dengan tenggat waktu menghasilkan jadwal pengingat untuk penanggung jawab */
    public function test_card_with_due_date_schedules_reminders_for_assignees(): void
    {
        // Tenggat 3 hari ke depan pukul 17:00 WIB
        $dueAt = Carbon::now()->addDays(3)->setTime(17, 0, 0);

        $response = $this->actingAs($this->user1)->postJson("/projects/{$this->project->id}/columns/{$this->columnTodo->id}/cards", [
            'title' => 'Tugas Fitur API',
            'due_at' => $dueAt->toDateTimeString(),
            'assignee_ids' => [$this->user1->id, $this->userNoEmail->id],
        ]);

        $response->assertStatus(201);
        $cardId = $response->json('id');

        // Pengingat hanya dibuat untuk user1 (karena userNoEmail mematikan notifikasi)
        $remindersUser1 = Reminder::where('card_id', $cardId)->where('user_id', $this->user1->id)->get();
        $this->assertNotEmpty($remindersUser1);

        $remindersUserNoEmail = Reminder::where('card_id', $cardId)->where('user_id', $this->userNoEmail->id)->get();
        $this->assertCount(0, $remindersUserNoEmail);
    }

    /** 2. Mengubah tenggat waktu membuat ulang pengingat yang belum terkirim */
    public function test_updating_due_date_recreates_unsent_reminders(): void
    {
        $dueAt = Carbon::now()->addDays(4)->setTime(15, 0, 0);

        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Awal',
            'due_at' => $dueAt,
            'position' => 1000,
        ]);
        $card->assignees()->sync([$this->user1->id => ['project_id' => $this->project->id]]);
        ReminderService::syncCardReminders($card);

        $initialCount = Reminder::where('card_id', $card->id)->count();
        $this->assertGreaterThan(0, $initialCount);

        // Perbarui tenggat menjadi 7 hari ke depan
        $newDueAt = Carbon::now()->addDays(7)->setTime(10, 0, 0);
        $updateResp = $this->actingAs($this->user1)->patchJson("/projects/{$this->project->id}/cards/{$card->id}", [
            'due_at' => $newDueAt->toDateTimeString(),
        ]);
        $updateResp->assertStatus(200);

        $updatedReminders = Reminder::where('card_id', $card->id)->get();
        $this->assertNotEmpty($updatedReminders);
    }

    /** 3. Memindahkan kartu ke kolom is_done membatalkan pengingat yang belum terkirim */
    public function test_moving_card_to_done_column_cancels_unsent_reminders(): void
    {
        $dueAt = Carbon::now()->addDays(2)->setTime(12, 0, 0);
        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas untuk Diselesaikan',
            'due_at' => $dueAt,
            'position' => 1000,
        ]);
        $card->assignees()->sync([$this->user1->id => ['project_id' => $this->project->id]]);
        ReminderService::syncCardReminders($card);

        $this->assertGreaterThan(0, Reminder::where('card_id', $card->id)->count());

        // Pindahkan kartu ke kolom selesai
        $moveResp = $this->actingAs($this->user1)->postJson("/projects/{$this->project->id}/cards/{$card->id}/move", [
            'column_id' => $this->columnDone->id,
            'position' => 1000,
        ]);
        $moveResp->assertStatus(200);

        // Seluruh pengingat yang belum terkirim harus dibatalkan
        $this->assertEquals(0, Reminder::where('card_id', $card->id)->whereNull('sent_at')->count());

        // Pindahkan kembali ke kolom todo -> pengingat dibuat ulang
        $this->actingAs($this->user1)->postJson("/projects/{$this->project->id}/cards/{$card->id}/move", [
            'column_id' => $this->columnTodo->id,
            'position' => 2000,
        ])->assertStatus(200);

        $this->assertGreaterThan(0, Reminder::where('card_id', $card->id)->whereNull('sent_at')->count());
    }

    /** 4. Soft delete membatalkan pengingat, restore mengaktifkannya kembali */
    public function test_soft_delete_cancels_reminders_and_restore_recreates(): void
    {
        $dueAt = Carbon::now()->addDays(3)->setTime(12, 0, 0);
        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Sampah',
            'due_at' => $dueAt,
            'position' => 1000,
        ]);
        $card->assignees()->sync([$this->user1->id => ['project_id' => $this->project->id]]);
        ReminderService::syncCardReminders($card);

        $this->assertGreaterThan(0, Reminder::where('card_id', $card->id)->count());

        // Hapus kartu (soft delete)
        $this->actingAs($this->user1)->deleteJson("/projects/{$this->project->id}/cards/{$card->id}")->assertStatus(200);
        $this->assertEquals(0, Reminder::where('card_id', $card->id)->whereNull('sent_at')->count());

        // Pulihkan kartu dari tempat sampah
        $this->actingAs($this->user1)->postJson("/projects/{$this->project->id}/trash/cards/{$card->id}/restore")->assertStatus(200);
        $this->assertGreaterThan(0, Reminder::where('card_id', $card->id)->whereNull('sent_at')->count());
    }

    /** 5. Pengiriman pengingat: Menggabungkan banyak kartu dalam satu email per pengguna dan tidak ganda */
    public function test_due_reminders_sent_as_single_digest_per_user_and_idempotent(): void
    {
        // Buat 2 kartu bertenggat sama untuk user1
        $card1 = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Digest 1',
            'due_at' => now()->addHour(),
            'position' => 1000,
        ]);
        $card1->assignees()->sync([$this->user1->id => ['project_id' => $this->project->id]]);

        $card2 = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Digest 2',
            'due_at' => now()->addHour(),
            'position' => 2000,
        ]);
        $card2->assignees()->sync([$this->user1->id => ['project_id' => $this->project->id]]);

        // Buat entri pengingat yang sudah jatuh tempo (remind_at di masa lampau)
        Reminder::create([
            'project_id' => $this->project->id,
            'card_id' => $card1->id,
            'user_id' => $this->user1->id,
            'remind_at' => now()->subMinutes(10),
        ]);

        Reminder::create([
            'project_id' => $this->project->id,
            'card_id' => $card2->id,
            'user_id' => $this->user1->id,
            'remind_at' => now()->subMinutes(5),
        ]);

        // Jalankan pengiriman pengingat
        $sentCount = ReminderService::sendDueReminders();
        $this->assertEquals(1, $sentCount); // 1 email untuk user1 yang memuat kedua kartu

        Mail::assertQueued(CardReminderDigestMail::class, function (CardReminderDigestMail $mail) {
            return $mail->user->id === $this->user1->id && $mail->cards->count() === 2;
        });

        // Seluruh baris pengingat ditandai sent_at
        $this->assertEquals(0, Reminder::where('user_id', $this->user1->id)->whereNull('sent_at')->count());

        // Jalankan lagi -> tidak boleh ada email tambahan yang terkirim (idempoten)
        $secondSentCount = ReminderService::sendDueReminders();
        $this->assertEquals(0, $secondSentCount);
    }

    /** 6. Pengaturan preferensi notifikasi email pengguna */
    public function test_user_can_toggle_email_notification_preference(): void
    {
        $resp = $this->actingAs($this->user1)->patchJson('/user/preferences', [
            'notify_email' => false,
        ]);
        $resp->assertStatus(200);

        $this->user1->refresh();
        $this->assertFalse($this->user1->notify_email);
    }
}
