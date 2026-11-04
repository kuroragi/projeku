<x-layout :title="$page->title . ' — Perencanaan — ' . $project->name">
    @php
        $canEdit = \App\Services\ProjectAccess::can($project, 'plan.edit');
    @endphp

    <div 
        class="mx-auto max-w-4xl px-4 py-8 sm:px-6"
        x-data="planningEditor({
            projectId: {{ $project->id }},
            pageId: {{ $page->id }},
            canEdit: {{ $canEdit ? 'true' : 'false' }},
            blocks: {{ Js::from($page->blocks) }}
        })"
    >
        <!-- Header Dokumen -->
        <div class="mb-6 border-b border-border pb-5">
            <div class="flex items-center gap-2 text-xs text-text-muted mb-2">
                <a href="{{ route('projects.planning.index', $project) }}" class="hover:text-text transition flex items-center gap-1">
                    <x-icon name="arrow-left" :size="12" />
                    <span>Dokumen Perencanaan</span>
                </a>
                <span>/</span>
                <span class="text-text-faint truncate max-w-xs">{{ $page->title }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    @if($canEdit)
                        <form action="{{ route('projects.pages.update', [$project, $page]) }}" method="POST" class="group flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input 
                                type="text" 
                                name="title" 
                                value="{{ old('title', $page->title) }}" 
                                class="w-full rounded-[6px] border border-transparent bg-transparent px-2 py-1 text-2xl font-bold text-text hover:border-border focus:border-accent focus:bg-surface-2 focus:outline-none transition"
                                onblur="this.form.submit()"
                            />
                        </form>
                    @else
                        <h1 class="text-2xl font-bold text-text px-2 py-1">{{ $page->title }}</h1>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('projects.pages.presentation', [$project, $page]) }}" class="inline-flex items-center gap-2 rounded-[6px] bg-accent px-3.5 py-1.5 text-xs font-medium text-on-accent hover:bg-accent-hover transition shadow-sm">
                        <x-icon name="presentation" :size="14" />
                        <span>Mode Presentasi</span>
                    </a>

                    @if($canEdit)
                        <form action="{{ route('projects.pages.destroy', [$project, $page]) }}" method="POST" onsubmit="return confirm('Pindahkan halaman ini ke tempat sampah?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="ghost" size="sm" class="text-text-faint hover:text-danger">
                                <x-icon name="trash" :size="14" />
                                <span class="sr-only">Hapus</span>
                            </x-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Toolbar Tambah Blok (Jika punya izin edit) -->
        @if($canEdit)
            <div class="mb-6 flex flex-wrap items-center gap-2 rounded-[8px] border border-border bg-surface-1 p-2.5 shadow-sm">
                <span class="text-xs font-medium text-text-muted px-2">Tambah Blok:</span>
                <button @click="openAddModal('judul')" class="inline-flex items-center gap-1.5 rounded-[6px] border border-border bg-surface-2 px-2.5 py-1 text-xs text-text hover:border-accent hover:text-accent transition">
                    <x-icon name="heading" :size="14" />
                    <span>Judul</span>
                </button>
                <button @click="openAddModal('teks')" class="inline-flex items-center gap-1.5 rounded-[6px] border border-border bg-surface-2 px-2.5 py-1 text-xs text-text hover:border-accent hover:text-accent transition">
                    <x-icon name="document" :size="14" />
                    <span>Teks Paragraf</span>
                </button>
                <button @click="openAddModal('gambar')" class="inline-flex items-center gap-1.5 rounded-[6px] border border-border bg-surface-2 px-2.5 py-1 text-xs text-text hover:border-accent hover:text-accent transition">
                    <x-icon name="image" :size="14" />
                    <span>Gambar</span>
                </button>
                <button @click="openAddModal('daftar')" class="inline-flex items-center gap-1.5 rounded-[6px] border border-border bg-surface-2 px-2.5 py-1 text-xs text-text hover:border-accent hover:text-accent transition">
                    <x-icon name="list" :size="14" />
                    <span>Daftar Butir</span>
                </button>
            </div>
        @endif

        <!-- Area Blok Dokumen -->
        <div id="blocks-container" class="space-y-4">
            <template x-for="(block, index) in blocks" :key="block.id">
                <div 
                    :data-id="block.id"
                    class="group relative rounded-[10px] border border-border bg-surface-1 p-4 sm:p-5 transition hover:border-border-strong shadow-sm"
                    :class="{ 'ring-1 ring-accent': block.type === 'judul' && block.content && block.content.level == 1 }"
                >
                    <!-- Drag handle & Action Buttons -->
                    <div class="absolute right-3 top-3 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <template x-if="canEdit">
                            <div class="flex items-center gap-1 bg-surface-2/90 backdrop-blur rounded-[6px] border border-border px-1.5 py-0.5">
                                <button @click="editBlock(block)" class="p-1 text-text-muted hover:text-accent transition" title="Ubah Blok">
                                    <x-icon name="settings" :size="13" />
                                </button>
                                <button @click="deleteBlock(block)" class="p-1 text-text-muted hover:text-danger transition" title="Hapus Blok">
                                    <x-icon name="trash" :size="13" />
                                </button>
                                <span class="drag-handle cursor-grab p-1 text-text-faint hover:text-text active:cursor-grabbing" title="Seret untuk urutkan">
                                    <x-icon name="grip" :size="14" />
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Render Sesuai Tipe Blok -->
                    <!-- 1. BLOK JUDUL -->
                    <template x-if="block.type === 'judul'">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5" x-show="block.content.level == 1">
                                <span class="inline-flex items-center gap-1 rounded-full bg-accent-soft px-2 py-0.5 text-[11px] font-medium text-accent">
                                    <x-icon name="presentation" :size="11" />
                                    <span>Judul memulai slide baru</span>
                                </span>
                            </div>

                            <template x-if="block.content.level == 1">
                                <h1 class="text-2xl sm:text-3xl font-bold text-text tracking-tight" x-text="block.content.text"></h1>
                            </template>
                            <template x-if="block.content.level == 2">
                                <h2 class="text-xl sm:text-2xl font-semibold text-text" x-text="block.content.text"></h2>
                            </template>
                            <template x-if="block.content.level == 3">
                                <h3 class="text-lg sm:text-xl font-medium text-text" x-text="block.content.text"></h3>
                            </template>
                        </div>
                    </template>

                    <!-- 2. BLOK TEKS -->
                    <template x-if="block.type === 'teks'">
                        <div class="text-sm sm:text-base text-text/90 leading-relaxed whitespace-pre-wrap" x-text="block.content.text"></div>
                    </template>

                    <!-- 3. BLOK GAMBAR -->
                    <template x-if="block.type === 'gambar'">
                        <div class="flex flex-col items-center">
                            <div class="max-h-[480px] w-full overflow-hidden rounded-[8px] border border-border bg-surface-2 flex items-center justify-center p-2">
                                <img 
                                    :src="block.content.url || (block.content.file_id ? `/projects/${projectId}/files/${block.content.file_id}` : '')" 
                                    :alt="block.content.caption || 'Gambar perencanaan'"
                                    class="max-h-[440px] max-w-full object-contain rounded"
                                />
                            </div>
                            <template x-if="block.content.caption">
                                <p class="mt-2 text-center text-xs text-text-muted italic" x-text="block.content.caption"></p>
                            </template>
                        </div>
                    </template>

                    <!-- 4. BLOK DAFTAR -->
                    <template x-if="block.type === 'daftar'">
                        <div>
                            <template x-if="block.content.style === 'numbered'">
                                <ol class="list-decimal list-inside space-y-1 text-sm sm:text-base text-text">
                                    <template x-for="(item, i) in (block.content.items || [])" :key="i">
                                        <li x-text="item"></li>
                                    </template>
                                </ol>
                            </template>
                            <template x-if="block.content.style !== 'numbered'">
                                <ul class="list-disc list-inside space-y-1 text-sm sm:text-base text-text">
                                    <template x-for="(item, i) in (block.content.items || [])" :key="i">
                                        <li x-text="item"></li>
                                    </template>
                                </ul>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <!-- State Kosong jika belum ada blok -->
            <template x-if="blocks.length === 0">
                <div class="rounded-[10px] border border-dashed border-border p-10 text-center">
                    <p class="text-sm text-text-muted">Halaman ini belum memiliki blok konten.</p>
                    <p class="text-xs text-text-faint mt-1">Gunakan tombol di atas untuk menambahkan judul, teks, gambar, atau daftar.</p>
                </div>
            </template>
        </div>

        <!-- Bagian Kartu Terhubung (Bidirectional Linkage) -->
        <div class="mt-12 border-t border-border pt-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-text uppercase tracking-wider flex items-center gap-2">
                    <x-icon name="board" :size="16" />
                    <span>Kartu Kanban Terhubung</span>
                    <span class="rounded-full bg-surface-3 px-2 py-0.5 text-xs text-text-muted">{{ $page->linkedCards->count() }}</span>
                </h3>
                <a href="{{ route('projects.board', $project) }}" class="text-xs text-accent hover:underline flex items-center gap-1">
                    <span>Lihat di Board</span>
                    <x-icon name="arrow-right" :size="12" />
                </a>
            </div>

            @if($page->linkedCards->isEmpty())
                <p class="text-xs text-text-muted italic bg-surface-1 rounded-[8px] p-3 border border-border">
                    Belum ada kartu di board yang ditautkan ke halaman perencanaan ini. Anda dapat menautkannya langsung dari panel samping kartu pada board.
                </p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($page->linkedCards as $card)
                        <a href="{{ route('projects.board', $project) }}" class="flex items-center justify-between rounded-[8px] border border-border bg-surface-1 p-3 hover:border-accent hover:bg-surface-2 transition">
                            <span class="text-xs font-medium text-text truncate">{{ $card->title }}</span>
                            <span class="shrink-0 rounded-[4px] px-2 py-0.5 text-[10px] font-medium 
                                @if($card->priority === 'high') bg-danger-soft text-danger 
                                @elseif($card->priority === 'low') bg-surface-3 text-text-muted 
                                @else bg-info-soft text-info @endif">
                                {{ ucfirst($card->priority) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- MODAL TAMBAH / UBAH BLOK -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="modalOpen = false"></div>
            <div class="relative w-full max-w-lg rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-text mb-4" x-text="isEditing ? 'Ubah Blok' : 'Tambah Blok ' + capitalize(activeType)"></h3>

                <form @submit.prevent="saveBlock()">
                    <!-- Form Blok Judul -->
                    <template x-if="activeType === 'judul'">
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-text-muted block mb-1">Level Judul</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="formData.level = 1" class="rounded-[6px] border py-2 text-xs font-medium transition" :class="formData.level == 1 ? 'border-accent bg-accent-soft text-accent' : 'border-border bg-surface-3 text-text-muted hover:text-text'">
                                        H1 (Slide Baru)
                                    </button>
                                    <button type="button" @click="formData.level = 2" class="rounded-[6px] border py-2 text-xs font-medium transition" :class="formData.level == 2 ? 'border-accent bg-accent-soft text-accent' : 'border-border bg-surface-3 text-text-muted hover:text-text'">
                                        H2 (Subjudul)
                                    </button>
                                    <button type="button" @click="formData.level = 3" class="rounded-[6px] border py-2 text-xs font-medium transition" :class="formData.level == 3 ? 'border-accent bg-accent-soft text-accent' : 'border-border bg-surface-3 text-text-muted hover:text-text'">
                                        H3 (Kecil)
                                    </button>
                                </div>
                            </div>
                            <x-input label="Teks Judul" x-model="formData.text" required placeholder="Tuliskan judul di sini..." />
                        </div>
                    </template>

                    <!-- Form Blok Teks -->
                    <template x-if="activeType === 'teks'">
                        <div class="space-y-3">
                            <label class="text-xs font-medium text-text-muted block">Isi Paragraf</label>
                            <textarea 
                                x-model="formData.text" 
                                rows="6" 
                                required
                                class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text placeholder-text-faint focus:border-accent focus:outline-none"
                                placeholder="Tulis teks atau penjelasan perencanaan di sini..."
                            ></textarea>
                        </div>
                    </template>

                    <!-- Form Blok Gambar -->
                    <template x-if="activeType === 'gambar'">
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-text-muted block mb-1">Pilih Berkas dari Galeri Projek</label>
                                <select x-model="formData.file_id" class="w-full rounded-[6px] border border-border bg-surface-3 px-3 py-2 text-xs text-text focus:border-accent focus:outline-none">
                                    <option :value="null">-- Pilih Berkas Gambar --</option>
                                    @foreach($availableFiles as $f)
                                        <option value="{{ $f->id }}">{{ $f->original_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-input label="Keterangan Gambar (Opsional)" x-model="formData.caption" placeholder="Keterangan atau sumber gambar..." />
                        </div>
                    </template>

                    <!-- Form Blok Daftar -->
                    <template x-if="activeType === 'daftar'">
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-text-muted block mb-1">Gaya Daftar</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="formData.style = 'bullet'" class="rounded-[6px] border py-2 text-xs font-medium transition" :class="formData.style !== 'numbered' ? 'border-accent bg-accent-soft text-accent' : 'border-border bg-surface-3 text-text-muted hover:text-text'">
                                        Bullet (Poin)
                                    </button>
                                    <button type="button" @click="formData.style = 'numbered'" class="rounded-[6px] border py-2 text-xs font-medium transition" :class="formData.style === 'numbered' ? 'border-accent bg-accent-soft text-accent' : 'border-border bg-surface-3 text-text-muted hover:text-text'">
                                        Nomor (1, 2, 3)
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-text-muted block mb-1">Butir-Butir Daftar (Satu baris per butir)</label>
                                <textarea 
                                    x-model="formData.items_raw" 
                                    rows="5" 
                                    required
                                    class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text placeholder-text-faint focus:border-accent focus:outline-none font-mono"
                                    placeholder="Butir 1&#10;Butir 2&#10;Butir 3"
                                ></textarea>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end gap-2 mt-6">
                        <x-button type="button" @click="modalOpen = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Simpan Blok</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function planningEditor(config) {
            return {
                projectId: config.projectId,
                pageId: config.pageId,
                canEdit: config.canEdit,
                blocks: config.blocks || [],
                modalOpen: false,
                isEditing: false,
                editingBlockId: null,
                activeType: 'judul',
                formData: {
                    level: 1,
                    text: '',
                    file_id: null,
                    caption: '',
                    style: 'bullet',
                    items_raw: ''
                },

                init() {
                    if (this.canEdit) {
                        this.initSortable();
                    }
                },

                initSortable() {
                    const SortableClass = window.Sortable || (typeof Sortable !== 'undefined' ? Sortable : null);
                    if (!SortableClass) {
                        setTimeout(() => this.initSortable(), 50);
                        return;
                    }
                    const container = document.getElementById('blocks-container');
                    if (container) {
                        if (container._sortable) {
                            container._sortable.destroy();
                        }
                        container._sortable = new SortableClass(container, {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-40',
                            onEnd: () => this.syncOrder()
                        });
                    }
                },

                capitalize(s) {
                    if (!s) return '';
                    return s.charAt(0).toUpperCase() + s.slice(1);
                },

                openAddModal(type) {
                    this.isEditing = false;
                    this.editingBlockId = null;
                    this.activeType = type;
                    this.formData = {
                        level: 1,
                        text: '',
                        file_id: null,
                        caption: '',
                        style: 'bullet',
                        items_raw: ''
                    };
                    this.modalOpen = true;
                },

                editBlock(block) {
                    this.isEditing = true;
                    this.editingBlockId = block.id;
                    this.activeType = block.type;
                    const content = block.content || {};

                    this.formData = {
                        level: content.level || 1,
                        text: content.text || '',
                        file_id: content.file_id || null,
                        caption: content.caption || '',
                        style: content.style || 'bullet',
                        items_raw: Array.isArray(content.items) ? content.items.join('\n') : ''
                    };
                    this.modalOpen = true;
                },

                async saveBlock() {
                    let contentPayload = {};
                    if (this.activeType === 'judul') {
                        contentPayload = {
                            text: this.formData.text,
                            level: parseInt(this.formData.level) || 1
                        };
                    } else if (this.activeType === 'teks') {
                        contentPayload = {
                            text: this.formData.text
                        };
                    } else if (this.activeType === 'gambar') {
                        contentPayload = {
                            file_id: this.formData.file_id ? parseInt(this.formData.file_id) : null,
                            caption: this.formData.caption
                        };
                    } else if (this.activeType === 'daftar') {
                        const items = this.formData.items_raw
                            .split('\n')
                            .map(i => i.trim())
                            .filter(i => i.length > 0);

                        contentPayload = {
                            style: this.formData.style,
                            items: items
                        };
                    }

                    const csrf = document.querySelector('meta[name="csrf-token"]').content;

                    if (this.isEditing) {
                        const res = await fetch(`/projects/${this.projectId}/planning/${this.pageId}/blocks/${this.editingBlockId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({ content: contentPayload })
                        });

                        if (res.ok) {
                            const updated = await res.json();
                            const idx = this.blocks.findIndex(b => b.id === this.editingBlockId);
                            if (idx !== -1) {
                                this.blocks[idx] = updated;
                            }
                            this.modalOpen = false;
                        }
                    } else {
                        const res = await fetch(`/projects/${this.projectId}/planning/${this.pageId}/blocks`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({
                                type: this.activeType,
                                content: contentPayload
                            })
                        });

                        if (res.ok) {
                            const created = await res.json();
                            this.blocks.push(created);
                            this.modalOpen = false;
                        }
                    }
                },

                async deleteBlock(block) {
                    if (!confirm('Hapus blok ini?')) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    const res = await fetch(`/projects/${this.projectId}/planning/${this.pageId}/blocks/${block.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        }
                    });

                    if (res.ok) {
                        this.blocks = this.blocks.filter(b => b.id !== block.id);
                    }
                },

                async syncOrder() {
                    const container = document.getElementById('blocks-container');
                    const elements = container.querySelectorAll('[data-id]');
                    const order = [];
                    elements.forEach((el, index) => {
                        order.push({
                            id: parseInt(el.getAttribute('data-id')),
                            position: (index + 1) * 1000
                        });
                    });

                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    await fetch(`/projects/${this.projectId}/planning/${this.pageId}/blocks/reorder`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ order: order })
                    });
                }
            };
        }
    </script>
    @endpush
</x-layout>
