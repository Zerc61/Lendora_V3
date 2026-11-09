# 2026-11-08 — Preferensi Notifikasi

## Tujuan
Memberi pengguna kendali atas jenis notifikasi yang diterima, tanpa menghapus notifikasi yang sudah ada.

## Perubahan
- Migrasi `0015_add_notification_preferences_to_users_table`: kolom JSON `notification_preferences` nullable.
- `User::notificationTypes()`, `notificationAudience()`, `wantsNotification()`.
- Checkbox preferensi di halaman profil borrower dan profil staf.
- Komponen Blade `x-notification-preferences` dipakai kedua area.
- Lima kelas notifikasi menghormati preferensi lewat `via()`: `booking_received`, `booking_decision`, `loan_pickup`, `loan_due_reminder`, `asset_damage`.
- Hanya kunci yang relevan dengan peran pengguna yang disimpan.
- `UpdateBorrowerProfileRequest` dan `UpdateAdminProfileRequest` memvalidasi array preferensi.

## File tersentuh
- `app/database/migrations/0015_add_notification_preferences_to_users_table.php`
- `app/app/Models/User.php`
- `app/app/Http/Requests/UpdateBorrowerProfileRequest.php`
- `app/app/Http/Requests/UpdateAdminProfileRequest.php`
- `app/app/Http/Controllers/Borrower/ProfileController.php`
- `app/app/Http/Controllers/Admin/ProfileController.php`
- `app/app/Notifications/*.php` (5 kelas)
- `app/resources/views/components/notification-preferences.blade.php`
- `app/resources/views/borrower/profile/edit.blade.php`
- `app/resources/views/admin/profile/edit.blade.php`
- `app/lang/id/ui.php`
- `app/tests/Feature/Borrower/ProfileTest.php`
- `app/tests/Feature/Admin/ProfileTest.php`

## Verifikasi
- `php artisan migrate`: migrasi 0015 berhasil.
- Test: preferensi tersimpan, kunci asing ditolak, notifikasi nonaktif tidak dikirim.
- `php artisan test`: 129 passed (454 assertions).

## Risiko
Preferensi hanya berpengaruh pada notifikasi database. Bila kelak ada kanal surel, aturan yang sama perlu diterapkan.
