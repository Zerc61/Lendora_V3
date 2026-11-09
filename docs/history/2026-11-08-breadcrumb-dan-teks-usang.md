# 2026-11-08 — Breadcrumb Admin dan Teks Usang

## Tujuan
Menyelaraskan pola admin dengan arahan PDF dan menghapus teks yang menyebut fitur belum ada.

## Perubahan
- Breadcrumb `Dashboard / Judul Halaman` di semua halaman admin kecuali Dashboard.
- CSS breadcrumb.
- Teks Beranda borrower tidak lagi menyebut "fase berikutnya".
- Translation key mati dihapus: grup `ui.empty`, `ui.catalog.apply_notice`.

## File tersentuh
- `app/resources/views/layouts/admin.blade.php`
- `app/public/css/admin.css`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/ShellTest.php`

## Verifikasi
- Test breadcrumb muncul di halaman selain Dashboard.
- Grep tidak menemukan `href="#"` atau teks "fase berikutnya".
- `php artisan test` lulus.

## Risiko
Tidak ada.
