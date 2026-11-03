# Panduan Desain & Template UX (projeku)

Dokumen ini merupakan standar acuan desain antarmuka (UI) dan pengalaman pengguna (UX) untuk aplikasi **projeku**. Seluruh tampilan antarmuka dan komponen Blade wajib mengikuti aturan dan template di direktori `/docs/ux/` ini agar gaya visual konsisten, rapi, dan mudah dipelihara.

---

## 1. Prinsip Utama Desain

1. **Satu Tema Gelap (Dark Theme Only):**
   - MVP hanya mengimplementasikan tema gelap berkualitas tinggi tanpa tombol switch dark/light.
   - Latar tidak menggunakan hitam murni (#000000) agar tidak melelahkan mata dan memberikan pemisahan hierarki permukaan yang jelas.
   - Pengecualian: **Cetak ke PDF / Print** selalu menggunakan tema terang (light) agar terbaca jelas di atas kertas dan hemat tinta printer.

2. **Hierarki Permukaan (Layered Surfaces) & Garis Tepi:**
   - Kedalaman elemen diciptakan melalui tingkat kecerahan permukaan (`--surface-1`, `--surface-2`, `--surface-3`) dan garis tepi (`--border`, `--border-strong`), bukan dengan bayangan tebal (karena bayangan sulit terlihat di tema gelap).
   - Bayangan hanya digunakan pada elemen yang melayang bebas (*floating* / *modal* / *dragged card* / *side panel*).

3. **Aturan Keras Token Warna (No Literal Hex):**
   - **Dilarang keras menuliskan warna heksadesimal literal (`#...`) di dalam komponen Blade atau tampilan.**
   - Semua warna wajib bersumber dari token warna CSS yang telah didefinisikan pada `resources/css/app.css` (Tailwind CSS 4).
   - Jika di masa mendatang ingin menambahkan tema terang atau mengganti warna aksen, cukup ubah satu berkas token tanpa perlu menyentuh komponen Blade.

4. **Satu Warna Aksen yang Konsisten:**
   - Aksen utama menggunakan warna ungu-biru lembut (`--accent: #8B7CF6`).
   - Dipakai untuk fokus papan ketik, tombol tindakan utama, tab aktif, dan progress checklist.

5. **Warna Status Selalu Disertai Ikon / Teks:**
   - Status (Berhasil, Peringatan, Bahaya/Terlambat, Info) tidak boleh hanya mengandalkan warna semata. Wajib selalu didampingi ikon Lucide atau label teks demi aksesibilitas.
   - Setiap warna status memiliki varian lembut (`-soft`) dengan transparansi 16% sebagai latar belakang chip/badge.

6. **Font Sistem & Ikon Inline:**
   - Menggunakan font sistem bawaan OS (tanpa Google Fonts atau webfont eksternal) untuk memangkas waktu pemuatan di jaringan seluler.
   - Ikon menggunakan **Lucide SVG inline** (lewat komponen Blade), goresan garis (*stroke*) 1.5 px, ukuran standar 16 px atau 20 px, dengan warna mengikuti teks (`currentColor`).

7. **Aksesibilitas & Standar Sentuh Ponsel:**
   - Kontras teks minimal 4.5:1.
   - Indikator fokus papan ketik (*focus ring*) selalu tampak jelas: outline aksen 2 px dengan offset 2 px.
   - Target sentuh di perangkat ponsel minimal 44 px.

---

## 2. Struktur Direktori `/docs/ux/`

```text
docs/ux/
├── README.md                      # Panduan utama & prinsip desain UX (berkas ini)
├── tokens.md                      # Spesifikasi variabel token warna, tipografi, & radius
└── components/
    ├── button.md                  # Template tombol (Primary, Secondary, Ghost, Danger)
    ├── card.md                    # Template kartu Kanban, kartu projek, & kartu dokumen
    ├── form.md                    # Template input formulir, textarea, dropdown, & validasi
    ├── list.md                    # Template checklist progres, daftar anggota, daftar sampah
    ├── badge-and-chip.md          # Template label projek, prioritas, & tenggat waktu
    ├── board-column.md            # Template kolom kanban board & interaksi drag
    ├── side-panel.md              # Template panel samping detail kartu & lembar seluler
    ├── modal-and-dialog.md        # Template dialog konfirmasi, modal, & popover menu
    ├── navigation-and-tabs.md     # Template bilah atas, breadcrumb, tab, & avatar anggota
    └── empty-state-and-toast.md   # Template keadaan kosong & pesan notifikasi toast
```

---

## 3. Komponen Blade Standar

Semua elemen di atas diwujudkan dalam komponen Blade di `resources/views/components/` sehingga perubahan gaya terisolasi pada satu berkas:
- `<x-button ... />`
- `<x-card ... />`
- `<x-input ... />`
- `<x-chip ... />`
- `<x-icon ... />`
- `<x-modal ... />`
- `<x-side-panel ... />`

