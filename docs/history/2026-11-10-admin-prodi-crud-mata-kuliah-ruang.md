# Admin Prodi: CRUD Mata Kuliah & Ruang

Tanggal: 2026-11-10
Jenis pekerjaan: backend + frontend (area Admin Prodi)
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Menutup celah data master: sebelumnya mata kuliah dan ruang hanya bisa diisi
lewat seeder. Sekarang Admin Prodi dapat menambah, mengubah, dan menghapus
keduanya dari UI.

## Perubahan

### Backend

- `app/Http/Requests/StoreCourseRequest.php` (baru) dan
  `UpdateCourseRequest.php` (baru): validasi kode unik per organisasi, nama,
  dosen, semester 1–14, SKS 1–24, kuota jam opsional 1–9999.
- `app/Http/Requests/StoreRoomRequest.php` (baru) dan `UpdateRoomRequest.php`
  (baru): kode unik per organisasi, nama, kapasitas 1–500, catatan.
- `app/Services/CourseQuotaService.php`: `createCourse()`, `updateCourse()`,
  `deleteCourse()`. Hapus ditolak bila mata kuliah sudah dipakai peminjaman
  atau masih punya jadwal praktikum.
- `app/Services/PracticumScheduleService.php`: `createRoom()`,
  `updateRoom()`, `deleteRoom()`. Hapus ditolak bila ruang masih punya jadwal.
- `app/Http/Controllers/Admin/CourseController.php`: `store()`, `update()`,
  `destroy()`.
- `app/Http/Controllers/Admin/RoomController.php` (baru): `store()`,
  `update()`, `destroy()`.
- `routes/web.php`: `admin.courses.store|update|destroy` dan
  `admin.rooms.store|update|destroy`, semuanya di grup `role:admin_prodi`.
- Semua aksi tulis mencatat activity log: `course.created`, `course.updated`,
  `course.deleted`, `room.created`, `room.updated`, `room.deleted`.

### Frontend

- `resources/views/admin/courses/index.blade.php`: form "Tambah Mata Kuliah",
  panel edit per baris (`<details>`), tombol hapus dengan konfirmasi.
- `resources/views/admin/practicum/index.blade.php`: form "Tambah Ruang",
  panel edit per ruang, tombol hapus.
- `public/css/admin.css`: `.ldr-inline-edit*`, `.ldr-inline-delete`,
  `.ldr-course-create*`, `.ldr-room-create*`.
- `lang/id/ui.php`, `lang/id/messages.php`: kunci baru berbahasa Indonesia.

## Aturan penghapusan

- Mata kuliah: ditolak bila `bookings.course_id` merujuk atau ada
  `practicum_schedules.course_id`.
- Ruang: ditolak bila ada `practicum_schedules.room_id`.
- Tidak ada penghapusan paksa; pesan kesalahan berbahasa Indonesia.

## Verifikasi

```
php artisan test tests/Feature/Admin/CourseQuotaTest.php tests/Feature/Admin/PracticumScheduleTest.php
Tests:    27 passed (118 assertions)

php artisan test
Tests:    304 passed (1294 assertions)
Duration: 27.20s
```

## Batasan

- Belum ada QA visual browser.
- Penghapusan bersifat permanen; tidak ada arsip data master.
