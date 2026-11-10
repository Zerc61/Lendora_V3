# Organization Admin: Koreksi Otorisasi Serah Terima & Pemeriksaan

Tanggal: 2026-11-09
Jenis pekerjaan: backend (otorisasi route) + guard tampilan
Status: selesai
Branch: `feature/operator-operasional`

## Masalah

Audit navigasi mencatat ketidaksesuaian antara `routes/web.php` dan matriks
otorisasi master (§3):

- Serah terima (catat pickup/return) terbuka untuk `organization_admin`,
  padahal matriks hanya memberi lihat.
- Pembuatan pemeriksaan terbuka untuk `organization_admin`, padahal hanya
  operator dan teknisi yang boleh mencatat.

## Perubahan

`routes/web.php`:

- `GET/POST admin/bookings/{booking}/handover` → hanya `operator`.
- `GET admin/handovers` dan `GET admin/bookings/{booking}/receipt/{type}`
  tetap `operator,organization_admin`.
- `GET admin/inspections` dan `GET admin/inspections/{inspection}` tetap
  `operator,organization_admin,technician`.
- `GET admin/inspections/create` dan `POST admin/inspections` → hanya
  `operator,technician`.

Guard tampilan:

- `resources/views/admin/bookings/show.blade.php`: tombol serah terima hanya
  untuk operator.
- `resources/views/admin/handovers/index.blade.php`: tautan aksi serah terima
  hanya untuk operator; daftar tetap terlihat.
- `resources/views/admin/inspections/index.blade.php`: tombol tambah
  pemeriksaan hanya untuk operator dan teknisi.

Tes:

- `tests/Feature/Admin/HandoverTest.php`: admin dapat membuka daftar, tetapi
  403 pada form dan POST, serta tidak melihat tautan aksi.
- `tests/Feature/Admin/InspectionTest.php`: admin dapat membuka daftar, tetapi
  403 pada form dan POST.
- `tests/Feature/Admin/PageSmokeTest.php`: hapus `inspections.create` dari
  daftar halaman yang harus 200 untuk admin.

## File Tersentuh

- `routes/web.php`
- `resources/views/admin/bookings/show.blade.php`
- `resources/views/admin/handovers/index.blade.php`
- `resources/views/admin/inspections/index.blade.php`
- `tests/Feature/Admin/HandoverTest.php`
- `tests/Feature/Admin/InspectionTest.php`
- `tests/Feature/Admin/PageSmokeTest.php`

## Verifikasi

```
php artisan test
Tests:    276 passed (1184 assertions)
Duration: 26.96s
```

## Batasan

- `POST admin/maintenances/{maintenance}/parts` masih terbuka untuk operator.
  Matriks master menyebut operator hanya membuat tugas, tetapi alur suku
  cadangan belum diputuskan; tidak diubah tanpa keputusan.
- Visibilitas menu Pengaturan untuk operator masih mengikuti route saat ini.
- Verifikasi visual browser belum dilakukan.
