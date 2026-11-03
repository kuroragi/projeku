# Template Komponen: Board Column (Kolom Kanban)

Kolom kanban merupakan kontainer utama kartu-kartu tugas dalam tampilan Board projek.

---

## 1. Spesifikasi Teknis Kolom

- **Lebar Kolom (Desktop):** Tepat `296px` (`w-[296px] shrink-0`)
- **Latar Belakang:** `--color-surface-1` (`#151821`)
- **Garis Tepi:** `1px solid --color-border` (`#2A3040`)
- **Sudut Membulat:** `12px` (`rounded-[12px]`)
- **Padding:** `8px` (`p-2`)
- **Jarak Antarkartu:** `8px` (`space-y-2`)
- **Judul Kolom:** `13px` (`text-[13px]`), font-weight 500 (`font-medium`), warna `--color-text-muted`.
- **Indikator Selesai (`is_done`):** Kolom yang ditandai sebagai kolom selesai (`is_done = true`) diberi ikon centang kecil di samping judulnya.
- **Tampilan Ponsel:** 
  - Menggunakan tata letak 1 kolom per layar dengan gulir mendatar (*horizontal scroll-snap*: `snap-x snap-mandatory`).
  - Pemindahan kartu di layar sentuh ponsel dilakukan melalui tombol menu opsi kartu (*"Pindahkan ke..."*), bukan diseret (*drag*), untuk menghindari bentrok dengan fungsi gulir halaman.

---

## 2. Struktur HTML / Blade Kolom Kanban

```html
<div 
  class="flex h-full w-[296px] shrink-0 flex-col rounded-[12px] border border-border bg-surface-1 p-2 max-h-[calc(100vh-140px)] select-none"
  data-column-id="1"
>
  <!-- Header Kolom -->
  <div class="flex items-center justify-between px-2 py-2">
    <div class="flex items-center gap-2">
      <!-- Ikon Centang jika Kolom is_done -->
      <!--
      <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-success-soft text-success" title="Kolom Selesai">
        <svg class="h-3 w-3 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      </span>
      -->

      <!-- Judul Kolom -->
      <h3 class="text-[13px] font-medium text-text-muted">Dalam Pengerjaan</h3>
      
      <!-- Penghitung Kartu -->
      <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-surface-3 px-1.5 text-[11px] font-medium text-text-faint">
        4
      </span>
    </div>

    <!-- Menu Tindakan Kolom (Ganti Nama, Jadikan Done, Hapus) -->
    <div class="relative" x-data="{ open: false }">
      <button 
        @click="open = !open" 
        class="inline-flex h-6 w-6 items-center justify-center rounded-[6px] text-text-muted hover:bg-surface-3 hover:text-text transition"
      >
        <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
      </button>

      <!-- Dropdown Menu Opsi Kolom -->
      <div 
        x-show="open" 
        @click.outside="open = false" 
        class="absolute right-0 z-20 mt-1 w-44 rounded-[8px] border border-border-strong bg-surface-2 p-1 shadow-lg"
      >
        <button class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
          Ganti Nama
        </button>
        <button class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
          Tandai Selesai (is_done)
        </button>
        <div class="my-1 border-t border-border"></div>
        <button class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-danger hover:bg-danger-soft transition">
          Hapus Kolom
        </button>
      </div>
    </div>
  </div>

  <!-- Area Daftar Kartu (SortableJS Drop Container) -->
  <div 
    class="flex-1 space-y-2 overflow-y-auto px-1 py-1 min-h-[100px] scrollbar-thin"
    id="column-cards-1"
  >
    <!-- Komponen Kartu Disisipkan di Sini (lihat /docs/ux/components/card.md) -->
  </div>

  <!-- Tombol Tambah Kartu Cepat di Bawah Kolom -->
  <div class="mt-2 pt-1 border-t border-border/60">
    <button 
      type="button" 
      class="flex w-full items-center justify-center gap-1.5 rounded-[8px] py-1.5 text-xs font-medium text-text-muted transition hover:bg-surface-3 hover:text-text"
    >
      <svg class="h-3.5 w-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      <span>Tambah kartu</span>
    </button>
  </div>
</div>
```

