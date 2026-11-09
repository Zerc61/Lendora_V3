# 2026-11-08 — Profil Admin

## Tujuan
Memberi staf (operator, organization admin, admin prodi, technician) halaman untuk mengubah data akun sendiri.
Sebelumnya hanya borrower yang punya halaman profil.

## Perubahan
- Route `GET /admin/profile` dan `PUT /admin/profile`.
- Controller `Admin\ProfileController`.
- FormRequest `UpdateAdminProfileRequest` (nama, telepon, kata sandi opsional, preferensi notifikasi).
- Halaman profil admin: kartu identitas, form data akun, preferensi notifikasi, ubah kata sandi.
- Nama pengguna di topbar menjadi tautan ke profil.
- CSS kartu profil dan grid form profil untuk area admin.

## File tersentuh
- `app/routes/web.php`
- `app/app/Http/Controllers/Admin/ProfileController.php`
- `app/app/Http/Requests/UpdateAdminProfileRequest.php`
- `app/resources/views/admin/profile/edit.blade.php`
- `app/resources/views/layouts/admin.blade.php`
- `app/public/css/admin.css`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/ProfileTest.php`
- `app/tests/Feature/Admin/PageSmokeTest.php`

## Verifikasi
- Test: staf membuka dan memperbarui profil, borrower ditolak `403`.
- `php artisan test`: 129 passed (454 assertions).
- `php artisan view:cache`: berhasil.

## Risiko
Tidak ada perubahan izin selain akses ke profil sendiri.
