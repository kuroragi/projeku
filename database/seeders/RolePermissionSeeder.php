<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Reset cache permission
        $registrar->forgetCachedPermissions();

        // Tetapkan team_id ke null agar peran dibuat sebagai peran global
        $registrar->setPermissionsTeamId(null);

        // 9 Izin Aplikasi
        $permissions = [
            'project.view',
            'board.edit',
            'file.upload',
            'trash.restore',
            'plan.edit',
            'project.manage',
            'members.manage',
            'trash.purge',
            'project.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Tiga Peran Projek
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Sinkronisasi izin
        $viewerPermissions = ['project.view'];
        $editorPermissions = [
            'project.view',
            'board.edit',
            'file.upload',
            'trash.restore',
            'plan.edit',
        ];
        $ownerPermissions = $permissions;

        $viewerRole->syncPermissions($viewerPermissions);
        $editorRole->syncPermissions($editorPermissions);
        $ownerRole->syncPermissions($ownerPermissions);

        // Reset cache di akhir
        $registrar->forgetCachedPermissions();
    }
}

