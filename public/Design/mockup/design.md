# Lendora V3 — Design Document (UI/UX & Design System)

> Dokumen desain untuk **Lendora V3**, platform peminjaman & pengelolaan inventaris
> peralatan (fokus awal: peralatan kamera) berbasis **Laravel 12 + MariaDB**.
> Dokumen ini melengkapi *Rencana Pengembangan Projek Lendora V3* (PDF 31 hlm.)
> dan menjadi rujukan tunggal untuk dua keperluan:
>
> 1. **Membuat mockup di Google Stitch** — gunakan bersama `stitch-prompts.md`.
> 2. **Implementasi frontend Blade** — token & komponen di sini menjadi acuan CSS.

| Item | Nilai |
|---|---|
| Produk | Lendora V3 — platform peminjaman & inventaris peralatan |
| Stack frontend | Blade template + CSS murni (tanpa Vue/React/Inertia/Livewire) [USULAN-disetujui] |
| Arah visual | Clean Professional, keluarga warna oranye–putih [FINAL] |
| Warna utama | Vibrant Orange `#F97316` [FINAL] |
| Dua area pengalaman | Borrower (mobile-first, bottom nav) · Admin (desktop, sidebar) [FINAL] |
| Bahasa UI | Indonesia (label & pesan); nama tabel/kolom/kode tetap Inggris |

**Penanda status:** `[FINAL]` = dikunci panduan proyek · `[USULAN]` = usulan dokumen rencana yang diperlakukan aktif secara default · `[DESAIN]` = keputusan desain baru pada dokumen ini (bebas diubah, asal konsisten).

---

## 1. Prinsip Desain

1. **Clean Professional `[FINAL]`** — putih dan netral mendominasi halaman; oranye
   hanya untuk aksi utama, elemen aktif, dan penanda identitas. Tanpa gradient,
   glow, glassmorphism, atau dekorasi berlebihan.
2. **Asset-first** — foto unit, kode aset, dan status selalu terlihat. Setiap kartu
   aset wajib menampilkan foto utama; unit tanpa foto memakai placeholder ikon kamera.
3. **Status selalu terlihat** — setiap transaksi dan aset menampilkan badge status
   dengan warna yang konsisten di seluruh aplikasi (Bagian 3).
4. **Dua dunia, dua pola** — borrower memakai kartu + daftar + bottom navigation;
   admin memakai tabel padat + filter + sidebar. Keduanya tidak berbagi layout,
   tetapi berbagi token warna, tipografi, ikon, dan komponen dasar `[FINAL]`.
5. **Bahasa manusia** — label UI berbahasa Indonesia yang jelas ("Menunggu
   Persetujuan", bukan "pending"). Kode transaksi tetap ditampilkan apa adanya
   (`LDR-BK-2026-0001`) dengan font monospace.
6. **Mobile-first untuk borrower, data-dense untuk admin** — target sentuh minimal
   44px di sisi borrower; kepadatan informasi diutamakan di sisi admin.

---

## 2. Design Tokens

### 2.1 Warna

**Warna inti `[FINAL]`:**

| Token | Hex | Penggunaan |
|---|---|---|
| `--color-primary` | `#F97316` | Aksi utama, elemen aktif, identitas brand |
| `--color-primary-dark` | `#EA580C` | Hover/active tombol utama, penekanan |
| `--color-orange-tint` | `#FFF7ED` | Latar lembut untuk highlight/banner brand |
| `--color-white` | `#FFFFFF` | Latar utama dan permukaan kartu |
| `--color-surface` | `#F5F5F5` | Latar sekunder (area konten, baris tabel hover) |
| `--color-text` | `#262626` | Teks utama |
| `--color-border` | `#E5E7EB` | Garis pemisah dan batas komponen |

**Warna pelengkap `[DESAIN]`:**

| Token | Hex | Pakai bg tint | Penggunaan |
|---|---|---|---|
| `--color-text-muted` | `#6B7280` | — | Teks sekunder, placeholder, meta |
| `--color-success` | `#16A34A` | `#F0FDF4` | Status positif (Tersedia, Selesai) |
| `--color-warning` | `#D97706` | `#FFFBEB` | Status menunggu (Pending, Berjalan) |
| `--color-danger` | `#DC2626` | `#FEF2F2` | Ditolak, rusak, terlambat, hapus |
| `--color-info` | `#0284C7` | `#F0F9FF` | Disetujui, sedang dipinjam, terjadwal |
| `--color-violet` | `#7C3AED` | `#F5F3FF` | Direservasi (approved, belum diambil) |

Dark mode **tidak termasuk** MVP (masuk pengembangan lanjutan bila disetujui).

### 2.2 Tipografi `[DESAIN]`

- **Heading:** Plus Jakarta Sans (fallback: Inter, system-ui).
- **Body:** Inter (fallback: system-ui, sans-serif).
- Muat via Google Fonts: `family=Plus+Jakarta+Sans:wght@500;600;700;800` dan
  `family=Inter:wght@400;500;600;700`.

| Gaya | Ukuran/tinggi | Bobot | Pakai |
|---|---|---|---|
| Display | 28/36 | 800 | Judul hero mobile (Beranda) |
| H1 | 24/32 | 700 | Judul halaman |
| H2 | 20/28 | 600 | Judul seksi |
| H3 | 17/24 | 600 | Judul kartu, judul form section |
| Body | 15/24 | 400 | Teks umum |
| Small | 13/20 | 400 | Meta, helper, isi badge |
| Caption | 12/16 | 600, uppercase, letter-spacing 0.04em | Label kecil, overline |

Aturan: angka memakai `font-variant-numeric: tabular-nums` untuk kode, tanggal,
jam, dan uang; kode transaksi/aset memakai monospace (`ui-monospace, 'JetBrains
Mono', 'Courier New'`).

### 2.3 Spacing, Radius, Elevation, Ikon `[DESAIN]`

- **Grid 4/8px.** Skala spacing: 4, 8, 12, 16, 20, 24, 32, 40, 48.
- **Radius:** input & tombol 10px · kartu 14px · modal 16px · badge pill penuh.
- **Bayangan (hanya 2 level):**
  - `--shadow-sm: 0 1px 2px rgba(0,0,0,.06)` — kartu, permukaan.
  - `--shadow-md: 0 4px 12px rgba(0,0,0,.08)` — modal, dropdown, bottom sheet.
- **Ikon:** Lucide, stroke 1.75; ukuran 20px (in-UI) dan 24px (bottom nav / sidebar).

### 2.4 Breakpoint

| Range | Sifat |
|---|---|
| `< 768px` | Borrower utama (mobile-first); admin tetap bisa dibuka namun bukan target |
| `768–1023px` | Transisi; admin sidebar collapsible |
| `≥ 1024px` | Admin utama; lebar nyaman 1280px |
| Desktop membuka area borrower | Konten dibatasi `max-width: 480px`, di tengah |

---

## 3. Status & Badge (pemetaan resmi)

Badge = pill kecil (Small 13/20, bobot 600), teks + latar tint. Pemetaan ini
wajib dipakai konsisten di seluruh layar, mockup Stitch, dan implementasi.

### 3.1 Status transaksi — `bookings.status`

| Nilai DB | Label UI | Warna |
|---|---|---|
| `pending` | Menunggu Persetujuan | Warning |
| `approved` | Disetujui | Info |
| `active` | Dipinjam | Success |
| `returned` | Dikembalikan | Netral (surface + text-muted) |
| `rejected` | Ditolak | Danger |
| `cancelled` | Dibatalkan | Netral (surface + text-muted) |

### 3.2 Status aset — `assets.status`

| Nilai DB | Label UI | Warna |
|---|---|---|
| `available` | Tersedia | Success |
| `reserved` | Direservasi | Violet |
| `borrowed` | Dipinjam | Info |
| `maintenance` | Maintenance | Warning |
| `retired` | Nonaktif | Netral |

### 3.3 Status item — `booking_items.status`

| Nilai DB | Label UI | Warna |
|---|---|---|
| `pending` | Menunggu | Warning |
| `approved` | Disetujui | Info |
| `picked_up` | Diambil | Violet |
| `returned` | Kembali | Success |
| `damaged` | Rusak | Danger |
| `lost` | Hilang | Danger |

### 3.4 Status lain

| Tabel | Nilai → Label → Warna |
|---|---|
| `maintenances.status` | `scheduled` Terjadwal → Info · `in_progress` Berjalan → Warning · `done` Selesai → Success · `cancelled` Dibatalkan → Netral |
| `inspections.result` | `ok` Baik → Success · `needs_repair` Perlu Perbaikan → Warning · `damaged` Rusak → Danger |
| `assets.condition_status` | `good` Baik → Success · `minor_damage` Kerusakan Ringan → Warning · `damaged` Rusak → Danger · `lost` Hilang → Danger |
| `users.status` | `active` Aktif → Success · `suspended` Ditangguhkan → Danger |

---

## 4. Komponen Inti

### 4.1 Tombol

| Varian | Tampilan | Pakai |
|---|---|---|
| Primary | Latar `#F97316`, teks putih; hover `#EA580C` | Satu aksi utama per layar (Ajukan, Setujui, Simpan) |
| Secondary | Latar putih, border `#E5E7EB`, teks `#262626` | Aksi sekunder (Batal, Filter) |
| Ghost | Teks oranye, tanpa latar | Aksi ringan (Batalkan Pengajuan) |
| Danger | Latar `#DC2626`, teks putih | Hapus, Tolak, Tangguhkan |

Tinggi: 44px (mobile/borrower), 38px (admin desktop). Radius 10px. Disabled:
opacity 40% + cursor not-allowed. Setiap tombol punya state hover, active,
focus-visible (ring oranye 2px offset 2px), dan loading (spinner + teks).

### 4.2 Kartu Aset (borrower)

- Foto rasio 4:3, object-fit cover, radius atas 14px; tanpa foto → placeholder ikon.
- Baris judul: nama (H3) + kode aset (mono, caption).
- Chip kategori kecil (orange-tint) + badge status aset di kanan atas foto.
- Meta satu baris: kondisi terakhir ("Baik") + lokasi singkat.
- Ukuran grid 2 kolom di mobile, 4 kolom di desktop fallback.

### 4.3 Kartu Booking (borrower & antrean admin)

- Header: kode booking (mono) + badge status.
- Baris tanggal: `12–14 Nov 2026` + chip sisa waktu ("Kembalikan besok!" saat H-1).
- Item ringkas: thumbnail 40px + nama unit (maks 2 item, sisanya "+3 lainnya").
- Mini progress: 5 titik timeline (Diajukan → Disetujui → Diambil → Dikembalikan → Selesai).
- Aksi kontekstual: Batalkan (saat pending), Lihat Detail.

### 4.4 Badge & chip

Badge status (Bagian 3); chip filter (pill outline, aktif = orange-tint + teks
primary); chip kategori (orange-tint). Ukuran compact 24px tinggi.

### 4.5 Form

- Label di atas input (Small, 600), wajib ditandai `*`.
- Input tinggi 44px (borrower) / 38px (admin), radius 10px, border `#E5E7EB`,
  fokus: border oranye + ring tint.
- Helper text abu-abu; error teks danger + border danger, pesan selalu spesifik
  (contoh: "Unit LDR-CAM-0001 sudah dibooking 12–14 Nov — pilih tanggal lain").
- Date range: dua field (Tanggal Mulai / Tanggal Selesai) + strip mini kalender
  ketersediaan 14 hari di detail aset.

### 4.6 Tabel data (admin)

- Header sticky, latar surface, teks caption uppercase.
- Baris: tinggi 52px, pemisah border; hover surface.
- Aksi baris berupa ikon (lihat/edit/hapus) dengan tooltip.
- Filter bar di atas tabel: pencarian + dropdown filter + chip filter aktif.
- Pagination angka + info "Menampilkan 1–15 dari 82".
- Sel status selalu badge; sel uang & tanggal rata kanan.

### 4.7 Timeline status booking

5 langkah: **Diajukan → Disetujui → Diambil → Dikembalikan → Selesai**.
Langkah tercapai = titik oranye terisi; langkah aktif = titik oranye berdenyut;
langkah gagal (Ditolak/Dibatalkan) = titik merah + label di ujung.
Versi admin menampilkan waktu tiap langkah.

### 4.8 Empty / Loading / Error

- **Empty state:** ikon + judul + 1 kalimat + CTA. Contoh: "Belum ada peminjaman
  — Cari peralatan di katalog" + tombol "Buka Katalog".
- **Loading:** skeleton berbentuk kartu/baris (bukan spinner penuh halaman).
- **Error:** banner danger di atas konten + tombol "Coba lagi".

### 4.9 Toast & modal

- Toast kanan-bawah (desktop) / atas (mobile), auto hilang 4 detik; sukses =
  garis kiri success, gagal = danger.
- Modal konfirmasi untuk aksi berisiko (Tolak booking, Hapus aset, Tangguhkan
  akun): judul + 1 kalimat konsekuensi + tombol danger/primary.

---

## 5. Navigasi

### 5.1 Borrower — bottom navigation (5 menu) `[USULAN-disetujui]`

Fixed bottom, latar putih, border atas; item: ikon 24px + label caption.
Item aktif: ikon + label oranye, titik penanda atas.

| Urutan | Label | Ikon (Lucide) |
|---|---|---|
| 1 | Beranda | `home` |
| 2 | Katalog | `search` |
| 3 | Peminjaman Saya | `clipboard-list` (badge jumlah aktif) |
| 4 | Notifikasi | `bell` (dot unread) |
| 5 | Profil | `user` |

### 5.2 Admin — sidebar (collapsible) `[USULAN-disetujui]`

Lebar 256px terbuka / 68px collapse; header brand + menu bergrup + footer user
(nama, role chip, keluar). Visibilitas item mengikuti role:

| Grup | Item | Route | Role yang melihat |
|---|---|---|---|
| — | Dashboard | `/admin` | Semua role admin |
| Inventaris | Aset | `/admin/assets` | org_admin (operator: lihat) |
| Inventaris | Kategori | `/admin/categories` | org_admin |
| Peminjaman | Antrean Persetujuan | `/admin/bookings?status=pending` | operator, org_admin, admin_prodi (lihat) |
| Peminjaman | Semua Transaksi | `/admin/bookings` | operator, org_admin, admin_prodi (lihat) |
| Peminjaman | Kalender Ketersediaan | `/admin/bookings/calendar` | operator, org_admin |
| Operasional | Serah Terima | `/admin/handovers` | operator |
| Operasional | Pemeriksaan | `/admin/inspections` | operator, technician |
| Operasional | Maintenance | `/admin/maintenances` | org_admin, technician; operator membuat tugas |
| Organisasi | Pengguna & Role | `/admin/users` | org_admin |
| Organisasi | Laporan & Analitik | `/admin/reports` | org_admin, admin_prodi |
| Organisasi | Pengaturan | `/admin/settings` | org_admin |

Admin Prodi melihat: Dashboard (varian monitoring), Antrean Persetujuan
(Persetujuan Khusus), Laporan. Technician melihat: Dashboard ringkas,
Maintenance (tugas miliknya), Pemeriksaan.

---

## 6. Spesifikasi Layar — Borrower (mobile)

> Format tiap layar: Tujuan · Struktur · Data & komponen · Aksi/state.

### 6.1 Login & Register (auth, tanpa bottom nav)
- **Tujuan:** masuk cepat; registrasi mandiri sebagai Borrower.
- **Struktur:** logo Lendora (wordmark oranye), judul sapaan, form, tautan bawah.
- **Komponen:** input email + password (login); nama, email, telepon, password
  (register); tombol Primary "Masuk"/"Buat Akun"; tautan "Lupa kata sandi?".
- **State:** error kredensial = banner danger; validasi inline per field.

### 6.2 Beranda (`/borrower`)
- **Tujuan:** ringkas status pinjaman berjalan + pintasan ke katalog.
- **Struktur:** header sapaan ("Halo, Ani") gaya Display; kartu "Peminjaman
  Aktif" (orange-tint, kode + unit + countdown due_at + tombol Detail); seksi
  "Rekomendasi untuk kamu" (scroll horizontal kartu aset); seksi "Pengumuman"
  (kartu teks dari pengaturan organisasi); pintasan kategori populer.
- **State:** tanpa pinjaman aktif → kartu CTA "Ajukan peminjaman pertamamu".

### 6.3 Katalog (`/borrower/catalog`)
- **Tujuan:** menemukan unit peralatan & melihat ketersediaan nyata.
- **Struktur:** search bar sticky; chip kategori horizontal (Semua, Kamera
  Mirrorless, Lensa, Tripod, Lampu); filter tanggal (opsional, bottom sheet);
  grid 2 kolom Kartu Aset; hasil count + urutan.
- **Data:** foto utama, nama, kode, badge status aset (Bagian 3.2) — unit
  `maintenance`/`retired` tidak muncul di katalog default.
- **State:** skeleton grid saat memuat; empty state "Tidak ditemukan — ubah
  filter atau kata kunci".

### 6.4 Detail Aset (`/borrower/catalog/{asset}`)
- **Tujuan:** meyakinkan borrower sebelum mengajukan.
- **Struktur:** galeri foto (swipe + dot indicator); nama + kode + chip kategori;
  badge status & kondisi; daftar spesifikasi (merek, model, deskripsi); strip
  kalender mini 14 hari (hijau = bebas, abu = terikat); kartu info lokasi;
  **sticky bottom bar** tombol Primary "Ajukan Peminjaman".
- **State:** unit bukan `available` → CTA dinonaktifkan dengan teks alasan
  ("Sedang dipinjam sampai 14 Nov").

### 6.5 Ajukan Peminjaman (halaman/modal dari Detail Aset)
- **Tujuan:** formulir sesingkat mungkin (selesai ≤ 60 detik).
- **Struktur:** ringkasan unit (thumbnail + nama + kode); Tanggal Mulai & Tanggal
  Selesai (date picker + validasi durasi ≤ `max_loan_days`); textarea "Tujuan
  peminjaman" (wajib); ringkasan aturan ("Maksimal 7 hari — dihitung otomatis").
- **Aksi:** Primary "Kirim Pengajuan" → toast sukses + pindah ke Detail Peminjaman.
- **State konflik:** jika rentang bertabrakan dengan booking `approved`/`active`,
  pengiriman ditolak dengan pesan yang menyebut unit, tanggal, dan kode booking
  yang bertabrakan + saran rentang bebas terdekat.

### 6.6 Peminjaman Saya (`/borrower/bookings`)
- **Tujuan:** memantau semua transaksi milik sendiri (bukan milik orang lain).
- **Struktur:** tab filter (Semua · Menunggu · Aktif · Selesai); daftar Kartu
  Booking; pull-to-refresh.
- **State:** empty per tab; kartu `pending` punya aksi "Batalkan" (ghost, dengan
  modal konfirmasi).

### 6.7 Detail Peminjaman (`/borrower/bookings/{id}`)
- **Tujuan:** status + rincian + jadwal dalam satu layar.
- **Struktur:** header kode + badge status; timeline status (Bagian 4.7) dengan
  waktu per langkah; kartu jadwal (mulai, selesai, batas kembali `due_at`);
  daftar item (thumbnail, nama, kondisi keluar/masuk bila ada); catatan
  serah terima; alasan penolakan tampil sebagai banner danger bila `rejected`.
- **Aksi:** "Batalkan Pengajuan" saat `pending`; hubungi operator (link telepon
  dari pengaturan organisasi) — opsional.

### 6.8 Notifikasi (`/borrower/notifications`)
- **Tujuan:** hasil keputusan, pengingat H-1, hasil inspeksi.
- **Struktur:** pengelompokan "Hari ini / Sebelumnya"; baris: ikon jenis + judul
  + potongan isi + waktu; dot biru untuk belum dibaca; tombol "Tandai semua
  dibaca". Menekan baris menandai dibaca & membuka objek terkait.

### 6.9 Profil (`/borrower/profile`)
- **Tujuan:** data diri & keamanan akun.
- **Struktur:** avatar inisial + nama + role chip "Borrower"; daftar menu: Data
  Diri (edit nama/telepon), Ubah Kata Sandi, Riwayat Ringkas (jumlah transaksi
  per status), Keluar (danger, konfirmasi).

---

## 7. Spesifikasi Layar — Admin Console (desktop)

### 7.1 Dashboard `/admin` (varian per role)
- **Operator:** 4 kartu statistik (Pengajuan Baru, Pickup Dijadwalkan Hari Ini,
  Jatuh Tempo Hari Ini, Terlambat) + tabel "Antrean Hari Ini" (kode, peminjam,
  unit, jadwal, status, aksi).
- **Organization Admin:** KPI (Utilisasi Aset %, Transaksi Aktif, Keterlambatan,
  Maintenance Berjalan) + grafik batang utilisasi per kategori + grafik garis
  transaksi 30 hari + daftar aktivitas terbaru (dari activity_logs).
- **Admin Prodi:** ringkasan utilisasi aset prodi + antrean Persetujuan Khusus.
- **Technician:** daftar tugas miliknya (Terjadwal/Berjalan) + pintasan
  Pemeriksaan.

### 7.2 Aset — daftar `/admin/assets` (org_admin; operator lihat)
Filter bar (pencarian nama/kode/serial, kategori, status, kondisi) + tabel:
thumbnail, kode (mono), nama, kategori, status badge, kondisi badge, lokasi,
aksi (lihat/edit/retire). Tombol "+ Tambah Aset" (primary). Pagination 15/hal.

### 7.3 Aset — form create/edit
Dua kolom: (1) Identitas — nama, kategori (select bertingkat), merek, model,
serial number, deskripsi; (2) Operasional — status (default available), kondisi,
lokasi, tanggal perolehan, harga perolehan (Rp utuh), catatan. Kartu Foto:
uploader drag-and-drop multi file, thumbnail grid, radio foto utama, urutan
drag. Simpan → validasi unik kode + serial.

### 7.4 Kategori `/admin/categories`
Daftar pohon (parent → sub) dengan expand/collapse; tiap baris: nama, slug,
jumlah aset (badge), aksi edit/hapus (hapus diblokir bila masih dipakai aset —
tampilkan pesan). Tombol "+ Tambah Kategori" dengan pilihan induk.

### 7.5 Antrean Persetujuan `/admin/bookings?status=pending`
Daftar booking `pending`: kode, peminjam, rentang tanggal, item (thumbnail +
kode), tujuan, **banner peringatan konflik** (danger) bila tertabrakan booking
`approved`/`active` — menyebut unit, tanggal, dan kode booking pemilik konflik.
Aksi: "Setujui" (primary) dan "Tolak" (danger, wajib isi alasan lewat modal).
Booking di luar batas kewenangan operator: tombol Setujui dinonaktifkan + teks
"Menunggu persetujuan Organization Admin / Admin Prodi".

### 7.6 Detail Booking (admin) `/admin/bookings/{id}`
Header: kode (mono besar) + badge status + tanggal. Panel kiri: data peminjam,
tujuan, catatan; tabel item (unit, kondisi keluar, kondisi masuk, status item).
Panel kanan: timeline status dengan waktu + aktor; riwayat handover; aksi
kontekstual sesuai status (Setujui / Catat Pickup / Catat Return / Batalkan).

### 7.7 Serah Terima `/admin/handovers` (operator)
Form satu layar: pilih booking (status `approved` untuk pickup, `active` untuk
return); toggle jenis Pickup/Return; checklist per item dengan field "Kondisi
keluar/masuk" (wajib) + catatan; kolom catatan umum; simpan → log handover +
transisi status sesuai Tabel 4.19 rencana.

### 7.8 Pemeriksaan `/admin/inspections` (operator, technician)
Form: unit (cari by kode/nama), konteks booking item (opsional), hasil radio
(Baik / Perlu Perbaikan / Rusak), catatan temuan. Di bawahnya: tabel riwayat
inspeksi terbaru. Hasil `needs_repair`/`damaged` → tugas maintenance terbentuk
dan aset berubah `maintenance`.

### 7.9 Maintenance `/admin/maintenances` (org_admin kelola; technician kerjakan)
Tabel tugas: aset, deskripsi ringkas, teknisi, status badge, biaya, mulai/
selesai. Detail (drawer/halaman): deskripsi lengkap, hasil, catatan. Aksi:
technician — "Mulai" / "Selesaikan" (isi hasil + biaya + kondisi akhir);
org_admin — buat/tugaskan/batalkan. Tugas `done` + kondisi baik → aset kembali
`available`.

### 7.10 Pengguna & Role `/admin/users` (org_admin)
Tabel: nama, email, telepon, role badge, status badge, aksi. Modal tambah
pengguna: nama, email, telepon, role (select 5 role), organisasi (bila role
non-borrower). Aksi: tangguhkan/aktifkan (konfirmasi), ubah role. Tidak ada
hapus fisik — gunakan penangguhan.

### 7.11 Laporan & Analitik `/admin/reports` (org_admin, admin_prodi)
Filter bar periode + kategori; KPI row; grafik batang utilisasi per kategori;
tabel rekap (kategori, total unit, hari dipinjam, utilisasi %); daftar aktivitas
borrower; daftar kerusakan (dari inspections). Tombol "Ekspor CSV".

### 7.12 Pengaturan `/admin/settings` (org_admin)
Seksi: (1) Data Organisasi — nama, kode, alamat, telepon, email; (2) Kebijakan
Peminjaman — `max_loan_days` (default 7), `operator_approval_limit` (Rp, default
5.000.000), teks pengumuman beranda borrower; (3) Notifikasi — pengingat H-1
on/off. Simpan per seksi + toast sukses.

---

## 8. Aturan Visual Khusus

- **Konflik jadwal** selalu ditampilkan sebagai banner danger berisi unit,
  rentang tabrakan, dan kode booking pemilik konflik — bukan pesan umum.
- **Pengingat H-1:** chip warning "Kembalikan besok!" pada kartu booking aktif
  (borrower) dan baris dashboard operator.
- **Uang:** format `Rp 12.500.000` (integer, tanpa desimal, pemisah titik).
- **Tanggal:** format Indonesia `12 Nov 2026` / `12–14 Nov 2026`.
- **Kode transaksi/aset:** selalu monospace, tidak pernah terpotong (ellipsis).

---

## 9. Aksesibilitas & Kualitas

- Kontras teks ≥ 4.5:1 (teks `#262626` di atas putih; teks putih di atas
  `#F97316` hanya untuk teks ≥ 15px bold — gunakan `#EA580C` bila butuh kontras).
- Target sentuh ≥ 44×44px di area borrower; jarak antar-target ≥ 8px.
- `:focus-visible` ring oranye 2px pada semua elemen interaktif.
- Status tidak pernah disampaikan lewat warna saja — badge selalu berlabel teks.
- Semua gambar aset punya `alt` (nama unit); form memakai label eksplisit.
- Bahasa UI Indonesia santai-profesional; hindari istilah teknis di pesan
  pengguna ("unit sedang dipinjam", bukan "asset status = borrowed").

---

## 10. Implementasi Token (CSS)

```css
:root {
  /* Inti [FINAL] */
  --color-primary: #F97316;
  --color-primary-dark: #EA580C;
  --color-orange-tint: #FFF7ED;
  --color-white: #FFFFFF;
  --color-surface: #F5F5F5;
  --color-text: #262626;
  --color-border: #E5E7EB;
  /* Pelengkap [DESAIN] */
  --color-text-muted: #6B7280;
  --color-success: #16A34A;  --color-success-bg: #F0FDF4;
  --color-warning: #D97706;  --color-warning-bg: #FFFBEB;
  --color-danger:  #DC2626;  --color-danger-bg:  #FEF2F2;
  --color-info:    #0284C7;  --color-info-bg:    #F0F9FF;
  --color-violet:  #7C3AED;  --color-violet-bg:  #F5F3FF;
  /* Tipografi */
  --font-heading: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
  --font-body: 'Inter', system-ui, sans-serif;
  --font-mono: ui-monospace, 'JetBrains Mono', 'Courier New', monospace;
  /* Radius & bayangan */
  --radius-input: 10px; --radius-card: 14px; --radius-modal: 16px;
  --shadow-sm: 0 1px 2px rgba(0,0,0,.06);
  --shadow-md: 0 4px 12px rgba(0,0,0,.08);
}
```

Jika suatu saat Tailwind disetujui (saat ini **belum** — Blade + CSS murni),
mapping token: `primary = orange-500`, `primary-dark = orange-600`,
`orange-tint = orange-50`, `surface = neutral-100`, `text = neutral-800`,
`border = gray-200`, `success = green-600`, `warning = amber-600`,
`danger = red-600`, `info = sky-600`, `violet = violet-600`.

---

## 11. Peta Layar → Prompt Stitch → Route

| Layar | Prompt Stitch | Route Laravel | Fase |
|---|---|---|---|
| Login / Register | M1, M2 | `/login`, `/register` | Fondasi |
| Beranda | M3 | `/borrower` | Phase 2 |
| Katalog + filter | M4 | `/borrower/catalog` | Phase 1–2 |
| Detail Aset | M5 | `/borrower/catalog/{asset}` | Phase 1–2 |
| Ajukan Peminjaman | M6 | POST `/borrower/bookings` | Phase 2 |
| Peminjaman Saya | M7 | `/borrower/bookings` | Phase 2 |
| Detail Peminjaman | M8 | `/borrower/bookings/{id}` | Phase 2 |
| Notifikasi | M9 | `/borrower/notifications` | Phase 3 |
| Profil | M10 | `/borrower/profile` | Fondasi |
| Dashboard admin | W1, W2 | `/admin` | Fondasi |
| Aset daftar + form | W3, W4 | `/admin/assets` | Phase 1 |
| Kategori | W5 | `/admin/categories` | Phase 1 |
| Antrean Persetujuan | W6 | `/admin/bookings?status=pending` | Phase 2 |
| Detail Booking admin | W7 | `/admin/bookings/{id}` | Phase 2–3 |
| Serah Terima | W8 | `/admin/handovers` | Phase 3 |
| Pemeriksaan | W9 | `/admin/inspections` | Phase 3 |
| Maintenance | W10 | `/admin/maintenances` | Phase 3 |
| Pengguna & Role | W11 | `/admin/users` | Phase 3 |
| Laporan | W12 | `/admin/reports` | Phase 3 |
| Pengaturan | W13 | `/admin/settings` | Phase 3 |
