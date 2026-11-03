<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\Column;
use App\Models\File;
use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase2BoardTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $editor;
    protected User $viewer;
    protected User $outsider;

    protected Project $project;
    protected Column $columnTodo;
    protected Column $columnDone;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->seed(RolePermissionSeeder::class);

        $this->owner = User::create([
            'name' => 'Project Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123456'),
        ]);

        $this->editor = User::create([
            'name' => 'Project Editor',
            'email' => 'editor@example.com',
            'password' => Hash::make('password123456'),
        ]);

        $this->viewer = User::create([
            'name' => 'Project Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123456'),
        ]);

        $this->outsider = User::create([
            'name' => 'Outsider',
            'email' => 'outsider@example.com',
            'password' => Hash::make('password123456'),
        ]);

        $this->project = Project::create([
            'name' => 'Projek Uji Board',
        ]);

        ProjectAccess::assignRole($this->project, $this->owner, 'owner');
        ProjectAccess::assignRole($this->project, $this->editor, 'editor');
        ProjectAccess::assignRole($this->project, $this->viewer, 'viewer');

        // Buat 2 kolom awal
        $this->columnTodo = $this->project->columns()->create([
            'name' => 'Tugas Baru',
            'position' => 1000,
            'is_done' => false,
        ]);

        $this->columnDone = $this->project->columns()->create([
            'name' => 'Selesai',
            'position' => 2000,
            'is_done' => true,
        ]);
    }

    /** 1. Editor dapat membuat kolom baru; Viewer ditolak 403 */
    public function test_editor_can_create_column_and_viewer_is_forbidden(): void
    {
        $viewerResp = $this->actingAs($this->viewer)->postJson("/projects/{$this->project->id}/columns", [
            'name' => 'Kolom Viewer',
        ]);
        $viewerResp->assertStatus(403);

        $editorResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/columns", [
            'name' => 'Sedang Dikerjakan',
            'is_done' => false,
        ]);
        $editorResp->assertStatus(201);
        $this->assertDatabaseHas('columns', [
            'project_id' => $this->project->id,
            'name' => 'Sedang Dikerjakan',
            'position' => 3000,
        ]);
    }

    /** 2. Kolom tidak dapat dihapus jika masih berisi kartu (Aturan Keras Desain) */
    public function test_column_cannot_be_deleted_if_it_contains_cards(): void
    {
        // Buat kartu di dalam columnTodo
        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Kartu Penguji',
            'position' => 1000,
        ]);

        // Coba hapus kolom yang berisi kartu -> harus ditolak 422
        $resp = $this->actingAs($this->editor)->deleteJson("/projects/{$this->project->id}/columns/{$this->columnTodo->id}");
        $resp->assertStatus(422);
        $this->assertDatabaseHas('columns', ['id' => $this->columnTodo->id]);

        // Hapus kartu terlebih dahulu
        $card->delete();

        // Sekarang kolom kosong -> berhasil dihapus
        $deleteResp = $this->actingAs($this->editor)->deleteJson("/projects/{$this->project->id}/columns/{$this->columnTodo->id}");
        $deleteResp->assertStatus(200);
        $this->assertDatabaseMissing('columns', ['id' => $this->columnTodo->id]);
    }

    /** 3. Membuat kartu dengan label dan banyak penanggung jawab */
    public function test_editor_can_create_and_update_card_with_assignees_and_labels(): void
    {
        $label = $this->project->labels()->create([
            'name' => 'Frontend',
            'color' => '#C4B5FD',
        ]);

        $resp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/columns/{$this->columnTodo->id}/cards", [
            'title' => 'Desain komponen kartu',
            'description' => 'Gunakan token CSS tema gelap',
            'priority' => 'high',
            'label_ids' => [$label->id],
            'assignee_ids' => [$this->owner->id, $this->editor->id],
        ]);

        $resp->assertStatus(201);
        $cardId = $resp->json('id');

        $this->assertDatabaseHas('cards', [
            'id' => $cardId,
            'priority' => 'high',
            'position' => 1000,
        ]);

        $this->assertDatabaseHas('card_user', ['card_id' => $cardId, 'user_id' => $this->owner->id]);
        $this->assertDatabaseHas('card_user', ['card_id' => $cardId, 'user_id' => $this->editor->id]);
        $this->assertDatabaseHas('card_label', ['card_id' => $cardId, 'label_id' => $label->id]);
    }

    /** 4. Memindahkan kartu ke kolom lain (move) */
    public function test_card_can_be_moved_to_another_column(): void
    {
        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Geser',
            'position' => 1000,
        ]);

        $moveResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/cards/{$card->id}/move", [
            'column_id' => $this->columnDone->id,
            'position' => 1500,
        ]);

        $moveResp->assertStatus(200);
        $card->refresh();
        $this->assertEquals($this->columnDone->id, $card->column_id);
        $this->assertEquals(1500, $card->position);
    }

    /** 5. Interaksi Checklist: Buat checklist, tambah item, toggle is_done */
    public function test_card_checklist_lifecycle(): void
    {
        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Checklist',
            'position' => 1000,
        ]);

        // Buat Checklist
        $chResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/cards/{$card->id}/checklists", [
            'title' => 'Daftar Subtugas',
        ]);
        $chResp->assertStatus(201);
        $chId = $chResp->json('id');

        // Tambah Item
        $itemResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/cards/{$card->id}/checklists/{$chId}/items", [
            'text' => 'Item Pertama',
        ]);
        $itemResp->assertStatus(201);
        $itemId = $itemResp->json('id');

        // Toggle Status Selesai
        $toggleResp = $this->actingAs($this->editor)->patchJson("/projects/{$this->project->id}/cards/{$card->id}/checklists/{$chId}/items/{$itemId}");
        $toggleResp->assertStatus(200);
        $this->assertTrue($toggleResp->json('is_done'));
    }

    /** 6. Validasi Berkas: SVG ditolak, berkas valid disimpan sebagai WebP */
    public function test_file_upload_rejects_svg_and_processes_images(): void
    {
        // 1. Coba unggah SVG -> harus ditolak 422
        $svgFile = UploadedFile::fake()->create('malicious.svg', 10, 'image/svg+xml');
        $svgResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/files", [
            'file' => $svgFile,
        ]);
        $svgResp->assertStatus(422);

        // 2. Unggah gambar PNG valid
        $pngFile = UploadedFile::fake()->image('mockup.png', 800, 600);
        $uploadResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/files", [
            'file' => $pngFile,
        ]);
        $uploadResp->assertStatus(201);
        $fileId = $uploadResp->json('id');

        $this->assertDatabaseHas('files', [
            'id' => $fileId,
            'project_id' => $this->project->id,
            'mime_type' => 'image/webp',
            'width' => 800,
            'height' => 600,
        ]);

        // 3. Coba akses berkas oleh Outsider -> 404
        $outsiderResp = $this->actingAs($this->outsider)->get("/projects/{$this->project->id}/files/{$fileId}");
        $outsiderResp->assertStatus(404);

        // 4. Akses berkas oleh Viewer projek -> 200
        $viewerResp = $this->actingAs($this->viewer)->get("/projects/{$this->project->id}/files/{$fileId}");
        $viewerResp->assertStatus(200);
        $this->assertEquals('image/webp', $viewerResp->headers->get('Content-Type'));
    }

    /** 7. Siklus Tempat Sampah (Trash): Soft delete, restore, dan permanent purge (hanya Owner) */
    public function test_trash_soft_delete_restore_and_owner_only_purge(): void
    {
        $card = $this->columnTodo->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Kartu untuk Dihapus',
            'position' => 1000,
        ]);

        // Soft Delete oleh Editor
        $delResp = $this->actingAs($this->editor)->deleteJson("/projects/{$this->project->id}/cards/{$card->id}");
        $delResp->assertStatus(200);
        $this->assertSoftDeleted('cards', ['id' => $card->id]);

        // Restore oleh Editor
        $restoreResp = $this->actingAs($this->editor)->postJson("/projects/{$this->project->id}/trash/cards/{$card->id}/restore");
        $restoreResp->assertStatus(200);
        $this->assertNotSoftDeleted('cards', ['id' => $card->id]);

        // Hapus lagi
        $card->delete();

        // Editor mencoba hapus permanen (purge) -> DITOLAK 403 (Hanya Owner)
        $editorPurge = $this->actingAs($this->editor)->deleteJson("/projects/{$this->project->id}/trash/cards/{$card->id}");
        $editorPurge->assertStatus(403);

        // Owner melakukan hapus permanen (purge) -> DIIZINKAN 200
        $ownerPurge = $this->actingAs($this->owner)->deleteJson("/projects/{$this->project->id}/trash/cards/{$card->id}");
        $ownerPurge->assertStatus(200);
        $this->assertDatabaseMissing('cards', ['id' => $card->id]);
    }
}

