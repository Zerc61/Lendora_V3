# 2026-11-08 — Filter dan Ekspor Log Aktivitas

## Tujuan
Membuat log aktivitas dapat dicari, difilter, dan diekspor tanpa membocorkan data organisasi lain.

## Perubahan
- Filter pencarian: deskripsi, aksi, nama pengguna.
- Filter aksi dari daftar aksi yang tersedia.
- Filter tanggal mulai dan selesai.
- Ekspor CSV mengikuti filter aktif.
- Query tetap dibatasi `organization_id` pengguna.

## File tersentuh
- `app/app/Http/Controllers/Admin/ActivityLogController.php`
- `app/resources/views/admin/activity-logs/index.blade.php`
- `app/routes/web.php`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/ActivityLogTest.php`

## Verifikasi
- Test filter aksi dan pencarian.
- Test isi CSV dan isolasi organisasi.
- `php artisan test` lulus.

## Risiko
Ekspor memuat seluruh hasil filter tanpa pagination. Untuk data sangat besar perlu streaming bertahap di masa depan.
