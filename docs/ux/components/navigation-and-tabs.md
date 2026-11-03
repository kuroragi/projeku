# Template Komponen: Navigation & Tabs (Bilah Navigasi & Tab)

Dokumen ini mendefinisikan template bilah atas (*top bar*), navigasi tab projek, dan deretan avatar anggota.

---

## 1. Spesifikasi Teknis Navigasi & Tab

- **Bilah Atas (Top Navbar):** Tinggi `56px` (`h-14`), latar `--color-surface-1` (`#151821`), garis tepi bawah `1px solid --color-border`.
- **Tab Projek:**
  - **Tab Aktif:** Garis bawah setebal 2 px berwarna aksen (`border-b-2 border-accent`), teks warna utama `--color-text`, font-weight 500.
  - **Tab Tidak Aktif:** Teks sekunder `--color-text-muted`, hover teks `--color-text`.
  - Transisi mulus tanpa loncatan layout.

---

## 2. Struktur HTML / Blade Bilah Atas Global

```html
<header class="flex h-14 w-full items-center justify-between border-b border-border bg-surface-1 px-4 sm:px-6">
  <!-- Sisi Kiri: Logo & Pemilih Projek -->
  <div class="flex items-center gap-4">
    <a href="/projects" class="flex items-center gap-2 font-semibold text-text hover:text-accent transition">
      <span class="inline-flex h-7 w-7 items-center justify-center rounded-[6px] bg-accent text-on-accent text-xs font-bold">
        P
      </span>
      <span class="text-sm tracking-wide">projeku</span>
    </a>

    <!-- Pemisah Vertikal -->
    <span class="h-4 w-[1px] bg-border"></span>

    <!-- Breadcrumb / Nama Projek Aktif -->
    <div class="flex items-center gap-2 text-xs">
      <span class="text-text-muted">Projek:</span>
      <span class="font-medium text-text">Sistem Manajemen Internal</span>
    </div>
  </div>

  <!-- Sisi Kanan: Pencarian, Notifikasi, Avatar Pengguna -->
  <div class="flex items-center gap-3">
    <!-- Input Pencarian Cepat (Desktop) -->
    <div class="hidden sm:block">
      <!-- Search Input Component (lihat /docs/ux/components/form.md) -->
    </div>

    <!-- Menu Profil Pengguna -->
    <div class="relative" x-data="{ userMenu: false }">
      <button 
        @click="userMenu = !userMenu" 
        class="flex items-center gap-2 rounded-full p-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
      >
        <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-xs font-semibold text-[#C4B5FD]">
          UR
        </div>
      </button>

      <!-- Dropdown Akun Pengguna -->
      <div 
        x-show="userMenu" 
        @click.outside="userMenu = false" 
        class="absolute right-0 z-30 mt-2 w-48 rounded-[8px] border border-border-strong bg-surface-2 p-1 shadow-xl"
      >
        <div class="px-2.5 py-2 border-b border-border">
          <p class="text-xs font-medium text-text">Uum Rachman</p>
          <p class="text-[11px] text-text-faint truncate">uum@example.com</p>
        </div>
        <a href="/profile" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-text hover:bg-surface-3 transition">
          Pengaturan Akun
        </a>
        <form method="POST" action="/logout">
          <button type="submit" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-danger hover:bg-danger-soft transition">
            Keluar (Logout)
          </button>
        </form>
      </div>
    </div>
  </div>
</header>
```

---

## 3. Struktur Header Projek & Navigasi Tab

```html
<div class="border-b border-border bg-surface-1 px-4 sm:px-6 pt-5 pb-0">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <!-- Judul Projek & Avatar Tim -->
    <div>
      <div class="flex items-center gap-3">
        <h1 class="text-[24px] sm:text-[28px] font-semibold text-text leading-tight">
          Redesain Portal Klien KDS
        </h1>
        <span class="rounded-[6px] bg-accent-soft px-2 py-0.5 text-xs font-medium text-accent">
          Aktif
        </span>
      </div>
      <p class="mt-1 text-xs text-text-muted">
        Ruang kerja terpadu untuk perencanaan desain, papan tugas, dan berkas aset.
      </p>
    </div>

    <!-- Avatar Tim Projek -->
    <div class="flex items-center gap-2">
      <div class="flex -space-x-2 overflow-hidden">
        <div class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-xs font-semibold text-[#C4B5FD] ring-2 ring-surface-1" title="Uum Rachman (Owner)">UR</div>
        <div class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#93C5FD]/20 text-xs font-semibold text-[#93C5FD] ring-2 ring-surface-1" title="Budi Santoso (Editor)">BS</div>
        <div class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#5EEAD4]/20 text-xs font-semibold text-[#5EEAD4] ring-2 ring-surface-1" title="Siti Aminah (Viewer)">SA</div>
      </div>
      
      <button class="inline-flex h-7 items-center rounded-[6px] border border-border bg-surface-3 px-2 text-xs text-text-muted hover:border-border-strong hover:text-text transition">
        + Anggota
      </button>
    </div>
  </div>

  <!-- Baris Tab Navigasi 4 Bagian -->
  <nav class="mt-6 flex space-x-6">
    <!-- Tab 1: Board (Aktif) -->
    <a 
      href="/projects/1/board" 
      class="flex items-center gap-2 border-b-2 border-accent pb-3 text-xs font-medium text-text transition"
    >
      <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
      <span>Board</span>
      <span class="rounded-full bg-surface-3 px-1.5 py-0.2 text-[10px] text-text-muted">18</span>
    </a>

    <!-- Tab 2: Halaman Perencanaan -->
    <a 
      href="/projects/1/planning" 
      class="flex items-center gap-2 border-b-2 border-transparent pb-3 text-xs font-medium text-text-muted hover:text-text transition"
    >
      <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      <span>Perencanaan</span>
      <span class="rounded-full bg-surface-3 px-1.5 py-0.2 text-[10px] text-text-muted">4</span>
    </a>

    <!-- Tab 3: Berkas -->
    <a 
      href="/projects/1/files" 
      class="flex items-center gap-2 border-b-2 border-transparent pb-3 text-xs font-medium text-text-muted hover:text-text transition"
    >
      <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      <span>Berkas</span>
    </a>

    <!-- Tab 4: Pengaturan (Khusus Pemilik / Owner) -->
    <a 
      href="/projects/1/settings" 
      class="flex items-center gap-2 border-b-2 border-transparent pb-3 text-xs font-medium text-text-muted hover:text-text transition"
    >
      <svg class="h-4 w-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      <span>Pengaturan</span>
    </a>
  </nav>
</div>
```

