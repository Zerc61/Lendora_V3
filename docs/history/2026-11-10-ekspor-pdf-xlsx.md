# Ekspor PDF & XLSX

Tanggal: 2026-11-10
Jenis pekerjaan: backend + frontend (semua role)
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Mengganti ketergantungan pada "print ke PDF" browser dan CSV saja dengan unduhan
PDF asli serta spreadsheet Excel asli.

## Dependensi baru (disetujui pengguna)

- `barryvdh/laravel-dompdf` `^3.1` — render PDF dari Blade.
- `phpoffice/phpspreadsheet` `^5.11` — tulis file XLSX.

Keduanya terpasang di PHP 8.5 / Laravel 12. `gd`, `dom`, `zip`, `mbstring`
sudah tersedia. Tidak ada config tambahan yang perlu dipublikasikan.

## Perubahan backend

- `app/Services/PdfService.php` (baru): `download(view, data, filename, paper)`
  memanggil dompdf dan mengembalikan respons unduhan.
- `app/Services/SpreadsheetService.php` (baru): `download(filename, headings,
  rows)` menulis XLSX ke berkas sementara lalu mengirimkannya.
- `app/Http/Controllers/Admin/ReportController.php`: `pdf()`, `semesterPdf()`,
  dan parameter `format=xlsx` pada `export()` / `semesterExport()`.
- `app/Http/Controllers/Admin/AssetController.php`: `format=xlsx`.
- `app/Http/Controllers/Admin/ActivityLogController.php`: `format=xlsx`.
- `app/Http/Controllers/Admin/BookingController.php`: `format=xlsx` pada
  `calendarExport()` (sebelumnya hanya `csv` dan `ics`).
- `app/Http/Controllers/Admin/HandoverController.php`: `format=pdf` pada
  `receipt()`.
- `app/Http/Controllers/Admin/PenaltyController.php`: `format=pdf` pada
  `print()`.
- `app/Http/Controllers/Borrower/BookingController.php`: `format=pdf` pada
  `document()` dan `returnPass()`.
- `app/Http/Controllers/Borrower/PenaltyController.php`: `format=pdf` pada
  `document()`.
- `routes/web.php`: `admin.reports.pdf`, `admin.reports.semester.pdf`.

## Perubahan tampilan

- `resources/views/layouts/pdf.blade.php` (baru): layout cetak khusus PDF,
  menyalin isi `public/css/lendora.css` langsung ke `<style>` agar dompdf tidak
  perlu mengambil aset lewat HTTP, lalu menambahkan aturan tabel `@page`.
- Tujuh view kini memilih layout secara dinamis:
  `@extends(($pdf ?? false) ? 'layouts.pdf' : 'layouts.print')` — berlaku untuk
  laporan, BAP admin, bukti serah terima, surat/BAP peminjam, return pass, dan
  BAP denda peminjam.
- Tombol "Unduh PDF" dan "Unduh Excel" ditambahkan pada halaman laporan,
  laporan semester, aset, log aktivitas, kalender, BAP, bukti serah terima, dan
  dokumen peminjam.
- `lang/id/ui.php`: `ui.common.download_pdf`, `ui.common.download_xlsx`.

## Perilaku

- Tanpa parameter `format`, semua endpoint tetap mengirim CSV seperti
  sebelumnya. Tidak ada tautan lama yang rusak.
- PDF memakai kertas A4 potret, margin 18 mm × 14 mm, font DejaVu Sans.
- Tombol cetak dan tautan aksi disembunyikan saat render PDF.

## Verifikasi

```
php artisan test tests/Feature/Admin/ExportFormatTest.php
Tests:    6 passed (31 assertions)

php artisan test tests/Feature/Admin/PenaltyBapTest.php tests/Feature/Admin/HandoverTest.php tests/Feature/Borrower/UiRegressionTest.php
Tests:    32 passed (194 assertions)

php artisan test
Tests:    311 passed (1343 assertions)
Duration: 33.09s
```

## Batasan

- Grafik tren belum ada, jadi PDF hanya memuat KPI dan tabel.
- Gaya CSS grid/flex tidak didukung dompdf; PDF memakai varian tabel dan blok.
- Logo organisasi belum ada, jadi kop dokumen hanya teks.
- Verifikasi visual PDF hanya lewat ukuran berkas dan header, belum inspeksi
  halaman per halaman.
