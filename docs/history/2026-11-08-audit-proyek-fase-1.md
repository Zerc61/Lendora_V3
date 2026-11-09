# Audit Proyek Lendora V3 — Fase 1

Tanggal: 2026-11-08
Jenis pekerjaan: audit (tanpa perubahan kode)
Status: selesai, menunggu persetujuan sebelum implementasi redesign

## Tujuan

Memetakan kondisi nyata aplikasi sebelum redesign UI:

1. Inventaris halaman untuk lima role resmi.
2. Inventaris seluruh mockup dan pemetaannya ke implementasi.
3. Analisis ketergantungan backend untuk setiap layar mockup.
4. Daftar inkonsistensi desain.
5. Roadmap implementasi bertahap.

## Perubahan

Tidak ada perubahan kode, database, aset desain, atau konfigurasi.
Dokumen ini dan dua dokumen pendamping adalah satu-satunya artefak baru.

## Ruang Lingkup Pemeriksaan

- `routes/web.php` — peta route dan middleware role.
- `app/Http/Controllers/{Admin,Borrower}` — 14 + 5 controller.
- `app/Models` — 13 model: Role, Organization, User, Category, Asset, AssetPhoto,
  Booking, BookingItem, Handover, Inspection, Maintenance, ActivityLog, Setting.
- `app/Services` — 8 service: ActivityLogger, AssetService, AvailabilityService,
  BookingService, HandoverService, InspectionService, MaintenanceService,
  SettingService.
- `app/Policies`, `app/Http/Requests`, `app/Http/Middleware`.
- `resources/views` — 61 file Blade.
- `public/css` — lendora.css, admin.css, borrower.css, ldr-login.css,
  onboarding.css.
- `public/js` — borrower.js, admin.js, onboarding.js.
- `public/Design/mockup` — 49 folder dengan `code.html`, 46 `screen.png`.
- `public/Design/logo` dan `public/images` — aset logo.
- `docs/Design.md`, `docs/rencana/README.md`,
  `public/Design/mockup/lendora_v3_struktur_menu_resmi_per_role_rbac.md`,
  `public/Design/mockup/lendora_v3_blade_implementation_guide.md`,
  `public/Design/mockup/lendora_campus_gear/DESIGN.md`.
- `tests/` — suite PHPUnit.

Tidak dilakukan: verifikasi visual aplikasi berjalan (tidak ada browser
headless terpasang), UAT manual per role, dan pengujian beban.

## Kondisi Implementasi

- Laravel 12, Blade, CSS statis, tanpa build step.
- Database lokal MariaDB `lendora`; migrasi berjalan sampai
  `0015_add_notification_preferences_to_users_table`.
- Route hanya mencakup MVP: auth, borrower, dan satu area `/admin` untuk empat
  role staf.
- Tidak ada route atau tabel untuk pembayaran/denda, kiosk, suku cadang,
  kalibrasi, kuota prodi, dan analitik semesteran.
- Layout staf tunggal (`layouts/admin.blade.php`) dipakai semua role staf;
  perbedaan role hanya pada visibilitas menu.
- Layout borrower memakai bottom navigation 5 tab dan lebar maksimum ponsel.

## Inventaris Halaman per Role

### 1. Borrower

| Halaman | Route | View | Status |
|---|---|---|---|
| Beranda | `borrower.home` | `borrower/home.blade.php` | Kerangka kosong: judul, pengumuman, peminjaman aktif, daftar rekomendasi tanpa komponen visual |
| Katalog | `borrower.catalog.index` | `borrower/catalog/index.blade.php` | Ada |
| Detail aset | `borrower.catalog.show` | `borrower/catalog/show.blade.php` | Ada |
| Daftar peminjaman | `borrower.bookings.index` | `borrower/bookings/index.blade.php` | Ada |
| Form peminjaman | `borrower.bookings.create` | `borrower/bookings/create.blade.php` | Ada |
| Detail peminjaman | `borrower.bookings.show` | `borrower/bookings/show.blade.php` | Ada |
| Notifikasi | `borrower.notifications.index` | `borrower/notifications/index.blade.php` | Ada |
| Profil | `borrower.profile.edit` | `borrower/profile/edit.blade.php` | Ada |
| Onboarding | `/` (publik) | `welcome.blade.php` | Ada, memakai `onboarding.css` + `onboarding.js` |

Tidak ada halaman borrower untuk pembayaran denda (M11), pusat bantuan (B9),
QR/PDF pengembalian, atau perpanjangan pinjam (B6).

### 2. Operator

| Halaman | Route | View | Status |
|---|---|---|---|
| Dashboard | `admin.dashboard` | `admin/dashboard.blade.php` | Template generik yang sama untuk semua role staf |
| Antrean persetujuan | `admin.bookings.index` | `admin/bookings/index.blade.php` | Ada, filter status |
| Detail booking | `admin.bookings.show` | `admin/bookings/show.blade.php` | Ada |
| Kalender ketersediaan | `admin.bookings.calendar` | `admin/bookings/calendar.blade.php` | Ada, kalender bulanan; bukan Gantt |
| Serah terima | `admin.bookings.handover`, `admin.handovers.index` | `admin/bookings/handover.blade.php`, `admin/handovers/index.blade.php` | Ada |
| Inspeksi | `admin.inspections.*` | `admin/inspections/*` | Ada (index, create) |
| Maintenance | `admin.maintenances.*` | `admin/maintenances/*` | Ada (index, create, store) |
| Aset (baca saja) | `admin.assets.*` | `admin/assets/*` | Route mengizinkan operator; policy memblokir store/update |
| Laporan | `admin.reports.index` | `admin/reports/index.blade.php` | Ada |
| Notifikasi, profil, pencarian | `admin.notifications.*`, `admin.profile.*`, `admin.search` | sesuai | Ada, fitur tambahan di luar mockup |

Tidak ada kiosk scanner (O1), BAP kerusakan/klaim denda (O3), atau kiosk antrean
(O4).

### 3. Organization Admin

| Halaman | Route | View | Status |
|---|---|---|---|
| Dashboard | `admin.dashboard` | `admin/dashboard.blade.php` | Sama dengan role lain |
| Aset | `admin.assets.*` | `admin/assets/*` | CRUD penuh |
| Kategori | `admin.categories.*` | `admin/categories/*` | CRUD penuh |
| Booking | `admin.bookings.*` | `admin/bookings/*` | Ada |
| Serah terima | `admin.handovers.index` | `admin/handovers/index.blade.php` | Ada |
| Inspeksi | `admin.inspections.*` | `admin/inspections/*` | Ada |
| Maintenance | `admin.maintenances.*` | `admin/maintenances/*` | CRUD penuh |
| Pengguna & role | `admin.users.*` | `admin/users/*` | Ada |
| Laporan | `admin.reports.*` | `admin/reports/index.blade.php` | Ada + ekspor |
| Pengaturan | `admin.settings.*` | `admin/settings/edit.blade.php` | Ada |
| Log aktivitas | `admin.activity-logs.*` | `admin/activity-logs/index.blade.php` | Ada + ekspor |

### 4. Admin Prodi

| Halaman | Route | View | Status |
|---|---|---|---|
| Dashboard | `admin.dashboard` | `admin/dashboard.blade.php` | Sama dengan role lain; tidak ada blok khusus prodi |
| Antrean persetujuan | `admin.bookings.index`, `admin.bookings.approve` | `admin/bookings/*` | Route mengizinkan prodi menyetujui booking |
| Laporan | `admin.reports.*` | `admin/reports/index.blade.php` | Ada, laporan operasional umum |
| Aset (menu tampil) | `admin.assets.index` | — | **403**: route dibatasi `operator,organization_admin` |
| Kategori (menu tampil) | `admin.categories.index` | — | **403**: route dibatasi `organization_admin` |

Tidak ada data atau tampilan untuk kuota/utilisasi mata kuliah (P3), kalender
praktikum/studio (P4), skor kepatuhan (P5), laporan semesteran/RAB (P6), atau
verifikasi portofolio (P2).

### 5. Technician

| Halaman | Route | View | Status |
|---|---|---|---|
| Dashboard | `admin.dashboard` | `admin/dashboard.blade.php` | Generik; tidak ada workbench |
| Inspeksi | `admin.inspections.index`, `create`, `store` | `admin/inspections/*` | Ada |
| Maintenance | `admin.maintenances.index` | `admin/maintenances/index.blade.php` | Ada |
| Mulai tugas | `admin.maintenances.start` | — | Aksi POST |
| Selesaikan tugas | `admin.maintenances.finish`, `finish.store` | `admin/maintenances/finish.blade.php` | Ada |

Tidak ada akses ke aset (`/admin/assets` dibatasi `operator,organization_admin`)
meskipun matriks izin menyebut teknisi boleh melihat aset dan mengubah kondisi.
Tidak ada work order board (workbench), inventaris suku cadang (T3), atau
kalibrasi (T4).

## Temuan Defect dan Risiko

### Tinggi

1. **Link mati di sidebar Admin Prodi.** Menu "Aset" dan "Kategori" tampil,
   tetapi route-nya menolak role `admin_prodi` sehingga menghasilkan 403.
   Lokasi: `resources/views/layouts/admin.blade.php` (daftar `roles` menu) vs
   `routes/web.php`.
2. **Beranda borrower belum didesain.** `borrower/home.blade.php` hanya memakai
   elemen HTML dasar dan satu `x-empty-state`; tidak sesuai mockup M3/B1.
3. **Satu dashboard untuk empat role staf.** Mockup W1 (operator), W2 (org
   admin), P1 (admin prodi), dan workbench teknisi berbeda struktur, tetapi
   implementasi hanya satu template dengan blok kondisional.
4. **Pemisahan area role belum ada.** Semua role staf berbagi sidebar, topbar,
   dan gaya yang sama. Mockup mendefinisikan navigasi dan pengalaman berbeda.
5. **Fitur mockup tanpa backend.** Enam kelompok fitur tidak punya route,
   controller, tabel, atau model (rincian di dokumen pemetaan).

### Sedang

6. **Kelas CSS terpakai tanpa definisi.** Diverifikasi dengan pencarian silang:
   `ldr-table__actions-head` (7 view), `ldr-table--bookings`,
   `ldr-timeline__content`, `ldr-onboarding__slides`, `ldr-auth__badge`,
   `ldr-auth__brand-name`, `ldr-auth__brand-row`, `ldr-auth__mark`.
7. **Inline style** di `admin/bookings/handover.blade.php` (1) dan
   `admin/reports/index.blade.php` (4). Melanggar aturan CSS proyek (P5).
8. **Sistem visual login tidak konsisten.** Login/register memakai
   `ldr-login.css` dengan split panel, gradient, dan palet gelap; halaman
   forgot/reset/confirm/verify memakai `ldr-auth` terpusat yang lebih lama.
9. **Gradient dan warna hardcoded.** `ldr-login.css` memakai 4 gradient latar,
   2 gradient overlay, 2 gradient tombol, serta banyak warna hardcoded. Bertentangan
   dengan arah desain "orange + putih, tanpa gradient/glow/glassmorphism".
10. **Logo tidak dipakai konsisten.** Appbar/sidebar memakai ikon + teks
    "Lendora"; login/register/welcome memakai file di `public/images/`. File
    tersebut identik byte-per-byte dengan `public/Design/logo/` (duplikasi).
11. **Matriks izin vs route tidak sinkron.** Teknisi tidak bisa membuka aset
    atau memperbarui kondisi karena seluruh resource aset dibatasi
    `operator,organization_admin`. Operator bisa membuka form aset tetapi
    ditolak policy saat menyimpan (perilaku ini sudah tercakup tes).
12. **Belum ada verifikasi visual aplikasi berjalan.** Tidak ada browser
    headless; penilaian tampilan berbasis kode dan screenshot mockup saja.

### Rendah

13. Folder aneh `public/Design/mockup/lendora_favicon_180_siklus.png/` hanya
    berisi `screen.png`.
14. B1–B4 tidak punya `screen.png` (hanya `code.html`).
15. Branding logo masih berstatus draf menurut README folder logo.

## Analisis Ketergantungan Backend

Klasifikasi pekerjaan redesign:

| Kebutuhan | Cukup frontend? | Backend dibutuhkan |
|---|---|---|
| Layout dan navigasi per role staf | Ya, jika memakai route yang ada | Tidak |
| Dashboard operator (W1) | Sebagian | Data ringkasan per role; belum tentu query baru |
| Dashboard org admin (W2) | Sebagian | Idem |
| Dashboard prodi (P1) | Tidak | Agregasi kuota/utilisasi per prodi |
| Workbench teknisi | Sebagian | Daftar work order milik teknisi; filter status |
| Perbaikan beranda borrower (M3/B1) | Ya | Tidak, variabel sudah tersedia |
| Login/register selaras desain | Ya | Tidak |
| Antrean otorisasi khusus (P2) | Tidak | Aturan approval tingkat dua + data portofolio |
| Kuota/utilisasi MK (P3) | Tidak | Model prodi/mata kuliah/kuota |
| Kalender praktikum/studio (P4) | Tidak | Model jadwal praktikum dan studio |
| Skor kepatuhan (P5) | Tidak | Perhitungan skor |
| Laporan semesteran/RAB (P6) | Tidak | Agregasi semester + data RAB |
| Suku cadang (T3) | Tidak | Tabel stok suku cadang |
| Kalibrasi (T4) | Tidak | Tabel jadwal/sertifikat kalibrasi |
| Kiosk (O1, O4) | Tidak | Endpoint scan/antrean + perangkat |
| BAP/denda (O3, M11) | Tidak | Modul pembayaran/denda (di luar MVP) |
| QR/PDF/perpanjangan (B6) | Tidak | Pembuatan dokumen + aturan perpanjangan |
| Pusat bantuan/regulasi (B9) | Bisa statis | Tidak, jika konten statis disetujui |

Kesimpulan: redesign visual role yang sudah punya route dapat dikerjakan tanpa
mengubah backend. Semua fitur baru di atas memerlukan persetujuan backend
terpisah dan tidak boleh dicampur ke pekerjaan frontend.

## Inkonsistensi Desain (Ringkas)

- Dua sistem auth berbeda dalam satu aplikasi.
- Login memakai gradient dan palet gelap; area lain memakai orange/putih.
- Empat role staf memakai satu shell visual.
- Aksen dan badge status konsisten di area admin, tetapi beranda borrower belum
  memakai sistem komponen.
- Borrow jadi: mockup B1–B9 mendeskripsikan portal desktop, sedangkan
  `layouts/borrower.blade.php` mengunci lebar ponsel dan bottom navigation.
  Arah ini harus diputuskan sebelum implementasi.
- Ikon memakai komponen `x-icon`; mockup memakai ikon yang sama keluarga
  (Lucide), jadi tidak perlu aset baru.
- Tipografi sudah sesuai: Plus Jakarta Sans untuk heading, Inter untuk body.
- Token warna utama sudah sesuai: `#F97316`, `#EA580C`, `#FFF7ED`, `#FFFFFF`,
  `#F5F5F5`, `#262626`, `#E5E7EB`.

## Roadmap Usulan

Urutan berdasarkan risiko dan ketergantungan:

1. **Perbaikan cepat (tanpa backend):** hapus menu 403 prodi, definisikan 8
   kelas CSS hilang, pindahkan inline style ke CSS, samakan sistem auth.
2. **Fondasi visual:** satu set token dan komponen bersama, layout staf per
   role (sidebar/menu berbeda), layout borrower final.
3. **Borrower:** beranda M3/B1, katalog, detail, form, daftar, detail booking,
   notifikasi, profil. Sesuaikan dengan keputusan mobile vs desktop.
4. **Operator:** dashboard W1, antrean W6, detail W7, handover W8, inspeksi W9,
   maintenance W10, kalender O2.
5. **Organization Admin:** dashboard W2, aset W3/W4, kategori W5, pengguna W11,
   laporan W12, pengaturan W13.
6. **Technician:** dashboard/workbench, daftar tugas, detail dan penyelesaian.
7. **Admin Prodi:** dashboard P1, antrean P2, laporan P3–P6 — hanya setelah
   backend disetujui.
8. **Di luar MVP:** M11, O1, O3, O4, T3, T4, B9, B6 — ditunda sampai ada
   persetujuan eksplisit.

Setiap tahap wajib menghasilkan dokumen history dan hasil tes nyata.

## Verifikasi

Perintah:

```
php artisan test --compact
```

Hasil nyata:

```
  ............................................................................
  .....................................................

  Tests:    129 passed (454 assertions)
  Duration: 8.77s
```

Catatan: suite berjalan di SQLite in-memory sesuai `phpunit.xml`, bukan
MariaDB. Tidak ada tes baru pada audit ini.

## File Tersentuh

Dibuat:

- `docs/history/2026-11-08-audit-proyek-fase-1.md`
- `docs/history/2026-11-08-inventaris-mockup.md`
- `docs/history/2026-11-08-pemetaan-mockup-implementasi.md`

Tidak ada file kode yang diubah.

## Risiko / Catatan Lanjutan

- Audit ini tidak menyertakan UAT manual atau verifikasi visual browser.
- Keputusan yang masih terbuka:
  1. Varian logo final (branding masih draf).
  2. Borrower: pertahankan layout ponsel atau ikuti portal desktop B1–B9.
  3. Fitur di luar MVP: tunda atau setujui pekerjaan backend.
  4. Apakah Admin Prodi tetap melihat menu aset/kategori sebagai baca-saja
     (butuh perubahan route/policy) atau menu dihapus.
- Jangan mulai implementasi sebelum keputusan di atas dan roadmap disetujui.
