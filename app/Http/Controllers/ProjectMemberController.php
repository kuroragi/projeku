<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'members.manage');

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:owner,editor,viewer'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if (! $user->isActive()) {
            return back()->withErrors(['email' => 'Pengguna ini sedang nonaktif dan tidak dapat ditambahkan ke projek.']);
        }

        ProjectAccess::assignRole($project, $user, $validated['role']);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Anggota berhasil ditambahkan.', 'user' => $user, 'role' => $validated['role']]);
        }

        return back()->with('status', "Anggota {$user->name} berhasil ditambahkan sebagai {$validated['role']}.");
    }

    public function update(Request $request, Project $project, User $user)
    {
        ProjectAccess::authorize($project, 'members.manage');

        $validated = $request->validate([
            'role' => ['required', 'in:owner,editor,viewer'],
        ]);

        $newRole = $validated['role'];

        DB::transaction(function () use ($project, $user, $newRole) {
            // Kunci baris projek untuk mencegah race condition penurunan owner
            Project::where('id', $project->id)->lockForUpdate()->first();

            $isCurrentOwner = ProjectAccess::can($project, 'project.delete', $user);

            // Jika menurunkan dari owner ke non-owner, pastikan minimal 1 owner tersisa
            if ($isCurrentOwner && $newRole !== 'owner') {
                $ownerCount = ProjectAccess::countOwners($project);
                if ($ownerCount <= 1) {
                    abort(422, 'Tidak dapat menurunkan peran: projek harus memiliki setidaknya satu pemilik (owner).');
                }
            }

            ProjectAccess::assignRole($project, $user, $newRole);
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Peran anggota berhasil diperbarui.']);
        }

        return back()->with('status', 'Peran anggota berhasil diperbarui.');
    }

    public function destroy(Request $request, Project $project, User $user)
    {
        ProjectAccess::authorize($project, 'members.manage');

        DB::transaction(function () use ($project, $user) {
            Project::where('id', $project->id)->lockForUpdate()->first();

            $isCurrentOwner = ProjectAccess::can($project, 'project.delete', $user);

            if ($isCurrentOwner) {
                $ownerCount = ProjectAccess::countOwners($project);
                if ($ownerCount <= 1) {
                    abort(422, 'Tidak dapat mengeluarkan anggota: projek harus memiliki setidaknya satu pemilik (owner).');
                }
            }

            ProjectAccess::removeMember($project, $user);
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Anggota berhasil dikeluarkan.']);
        }

        return back()->with('status', 'Anggota berhasil dikeluarkan dari projek.');
    }
}

