<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Column;
use App\Models\Label;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles & Permissions
        $this->call(RolePermissionSeeder::class);

        // 2. Buat / Perbarui Akun Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@projeku.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'notify_email' => true,
                'deactivated_at' => null,
            ]
        );

        // 3. Buat / Perbarui Akun Tester
        $tester = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'notify_email' => true,
                'deactivated_at' => null,
            ]
        );

        // 4. Buat Demo Project jika belum ada
        $project = Project::firstOrCreate(
            ['name' => 'Projek Demo Manajemen'],
            [
                'description' => 'Projek contoh untuk mengeksplorasi Kanban Board, Halaman Perencanaan, dan Mode Presentasi.',
                'due_at' => now()->addDays(14),
            ]
        );

        // Berikan akses: Admin sebagai Owner, Tester sebagai Editor
        ProjectAccess::assignRole($project, $admin, 'owner');
        ProjectAccess::assignRole($project, $tester, 'editor');

        // Buat Label bawaan projek jika belum ada
        $labelBackend = Label::firstOrCreate([
            'project_id' => $project->id,
            'name' => 'Backend',
            'color' => 'indigo',
        ]);
        $labelFrontend = Label::firstOrCreate([
            'project_id' => $project->id,
            'name' => 'Frontend',
            'color' => 'cyan',
        ]);
        $labelUrgent = Label::firstOrCreate([
            'project_id' => $project->id,
            'name' => 'Mendesak',
            'color' => 'rose',
        ]);

        // Buat Kolom Kanban
        if ($project->columns()->count() === 0) {
            $colTodo = $project->columns()->create([
                'name' => 'To Do',
                'is_done' => false,
                'position' => 1000,
            ]);

            $colInProgress = $project->columns()->create([
                'name' => 'Sedang Dikerjakan',
                'is_done' => false,
                'position' => 2000,
            ]);

            $colDone = $project->columns()->create([
                'name' => 'Selesai',
                'is_done' => true,
                'position' => 3000,
            ]);

            // Buat Halaman Perencanaan Demo
            $page = $project->pages()->create([
                'title' => 'Rancangan Arsitektur Aplikasi',
                'position' => 1000,
            ]);

            // Blok 1: Judul H1 (Slide 1)
            $page->blocks()->create([
                'project_id' => $project->id,
                'type' => 'judul',
                'content' => [
                    'text' => 'Projeku: Arsitektur & Perencanaan',
                    'level' => 1,
                ],
                'position' => 1000,
            ]);

            // Blok 2: Teks Paragraf
            $page->blocks()->create([
                'project_id' => $project->id,
                'type' => 'teks',
                'content' => [
                    'text' => 'Aplikasi ini dirancang sebagai sistem manajemen projek mandiri dengan fokus pada kecepatan, keamanan isolasi data per-projek, dan presentasi langsung dari dokumen konsep.',
                ],
                'position' => 2000,
            ]);

            // Blok 3: Daftar Fitur
            $page->blocks()->create([
                'project_id' => $project->id,
                'type' => 'daftar',
                'content' => [
                    'style' => 'bullet',
                    'items' => [
                        'Kanban Board interaktif dengan drag & drop dan checklist bertingkat',
                        'Otorisasi ketat Spatie Teams dengan ProjectAccess Gatekeeper',
                        'Pemrosesan gambar GD aman (tanpa SVG, WebP otomatis, thumbnail)',
                        'Mode Presentasi 16:9 dengan navigasi papan ketik dan cetak PDF',
                    ],
                ],
                'position' => 3000,
            ]);

            // Blok 4: Judul H1 (Slide 2)
            $page->blocks()->create([
                'project_id' => $project->id,
                'type' => 'judul',
                'content' => [
                    'text' => 'Tahapan Pengembangan & Roadmap',
                    'level' => 1,
                ],
                'position' => 4000,
            ]);

            // Blok 5: Teks Slide 2
            $page->blocks()->create([
                'project_id' => $project->id,
                'type' => 'teks',
                'content' => [
                    'Seluruh 5 tahap implementasi telah selesai dan diverifikasi oleh 45 unit & feature test suite.',
                ],
                'position' => 5000,
            ]);

            // Buat Kartu-kartu Kanban
            $card1 = $colTodo->cards()->create([
                'project_id' => $project->id,
                'title' => 'Review Desain UI & Token Tailwind',
                'description' => 'Pastikan seluruh komponen mengikuti tema gelap dan standar UX.',
                'priority' => 'high',
                'due_at' => now()->addDays(2),
                'page_id' => $page->id,
                'position' => 1000,
            ]);
            $card1->labels()->sync([
                $labelFrontend->id => ['project_id' => $project->id],
                $labelUrgent->id => ['project_id' => $project->id],
            ]);
            $card1->assignees()->sync([
                $tester->id => ['project_id' => $project->id],
            ]);

            $chk1 = $card1->checklists()->create([
                'project_id' => $project->id,
                'title' => 'Pemeriksaan Token',
            ]);
            $chk1->items()->create(['project_id' => $project->id, 'text' => 'Periksa kontras warna teks dan latar belakang', 'is_done' => true, 'position' => 1000]);
            $chk1->items()->create(['project_id' => $project->id, 'text' => 'Verifikasi token print @media print', 'is_done' => false, 'position' => 2000]);

            $card2 = $colInProgress->cards()->create([
                'project_id' => $project->id,
                'title' => 'Integrasi Pengingat Email Otomatis',
                'description' => 'Cron job berjalan setiap menit dan mengirim ringkasan email H-1 dan H-0.',
                'priority' => 'normal',
                'due_at' => now()->addDay(),
                'position' => 1000,
            ]);
            $card2->labels()->sync([
                $labelBackend->id => ['project_id' => $project->id],
            ]);
            $card2->assignees()->sync([
                $admin->id => ['project_id' => $project->id],
            ]);

            $card3 = $colDone->cards()->create([
                'project_id' => $project->id,
                'title' => 'Setup Basis Data & Sistem Akses',
                'description' => 'Konfigurasi Spatie Permission dengan isolasi multi-tenant.',
                'priority' => 'normal',
                'due_at' => now()->subDay(),
                'position' => 1000,
            ]);
            $card3->labels()->sync([
                $labelBackend->id => ['project_id' => $project->id],
            ]);
        }
    }
}
