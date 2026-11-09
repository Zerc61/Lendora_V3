# 2026-11-08 — Pusat Notifikasi Staf

## Tujuan
Membuat notifikasi staf (operator, organization admin, technician) dapat dibaca dari UI.
Sebelumnya notifikasi kerusakan dikirim ke database tetapi tidak punya halaman.

## Perubahan
- Route `GET /admin/notifications`, `POST /admin/notifications/{id}/read`, `POST /admin/notifications/read-all`.
- Controller `Admin\NotificationController`.
- Halaman notifikasi dengan grup Hari ini/Sebelumnya, status baca, dan aksi tandai.
- Bell + indikator belum dibaca di topbar admin.
- View composer `layouts.admin` membagikan `unreadNotifications`.
- CSS bell dan dot untuk topbar admin.

## File tersentuh
- `app/routes/web.php`
- `app/app/Http/Controllers/Admin/NotificationController.php`
- `app/app/Providers/AppServiceProvider.php`
- `app/resources/views/admin/notifications/index.blade.php`
- `app/resources/views/layouts/admin.blade.php`
- `app/public/css/admin.css`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/NotificationTest.php`

## Verifikasi
- Test notifikasi: pengguna hanya melihat notifikasi miliknya, tandai dibaca berhasil, borrower ditolak.
- `php artisan test` lulus.

## Risiko
Tidak ada perubahan pada pembuatan notifikasi. Hanya akses baca yang ditambahkan.
