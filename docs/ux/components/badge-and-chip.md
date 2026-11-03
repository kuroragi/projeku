# Template Komponen: Badge & Chip

Dokumen ini mengatur spesifikasi dan template untuk label projek, indikator prioritas, chip tenggat waktu, dan avatar inisial.

---

## 1. Spesifikasi Umum Badge & Chip

- **Sudut Membulat:** `6px` (`rounded-[6px]`)
- **Tipografi:** `11px` (`text-[11px]`), font-weight 500 (`font-medium`)
- **Padding:** Vertikal `2px`, horizontal `8px` (`px-2 py-0.5`)
- **Ikon Pelengkap:** Ukuran `12px` (`h-3 w-3`), stroke 1.5 - 2 px, warna mengikuti teks (`currentColor`).

---

## 2. Chip Label Projek (8 Pilihan Palet)

Latar belakang menggunakan warna teksnya pada transparansi 16% (`/16`):

```html
<!-- Ungu -->
<span class="inline-flex items-center rounded-[6px] bg-[#C4B5FD]/16 px-2 py-0.5 text-[11px] font-medium text-[#C4B5FD]">
  Backend
</span>

<!-- Biru -->
<span class="inline-flex items-center rounded-[6px] bg-[#93C5FD]/16 px-2 py-0.5 text-[11px] font-medium text-[#93C5FD]">
  Frontend
</span>

<!-- Tosca -->
<span class="inline-flex items-center rounded-[6px] bg-[#5EEAD4]/16 px-2 py-0.5 text-[11px] font-medium text-[#5EEAD4]">
  Desain UI
</span>

<!-- Hijau -->
<span class="inline-flex items-center rounded-[6px] bg-[#86EFAC]/16 px-2 py-0.5 text-[11px] font-medium text-[#86EFAC]">
  Rilis MVP
</span>

<!-- Oranye -->
<span class="inline-flex items-center rounded-[6px] bg-[#FDBA74]/16 px-2 py-0.5 text-[11px] font-medium text-[#FDBA74]">
  Pemasaran
</span>

<!-- Mawar (Rose) -->
<span class="inline-flex items-center rounded-[6px] bg-[#FDA4AF]/16 px-2 py-0.5 text-[11px] font-medium text-[#FDA4AF]">
  Bug Kritis
</span>

<!-- Kuning -->
<span class="inline-flex items-center rounded-[6px] bg-[#FCD34D]/16 px-2 py-0.5 text-[11px] font-medium text-[#FCD34D]">
  Dokumentasi
</span>

<!-- Abu -->
<span class="inline-flex items-center rounded-[6px] bg-[#CBD5E1]/16 px-2 py-0.5 text-[11px] font-medium text-[#CBD5E1]">
  Arsip
</span>
```

---

## 3. Indikator Prioritas (Priority Badge)

Aturan ketat:
- **Tinggi:** Tampilkan ikon panah atas dan teks "Tinggi" berlatar `--danger-soft` dan teks `--danger`.
- **Normal:** **Sengaja tidak ditampilkan** agar kartu tidak berantakan.
- **Rendah:** Tampilkan ikon panah bawah dengan teks warna `--text-muted`.

```html
<!-- Prioritas Tinggi -->
<span class="inline-flex items-center gap-1 rounded-[6px] bg-danger-soft px-1.5 py-0.5 text-[11px] font-medium text-danger">
  <svg class="h-3 w-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7"/></svg>
  <span>Tinggi</span>
</span>

<!-- Prioritas Rendah -->
<span class="inline-flex items-center gap-1 text-[11px] font-medium text-text-muted">
  <svg class="h-3 w-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M19 12l-7 7-7-7"/></svg>
  <span>Rendah</span>
</span>
```

---

## 4. Chip Status Tenggat Waktu (Due Date Chip)

Memiliki 4 kondisi dinamis berdasarkan sisa waktu:

```html
<!-- 1. Kondisi Normal (Masih > 24 Jam) -->
<span class="inline-flex items-center gap-1 rounded-[6px] bg-surface-3 px-1.5 py-0.5 text-[11px] font-medium text-text-muted">
  <svg class="h-3 w-3 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
  <span>10 Nov</span>
</span>

<!-- 2. Mendekati Tenggat (< 24 Jam) -->
<span class="inline-flex items-center gap-1 rounded-[6px] bg-warning-soft px-1.5 py-0.5 text-[11px] font-medium text-warning">
  <svg class="h-3 w-3 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
  <span>Besok 12:00</span>
</span>

<!-- 3. Terlambat (Overdue) -->
<span class="inline-flex items-center gap-1 rounded-[6px] bg-danger-soft px-1.5 py-0.5 text-[11px] font-medium text-danger">
  <svg class="h-3 w-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  <span>Terlambat</span>
</span>

<!-- 4. Tugas Selesai (Di Kolom Done) -->
<span class="inline-flex items-center gap-1 rounded-[6px] bg-success-soft px-1.5 py-0.5 text-[11px] font-medium text-success">
  <svg class="h-3 w-3 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
  <span>Selesai</span>
</span>
```

---

## 5. Avatar Inisial Pengguna (Avatar Inisial)

- Berbentuk lingkaran 24 px (`h-6 w-6 rounded-full`), font-weight 600, ukuran teks 10 px.
- Warna dipilih secara deterministik dari 8 warna palet label berdasarkan hash nama pengguna (misal algoritma CRC32 atau modulo nama), sehingga orang yang sama selalu memiliki warna inisial yang sama di seluruh projek.

```html
<!-- Contoh Avatar Dua Huruf Inisial -->
<div 
  class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-[10px] font-semibold text-[#C4B5FD] ring-2 ring-surface-2" 
  title="Uum Rachman"
>
  UR
</div>
```

