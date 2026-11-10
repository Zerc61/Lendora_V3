# Admin Prodi B3: Kalender Praktikum & Studio (P4)

Tanggal: 2026-11-09
Jenis pekerjaan: schema + service + frontend
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Memberi Admin Prodi kalender mingguan jadwal praktikum per studio dan deteksi
bentrok slot pada ruang yang sama, memakai skema baru yang minimal.

## Perubahan schema

`database/migrations/2026_11_09_000031_create_rooms_table.php`:

- `rooms`: organisasi, kode, nama, kapasitas, catatan, status aktif.
- Unik per organisasi + kode.

`database/migrations/2026_11_09_000032_create_practicum_schedules_table.php`:

- `practicum_schedules`: organisasi, mata kuliah, ruang, hari (1–7), jam mulai,
  jam selesai, status aktif.
- Index ruang + hari dan organisasi + hari.

Model baru: `Room`, `PracticumSchedule` (relasi organisasi, mata kuliah, ruang).

Seeder:

- `RoomSeeder`: 4 ruang demo (Studio AV 1, Green Screen 2, Lab Sound, Dry Box).
- `PracticumScheduleSeeder`: 5 slot mingguan pada 4 mata kuliah, tanpa bentrok.
- Keduanya didaftarkan di `DatabaseSeeder` setelah `CourseSeeder`.

## Service

`app/Services/PracticumScheduleService.php` (baru):

- Mengambil ruang aktif organisasi beserta jadwal aktif dan mata kuliahnya.
- Bentrok = dua slot pada ruang dan hari yang sama dengan jam bertumpuk.
  Slot yang bersambung (selesai 11:00, mulai 11:00) bukan bentrok.
- KPI: jumlah ruang, jam terjadwal per minggu, kapasitas, persentase, jumlah
  bentrok.
- Kapasitas memakai asumsi 08:00–20:00, Senin–Jumat, dikali jumlah ruang.

## Halaman

`app/Http/Controllers/Admin/PracticumScheduleController.php` (baru):

- `GET /admin/practicum` → `admin.practicum.index`, middleware `role:admin_prodi`.

`resources/views/admin/practicum/index.blade.php` (baru):

- Header + chip semester.
- Banner bentrok berisi ruang, hari, dan dua mata kuliah yang bertumpuk.
- 4 KPI.
- Matriks ruang × hari (Senin–Minggu); tiap sel berisi chip jam, kode/nama
  mata kuliah, dan dosen. Chip bentrok diberi warna bahaya.
- Legenda dan empty state.

## Navigasi, gaya, bahasa

- Sidebar Monitoring Prodi: "Jadwal Praktikum" di antara kuota MK dan
  mahasiswa.
- `public/css/admin.css`: blok `/* Admin Prodi — jadwal praktikum */`.
- `lang/id/ui.php`: `ui.nav.practicum`, grup `ui.practicum.*`.

## File Tersentuh

Dibuat:

- `app/Models/Room.php`
- `app/Models/PracticumSchedule.php`
- `app/Services/PracticumScheduleService.php`
- `app/Http/Controllers/Admin/PracticumScheduleController.php`
- `resources/views/admin/practicum/index.blade.php`
- `database/migrations/2026_11_09_000031_create_rooms_table.php`
- `database/migrations/2026_11_09_000032_create_practicum_schedules_table.php`
- `database/seeders/RoomSeeder.php`
- `database/seeders/PracticumScheduleSeeder.php`
- `tests/Unit/PracticumScheduleServiceTest.php`
- `tests/Feature/Admin/PracticumScheduleTest.php`
- `docs/history/2026-11-09-admin-prodi-b3-jadwal-praktikum.md`

Diubah:

- `routes/web.php`
- `resources/views/layouts/admin.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `database/seeders/DatabaseSeeder.php`
- `tests/Feature/Admin/ShellTest.php`

## Verifikasi

```
php artisan migrate
2026_11_09_000031_create_rooms_table .......................... 74.54ms DONE
2026_11_09_000032_create_practicum_schedules_table ........... 135.29ms DONE

php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=PracticumScheduleSeeder
INFO  Seeding database.

php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test
Tests:    268 passed (1136 assertions)
Duration: 25.63s
```

## Batasan

- Belum ada layar plotting/edit jadwal. Data jadwal diisi lewat seeder atau
  basis data sampai ada layar pengelolaan.
- Tidak dibuat: sinkronisasi SIAKAD, cetak PDF, dispensasi tugas akhir,
  pemeliharaan ruang, dan reservasi slot mandiri mahasiswa. Semua butuh
  integrasi atau aturan baru.
- Bentrok hanya membandingkan jadwal praktikum pada ruang yang sama; bentrok
  dengan peminjaman aset tidak dihitung karena peminjaman tidak menyimpan ruang.
- Kapasitas memakai asumsi jam operasional 08:00–20:00, Senin–Jumat.
- Verifikasi visual browser belum dilakukan.
