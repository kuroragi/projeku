<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function __construct()
    {
        // Hanya pengguna dengan is_admin = true yang diizinkan
    }

    protected function checkAdmin(): void
    {
        if (! auth()->user() || ! auth()->user()->is_admin) {
            abort(403, 'Aksi ini hanya untuk administrator.');
        }
    }

    public function index()
    {
        $this->checkAdmin();

        $users = User::latest()->paginate(20);

        if (request()->wantsJson()) {
            return response()->json($users);
        }

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Str::random(32),
            'is_admin' => $validated['is_admin'] ?? false,
            'notify_email' => true,
        ]);

        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Pengguna berhasil dibuat dan undangan telah dikirim.',
                'user' => $user,
            ], 201);
        }

        return back()->with('status', "Undangan telah dikirim ke {$user->email}.");
    }

    public function resendInvite(Request $request, User $user)
    {
        $this->checkAdmin();

        if (! $user->isActive()) {
            abort(422, 'Tidak dapat mengirim undangan ke pengguna nonaktif.');
        }

        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Undangan berhasil dikirim ulang.']);
        }

        return back()->with('status', "Undangan baru berhasil dikirim ulang ke {$user->email}.");
    }

    public function deactivate(Request $request, User $user)
    {
        $this->checkAdmin();

        if ($user->id === auth()->id()) {
            abort(422, 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        // Cek aturan pemilik terakhir
        $soleProjects = ProjectAccess::projectsWhereSoleOwner($user);
        if (! empty($soleProjects)) {
            $projectNames = implode(', ', $soleProjects);
            abort(422, "Pengguna ini tidak dapat dinonaktifkan karena merupakan satu-satunya pemilik pada projek: {$projectNames}. Alihkan kepemilikan projek terlebih dahulu.");
        }

        $user->update(['deactivated_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pengguna berhasil dinonaktifkan.']);
        }

        return back()->with('status', "Akun {$user->name} berhasil dinonaktifkan.");
    }

    public function activate(Request $request, User $user)
    {
        $this->checkAdmin();

        // Aktifkan kembali; peran projek yang lama tetap kosong sesuai aturan desain
        $user->update(['deactivated_at' => null]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pengguna berhasil diaktifkan kembali.']);
        }

        return back()->with('status', "Akun {$user->name} berhasil diaktifkan kembali.");
    }
}

