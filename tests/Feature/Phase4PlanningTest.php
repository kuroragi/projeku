<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Card;
use App\Models\Column;
use App\Models\File;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Phase4PlanningTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $editor;
    private User $viewer;
    private User $nonMember;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('local');

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->owner = User::create([
            'name' => 'Project Owner',
            'email' => 'owner@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123456'),
        ]);
        $this->editor = User::create([
            'name' => 'Project Editor',
            'email' => 'editor@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123456'),
        ]);
        $this->viewer = User::create([
            'name' => 'Project Viewer',
            'email' => 'viewer@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123456'),
        ]);
        $this->nonMember = User::create([
            'name' => 'Non Member',
            'email' => 'nonmember@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123456'),
        ]);

        $this->project = Project::create([
            'name' => 'Projek Tahap 4',
            'created_by' => $this->owner->id,
        ]);

        $this->assignRole($this->owner, 'owner', $this->project->id);
        $this->assignRole($this->editor, 'editor', $this->project->id);
        $this->assignRole($this->viewer, 'viewer', $this->project->id);
    }

    private function assignRole(User $user, string $roleName, int $projectId): void
    {
        $role = Role::findByName($roleName, 'web');
        DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
            'project_id' => $projectId,
        ]);
    }

    public function test_viewer_can_view_planning_index_and_page(): void
    {
        $page = $this->project->pages()->create([
            'title' => 'Rencana Arsitektur',
            'position' => 1000,
        ]);

        $this->actingAs($this->viewer)
            ->get(route('projects.planning.index', $this->project))
            ->assertOk();

        $this->actingAs($this->viewer)
            ->get(route('projects.pages.show', [$this->project, $page]))
            ->assertOk()
            ->assertSee('Rencana Arsitektur');
    }

    public function test_viewer_can_access_presentation_mode(): void
    {
        $page = $this->project->pages()->create([
            'title' => 'Pitch Deck',
            'position' => 1000,
        ]);

        $this->actingAs($this->viewer)
            ->get(route('projects.pages.presentation', [$this->project, $page]))
            ->assertOk()
            ->assertSee('Mode Presentasi');
    }

    public function test_viewer_cannot_create_or_modify_page(): void
    {
        $this->actingAs($this->viewer)
            ->post(route('projects.planning.store', $this->project), [
                'title' => 'Halaman Ilegal',
            ])
            ->assertForbidden();

        $page = $this->project->pages()->create([
            'title' => 'Halaman Awal',
            'position' => 1000,
        ]);

        $this->actingAs($this->viewer)
            ->patch(route('projects.pages.update', [$this->project, $page]), [
                'title' => 'Ubah Ilegal',
            ])
            ->assertForbidden();

        $this->actingAs($this->viewer)
            ->delete(route('projects.pages.destroy', [$this->project, $page]))
            ->assertForbidden();
    }

    public function test_non_member_gets_404(): void
    {
        $page = $this->project->pages()->create([
            'title' => 'Halaman Rahasia',
            'position' => 1000,
        ]);

        $this->actingAs($this->nonMember)
            ->get(route('projects.planning.index', $this->project))
            ->assertNotFound();

        $this->actingAs($this->nonMember)
            ->get(route('projects.pages.show', [$this->project, $page]))
            ->assertNotFound();

        $this->actingAs($this->nonMember)
            ->get(route('projects.pages.presentation', [$this->project, $page]))
            ->assertNotFound();
    }

    public function test_editor_can_create_page_with_default_h1_block(): void
    {
        $response = $this->actingAs($this->editor)
            ->post(route('projects.planning.store', $this->project), [
                'title' => 'Konsep Desain Produk',
            ]);

        $page = $this->project->pages()->first();
        $this->assertNotNull($page);
        $this->assertEquals('Konsep Desain Produk', $page->title);

        $response->assertRedirect(route('projects.pages.show', [$this->project, $page]));

        // Cek bahwa blok default judul H1 otomatis dibuat
        $this->assertDatabaseHas('blocks', [
            'project_id' => $this->project->id,
            'page_id' => $page->id,
            'type' => 'judul',
        ]);

        $block = $page->blocks()->first();
        $this->assertEquals('Konsep Desain Produk', $block->content['text']);
        $this->assertEquals(1, $block->content['level']);
    }

    public function test_editor_can_add_all_four_block_types(): void
    {
        $page = $this->project->pages()->create([
            'title' => 'Spesifikasi Fitur',
            'position' => 1000,
        ]);

        // 1. Tambah Blok Judul H2
        $this->actingAs($this->editor)
            ->postJson(route('projects.blocks.store', [$this->project, $page]), [
                'type' => 'judul',
                'content' => ['text' => 'Sub Bab 1', 'level' => 2],
            ])
            ->assertCreated();

        // 2. Tambah Blok Teks
        $this->actingAs($this->editor)
            ->postJson(route('projects.blocks.store', [$this->project, $page]), [
                'type' => 'teks',
                'content' => ['text' => 'Paragraf penjelasan fitur utama...'],
            ])
            ->assertCreated();

        // 3. Tambah Blok Gambar
        $file = $this->project->files()->create([
            'original_name' => 'diagram.webp',
            'stored_name' => 'random_hash.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
            'created_by' => $this->editor->id,
        ]);

        $this->actingAs($this->editor)
            ->postJson(route('projects.blocks.store', [$this->project, $page]), [
                'type' => 'gambar',
                'content' => ['file_id' => $file->id, 'caption' => 'Diagram Alur Kerja'],
            ])
            ->assertCreated();

        // 4. Tambah Blok Daftar
        $this->actingAs($this->editor)
            ->postJson(route('projects.blocks.store', [$this->project, $page]), [
                'type' => 'daftar',
                'content' => ['style' => 'bullet', 'items' => ['Poin 1', 'Poin 2', 'Poin 3']],
            ])
            ->assertCreated();

        $this->assertEquals(4, $page->blocks()->count());
    }

    public function test_editor_can_update_and_delete_blocks(): void
    {
        $page = $this->project->pages()->create([
            'title' => 'Page Test',
            'position' => 1000,
        ]);

        $block = $page->blocks()->create([
            'project_id' => $this->project->id,
            'type' => 'teks',
            'content' => ['text' => 'Teks awal'],
            'position' => 1000,
        ]);

        $this->actingAs($this->editor)
            ->patchJson(route('projects.blocks.update', [$this->project, $page, $block]), [
                'content' => ['text' => 'Teks yang sudah diperbarui'],
            ])
            ->assertOk();

        $this->assertEquals('Teks yang sudah diperbarui', $block->fresh()->content['text']);

        $this->actingAs($this->editor)
            ->deleteJson(route('projects.blocks.destroy', [$this->project, $page, $block]))
            ->assertOk();

        $this->assertDatabaseMissing('blocks', ['id' => $block->id]);
    }

    public function test_editor_can_reorder_blocks(): void
    {
        $page = $this->project->pages()->create(['title' => 'Reorder Page', 'position' => 1000]);

        $b1 = $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'teks', 'content' => ['text' => 'B1'], 'position' => 1000]);
        $b2 = $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'teks', 'content' => ['text' => 'B2'], 'position' => 2000]);

        $this->actingAs($this->editor)
            ->postJson(route('projects.blocks.reorder', [$this->project, $page]), [
                'order' => [
                    ['id' => $b2->id, 'position' => 500],
                    ['id' => $b1->id, 'position' => 1500],
                ]
            ])
            ->assertOk();

        $this->assertEquals(500, $b2->fresh()->position);
        $this->assertEquals(1500, $b1->fresh()->position);
    }

    public function test_slide_segmentation_by_h1_headings(): void
    {
        $page = $this->project->pages()->create(['title' => 'Slide Test', 'position' => 1000]);

        // Slide 1
        $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'judul', 'content' => ['text' => 'Slide 1 Title', 'level' => 1], 'position' => 1000]);
        $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'teks', 'content' => ['text' => 'Slide 1 content'], 'position' => 2000]);

        // Slide 2
        $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'judul', 'content' => ['text' => 'Slide 2 Title', 'level' => 1], 'position' => 3000]);
        $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'judul', 'content' => ['text' => 'Slide 2 Subtitle', 'level' => 2], 'position' => 4000]);
        $page->blocks()->create(['project_id' => $this->project->id, 'type' => 'teks', 'content' => ['text' => 'Slide 2 content'], 'position' => 5000]);

        $response = $this->actingAs($this->viewer)
            ->get(route('projects.pages.presentation', [$this->project, $page]));

        $response->assertOk();
        $slides = $response->viewData('slides');

        $this->assertCount(2, $slides);
        $this->assertCount(2, $slides[0]); // Slide 1 memiliki 2 blok
        $this->assertCount(3, $slides[1]); // Slide 2 memiliki 3 blok
    }

    public function test_page_can_be_soft_deleted_restored_and_purged(): void
    {
        $page = $this->project->pages()->create(['title' => 'Page to Delete', 'position' => 1000]);

        // 1. Soft delete
        $this->actingAs($this->editor)
            ->delete(route('projects.pages.destroy', [$this->project, $page]))
            ->assertRedirect(route('projects.planning.index', $this->project));

        $this->assertSoftDeleted('pages', ['id' => $page->id]);

        // 2. Muncul di tempat sampah
        $this->actingAs($this->owner)
            ->get(route('projects.trash.index', $this->project))
            ->assertOk()
            ->assertSee('Page to Delete');

        // 3. Restore oleh editor
        $this->actingAs($this->editor)
            ->post(route('projects.trash.pages.restore', [$this->project, $page->id]))
            ->assertRedirect();

        $this->assertNotSoftDeleted('pages', ['id' => $page->id]);

        // 4. Soft delete lagi lalu purge oleh owner
        $page->delete();

        // Viewer tidak bisa purge
        $this->actingAs($this->viewer)
            ->delete(route('projects.trash.pages.purge', [$this->project, $page->id]))
            ->assertForbidden();

        // Editor tidak bisa purge
        $this->actingAs($this->editor)
            ->delete(route('projects.trash.pages.purge', [$this->project, $page->id]))
            ->assertForbidden();

        // Owner bisa purge
        $this->actingAs($this->owner)
            ->delete(route('projects.trash.pages.purge', [$this->project, $page->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_bidirectional_card_linking(): void
    {
        $page = $this->project->pages()->create(['title' => 'Halaman Spesifikasi API', 'position' => 1000]);
        $column = $this->project->columns()->create(['name' => 'To Do', 'position' => 1000]);

        // Buat kartu yang ditautkan ke page
        $card = $column->cards()->create([
            'project_id' => $this->project->id,
            'title' => 'Buat Endpoint Auth',
            'page_id' => $page->id,
            'position' => 1000,
        ]);

        $this->assertEquals($page->id, $card->fresh()->page_id);
        $this->assertEquals('Halaman Spesifikasi API', $card->page->title);

        $this->assertCount(1, $page->linkedCards);
        $this->assertEquals('Buat Endpoint Auth', $page->linkedCards->first()->title);

        // Akses view halaman perencanaan, kartu tertaut harus terlihat
        $this->actingAs($this->viewer)
            ->get(route('projects.pages.show', [$this->project, $page]))
            ->assertOk()
            ->assertSee('Buat Endpoint Auth');
    }
}
