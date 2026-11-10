# Admin Prodi: CRUD Kuota MK & Plotting Jadwal Praktikum

Tanggal: 2026-11-10
Jenis pekerjaan: backend + frontend (area Admin Prodi)
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Menutup sisa pekerjaan Admin Prodi yang sebelumnya hanya baca-saja: mengubah
kuota jam per mata kuliah, memindahkan kuota antar-mata kuliah, dan mengelola
jadwal praktikum (tambah/hapus) langsung dari UI.

## Perubahan

### Backend

- `app/Http/Requests/UpdateCourseQuotaRequest.php` (baru): validasi
  `quota_hours` (nullable, 1–9999), hanya `admin_prodi`.
- `app/Http/Requests/TransferCourseQuotaRequest.php` (baru): validasi asal,
  tujuan (harus berbeda), dan jumlah jam.
- `app/Http/Requests/StorePracticumScheduleRequest.php` (baru): validasi MK dan
  ruang harus aktif dan milik organisasi yang sama; `end_time` setelah
  `start_time`.
- `app/Services/CourseQuotaService.php`: `updateQuota()` dan `transfer()`
  memakai transaksi + `lockForUpdate()`, mencatat `course_quota.updated` dan
  `course_quota.transferred` ke activity log.
- `app/Services/PracticumScheduleService.php`: `options()`, `create()` (cek
  bentrok slot dengan lock pada ruang), dan `remove()`; log
  `practicum.created` / `practicum.deleted`.
- `app/Http/Controllers/Admin/CourseController.php`: `updateQuota()`,
  `transfer()`.
- `app/Http/Controllers/Admin/PracticumScheduleController.php`: `store()`,
  `destroy()`.
- `routes/web.php`: `admin.courses.quota.update`, `admin.courses.transfer`,
  `admin.practicum.store`, `admin.practicum.destroy` — tetap di grup
  `role:admin_prodi`.

### Frontend

- `resources/views/admin/courses/index.blade.php`: kolom "Atur Kuota" per baris
  dan kartu "Transfer Kuota Antar-Mata Kuliah".
- `resources/views/admin/practicum/index.blade.php`: form plotting jadwal
  (MK, ruang, hari, jam mulai/selesai) dan tombol hapus per slot dengan
  konfirmasi.
- `public/css/admin.css`: `.ldr-quota-form`, `.ldr-input--sm`,
  `.ldr-quota-transfer*`, `.ldr-practicum-plot*`,
  `.ldr-practicum-slot__remove`.
- `lang/id/ui.php`, `lang/id/messages.php`: seluruh kunci baru berbahasa
  Indonesia.

## Aturan bisnis

- Kuota hanya dipantau; tidak memblokir pengajuan peminjaman.
- Transfer ditolak bila kuota asal `null` atau lebih kecil dari jumlah jam yang
  dipindahkan.
- Slot jadwal bentrok bila `existing_start < new_end AND existing_end >
  new_start` pada ruang, hari, dan status aktif yang sama.
- Semua aksi tulis hanya untuk `admin_prodi`; role lain mendapat 403.

## Verifikasi

```
php artisan test tests/Feature/Admin/CourseQuotaTest.php tests/Feature/Admin/PracticumScheduleTest.php
Tests:    16 passed (70 assertions)

php artisan test
Tests:    293 passed (1246 assertions)
Duration: 26.99s
```

## Batasan

- Belum ada UI untuk menambah/mengubah ruang dan mata kuliah; data ruang masih
  dari seeder.
- Belum ada QA visual browser; verifikasi hanya lewat tes HTML.
- Dispensasi dekan, ekspor XLS, dan sinkronisasi SIAKAD tetap di luar cakupan.
