# Template Komponen: Form & Input (Formulir)

Dokumen ini mendefinisikan template elemen masukan data, formulir, dan penanganan status galat (*error validation*).

---

## 1. Spesifikasi Standar Bidang Input (Text Input)

- **Latar Belakang:** `--color-surface-3` (`#232836`)
- **Garis Tepi (Border):** `1px solid --color-border` (`#2A3040`)
- **Sudut Membulat:** `8px` (`rounded-[8px]`)
- **Tinggi Standar:** `36px` (`h-9`), ukuran kecil `32px` (`h-8`)
- **Padding:** `px-3 text-sm text-text`
- **Placeholder:** Warna `--color-text-faint` (`#8089A0`)
- **Fokus Papan Ketik:** Garis tepi menjadi `--color-accent` dengan cincin aksen 2 px beropasitas 40% (`focus:border-accent focus:ring-2 focus:ring-accent/40 focus:outline-none`).
- **Status Galat (Error):** Garis tepi `--color-danger` didampingi pesan teks galat dan ikon Lucide AlertCircle di bawah bidang input.

---

## 2. Struktur HTML / Blade Komponen Input

### A. Input Teks Biasa dengan Label & Bantuan
```html
<div class="flex flex-col gap-1.5">
  <label for="project_name" class="text-xs font-medium text-text">
    Nama Projek <span class="text-danger">*</span>
  </label>
  
  <input 
    type="text" 
    id="project_name" 
    name="name" 
    value="Manajemen Projek Internal"
    placeholder="Masukkan nama projek..."
    class="h-9 w-full rounded-[8px] border border-border bg-surface-3 px-3 text-sm text-text placeholder-text-faint transition focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40"
    required
  />

  <p class="text-[11px] text-text-faint">
    Nama unik untuk mengidentifikasi workspace kerja projek.
  </p>
</div>
```

### B. Input Keadaan Galat (Validation Error)
```html
<div class="flex flex-col gap-1.5">
  <label for="email" class="text-xs font-medium text-text">
    Alamat Email
  </label>
  
  <input 
    type="email" 
    id="email" 
    name="email" 
    value="email-tidak-valid"
    class="h-9 w-full rounded-[8px] border border-danger bg-surface-3 px-3 text-sm text-text focus:border-danger focus:outline-none focus:ring-2 focus:ring-danger/40"
    aria-invalid="true"
    aria-describedby="email-error"
  />

  <!-- Pesan Galat dengan Ikon Wajib -->
  <div id="email-error" class="flex items-center gap-1.5 text-xs text-danger">
    <svg class="h-3.5 w-3.5 shrink-0 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
    </svg>
    <span>Format alamat email tidak valid atau sudah digunakan.</span>
  </div>
</div>
```

---

## 3. Textarea (Area Teks Panjang)

Digunakan untuk deskripsi kartu dan catatan:
```html
<div class="flex flex-col gap-1.5">
  <label for="card_desc" class="text-xs font-medium text-text">Deskripsi</label>
  
  <textarea 
    id="card_desc" 
    name="description" 
    rows="4" 
    placeholder="Tuliskan detail tugas dalam teks biasa (tanpa tag HTML)..."
    class="w-full rounded-[8px] border border-border bg-surface-3 p-3 text-sm text-text placeholder-text-faint transition focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40 resize-y"
  ></textarea>
  <span class="text-[11px] text-text-faint">Mendukung format baris teks biasa yang aman dari XSS.</span>
</div>
```

---

## 4. Dropdown Select

```html
<div class="flex flex-col gap-1.5">
  <label for="priority" class="text-xs font-medium text-text">Prioritas</label>
  
  <div class="relative">
    <select 
      id="priority" 
      name="priority" 
      class="h-9 w-full appearance-none rounded-[8px] border border-border bg-surface-3 pl-3 pr-8 text-sm text-text focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40"
    >
      <option value="normal" selected>Normal</option>
      <option value="high">Tinggi (Mendesak)</option>
      <option value="low">Rendah</option>
    </select>
    
    <!-- Chevron Icon Lucide -->
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-text-muted">
      <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </div>
  </div>
</div>
```

---

## 5. Input Pencarian (Search Input)

```html
<div class="relative w-full max-w-xs">
  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-text-faint">
    <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
    </svg>
  </div>
  
  <input 
    type="search" 
    placeholder="Cari kartu atau dokumen..." 
    class="h-8 w-full rounded-[8px] border border-border bg-surface-3 pl-8 pr-3 text-xs text-text placeholder-text-faint transition focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
  />
</div>
```

---

## 6. Saklar Toggle (Switch) & Kotak Centang (Checkbox)

```html
<!-- Saklar Notifikasi Email -->
<label class="flex items-center justify-between cursor-pointer">
  <div class="flex flex-col">
    <span class="text-xs font-medium text-text">Kirim Pengingat Email</span>
    <span class="text-[11px] text-text-faint">Terima ringkasan harian kartu yang mendekati tenggat.</span>
  </div>
  
  <div class="relative inline-flex items-center cursor-pointer">
    <input type="checkbox" name="notify_email" value="1" checked class="sr-only peer">
    <div class="w-10 h-5 bg-surface-3 border border-border peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-accent peer-checked:border-accent"></div>
  </div>
</label>
```

