# Template Komponen: List (Daftar)

Dokumen ini mendefinisikan template tampilan untuk seluruh komponen bertipe daftar dalam aplikasi **projeku**.

---

## 1. Daftar Checklist & Progres (Card Checklist List)

Sesuai rancangan, setiap kartu dapat memiliki banyak checklist yang masing-masing memiliki judul mandiri.

### Spesifikasi:
- **Judul Checklist:** `13px` (`text-[13px]`), font-weight 500, warna `--color-text`, diiringi jumlah item selesai (misal `2/5`) dan tombol hapus/menu checklist.
- **Batang Progres (Progress Bar):** Tinggi `4px` (`h-1`), latar `--color-surface-3`, warna batang `--color-accent` (`bg-accent`), radius penuh.
- **Item Checklist:**
  - Jarak vertikal `6px` (`space-y-1.5`).
  - Kotak centang kustom 16 px (`h-4 w-4 rounded-[4px] border-border bg-surface-3 checked:bg-accent checked:border-accent`).
  - Teks item: `13px` (`text-[13px]`). Bila selesai: warna `--color-text-faint` dan coret tengah (`line-through`).
  - Hover: Tombol hapus item (ikon silang atau tempat sampah) muncul saat baris di-hover.

### Struktur HTML / Blade:
```html
<div class="space-y-3" x-data="{ total: 3, done: 2 }">
  <!-- Header Checklist: Judul & Progres -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2">
      <svg class="h-4 w-4 stroke-[1.5] text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <h5 class="text-sm font-medium text-text">Daftar Pengujian API</h5>
    </div>
    <span class="text-xs font-medium text-text-muted">2/3</span>
  </div>

  <!-- Batang Progres 4px -->
  <div class="h-1 w-full overflow-hidden rounded-full bg-surface-3">
    <div class="h-full bg-accent transition-all duration-200" style="width: 66.6%"></div>
  </div>

  <!-- Baris Item Checklist -->
  <ul class="space-y-1">
    <!-- Item Selesai -->
    <li class="group flex items-start gap-2.5 rounded-[6px] px-2 py-1.5 transition hover:bg-surface-3/50">
      <input 
        type="checkbox" 
        checked 
        class="mt-0.5 h-4 w-4 rounded-[4px] border-border bg-surface-3 text-accent focus:ring-accent focus:ring-offset-0 focus:ring-1"
      />
      <span class="flex-1 text-[13px] text-text-faint line-through">
        Uji skenario batas otorisasi Spatie mode teams
      </span>
      <button class="opacity-0 group-hover:opacity-100 text-text-faint hover:text-danger transition">
        <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </li>

    <!-- Item Belum Selesai -->
    <li class="group flex items-start gap-2.5 rounded-[6px] px-2 py-1.5 transition hover:bg-surface-3/50">
      <input 
        type="checkbox" 
        class="mt-0.5 h-4 w-4 rounded-[4px] border-border bg-surface-3 text-accent focus:ring-accent focus:ring-offset-0 focus:ring-1"
      />
      <span class="flex-1 text-[13px] text-text">
        Validasi respons JSON pada rute /api/cards/reorder
      </span>
      <button class="opacity-0 group-hover:opacity-100 text-text-faint hover:text-danger transition">
        <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </li>
  </ul>

  <!-- Tambah Item Baru Cepat -->
  <div class="pt-1">
    <input 
      type="text" 
      placeholder="Tambah item baru..." 
      class="w-full rounded-[6px] border border-border bg-surface-3 px-2.5 py-1.5 text-xs text-text placeholder-text-faint focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
    />
  </div>
</div>
```

---

## 2. Daftar Anggota Projek (Project Members List)

Digunakan pada Pengaturan Projek untuk mengelola hak akses peran (*Owner*, *Editor*, *Viewer*).

### Struktur HTML / Blade:
```html
<div class="divide-y divide-border rounded-[8px] border border-border bg-surface-2">
  <!-- Baris Anggota -->
  <div class="flex items-center justify-between p-3.5">
    <div class="flex items-center gap-3">
      <!-- Avatar Inisial 32px -->
      <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-xs font-semibold text-[#C4B5FD]">
        UU
      </div>
      <div>
        <p class="text-sm font-medium text-text">Uum Rachman</p>
        <p class="text-xs text-text-faint">uum@example.com</p>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <span class="inline-flex items-center rounded-[6px] bg-accent-soft px-2 py-0.5 text-xs font-medium text-accent">
        Owner (Utama)
      </span>
    </div>
  </div>

  <!-- Baris Anggota Biasa (Dapat Diubah Peran atau Dihapus) -->
  <div class="flex items-center justify-between p-3.5">
    <div class="flex items-center gap-3">
      <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#93C5FD]/20 text-xs font-semibold text-[#93C5FD]">
        BS
      </div>
      <div>
        <p class="text-sm font-medium text-text">Budi Santoso</p>
        <p class="text-xs text-text-faint">budi@example.com</p>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <!-- Pilihan Peran -->
      <select class="h-8 rounded-[6px] border border-border bg-surface-3 px-2 text-xs text-text focus:border-accent focus:outline-none">
        <option value="editor" selected>Editor</option>
        <option value="viewer">Viewer</option>
        <option value="owner">Jadikan Owner</option>
      </select>

      <!-- Tombol Hapus Akses -->
      <button title="Keluarkan Anggota" class="inline-flex h-8 w-8 items-center justify-center rounded-[6px] text-text-muted hover:bg-danger-soft hover:text-danger transition">
        <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </button>
    </div>
  </div>
</div>
```

---

## 3. Daftar Berkas / Lampiran (File Attachment List)

### Spesifikasi:
- Digunakan pada panel samping detail kartu dan galeri berkas projek.
- Menampilkan thumbnail kecil 36×36 px bersudut 6 px.
- Menampilkan nama berkas, ukuran (KB / MB), serta tombol unduh dan hapus.

### Struktur HTML / Blade:
```html
<div class="space-y-2">
  <div class="flex items-center justify-between rounded-[8px] border border-border bg-surface-2 p-2 transition hover:border-border-strong">
    <div class="flex items-center gap-2.5 overflow-hidden">
      <!-- Thumbnail Gambar / Ikon Berkas -->
      <div class="h-9 w-9 shrink-0 overflow-hidden rounded-[6px] bg-surface-3 flex items-center justify-center border border-border">
        <img src="/storage/projects/1/thumb_mockup.webp" alt="Thumb" class="h-full w-full object-cover"/>
      </div>
      <div class="truncate">
        <p class="truncate text-xs font-medium text-text">mockup-dashboard-v2.png</p>
        <p class="text-[11px] text-text-faint">1.4 MB • Diunggah 2 jam lalu</p>
      </div>
    </div>

    <div class="flex items-center gap-1 shrink-0">
      <a href="#" class="inline-flex h-7 w-7 items-center justify-center rounded-[6px] text-text-muted hover:bg-surface-3 hover:text-text transition" title="Unduh">
        <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      </a>
      <button class="inline-flex h-7 w-7 items-center justify-center rounded-[6px] text-text-muted hover:bg-danger-soft hover:text-danger transition" title="Hapus">
        <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </button>
    </div>
  </div>
</div>
```

