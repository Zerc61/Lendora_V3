# Perbaikan Perhitungan Durasi & Bentrok (Prodi)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix backend
Status: selesai
Branch: `feature/operator-operasional`

## Bug yang diperbaiki

### 1. Peminjaman sehari dihitung 0 hari

`Booking::durationInDays()` memakai `diffInDays`, sehingga rentang mulai =
selesai menghasilkan 0. Semua laporan prodi dan tampilan durasi ikut salah.

Perbaikan: durasi inklusif (`diffInDays + 1`, minimal 1). Rentang 1–3 menjadi
3 hari, bukan 2. Batas `max_loan_days` juga ikut lebih ketat: peminjaman 1–8
sekarang dihitung 8 hari.

### 2. Peminjaman lintas batas semester

Kueri memakai `whereBetween('requested_start_date', ...)` sehingga peminjaman
yang mulai sebelum atau berakhir setelah semester hilang atau dihitung penuh.

Perbaikan:

- Kueri menjadi irisan: `start <= semester_end AND end >= semester_start`.
- `Booking::durationInDaysWithin($from, $to)` menghitung hari irisan.
  `CourseQuotaService` dan `SemesterReportService` memakai metode ini.

### 3. Asumsi jam tidak konsisten

Kuota MK memakai 24 jam/hari, laporan semester 12 jam/hari.

Perbaikan: keduanya memakai 12 jam operasional per hari.

### 4. Bentrok jadwal kurang terdeteksi

`PracticumScheduleService` hanya membandingkan pasangan slot berurutan. Tiga
slot bertumpuk menghasilkan 2 bentrok, bukan 3.

Perbaikan: perbandingan semua pasangan pada ruang dan hari yang sama.

## File Tersentuh

- `app/Models/Booking.php`
- `app/Services/CourseQuotaService.php`
- `app/Services/SemesterReportService.php`
- `app/Services/PracticumScheduleService.php`
- `tests/Unit/CourseQuotaServiceTest.php`
- `tests/Unit/PracticumScheduleServiceTest.php`
- `tests/Unit/SemesterReportServiceTest.php`
- `tests/Feature/Admin/CourseQuotaTest.php`
- `tests/Feature/Admin/ProdiDashboardTest.php`
- `tests/Feature/Admin/StudentComplianceTest.php`
- `docs/history/2026-11-09-admin-prodi-b2-kuota-mk.md`
- `docs/history/2026-11-09-admin-prodi-b4-laporan-semester.md`

## Verifikasi

```
php artisan test
Tests:    279 passed (1187 assertions)
Duration: 26.88s
```

## Batasan

- Jam tetap dihitung dari tanggal, bukan jam pemakaian nyata.
- Asumsi 12 jam operasional per hari masih konstanta kode, belum setting.
- Verifikasi visual browser belum dilakukan.
