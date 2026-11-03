# Template Komponen: Empty State, Toast & Loading

Dokumen ini mendefinisikan template untuk keadaan kosong (*empty state*), notifikasi mengambang (*toast*), dan pemuatan kerangka (*skeleton loading*).

---

## 1. Keadaan Kosong (Empty State)

Digunakan ketika belum ada data (misal: belum ada projek, belum ada dokumen perencanaan, tempat sampah kosong).

### Spesifikasi:
- **Ikon Utama:** Ukuran `32px` (`h-8 w-8`), warna `--color-text-faint` (`#8089A0`), stroke 1.5 px.
- **Satu Kalimat Penjelas:** Teks ukuran `13px` - `14px` berwarna `--color-text-muted`.
- **Satu Tombol Tindakan:** Tombol ukuran standar (Primary atau Secondary).

### Struktur HTML / Blade:
```html
<div class="flex flex-col items-center justify-center rounded-[12px] border border-dashed border-border bg-surface-1/40 p-12 text-center">
  <!-- Ikon Lucide 32px -->
  <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-surface-3 text-text-faint mb-3">
    <svg class="h-8 w-8 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
  </div>

  <h4 class="text-sm font-semibold text-text">Belum ada kartu di kolom ini</h4>
  <p class="mt-1 max-w-sm text-xs text-text-muted">
    Mulai tambahkan tugas baru atau tarik kartu dari kolom lain ke sini.
  </p>

  <div class="mt-4">
    <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-[8px] bg-accent px-3 text-xs font-medium text-on-accent hover:bg-accent-hover transition">
      <svg class="h-3.5 w-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      <span>Buat Kartu Baru</span>
    </button>
  </div>
</div>
```

---

## 2. Pesan Singkat Mengambang (Toast Notification)

### Spesifikasi:
- **Latar Belakang:** `--color-surface-3` (`#232836`)
- **Garis Tepi Kiri:** Garis tebal `3px` berwarna status (`border-l-[3px] border-l-success` atau `border-l-danger` dsb).
- **Garis Tepi Lainnya:** `1px solid --color-border-strong`.
- **Sudut Membulat:** `8px` (`rounded-[8px]`).
- **Bayangan:** `shadow-xl`.
- **Perilaku:** Otomatis menghilang sendiri setelah **5 detik**, *kecuali pesan galat (error)* yang bertahan hingga ditutup manual oleh pengguna.

### Struktur HTML / Blade:
```html
<div 
  x-data="{ show: true }"
  x-show="show"
  x-init="setTimeout(() => show = false, 5000)"
  class="fixed bottom-5 right-5 z-50 flex max-w-sm items-center gap-3 rounded-[8px] border border-border-strong border-l-[3px] border-l-success bg-surface-3 p-3.5 shadow-xl transition-all"
>
  <!-- Ikon Status -->
  <div class="text-success shrink-0">
    <svg class="h-5 w-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  </div>

  <div class="flex-1 text-xs text-text">
    <p class="font-medium">Perubahan Berhasil Disimpan</p>
    <p class="text-text-muted mt-0.5">Urutan kartu telah diperbarui di server.</p>
  </div>

  <!-- Tombol Tutup Toast -->
  <button @click="show = false" class="text-text-faint hover:text-text transition shrink-0">
    <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>
</div>
```

---

## 3. Pemuatan Kerangka (Skeleton Loading)

Digunakan saat memuat kartu atau daftar:
```html
<div class="space-y-2 animate-pulse">
  <div class="h-20 w-full rounded-[8px] bg-surface-3 border border-border"></div>
  <div class="h-20 w-full rounded-[8px] bg-surface-3 border border-border"></div>
</div>
```
*Catatan:* Denyut opasitas dimatikan jika browser pengguna mengaktifkan preferensi `@media (prefers-reduced-motion: reduce)`.

