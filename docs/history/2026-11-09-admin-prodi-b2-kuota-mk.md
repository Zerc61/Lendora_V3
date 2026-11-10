# Admin Prodi B2: Kuota & Utilisasi Mata Kuliah (P3)

Tanggal: 2026-11-09
Jenis pekerjaan: schema + service + frontend
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Menghubungkan peminjaman ke mata kuliah dan menampilkan penyerapan kuota jam
per mata kuliah untuk Admin Prodi, sesuai mockup P3.

## Perubahan schema

`database/migrations/2026_11_09_000030_add_quota_hours_to_courses_table.php`:

- `courses.quota_hours` (unsigned integer, nullable).
- `quota_hours = null` berarti kuota belum diatur; halaman menampilkannya
  sebagai "Belum diatur", bukan 0.

`database/seeders/CourseSeeder.php`:

- Empat mata kuliah demo diberi kuota: 120, 180, 120, 200 jam.

`app/Models/Course.php`:

- `quota_hours` masuk fillable dan cast integer.

## Penugasan mata kuliah

`app/Services/BookingService.php`:

- `createRequest()` menerima parameter opsional `?int $courseId`.
- Service memvalidasi mata kuliah harus aktif dan milik organisasi peminjam.

`app/Http/Requests/StoreBookingRequest.php`:

- Rule `course_id` nullable integer + validasi organisasi/aktif di `after()`.

`app/Http/Controllers/Borrower/BookingController.php`:

- Form menerima daftar mata kuliah aktif organisasi.
- `store()` meneruskan `course_id` bila diisi.

`resources/views/borrower/bookings/create.blade.php`:

- Select "Mata kuliah" opsional, dengan opsi "Tidak terkait mata kuliah".

## Halaman kuota

`app/Services/CourseQuotaService.php` (baru):

- Semester memakai setting `semester_start`/`semester_end`.
- Pemakaian = booking `approved`, `active`, `returned` yang punya `course_id`
  dan tanggal mulai di dalam semester.
- Jam terpakai = durasi hari × 24. `bookings` hanya menyimpan tanggal, jadi ini
  asumsi kapasitas alat per hari penuh.
- Status: `kritis` (> 85%), `normal` (50–85%), `surplus` (< 50%),
  `unset` (kuota null).
- KPI: total kuota, terpakai, sisa, jumlah mata kuliah kritis.

`app/Http/Controllers/Admin/CourseController.php` (baru):
`resources/views/admin/courses/index.blade.php` (baru):

- KPI, chip semester, tabel mata kuliah (kode, nama, semester/SKS, dosen,
  sesi, pemakaian/kuota, bar, sisa, status).
- Tanpa tombol tulis; prodi tetap baca-saja.

## Navigasi dan bahasa

- `routes/web.php`: `GET /admin/courses` → `admin.courses.index`,
  middleware `role:admin_prodi`.
- `resources/views/layouts/admin.blade.php`: item "Kuota & Utilisasi MK" di
  grup Monitoring Prodi.
- `lang/id/ui.php`: `ui.nav.course_quota`, grup `ui.course_quota.*`,
  `ui.booking.course*`.
- `lang/id/messages.php`: `messages.booking.course_invalid`.

## File Tersentuh

Dibuat:

- `app/Services/CourseQuotaService.php`
- `app/Http/Controllers/Admin/CourseController.php`
- `resources/views/admin/courses/index.blade.php`
- `database/migrations/2026_11_09_000030_add_quota_hours_to_courses_table.php`
- `tests/Unit/CourseQuotaServiceTest.php`
- `tests/Feature/Admin/CourseQuotaTest.php`
- `docs/history/2026-11-09-admin-prodi-b2-kuota-mk.md`

Diubah:

- `app/Models/Course.php`
- `app/Services/BookingService.php`
- `app/Http/Requests/StoreBookingRequest.php`
- `app/Http/Controllers/Borrower/BookingController.php`
- `resources/views/borrower/bookings/create.blade.php`
- `resources/views/layouts/admin.blade.php`
- `routes/web.php`
- `lang/id/ui.php`
- `lang/id/messages.php`
- `database/seeders/CourseSeeder.php`
- `tests/Feature/Borrower/BookingFlowTest.php`
- `tests/Feature/Admin/ShellTest.php`

## Verifikasi

```
php artisan migrate
2026_11_09_000030_add_quota_hours_to_courses_table ............ 23.45ms DONE

php artisan db:seed --class=CourseSeeder
INFO  Seeding database.

php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test
Tests:    262 passed (1113 assertions)
Duration: 24.39s
```

## Catatan dan batasan

- Kuota belum menolak pengajuan yang melebihi batas. Halaman ini pemantauan,
  bukan gerbang persetujuan. Penolakan otomatis butuh keputusan bisnis
  terpisah.
- Belum ada layar pengelolaan mata kuliah/kuota. Nilai kuota diisi lewat
  seeder atau basis data sampai ada layar khusus (mis. Organization Admin).
- Tidak dibuat: transfer kuota antar-MK, dispensasi dekan, ekspor XLS, dan
  proyeksi pekan tersisa. Semua butuh aturan/anggaran yang belum ada.
- Jam dihitung dari tanggal (hari × 24), bukan jam pemakaian sebenarnya.
- Verifikasi visual browser belum dilakukan.
