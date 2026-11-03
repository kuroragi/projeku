<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class ProjectAccess
{
    /**
     * Memeriksa apakah pengguna memiliki izin tertentu pada sebuah projek.
     */
    public static function can(Project $project, string $permission, ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user || $user->deactivated_at !== null) {
            return false;
        }

        $registrar = app(PermissionRegistrar::class);
        $previous  = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($project->id);

        $user->unsetRelation('roles')->unsetRelation('permissions');

        try {
            return $user->hasPermissionTo($permission);
        } catch (\Throwable $e) {
            return false;
        } finally {
            $registrar->setPermissionsTeamId($previous);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    /**
     * Otorisasi izin projek:
     * - Jika bukan anggota: abort 404 (keberadaan projek tidak terungkap).
     * - Jika anggota tetapi tidak punya izin: abort 403.
     */
    public static function authorize(Project $project, string $permission): void
    {
        abort_unless(self::can($project, 'project.view'), 404);
        abort_unless(self::can($project, $permission), 403);
    }

    /**
     * Mengambil query projek yang hanya bisa dilihat oleh pengguna tertentu.
     */
    public static function visibleProjects(User $user): Builder
    {
        if ($user->deactivated_at !== null) {
            return Project::query()->whereRaw('1 = 0');
        }

        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'project_id';
        $tableNames = config('permission.table_names');

        return Project::query()->whereIn('id',
            DB::table($tableNames['model_has_roles'])
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->whereNotNull($teamForeignKey)
                ->select($teamForeignKey)
        );
    }

    /**
     * Menetapkan peran pengguna pada sebuah projek.
     */
    public static function assignRole(Project $project, User $user, string $roleName): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previous  = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($project->id);

        $user->unsetRelation('roles')->unsetRelation('permissions');

        try {
            // Bersihkan peran sebelumnya di projek ini
            $user->syncRoles([]);
            $user->assignRole($roleName);
        } finally {
            $registrar->setPermissionsTeamId($previous);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    /**
     * Menghapus semua peran pengguna pada sebuah projek.
     */
    public static function removeMember(Project $project, User $user): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previous  = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($project->id);

        $user->unsetRelation('roles')->unsetRelation('permissions');

        try {
            $user->syncRoles([]);
        } finally {
            $registrar->setPermissionsTeamId($previous);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    /**
     * Menghitung jumlah owner aktif di dalam sebuah projek.
     */
    public static function countOwners(Project $project): int
    {
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'project_id';
        $tableNames = config('permission.table_names');

        $ownerRole = Role::where('name', 'owner')->first();
        if (! $ownerRole) {
            return 0;
        }

        return DB::table($tableNames['model_has_roles'])
            ->join('users', 'users.id', '=', $tableNames['model_has_roles'].'.model_id')
            ->where($tableNames['model_has_roles'].'.model_type', User::class)
            ->where($tableNames['model_has_roles'].'.'.$teamForeignKey, $project->id)
            ->where($tableNames['model_has_roles'].'.role_id', $ownerRole->id)
            ->whereNull('users.deactivated_at')
            ->count();
    }

    /**
     * Mengambil daftar projek di mana pengguna adalah satu-satunya owner.
     *
     * @return array<int, string> [project_id => project_name]
     */
    public static function projectsWhereSoleOwner(User $user): array
    {
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'project_id';
        $tableNames = config('permission.table_names');

        $ownerRole = Role::where('name', 'owner')->first();
        if (! $ownerRole) {
            return [];
        }

        $projectIds = DB::table($tableNames['model_has_roles'])
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('role_id', $ownerRole->id)
            ->pluck($teamForeignKey);

        $soleProjects = [];

        foreach ($projectIds as $pId) {
            $project = Project::find($pId);
            if ($project && self::countOwners($project) <= 1) {
                $soleProjects[$project->id] = $project->name;
            }
        }

        return $soleProjects;
    }
}

