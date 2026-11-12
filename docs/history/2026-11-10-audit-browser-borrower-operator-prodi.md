# Audit Browser Borrower, Operator, Admin Prodi — 10 November 2026

## Tujuan

Memeriksa ulang area Borrower, Operator, dan Admin Prodi setelah rombak UI:
error console, respons jaringan, tautan terlarang, overflow, aksesibilitas
dasar, dan isi berkas ekspor. Chrome DevTools MCP tidak terpasang di sesi ini,
jadi audit memakai Playwright/Chromium headless dengan browser dan server lokal
yang sama (`php artisan serve --port=8123`, database `lendora`).

## Perubahan

| Temuan | Perbaikan |
|---|---|
| Admin Prodi melihat tombol ekspor aset dan tautan kalender, keduanya berakhir 403 | Tautan disembunyikan untuk role yang tidak berhak di `admin/assets/index` dan `admin/bookings/index` |
| Admin Prodi melihat aksi "Laporkan kerusakan", cetak tanda terima, dan form keputusan perpanjangan; semuanya 403 saat dipakai | Aksi disembunyikan untuk `admin_prodi`; data perpanjangan tetap dapat dibaca |
| Kartu peminjaman borrower membungkus `<form>` dan `<a>` di dalam `<a>` (HTML tidak valid, nama aksesibel kosong) | Tautan kartu dipisah dari baris aksi; aksi cancel/perpanjang berada di luar anchor |
| Halaman detail peminjaman borrower tidak punya `h1` | Kode booking dinaikkan menjadi `h1` dengan `margin: 0` |
| Input kalkulator denda tanpa label terhubung | Ditambah `id` dan `for` pada komponen kalkulator |

Temuan yang bukan cacat:

- Empat gambar di `/admin/bookings?status=all` dilaporkan "belum termuat"
  karena memakai `loading="lazy"` dan berada di luar viewport saat diukur.
- Navigasi ke endpoint ekspor berhenti dengan status `-1` karena respons berupa
  unduhan. Semua endpoint diperiksa ulang lewat HTTP: 200 dengan tipe dan ukuran
  yang benar.
- Akun `borrower1@lendora.test` berstatus `suspended`, sehingga 403 di area
  borrower adalah perilaku benar. Audit memakai `ani@lendora.test`.

## File tersentuh

- `resources/views/admin/assets/index.blade.php`
- `resources/views/admin/bookings/index.blade.php`
- `resources/views/admin/bookings/show.blade.php`
- `resources/views/borrower/bookings/index.blade.php`
- `resources/views/borrower/bookings/show.blade.php`
- `resources/views/components/borrower/penalty-calculator.blade.php`
- `public/css/borrower.css`
- `tests/Feature/Admin/AuthorizationRegressionTest.php`

## Verifikasi

| Pemeriksaan | Hasil |
|---|---|
| 199 halaman (3 role, 1440 & 390 px, crawl tautan) | 0 error console, 0 error jaringan |
| Tautan lintas role (16 URL terlarang) | Semua 403 dengan halaman error Indonesia |
| Overflow horizontal 192 kombinasi role × halaman × lebar | 0 |
| Interaksi JS (sidebar, modal, BAP, favorit, QR, kalkulator, onboarding, kiosk, Gantt) | 11/11 lulus |
| Validasi form (tanggal terbalik, kode MK duplikat) | Ditolak server, tidak ada 500, data tidak berubah |
| Ekspor CSV/XLSX/ICS/PDF (11 endpoint) | Semua 200, tipe dan ukuran benar |
| PHPUnit | `314 passed (1357 assertions)` |

## Risiko / catatan lanjutan

- Uji concurrency `lockForUpdate()` tetap memakai SQLite di PHPUnit; perilaku
  kunci baris MariaDB belum dibuktikan otomatis.
- Audit ini tidak mengirim form yang mengubah data (approve, handover,
  inspeksi). Jalur tersebut sudah dicakup PHPUnit.
- Integrasi SIAKAD/dekanat dan pengingat H-1 terjadwal masih di luar cakupan
  karena belum ada endpoint atau cron server.
