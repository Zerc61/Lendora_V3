# QA Browser & Perbaikan Temuan — 10 November 2026

## Tujuan

QA browser nyata memakai Playwright (Chromium headless) terhadap server lokal
`php artisan serve --port=8123` dan database MariaDB `lendora`. Cakupan:
5 role × 48 halaman × 4 lebar layar = 192 pemuatan halaman, ditambah 11 uji
interaksi JavaScript dan verifikasi isi PDF.

Hasil akhir:

| Pemeriksaan | Hasil |
|---|---|
| Halaman dimuat (192 kombinasi) | 0 error console, 0 respons HTTP >= 400, 0 redirect ke login |
| Overflow horizontal | 0 halaman pada 1440 / 1280 / 1024 / 768 / 390 px |
| Uji interaksi JS | 11/11 lulus |
| Isi PDF (teks, jumlah halaman, ukuran A4) | Laporan, semester, surat jalan, BAP, tiket pengembalian terbaca |
| Test suite PHPUnit | 313 passed (1351 assertions) |

## Perubahan

### 1. Topbar admin meluber di 768 px

`a.ldr-admin__user` selebar 280 px mendorong dokumen melewati viewport saat
sidebar desktop masih tampil. Perbaikan di `public/css/admin.css` pada
`@media (max-width: 1080px)`: badge role dan badge shift disembunyikan, nama
pengguna serta judul halaman dipotong dengan ellipsis. Fungsi dan tautan tetap
sama.

### 2. Tabel lebar memperlebar dokumen

`.ldr-table-card` sudah `overflow: hidden`, tetapi Chromium tetap menghitung
area gulir dokumen dari tabel di dalam `.ldr-table-wrap`. Ditambahkan
`contain: paint` pada `.ldr-table-card`. Halaman `/admin/courses` di 390 px
sempat meluber 175 px; sekarang 0.

### 3. Header portal borrower meluber di 1440 px

`.ldr-portal-actions` memakai `flex: none` sehingga search + lonceng + profil
tidak boleh menyusut. Diubah menjadi `flex: 0 1 auto` dengan `min-width: 0`.
`.ldr-portal-nav` diberi `min-width: 0` dan `overflow-x: auto`.

### 4. Grid utama borrower tidak membatasi kolom

`.ldr-borrower-main` memakai grid tanpa `grid-template-columns`, sehingga
judul panjang dapat memperlebar kolom. Ditambahkan
`grid-template-columns: minmax(0, 1fr)`.

### 5. Halaman cetak laporan di 390 px

Tabel laporan pada `/admin/reports/print` memakai `.ldr-card` tanpa pembatas.
Ditambahkan aturan `@media screen and (max-width: 640px)`:
`.ldr-print .ldr-card { overflow-x: auto; contain: paint; }`.

### 6. Tiket pengembalian berisi QR kosong

`bookings.return_token` tidak pernah diisi oleh alur persetujuan, sehingga
`/borrower/bookings/{booking}/return-pass` menampilkan QR kosong untuk semua
booking. Perbaikan:

- `BookingService::approve()` mengisi `return_token` saat booking disetujui.
- `BookingService::ensureReturnToken()` mengisi token untuk booking lama yang
  belum punya token; dipanggil dari `Borrower\BookingController::returnPass()`.
- Token 10 karakter alfanumerik unik (dicek terhadap baris soft-deleted juga).

### 7. Kode mati drawer filter katalog

`initDrawerFilter()` di `public/js/borrower.js` menargetkan
`[data-filter-open]` / `[data-filter-drawer]` yang tidak ada di view mana pun.
Katalog memakai chip filter sebaris sesuai `docs/Design.md`. Fungsi dihapus.

## Uji interaksi yang dijalankan

| Interaksi | Halaman | Hasil |
|---|---|---|
| Toggle sidebar | `/admin` | lulus |
| Tambah baris dinamis BAP | `/admin/penalties/create` | lulus |
| Buka modal konfirmasi | `/admin/assets` | lulus |
| Tutup modal dengan Escape | `/admin/assets` | lulus |
| Toggle favorit aset | `/borrower/catalog` | lulus |
| Chip filter katalog | `/borrower/catalog` | lulus |
| Render QR tiket pengembalian | `/borrower/bookings/{id}/return-pass` | lulus |
| Kalkulator denda | `/borrower/help` | lulus (`Rp 27.000` untuk 3 jam) |
| Slider onboarding | `/` | lulus |
| Input scanner kiosk | `/admin/kiosk` | lulus |
| Baris Gantt kalender | `/admin/bookings/calendar` | lulus |

## Verifikasi PDF

| Dokumen | Status | Halaman | Catatan |
|---|---|---|---|
| `/admin/reports/pdf` | 200, `application/pdf` | 1 (A4) | teks KPI dan tabel terbaca |
| `/admin/reports/semester/pdf` | 200, `application/pdf` | 1 (A4) | hanya untuk Admin Prodi |
| `/borrower/bookings/9/return-pass?format=pdf` | 200 | 1 (A4) | token dan jadwal kembali tercetak |
| `/borrower/bookings/9/document/letter?format=pdf` | 200 | 1 (A4) | surat jalan |
| `/borrower/bookings/9/document/bap?format=pdf` | 200 | 1 (A4) | berita acara |

## Risiko / catatan lanjutan

- Uji concurrency `lockForUpdate()` masih memakai SQLite di PHPUnit; perilaku
  kunci baris MariaDB belum dibuktikan otomatis.
- Integrasi SIAKAD, pengiriman ke dekanat, dan dispensasi dekan belum ada
  endpoint atau aturan bisnisnya.
- Backup database dari UI tidak dibuat sesuai keputusan pengguna.
- QA memakai data demo; akun `borrower1@lendora.test` berstatus `suspended`
  sehingga 403 pada area borrower adalah perilaku benar, bukan cacat.

## File tersentuh

- `app/Services/BookingService.php`
- `app/Http/Controllers/Borrower/BookingController.php`
- `public/css/admin.css`
- `public/css/borrower.css`
- `public/js/borrower.js`
- `tests/Unit/BookingServiceTest.php`
- `tests/Feature/Borrower/UiRegressionTest.php`
