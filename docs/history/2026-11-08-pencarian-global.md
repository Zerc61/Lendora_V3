# 2026-11-08 — Pencarian Global

## Tujuan
Menyediakan satu kotak pencarian di topbar admin untuk menemukan aset, transaksi, pengguna, dan kategori.

## Perubahan
- Route `GET /admin/search` dengan parameter `q`.
- Controller `Admin\SearchController`.
- Hasil dikelompokkan per jenis, maksimal 5 baris per kelompok.
- Scope organisasi dijaga pada semua query.
- Hasil per peran: aset/booking untuk operator, organization admin, admin prodi; pengguna hanya organization admin; kategori untuk organization admin dan admin prodi.
- Kotak pencarian di topbar admin, minimal 2 karakter.
- Halaman hasil dengan empty state dan tautan aksi ke detail/edit.

## File tersentuh
- `app/routes/web.php`
- `app/app/Http/Controllers/Admin/SearchController.php`
- `app/resources/views/admin/search/index.blade.php`
- `app/resources/views/layouts/admin.blade.php`
- `app/public/css/admin.css`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/SearchTest.php`
- `app/tests/Feature/Admin/PageSmokeTest.php`

## Verifikasi
- Test: hasil sesuai peran, pengguna tidak muncul untuk operator, organisasi lain tidak bocor, istilah < 2 karakter tidak mencari.
- `php artisan test`: 129 passed (454 assertions).

## Risiko
Pencarian memakai `LIKE` tanpa indeks khusus. Untuk data sangat besar perlu full-text index di masa depan.
