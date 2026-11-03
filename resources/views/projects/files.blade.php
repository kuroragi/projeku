<x-layout :title="$project->name . ' — Galeri Berkas'">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6" x-data="{ uploadModal: false, activePreview: null }">
        <!-- Header -->
        <div class="border-b border-border bg-surface-1 px-4 sm:px-6 pt-4 pb-0 -mx-4 sm:-mx-6 -mt-8 mb-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-text">{{ $project->name }}</h1>
                    <p class="text-xs text-text-muted mt-0.5">Galeri berkas dan lampiran aset projek</p>
                </div>

                <div class="flex items-center gap-2">
                    <x-button @click="uploadModal = true" variant="primary" size="sm">
                        <x-icon name="plus" :size="14" class="stroke-[2]" />
                        <span>Unggah Gambar</span>
                    </x-button>
                </div>
            </div>

            <!-- Tab Navigasi -->
            <nav class="mt-4 flex space-x-6">
                <a href="{{ route('projects.board', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="board" :size="16" />
                    <span>Board</span>
                </a>
                <a href="{{ route('projects.files.index', $project) }}" class="flex items-center gap-2 border-b-2 border-accent pb-2.5 text-xs font-medium text-text">
                    <x-icon name="folder" :size="16" />
                    <span>Berkas</span>
                    <span class="rounded-full bg-surface-3 px-1.5 py-0.2 text-[10px] text-text-muted">{{ $files->total() }}</span>
                </a>
                <a href="{{ route('projects.trash.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="trash" :size="16" />
                    <span>Tempat Sampah</span>
                </a>
            </nav>
        </div>

        <!-- Galeri Grid -->
        @if($files->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-[12px] border border-dashed border-border bg-surface-1/40 p-12 text-center">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-surface-3 text-text-faint mb-3">
                    <x-icon name="folder" :size="32" />
                </div>
                <h4 class="text-sm font-semibold text-text">Belum ada berkas gambar</h4>
                <p class="mt-1 max-w-sm text-xs text-text-muted">
                    Semua gambar yang diunggah ke kartu atau galeri akan muncul di sini.
                </p>
                <div class="mt-4">
                    <x-button @click="uploadModal = true" variant="primary">
                        <x-icon name="plus" :size="16" class="stroke-[2]" />
                        <span>Unggah Gambar Pertama</span>
                    </x-button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach($files as $file)
                    <div class="group relative aspect-square overflow-hidden rounded-[8px] border border-border bg-surface-2 transition hover:border-border-strong">
                        <!-- Thumbnail Image -->
                        <img 
                            src="{{ route('projects.files.serve', [$project, $file]) }}?thumb=1" 
                            alt="{{ $file->original_name }}"
                            class="h-full w-full object-cover transition duration-200 group-hover:scale-105"
                            loading="lazy"
                        />

                        <!-- Overlay Info on Hover -->
                        <div class="absolute inset-0 flex flex-col justify-between bg-black/75 p-2.5 opacity-0 transition-opacity duration-150 group-hover:opacity-100">
                            <p class="text-xs font-medium text-text truncate" title="{{ $file->original_name }}">
                                {{ $file->original_name }}
                            </p>

                            <div class="flex items-center justify-between text-[11px] text-text-muted">
                                <span>{{ round($file->size / 1024, 1) }} KB</span>
                                <div class="flex items-center gap-1.5">
                                    <button 
                                        type="button"
                                        @click="activePreview = '{{ route('projects.files.serve', [$project, $file]) }}'"
                                        class="rounded p-1 text-text hover:text-accent"
                                        title="Perbesar"
                                    >
                                        <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('projects.files.destroy', [$project, $file]) }}" onsubmit="return confirm('Pindahkan ke tempat sampah?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded p-1 text-text hover:text-danger" title="Hapus">
                                            <x-icon name="trash" :size="14" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $files->links() }}
            </div>
        @endif

        <!-- Image Fullscreen Preview (rgba 0,0,0,0.9) -->
        <div 
            x-show="activePreview !== null" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            @click="activePreview = null"
            @keydown.escape.window="activePreview = null"
            style="display: none;"
        >
            <button @click="activePreview = null" class="absolute top-5 right-5 text-text-muted hover:text-text">
                <x-icon name="close" :size="24" />
            </button>
            <img :src="activePreview" class="max-h-[90vh] max-w-[90vw] object-contain rounded-[8px]" />
        </div>

        <!-- Modal Unggah -->
        <div x-show="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="uploadModal = false"></div>
            <div class="relative w-full max-w-md rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-text mb-4">Unggah Berkas Gambar</h3>
                <form method="POST" action="{{ route('projects.files.store', $project) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-medium text-text">Pilih Gambar</label>
                            <input 
                                type="file" 
                                name="file" 
                                accept="image/jpeg,image/png,image/webp" 
                                required 
                                class="h-9 w-full rounded-[8px] border border-border bg-surface-3 px-3 py-1.5 text-xs text-text file:mr-2 file:rounded-[4px] file:border-0 file:bg-surface-2 file:px-2 file:text-xs file:text-text"
                            />
                            <p class="text-[11px] text-text-faint">
                                Format: JPEG, PNG, WebP. Maksimal 10 MB dan 24 Megapiksel. SVG ditolak demi keamanan.
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-border">
                            <x-button type="button" @click="uploadModal = false" variant="secondary" size="sm">Batal</x-button>
                            <x-button type="submit" variant="primary" size="sm">Mulai Unggah</x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>

