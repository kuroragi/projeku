# Spesifikasi Token Desain Visual (Design Tokens)

Seluruh komponen UI wajib merujuk pada token di bawah ini. Jangan pernah menggunakan nilai warna heksadesimal langsung di dalam komponen Blade atau kelas HTML inline.

---

## 1. Token Warna (Color Tokens)

### A. Permukaan & Latar (Surfaces & Backgrounds)
| Token | Heksadesimal | Peruntukan / Penggunaan |
| :--- | :--- | :--- |
| `--color-bg` | `#0F1115` | Latar belakang halaman aplikasi |
| `--color-surface-1` | `#151821` | Kolom board kanban, panel samping, bilah atas (top navbar) |
| `--color-surface-2` | `#1B1F2A` | Kartu tugas/kanban, kartu projek, jendela dialog, menu dropdown |
| `--color-surface-3` | `#232836` | Bidang input, tombol sekunder, kartu keadaan hover |

### B. Garis Tepi (Borders) — Revisi 1
| Token | Heksadesimal | Peruntukan / Penggunaan | Kontras Relatif |
| :--- | :--- | :--- | :--- |
| `--color-border` | `#434B61` | Garis tepi kartu, kolom (1px), pemisah kontainer & tabel | ~1.9:1 terhadap `--surface-2` |
| `--color-border-strong`| `#6B7388` | Input, select, textarea, tombol sekunder, kotak centang, hover, dialog & menu | ~3.1:1 terhadap `--surface-3` |

### C. Tipografi & Teks (Text & Typography)
| Token | Heksadesimal | Peruntukan / Penggunaan | Kontras Relatif |
| :--- | :--- | :--- | :--- |
| `--color-text` | `#E6E8EE` | Teks utama, judul, teks kartu aktif | ~13:1 |
| `--color-text-muted` | `#A3AABD` | Teks sekunder, label judul kolom, placeholder | ~7:1 |
| `--color-text-faint` | `#8089A0` | Stempel waktu (timestamp), bantuan kecil, ikon pasif | ~4.7:1 |

### D. Aksen (Accent)
| Token | Heksadesimal | Peruntukan / Penggunaan |
| :--- | :--- | :--- |
| `--color-accent` | `#8B7CF6` | Tombol utama, tautan aktif, garis tab aktif, fokus cincin |
| `--color-accent-hover`| `#A094F8` | Keadaan hover tombol utama atau tautan |
| `--color-on-accent` | `#0F1115` | Teks di atas latar belakang aksen (~5.7:1) |
| `--color-accent-soft` | `rgba(139, 124, 246, 0.16)` | Latar belakang chip aksen, sorotan baris terpilih |

### E. Status & Indikator (Status Colors)
Setiap status memiliki varian solid (untuk ikon dan teks) serta varian lembut / `-soft` beropasitas 16% (untuk latar chip/badge):
| Status | Solid (Teks & Ikon) | Soft (Latar Belakang Badge) | Makna / Konteks |
| :--- | :--- | :--- | :--- |
| **Success** | `--color-success: #4ADE80` | `--color-success-soft: rgba(74, 222, 128, 0.16)` | Selesai, sukses, checklist penuh |
| **Warning** | `--color-warning: #FBBF24` | `--color-warning-soft: rgba(251, 191, 36, 0.16)` | Tenggat < 24 jam, peringatan bentrok |
| **Danger** | `--color-danger: #F87171` | `--color-danger-soft: rgba(248, 113, 113, 0.16)` | Terlambat (overdue), galat validasi, hapus |
| **Info** | `--color-info: #60A5FA` | `--color-info-soft: rgba(96, 165, 250, 0.16)` | Informasi sistem, petunjuk netral |

---

## 2. Palet Warna Label Projek (8 Warna)

Digunakan untuk label kustom pada kartu projek dan inisial avatar pengguna. Warna latar chip otomatis menggunakan transparansi 16% dari warna teksnya:

| Nama Label | Warna Teks (Solid) | Warna Latar Chip (Soft 16%) |
| :--- | :--- | :--- |
| **Ungu** | `#C4B5FD` | `rgba(196, 181, 253, 0.16)` |
| **Biru** | `#93C5FD` | `rgba(147, 197, 253, 0.16)` |
| **Tosca** | `#5EEAD4` | `rgba(94, 234, 212, 0.16)` |
| **Hijau** | `#86EFAC` | `rgba(134, 239, 172, 0.16)` |
| **Oranye** | `#FDBA74` | `rgba(253, 186, 116, 0.16)` |
| **Mawar (Rose)**| `#FDA4AF` | `rgba(253, 164, 175, 0.16)` |
| **Kuning** | `#FCD34D` | `rgba(252, 211, 77, 0.16)` |
| **Abu** | `#CBD5E1` | `rgba(203, 213, 225, 0.16)` |

---

## 3. Tipografi & Ukuran Huruf

Hanya menggunakan font sistem bebas latensi jaringan:
```css
--font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
--font-mono: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
```

### Skala Ukuran Huruf:
- **Judul Halaman (H1):** `28px` (`text-[28px]` atau `text-2xl`), font-weight 600 (`font-semibold`).
- **Judul Bagian (H2):** `20px` (`text-[20px]` atau `text-xl`), font-weight 600 (`font-semibold`).
- **Teks Dasar (Body):** `15px` (`text-[15px]`), font-weight 400 (`font-normal`), line-height 1.5.
- **Teks Kartu & Tabel:** `14px` (`text-[14px]` atau `text-sm`), font-weight 500 (`font-medium`).
- **Label Kolom / Subtitle:** `13px` (`text-[13px]`), font-weight 500 (`font-medium`).
- **Keterangan / Bantuan / Chip:** `11px - 12px` (`text-[11px]` / `text-xs`), font-weight 500.

---

## 4. Sudut Membulat (Border Radius) & Grid Ukuran

Semua ukuran dan margin wajib menggunakan kelipatan **4 px**:
- **6 px (`rounded-[6px]`):** Chip label, badge status, tag tenggat.
- **8 px (`rounded-lg` atau `rounded-[8px]`):** Kartu kanban, kartu projek, input form, tombol, thumbnail berkas.
- **12 px (`rounded-xl` atau `rounded-[12px]`):** Kolom board kanban, panel samping detail kartu, modal dialog konfirmasi.
- **Lingkaran Penuh (`rounded-full`):** Avatar profil anggota (ukuran standar 24 px atau 32 px).

---

## 5. Cetak ke PDF / @media print

Saat halaman presentasi atau kartu dicetak ke PDF, token otomatis dibalik ke tema terang:
```css
@media print {
  :root {
    color-scheme: light;
    --color-bg: #FFFFFF;
    --color-surface-1: #FFFFFF;
    --color-surface-2: #F8FAFC;
    --color-surface-3: #F1F5F9;
    --color-border: #E2E8F0;
    --color-text: #111827;
    --color-text-muted: #4B5563;
    --color-accent: #4F46E5;
  }
  @page { size: A4 landscape; margin: 12mm; }
  .no-print { display: none !important; }
  .slide { break-after: page; break-inside: avoid; }
  .slide img { print-color-adjust: exact; }
}
```

