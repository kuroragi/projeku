# Template Komponen: Button (Tombol)

Dokumen ini mendefinisikan varian, ukuran, dan aturan interaksi tombol dalam aplikasi **projeku**.

---

## 1. Spesifikasi Umum Tombol

- **Tinggi Standar:** `36px` (`h-9`), padding horizontal `14px` (`px-3.5`)
- **Tinggi Kecil (Small):** `32px` (`h-8`), padding horizontal `10px` (`px-2.5`)
- **Sudut Membulat:** `8px` (`rounded-[8px]`)
- **Tipografi:** `13px` - `14px` (`text-sm`), font-weight 500 (`font-medium`)
- **Fokus Aksesibilitas:** Outline 2 px aksen dengan jarak offset 2 px (`focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent`)
- **Target Sentuh Ponsel:** Minimal 44 px (diberi ruang klik / tap target via wrapper atau padding responsif).

---

## 2. Varian Tombol

### A. Tombol Utama (Primary)
Digunakan untuk aksi primer satu halaman (misal: "Buat Projek", "Simpan Perubahan", "Kirim Undangan").
- **Latar:** `--color-accent` (`#8B7CF6`)
- **Teks:** `--color-on-accent` (`#0F1115`)
- **Hover:** `--color-accent-hover` (`#A094F8`)

```html
<button 
  type="button" 
  class="inline-flex h-9 items-center justify-center gap-2 rounded-[8px] bg-accent px-3.5 text-sm font-medium text-on-accent transition-all duration-150 hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent active:scale-[0.98]"
>
  <svg class="h-4 w-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
  <span>Tambah Kartu</span>
</button>
```

### B. Tombol Sekunder (Secondary)
Digunakan untuk aksi pendukung atau opsi kedua (misal: "Batal", "Unggah Berkas", "Filter").
- **Latar:** `--color-surface-3` (`#232836`)
- **Garis Tepi:** `1px solid --color-border` (`#2A3040`)
- **Teks:** `--color-text` (`#E6E8EE`)
- **Hover:** Border `--color-border-strong` (`#3A4157`)

```html
<button 
  type="button" 
  class="inline-flex h-9 items-center justify-center gap-2 rounded-[8px] border border-border bg-surface-3 px-3.5 text-sm font-medium text-text transition-all duration-150 hover:border-border-strong hover:bg-surface-3/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent active:scale-[0.98]"
>
  <span>Batal</span>
</button>
```

### C. Tombol Ghost (Transparan)
Digunakan untuk ikon tindakan di dalam baris daftar, tab secondary, atau tombol tutup panel.
- **Latar:** Transparan
- **Teks:** `--color-text-muted`
- **Hover:** Latar `--color-surface-3`, teks `--color-text`

```html
<button 
  type="button" 
  class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] text-text-muted transition hover:bg-surface-3 hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
  aria-label="Tutup panel"
>
  <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
</button>
```

### D. Tombol Bahaya Lembut (Danger Soft)
Digunakan untuk tindakan destruktif awal (seperti memindahkan kartu ke tempat sampah, menghapus checklist).
- **Latar:** `--color-danger-soft` (`rgba(248, 113, 113, 0.16)`)
- **Teks:** `--color-danger` (`#F87171`)
- **Hover:** Border `--color-danger/40`

```html
<button 
  type="button" 
  class="inline-flex h-9 items-center justify-center gap-1.5 rounded-[8px] bg-danger-soft px-3.5 text-sm font-medium text-danger transition hover:bg-danger-soft/80 hover:border-danger/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger"
>
  <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
  <span>Pindahkan ke Sampah</span>
</button>
```

### E. Tombol Bahaya Solid (Danger Solid / Hapus Permanen)
Hanya digunakan pada jendela konfirmasi hapus permanen projek atau berkas (tindakan yang tidak bisa di-undo).
- **Latar:** `--color-danger` (`#F87171`)
- **Teks:** `#FFFFFF` atau `--color-bg`

```html
<button 
  type="button" 
  class="inline-flex h-9 items-center justify-center gap-1.5 rounded-[8px] bg-danger px-3.5 text-sm font-medium text-white transition hover:bg-danger/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger"
>
  <span>Ya, Hapus Permanen</span>
</button>
```

---

## 3. Keadaan Memuat (Loading State)

Saat tombol sedang memproses panggilan AJAX atau pengiriman formulir:
```html
<button 
  type="button" 
  disabled 
  class="inline-flex h-9 items-center justify-center gap-2 rounded-[8px] bg-accent/70 px-3.5 text-sm font-medium text-on-accent cursor-not-allowed"
>
  <!-- Spinner Lucide / SVG -->
  <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
  </svg>
  <span>Menyimpan...</span>
</button>
```

