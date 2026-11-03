<x-layout :title="$project->name">
    <div class="border-b border-border bg-surface-1 px-4 sm:px-6 pt-5 pb-0">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-[28px] font-semibold text-text leading-tight">
                        {{ $project->name }}
                    </h1>
                    @if($project->isArchived())
                        <span class="rounded-[6px] bg-surface-3 px-2 py-0.5 text-xs font-medium text-text-faint">
                            Arsip
                        </span>
                    @endif
                </div>
                @if($project->description)
                    <p class="mt-1 text-xs text-text-muted">
                        {{ $project->description }}
                    </p>
                @endif
            </div>

            <!-- Aksi Projek -->
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.index') }}" class="text-xs text-text-muted hover:text-text transition">
                    &larr; Kembali ke Daftar Projek
                </a>
            </div>
        </div>

        <!-- Tab Navigasi -->
        <nav class="mt-6 flex space-x-6">
            <span class="flex items-center gap-2 border-b-2 border-accent pb-3 text-xs font-medium text-text cursor-default">
                <x-icon name="board" :size="16" />
                <span>Board (Tahap 2)</span>
            </span>
            <span class="flex items-center gap-2 border-b-2 border-transparent pb-3 text-xs font-medium text-text-faint cursor-default">
                <x-icon name="document" :size="16" />
                <span>Perencanaan (Tahap 4)</span>
            </span>
            <span class="flex items-center gap-2 border-b-2 border-transparent pb-3 text-xs font-medium text-text-faint cursor-default">
                <x-icon name="folder" :size="16" />
                <span>Berkas (Tahap 2)</span>
            </span>
        </nav>
    </div>

    <!-- Area Konten Utama -->
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <div class="rounded-[12px] border border-border bg-surface-2 p-6">
            <h3 class="text-base font-semibold text-text mb-2">Informasi Projek Fondasi (Tahap 1)</h3>
            <p class="text-xs text-text-muted leading-relaxed">
                Projek ini telah berhasil dikonfigurasi dengan mode multi-tenant Spatie Permission (teams). Board dan kartu kanban akan aktif pada implementasi Tahap 2.
            </p>
            <div class="mt-4 pt-4 border-t border-border flex items-center justify-between text-xs text-text-faint">
                <span>ID Projek: #{{ $project->id }}</span>
                <span>Dibuat pada: {{ $project->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>
</x-layout>

