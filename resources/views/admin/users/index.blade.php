<x-layout title="Kelola Pengguna">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6" x-data="{ inviteModal: false }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-text">Kelola Pengguna</h1>
                <p class="mt-1 text-xs text-text-muted">Kelola akun dan kirim undangan ke anggota tim</p>
            </div>

            <div>
                <x-button @click="inviteModal = true" variant="primary">
                    <x-icon name="plus" :size="16" class="stroke-[2]" />
                    <span>Undang Pengguna Baru</span>
                </x-button>
            </div>
        </div>

        <div class="overflow-hidden rounded-[8px] border border-border bg-surface-2 shadow-sm">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-border bg-surface-1 text-text-muted font-medium">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Role Sistem</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($users as $user)
                        <tr class="hover:bg-surface-3/40 transition">
                            <td class="px-4 py-3 font-medium text-text">
                                <div class="flex items-center gap-2.5">
                                    <div class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-xs font-semibold text-[#C4B5FD]">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-text-muted">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @if($user->isActive())
                                    <span class="inline-flex items-center gap-1 rounded-[6px] bg-success-soft px-2 py-0.5 text-[11px] font-medium text-success">
                                        <x-icon name="check" :size="12" class="stroke-[2]" />
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-[6px] bg-danger-soft px-2 py-0.5 text-[11px] font-medium text-danger">
                                        <x-icon name="close" :size="12" class="stroke-[2]" />
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($user->is_admin)
                                    <span class="inline-flex items-center rounded-[6px] bg-accent-soft px-2 py-0.5 text-[11px] font-medium text-accent">
                                        Admin
                                    </span>
                                @else
                                    <span class="text-text-faint">Anggota</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($user->isActive())
                                        <form method="POST" action="{{ route('admin.users.resend', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-text-muted hover:text-accent transition text-xs" title="Kirim Ulang Undangan">
                                                Kirim Undangan
                                            </button>
                                        </form>

                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="inline" onsubmit="return confirm('Nonaktifkan akun ini?');">
                                                @csrf
                                                <button type="submit" class="text-danger hover:underline transition text-xs">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-success hover:underline transition text-xs">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal Undang Pengguna -->
        <div 
            x-show="inviteModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="inviteModal = false"></div>

            <div class="relative w-full max-w-md rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <h3 class="text-base font-semibold text-text">Undang Pengguna Baru</h3>
                    <button @click="inviteModal = false" class="text-text-muted hover:text-text transition">
                        <x-icon name="close" :size="16" />
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <x-input 
                        label="Nama Lengkap" 
                        name="name" 
                        required 
                        placeholder="e.g. Siti Aminah"
                    />

                    <x-input 
                        label="Alamat Email" 
                        type="email" 
                        name="email" 
                        required 
                        placeholder="siti@example.com"
                    />

                    <label class="flex items-center gap-2 cursor-pointer pt-1">
                        <input 
                            type="checkbox" 
                            name="is_admin" 
                            value="1"
                            class="h-4 w-4 rounded-[4px] border-border bg-surface-3 text-accent focus:ring-accent focus:ring-1"
                        />
                        <span class="text-xs text-text">Jadikan Administrator Sistem</span>
                    </label>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-border">
                        <x-button type="button" @click="inviteModal = false" variant="secondary">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Kirim Undangan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>

