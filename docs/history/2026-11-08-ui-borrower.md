# 2026-11-08 — UI Borrower: Notifikasi dan Profil

## Tujuan
Menyelaraskan halaman notifikasi dan profil borrower dengan token serta komponen desain.

## Perubahan
- Notifikasi: kartu per notifikasi, penanda belum dibaca, grup hari ini/sebelumnya, aksi tandai.
- Profil: kartu identitas dengan inisial, form data akun dan kata sandi.
- CSS borrower untuk page head, notifikasi, dan kartu profil.

## File tersentuh
- `app/resources/views/borrower/notifications/index.blade.php`
- `app/resources/views/borrower/profile/edit.blade.php`
- `app/public/css/borrower.css`
- `app/lang/id/ui.php`

## Verifikasi
- Test shell borrower lulus.
- `php artisan view:cache` berhasil.
- `php artisan test`: 119 passed.

## Risiko
Tidak ada perubahan perilaku controller atau validasi.
