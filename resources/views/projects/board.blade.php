<x-layout :title="$project->name . ' — Board'">
    <div 
        class="flex h-[calc(100vh-56px)] flex-col overflow-hidden" 
        x-data="kanbanBoard({{ $project->id }}, {{ $columns->toJson() }}, {{ $labels->toJson() }}, {{ $members->toJson() }})"
        x-init="initBoard()"
        @keydown.window="if ((event.ctrlKey || event.metaKey) && event.key === 's' && activeCard) { event.preventDefault(); saveCardForm(); }"
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
                        class="h-8 w-44 rounded-[8px] border border-border-strong bg-surface-3 px-2.5 text-xs text-text placeholder-text-faint focus:border-accent focus:outline-none"
                    />

                    <select x-model="priorityFilter" class="h-8 rounded-[8px] border border-border-strong bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none">
                        <option value="">Semua Prioritas</option>
                        <option value="high">Prioritas Tinggi</option>
                        <option value="normal">Prioritas Normal</option>
                        <option value="low">Prioritas Rendah</option>
                    </select>

                    <x-button @click="openNewColumnModal = true" variant="primary" size="sm">
                        <x-icon name="plus" size="xs" class="stroke-[2]" />
                        <span>Tambah Kolom</span>
                    </x-button>
                </div>
            </div>

            <!-- Tab Navigasi Projek -->
            <nav class="mt-4 flex space-x-6">
                <a href="{{ route('projects.board', $project) }}" class="flex items-center gap-2 border-b-2 border-accent pb-2.5 text-xs font-medium text-text">
                    <x-icon name="board" size="sm" />
                    <span>Board</span>
                    <span class="rounded-full bg-surface-3 px-1.5 py-0.2 text-[10px] text-text-muted" x-text="totalCardsCount"></span>
                </a>
                <a href="{{ route('projects.planning.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="document" size="sm" />
                    <span>Perencanaan</span>
                </a>
                <a href="{{ route('projects.files.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="folder" size="sm" />
                    <span>Berkas</span>
                </a>
                <a href="{{ route('projects.trash.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="trash" size="sm" />
                    <span>Tempat Sampah</span>
                </a>
            </nav>
        </div>

        <!-- Area Kanban Board (Gulir Mendatar / Snap di Ponsel) -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-4 sm:p-6 scrollbar-thin">
            <div class="flex h-full items-start gap-4 snap-x snap-mandatory pb-4">
                <template x-for="column in columns" :key="column.id">
                    <div 
                        class="flex h-full w-[296px] shrink-0 snap-center flex-col rounded-[12px] border border-border bg-surface-1 p-2 select-none shadow-sm"
                        :data-column-id="column.id"
                    >
                        <!-- Header Kolom dengan Inline Rename & Menu Tiga Titik -->
                        <div class="flex items-center justify-between px-2 py-2" x-data="columnTitle(column, `/projects/${projectId}/columns/${column.id}`)">
                            <div class="flex items-center gap-2 flex-1 min-w-0 mr-1">
                                <template x-if="column.is_done">
                                    <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-success-soft text-success" title="Kolom Selesai">
                                        <x-icon name="check" size="xs" class="stroke-[2.5]" />
                                    </span>
                                </template>

                                <!-- Mode Baca / Klik dua kali untuk ubah nama -->
                                <template x-if="!editing">
                                    <h3 
                                        @dblclick="start()" 
                                        class="text-[13px] font-medium text-text-muted hover:text-text truncate cursor-pointer transition select-none" 
                                        title="Klik dua kali untuk mengubah nama"
                                        x-text="column.name"
                                    ></h3>
                                </template>

                                <!-- Mode Edit Judul Kolom Inline -->
                                <template x-if="editing">
                                    <input 
                                        x-ref="input"
                                        type="text" 
                                        x-model="draft" 
                                        @keydown.enter="save()"
                                        @keydown.escape="cancel()"
                                        @blur="save()"
                                        maxlength="60"
                                        class="h-6 w-full rounded-[4px] border border-accent bg-surface-3 px-1.5 text-xs text-text focus:outline-none"
                                    />
                                </template>

                                <span class="inline-flex h-5 min-w-[20px] shrink-0 items-center justify-center rounded-full bg-surface-3 px-1.5 text-[11px] font-medium text-text-faint" x-text="filteredCards(column).length"></span>
                            </div>

                            <!-- Menu Tiga Titik Kolom -->
                            <div class="relative shrink-0" x-data="{ colMenu: false }">
                                <button @click="colMenu = !colMenu" class="inline-flex h-6 w-6 items-center justify-center rounded-[6px] text-text-muted hover:bg-surface-3 hover:text-text transition">
                                    <x-icon name="more-vertical" size="sm" />
                                </button>
                                <div x-show="colMenu" @click.outside="colMenu = false" class="absolute right-0 z-20 mt-1 w-44 rounded-[8px] border border-border-strong bg-surface-2 p-1 shadow-lg" style="display: none;">
                                    <button @click="start(); colMenu = false;" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
                                        <x-icon name="pencil" size="xs" />
                                        <span>Ubah Nama</span>
                                    </button>
                                    <button @click="toggleColumnDone(column); colMenu = false;" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
                                        <x-icon name="check" size="xs" />
                                        <span x-text="column.is_done ? 'Batal Tandai Selesai' : 'Tandai Selesai'"></span>
                                    </button>
                                    <div class="my-1 border-t border-border"></div>
                                    <button @click="deleteColumn(column); colMenu = false;" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-danger hover:bg-danger-soft transition">
                                        <x-icon name="trash" size="xs" />
                                        <span>Hapus Kolom</span>
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

                                        <template x-if="card.priority && card.priority !== 'normal'">
                                            <span 
                                                class="inline-flex items-center rounded-[4px] px-1.5 py-0.2 text-[10px] font-semibold uppercase tracking-wider"
                                                :class="card.priority === 'high' ? 'bg-danger-soft text-danger' : 'bg-surface-3 text-text-faint'"
                                                x-text="card.priority"
                                            ></span>
                                        </template>
                                    </div>

                                    <!-- Judul Kartu -->
                                    <p class="text-xs font-medium text-text leading-snug line-clamp-2" x-text="card.title"></p>

                                    <!-- Baris Bawah: Tenggat, Checklist, Penanggung Jawab -->
                                    <div class="flex items-center justify-between pt-1 text-[11px] text-text-faint">
                                        <div class="flex items-center gap-2">
                                            <template x-if="card.checklists && card.checklists.length > 0">
                                                <div class="flex items-center gap-1" :title="getChecklistSummary(card)">
                                                    <x-icon name="check" size="xs" />
                                                    <span x-text="getChecklistProgressText(card)"></span>
                                                </div>
                                            </template>

                                            <template x-if="card.due_at">
                                                <span 
                                                    class="inline-flex items-center gap-1 rounded-[6px] px-1.5 py-0.2 text-[10px] font-medium"
                                                    :class="getDueDateClass(card.due_at, column.is_done)"
                                                >
                                                    <x-icon name="clock" size="xs" />
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
                                class="flex w-full items-center gap-1.5 rounded-[8px] px-2 py-1.5 text-xs text-text-muted hover:bg-surface-2 hover:text-text transition"
                            >
                                <x-icon name="plus" size="xs" />
                                <span>Tambah kartu...</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Tombol Tambah Kolom Baru di Akhir Daftar Kolom -->
                <div class="w-[296px] shrink-0">
                    <button 
                        @click="openNewColumnModal = true"
                        class="flex w-full items-center justify-center gap-2 rounded-[12px] border border-dashed border-border-strong bg-surface-1/40 p-3.5 text-xs font-medium text-text-muted transition hover:border-accent hover:bg-surface-1 hover:text-text"
                    >
                        <x-icon name="plus" size="xs" />
                        <span>Tambah Kolom</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel Samping (Side Panel) Detail Kartu - 480px -->
        <div 
            x-show="activeCard" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-40 flex w-full flex-col border-l border-border-strong bg-surface-1 shadow-[-8px_0_24px_rgba(0,0,0,0.5)] md:w-[480px]"
            @keydown.escape.window="closeSidePanel()"
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
                        <button @click="closeSidePanel()" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] text-text-muted hover:bg-surface-3 hover:text-text transition">
                            <x-icon name="close" size="sm" />
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-1 space-y-5 overflow-y-auto p-5 scrollbar-thin">
                        <!-- Notifikasi Berhasil Disimpan -->
                        <div x-show="cardSaveSuccess" x-transition class="rounded-[8px] border border-success bg-success-soft p-3 text-xs text-success flex items-center gap-2">
                            <x-icon name="check" size="sm" class="shrink-0 stroke-[2.5]" />
                            <span>Perubahan kartu berhasil disimpan.</span>
                        </div>

                        <!-- Peringatan Konflik (409) -->
                        <div x-show="cardConflict" class="rounded-[8px] border border-warning bg-warning-soft p-3 text-xs text-warning flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-icon name="alert-triangle" size="sm" class="shrink-0" />
                                <span>Kartu diubah orang lain sejak kamu membukanya.</span>
                            </div>
                            <button type="button" @click="reloadActiveCard()" class="underline font-semibold ml-2 hover:text-text">Muat Ulang</button>
                        </div>

                        <!-- Peringatan Galat Validasi (422) -->
                        <div x-show="Object.keys(cardErrors).length > 0" class="rounded-[8px] border border-danger bg-danger-soft p-3 text-xs text-danger space-y-1">
                            <template x-for="(errs, field) in cardErrors" :key="field">
                                <p x-text="Array.isArray(errs) ? errs.join(', ') : errs"></p>
                            </template>
                        </div>

                        <!-- Judul Kartu (Form Eksplisit) -->
                        <div>
                            <label class="text-[11px] font-medium text-text-muted block mb-1">Judul Kartu</label>
                            <input 
                                type="text"
                                x-model="cardForm.title" 
                                maxlength="200"
                                required
                                class="w-full rounded-[6px] border border-border-strong bg-surface-3 p-2 text-base font-semibold text-text placeholder-text-faint focus:border-accent focus:outline-none"
                                placeholder="Judul kartu..."
                            />
                        </div>

                        <!-- Metadata Grid: Prioritas & Tenggat Waktu -->
                        <div class="grid grid-cols-2 gap-3 rounded-[8px] border border-border bg-surface-2 p-3.5">
                            <div>
                                <label class="text-[11px] font-medium text-text-muted">Prioritas</label>
                                <select 
                                    x-model="cardForm.priority" 
                                    class="mt-1 h-8 w-full rounded-[6px] border border-border-strong bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none"
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
                                    x-model="cardForm.due_at" 
                                    class="mt-1 h-8 w-full rounded-[6px] border border-border-strong bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Label Projek (Selector Multi-Label) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-text-muted">Label</label>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="lbl in labels" :key="lbl.id">
                                    <button 
                                        type="button"
                                        @click="toggleLabel(lbl.id)"
                                        class="inline-flex items-center gap-1 rounded-[6px] px-2 py-1 text-xs font-medium transition border"
                                        :style="`background-color: ${lbl.color}20; color: ${lbl.color}; border-color: ${cardForm.label_ids.includes(lbl.id) ? lbl.color : 'transparent'};`"
                                    >
                                        <template x-if="cardForm.label_ids.includes(lbl.id)">
                                            <x-icon name="check" size="xs" />
                                        </template>
                                        <span x-text="lbl.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Penanggung Jawab (Assignees Multi-Select) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-text-muted">Penanggung Jawab</label>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="member in members" :key="member.id">
                                    <button 
                                        type="button"
                                        @click="toggleAssignee(member.id)"
                                        class="inline-flex items-center gap-1.5 rounded-[6px] border px-2 py-1 text-xs transition"
                                        :class="cardForm.assignee_ids.includes(member.id) ? 'border-accent bg-accent-soft text-accent' : 'border-border-strong bg-surface-3 text-text-muted hover:text-text'"
                                    >
                                        <template x-if="cardForm.assignee_ids.includes(member.id)">
                                            <x-icon name="check" size="xs" />
                                        </template>
                                        <span x-text="member.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Deskripsi Kartu (Teks Biasa) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-text-muted">Deskripsi</label>
                            <textarea 
                                x-model="cardForm.description" 
                                rows="4" 
                                class="w-full rounded-[8px] border border-border-strong bg-surface-3 p-3 text-sm text-text placeholder-text-faint focus:border-accent focus:outline-none"
                                placeholder="Tambahkan catatan teks biasa..."
                            ></textarea>
                        </div>

                        <!-- Checklists Section (Simpan Langsung Sekali Klik) -->
                        <div class="border-t border-border pt-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-medium text-text uppercase tracking-wider">Checklist</h4>
                                <button @click="addChecklist()" class="text-xs text-accent hover:underline">+ Checklist Baru</button>
                            </div>

                            <template x-for="ch in activeCard.checklists" :key="ch.id">
                                <div class="space-y-2 rounded-[8px] border border-border bg-surface-2/60 p-3">
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
                                                    class="mt-0.5 h-4 w-4 rounded-[4px] border-border-strong bg-surface-3 text-accent focus:ring-accent"
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
                                        class="h-7 w-full rounded-[6px] border border-border-strong bg-surface-3 px-2 text-xs text-text placeholder-text-faint focus:border-accent focus:outline-none"
                                    />
                                </div>
                            </template>
                        </div>

                        <!-- Lampiran Berkas Section (Simpan Langsung) -->
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
                                <template x-if="cardForm.page_id">
                                    <a :href="`/projects/${projectId}/planning/${cardForm.page_id}`" class="text-xs text-accent hover:underline flex items-center gap-1">
                                        <span>Buka Halaman</span>
                                        <x-icon name="arrow-right" size="xs" />
                                    </a>
                                </template>
                            </div>
                            <div>
                                <select 
                                    x-model="cardForm.page_id" 
                                    class="w-full rounded-[6px] border border-border-strong bg-surface-3 px-3 py-1.5 text-xs text-text focus:border-accent focus:outline-none"
                                >
                                    <option value="">-- Tidak tertaut ke perencanaan --</option>
                                    @foreach($pages as $p)
                                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bilah Peringatan Perubahan Belum Disimpan (Hanya muncul saat dirty) -->
                    <div 
                        x-show="isCardDirty" 
                        class="border-t border-border-strong px-5 py-3 flex items-center justify-between bg-surface-2 shadow-lg shrink-0"
                    >
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-2 w-2 rounded-full bg-warning animate-pulse"></span>
                            <span class="text-xs text-warning font-medium">Ada perubahan yang belum disimpan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                @click="resetCardForm()" 
                                class="rounded-[6px] px-2.5 py-1 text-xs text-text-muted hover:text-text transition"
                            >
                                Batal
                            </button>
                            <x-button 
                                type="button" 
                                @click="saveCardForm()" 
                                variant="primary" 
                                size="sm" 
                                x-bind:disabled="isSavingCard"
                            >
                                <span x-text="isSavingCard ? 'Menyimpan...' : 'Simpan perubahan'"></span>
                            </x-button>
                        </div>
                    </div>

                    <!-- Footer Panel: Tombol Hapus Lunak & Tutup -->
                    <div class="border-t border-border px-5 py-3 flex items-center justify-between bg-surface-2/40 shrink-0">
                        <button @click="deleteActiveCard()" class="inline-flex items-center gap-1.5 text-xs text-text-faint hover:text-danger transition">
                            <x-icon name="trash" size="xs" />
                            <span>Pindahkan ke sampah</span>
                        </button>
                        <x-button @click="closeSidePanel()" variant="secondary" size="sm">
                            Tutup
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
                    <x-input label="Nama Kolom" x-model="newColumnName" required maxlength="60" placeholder="e.g. Dalam Review" />
                    <label class="flex items-center gap-2 cursor-pointer mt-3">
                        <input type="checkbox" x-model="newColumnIsDone" class="h-4 w-4 rounded-[4px] border-border-strong bg-surface-3 text-accent" />
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
            <div class="relative w-full max-w-sm rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-text mb-4">Tambah Kartu Baru</h3>
                <form @submit.prevent="createCard()">
                    <x-input label="Judul Tugas" x-model="newCardTitle" required maxlength="200" placeholder="Judul kartu tugas..." />
                    <div class="mt-3 flex justify-end gap-2">
                        <x-button type="button" @click="openNewCardModal = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Buat Kartu</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script SortableJS & Alpine Components -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('columnTitle', (column, url) => ({
                name: column.name,
                draft: column.name,
                editing: false,
                saving: false,
                error: null,
                start() {
                    this.draft = this.name;
                    this.editing = true;
                    this.$nextTick(() => this.$refs.input?.select());
                },
                cancel() {
                    this.editing = false;
                    this.error = null;
                },
                async save() {
                    if (!this.editing || this.saving) return;
                    const v = this.draft.trim();
                    if (v === this.name) return this.cancel();
                    if (!v) { this.error = 'Nama kolom wajib diisi'; return; }
                    if (v.length > 60) { this.error = 'Maksimal 60 karakter'; return; }

                    this.saving = true;
                    const res = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ name: v })
                    });
                    this.saving = false;
                    if (res.ok) {
                        const data = await res.json();
                        this.name = data.name;
                        column.name = data.name;
                        this.cancel();
                    } else {
                        const err = await res.json().catch(() => ({}));
                        alert(err.message ?? 'Gagal menyimpan. Coba lagi.');
                    }
                },
            }));
        });

        function kanbanBoard(projectId, initialColumns, labels, members) {
            return {
                projectId: projectId,
                columns: initialColumns,
                labels: labels,
                members: members,
                searchQuery: '',
                priorityFilter: '',
                activeCard: null,

                // Explicit Card Form State
                cardForm: {
                    title: '',
                    description: '',
                    priority: 'normal',
                    due_at: '',
                    page_id: '',
                    label_ids: [],
                    assignee_ids: []
                },
                baseCardForm: {},
                cardVersion: null,
                isSavingCard: false,
                cardConflict: false,
                cardErrors: {},
                cardSaveSuccess: false,

                get isCardDirty() {
                    if (!this.activeCard) return false;
                    return JSON.stringify(this.cardForm) !== JSON.stringify(this.baseCardForm);
                },

                formatForInput(isoStr) {
                    if (!isoStr) return '';
                    const d = new Date(isoStr);
                    if (isNaN(d.getTime())) return '';
                    const pad = n => String(n).padStart(2, '0');
                    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                },

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

                    // Event listener untuk pembaruan kartu realtime di board
                    window.addEventListener('card-saved', (e) => {
                        this.updateCardInColumns(e.detail);
                    });

                    // Konfirmasi saat meninggalkan halaman jika form dirty
                    window.addEventListener('beforeunload', (e) => {
                        if (this.isCardDirty) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
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
                            filter: 'input, button, select, textarea',
                            preventOnFilter: false,
                            onEnd: (evt) => {
                                const cardId = evt.item.getAttribute('data-card-id');
                                const fromColId = evt.from.getAttribute('data-column-id');
                                const targetColId = evt.to.getAttribute('data-column-id');
                                const newIndex = evt.newIndex;
                                const newPos = (newIndex + 1) * 1000;

                                const fromCol = this.columns.find(c => c.id == fromColId);
                                const targetCol = this.columns.find(c => c.id == targetColId);
                                if (fromCol && targetCol) {
                                    if (fromColId === targetColId) {
                                        const oldIndex = evt.oldIndex;
                                        if (fromCol.cards && oldIndex !== undefined && newIndex !== undefined) {
                                            const [movedCard] = fromCol.cards.splice(oldIndex, 1);
                                            if (movedCard) {
                                                movedCard.position = newPos;
                                                fromCol.cards.splice(newIndex, 0, movedCard);
                                            }
                                        }
                                    } else {
                                        const cardIndex = fromCol.cards ? fromCol.cards.findIndex(c => c.id == cardId) : -1;
                                        if (cardIndex !== -1) {
                                            const [movedCard] = fromCol.cards.splice(cardIndex, 1);
                                            if (movedCard) {
                                                movedCard.column_id = parseInt(targetColId, 10);
                                                movedCard.position = newPos;
                                                if (!targetCol.cards) targetCol.cards = [];
                                                targetCol.cards.splice(newIndex, 0, movedCard);
                                            }
                                        }
                                    }
                                }

                                if (this.activeCard && this.activeCard.id == cardId) {
                                    this.activeCard.column_id = parseInt(targetColId, 10);
                                }

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
                    if (this.isCardDirty && !confirm('Ada perubahan yang belum disimpan pada kartu ini. Batalkan perubahan?')) {
                        return;
                    }
                    this.activeCard = JSON.parse(JSON.stringify(card));
                    this.initCardForm();
                },

                closeSidePanel() {
                    if (this.isCardDirty && !confirm('Ada perubahan yang belum disimpan. Yakin ingin menutup?')) {
                        return;
                    }
                    this.activeCard = null;
                },

                initCardForm() {
                    if (!this.activeCard) return;
                    this.cardForm = {
                        title: this.activeCard.title || '',
                        description: this.activeCard.description || '',
                        priority: this.activeCard.priority || 'normal',
                        due_at: this.formatForInput(this.activeCard.due_at),
                        page_id: (this.activeCard.page_id !== null && this.activeCard.page_id !== undefined && this.activeCard.page_id !== '') ? String(this.activeCard.page_id) : '',
                        label_ids: this.activeCard.labels ? this.activeCard.labels.map(l => Number(l.id)).sort((a, b) => a - b) : [],
                        assignee_ids: this.activeCard.assignees ? this.activeCard.assignees.map(a => Number(a.id)).sort((a, b) => a - b) : [],
                    };
                    this.baseCardForm = JSON.parse(JSON.stringify(this.cardForm));
                    this.cardVersion = this.activeCard.updated_at;
                    this.cardConflict = false;
                    this.cardErrors = {};
                },

                resetCardForm() {
                    this.cardForm = JSON.parse(JSON.stringify(this.baseCardForm));
                    this.cardConflict = false;
                    this.cardErrors = {};
                },

                toggleLabel(labelId) {
                    labelId = Number(labelId);
                    const idx = this.cardForm.label_ids.indexOf(labelId);
                    if (idx === -1) {
                        this.cardForm.label_ids.push(labelId);
                    } else {
                        this.cardForm.label_ids.splice(idx, 1);
                    }
                    this.cardForm.label_ids.sort((a, b) => a - b);
                },

                toggleAssignee(userId) {
                    userId = Number(userId);
                    const idx = this.cardForm.assignee_ids.indexOf(userId);
                    if (idx === -1) {
                        this.cardForm.assignee_ids.push(userId);
                    } else {
                        this.cardForm.assignee_ids.splice(idx, 1);
                    }
                    this.cardForm.assignee_ids.sort((a, b) => a - b);
                },

                async saveCardForm() {
                    if (!this.isCardDirty || this.isSavingCard) return;
                    this.isSavingCard = true;
                    this.cardConflict = false;
                    this.cardErrors = {};

                    const payload = {
                        title: (this.cardForm.title || '').trim(),
                        description: this.cardForm.description,
                        priority: this.cardForm.priority,
                        due_at: (this.cardForm.due_at && !isNaN(new Date(this.cardForm.due_at).getTime()))
                            ? new Date(this.cardForm.due_at).toISOString()
                            : null,
                        page_id: (this.cardForm.page_id && this.cardForm.page_id !== '' && this.cardForm.page_id !== 'null')
                            ? parseInt(this.cardForm.page_id, 10)
                            : null,
                        label_ids: this.cardForm.label_ids,
                        assignee_ids: this.cardForm.assignee_ids,
                        version: this.cardVersion
                    };

                    try {
                        const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload)
                        });

                        this.isSavingCard = false;

                        if (res.ok) {
                            const updated = await res.json();
                            this.activeCard = updated;
                            this.initCardForm();
                            this.updateCardInColumns(updated);
                            this.cardSaveSuccess = true;
                            if (this._saveSuccessTimer) clearTimeout(this._saveSuccessTimer);
                            this._saveSuccessTimer = setTimeout(() => {
                                this.cardSaveSuccess = false;
                            }, 3000);
                            window.dispatchEvent(new CustomEvent('card-saved', { detail: updated }));
                        } else if (res.status === 409) {
                            this.cardConflict = true;
                        } else if (res.status === 422) {
                            const data = await res.json();
                            this.cardErrors = data.errors || {};
                        } else {
                            this.cardErrors = { _: ['Gagal menyimpan. Coba lagi.'] };
                        }
                    } catch (e) {
                        this.isSavingCard = false;
                        this.cardErrors = { _: ['Terjadi kesalahan koneksi. Silakan coba lagi.'] };
                    }
                },

                async reloadActiveCard() {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const freshCard = await res.json();
                        this.activeCard = freshCard;
                        this.initCardForm();
                        this.updateCardInColumns(freshCard);
                    }
                },

                updateCardInColumns(card) {
                    if (!card || !card.id) return;
                    let foundCard = null;
                    let foundCol = null;
                    let foundIdx = -1;

                    for (let col of this.columns) {
                        if (col.cards) {
                            const idx = col.cards.findIndex(c => c.id == card.id);
                            if (idx !== -1) {
                                foundCard = col.cards[idx];
                                foundCol = col;
                                foundIdx = idx;
                                break;
                            }
                        }
                    }

                    if (foundCard && foundCol) {
                        if (card.column_id && card.column_id != foundCol.id) {
                            foundCol.cards.splice(foundIdx, 1);
                            const targetCol = this.columns.find(c => c.id == card.column_id);
                            if (targetCol) {
                                if (!targetCol.cards) targetCol.cards = [];
                                Object.assign(foundCard, card);
                                targetCol.cards.push(foundCard);
                            }
                        } else {
                            Object.assign(foundCard, card);
                            foundCol.cards.splice(foundIdx, 1, foundCard);
                        }
                    }
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
                            'Accept': 'application/json',
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
                    try {
                        const res = await fetch(`/projects/${this.projectId}/columns`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                name: this.newColumnName.trim(),
                                is_done: Boolean(this.newColumnIsDone)
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
                        } else {
                            const err = await res.json().catch(() => ({}));
                            alert(err.message || 'Gagal menambahkan kolom.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    }
                },

                async toggleColumnDone(column) {
                    const updatedIsDone = !column.is_done;
                    const res = await fetch(`/projects/${this.projectId}/columns/${column.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
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
                        alert('Pindahkan kartunya dulu.');
                        return;
                    }
                    if (!confirm(`Hapus kolom "${column.name}"?`)) return;

                    const res = await fetch(`/projects/${this.projectId}/columns/${column.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        this.columns = this.columns.filter(c => c.id !== column.id);
                    } else {
                        const err = await res.json().catch(() => ({}));
                        alert(err.message || 'Pindahkan kartunya dulu.');
                    }
                },

                async deleteActiveCard() {
                    if (!this.activeCard) return;
                    if (!confirm('Pindahkan kartu ini ke tempat sampah?')) return;
                    const cardId = this.activeCard.id;
                    const res = await fetch(`/projects/${this.projectId}/cards/${cardId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
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

                // Checklist Aksi Langsung
                async addChecklist() {
                    const title = prompt('Judul Checklist:');
                    if (!title) return;
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ title: title })
                    });
                    if (res.ok) {
                        const ch = await res.json();
                        if (!this.activeCard.checklists) this.activeCard.checklists = [];
                        this.activeCard.checklists.push(ch);
                        this.updateCardInColumns(this.activeCard);
                    }
                },

                async deleteChecklist(checklistId) {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        this.activeCard.checklists = this.activeCard.checklists.filter(c => c.id !== checklistId);
                        this.updateCardInColumns(this.activeCard);
                    }
                },

                async addChecklistItem(checklistId, text) {
                    if (!text || !text.trim()) return;
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}/items`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
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
                            this.updateCardInColumns(this.activeCard);
                        }
                    }
                },

                async toggleChecklistItem(checklistId, item) {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}/items/${item.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        item.is_done = !item.is_done;
                        this.updateCardInColumns(this.activeCard);
                    }
                },

                async deleteChecklistItem(checklistId, itemId) {
                    const res = await fetch(`/projects/${this.projectId}/cards/${this.activeCard.id}/checklists/${checklistId}/items/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        const ch = this.activeCard.checklists.find(c => c.id == checklistId);
                        if (ch && ch.items) {
                            ch.items = ch.items.filter(i => i.id !== itemId);
                            this.updateCardInColumns(this.activeCard);
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
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: formData
                    });
                    if (res.ok) {
                        const uploaded = await res.json();
                        if (!this.activeCard.files) this.activeCard.files = [];
                        this.activeCard.files.push(uploaded);
                        this.updateCardInColumns(this.activeCard);
                    } else {
                        const err = await res.json().catch(() => ({}));
                        alert(err.message || 'Gagal mengunggah gambar');
                    }
                },

                async deleteCardFile(fileId) {
                    if (!confirm('Hapus lampiran gambar ini?')) return;
                    const res = await fetch(`/projects/${this.projectId}/files/${fileId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    if (res.ok) {
                        if (this.activeCard.files) {
                            this.activeCard.files = this.activeCard.files.filter(f => f.id !== fileId);
                            this.updateCardInColumns(this.activeCard);
                        }
                    } else {
                        const err = await res.json().catch(() => ({}));
                        alert(err.message || 'Gagal menghapus berkas');
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
