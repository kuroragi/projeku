<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Phase1AccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected User $userC;
    protected User $userD;
    protected User $userE;

    protected Project $project1;
    protected Project $project2;

    protected function setUp(): void
    {
        parent::setUp();

        // Jalankan seeder peran & izin Spatie dengan mode teams
        $this->seed(RolePermissionSeeder::class);

        // Buat data pengguna sesuai skenario:
        // A: admin, owner P1
        $this->userA = User::create([
            'name' => 'Admin User A',
            'email' => 'a@example.com',
            'password' => Hash::make('password-secret-12'),
            'is_admin' => true,
        ]);

        // B: editor di P1, viewer di P2
        $this->userB = User::create([
            'name' => 'User B',
            'email' => 'b@example.com',
            'password' => Hash::make('password-secret-12'),
            'is_admin' => false,
        ]);

        // C: owner P2
        $this->userC = User::create([
            'name' => 'User C',
            'email' => 'c@example.com',
            'password' => Hash::make('password-secret-12'),
            'is_admin' => false,
        ]);

        // D: tidak punya peran di projek mana pun
        $this->userD = User::create([
            'name' => 'User D',
            'email' => 'd@example.com',
            'password' => Hash::make('password-secret-12'),
            'is_admin' => false,
        ]);

        // E: nonaktif
        $this->userE = User::create([
            'name' => 'User E',
            'email' => 'e@example.com',
            'password' => Hash::make('password-secret-12'),
            'is_admin' => false,
            'deactivated_at' => now(),
        ]);

        // Buat Projek P1 dan P2
        $this->project1 = Project::create([
            'name' => 'Projek Alpha P1',
            'description' => 'Deskripsi Projek 1',
        ]);

        $this->project2 = Project::create([
            'name' => 'Projek Beta P2',
            'description' => 'Deskripsi Projek 2',
        ]);

        // Berikan peran sesuai matriks skenario
        ProjectAccess::assignRole($this->project1, $this->userA, 'owner');
        ProjectAccess::assignRole($this->project1, $this->userB, 'editor');

        ProjectAccess::assignRole($this->project2, $this->userB, 'viewer');
        ProjectAccess::assignRole($this->project2, $this->userC, 'owner');
    }

    /** Skenario 1: D membuka daftar projek, lalu GET /projects/{P1} -> Daftar kosong; 404 */
    public function test_scenario_01_user_d_gets_empty_list_and_404_on_p1(): void
    {
        $response = $this->actingAs($this->userD)->getJson('/projects');
        $response->assertStatus(200);
        $response->assertJsonCount(0);

        $showResponse = $this->actingAs($this->userD)->get('/projects/' . $this->project1->id);
        $showResponse->assertStatus(404);
    }

    /** Skenario 2: B membuka daftar projek -> P1 dan P2 muncul, tidak ada yang lain */
    public function test_scenario_02_user_b_sees_only_p1_and_p2(): void
    {
        // Buat projek ketiga P3 tanpa melibatkan B
        $p3 = Project::create(['name' => 'Projek Gamma P3']);
        ProjectAccess::assignRole($p3, $this->userA, 'owner');

        $response = $this->actingAs($this->userB)->getJson('/projects');
        $response->assertStatus(200);
        $response->assertJsonCount(2);

        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($this->project1->id, $ids);
        $this->assertContains($this->project2->id, $ids);
        $this->assertNotContains($p3->id, $ids);
    }

    /** Skenario 3: B mengubah board P1, lalu P2, lalu P1 lagi dalam satu proses -> Diizinkan, 403, diizinkan: peran tidak terbawa antarprojek */
    public function test_scenario_03_role_context_does_not_leak_between_projects(): void
    {
        // Di P1, B adalah editor -> punya izin board.edit
        $canP1_first = ProjectAccess::can($this->project1, 'board.edit', $this->userB);
        $this->assertTrue($canP1_first);

        // Di P2, B adalah viewer -> TIDAK punya izin board.edit
        $canP2 = ProjectAccess::can($this->project2, 'board.edit', $this->userB);
        $this->assertFalse($canP2);

        // Di P1 lagi, B harus tetap punya izin board.edit
        $canP1_second = ProjectAccess::can($this->project1, 'board.edit', $this->userB);
        $this->assertTrue($canP1_second);
    }

    /** Skenario 4: C membuka P1 -> 404 */
    public function test_scenario_04_user_c_cannot_open_p1(): void
    {
        $response = $this->actingAs($this->userC)->get('/projects/' . $this->project1->id);
        $response->assertStatus(404);
    }

    /** Skenario 5: B (viewer di P2) memanggil aksi board.edit dan members.manage di P2 -> 403 untuk keduanya */
    public function test_scenario_05_viewer_in_p2_gets_403_for_editing_and_members(): void
    {
        $this->assertTrue(ProjectAccess::can($this->project2, 'project.view', $this->userB));
        $this->assertFalse(ProjectAccess::can($this->project2, 'board.edit', $this->userB));
        $this->assertFalse(ProjectAccess::can($this->project2, 'members.manage', $this->userB));

        // Test via HTTP route (POST members di P2)
        $response = $this->actingAs($this->userB)->postJson("/projects/{$this->project2->id}/members", [
            'email' => $this->userD->email,
            'role' => 'editor',
        ]);
        $response->assertStatus(403);
    }

    /** Skenario 6: D memanggil GET /projects/{id} untuk semua id dari 1 sampai N, termasuk yang tidak ada -> Semua 404 dan isi identik */
    public function test_scenario_06_non_member_gets_identical_404_for_existing_and_non_existing_projects(): void
    {
        $responseExisting = $this->actingAs($this->userD)->get('/projects/' . $this->project1->id);
        $responseNonExisting = $this->actingAs($this->userD)->get('/projects/999999');

        $responseExisting->assertStatus(404);
        $responseNonExisting->assertStatus(404);

        $this->assertEquals($responseExisting->getContent(), $responseNonExisting->getContent());
    }

    /** Skenario 7: Menurunkan atau mengeluarkan satu-satunya owner, lalu menurunkan dua owner secara berurutan -> Ditolak; jumlah owner tidak pernah nol */
    public function test_scenario_07_last_owner_protection(): void
    {
        // P1 hanya punya 1 owner (User A)
        $this->assertEquals(1, ProjectAccess::countOwners($this->project1));

        // Coba turunkan A dari owner ke editor di P1 -> harus ditolak 422
        $response = $this->actingAs($this->userA)->patchJson("/projects/{$this->project1->id}/members/{$this->userA->id}", [
            'role' => 'editor',
        ]);
        $response->assertStatus(422);

        // Coba keluarkan A dari P1 -> harus ditolak 422
        $removeResponse = $this->actingAs($this->userA)->deleteJson("/projects/{$this->project1->id}/members/{$this->userA->id}");
        $removeResponse->assertStatus(422);

        // Tambahkan B sebagai owner kedua di P1
        ProjectAccess::assignRole($this->project1, $this->userB, 'owner');
        $this->assertEquals(2, ProjectAccess::countOwners($this->project1));

        // Turunkan A (masih ada B sebagai owner) -> diizinkan
        $demoteA = $this->actingAs($this->userB)->patchJson("/projects/{$this->project1->id}/members/{$this->userA->id}", [
            'role' => 'editor',
        ]);
        $demoteA->assertStatus(200);
        $this->assertEquals(1, ProjectAccess::countOwners($this->project1));

        // Sekarang B adalah satu-satunya owner di P1. Coba turunkan B -> harus ditolak 422
        $demoteB = $this->actingAs($this->userB)->patchJson("/projects/{$this->project1->id}/members/{$this->userB->id}", [
            'role' => 'editor',
        ]);
        $demoteB->assertStatus(422);
    }

    /** Skenario 8: E login dengan kata sandi benar; E punya sesi aktif lalu dinonaktifkan; E meminta tautan reset -> Login gagal; sesi terputus pada request berikutnya; tidak ada email reset */
    public function test_scenario_08_deactivated_user_cannot_login_and_active_session_is_terminated(): void
    {
        // 1. E login dengan kata sandi benar saat nonaktif -> ditolak
        $loginResponse = $this->post('/login', [
            'email' => 'e@example.com',
            'password' => 'password-secret-12',
        ]);
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // 2. Pengguna aktif (User B) login, lalu dinonaktifkan saat sesi berjalan
        $this->actingAs($this->userB);
        $this->userB->update(['deactivated_at' => now()]);

        // Request berikutnya harus memutuskan sesi dan redirect ke login
        $response = $this->get('/projects');
        $response->assertRedirect('/login');
        $this->assertGuest();

        // 3. E meminta tautan reset kata sandi -> tidak dikirim dan token dibatalkan
        \Illuminate\Support\Facades\Notification::fake();

        $resetResponse = $this->post('/forgot-password', [
            'email' => 'e@example.com',
        ]);

        \Illuminate\Support\Facades\Notification::assertNothingSent();
        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'e@example.com')->first());
    }

    /** Skenario 9: GET /register dan POST /register -> 404 */
    public function test_scenario_09_registration_routes_return_404(): void
    {
        $getRegister = $this->get('/register');
        $getRegister->assertStatus(404);

        $postRegister = $this->post('/register', [
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => 'password123456',
        ]);
        $postRegister->assertStatus(404);
    }

    /** Skenario 10: Enam percobaan login salah berturut-turut -> Percobaan keenam mendapat 429 */
    public function test_scenario_10_login_throttle_returns_429_on_sixth_attempt(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $resp = $this->post('/login', [
                'email' => 'b@example.com',
                'password' => 'wrong-password',
            ]);
            $resp->assertSessionHasErrors('email');
        }

        // Percobaan ke-6 harus 429
        $sixth = $this->post('/login', [
            'email' => 'b@example.com',
            'password' => 'wrong-password',
        ]);
        $sixth->assertStatus(429);
    }

    /** Skenario 11: Tautan undangan dipakai setelah 24 jam, dan dipakai dua kali -> Ditolak kedua-duanya */
    public function test_scenario_11_reset_token_expiration_and_single_use(): void
    {
        $token = Password::broker()->createToken($this->userD);

        // 1. Dipakai setelah 24 jam (+ 25 jam)
        Carbon::setTestNow(now()->addHours(25));

        $expiredResp = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->userD->email,
            'password' => 'new-password-secret-12',
            'password_confirmation' => 'new-password-secret-12',
        ]);
        $expiredResp->assertSessionHasErrors('email');

        // Kembalikan waktu
        Carbon::setTestNow();

        // Buat token baru yang valid
        $tokenValid = Password::broker()->createToken($this->userD);

        // Pemakaian pertama -> berhasil
        $successResp = $this->post('/reset-password', [
            'token' => $tokenValid,
            'email' => $this->userD->email,
            'password' => 'new-password-secret-12',
            'password_confirmation' => 'new-password-secret-12',
        ]);
        $successResp->assertSessionHasNoErrors();

        // Pemakaian kedua dengan token yang sama -> harus ditolak
        $secondResp = $this->post('/reset-password', [
            'token' => $tokenValid,
            'email' => $this->userD->email,
            'password' => 'another-password-12',
            'password_confirmation' => 'another-password-12',
        ]);
        $secondResp->assertSessionHasErrors('email');
    }

    /** Skenario 12: A (admin, bukan anggota P2) membuka isi P2, lalu membuat pengguna dan projek baru -> 404 untuk isi P2; pembuatan berhasil dan A menjadi owner projek baru */
    public function test_scenario_12_admin_cannot_access_unassigned_project_but_can_create_projects(): void
    {
        // A adalah admin, tapi BUKAN anggota P2
        $this->assertFalse(ProjectAccess::can($this->project2, 'project.view', $this->userA));
        $resp = $this->actingAs($this->userA)->get('/projects/' . $this->project2->id);
        $resp->assertStatus(404);

        // A membuat pengguna baru lewat admin API
        $createUserResp = $this->actingAs($this->userA)->postJson('/admin/users', [
            'name' => 'Fulan Baru',
            'email' => 'fulan@example.com',
        ]);
        $createUserResp->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'fulan@example.com']);

        // A membuat projek baru
        $createProjResp = $this->actingAs($this->userA)->postJson('/projects', [
            'name' => 'Projek Baru dari Admin',
        ]);
        $createProjResp->assertStatus(201);
        $newProjId = $createProjResp->json('id');
        $newProj = Project::find($newProjId);

        // A otomatis menjadi owner di projek baru tersebut
        $this->assertTrue(ProjectAccess::can($newProj, 'project.delete', $this->userA));
        $this->assertEquals(1, ProjectAccess::countOwners($newProj));
    }

    /** Skenario 13: E diaktifkan kembali -> Daftar projek kosong sampai perannya diberikan lagi */
    public function test_scenario_13_reactivated_user_has_empty_project_list(): void
    {
        $this->actingAs($this->userA)->postJson("/admin/users/{$this->userE->id}/activate")
            ->assertStatus(200);

        $this->userE->refresh();
        $this->assertTrue($this->userE->isActive());

        // Daftar projek harus kosong
        $resp = $this->actingAs($this->userE)->getJson('/projects');
        $resp->assertStatus(200);
        $resp->assertJsonCount(0);
    }

    /** Skenario 14: RolePermissionSeeder dijalankan dua kali; satu projek dihapus permanen -> Tetap 3 peran dan 9 izin tanpa duplikat; baris model_has_roles projek itu ikut terhapus */
    public function test_scenario_14_idempotent_seeder_and_cascade_on_project_force_delete(): void
    {
        // Jalankan seeder kedua kali
        $this->seed(RolePermissionSeeder::class);

        // Tetap tepat 3 peran dan 9 izin
        $this->assertEquals(3, Role::count());
        $this->assertEquals(9, Permission::count());

        // Pastikan ada baris model_has_roles untuk project1
        $tableNames = config('permission.table_names');
        $initialRolesCount = DB::table($tableNames['model_has_roles'])
            ->where('project_id', $this->project1->id)
            ->count();
        $this->assertGreaterThan(0, $initialRolesCount);

        // Hapus permanen project1 (force delete)
        $this->project1->forceDelete();

        // Baris model_has_roles untuk project1 harus terhapus karena foreign key ON DELETE CASCADE
        $remainingRolesCount = DB::table($tableNames['model_has_roles'])
            ->where('project_id', $this->project1->id)
            ->count();
        $this->assertEquals(0, $remainingRolesCount);
    }
}
