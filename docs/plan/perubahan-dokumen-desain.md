Panduan Utama: Aplikasi Manajemen Projek (projeku)
Oct 30, 2026 · @uum
Ringkasan
Aplikasi ini adalah alat manajemen projek pribadi untuk satu pemilik dan tim kecil: kanban untuk eksekusi, halaman perencanaan bergambar, dan mode presentasi, dibangun dengan Laravel di Hostinger shared hosting.
Pemakai: pemilik dan anggota tim kecil. Tidak ada pendaftaran terbuka dan tidak ada akses klien. Setiap orang hanya melihat projek tempat ia diberi peran.
Tujuan: satu tempat untuk board, rancangan bergambar, dan presentasi yang lahir dari rancangan itu, menggantikan kanban saja.
Batasan keras: tanpa proses yang berjalan terus-menerus (tanpa WebSocket), gambar maksimal 10 MB, penyimpanan di server sendiri untuk sementara, dan kode harus mudah dipindah ke VPS.
Prinsip kerja: pekerjaan diurutkan berdasarkan risiko, dan hal yang murah dipasang sekarang tetapi mahal diperbaiki nanti (hapus lunak, banyak penanggung jawab, project_id di setiap tabel) dimasukkan sejak awal.
Cara memakai dokumen ini: ini panduan utama dan satu-satunya acuan; ia menggantikan Dokumen Desain dan Spesifikasi Tahap 0 dan 1 yang terpisah. Bagian rancangan (keputusan, konsep, layar, gaya visual, akses, data, stack) dibaca lebih dulu, lalu bagian pelaksanaan (rencana MVP, Tahap 0, Tahap 1, urutan pengerjaan) dikerjakan berurutan.
Keputusan yang sudah disepakati
Semua keputusan di bawah sudah disetujui dalam diskusi desain; perubahan apa pun sebaiknya dibahas sebelum model data ditulis, karena itu yang paling mahal diubah.
Topik
Keputusan
Alasan
Struktur
Workspace → Projek → Board, Halaman perencanaan, Berkas
Memisahkan eksekusi dari perencanaan agar kartu tidak penuh lampiran
Presentasi
Bukan editor slide; judul level 1 memulai slide baru, gambar contain
Nilai besar, biaya kecil, hasil mudah diprediksi
Halaman perencanaan
Empat blok: judul, teks, gambar plus keterangan, daftar
Editor rich-text penuh jarang terpakai dan mahal
Deskripsi kartu
Teks biasa
Tanpa penyaringan HTML, tanpa risiko XSS di bagian itu
Kolom board
Bebas ditambah, diganti nama, diurutkan; is_done menentukan progres
Progres tidak rusak saat kolom diganti nama
Checklist
Banyak checklist per kartu, masing-masing berjudul
Lebih sulit ditambah setelah data ada
Berbagi
Per projek, tiga peran (owner, editor, viewer)
Izin per halaman memperbesar risiko celah
Otorisasi
spatie/laravel-permission mode teams, project_id sebagai team
Pilihan pemilik; cocok untuk peran per projek
Login
Laravel Fortify dengan view Blade sendiri, tanpa registrasi; undangan dan reset lewat email
Admin tidak pernah tahu kata sandi orang lain
Stack
Laravel + Blade + Alpine.js + SortableJS
Tanpa build Node di server, tanpa SPA
Berkas
Di server sendiri, maksimal 10 MB per gambar, disajikan lewat controller
Storage cukup besar; pindah ke R2 nanti lewat .env
Pengingat
Email dulu, satu email per pengguna berisi semua kartu jatuh tempo
Lebih aman terhadap batas kirim, lebih ramah penerima
Kalender
Ditunda: tombol Tambah ke kalender dan feed setelah MVP
Google tidak menjamin alarm dari .ics
Penanggung jawab
Lebih dari satu orang per kartu
Menambah nanti lebih mahal
Hapus
Soft delete dan tempat sampah 30 hari
Tidak ada undo untuk gambar yang hanya ada di satu server
Tampilan ponsel
Satu kolom per layar, pindah kartu lewat menu
Drag di layar sentuh bentrok dengan gulir
Urutan kerja
Berdasarkan risiko: deploy kosong dulu, akses, board, pengingat, perencanaan
Kegagalan hosting harus ketahuan paling awal
Tema visual
Gelap saja, dari token warna CSS; cetak PDF selalu terang
Separuh pekerjaan desain dan uji; tema terang kelak cukup satu set token; browser tidak mencetak latar gelap
Styling
Tailwind CSS 4, komponen Blade, ikon Lucide inline, font sistem
Dibangun di lokal tanpa Node di server; gaya komponen di satu tempat
Tempat kerja
Kode ditulis dan diuji di lokal; server hanya menerima lewat git pull dan deploy.sh
Satu lingkungan tanpa staging; server tidak pernah diedit langsung
Simpan detail kartu
Simpan eksplisit untuk isian teks dan field; aksi sekali klik (centang checklist) langsung tersimpan
Peringatan konflik butuh satu momen simpan; mengubah tenggat membuat ulang pengingat email dan tidak boleh terjadi saat mengetik
Konsep dan struktur
Aplikasi dibangun dari satu hierarki: workspace berisi projek, dan setiap projek punya tiga bagian yang saling terhubung.
Board: kanban untuk eksekusi, dengan kolom yang bisa diatur sendiri.
Halaman perencanaan: dokumen berisi blok-blok (gambar, catatan, rancangan) yang bisa dipresentasikan.
Berkas: galeri semua gambar dan lampiran projek, dipakai bersama oleh kartu dan halaman perencanaan.
Kartu bisa ditautkan ke satu halaman perencanaan, misalnya kartu "Buat modul laporan" membuka rancangan UI-nya. Kartu dan halaman terikat ke satu projek dan tidak lintas projek, karena lintas projek menambah kerumitan izin dan pencarian tanpa kebutuhan yang jelas.
Alur pemakaian: ide masuk ke halaman perencanaan, dipecah menjadi kartu di board, kartu dikerjakan dan digeser, lalu halaman perencanaan dipresentasikan ke tim.
Pemakai: tidak ada tamu atau klien. Semua orang yang masuk adalah pengguna terdaftar yang diberi peran di projek tertentu.
Layar dan alur
Ada tujuh layar. Tiga yang paling menentukan (board, detail kartu, perencanaan dan presentasi) sudah diwireframe dan disetujui, dengan gambar contain pada slide.
Layar
Isi utama
Catatan desain
Daftar projek
Kartu projek dengan status, tenggat, progres
Hanya projek tempat pengguna punya peran; progres dari kartu di kolom is_done
Halaman projek
Ringkasan, tab Board / Perencanaan / Berkas, avatar anggota
Avatar menunjukkan siapa yang punya akses
Board
Kolom bebas, kartu bisa diseret
Kartu ringan: judul, label, prioritas, tenggat, progres checklist; tanpa gambar
Detail kartu
Panel samping: deskripsi teks, checklist (banyak), label, tenggat, penanggung jawab (banyak), lampiran, tautan ke halaman perencanaan
Panel, bukan modal, supaya board tetap terlihat; tautan perencanaan di bagian bawah
Halaman perencanaan
Blok judul, teks, gambar plus keterangan, daftar; disusun ulang dengan seret
Penanda "judul memulai slide baru" terlihat langsung di editor
Mode presentasi
Layar penuh, navigasi slide, cetak ke PDF
Gambar contain; CSS @media print dengan page-break per slide
Pengaturan
Anggota dan peran, label, kolom bawaan, preferensi email
Aturan pemilik terakhir berlaku di sini
Filter board: berdasarkan penanggung jawab ("kartu saya"), label, dan prioritas, dijalankan di browser sehingga tidak membebani server.
Tampilan ponsel: satu kolom per layar dengan geser mendatar (scroll-snap). Kartu dipindah lewat menu "Pindahkan ke…", bukan seret, karena seret bentrok dengan gulir halaman. Drag tetap ada di desktop.
Pembaruan: browser menanyakan perubahan secara berkala (sekitar 10 detik), berhenti saat tab tidak terlihat, dan melambat saat lama tanpa perubahan. Jika kartu berubah sejak dibuka, muncul peringatan sebelum menyimpan.
Perilaku: ubah nama kolom dan menu kolom
Nama kolom bisa diubah langsung di judulnya (klik dua kali atau ikon pensil), dan menu tiga titik di kolom memuat tiga aksi: ubah nama, tandai sebagai kolom selesai, dan hapus kolom.
Aksi
Perilaku
Izin
Ubah nama
Klik dua kali judul membuka isian. Enter atau klik di luar menyimpan; Esc membatalkan. Nama kosong ditolak, maksimal 60 karakter
board.edit
Tandai sebagai kolom selesai
Mengubah is_done; progres projek dihitung ulang dari kartu di kolom bertanda
board.edit
Hapus kolom
Ditolak dengan pesan "Pindahkan kartunya dulu" bila kolom masih berisi kartu
board.edit
Rute: PATCH /projects/{project}/columns/{column} dengan isi { "name": "..." }; respons berisi nama yang tersimpan.
public function update(Request $request, Project $project, Column $column){    ProjectAccess::authorize($project, 'board.edit');    abort_unless($column->project_id === $project->id, 404);   // jangan dilewati    $data = $request->validate(['name' => ['required', 'string', 'max:60']]);    $column->update(['name' => trim($data['name'])]);    return response()->json(['name' => $column->name]);}
Alpine.data('columnTitle', (name, url) => ({  name, draft: name, editing: false, saving: false, error: null,  start() { this.draft = this.name; this.editing = true; this.$nextTick(() => this.$refs.input.select()); },  cancel() { this.editing = false; this.error = null; },  async save() {    if (!this.editing || this.saving) return;    const v = this.draft.trim();    if (v === this.name) return this.cancel();    if (!v) { this.error = 'Nama kolom wajib diisi'; return; }    this.saving = true;    const res = await fetch(url, { method: 'PATCH', headers: csrfJsonHeaders(), body: JSON.stringify({ name: v }) });    this.saving = false;    if (res.ok) { this.name = (await res.json()).name; this.cancel(); }    else { this.error = (await res.json().catch(() => ({}))).message ?? 'Gagal menyimpan. Coba lagi.'; }  },}));
Dua jebakan:
Pemeriksaan project_id. Tanpa baris abort_unless($column->project_id === $project->id, 404), seorang editor di P1 bisa mengubah kolom milik P2 lewat URL P1 dengan id kolom P2. Itu kebocoran antarprojek yang sama jenisnya dengan yang dijaga uji akses. Alternatifnya Route::scopeBindings(). Aturan ini berlaku untuk semua rute bersarang di bawah projek.
Konflik dengan SortableJS. Bila judul kolom berada di dalam elemen yang bisa diseret, mengetik di isian bisa memicu seret. Beri handle pada kolom, atau filter: 'input, button' dengan preventOnFilter: false.
Tes yang ditambahkan:
Viewer mengubah nama kolom: 403. Non-anggota: 404.
Kolom milik projek lain lewat URL projek sendiri: 404.
Nama kosong atau lebih dari 60 karakter: 422.
Menghapus kolom yang masih berisi kartu: ditolak, kartunya tidak berubah.
Perilaku: simpan perubahan di detail kartu
Detail kartu memakai simpan eksplisit untuk isian teks dan field, dan simpan langsung untuk aksi sekali klik.
Jenis perubahan
Penyimpanan
Judul, deskripsi, tenggat, prioritas, penanggung jawab, label
Eksplisit: lewat tombol Simpan perubahan
Centang checklist, pindah kolom, hapus lampiran
Langsung tersimpan saat diklik
Mengapa tidak simpan otomatis untuk isian teks:
Peringatan konflik ("kartu diubah sejak kamu membukanya") membutuhkan satu momen simpan yang jelas.
Mengubah tenggat membuat ulang pengingat email, dan itu tidak boleh terjadi sambil mengetik.
Pengguna meminta tombol simpan yang eksplisit.
Perilaku antarmuka:
Bilah di bawah panel muncul hanya saat ada perubahan: "Ada perubahan yang belum disimpan" dengan tombol Batal dan Simpan perubahan.
Ctrl atau Cmd+S menyimpan.
Menutup panel atau meninggalkan halaman saat ada perubahan meminta konfirmasi.
Setelah berhasil, kartu di board ikut diperbarui lewat peristiwa card-saved, tanpa memuat ulang halaman.
Alpine.data('cardForm', (initial, url) => {  const { updated_at, ...fields } = initial;  return {    form: structuredClone(fields), base: structuredClone(fields), version: updated_at,    saving: false, errors: {}, conflict: false,    get dirty() { return JSON.stringify(this.form) !== JSON.stringify(this.base); },    reset() { this.form = structuredClone(this.base); this.errors = {}; this.conflict = false; },    async save() {      if (!this.dirty || this.saving) return;      this.saving = true; this.errors = {}; this.conflict = false;      const res = await fetch(url, { method: 'PATCH', headers: csrfJsonHeaders(),        body: JSON.stringify({ ...this.form, version: this.version }) });      this.saving = false;      if (res.ok) {        const card = await res.json();        this.base = structuredClone(this.form); this.version = card.updated_at;        window.dispatchEvent(new CustomEvent('card-saved', { detail: card }));        return;      }      if (res.status === 422) this.errors = (await res.json()).errors;      else if (res.status === 409) this.conflict = true;      else this.errors = { _: ['Gagal menyimpan. Coba lagi.'] };    },  };});
Sisi server (bagian yang paling rawan salah): semua perubahan dalam satu transaksi dengan kunci baris, pembanding versi untuk konflik, dan tabel penghubung selalu membawa project_id:
public function update(UpdateCardRequest $request, Project $project, Card $card){    ProjectAccess::authorize($project, 'board.edit');    abort_unless($card->project_id === $project->id, 404);    return DB::transaction(function () use ($request, $project, $card) {        $card = Card::whereKey($card->id)->lockForUpdate()->firstOrFail();        if ($card->updated_at->getTimestamp() !== Carbon::parse($request->version)->getTimestamp()) {            return response()->json(['message' => 'Kartu diubah orang lain sejak kamu membukanya.'], 409);        }        $card->update($request->safe()->only(['title', 'description', 'priority', 'due_at']));        $card->assignees()->sync(collect($request->assignee_ids ?? [])            ->mapWithKeys(fn ($id) => [$id => ['project_id' => $project->id]]));        $card->labels()->sync(collect($request->label_ids ?? [])            ->mapWithKeys(fn ($id) => [$id => ['project_id' => $project->id]]));        $this->reminders->rebuildFor($card);        return response()->json($card->fresh());    });}
Isian
Aturan validasi
title
Wajib, maksimal 200 karakter
description
Opsional, teks biasa
priority
Salah satu dari low, normal, high
due_at
Opsional, berupa tanggal dan jam
assignee_ids.*
Harus anggota projek itu (Rule::in($project->memberIds()))
label_ids.*
Harus label milik projek itu (Rule::exists('labels', 'id')->where('project_id', $project->id))
Tanpa aturan assignee_ids, id pengguna dari projek lain bisa dipasang sebagai penanggung jawab. Pembanding versi memakai updated_at dengan ketelitian detik; dua simpan dalam detik yang sama bisa lolos. Untuk tim kecil itu sangat jarang, tetapi bila ingin ketat, tambahkan kolom lock_version bilangan bulat.
Tes yang ditambahkan:
Simpan dengan version lama: 409, dan kartu tidak berubah.
Penanggung jawab non-anggota, atau label milik projek lain: 422.
Mengubah due_at membuat ulang pengingat; memindahkan kartu ke kolom selesai membatalkannya.
Viewer menyimpan: 403. Kartu projek lain lewat URL projek sendiri: 404.
Gaya visual: tema gelap
Aplikasi memakai satu tema gelap tanpa tombol terang atau gelap, dibangun dari token warna CSS sehingga tema terang bisa ditambah nanti tanpa menulis ulang komponen. Tata letak mengikuti wireframe yang sudah disetujui; bagian ini hanya menentukan warna, huruf, dan perilaku visualnya.
Keputusan
Pilihan
Alasan
Tema
Gelap saja untuk MVP
Separuh pekerjaan desain dan pengujian; token membuat tema terang murah ditambahkan kelak
Latar
Permukaan berlapis, bukan hitam murni
Hitam murni terlalu keras dan membuat kartu tidak terpisah dari latar
Kedalaman
Lewat warna permukaan dan garis tepi, bukan bayangan
Bayangan hampir tidak terlihat di latar gelap; bayangan hanya untuk elemen yang melayang
Aksen
Satu warna, ungu-biru lembut
Satu variabel; ganti dengan warna merek KDS bila sudah ditetapkan
Status
Warna selalu disertai ikon atau teks
Warna saja tidak terbaca oleh sebagian orang, dan menurun di layar ponsel yang redup
Huruf
Font sistem, tanpa font eksternal
Tanpa permintaan jaringan tambahan, cepat di jaringan seluler, tanpa loncatan tata letak
Cetak PDF
Selalu terang
Browser umumnya tidak mencetak latar gelap, sehingga teks terang menjadi tak terbaca di kertas
Token warna. Nilai kontras adalah hitungan perkiraan terhadap permukaan tempat teks itu dipakai, dan diperiksa ulang saat implementasi.
Token
Nilai
Dipakai untuk
Kontras
--bg
#0F1115
Latar halaman
--surface-1
#151821
Kolom board, panel samping, bilah atas
--surface-2
#1B1F2A
Kartu, dialog, menu
--surface-3
#232836
Input, tombol sekunder, keadaan hover
--border
#434B61
Tepi kartu dan kolom, pemisah
sekitar 1,9:1
--border-strong
#6B7388
Input, tombol sekunder, kotak centang, hover, elemen melayang
sekitar 3,1:1
--text
#E6E8EE
Teks utama
sekitar 13:1
--text-muted
#A3AABD
Teks sekunder, label kolom
sekitar 7:1
--text-faint
#8089A0
Stempel waktu, bantuan kecil
sekitar 4,7:1
--accent
#8B7CF6
Tombol utama, tautan, fokus, tab aktif
sekitar 5:1 sebagai teks
--accent-hover
#A094F8
Hover aksen
--on-accent
#0F1115
Teks di atas aksen
sekitar 5,7:1
--success
#4ADE80
Selesai, berhasil
--warning
#FBBF24
Tenggat dekat, peringatan
--danger
#F87171
Terlambat, galat, hapus
--info
#60A5FA
Informasi netral
Setiap warna status dan aksen punya varian lembut (-soft) dengan transparansi 16%, dipakai sebagai latar chip dan pesan.
Palet label projek (delapan warna; latar adalah warna teksnya pada transparansi 16%):
Nama
Teks
Nama
Teks
Ungu
#C4B5FD
Oranye
#FDBA74
Biru
#93C5FD
Mawar
#FDA4AF
Tosca
#5EEAD4
Kuning
#FCD34D
Hijau
#86EFAC
Abu
#CBD5E1
Berkas token (resources/css/app.css, Tailwind CSS 4):
@import "tailwindcss";@theme {  --color-bg: #0F1115;  --color-surface-1: #151821;  --color-surface-2: #1B1F2A;  --color-surface-3: #232836;  --color-border: #434B61;  --color-border-strong: #6B7388;  --color-text: #E6E8EE;  --color-text-muted: #A3AABD;  --color-text-faint: #8089A0;  --color-accent: #8B7CF6;  --color-accent-hover: #A094F8;  --color-on-accent: #0F1115;  --color-success: #4ADE80;  --color-warning: #FBBF24;  --color-danger: #F87171;  --color-info: #60A5FA;  --font-sans: ui-sans-serif, system-ui, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;  --font-mono: ui-monospace, "SF Mono", Menlo, Consolas, monospace;}:root { color-scheme: dark; }
Aturan keras: tidak ada warna heksa literal di luar berkas token ini. Komponen hanya memakai token, sehingga mengganti aksen atau menambah tema terang cukup mengubah satu berkas.
Gaya visual: komponen
Setiap komponen berikut menjadi satu komponen Blade, jadi gayanya diubah di satu tempat. Ukuran memakai kelipatan 4 px; sudut membulat 6 px untuk chip, 8 px untuk kartu dan input, 12 px untuk kolom, panel, dan dialog.
Komponen
Spesifikasi
Huruf
Dasar 15 px; kartu dan tabel 14 px; keterangan 12 sampai 13 px; judul halaman 28 px, judul bagian 20 px. Berat 400, 500, dan 600 saja
Kolom board
Lebar 296 px, --surface-1, sudut 12 px, padding 8 px, jarak antarkartu 8 px. Judul 13 px berat 500 warna --text-muted dengan jumlah kartu; kolom bertanda selesai diberi ikon centang kecil
Kartu
--surface-2, garis tepi 1 px --border, sudut 8 px, padding 10 sampai 12 px. Judul 14 px berat 500, maksimal tiga baris. Hover: garis tepi menjadi --border-strong. Fokus papan ketik: cincin aksen
Kartu diseret
Diperbesar 1,02 kali, garis tepi aksen, bayangan 0 8px 24px rgba(0,0,0,.5). Penanda posisi tujuan: kotak putus-putus aksen dengan opasitas 40%
Chip label
Sudut 6 px, 11 px berat 500, padding 2×8 px, warna dari palet label
Prioritas
Tinggi: ikon panah atas dan teks "Tinggi" dengan --danger-soft. Normal: tidak ditampilkan. Rendah: ikon panah bawah warna --text-muted
Chip tenggat
Biasa: ikon jam, --text-muted. Kurang dari 24 jam: --warning-soft dengan ikon jam. Lewat: --danger-soft dengan ikon peringatan dan teks "Terlambat". Selesai: --success-soft dengan centang
Progres checklist
Teks "2/5" dan batang setebal 4 px berwarna aksen di atas --surface-3
Avatar
Lingkaran 24 px berisi inisial; warna dipilih dari palet label berdasarkan nama, supaya orang yang sama selalu berwarna sama
Tombol
Tinggi 36 px (32 px untuk kecil), sudut 8 px. Utama: latar aksen, teks --on-accent. Sekunder: --surface-3 dengan garis tepi. Ghost: transparan, hover --surface-3. Bahaya: --danger-soft dengan teks --danger; hapus permanen memakai latar --danger penuh
Input
--surface-3, garis tepi --border, sudut 8 px, tinggi 36 px. Fokus: garis tepi aksen dan cincin aksen 2 px transparan 40%. Galat: garis tepi --danger disertai pesan berikon di bawahnya
Tab
Aktif: garis bawah aksen 2 px dan teks --text. Lainnya: --text-muted
Panel samping detail kartu
Lebar 480 px, --surface-1, garis tepi kiri, bayangan -8px 0 24px rgba(0,0,0,.4). Di ponsel menjadi lembar layar penuh dengan tombol tutup di kiri atas
Dialog dan menu
--surface-2, garis tepi --border-strong, sudut 12 px, bayangan; latar penutup rgba(0,0,0,.6)
Pesan singkat (toast)
--surface-3 dengan garis kiri 3 px berwarna status dan ikon; hilang sendiri setelah 5 detik, kecuali galat
Galeri berkas
Grid thumbnail bersudut 8 px bergaris tepi; nama berkas muncul saat hover. Penampil gambar penuh berlatar rgba(0,0,0,.9)
Keadaan kosong
Ikon 32 px --text-faint, satu kalimat penjelas, dan satu tombol tindakan
Memuat
Kerangka --surface-3 dengan denyut opasitas pelan; mati bila pengguna memilih gerak minimal
Ikon
Lucide, SVG inline, garis 1,5 px, warna mengikuti teks; ukuran hanya lewat komponen x-icon (xs 14 px, sm 16 px, md 20 px, lg 32 px), tidak pernah memakai ukuran bawaan SVG
Penanda kelas SortableJS dipetakan ke gaya di atas: sortable-chosen untuk kartu terambil, sortable-drag untuk kartu yang diseret, dan sortable-ghost untuk penanda posisi tujuan.
Gaya visual: presentasi, cetak, aksesibilitas, implementasi
Mode presentasi tetap gelap di layar: latar --bg, slide berperbandingan 16:9 dengan margin 6%, judul 44 px berat 600, isi 24 px, dan gambar contain di dalam bingkai --surface-1 bersudut 12 px. Kontrol (sebelumnya, berikutnya, layar penuh, keluar) muncul saat penunjuk digerakkan atau layar diketuk dan hilang setelah dua detik. Penunjuk "3/12" dan batang progres tipis aksen selalu terlihat di bawah. Papan ketik: panah untuk berpindah, spasi untuk maju, F untuk layar penuh, Esc untuk keluar.
Cetak ke PDF selalu terang. Browser umumnya mematikan pencetakan latar secara bawaan, sehingga teks terang di atas kertas putih tidak terbaca. Karena semua warna adalah token, cukup menimpa token di @media print:
@media print {  :root {    color-scheme: light;    --color-bg: #FFFFFF;    --color-surface-1: #FFFFFF;    --color-surface-2: #F8FAFC;    --color-surface-3: #F1F5F9;    --color-border: #E2E8F0;    --color-text: #111827;    --color-text-muted: #4B5563;    --color-accent: #4F46E5;  }  @page { size: A4 landscape; margin: 12mm; }  .no-print { display: none !important; }  .slide { break-after: page; break-inside: avoid; }  .slide img { print-color-adjust: exact; }}
Semua kontrol presentasi, bilah atas, dan tombol diberi kelas no-print. Karena Tailwind 4 mengeluarkan token sebagai variabel CSS, penimpaan ini harus diuji nyata dengan mencetak satu halaman perencanaan ke PDF di Tahap 4, bukan diasumsikan jalan.
Email memakai HTML sederhana berlatar terang tanpa bergantung pada tema aplikasi; mode gelap di Gmail akan membaliknya sendiri.
Aksesibilitas:
Kontras teks minimal 4,5:1, dan garis tepi komponen yang menentukan fungsi (input, tombol sekunder) minimal 3:1 terhadap latarnya.
Fokus papan ketik selalu terlihat: garis luar aksen 2 px dengan jarak 2 px. Tidak ada outline: none tanpa pengganti.
Status tidak pernah hanya berupa warna; selalu ada ikon atau teks.
Target sentuh minimal 44 px di ponsel, termasuk menu "Pindahkan ke…".
Animasi dimatikan atau dikurangi bila pengguna memilih gerak minimal (prefers-reduced-motion).
Implementasi:
Hal
Pilihan
Styling
Tailwind CSS 4, token di resources/css/app.css, dibangun di lokal dan public/build ikut di-commit
Komponen
Komponen Blade (<x-button>, <x-card>, <x-chip>, <x-input>, <x-icon>) sebagai satu-satunya tempat gaya komponen
Palet label
Ditambahkan sebagai token --label-* di berkas token yang sama
Kontrol bawaan browser
<meta name="color-scheme" content="dark"> dan color-scheme: dark di CSS, supaya scrollbar, pemilih tanggal, dan isian otomatis ikut gelap
Bilah browser ponsel
<meta name="theme-color" content="#0F1115">
Ikon
Lucide sebagai SVG inline lewat komponen Blade, tanpa font ikon
Tema terang kelak
Set token kedua di :root[data-theme="light"] ditambah satu tombol; tidak ada perubahan komponen
Gaya visual: revisi 1, garis dan ikon
Pemakaian pertama menunjukkan dua hal yang diperbaiki: garis abu-abu hampir tidak terlihat dan ikon terlalu besar. Ukuran huruf sudah pas dan tidak diubah.
Garis. Nilai --border awal (#2A3040) hanya berkontras sekitar 1,25:1 terhadap kartu, di bawah aturan 3:1 untuk garis komponen fungsional di bagian aksesibilitas. Kesalahan itu ada di spesifikasi, bukan di kode. Perbaikannya dua tingkat: garis dekoratif cukup sekitar 2:1, garis kontrol harus lebih terang.
Token
Lama
Baru
Dipakai untuk
Kontras (perkiraan)
--border
#2A3040
#434B61
Kartu, kolom, pemisah
sekitar 1,9:1 terhadap --surface-2
--border-strong
#3A4157
#6B7388
Input, tombol sekunder, kotak centang, hover
sekitar 3,1:1 terhadap --surface-3
Nilai kontras adalah hitungan perkiraan; lihat dulu di layar dan geser sedikit bila terasa terlalu terang atau gelap.
Elemen
Token
Tepi kartu, tepi kolom (kolom kini diberi border 1 px, sebelumnya menyatu dengan latar), pemisah panel dan tabel
border-border
Input, select, textarea, tombol sekunder, kotak centang
border-border-strong
Hover kartu, tepi kiri panel samping, tepi dialog dan menu
border-border-strong
Fokus
Cincin aksen (tidak berubah)
Aturan penulisan: di Tailwind 4, kelas border tanpa warna memakai currentColor, sehingga tampak terlalu terang. Selalu tulis warnanya, misalnya border border-border.
Ikon. Ikon tampil terlalu besar karena SVG tanpa ukuran memakai ukuran bawaan. Ukuran sekarang menjadi bagian dari komponen x-icon, dengan satu prop size:
Ukuran
Piksel
Dipakai di
xs
14
Di dalam chip (prioritas, tenggat)
sm (bawaan)
16
Tombol, baris menu, tab, ikon pensil
md
20
Judul halaman, penampil
lg
32
Keadaan kosong
@props(['name', 'size' => 'sm'])@php    $cls = ['xs' => 'size-3.5', 'sm' => 'size-4', 'md' => 'size-5', 'lg' => 'size-8'][$size] ?? 'size-4';@endphp<svg {{ $attributes->class([$cls, 'shrink-0 inline-block']) }}     viewBox="0 0 24 24" fill="none" stroke="currentColor"     stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">    {{-- path sesuai $name --}}</svg>
Aturan: tidak ada <svg> tanpa ukuran di Blade mana pun; semua ikon lewat x-icon. Perubahan token ini sudah diterapkan juga pada tabel token dan berkas token di bagian Gaya visual sebelumnya.
Peran dan hak akses
Berbagi dilakukan di level projek dengan tiga peran, dan semua pemeriksaan akses lewat satu lapisan ProjectAccess yang dibangun di atas spatie/laravel-permission dalam mode teams.
Peran
Boleh
Tidak boleh
Owner
Semua hal di projek, termasuk mengelola anggota dan menghapus permanen
Dihapus atau diturunkan bila ia pemilik terakhir
Editor
Mengubah board, kartu, halaman perencanaan, dan berkas; memulihkan dari tempat sampah
Mengelola anggota, hapus permanen
Viewer
Melihat dan menjalankan mode presentasi
Mengubah apa pun
Autentikasi dan otorisasi dipisah. Login, sesi, dan reset kata sandi memakai Laravel Fortify dengan view Blade sendiri, tanpa rute registrasi. Spatie hanya menjawab "boleh atau tidak", bukan "siapa ini".
Aturan teknis yang wajib dipegang:
Mode teams diaktifkan di konfigurasi sebelum migrasi pertama dijalankan; mengaktifkannya belakangan mengubah kunci utama tabel yang sudah terisi.
ProjectAccess menetapkan konteks projek (setPermissionsTeamId) sendiri setiap kali dipanggil, sehingga tidak bergantung pada urutan middleware.
Controller tidak memanggil Spatie langsung. Semuanya lewat ProjectAccess (misalnya require_role($projectId, 'editor')), sehingga lupa menetapkan konteks tidak membuka akses.
Setiap tabel data projek menyimpan project_id, jadi pemeriksaan akses cukup satu langkah tanpa menelusuri induk.
users.is_admin hanya untuk mengelola pengguna dan membuat projek, tanpa Gate::before, sehingga admin tidak otomatis melihat isi projek.
Daftar projek difilter lewat query ke tabel peran, karena Spatie hanya memeriksa izin dan tidak memfilter daftar.
Pemeriksaan yang diuji otomatis di Tahap 1: satu pengguna dengan peran berbeda di dua projek tidak bisa membaca, menulis, atau menebak ID projek lain, termasuk URL gambar.
Undangan: admin membuat akun, lalu sistem mengirim tautan sekali pakai agar pengguna menetapkan kata sandinya sendiri.
Model data
Modelnya berisi 14 tabel aplikasi ditambah tabel Spatie, dan setiap tabel data projek menyimpan project_id langsung.
Tabel
Kolom penting
Catatan
users
name, email, password, is_admin, notify_email
Dibuat admin; tanpa registrasi; kolom deactivated_at menonaktifkan anggota tanpa menghapus datanya
projects
name, description, due_at, archived_at, deleted_at
Diarsipkan, bukan dihapus
Tabel Spatie
roles, permissions, model_has_roles, dan sejenisnya, dengan project_id sebagai team
Mode teams aktif sebelum migrasi pertama
columns
project_id, name, position, is_done
Tidak bisa dihapus selama masih berisi kartu
cards
project_id, column_id, title, description (teks), position, priority, due_at (tanggal dan jam), page_id (nullable), deleted_at
Soft delete
card_user
project_id, card_id, user_id
Banyak penanggung jawab per kartu
labels, card_label
project_id, name, color; project_id, card_id, label_id
Label per projek
checklists
project_id, card_id, title, position
Banyak checklist per kartu
checklist_items
project_id, checklist_id, text, is_done, position
pages
project_id, title, position, deleted_at
Halaman perencanaan
blocks
project_id, page_id, type (judul / teks / gambar / daftar), content (JSON), position
JSON karena isi tiap jenis blok berbeda
files
project_id, original_name, stored_name (acak), size, width, height, thumb_path, created_by, deleted_at
Satu galeri melayani kartu dan blok
card_file
project_id, card_id, file_id
Lampiran kartu; blok gambar mengacu ke files.id
reminders
project_id, card_id, user_id, remind_at, sent_at
Dibuat ulang saat tenggat berubah
Konvensi:
position berupa bilangan bulat berjarak 1000; memindah item mengambil nilai tengah, penomoran ulang hanya saat jarak habis.
due_at disimpan UTC dan ditampilkan dalam Asia/Jakarta.
Soft delete pada kartu, halaman, dan berkas. Hapus permanen hanya oleh owner atau otomatis oleh cron setelah 30 hari.
Setiap tabel punya created_at dan updated_at; updated_at dipakai untuk polling dan peringatan konflik.
Pembatasan percobaan login memakai throttle bawaan Laravel, jadi tabel login_attempts tidak diperlukan.
Stack dan deploy
Stack-nya Laravel dengan tampilan Blade, Alpine.js, dan SortableJS, tanpa framework JavaScript berat, dan aset dibangun di komputer lokal lalu dikirim ke server.
Lapisan
Pilihan
Alasan
Backend
Laravel 13 (PHP 8.4)
Auth, CSRF, validasi, migrasi, policy, pembatas login sudah matang
Basis data
MySQL dari hPanel
Satu-satunya yang tersedia
Tampilan
Blade + Alpine.js
Tanpa build Node di server dan tanpa SPA
Drag-and-drop
SortableJS
Kecil, stabil, ramah layar sentuh
Gambar
GD bawaan PHP
Tanpa dependensi tambahan
Presentasi
JavaScript biasa, Fullscreen API, CSS cetak
Sepenuhnya di sisi browser
PHP polos ditolak karena aplikasi ini intinya pemeriksaan akses; menulis sendiri hashing, sesi, CSRF, dan pembatas login berarti setiap bagian yang kurang rapi adalah celah. Slim ditolak karena penghematannya kecil dibanding risiko menulis auth sendiri.
Struktur folder. Hostinger shared hosting tidak memungkinkan web root dipindah dari public_html dan tidak mendukung git hook di sisi server, sehingga kode aplikasi diletakkan di luar public_html:
kds-pm/                 kode Laravel, .env, vendor (di luar folder publik)  storage/app/private/  berkas unggahan (tidak bisa dibuka lewat URL)public_html/            isi folder public/ Laravel saja  index.php             jalur disesuaikan ke ../kds-pm
Deploy lewat SSH: git pull, composer install --no-dev, cadangan basis data, php artisan migrate --force, php artisan config:cache, route:cache. Aset hasil build ikut di-commit atau diunggah.
Hosting yang sudah dipastikan dan yang tetap dicek:
SSH sudah tersedia di plan yang dipakai (dikonfirmasi), jadi deploy lewat SSH berlaku dan vendor tidak perlu diunggah manual.
PHP minimal 8.4 tersedia. Versi web dan CLI harus sama, karena pengalih versi di hPanel tidak mengubah php di terminal SSH; Laravel dan Spatie dipilih versi yang mendukung PHP 8.4.
Alamat aplikasi adalah subdomain projeku di domain induk milik sendiri, dibuat di hPanel dengan SSL aktif dan HTTPS dipaksa. APP_URL diisi alamat itu dan SESSION_DOMAIN dibiarkan kosong agar cookie sesi hanya berlaku untuk subdomain ini. Isi folder public/ Laravel masuk ke folder root subdomain tersebut.
Cron schedule:run harus terdaftar dan terbukti berjalan.
upload_max_filesize dan post_max_size diset sekitar 12 MB lewat hPanel.
Konfigurasi lewat .env dan penyimpanan lewat disk Laravel, supaya pindah ke VPS atau R2 tanpa menulis ulang.
Lingkungan: pengembangan di lokal dengan uji otomatis; hanya ada satu lingkungan di Hostinger, jadi tidak ada staging yang menyelamatkan migrasi yang salah.
Rincian struktur folder, .env, dan skrip deploy ada di bagian Pelaksanaan Tahap 0; gaya dan token visual ada di bagian Gaya visual.
Berkas dan gambar
Gambar disimpan di server sendiri dengan batas 10 MB per berkas, dan setiap gambar diproses dua tahap sebelum tersimpan.
Browser mengecilkan gambar (canvas) sebelum diunggah, sehingga unggahan lebih kecil dan beban server berkurang.
Server memeriksa dimensi dulu dengan getimagesize dan menolak di atas sekitar 24 megapiksel sebelum mendekode, lalu memvalidasi bahwa isinya benar-benar gambar, membuang metadata EXIF, mengubah ukuran, dan menyimpan ulang sebagai WebP.
Mengapa batas piksel, bukan hanya ukuran berkas: PNG 10 MB bisa berisi 8000×6000 piksel, dan GD butuh sekitar 4 byte per piksel saat membukanya, sehingga satu gambar bisa menghabiskan memori PHP.
Aturan
Nilai
Ukuran maksimal unggahan
10 MB (validasi Laravel); PHP diset sekitar 12 MB
Dimensi maksimal sebelum dekode
sekitar 24 megapiksel
Format simpan
WebP kualitas tinggi
Sisi terpanjang
maksimal 2560 px, ditambah thumbnail
Berkas asli
tidak disimpan (dapat ditambah bila dibutuhkan)
SVG
ditolak, karena bisa menyisipkan skrip
Nama tersimpan
acak, bukan nama asli
Penyajian. Berkas disimpan di luar folder publik dan disajikan lewat controller yang memeriksa keanggotaan projek lebih dulu. Header cache dipasang agar thumbnail tidak diminta berulang. Penyimpanan memakai disk Laravel yang dikonfigurasi lewat .env, sehingga pindah ke Cloudflare R2 nanti hanya mengganti konfigurasi.
Yang perlu dipantau:
Setiap gambar menjadi dua sampai tiga berkas, jadi pantau jumlah berkas (inode), bukan hanya megabyte.
Gambar hanya ada di satu server, jadi cadangan berkas harus dijadwalkan dan satu kali diuji pemulihannya.
Berkas yang dihapus masuk tempat sampah 30 hari; cron membersihkan berkas yatim.
Pengingat email
Pengingat tenggat dikirim lewat email, satu email per pengguna per pengiriman berisi semua kartunya yang jatuh tempo, dan inilah alarm utama aplikasi.
Aspek
Rancangan
Jadwal bawaan
Pagi H-1 dan pagi hari-H pukul 08.00 WIB
Penerima
Semua penanggung jawab kartu; pengguna bisa mematikan lewat notify_email
Pemicu
Scheduler Laravel dijalankan cron tiap beberapa menit; mengirim reminders yang jatuh tempo dan mengisi sent_at agar tidak ganda
Saat tenggat diubah
Pengingat kartu itu dibuat ulang
Saat kartu masuk kolom is_done
Pengingat yang belum terkirim dibatalkan
Isi email
Judul kartu, nama projek, tenggat, tautan yang meminta login; tanpa deskripsi dan tanpa gambar
Pengiriman
SMTP dari satu mailbox di domain induk, dengan SPF, DKIM, dan DMARC aktif; jeda antarpesan dan plafon harian (lihat di bawah)
Mengapa satu email berisi daftar: lima kartu bertenggat sama menjadi satu email, bukan lima. Ini lebih ramah bagi penerima dan jauh lebih aman terhadap batas kirim per jam di Hostinger.
Mengapa kalender tidak menggantikan email: kalender memberi visibilitas, bukan jaminan bunyi tepat waktu. Laporan pengguna menunjukkan Google Calendar tidak memakai alarm dari berkas .ics yang diimpor dan menerapkan pengaturan notifikasi kalender masing-masing, sehingga waktu bunyinya tidak bisa dikendalikan aplikasi.
Diuji di Tahap 0: satu email uji dari SMTP domain ke Gmail harus mendarat di kotak masuk, bukan spam.
Jeda pengiriman. Batas kirim email Hostinger berbeda menurut jenis email dan plan, dan Hostinger menyatakan batas bisa berubah tanpa pemberitahuan (dokumentasi Hostinger). Angka yang kutemukan bervariasi dan bersifat perkiraan: sekitar 100 pesan per hari per kotak surat untuk email gratis bawaan hosting, dan 200 sampai 300 pesan per jam pada plan cPanel lama. Rancangannya mengasumsikan yang terendah:
Plafon harian: paling banyak 50 email per hari, dapat diubah lewat MAIL_DAILY_CAP di .env. Pengingat yang melewati plafon ditunda ke hari berikutnya dan ditandai terlambat, tidak dibuang.
Jeda antarpesan: satu email setiap 30 detik lewat antrean database yang diproses cron, bukan dikirim serentak. Puncaknya 120 email per jam.
Beban nyata: tim sekitar 10 orang menghasilkan paling banyak sekitar 20 email per hari, dan batch pukul 08.00 (sekitar 10 email) selesai dalam sekitar 5 menit.
Kegagalan: bila SMTP menolak atau plafon tercapai, aplikasi mencatat log dan menampilkan peringatan ke admin; percobaan ulang maksimal tiga kali dengan jeda yang bertambah, tidak diulang tanpa henti.
Pengirim: satu mailbox di domain induk (misalnya noreply@) yang ada di Hostinger dan cocok dengan SPF dan DKIM, bukan alamat Gmail pribadi.
Tetap dicek: batas pasti untuk plan yang dipakai, di hPanel.
Rencana MVP bertahap
Pekerjaan dikerjakan dalam enam tahap berurutan, diurutkan berdasarkan risiko: yang paling mungkin gagal di shared hosting dicoba paling awal, dan tahap berikutnya baru dimulai setelah batas selesai tahap sebelumnya terpenuhi.
Urutan
Tahap
Isi
Selesai bila
1
Tahap 0: Deploy kosong
Laravel baru di Hostinger (subdomain projeku, PHP 8.4, SSL aktif) dengan struktur folder di luar public_html; .env dan MySQL berjalan; versi PHP web dan CLI sama; SSH berfungsi; cron schedule:run terbukti jalan; email uji dari SMTP domain; skrip deploy yang mencadangkan basis data sebelum migrate
Kode bisa di-deploy dengan satu rangkaian perintah dan email uji mendarat di kotak masuk Gmail
2
Tahap 1: Fondasi dan akses
Token tema gelap dan komponen dasar; Fortify tanpa registrasi; pembuatan pengguna oleh admin; undangan dan reset kata sandi lewat tautan sekali pakai; throttle login; Spatie teams (diaktifkan sebelum migrasi pertama); middleware konteks projek; ProjectAccess; daftar projek terfilter; kelola anggota dengan aturan pemilik terakhir
Uji otomatis akses lulus: satu pengguna dengan peran berbeda di dua projek tidak bisa membaca, menulis, atau menebak ID projek lain
3
Tahap 2: Board, kartu, berkas
Kolom bebas; kartu dengan drag; detail kartu (deskripsi teks, label, prioritas, tenggat, banyak penanggung jawab, banyak checklist); filter board; unggah gambar dua tahap; lampiran dan galeri Berkas; soft delete dan tempat sampah; tampilan ponsel; polling yang berhenti saat tab tidak terlihat dan peringatan konflik
URL gambar yang ditebak tidak bisa dibuka orang di luar projek, dan board tetap lancar dengan sekitar 100 kartu dan puluhan gambar
4
Tahap 3: Pengingat email
Tabel reminders; pembuatan ulang saat tenggat berubah; pembatalan saat kartu selesai; scheduler; satu email per pengguna berisi daftar; pengaturan matikan email
Tenggat yang diubah, ditunda, atau diselesaikan tidak pernah menghasilkan email yang salah atau ganda
5
Tahap 4: Perencanaan dan presentasi
Halaman dengan empat blok yang bisa diseret; tautan dua arah kartu dan halaman; mode presentasi (judul = slide baru, gambar contain, layar penuh, navigasi, CSS cetak)
Satu halaman nyata bisa dipresentasikan dan dicetak ke PDF tanpa perbaikan manual
6
Tahap 5: Pengerasan
Cadangan basis data dan berkas terjadwal; satu kali uji pemulihan sungguhan; pemantauan disk dan jumlah berkas; daftar pemeriksaan keamanan (header, cookie, batas ukuran, log kesalahan)
Cadangan berhasil dipulihkan di lingkungan lokal dan daftar pemeriksaan selesai
Kemungkinan penyesuaian:
Tahap 2 adalah yang terbesar. Bila terasa kepanjangan, dipecah menjadi 2a (board dan kartu) dan 2b (berkas) agar board sudah bisa dipakai lebih cepat.
Presentasi (Tahap 4) adalah fitur pembeda aplikasi tetapi urutannya paling akhir, karena tanpa akses dan berkas yang kokoh halaman perencanaan hanya menimbun gambar di tempat rapuh. Tahap 3 dan 4 bisa ditukar bila ada alasan memerlukan presentasi lebih awal.
Pelaksanaan: prinsip dan prasyarat
Bagian pelaksanaan menjelaskan apa yang dikerjakan dan bagaimana membuktikan hasilnya, dalam dua tahap: Tahap 0 (deploy kosong) dan Tahap 1 (fondasi dan akses).
Semua kode ditulis dan diuji di lokal. Server hanya menerima hasilnya lewat git pull dan deploy.sh, dan tidak pernah diedit langsung; git pull --ff-only akan menolak jalan bila ada perubahan manual di sana. Hanya ada satu lingkungan di Hostinger tanpa staging, jadi setiap perubahan harus sudah lulus uji di lokal sebelum sampai ke server. Server disentuh lebih awal hanya di Tahap 0, saat aplikasinya masih halaman kosong, karena masalah hosting lebih murah ketahuan sekarang.
Aturan gerbang: Tahap 1 tidak dimulai sebelum Tahap 0 selesai, dan Tahap 2 (board) tidak dimulai sebelum uji akses Tahap 1 lulus.
Delapan prasyarat berikut harus beres sebelum menulis baris kode pertama; tujuh pertama menghalangi Tahap 0, yang kedelapan hanya menghalangi Tahap 3.
☐ Subdomain projeku dibuat di hPanel, SSL aktif, dan folder root-nya dicatat persis.
☐ Basis data MySQL khusus aplikasi dibuat (nama, pengguna, kata sandi) dan dicatat di tempat aman.
☐ Mailbox pengirim noreply@<domain-induk> dibuat di Hostinger Email.
☐ Kunci SSH ditambahkan di hPanel dan login tanpa kata sandi berhasil.
☐ php -v di SSH menunjukkan 8.4 (catat jalur biner PHP-nya) dan composer --version berjalan.
☐ Repositori Git privat dibuat, dengan deploy key hanya-baca untuk git pull dari server.
☐ Lokal siap: PHP 8.4, Composer, Node untuk build aset (Tailwind dan Vite), dan MySQL lokal untuk uji.
☐ Batas kirim email pasti di hPanel dicatat (dipakai di Tahap 3).
Pelaksanaan Tahap 0: struktur server dan konfigurasi
Kode aplikasi tinggal di ~/kds-pm, di luar folder publik, dan hanya isi public/ yang disalin ke folder root subdomain projeku.
/home/<user-hostinger>/  kds-pm/                    repositori Git: kode, .env, vendor    deploy/      deploy.sh              skrip deploy, dijalankan di server      index.hostinger.php    index.php khusus server    storage/      app/private/           berkas unggahan, tidak bisa dibuka lewat URL      backups/               cadangan basis data  domains/<domain-induk>/public_html/projeku/                             root subdomain: isi public/ saja
Jalur root subdomain mengikuti yang ditampilkan hPanel. Folder itu hanya berisi isi public/ (tanpa kode dan tanpa .env), jadi tetap aman bila ternyata bisa diakses juga lewat path domain induk.
deploy/index.hostinger.php menggantikan public/index.php di server, supaya Laravel menemukan kodenya di luar folder publik:
<?phpuse Illuminate\Http\Request;define('LARAVEL_START', microtime(true));$base = '/home/<user-hostinger>/kds-pm';if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {    require $maintenance;}require $base.'/vendor/autoload.php';/** @var \Illuminate\Foundation\Application $app */$app = require_once $base.'/bootstrap/app.php';$app->usePublicPath(__DIR__);$app->handleRequest(Request::capture());
Baris usePublicPath(__DIR__) wajib, karena folder publik tidak lagi bersebelahan dengan kode. .htaccess bawaan Laravel ikut disalin; tambahkan pengalihan HTTPS di dalamnya bila opsi Force HTTPS di hPanel tidak dinyalakan.
.env produksi dibuat langsung di server, tidak pernah masuk Git, dengan izin chmod 600:
Kunci
Nilai
Catatan
APP_ENV, APP_DEBUG
production, false
Debug yang menyala di produksi membocorkan isi .env
APP_URL
https://projeku.<domain-induk>
APP_LOCALE
id
APP_TIMEZONE
UTC (biarkan)
Tampilan memakai KDS_DISPLAY_TIMEZONE=Asia/Jakarta
DB_*
Dari hPanel
DB_HOST biasanya localhost
SESSION_DRIVER, SESSION_LIFETIME
database, 10080 (7 hari)
SESSION_SECURE_COOKIE, SESSION_DOMAIN
true, null
Cookie hanya berlaku untuk subdomain ini
CACHE_STORE, QUEUE_CONNECTION
database, database
Tanpa Redis di shared hosting
FILESYSTEM_DISK
local
Disk local Laravel berakar di storage/app/private
LOG_CHANNEL, LOG_LEVEL
daily, warning
MAIL_MAILER, MAIL_HOST
smtp, smtp.hostinger.com
MAIL_PORT, MAIL_SCHEME
465, smtps
587 dengan STARTTLS sebagai alternatif
MAIL_USERNAME, MAIL_FROM_ADDRESS
noreply@<domain-induk>
Mailbox yang dibuat di Prasyarat
MAIL_DAILY_CAP
50
Dipakai di Tahap 3
APP_KEY dibuat sekali di server dengan php artisan key:generate, lalu disalin ke tempat aman di luar server. Kehilangan kunci ini membuat semua sesi dan data terenkripsi tidak terbaca.
Pelaksanaan Tahap 0: skrip deploy dan cadangan
Satu skrip menjalankan seluruh deploy, selalu mencadangkan basis data sebelum migrasi, dan berhenti dengan aplikasi tetap dalam mode pemeliharaan bila ada langkah yang gagal.
#!/usr/bin/env bash# Dijalankan di server: bash ~/kds-pm/deploy/deploy.shset -euo pipefailAPP="$HOME/kds-pm"DOCROOT="$HOME/domains/<domain-induk>/public_html/projeku"PHP="/usr/bin/php"            # ganti dengan biner PHP 8.4 dari `php -v`COMPOSER="$(command -v composer)"DB_NAME="<nama-db>"STAMP="$(date +%F-%H%M)"BACKUP="$APP/storage/backups/db-$STAMP.sql.gz"STEP="awal"cd "$APP""$PHP" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' \  || { echo "PHP di bawah 8.4, berhenti"; exit 1; }mkdir -p storage/backupsmysqldump --single-transaction --no-tablespaces "$DB_NAME" | gzip > "$BACKUP"test -s "$BACKUP" || { echo "Cadangan kosong, berhenti"; exit 1; }"$PHP" artisan down --retry=60 || trueon_fail() {  echo "GAGAL di langkah: $STEP. Aplikasi dibiarkan dalam mode pemeliharaan."  echo "Bila migrasi sempat jalan, pulihkan: gunzip < $BACKUP | mysql $DB_NAME"}trap on_fail ERR# Satu perintah per baris: kegagalan di dalam rantai && tidak menghentikan skrip.STEP="git pull";         git pull --ff-onlySTEP="composer install"; "$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interactionSTEP="migrate";          "$PHP" artisan migrate --forceSTEP="cache config";     "$PHP" artisan config:cacheSTEP="cache route";      "$PHP" artisan route:cacheSTEP="cache view";       "$PHP" artisan view:cacheSTEP="cache event";      "$PHP" artisan event:cacheSTEP="salin public";     rsync -a --delete --exclude='index.php' --exclude='.well-known' public/ "$DOCROOT/"STEP="salin index";      cp deploy/index.hostinger.php "$DOCROOT/index.php""$PHP" artisan upls -1t storage/backups/db-*.sql.gz | tail -n +15 | xargs -r rm --echo "Selesai: $STAMP"
Prasyarat skrip: file ~/.my.cnf berisi blok [client] dengan user, password, dan host basis data, dengan izin chmod 600, supaya mysqldump tidak meminta kata sandi dan kata sandi tidak muncul di riwayat perintah. Bila rsync tidak tersedia di server, ganti dengan cp -a setelah mengosongkan isi root subdomain kecuali index.php dan .well-known.
Aset frontend dibangun di komputer lokal (npm run build) dan folder public/build ikut di-commit, karena server tidak menjalankan Node.
Deploy pertama (sekali):
git clone repositori ke ~/kds-pm memakai deploy key.
Buat .env dari contoh di bagian sebelumnya, lalu php artisan key:generate dan salin APP_KEY ke tempat aman.
Buat ~/.my.cnf.
Jalankan deploy.sh.
Deploy berikutnya: di lokal npm run build, commit, push; di server bash ~/kds-pm/deploy/deploy.sh.
Pemulihan cadangan diuji sekali di Tahap 0, di lokal: gunzip < db-<stempel>.sql.gz | mysql <db-lokal>, lalu aplikasi dijalankan terhadap basis data itu. Cadangan yang belum pernah dipulihkan belum bisa disebut cadangan. Cadangan di server yang sama tidak melindungi dari kehilangan server; salinan keluar server menjadi pekerjaan Tahap 5.
Pelaksanaan Tahap 0: cron dan uji email
Cron dan email adalah dua hal yang paling sering gagal diam-diam di shared hosting, jadi keduanya dibuktikan dengan bukti yang bisa dilihat, bukan diasumsikan.
Cron. Di hPanel (Cron Jobs) dibuat satu tugas yang berjalan tiap menit, memakai biner PHP 8.4:
* * * * * /usr/bin/php /home/<user-hostinger>/kds-pm/artisan schedule:run >> /dev/null 2>&1
Detak jantung scheduler. Satu tugas terjadwal menulis baris log tiap menit. Di routes/console.php:
use Illuminate\Support\Facades\Log;use Illuminate\Support\Facades\Schedule;Schedule::call(fn () => Log::channel('heartbeat')->info('scheduler ok'))    ->everyMinute()    ->name('heartbeat');
Dan satu channel di config/logging.php:
'heartbeat' => [    'driver' => 'single',    'path' => storage_path('logs/heartbeat.log'),    'level' => 'info',],
Buktinya: tail -f storage/logs/heartbeat.log menunjukkan satu baris baru tiap menit selama lima menit. Setelah terbukti, jadwalnya diubah ke ->hourly() dan tidak dihapus: umur baris terakhir di berkas itu menjadi pemantau "apakah cron masih hidup" di Tahap 5.
Uji email. Dari server, lewat php artisan tinker:
Mail::raw('Uji pengiriman', fn ($m) => $m->to('<gmail-pengujimu>')->subject('Uji projeku'));
Email harus mendarat di kotak masuk, bukan spam. Di Gmail, buka "Tampilkan asli" dan pastikan SPF, DKIM, dan DMARC sama-sama berstatus PASS.
Rekaman DNS
Tujuan
Catatan
SPF (TXT di domain induk)
Menyatakan server mana yang boleh mengirim
Nilai persisnya diambil dari hPanel, bukan dari dokumen ini
DKIM (rekaman dari hPanel)
Tanda tangan digital tiap email
Bila DNS dikelola Hostinger, biasanya sudah otomatis; cukup dicek keberadaannya
DMARC (TXT _dmarc)
Kebijakan bila SPF atau DKIM gagal
Mulai dengan p=none; naikkan ke quarantine setelah dua minggu tanpa masalah
Alamat pengirim harus persis alamat mailbox di domain yang ditandatangani (noreply@<domain-induk>). Pengirim dengan alamat yang tidak cocok adalah penyebab paling umum email masuk spam di Gmail.
Pelaksanaan Tahap 1: paket dan konfigurasi
Tahap 1 memasang dua paket, Fortify untuk login dan Spatie permission v7 dalam mode teams untuk peran per projek, dan urutan konfigurasinya menentukan: mode teams harus menyala sebelum migrasi pertama Spatie dijalankan.
composer require laravel/fortify spatie/laravel-permission.
Pasang Fortify (php artisan fortify:install, atau publikasi manual sesuai dokumentasi Fortify). Aktifkan hanya fitur resetPasswords dan updatePasswords. Fitur registrasi, verifikasi email, dan profil tidak diaktifkan, sehingga rutenya tidak pernah ada.
Publikasikan konfigurasi dan migrasi Spatie (php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider").
Sebelum migrate, ubah config/permission.php: 'teams' => true dan 'team_foreign_key' => 'project_id'. Periksa file migrasi hasil publikasi: tabel roles dan model_has_roles harus sudah memuat kolom project_id.
Tambahkan use HasRoles; pada model User.
Jalankan php artisan migrate.
Berkas
Perubahan
config/permission.php
teams true; team_foreign_key project_id
config/fortify.php
Fitur hanya resetPasswords dan updatePasswords; home ke /projects
config/auth.php
passwords.users.expire 1440 (tautan undangan dan reset berlaku 24 jam); throttle 60
FortifyServiceProvider
View login, lupa kata sandi, dan reset (Blade buatan sendiri, memakai komponen tema gelap); authenticateUsing yang menolak pengguna nonaktif
bootstrap/app.php
Middleware EnsureUserIsActive di grup web: pengguna yang dinonaktifkan saat sesi berjalan langsung dikeluarkan
config/kds.php (baru)
display_timezone, mail_daily_cap dari .env
Penolakan pengguna nonaktif ditulis di authenticateUsing (bukan hanya di middleware), supaya pengguna nonaktif tidak pernah bisa memulai sesi:
Fortify::authenticateUsing(function (Request $request) {    $user = User::where('email', $request->email)->first();    if ($user && $user->deactivated_at === null        && Hash::check($request->password, $user->password)) {        return $user;    }});
Model User juga menimpa sendPasswordResetNotification agar pengguna nonaktif tidak bisa meminta tautan reset.
Tiga aturan yang berlaku di seluruh kode:
Tidak ada rute atau middleware yang memakai role: atau permission: bawaan Spatie. Semua pemeriksaan lewat ProjectAccess.
Blade tidak memakai @can untuk izin projek, karena Spatie mendaftarkan izin ke Gate dan hasilnya bergantung pada konteks projek yang sedang aktif. Gunakan ProjectAccess::can($project, 'board.edit').
Tidak ada rute registrasi. Ada uji otomatis yang memastikan GET /register dan POST /register mengembalikan 404.
Sumber yang perlu diverifikasi sebelum mulai: status Breeze dan kebutuhan PHP Laravel 13 diambil dari ringkasan pihak ketiga (status Breeze dan kit baru, kebutuhan PHP Laravel 13). Cek ulang di laravel.com/docs/releases, dokumentasi Fortify, dan dokumentasi Spatie v7 sebelum menjalankan composer require.
Pelaksanaan Tahap 1: migrasi, seeder, peran dan izin
Tahap 1 hanya membuat tabel yang dibutuhkan akses; tabel board, kartu, dan berkas menunggu Tahap 2.
Urutan
Migrasi
Isi
1
users (bawaan, diubah)
name, email unik, password, is_admin (default false), notify_email (default true), deactivated_at (nullable), remember_token, timestamps; kolom verifikasi email dibuang
2
Tabel bawaan Laravel
password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs
3
projects
name (150), description (teks, nullable), due_at (nullable), archived_at (nullable), deleted_at (nullable), timestamps; indeks pada archived_at dan deleted_at
4
Tabel Spatie
permissions, roles, model_has_permissions, model_has_roles, role_has_permissions, dengan project_id sebagai team
5
Kunci asing tambahan
model_has_roles.project_id merujuk projects.id dengan ON DELETE CASCADE, supaya penghapusan permanen projek ikut membersihkan peran anggotanya
Migrasi urutan 4 baru boleh dijalankan setelah konfigurasi teams menyala (lihat bagian sebelumnya).
Seeder RolePermissionSeeder idempoten (memakai firstOrCreate, aman dijalankan ulang di setiap deploy):
Memanggil setPermissionsTeamId(null) lebih dulu, sehingga tiga peran (owner, editor, viewer) tercipta sebagai peran global yang bisa dipasangkan ke projek mana pun.
Membuat izin sesuai matriks di bawah, lalu syncPermissions untuk tiap peran.
Menjalankan permission:cache-reset di akhir.
Perintah php artisan kds:create-admin {email} {name} membuat pengguna pertama dengan is_admin true dan kata sandi acak yang tidak diketahui siapa pun, lalu mengirim tautan penetapan kata sandi. Opsi --print-link mencetak tautannya di terminal, untuk keadaan darurat bila email belum berjalan.
Matriks peran dan izin. Sembilan izin dibagi ke tiga peran, dan setiap peran mewarisi semua izin peran di bawahnya.
Izin
Owner
Editor
Viewer
Dipakai untuk
project.view
ya
ya
ya
Melihat projek, board, halaman, dan menjalankan presentasi
board.edit
ya
ya
tidak
Kolom, kartu, checklist, label (Tahap 2)
file.upload
ya
ya
tidak
Unggah gambar dan lampiran (Tahap 2)
trash.restore
ya
ya
tidak
Memulihkan dari tempat sampah (Tahap 2)
plan.edit
ya
ya
tidak
Halaman perencanaan (Tahap 4)
project.manage
ya
tidak
tidak
Ubah nama dan tenggat projek, arsipkan
members.manage
ya
tidak
tidak
Undang, ubah peran, keluarkan anggota
trash.purge
ya
tidak
tidak
Hapus permanen isi tempat sampah
project.delete
ya
tidak
tidak
Menghapus projek
Seluruh izin disemai di Tahap 1 walaupun sebagian baru dipakai di tahap berikutnya, supaya tidak ada migrasi data peran nanti.
Pengguna dengan is_admin tidak mendapat akses otomatis ke isi projek. Ia hanya boleh mengelola pengguna (membuat akun, mengirim ulang undangan, menonaktifkan) dan membuat projek baru, dan pembuat projek otomatis menjadi owner-nya. Tidak ada Gate::before yang meloloskan admin ke semua izin. Alasannya: aturan "hanya melihat bagian yang dibagikan" tidak boleh punya pengecualian diam-diam, termasuk untuk admin; admin yang ingin melihat sebuah projek harus menjadi anggotanya, dan itu tercatat. Bila kelak semua anggota boleh membuat projek, cukup satu baris kebijakan yang diubah.
Peran viewer sengaja tidak punya izin presentasi terpisah: presentasi hanyalah cara lain melihat halaman yang sudah boleh ia lihat.
Pelaksanaan Tahap 1: ProjectAccess dan alur akun
Semua izin projek diperiksa lewat satu kelas, ProjectAccess, yang menetapkan konteks projek Spatie sendiri setiap kali dipanggil sehingga tidak bergantung pada urutan middleware.
final class ProjectAccess{    public static function can(Project $project, string $permission, ?User $user = null): bool    {        $user ??= auth()->user();        if (! $user || $user->deactivated_at !== null) {            return false;        }        $registrar = app(PermissionRegistrar::class);        $previous  = $registrar->getPermissionsTeamId();        $registrar->setPermissionsTeamId($project->id);        $user->unsetRelation('roles')->unsetRelation('permissions');        try {            return $user->hasPermissionTo($permission);        } finally {            $registrar->setPermissionsTeamId($previous);            $user->unsetRelation('roles')->unsetRelation('permissions');        }    }    public static function authorize(Project $project, string $permission): void    {        abort_unless(self::can($project, 'project.view'), 404);  // bukan anggota: seolah tidak ada        abort_unless(self::can($project, $permission), 403);     // anggota tanpa izin    }    public static function visibleProjects(User $user): Builder    {        return Project::query()->whereIn('id',            DB::table('model_has_roles')                ->where('model_type', User::class)                ->where('model_id', $user->id)                ->select('project_id'));    }}
unsetRelation wajib karena Spatie menyimpan peran di model pengguna; tanpa itu, peran dari projek sebelumnya bisa terbawa ke pemeriksaan berikutnya dalam satu request. Itulah kebocoran yang paling mungkin terjadi di desain ini, dan uji akses di bagian berikutnya menyerangnya langsung.
Aturan respons: bukan anggota mendapat 404 (keberadaan projek tidak terungkap), anggota tanpa izin mendapat 403. Daftar projek selalu lewat visibleProjects, tidak pernah Project::all().
Pemilik terakhir. Mengubah peran atau mengeluarkan anggota berjalan dalam satu transaksi dengan lockForUpdate pada baris projek, menghitung owner di dalam transaksi, dan menolak bila hasilnya nol owner. Menonaktifkan pengguna yang menjadi satu-satunya owner sebuah projek juga ditolak, dengan daftar projeknya, sampai kepemilikan dialihkan.
Alur akun:
Undangan. Admin mengisi nama dan email. Sistem membuat pengguna dengan kata sandi acak, membuat token lewat Password::broker()->createToken($user), dan mengirim email undangan berisi tautan penetapan kata sandi. Tautan berlaku 24 jam; admin bisa mengirim ulang.
Menetapkan kata sandi. Pengguna membuka tautan dan memakai halaman reset Fortify. Kata sandi minimal 12 karakter.
Lupa kata sandi. Alur Fortify yang sama; login dibatasi 5 percobaan per menit per kombinasi email dan IP.
Nonaktifkan. Mengisi deactivated_at, menghapus semua baris model_has_roles milik pengguna, menghapus barisnya di tabel sessions, dan mengganti remember_token. Pengguna yang sedang login langsung terlempar pada request berikutnya.
Aktifkan kembali. Mengosongkan deactivated_at tanpa memulihkan peran lama; admin dan owner memberi peran lagi per projek. Disengaja, karena akses yang kembali otomatis sering lebih luas dari yang diingat orang.
Projek tanpa owner (hampir mustahil karena aturan di atas, tetapi mungkin lewat edit basis data langsung): php artisan kds:assign-owner {projek} {email}, hanya bisa dijalankan lewat SSH.
Pelaksanaan Tahap 1: skenario uji akses
Empat belas skenario ini ditulis sebagai uji otomatis (Pest atau PHPUnit), dan seluruhnya harus lulus sebelum Tahap 2 dimulai.
Data uji. Dua projek, P1 dan P2, dan lima pengguna:
A: admin, owner P1.
B: editor di P1, viewer di P2.
C: owner P2.
D: tidak punya peran di projek mana pun.
E: nonaktif.
No
Skenario
Hasil yang diharapkan
1
D membuka daftar projek, lalu GET /projects/{P1}
Daftar kosong; 404
2
B membuka daftar projek
P1 dan P2 muncul, tidak ada yang lain
3
B mengubah board P1, lalu P2, lalu P1 lagi dalam satu proses
Diizinkan, 403, diizinkan: peran tidak terbawa antarprojek
4
C membuka P1
404
5
B (viewer di P2) memanggil aksi board.edit dan members.manage di P2
403 untuk keduanya
6
D memanggil GET /projects/{id} untuk semua id dari 1 sampai N, termasuk yang tidak ada
Semua 404, dan isi respons id yang ada identik dengan id yang tidak ada
7
Menurunkan atau mengeluarkan satu-satunya owner, lalu menurunkan dua owner secara berurutan
Ditolak; jumlah owner tidak pernah nol
8
E login dengan kata sandi benar; E punya sesi aktif lalu dinonaktifkan; E meminta tautan reset
Login gagal; sesi terputus pada request berikutnya; tidak ada email reset
9
GET /register dan POST /register
404
10
Enam percobaan login salah berturut-turut
Percobaan keenam mendapat 429
11
Tautan undangan dipakai setelah 24 jam, dan dipakai dua kali
Ditolak kedua-duanya
12
A (admin, bukan anggota P2) membuka isi P2, lalu membuat pengguna dan projek baru
404 untuk isi P2; pembuatan berhasil dan A menjadi owner projek baru
13
E diaktifkan kembali
Daftar projek kosong sampai perannya diberikan lagi
14
RolePermissionSeeder dijalankan dua kali; satu projek dihapus permanen
Tetap 3 peran dan 9 izin tanpa duplikat; baris model_has_roles projek itu ikut terhapus
Skenario 3 dan 6 adalah yang terpenting. Yang pertama menyerang kebocoran konteks Spatie, yang kedua kebocoran keberadaan data. Keduanya ditulis lebih dulu, sebelum ada fitur lain.
Di server (setelah deploy, dengan dua akun nyata): skenario 1, 3, 6, dan 8 dijalankan manual sebagai uji asap, karena yang diuji di sini adalah konfigurasi server, bukan logika.
Urutan pengerjaan: Tahap 0 dan 1
Empat belas langkah dikerjakan berurutan; langkah 1 sampai 6 adalah Tahap 0, langkah 7 sampai 14 adalah Tahap 1. Uji yang paling berisiko (langkah 7) sengaja dikerjakan sebelum fitur akses lainnya, dan hanya langkah 3, 4, 5, dan 14 yang menyentuh server.
No
Tempat
Pekerjaan
1
hPanel dan lokal
Selesaikan seluruh daftar prasyarat
2
Lokal
Buat repositori dan proyek Laravel 13 kosong dengan satu halaman "Halo"; commit dan push
3
Lokal, lalu server
Tulis deploy/index.hostinger.php dan deploy/deploy.sh di lokal; jalankan deploy pertama di server sampai halaman terbuka di https://projeku.<domain-induk>
4
Server
Pasang cron dan tugas detak jantung; buktikan lima menit baris log berurutan
5
Server
Kirim email uji ke Gmail; pastikan SPF, DKIM, DMARC berstatus PASS dan email tidak masuk spam
6
Lokal
Unduh satu cadangan basis data dari server, pulihkan di lokal, dan jalankan aplikasi terhadapnya
7
Lokal
Uji awal Spatie teams: pasang paket dengan konfigurasi minimal, tulis skenario 3 dan 6, dan buat keduanya lulus sebelum menulis hal lain
8
Lokal
Pasang Tailwind CSS 4, tulis berkas token tema gelap, komponen Blade dasar (x-button, x-input, x-card, x-icon), dan layout dasar
9
Lokal
Pasang Fortify dengan view login, lupa kata sandi, dan reset bertema gelap, tanpa registrasi; skenario 9 dan 10 lulus
10
Lokal
Tulis migrasi dan RolePermissionSeeder; skenario 14 lulus
11
Lokal
Tulis ProjectAccess dan daftar projek; skenario 1, 2, 4, dan 5 lulus
12
Lokal
Bangun halaman admin pengguna (buat akun, undangan, kirim ulang, nonaktifkan, aktifkan); skenario 8, 11, 12, dan 13 lulus
13
Lokal
Bangun kelola anggota projek dan aturan pemilik terakhir; skenario 7 lulus
14
Server
Deploy hasilnya dengan deploy.sh dan jalankan uji asap dengan dua akun nyata
Daftar periksa gerbang Tahap 0:
☐ deploy.sh berjalan dari awal sampai akhir tanpa campur tangan manual.
☐ Detak jantung tercatat tiap menit selama lima menit.
☐ Email uji mendarat di kotak masuk Gmail dengan SPF, DKIM, dan DMARC PASS.
☐ Cadangan sebelum migrasi terbentuk dan sekali berhasil dipulihkan di lokal.
☐ APP_KEY dan .my.cnf tersimpan salinannya di luar server.
Daftar periksa gerbang Tahap 1:
☐ Empat belas skenario uji akses lulus di lokal.
☐ Uji asap skenario 1, 3, 6, dan 8 lulus di server dengan dua akun nyata.
☐ Halaman login dan admin tampil dengan token tema gelap, dan tidak ada warna heksa literal di luar berkas token.
☐ Tidak ada rute registrasi, dan tidak ada Project::all() di kode.
☐ Akun admin pertama dibuat lewat kds:create-admin dan bisa login.
Ditunda dan tidak dibangun
Dua kelompok di bawah sengaja tidak masuk MVP; model data sudah siap menampung yang ditunda, jadi menambahkannya nanti tidak memerlukan penulisan ulang.
Ditunda, dibangun sesudah MVP:
Fitur
Catatan
Tombol Tambah ke kalender
Berkas .ics per kartu dan tautan Google Calendar; tanpa OAuth
Feed langganan kalender (webcal://)
Alamat rahasia per pengguna, bisa dicabut; isi minimal; dihitung ulang dari keanggotaan. Google menyegarkan langganan sekitar 12 sampai 24 jam, sehingga cocok untuk visibilitas, bukan alarm
Email undangan dengan lampiran .ics
Diuji dulu dengan satu akun Gmail
Templat dan duplikasi projek
Menurut penilaian, yang paling berguna di antara semua yang ditunda
Pencarian kartu dan halaman
FULLTEXT MySQL cukup
Halaman "Tugas saya" lintas projek, ekspor projek (ZIP), 2FA
Google Calendar lewat OAuth
Hanya bila bunyi alarm di kalender terbukti penting; biayanya tinggi (sinkronisasi, penyimpanan token, verifikasi aplikasi Google)
Sengaja tidak dibangun:
Gantt chart: godaan terbesar, biaya terbesar, nilai terkecil untuk alur kerja sekarang.
Time tracking dan otomasi.
Komentar dan log aktivitas: untuk tim kecil yang bisa saling bicara, menambah kerumitan akses dan notifikasi tanpa imbalan sebanding.
Multi-bahasa: antarmuka cukup berbahasa Indonesia.
Editor slide dan editor rich-text penuh.
Akses klien read-only: dicoret karena aplikasi hanya untuk pemilik dan tim.
Izin per halaman atau per kartu: solusi bila ada bahan yang tidak boleh dilihat anggota tertentu adalah memecah projek.
Realtime WebSocket: tidak mungkin di shared hosting; diganti polling ringan.
Riwayat versi halaman perencanaan juga ditunda; untuk saat ini peringatan "halaman berubah sejak kamu membukanya" (berbasis updated_at) sudah cukup.
Risiko dan hal yang perlu dicek
Risiko terbesar bukan di fitur, melainkan di hosting dan keamanan akses, jadi keduanya diuji paling awal.
Risiko
Dampak
Penanganan
Tahap
Akses SSH di plan Hostinger
Sudah dipastikan tersedia; tanpa SSH berarti deploy lewat File Manager, vendor diunggah manual
Terselesaikan: SSH tersedia di plan yang dipakai
0
Versi PHP web dan CLI berbeda
composer install menolak jalan
Samakan versi; uji di Tahap 0
0
Cron tidak berjalan
Pengingat tidak pernah terkirim
Log tiap menit sebagai bukti di Tahap 0
0
Email masuk spam
Alarm utama tidak sampai
SPF, DKIM, DMARC; uji ke Gmail di Tahap 0
0
Konteks projek Spatie terlupa atau mode teams salah
Akses bocor antarprojek
Mode teams sebelum migrasi pertama; satu lapisan ProjectAccess; uji otomatis
1
Gambar besar menghabiskan memori GD
Server error saat unggah
Cek piksel sebelum dekode; kecilkan di browser
2
Inode atau storage penuh
Unggahan gagal
Pantau jumlah berkas; siapkan pemindahan ke R2 lewat .env
2, 5
Polling membebani jatah proses
Hosting melambat
Berhenti saat tab tidak terlihat; melambat bila diam; respons 304
2
Migrasi salah di satu-satunya lingkungan
Data rusak
Cadangan otomatis sebelum migrate; uji lokal
0
Cadangan tidak pernah dipulihkan
Cadangan tidak bisa dipakai
Satu kali uji pemulihan sungguhan
5
Tahap 2 terlalu besar
Jadwal melar
Pecah menjadi 2a dan 2b
2
Batas kirim email terlampaui
Pengingat tertahan atau pengiriman diblokir sementara
Jeda 30 detik antarpesan, plafon 50 email per hari, log dan peringatan ke admin; batas pasti dicek di hPanel
3
Sudah dipastikan (30 Oktober 2026), dan yang masih terbuka:
Dipastikan: plan Hostinger memiliki akses SSH.
Dipastikan: PHP minimal 8.4 tersedia; versi Laravel dan Spatie dipilih yang mendukungnya.
Dipastikan: alamat aplikasi adalah subdomain projeku; pengiriman email memakai jeda teraman (lihat bagian Pengingat email).
Terbuka: nama domain induk persis dan mailbox pengirim yang akan dibuat di Hostinger.
Terbuka: batas kirim email pasti untuk plan yang dipakai, dilihat di hPanel sebelum Tahap 3.
Titik yang paling mungkin dibantah: pemakaian Laravel untuk aplikasi pribadi dianggap berlebihan. Alasan mempertahankannya: aplikasi memegang data tim dan gambar yang tidak boleh bocor antarprojek, dan menulis auth sendiri membawa risiko lebih besar daripada overhead framework.
Status dan riwayat revisi
Status per 4 November 2026, berdasarkan laporan pemilik proyek (kodenya belum diperiksa oleh penyusun panduan):
Tahap 1 sampai 5 dilaporkan selesai dan teruji di lokal, dengan 45 tes otomatis lulus.
Bug seret kartu (SortableJS) sudah diperbaiki.
Aplikasi belum pernah di-deploy, jadi Tahap 0 belum terbukti di server.
Revisi 1 memperbaiki tampilan dan perilaku hasil pemakaian pertama di lokal.
Langkah berikutnya adalah rilis pertama ke server. Langkah 1 sampai 10 adalah gerbang rilis; langkah 11 menentukan apa yang dibangun sesudahnya.
No
Pekerjaan
Tempat
Selesai bila
1
Cek prasyarat: subdomain projeku, basis data, mailbox, SSH, PHP 8.4
hPanel
Kelimanya ada
2
Isi placeholder di deploy/index.hostinger.php dan deploy/deploy.sh, lalu push
Lokal
Repositori bersih, public/build ikut ter-commit
3
SSH, cek php -v, pasang deploy key, git clone
Server
~/kds-pm terisi
4
Buat .env, key:generate, ~/.my.cnf, composer install
Server
mysql -e "SELECT 1" mencetak 1
5
Jalankan deploy.sh
Server
Berakhir dengan Selesai: <stempel>
6
Seeder dan kds:create-admin, lalu login
Server dan browser
Bisa login
7
Cron dan detak jantung
hPanel dan server
Satu baris log baru tiap menit selama 5 menit
8
Cek GD dan WebP, kirim email uji ke Gmail
Server
imagewebp true; email di kotak masuk dengan SPF, DKIM, DMARC PASS
9
Uji asap: seret kartu, unggah gambar mendekati 10 MB, hapus dan pulihkan, pengingat nyata, cetak PDF, buka dari ponsel
Browser
Semuanya jalan tanpa galat di Console
10
Unduh satu cadangan, pulihkan di lokal; simpan salinan APP_KEY dan .my.cnf di luar server
Lokal
Aplikasi jalan terhadap cadangan itu
11
Pakai satu projek nyata satu sampai dua minggu dan catat gesekannya
Server
Daftar catatan pemakaian
Risiko khusus di server: header Content-Security-Policy dari middleware security headers dapat memblokir Alpine.js di produksi, sementara di lokal normal. Bila tombol atau seret tiba-tiba mati setelah deploy, cek Console untuk galat "Content Security Policy" lebih dulu. Keputusannya: longgarkan CSP, atau pindah ke build CSP Alpine.
Kandidat sesudah rilis, urutannya diputuskan dari catatan pemakaian: tombol Tambah ke kalender, templat dan duplikasi projek, pencarian, tes peramban otomatis untuk seret kartu, serta cadangan keluar server dan pemantauan umur detak jantung.
Riwayat revisi dokumen:
No
Tanggal
Perubahan
Alasan
0
30 Oktober 2026
Dokumen Desain dan Spesifikasi Tahap 0 dan 1 digabung menjadi Panduan Utama, ditambah gaya visual tema gelap
Satu acuan tunggal
1
4 November 2026
Garis lebih terang (--border, --border-strong), ukuran ikon lewat x-icon, perilaku ubah nama kolom, simpan eksplisit di detail kartu
Temuan pemakaian pertama di lokal