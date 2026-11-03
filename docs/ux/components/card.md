# Template Komponen: Card (Kartu)

Komponen kartu adalah elemen terpenting dalam aplikasi **projeku**. Kartu digunakan untuk:
1. **Kartu Tugas Kanban (Board Card)**
2. **Kartu Projek (Project List Card)**
3. **Kartu Halaman Perencanaan (Planning Page Card)**

---

## 1. Kartu Tugas Kanban (Board Task Card)

### Spesifikasi:
- **Latar Belakang:** `--color-surface-2` (`#1B1F2A`)
- **Garis Tepi (Border):** `1px solid --color-border` (`#2A3040`)
- **Sudut Membulat:** `8px` (`rounded-[8px]`)
- **Padding:** `10px` hingga `12px` (`p-3`)
- **Tipografi Judul:** `14px` (`text-sm`), font-weight 500 (`font-medium`), warna `--color-text`, dibatasi maksimal 3 baris (`line-clamp-3`).
- **Keadaan Hover:** Garis tepi berubah menjadi `--color-border-strong` (`#3A4157`).
- **Fokus Papan Ketik:** Outline aksen 2 px dengan jarak offset 2 px (`focus-visible:ring-2 focus-visible:ring-accent focus-visible:outline-none`).
- **Keadaan Diseret (Dragging / SortableJS):**
  - **`sortable-drag` / Terangkat:** Diperbesar `scale-[1.02]`, border berubah menjadi `--color-accent`, bayangan `shadow-[0_8px_24px_rgba(0,0,0,0.5)]`.
  - **`sortable-ghost` / Drop Target Placeholder:** Kotak putus-putus (`border-2 border-dashed border-accent/40 rounded-[8px] bg-accent/5 min-h-[72px]`).

### Struktur HTML / Blade:
```html
<div 
  tabindex="0"
  role="button"
  @click="openCardDetail(card.id)"
  class="group relative flex flex-col gap-2.5 rounded-[8px] border border-border bg-surface-2 p-3 text-left transition-all duration-150 hover:border-border-strong hover:bg-surface-2/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent cursor-pointer select-none"
  :data-id="card.id"
>
  <!-- Baris Atas: Label Projek & Indikator Prioritas -->
  <div class="flex flex-wrap items-center justify-between gap-1.5">
    <div class="flex flex-wrap items-center gap-1">
      <!-- Contoh Chip Label -->
      <span class="inline-flex items-center rounded-[6px] px-2 py-0.5 text-[11px] font-medium bg-[#C4B5FD]/16 text-[#C4B5FD]">
        Frontend
      </span>
    </div>

    <!-- Prioritas Tinggi (Jika Normal tidak ditampilkan, Rendah ikon panah bawah muted) -->
    <span class="inline-flex items-center gap-1 rounded-[6px] bg-danger-soft px-1.5 py-0.5 text-[11px] font-medium text-danger">
      <!-- Ikon Lucide ArrowUp 12px -->
      <svg class="h-3 w-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7"/>
      </svg>
      Tinggi
    </span>
  </div>

  <!-- Judul Kartu (Maksimal 3 Baris, Teks Biasa) -->
  <h4 class="text-sm font-medium leading-snug text-text line-clamp-3">
    Refactor modul autentikasi dengan Fortify dan perlindungan team Spatie
  </h4>

  <!-- Baris Bawah / Meta: Checklist Progress, Tenggat Waktu, Avatar Penanggung Jawab -->
  <div class="mt-0.5 flex items-center justify-between gap-2 pt-1 border-t border-border/50 text-xs text-text-muted">
    <div class="flex items-center gap-2.5">
      <!-- Progres Checklist (Contoh 3/5) -->
      <div class="flex items-center gap-1 text-[11px] font-medium text-text-muted" title="3 dari 5 tugas checklist selesai">
        <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
        </svg>
        <span>3/5</span>
      </div>

      <!-- Chip Tenggat Waktu (Contoh Status Mendekati < 24 Jam) -->
      <span class="inline-flex items-center gap-1 rounded-[6px] bg-warning-soft px-1.5 py-0.5 text-[11px] font-medium text-warning">
        <svg class="h-3 w-3 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
        </svg>
        Besok 17:00
      </span>
    </div>

    <!-- Avatar Multi Penanggung Jawab -->
    <div class="flex -space-x-1.5 overflow-hidden">
      <div class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#93C5FD]/20 text-[10px] font-semibold text-[#93C5FD] ring-2 ring-surface-2" title="Ahmad Fauzi">
        AF
      </div>
      <div class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#5EEAD4]/20 text-[10px] font-semibold text-[#5EEAD4] ring-2 ring-surface-2" title="Budi Santoso">
        BS
      </div>
    </div>
  </div>
</div>
```

---

## 2. Kartu Projek (Project List Card)

### Spesifikasi:
- Digunakan pada layar Beranda / Dashboard (`/projects`).
- Hanya menampilkan projek di mana pengguna memiliki peran (*Owner*, *Editor*, atau *Viewer*).
- Menampilkan ringkasan progres kartu di kolom `is_done`, jumlah dokumen perencanaan, dan avatar tim.

### Struktur HTML / Blade:
```html
<a 
  href="/projects/1" 
  class="group flex flex-col justify-between rounded-[8px] border border-border bg-surface-2 p-5 transition-all duration-150 hover:border-border-strong hover:bg-surface-2/95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
>
  <div>
    <!-- Baris Judul & Badge Peran -->
    <div class="flex items-start justify-between gap-3">
      <h3 class="text-base font-semibold text-text group-hover:text-accent transition-colors">
        Redesain Portal Klien KDS
      </h3>
      <span class="inline-flex items-center rounded-[6px] bg-accent-soft px-2 py-0.5 text-[11px] font-medium text-accent">
        Owner
      </span>
    </div>

    <!-- Deskripsi Singkat -->
    <p class="mt-2 text-xs leading-relaxed text-text-muted line-clamp-2">
      Perombakan UI portal dan integrasi laporan bulanan dengan mode presentasi dokumen.
    </p>
  </div>

  <div class="mt-5 pt-3 border-t border-border flex flex-col gap-2.5">
    <!-- Bar Progress Penyelesaian -->
    <div>
      <div class="flex items-center justify-between text-xs text-text-muted mb-1">
        <span>Progres Projek</span>
        <span class="font-medium text-text">14 / 20 Kartu (70%)</span>
      </div>
      <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-3">
        <div class="h-full bg-accent transition-all duration-300" style="width: 70%"></div>
      </div>
    </div>

    <!-- Footer: Tenggat & Anggota -->
    <div class="flex items-center justify-between text-xs text-text-faint pt-1">
      <span class="flex items-center gap-1.5">
        <svg class="h-3.5 w-3.5 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
        </svg>
        Tenggat: 15 Nov 2026
      </span>

      <!-- Tim yang Terlibat -->
      <div class="flex -space-x-1.5 overflow-hidden">
        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-[10px] font-semibold text-[#C4B5FD] ring-2 ring-surface-2">UU</span>
        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#86EFAC]/20 text-[10px] font-semibold text-[#86EFAC] ring-2 ring-surface-2">EK</span>
      </div>
    </div>
  </div>
</a>
```

