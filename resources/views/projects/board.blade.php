<x-layout :title="$project->name . ' — Board'">
    <div 
        class="flex h-[calc(100vh-56px)] flex-col overflow-hidden" 
        x-data="kanbanBoard({{ $project->id }}, {{ $columns->toJson() }}, {{ $labels->toJson() }}, {{ $members->toJson() }})"
        x-init="initBoard()"
    >
        <!-- Header Projek & Navigasi Tab -->
        <div class="border-b border-border bg-surface-1 px-4 sm:px-6 pt-4 pb-0 shrink-0">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-semibold text-text">{{ $project->name }}</h1>
                    @if($project->isArchived())
                        <span class="rounded-[6px] bg-surface-3 px-2 py-0.5 text-xs font-medium text-text-faint">Arsip</span>
                    @endif
                </div>

                <!-- Tombol Aksi & Filter -->
                <div class="flex items-center gap-2">
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Filter kartu..." 
                        class="h-8 w-44 rounded-[8px] border border-border bg-surface-3 px-2.5 text-xs text-text placeholder-text-faint focus:border-accent focus:outline-none"
                    />

                    <select x-model="priorityFilter" class="h-8 rounded-[8px] border border-border bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none">
                        <option value="">Semua Prioritas</option>
                        <option value="high">Prioritas Tinggi</option>
                        <option value="normal">Prioritas Normal</option>
                        <option value="low">Prioritas Rendah</option>
                    </select>

                    <x-button @click="openNewColumnModal = true" variant="primary" size="sm">
                        <x-icon name="plus" :size="14" class="stroke-[2]" />
                        <span>Kolom Baru</span>
                    </x-button>
                </div>
            </div>

            <!-- Tab Navigasi Projek -->
            <nav class="mt-4 flex space-x-6">
                <a href="{{ route('projects.board', $project) }}" class="flex items-center gap-2 border-b-2 border-accent pb-2.5 text-xs font-medium text-text">
                    <x-icon name="board" :size="16" />
                    <span>Board</span>
                    <span class="rounded-full bg-surface-3 px-1.5 py-0.2 text-[10px] text-text-muted" x-text="totalCardsCount"></span>
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

        <!-- Area Kanban Board (Gulir Mendatar / Snap di Ponsel) -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-4 sm:p-6 scrollbar-thin">
            <div class="flex h-full items-start gap-4 snap-x snap-mandatory pb-4">
                <template x-for="column in columns" :key="column.id">
                    <div 
                        class="flex h-full w-[296px] shrink-0 snap-center flex-col rounded-[12px] border border-border bg-surface-1 p-2 select-none"
                        :data-column-id="column.id"
                    >
                        <!-- Header Kolom -->
                        <div class="flex items-center justify-between px-2 py-2">
                            <div class="flex items-center gap-2">
                                <template x-if="column.is_done">
                                    <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-success-soft text-success" title="Kolom Selesai">
                                        <x-icon name="check" :size="12" class="stroke-[2.5]" />
                                    </span>
                                </template>
                                <h3 class="text-[13px] font-medium text-text-muted truncate max-w-[170px]" x-text="column.name"></h3>
                                <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-surface-3 px-1.5 text-[11px] font-medium text-text-faint" x-text="filteredCards(column).length"></span>
                            </div>

                            <div class="relative" x-data="{ colMenu: false }">
                                <button @click="colMenu = !colMenu" class="inline-flex h-6 w-6 items-center justify-center rounded-[6px] text-text-muted hover:bg-surface-3 hover:text-text transition">
                                    <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                                <div x-show="colMenu" @click.outside="colMenu = false" class="absolute right-0 z-20 mt-1 w-44 rounded-[8px] border border-border-strong bg-surface-2 p-1 shadow-lg" style="display: none;">
                                    <button @click="toggleColumnDone(column); colMenu = false;" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
                                        <span x-text="column.is_done ? 'Batal Tandai Selesai' : 'Tandai Selesai (is_done)'"></span>
                                    </button>
                                    <div class="my-1 border-t border-border"></div>
                                    <button @click="deleteColumn(column); colMenu = false;" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-danger hover:bg-danger-soft transition">
                                        Hapus Kolom
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Drop Container Kartu (SortableJS) -->
                        <div 
                            class="card-list flex-1 space-y-2 overflow-y-auto px-1 py-1 min-h-[60px] scrollbar-thin"
                            :data-column-id="column.id"
                        >
                            <template x-for="card in filteredCards(column)" :key="card.id">
                                <div 
                                    @click="selectCard(card)"
                                    class="group relative flex flex-col gap-2 rounded-[8px] border border-border bg-surface-2 p-3 text-left transition-all duration-150 hover:border-border-strong hover:bg-surface-2/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent cursor-pointer select-none"
                                    :data-card-id="card.id"
                                >
                                    <!-- Baris Atas: Label & Prioritas -->
                                    <div class="flex flex-wrap items-center justify-between gap-1">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <template x-for="lbl in card.labels" :key="lbl.id">
                                                <span 
                                                    class="inline-flex items-center rounded-[6px] px-1.5 py-0.2 text-[10px] font-medium"
                                                    :style="`background-color: ${lbl.color}28; color: ${lbl.color};`"
                                                    x-text="lbl.name"
                                                ></span>
                                            </template>
                                        </div>

                                        <template x-if="card.priority === 'high'">
                                            <span class="inline-flex items-center gap-0.5 rounded-[6px] bg-danger-soft px-1.5 py-0.2 text-[10px] font-medium text-danger">
                                                <x-icon name="arrow-up" :size="10" class="stroke-[2.5]" />
                                                <span>Tinggi</span>
                                            </span>
                                        </template>
                                        <template x-if="card.priority === 'low'">
                                            <span class="inline-flex items-center gap-0.5 text-[10px] font-medium text-text-muted">
                                                <x-icon name="arrow-down" :size="10" class="stroke-[2.5]" />
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Judul Kartu -->
                                    <h4 class="text-sm font-medium leading-snug text-text line-clamp-3" x-text="card.title"></h4>

                                    <!-- Baris Bawah: Meta Checklist, Tenggat, Avatar -->
                                    <div class="mt-0.5 flex items-center justify-between gap-2 pt-1 border-t border-border/50 text-xs text-text-muted">
                                        <div class="flex items-center gap-2">
                                            <template x-if="card.checklists && card.checklists.length > 0">
                                                <div class="flex items-center gap-1 text-[11px] font-medium text-text-muted" :title="getChecklistSummary(card)">
                                                    <x-icon name="check" :size="12" />
                                                    <span x-text="getChecklistProgressText(card)"></span>
                                                </div>
                                            </template>

                                            <template x-if="card.due_at">
                                                <span 
                                                    class="inline-flex items-center gap-1 rounded-[6px] px-1.5 py-0.2 text-[10px] font-medium"
                                                    :class="getDueDateClass(card.due_at, column.is_done)"
                                                >
                                                    <x-icon name="clock" :size="10" />
                                                    <span x-text="formatDate(card.due_at)"></span>
                                                </span>
                                            </template>
                                        </div>

                                        <template x-if="card.assignees && card.assignees.length > 0">
                                            <div class="flex -space-x-1 overflow-hidden">
                                                <template x-for="user in card.assignees" :key="user.id">
                                                    <div 
                                                        class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-[9px] font-semibold text-[#C4B5FD] ring-1 ring-surface-2" 
                                                        :title="user.name"
                                                        x-text="user.name.substring(0, 2).toUpperCase()"
                                                    ></div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Tombol Tambah Kartu Cepat -->
                        <div class="mt-2 pt-1 border-t border-border/60">
                            <button 
                                @click="openCreateCard(column)" 
                                type="button" 
                                class="flex w-full items-center justify-center gap-1.5 rounded-[8px] py-1.5 text-xs font-medium text-text-muted transition hover:bg-surface-3 hover:text-text"
                            >
                                <x-icon name="plus" :size="14" class="stroke-[2]" />
                                <span>Tambah kartu</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Side Panel: Detail Kartu (lihat /docs/ux/components/side-panel.md) -->
        <div 
            x-show="activeCard !== null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-40 flex w-full flex-col border-l border-border bg-surface-1 shadow-[-8px_0_24px_rgba(0,0,0,0.4)] md:w-[480px]"
            @keydown.escape.window="activeCard = null"
            style="display: none;"
        >
            <template x-if="activeCard">
                <div class="flex h-full flex-col">
                    <!-- Top Bar Side Panel -->
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5 shrink-0">
                        <div class="flex items-center gap-2 text-xs text-text-muted">
                            <span>Kolom:</span>
                            <span class="font-medium text-text" x-text="getColumnName(activeCard.column_id)"></span>
                        </div>
                        <button @click="activeCard = null" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] text-text-muted hover:bg-surface-3 hover:text-text transition">
                            <x-icon name="close" :size="16" />
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-1 space-y-5 overflow-y-auto p-5 scrollbar-thin">
                        <!-- Judul Kartu Editable -->
                        <div>
                            <textarea 
                                x-model="activeCard.title" 
                                @blur="saveActiveCard()"
                                rows="2" 
                                class="w-full resize-none rounded-[6px] border border-transparent bg-transparent text-lg font-semibold text-text hover:border-border focus:border-accent focus:bg-surface-3 focus:p-2 focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- Metadata Grid -->
                        <div class="grid grid-cols-2 gap-3 rounded-[8px] border border-border bg-surface-2 p-3.5">
                            <div>
                                <label class="text-[11px] font-medium text-text-muted">Prioritas</label>
                                <select 
                                    x-model="activeCard.priority" 
                                    @change="saveActiveCard()"
                                    class="mt-1 h-8 w-full rounded-[6px] border border-border bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none"
                                >
                                    <option value="low">Rendah</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">Tinggi</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] font-medium text-text-muted">Tenggat Waktu</label>
                                <input 
                                    type="datetime-local" 
                                    x-model="activeCard.due_at" 
                                    @change="saveActiveCard()"
                                    class="mt-1 h-8 w-full rounded-[6px] border border-border bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Deskripsi Kartu (Teks Biasa) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-text-muted">Deskripsi</label>
                            <textarea 
                                x-model="activeCard.description" 
                                @blur="saveActiveCard()"
                                rows="4" 
                                class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text placeholder-text-faint focus:border-accent focus:outline-none"
                                placeholder="Tambahkan catatan teks biasa..."
                            ></textarea>
                        </div>

                        <!-- Checklists Section -->
                        <div class="border-t border-border pt-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-medium text-text uppercase tracking-wider">Checklist</h4>
                                <button @click="addChecklist()" class="text-xs text-accent hover:underline">+ Checklist Baru</button>
                            </div>

                            <template x-for="ch in activeCard.checklists" :key="ch.id">
                                <div class="space-y-2 rounded-[8px] border border-border/80 bg-surface-2/40 p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-text" x-text="ch.title"></span>
                                        <button @click="deleteChecklist(ch.id)" class="text-[11px] text-text-faint hover:text-danger">Hapus</button>
                                    </div>

                                    <!-- Items -->
                                    <ul class="space-y-1">
                                        <template x-for="item in ch.items" :key="item.id">
                                            <li class="group flex items-start gap-2 rounded-[6px] px-1 py-1 hover:bg-surface-3/50">
                                                <input 
                                                    type="checkbox" 
                                                    :checked="item.is_done" 
                                                    @change="toggleChecklistItem(ch.id, item)"
                                                    class="mt-0.5 h-4 w-4 rounded-[4px] border-border bg-surface-3 text-accent focus:ring-accent"
                                                />
                                                <span class="flex-1 text-xs" :class="item.is_done ? 'text-text-faint line-through' : 'text-text'" x-text="item.text"></span>
                                                <button @click="deleteChecklistItem(ch.id, item.id)" class="opacity-0 group-hover:opacity-100 text-text-faint hover:text-danger text-[10px]">×</button>
                                            </li>
                                        </template>
                                    </ul>

                                    <!-- Add item quick input -->
                                    <input 
                                        type="text" 
                                        placeholder="Tambah item (tekan enter)..." 
                                        @keydown.enter.prevent="addChecklistItem(ch.id, $event.target.value); $event.target.value = '';"
                                        class="h-7 w-full rounded-[6px] border border-border bg-surface-3 px-2 text-xs text-text placeholder-text-faint focus:border-accent focus:outline-none"
                                    />
                                </div>
                            </template>
                        </div>

                        <!-- Lampiran Berkas Section -->
                        <div class="border-t border-border pt-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-medium text-text uppercase tracking-wider">Lampiran Berkas</h4>
                                <label class="cursor-pointer text-xs text-accent hover:underline">
                                    <span>+ Unggah Gambar</span>
                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="uploadCardImage($event)" />
                                </label>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="f in activeCard.files" :key="f.id">
                                    <div class="group relative aspect-video overflow-hidden rounded-[6px] border border-border bg-surface-3">
                                        <img :src="`/projects/${projectId}/files/${f.id}?thumb=1`" class="h-full w-full object-cover" />
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 transition-opacity">
                                            <a :href="`/projects/${projectId}/files/${f.id}`" target="_blank" class="text-xs text-text hover:text-accent">Buka</a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Tautan ke Halaman Perencanaan Section -->
                        <div class="border-t border-border pt-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-medium text-text uppercase tracking-wider">Halaman Perencanaan</h4>
                                <template x-if="activeCard.page_id">
                                    <a :href="`/projects/${projectId}/planning/${activeCard.page_id}`" class="text-xs text-accent hover:underline flex items-center gap-1">
                                        <span>Buka Halaman</span>
                                        <x-icon name="arrow-right" :size="12" />
                                    </a>
                                </template>
                            </div>
                            <div>
                                <select 
                                    x-model="activeCard.page_id" 
                                    @change="saveActiveCard()" 
                                    class="w-full rounded-[6px] border border-border bg-surface-3 px-3 py-1.5 text-xs text-text focus:border-accent focus:outline-none"
                                >
                                    <option :value="null">-- Tidak tertaut ke perencanaan --</option>
                                    @foreach($pages as $p)
                                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Panel: Tombol Hapus Lunak -->
                    <div class="border-t border-border px-5 py-3 flex items-center justify-between bg-surface-2/40 shrink-0">
                        <button @click="deleteActiveCard()" class="inline-flex items-center gap-1.5 text-xs text-text-faint hover:text-danger transition">
                            <x-icon name="trash" :size="14" />
                            <span>Pindahkan ke sampah</span>
                        </button>
                        <x-button @click="saveActiveCard(); activeCard = null;" variant="primary" size="sm">
                            Selesai
                        </x-button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Modal Buat Kolom Baru -->
        <div x-show="openNewColumnModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="openNewColumnModal = false"></div>
            <div class="relative w-full max-w-sm rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-text mb-4">Tambah Kolom Baru</h3>
                <form @submit.prevent="createColumn()">
                    <x-input label="Nama Kolom" x-model="newColumnName" required placeholder="e.g. Dalam Review" />
                    <label class="flex items-center gap-2 cursor-pointer mt-3">
                        <input type="checkbox" x-model="newColumnIsDone" class="h-4 w-4 rounded-[4px] border-border bg-surface-3 text-accent" />
                        <span class="text-xs text-text">Kolom Selesai (is_done)</span>
                    </label>
                    <div class="flex justify-end gap-2 mt-5">
                        <x-button type="button" @click="openNewColumnModal = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Tambah Kolom</x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Buat Kartu Cepat -->
        <div x-show="openNewCardModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="openNewCardModal = false"></div>
            <div class="relative w-full max-w-md rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-text mb-4">Tambah Kartu Baru</h3>
                <form @submit.prevent="createCard()">
                    <x-input label="Judul Tugas" x-model="newCardTitle" required placeholder="Judul kartu tugas..." />
                    <div class="mt-3 flex justify-end gap-2">
                        <x-button type="button" @click="openNewCardModal = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Buat Kartu</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script SortableJS & Alpine Component -->
    <script>
        function kanbanBoard(projectId, initialColumns, labels, members) {
            return {
                projectId: projectId,
                columns: initialColumns,
                labels: labels,
                members: members,
                searchQuery: '',
                priorityFilter: '',
                activeCard: null,
                openNewColumnModal: false,
                newColumnName: '',
                newColumnIsDone: false,
                openNewCardModal: false,
                targetColumnId: null,
                newCardTitle: '',

                get totalCardsCount() {
                    return this.columns.reduce((total, col) => total + (col.cards ? col.cards.length : 0), 0);
                },

                initBoard() {
                    this.$nextTick(() => {
                        this.initSortable();
                    });
                },

                initSortable() {
                    const SortableClass = window.Sortable || (typeof Sortable !== 'undefined' ? Sortable : null);
                    if (!SortableClass) {
                        setTimeout(() => this.initSortable(), 50);
                        return;
                    }
                    const containers = document.querySelectorAll('.card-list');
                    containers.forEach(container => {
                        if (container._sortable) {
                            container._sortable.destroy();
                        }
                        container._sortable = new SortableClass(container, {
                            group: 'kanban-cards',
                            animation: 150,
                            ghostClass: 'border-2',
                            chosenClass: 'scale-[1.02]',
                            dragClass: 'shadow-2xl',
                            onEnd: (evt) => {
                                const cardId = evt.item.getAttribute('data-card-id');
                                const targetColId = evt.to.getAttribute('data-column-id');
                                const newIndex = evt.newIndex;

                                // Hitung position 1000 intervals
                                const col = this.columns.find(c => c.id == targetColId);
                                const newPos = (newIndex + 1) * 1000;

                                fetch(`/projects/${this.projectId}/cards/${cardId}/move`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    },
                                    body: JSON.stringify({
                                        column_id: targetColId,
                                        position: newPos,
                                    })
                                });
                            }
                        });
                    });
                },

                filteredCards(column) {
                    if (!column.cards) return [];
                    return column.cards.filter(c => {
                        const matchSearch = !this.searchQuery || c.title.toLowerCase().includes(this.searchQuery.toLowerCase());
                        const matchPriority = !this.priorityFilter || c.priority === this.priorityFilter;
                        return matchSearch && matchPriority;
                    });
                },

                selectCard(card) {
                    this.activeCard = JSON.parse(JSON.stringify(card));
                },

                getColumnName(colId) {
                    const col = this.columns.find(c => c.id == colId);
                    return col ? col.name : '';
                },

                openCreateCard(column) {
                    this.targetColumnId = column.id;
                    this.newCardTitle = '';
                    this.openNewCardModal = true;
                },

                async createCard() {
                    if (!this.newCardTitle.trim()) return;
                    const res = await fetch(`/projects/${this.projectId}/columns/${this.targetColumnId}/cards`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ title: this.newCardTitle })
                    });
                    if (res.ok) {
                        const newCard = await res.json();
                        const col = this.columns.find(c => c.id == this.targetColumnId);
                        if (!col.cards) col.cards = [];
                        col.cards.push(newCard);
                        this.openNewCardModal = false;
                    }
                },

                async createColumn() {
                    if (!this.newColumnName.trim()) return;
                    const res = await fetch(`/projects/${this.projectId}/columns`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            name: this.newColumnName,
                            is_done: this.newColumnIsDone
                        })
                    });
                    if (res.ok) {
                        const newCol = await res.json();
                        newCol.cards = [];
                        this.columns.push(newCol);
                        this.openNewColumnModal = false;
                        this.newColumnName = '';
                        this.newColumnIsDone = false;
                        this.$nextTick(() => this.initSortable());
                    }
                },

                async toggleColumnDone(column) {
                    const updatedIsDone = !column.is_done;
                    const res = await fetch(`/projects/${this.projectId}/columns/${column.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ is_done: updatedIsDone })
                    });
                    if (res.ok) {
                        column.is_done = updatedIsDone;
                    }
                },

                async deleteColumn(column) {
                    if (column.cards && column.cards.length > 0) {
                        alert('Kolom tidak dapat dihapus selama masih berisi kartu.');
                        return;
                    }
                    if (!confirm(`Hapus kolom "${column.name}"?`)) return;

                    const res = await fetch(`/projects/${this.projectId}/columns/${column.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        this.columns = this.columns.filter(c => c.id !== column.id);
                    }
                },

                async saveActiveCard() {
                    if (!this.activeCard) return;
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.activeCard)
                    });
                    if (res.ok) {
                        const updated = await res.json();
                        for (let col of this.columns) {
                            if (col.cards) {
                                const idx = col.cards.findIndex(c => c.id == updated.id);
                                if (idx !== -1) {
                                    col.cards[idx] = updated;
                                    break;
                                }
                            }
                        }
                    }
                },

                async deleteActiveCard() {
                    if (!this.activeCard) return;
                    if (!confirm('Pindahkan kartu ini ke tempat sampah?')) return;
                    const cardId = this.activeCard.id;
                    const res = await fetch(`/projects/${this.projectId}/cards/${cardId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        for (let col of this.columns) {
                            if (col.cards) {
                                col.cards = col.cards.filter(c => c.id !== cardId);
                            }
                        }
                        this.activeCard = null;
                    }
                },

                async addChecklist() {
                    const title = prompt('Judul Checklist:');
                    if (!title) return;
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ title: title })
                    });
                    if (res.ok) {
                        const ch = await res.json();
                        if (!this.activeCard.checklists) this.activeCard.checklists = [];
                        this.activeCard.checklists.push(ch);
                    }
                },

                async deleteChecklist(checklistId) {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        this.activeCard.checklists = this.activeCard.checklists.filter(c => c.id !== checklistId);
                    }
                },

                async addChecklistItem(checklistId, text) {
                    if (!text || !text.trim()) return;
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}/items`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ text: text })
                    });
                    if (res.ok) {
                        const item = await res.json();
                        const ch = this.activeCard.checklists.find(c => c.id == checklistId);
                        if (ch) {
                            if (!ch.items) ch.items = [];
                            ch.items.push(item);
                        }
                    }
                },

                async toggleChecklistItem(checklistId, item) {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}/items/${item.id}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        item.is_done = !item.is_done;
                    }
                },

                async deleteChecklistItem(checklistId, itemId) {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}/items/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        const ch = this.activeCard.checklists.find(c => c.id == checklistId);
                        if (ch && ch.items) {
                            ch.items = ch.items.filter(i => i.id !== itemId);
                        }
                    }
                },

                async uploadCardImage(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('card_id', this.activeCard.id);

                    const res = await fetch(`/projects/${this.projectId}/files`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: formData
                    });
                    if (res.ok) {
                        const uploaded = await res.json();
                        if (!this.activeCard.files) this.activeCard.files = [];
                        this.activeCard.files.push(uploaded);
                    } else {
                        const err = await res.json();
                        alert(err.message || 'Gagal mengunggah gambar');
                    }
                },

                getChecklistProgressText(card) {
                    let total = 0;
                    let done = 0;
                    if (card.checklists) {
                        card.checklists.forEach(ch => {
                            if (ch.items) {
                                total += ch.items.length;
                                done += ch.items.filter(i => i.is_done).length;
                            }
                        });
                    }
                    return `${done}/${total}`;
                },

                getChecklistSummary(card) {
                    return `Checklist: ${this.getChecklistProgressText(card)} selesai`;
                },

                getDueDateClass(dueAt, isDone) {
                    if (isDone) return 'bg-success-soft text-success';
                    const now = new Date();
                    const due = new Date(dueAt);
                    const diffHours = (due - now) / (1000 * 60 * 60);

                    if (diffHours < 0) return 'bg-danger-soft text-danger';
                    if (diffHours < 24) return 'bg-warning-soft text-warning';
                    return 'bg-surface-3 text-text-muted';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                }
            };
        }
    </script>
</x-layout>

