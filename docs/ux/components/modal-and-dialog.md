# Template Komponen: Modal, Dialog & Menu Popover

Dokumen ini mendefinisikan template untuk kotak dialog konfirmasi, modal formulir, dan menu popover dropdown.

---

## 1. Spesifikasi Teknis Modal & Dialog

- **Latar Belakang Kontainer:** `--color-surface-2` (`#1B1F2A`)
- **Garis Tepi (Border):** `1px solid --color-border-strong` (`#3A4157`)
- **Sudut Membulat:** `12px` (`rounded-[12px]`)
- **Latar Penutup (Backdrop Overlay):** `rgba(0, 0, 0, 0.6)` (`bg-black/60`) dengan sedikit efek buram (`backdrop-blur-sm`).
- **Aksesibilitas:** Menutup dengan tombol Escape (`@keydown.escape.window="open = false"`), fokus otomatis terjebak di dalam dialog, dan dapat ditutup bila mengklik area luar dialog (`@click.outside="open = false"`).

---

## 2. Dialog Konfirmasi Tindakan Destruktif (Hapus Permanen)

Digunakan khusus ketika pemilik projek ingin menghapus berkas atau projek secara permanen (tanpa bisa dipulihkan).

```html
<div 
  x-show="confirmModalOpen" 
  class="fixed inset-0 z-50 flex items-center justify-center p-4"
  style="display: none;"
>
  <!-- Backdrop Overlay -->
  <div 
    class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
    @click="confirmModalOpen = false"
  ></div>

  <!-- Kontainer Dialog 12px Radius -->
  <div 
    class="relative w-full max-w-md rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl transition-all"
    @click.outside="confirmModalOpen = false"
  >
    <!-- Ikon Peringatan & Judul -->
    <div class="flex items-start gap-4">
      <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-danger-soft text-danger">
        <svg class="h-5 w-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>

      <div class="flex-1">
        <h3 class="text-base font-semibold text-text">Hapus Berkas Permanen?</h3>
        <p class="mt-1.5 text-xs leading-relaxed text-text-muted">
          Tindakan ini tidak dapat dibatalkan. Berkas gambar dan lampiran terkait akan segera dimusnahkan dari server.
        </p>
      </div>
    </div>

    <!-- Tombol Aksi Bawah -->
    <div class="mt-6 flex items-center justify-end gap-2.5">
      <button 
        type="button" 
        @click="confirmModalOpen = false"
        class="inline-flex h-9 items-center justify-center rounded-[8px] border border-border bg-surface-3 px-3.5 text-sm font-medium text-text transition hover:border-border-strong"
      >
        Batal
      </button>

      <button 
        type="button" 
        class="inline-flex h-9 items-center justify-center rounded-[8px] bg-danger px-3.5 text-sm font-medium text-white transition hover:bg-danger/90"
      >
        Hapus Permanen
      </button>
    </div>
  </div>
</div>
```

---

## 3. Modal Formulir (Buat Projek Baru / Tambah Kolom)

```html
<div 
  x-show="newProjectModal" 
  class="fixed inset-0 z-50 flex items-center justify-center p-4"
  style="display: none;"
>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="newProjectModal = false"></div>

  <div class="relative w-full max-w-lg rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
    <!-- Header Modal -->
    <div class="flex items-center justify-between border-b border-border pb-3">
      <h3 class="text-base font-semibold text-text">Buat Projek Baru</h3>
      <button @click="newProjectModal = false" class="text-text-muted hover:text-text transition">
        <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Form Isi -->
    <form class="mt-4 space-y-4">
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-medium text-text">Nama Projek <span class="text-danger">*</span></label>
        <input type="text" placeholder="e.g. Website Company Profile" class="h-9 w-full rounded-[8px] border border-border bg-surface-3 px-3 text-sm text-text focus:border-accent focus:outline-none"/>
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-medium text-text">Deskripsi</label>
        <textarea rows="3" placeholder="Tujuan atau cakupan projek..." class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text focus:border-accent focus:outline-none"></textarea>
      </div>

      <div class="flex justify-end gap-2.5 pt-3 border-t border-border">
        <button type="button" @click="newProjectModal = false" class="h-9 rounded-[8px] border border-border bg-surface-3 px-4 text-xs font-medium text-text">Batal</button>
        <button type="submit" class="h-9 rounded-[8px] bg-accent px-4 text-xs font-medium text-on-accent hover:bg-accent-hover transition">Simpan Projek</button>
      </div>
    </form>
  </div>
</div>
```

---

## 4. Popover Menu Dropdown

```html
<div class="relative" x-data="{ menuOpen: false }">
  <button @click="menuOpen = !menuOpen" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] text-text-muted hover:bg-surface-3 hover:text-text transition">
    <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
  </button>

  <div 
    x-show="menuOpen" 
    @click.outside="menuOpen = false" 
    class="absolute right-0 z-30 mt-1 w-48 rounded-[8px] border border-border-strong bg-surface-2 p-1 shadow-xl"
  >
    <a href="#" class="flex items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
      <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
      <span>Edit Projek</span>
    </a>
    <div class="my-1 border-t border-border"></div>
    <button class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-danger hover:bg-danger-soft transition">
      <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      <span>Arsipkan Projek</span>
    </button>
  </div>
</div>
```

