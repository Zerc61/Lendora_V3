# Pemetaan Mockup ke Implementasi

Tanggal: 2026-11-08
Jenis pekerjaan: audit (tanpa perubahan kode)
Status: selesai

## Tujuan

Memetakan setiap mockup ke route, controller, view, dan status implementasi
nyata. Dokumen ini mencegah redesign membangun layar yang tidak punya backend.

## Perubahan

Tidak ada perubahan kode. Hanya dokumen ini yang dibuat.

## Legenda Status

| Status | Arti |
|---|---|
| Sesuai | Route, controller, view, dan alur utama ada; selisih hanya polish visual |
| Sebagian | Route dan view ada, tetapi sebagian isi mockup belum didukung data/alur |
| Belum ada | Tidak ada route/controller/view |
| Di luar MVP | Sengaja tidak dibangun sesuai `AGENTS.md` §12 |

## Ringkasan

- 49 layar mockup.
- 8 layar tanpa route sama sekali: M11, O1a, O1b, O3, O4, B9, T3, T4.
- 1 layar berbagi route dengan layar lain dan hanya terpenuhi sebagian: B6.
- Sisanya punya route, tetapi banyak yang hanya "Sebagian" karena data atau
  komponen mockup belum ada.
- 4 role staf berbagi satu dashboard dan satu layout.

## Borrower Mobile

| Mockup | Route | Controller | View | Status |
|---|---|---|---|---|
| M0 Onboarding | `/` | Closure di `routes/web.php` | `welcome.blade.php` | Sesuai |
| M1 Login | `login` | Breeze `AuthenticatedSessionController` | `auth/login.blade.php` | Sebagian: beda sistem visual |
| M2 Register | `register` | Breeze `RegisteredUserController` | `auth/register.blade.php` | Sebagian: beda sistem visual |
| M3 Beranda | `borrower.home` | `Borrower\HomeController` | `borrower/home.blade.php` | Sebagian: markup dasar tanpa desain |
| M4 Katalog | `borrower.catalog.index` | `Borrower\CatalogController@index` | `borrower/catalog/index.blade.php` | Sesuai |
| M5 Detail aset | `borrower.catalog.show` | `Borrower\CatalogController@show` | `borrower/catalog/show.blade.php` | Sesuai |
| M6 Form peminjaman | `borrower.bookings.create` | `Borrower\BookingController@create` | `borrower/bookings/create.blade.php` | Sesuai |
| M7 Peminjaman saya | `borrower.bookings.index` | `Borrower\BookingController@index` | `borrower/bookings/index.blade.php` | Sesuai |
| M8 Detail peminjaman | `borrower.bookings.show` | `Borrower\BookingController@show` | `borrower/bookings/show.blade.php` | Sesuai |
| M9 Notifikasi | `borrower.notifications.index` | `Borrower\NotificationController@index` | `borrower/notifications/index.blade.php` | Sesuai |
| M10 Profil | `borrower.profile.edit` | `Borrower\ProfileController@edit` | `borrower/profile/edit.blade.php` | Sesuai |
| M11 Pembayaran denda | — | — | — | Di luar MVP |

## Borrower Desktop

| Mockup | Route | Controller | View | Status |
|---|---|---|---|---|
| B1 Beranda portal | `borrower.home` | `Borrower\HomeController` | `borrower/home.blade.php` | Sebagian: layout desktop belum ada |
| B2 Katalog | `borrower.catalog.index` | `Borrower\CatalogController@index` | `borrower/catalog/index.blade.php` | Sebagian: layout ponsel |
| B3 Peminjaman saya | `borrower.bookings.index` | `Borrower\BookingController@index` | `borrower/bookings/index.blade.php` | Sebagian: layout ponsel |
| B4 Form peminjaman | `borrower.bookings.create` | `Borrower\BookingController@create` | `borrower/bookings/create.blade.php` | Sebagian: layout ponsel |
| B5 Detail unit | `borrower.catalog.show` | `Borrower\CatalogController@show` | `borrower/catalog/show.blade.php` | Sebagian: layout ponsel |
| B6 Detail booking + tracking | `borrower.bookings.show` | `Borrower\BookingController@show` | `borrower/bookings/show.blade.php` | Sebagian: QR, PDF, perpanjangan belum ada |
| B7 Notifikasi + pengumuman | `borrower.notifications.index` | `Borrower\NotificationController@index` | `borrower/notifications/index.blade.php` | Sebagian: pengumuman lab dari setting, bukan halaman khusus |
| B8 Profil + pengaturan akun | `borrower.profile.edit` | `Borrower\ProfileController@edit` | `borrower/profile/edit.blade.php` | Sebagian: layout ponsel |
| B9 Pusat bantuan/regulasi/SOP | — | — | — | Belum ada; konten statis bisa ditambahkan tanpa backend |

## Operator

| Mockup | Route | Controller | View | Status |
|---|---|---|---|---|
| O1a Kiosk scanner 1 | — | — | — | Di luar MVP |
| O1b Kiosk scanner 2 | — | — | — | Di luar MVP |
| O2 Kalender Gantt | `admin.bookings.calendar` | `Admin\BookingController@calendar` | `admin/bookings/calendar.blade.php` | Sebagian: kalender bulanan, bukan Gantt |
| O3 BAP kerusakan + denda | — | — | — | Di luar MVP |
| O4 Kiosk antrean | — | — | — | Di luar MVP |

## Konsol Admin Bersama

| Mockup | Route | Controller | View | Status |
|---|---|---|---|---|
| W1 Dashboard operator | `admin.dashboard` | `Admin\DashboardController` | `admin/dashboard.blade.php` | Sebagian: template generik |
| W2 Dashboard org admin | `admin.dashboard` | `Admin\DashboardController` | `admin/dashboard.blade.php` | Sebagian: template generik |
| W3 Daftar aset | `admin.assets.index` | `Admin\AssetController@index` | `admin/assets/index.blade.php` | Sesuai |
| W4 Form aset | `admin.assets.create`, `admin.assets.edit` | `Admin\AssetController` | `admin/assets/create.blade.php`, `edit.blade.php` | Sesuai |
| W5 Kategori | `admin.categories.index` | `Admin\CategoryController@index` | `admin/categories/index.blade.php` | Sesuai |
| W6 Antrean persetujuan | `admin.bookings.index` | `Admin\BookingController@index` | `admin/bookings/index.blade.php` | Sesuai |
| W7 Detail booking | `admin.bookings.show` | `Admin\BookingController@show` | `admin/bookings/show.blade.php` | Sesuai |
| W8 Handover | `admin.bookings.handover`, `admin.handovers.index` | `Admin\HandoverController` | `admin/bookings/handover.blade.php`, `admin/handovers/index.blade.php` | Sesuai |
| W9 Inspeksi | `admin.inspections.index`, `create` | `Admin\InspectionController` | `admin/inspections/*` | Sesuai |
| W10 Maintenance + drawer | `admin.maintenances.index`, `edit` | `Admin\MaintenanceController` | `admin/maintenances/*` | Sebagian: detail lewat halaman edit, bukan drawer |
| W11 Pengguna & role | `admin.users.index` | `Admin\UserController@index` | `admin/users/index.blade.php` | Sesuai |
| W12 Laporan analitik | `admin.reports.index` | `Admin\ReportController@index` | `admin/reports/index.blade.php` | Sesuai |
| W13 Pengaturan organisasi | `admin.settings.edit` | `Admin\SettingController@edit` | `admin/settings/edit.blade.php` | Sesuai |

## Admin Prodi

| Mockup | Route | Controller | View | Status |
|---|---|---|---|---|
| P1 Dashboard prodi | `admin.dashboard` | `Admin\DashboardController` | `admin/dashboard.blade.php` | Sebagian: tidak ada blok/data prodi |
| P2 Antrean otorisasi khusus | `admin.bookings.index`, `approve` | `Admin\BookingController` | `admin/bookings/*` | Sebagian: tidak ada tingkat approval dua atau portofolio |
| P3 Kuota & utilisasi MK | `admin.reports.index` | `Admin\ReportController@index` | `admin/reports/index.blade.php` | Sebagian: laporan umum, tanpa data mata kuliah |
| P4 Kalender praktikum/studio | `admin.bookings.calendar` | `Admin\BookingController@calendar` | `admin/bookings/calendar.blade.php` | Sebagian: hanya ketersediaan unit |
| P5 Skor kepatuhan | `admin.reports.index` | `Admin\ReportController@index` | `admin/reports/index.blade.php` | Sebagian: tidak ada perhitungan skor |
| P6 Laporan semesteran/RAB | `admin.reports.export` | `Admin\ReportController@export` | — (unduhan CSV) | Sebagian: ekspor CSV umum, bukan laporan semesteran |

## Technician

| Mockup | Route | Controller | View | Status |
|---|---|---|---|---|
| Dashboard teknisi | `admin.dashboard` | `Admin\DashboardController` | `admin/dashboard.blade.php` | Sebagian: generik, tanpa workbench |
| Workbench work order | `admin.maintenances.index` | `Admin\MaintenanceController@index` | `admin/maintenances/index.blade.php` | Sebagian: daftar, bukan papan kerja |
| T3 Suku cadang | — | — | — | Di luar MVP |
| T4 Kalibrasi | — | — | — | Di luar MVP |

## Fitur Mockup yang Butuh Backend Baru

| Fitur | Mockup | Kebutuhan backend |
|---|---|---|
| Denda/pembayaran | M11, O3 | Tabel pembayaran/denda, status, integrasi (di luar MVP) |
| Kiosk scan/antrean | O1a, O1b, O4 | Endpoint scan, sesi kiosk, nomor antrean |
| Gantt ketersediaan | O2 | Data per unit per hari; bisa diturunkan dari booking yang ada |
| Portal desktop borrower | B1–B9 | Tidak wajib; hanya layout dan breakpoint |
| Pusat bantuan | B9 | Tidak wajib jika konten statis |
| QR/PDF/perpanjangan | B6 | Generator dokumen, aturan perpanjangan |
| Otorisasi tingkat dua | P2 | Aturan approval khusus + data portofolio |
| Kuota/utilisasi MK | P1, P3 | Model prodi, mata kuliah, kuota, pemakaian |
| Kalender praktikum/studio | P4 | Model jadwal praktikum dan studio |
| Skor kepatuhan | P5 | Aturan perhitungan dan riwayat |
| Laporan semesteran/RAB | P6 | Agregasi semester dan data RAB |
| Suku cadang | T3 | Tabel stok, transaksi keluar/masuk |
| Kalibrasi | T4 | Tabel jadwal, sertifikat, riwayat |

## Route yang Ada Tanpa Mockup

Route berikut dipakai aplikasi tetapi tidak punya layar mockup khusus. Tetap
dipertahankan dan mengikuti sistem visual baru:

- Auth: lupa/reset password, konfirmasi password, verifikasi email.
- `admin.search` — pencarian global.
- `admin.notifications.index` — notifikasi staf.
- `admin.profile.edit` — profil staf.
- `admin.activity-logs.index` + ekspor.
- `admin.handovers.index` — daftar serah terima.
- `admin.reports.export`, `admin.activity-logs.export`.
- Semua aksi POST (approve, reject, cancel, handover, start, finish, suspend,
  activate, retire).

## Kesimpulan

Redesign yang aman dilakukan sekarang hanya pada layar berstatus "Sesuai" dan
"Sebagian" yang datanya sudah tersedia. Layar "Belum ada" dan "Di luar MVP"
tidak boleh dibangun tanpa persetujuan backend dan produk.

## Verifikasi

Pemetaan dicek silang dengan:

```
grep -nE "Route::" routes/web.php
find resources/views -name '*.blade.php'
```

Tidak ada route atau view baru yang dibuat.

## File Tersentuh

Dibuat:

- `docs/history/2026-11-08-pemetaan-mockup-implementasi.md`

## Risiko / Catatan Lanjutan

- Status "Sesuai" berarti alur dan view ada, bukan berarti identik dengan
  mockup. Verifikasi visual browser belum dilakukan.
- Beberapa route mengizinkan role yang tidak sesuai mockup (lihat dokumen
  audit). Perbaikan otorisasi bukan bagian dari redesign visual.
- Jika produk memutuskan membangun fitur bertanda "Di luar MVP", pekerjaan
  harus dipecah menjadi fase backend dan frontend terpisah.
