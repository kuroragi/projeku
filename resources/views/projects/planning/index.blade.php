<x-layout :title="$project->name . ' — Halaman Perencanaan'">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6" x-data="{ newPageModal: false }">
        <!-- Header -->
        <div class="border-b border-border bg-surface-1 px-4 sm:px-6 pt-4 pb-0 -mx-4 sm:-mx-6 -mt-8 mb-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-text">{{ $project->name }}</h1>
                    <p class="text-xs text-text-muted mt-0.5">Dokumen perencanaan, rancangan bergambar, dan presentasi tim</p>
                </div>

                <div class="flex items-center gap-2">
                    <x-button @click="newPageModal = true" variant="primary" size="sm">
                        <x-icon name="plus" :size="14" class="stroke-[2]" />
                        <span>Halaman Baru</span>
                    </x-button>
                </div>
            </div>

            <!-- Tab Navigasi -->
            <nav class="mt-4 flex space-x-6">
                <a href="{{ route('projects.board', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="board" :size="16" />
                    <span>Board</span>
                </a>
                <a href="{{ route('projects.planning.index', $project) }}" class="flex items-center gap-2 border-b-2 border-accent pb-2.5 text-xs font-medium text-text">
                    <x-icon name="document" :size="16" />
                    <span>Perencanaan</span>
                    <span class="rounded-full bg-surface-3 px-1.5 py-0.2 text-[10px] text-text-muted">{{ $pages->count() }}</span>
                </a>
                <a href="{{ route('projects.files.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="folder" :size="16" />
                    <span>Berkas</span>
                </a>
                <a href="{{ route('projects.trash.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="trash" :size="16" />
                    <span>Tempat Sampah</span>
                </a>
            </nav>
        </div>

        <!-- Daftar Dokumen Perencanaan -->
        @if($pages->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-[12px] border border-dashed border-border bg-surface-1/40 p-12 text-center">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-surface-3 text-text-faint mb-3">
                    <x-icon name="document" :size="32" />
                </div>
                <h4 class="text-sm font-semibold text-text">Belum ada dokumen perencanaan</h4>
                <p class="mt-1 max-w-sm text-xs text-text-muted">
                    Buat halaman untuk menyusun konsep, rancangan arsitektur, atau materi presentasi bergambar.
                </p>
                <div class="mt-4">
                    <x-button @click="newPageModal = true" variant="primary">
                        <x-icon name="plus" :size="16" class="stroke-[2]" />
                        <span>Buat Dokumen Pertama</span>
                    </x-button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($pages as $page)
                    <div class="group flex flex-col justify-between rounded-[8px] border border-border bg-surface-2 p-5 transition hover:border-border-strong hover:bg-surface-2/90">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-base font-semibold text-text group-hover:text-accent transition">
                                    <a href="{{ route('projects.pages.show', [$project, $page]) }}">
                                        {{ $page->title }}
                                    </a>
                                </h3>
                            </div>

                            <div class="mt-3 flex items-center gap-3 text-xs text-text-faint">
                                <span>{{ $page->blocks_count }} Blok</span>
                                @if($page->linked_cards_count > 0)
                                    <span>• {{ $page->linked_cards_count }} Kartu Terkait</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-3 border-t border-border flex items-center justify-between text-xs">
                            <a href="{{ route('projects.pages.presentation', [$project, $page]) }}" class="inline-flex items-center gap-1.5 font-medium text-accent hover:underline">
                                <svg class="h-3.5 w-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                <span>Presentasi</span>
                            </a>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('projects.pages.show', [$project, $page]) }}" class="text-text-muted hover:text-text">
                                    Edit &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Modal Buat Halaman -->
        <div x-show="newPageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="newPageModal = false"></div>
            <div class="relative w-full max-w-md rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-text mb-4">Buat Halaman Perencanaan Baru</h3>
                <form method="POST" action="{{ route('projects.planning.store', $project) }}">
                    @csrf
                    <x-input label="Judul Dokumen" name="title" required placeholder="e.g. Rancangan Arsitektur UI v1" />
                    <div class="flex justify-end gap-2 mt-5">
                        <x-button type="button" @click="newPageModal = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Buat Halaman</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
