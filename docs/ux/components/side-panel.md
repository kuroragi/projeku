# Template Komponen: Side Panel (Panel Samping Detail Kartu)

Panel samping digunakan untuk menampilkan dan mengedit rincian lengkap kartu tanpa menutupi seluruh layar board kanban.

---

## 1. Spesifikasi Teknis Panel Samping

- **Lebar (Desktop):** `480px` (`w-[480px]`)
- **Latar Belakang:** `--color-surface-1` (`#151821`)
- **Garis Tepi:** Garis tepi kiri `1px solid --color-border` (`border-l border-border`)
- **Bayangan Elemen:** `shadow-[-8px_0_24px_rgba(0,0,0,0.4)]`
- **Tampilan Ponsel:** Menjadi lembar layar penuh (*full-screen sheet* `fixed inset-0 z-50`) dengan tombol tutup di kiri atas.
- **Isi Panel:**
  1. Header: Nama kolom saat ini, status, tombol tutup (*close*).
  2. Judul kartu (dapat diedit *inline*).
  3. Meta aksi cepat: Tenggat waktu (*due date*), Prioritas, Penanggung jawab (*multi assignees*), Label projek.
  4. Tautan ke Halaman Perencanaan (*link to planning page* `page_id`).
  5. Deskripsi kartu (teks biasa).
  6. Multi-Checklist dengan batang progres individual.
  7. Galeri lampiran berkas kartu.
  8. Tombol hapus lunak (*Pindahkan ke tempat sampah*).

---

## 2. Struktur HTML / Blade Panel Samping

```html
<div 
  x-show="activeCardId !== null"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="translate-x-full"
  x-transition:enter-end="translate-x-0"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="translate-x-0"
  x-transition:leave-end="translate-x-full"
  class="fixed inset-y-0 right-0 z-40 flex w-full flex-col border-l border-border bg-surface-1 shadow-[-8px_0_24px_rgba(0,0,0,0.4)] md:w-[480px]"
  @keydown.escape.window="activeCardId = null"
>
  <!-- Bilah Atas Panel: Navigasi Kolom & Tombol Tutup -->
  <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
    <div class="flex items-center gap-2 text-xs text-text-muted">
      <span>Kolom:</span>
      <span class="font-medium text-text">Dalam Pengerjaan</span>
    </div>

    <div class="flex items-center gap-2">
      <!-- Tombol Tutup -->
      <button 
        @click="activeCardId = null" 
        class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] text-text-muted hover:bg-surface-3 hover:text-text transition"
        title="Tutup (Esc)"
      >
        <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
  </div>

  <!-- Area Konten yang Dapat Digulir -->
  <div class="flex-1 space-y-6 overflow-y-auto p-5 scrollbar-thin">
    <!-- Judul Kartu -->
    <div>
      <textarea 
        rows="2" 
        class="w-full resize-none rounded-[6px] border border-transparent bg-transparent text-lg font-semibold text-text hover:border-border focus:border-accent focus:bg-surface-3 focus:p-2 focus:outline-none"
      >Refactor modul autentikasi dengan Fortify dan perlindungan team Spatie</textarea>
    </div>

    <!-- Grid Metadata: Penanggung Jawab, Tenggat, Prioritas, Label -->
    <div class="grid grid-cols-2 gap-4 rounded-[8px] border border-border bg-surface-2 p-3.5">
      <!-- Penanggung Jawab -->
      <div>
        <label class="text-[11px] font-medium text-text-muted">Penanggung Jawab</label>
        <div class="mt-1 flex items-center gap-1.5">
          <div class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-[10px] font-semibold text-[#C4B5FD]">UU</div>
          <button class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-dashed border-border-strong text-text-muted hover:border-accent hover:text-accent transition text-xs">
            +
          </button>
        </div>
      </div>

      <!-- Tenggat Waktu -->
      <div>
        <label class="text-[11px] font-medium text-text-muted">Tenggat Waktu</label>
        <div class="mt-1 flex items-center gap-1 text-xs text-warning">
          <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
          <span class="font-medium">10 Nov 2026, 17:00</span>
        </div>
      </div>
    </div>

    <!-- Tautan ke Halaman Perencanaan -->
    <div class="rounded-[8px] border border-accent/30 bg-accent/5 p-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="h-4 w-4 stroke-[1.5] text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <div>
          <p class="text-xs font-medium text-text">Taut ke Dokumen Desain</p>
          <p class="text-[11px] text-text-muted">Arsitektur Autentikasi v1.2</p>
        </div>
      </div>
      <a href="#" class="text-xs font-medium text-accent hover:underline">Buka Rancangan &rarr;</a>
    </div>

    <!-- Deskripsi Kartu (Teks Biasa) -->
    <div class="space-y-1.5">
      <label class="text-xs font-medium text-text-muted">Deskripsi</label>
      <textarea 
        rows="4" 
        class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text placeholder-text-faint focus:border-accent focus:outline-none"
        placeholder="Tambahkan catatan atau deskripsi rinci..."
      ></textarea>
    </div>

    <!-- Bagian Checklist (lihat /docs/ux/components/list.md) -->
    <div class="border-t border-border pt-4">
      <!-- Checklist Component Disisipkan di Sini -->
    </div>
  </div>

  <!-- Footer Panel: Tombol Hapus Lunak -->
  <div class="border-t border-border px-5 py-3 flex items-center justify-between bg-surface-2/40">
    <button class="inline-flex items-center gap-1.5 text-xs text-text-faint hover:text-danger transition">
      <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      <span>Pindahkan ke tempat sampah</span>
    </button>
    <button class="inline-flex h-8 items-center rounded-[6px] bg-accent px-3 text-xs font-medium text-on-accent hover:bg-accent-hover transition">
      Simpan Perubahan
    </button>
  </div>
</div>
```

