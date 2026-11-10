# Admin Prodi B1: Daftar Mahasiswa & Skor Kepatuhan (P5)

Tanggal: 2026-11-09
Jenis pekerjaan: frontend + service baca-saja
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Menambah halaman pemantauan mahasiswa untuk Admin Prodi sesuai mockup P5,
memakai data yang sudah ada. Tanpa migration, tanpa aksi sanksi, tanpa
perubahan wewenang.

## Perubahan

### Route

`routes/web.php`:

- `GET /admin/students` → `admin.students.index`, middleware `role:admin_prodi`.
- Route baru ini khusus prodi; operator, organization admin, dan teknisi
  mendapat 403.

### Service

`app/Services/StudentComplianceService.php` (baru):

- Daftar mahasiswa (role borrower) per organisasi dengan pencarian
  nama/NIM/email, filter tier, filter status akun, dan urutan skor/nama.
- Ringkasan: total mahasiswa aktif, rata-rata skor, tier A+ (>= 90),
  pembinaan (< 60), jumlah BAP, total denda, BAP belum lunas.
- Sesi dan hari pinjam dihitung dari booking `approved`, `active`, `returned`.
- Tier memakai `BorrowerScoreService::tierFor()`; skor `null` tidak
  dinaikkan diam-diam menjadi 100.

`app/Services/BorrowerScoreService.php`:

- Tambah `tierFor(?int $score): ?string`; `getTier()` memakai metode itu.

### Controller dan view

- `app/Http/Controllers/Admin/StudentController.php` (baru): validasi filter
  ringan, lalu memanggil service.
- `resources/views/admin/students/index.blade.php` (baru): 4 KPI, filter bar,
  tabel mahasiswa (identitas, skor + tier, sesi/hari pinjam, BAP + denda,
  status akun, aksi "Lihat pinjaman"), pagination, empty state.
- Aksi "Lihat pinjaman" menuju `admin.bookings.index` dengan
  `status=all` dan `search=<nama>`; tidak ada halaman detail mahasiswa baru.

### Navigasi

`resources/views/layouts/admin.blade.php`:

- Item "Mahasiswa & Kepatuhan" di grup Monitoring Prodi, sebelum Aset.

### Bahasa

`lang/id/ui.php`:

- `ui.nav.students` dan grup `ui.student.*`.

## File Tersentuh

Dibuat:

- `app/Services/StudentComplianceService.php`
- `app/Http/Controllers/Admin/StudentController.php`
- `resources/views/admin/students/index.blade.php`
- `tests/Feature/Admin/StudentComplianceTest.php`
- `docs/history/2026-11-09-admin-prodi-b1-mahasiswa.md`

Diubah:

- `app/Services/BorrowerScoreService.php`
- `routes/web.php`
- `resources/views/layouts/admin.blade.php`
- `lang/id/ui.php`
- `tests/Feature/Admin/ShellTest.php`

## Verifikasi

```
php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test
Tests:    256 passed (1078 assertions)
Duration: 23.69s
```

## Catatan

- Sesuai mockup, kolom durasi memakai hari karena `bookings` hanya menyimpan
  tanggal, bukan jam. Tidak ada jam yang dikarang.
- Tidak dibuat: ekspor CSV, peringatan kolektif WhatsApp, kebijakan pembinaan,
  proses BAP, dan sanksi. Semua itu butuh keputusan/aturan terpisah.
- Halaman ini baca-saja; tidak mengubah status akun atau skor.
