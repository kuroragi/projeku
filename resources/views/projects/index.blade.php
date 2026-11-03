<x-layout title="Daftar Projek">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6" x-data="{ newProjectModal: false }">
        <!-- Header Halaman -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-text">Projek Saya</h1>
                <p class="mt-1 text-xs text-text-muted">Daftar projek yang dapat Anda akses</p>
            </div>

            <div>
                <x-button @click="newProjectModal = true" variant="primary">
                    <x-icon name="plus" :size="16" class="stroke-[2]" />
                    <span>Buat Projek Baru</span>
                </x-button>
            </div>
        </div>

        <!-- Grid Projek -->
        @if($projects->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-[12px] border border-dashed border-border bg-surface-1/40 p-12 text-center">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-surface-3 text-text-faint mb-3">
                    <x-icon name="folder" :size="32" />
                </div>
                <h4 class="text-sm font-semibold text-text">Belum ada projek</h4>
                <p class="mt-1 max-w-sm text-xs text-text-muted">
                    Anda belum terdaftar di projek mana pun. Buat projek baru untuk memulai.
                </p>
                <div class="mt-4">
                    <x-button @click="newProjectModal = true" variant="primary">
                        <x-icon name="plus" :size="16" class="stroke-[2]" />
                        <span>Buat Projek Pertama</span>
                    </x-button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($projects as $project)
                    <a 
                        href="{{ route('projects.show', $project) }}" 
                        class="group flex flex-col justify-between rounded-[8px] border border-border bg-surface-2 p-5 transition-all duration-150 hover:border-border-strong hover:bg-surface-2/95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-base font-semibold text-text group-hover:text-accent transition-colors">
                                    {{ $project->name }}
                                </h3>
                                @if($project->isArchived())
                                    <span class="inline-flex items-center rounded-[6px] bg-surface-3 px-2 py-0.5 text-[11px] font-medium text-text-faint">
                                        Arsip
                                    </span>
                                @endif
                            </div>

                            @if($project->description)
                                <p class="mt-2 text-xs leading-relaxed text-text-muted line-clamp-2">
                                    {{ $project->description }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-6 pt-3 border-t border-border flex items-center justify-between text-xs text-text-faint">
                            <span>Dibuat {{ $project->created_at->format('d M Y') }}</span>
                            <span class="text-accent group-hover:translate-x-0.5 transition-transform">Buka &rarr;</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Modal Buat Projek Baru -->
        <div 
            x-show="newProjectModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="newProjectModal = false"></div>

            <div class="relative w-full max-w-lg rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <h3 class="text-base font-semibold text-text">Buat Projek Baru</h3>
                    <button @click="newProjectModal = false" class="text-text-muted hover:text-text transition">
                        <x-icon name="close" :size="16" />
                    </button>
                </div>

                <form method="POST" action="{{ route('projects.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <x-input 
                        label="Nama Projek" 
                        name="name" 
                        id="name" 
                        required 
                        placeholder="e.g. Website Company Profile"
                        :error="$errors->first('name')"
                    />

                    <div class="flex flex-col gap-1.5">
                        <label for="desc" class="text-xs font-medium text-text">Deskripsi</label>
                        <textarea 
                            name="description" 
                            id="desc" 
                            rows="3" 
                            placeholder="Tujuan atau cakupan projek..." 
                            class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text placeholder-text-faint focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40"
                        ></textarea>
                    </div>

                    <x-input 
                        label="Tenggat Waktu (Opsional)" 
                        type="datetime-local" 
                        name="due_at" 
                        id="due_at" 
                    />

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-border">
                        <x-button type="button" @click="newProjectModal = false" variant="secondary">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Simpan Projek
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>

