# Admin Prodi B4: Laporan Semester & Rekomendasi RAB (P6)

Tanggal: 2026-11-09
Jenis pekerjaan: service + frontend (tanpa perubahan schema)
Status: selesai (tanpa generator RAB)
Branch: `feature/operator-operasional`

## Tujuan

Memberi Admin Prodi rekap semesteran: pemanfaatan jam alat per kategori,
tingkat penolakan permintaan, dan biaya pemeliharaan, plus blok pengesahan
dokumen. Bagian "Smart RAB Generator" pada mockup TIDAK dibuat karena tidak ada
data harga pengadaan maupun aturan kelayakan yang disetujui.

## Service

`app/Services/SemesterReportService.php` (baru):

- Periode dari setting `semester_start`/`semester_end`.
- Baris per kategori: jumlah unit (aset non-retired), jam permintaan, jam
  kapasitas, pemanfaatan, jumlah pengajuan, jumlah ditolak, rasio penolakan.
- Jam permintaan = jumlah unit dipinjam × durasi hari × 12 jam operasional.
- Jam kapasitas = jumlah unit × 12 jam × jumlah hari periode.
- Status baris: `kritis` (penolakan ≥ 20% atau pemanfaatan ≥ 80%),
  `perlu_tambahan` (penolakan ≥ 10%), `seimbang` (lainnya).
- KPI: jam terpakai, pemanfaatan total, biaya pemeliharaan, rasio penolakan,
  jumlah kategori kritis.
- Biaya pemeliharaan = `cost` + `labor_cost` + subtotal `maintenance_parts`
  untuk maintenance yang dibuat di dalam periode.
- Identitas dokumen: `SK-LAB-{ORG}/{tahun}/SMT-{GANJIL|GENAP}`, diturunkan dari
  organisasi dan tanggal mulai semester.

## Halaman

`ReportController@semester` → `GET /admin/reports/semester` →
`admin.reports.semester`, middleware `role:admin_prodi`.

`resources/views/admin/reports/semester.blade.php` (baru):

- Header + chip semester.
- 4 KPI.
- Tabel analisis per kategori dengan bar pemanfaatan dan badge status.
- Blok pengesahan: prodi, organisasi, nomor dokumen, waktu pembuatan.
- Tanpa tombol tulis; prodi tetap baca-saja.

## Navigasi, gaya, bahasa

- `resources/views/layouts/admin.blade.php`: "Laporan Semester" di grup
  Laporan & Dokumen, sebelum "Laporan".
- `public/css/admin.css`: blok `/* Admin Prodi — laporan semester */`.
- `lang/id/ui.php`: `ui.nav.semester_report`, grup `ui.semester_report.*`.

## File Tersentuh

Dibuat:

- `app/Services/SemesterReportService.php`
- `resources/views/admin/reports/semester.blade.php`
- `tests/Unit/SemesterReportServiceTest.php`
- `tests/Feature/Admin/SemesterReportTest.php`
- `docs/history/2026-11-09-admin-prodi-b4-laporan-semester.md`

Diubah:

- `app/Http/Controllers/Admin/ReportController.php`
- `routes/web.php`
- `resources/views/layouts/admin.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/ShellTest.php`

## Verifikasi

```
php artisan test
Tests:    273 passed (1164 assertions)
Duration: 26.01s
```

Cek data lokal:

```
{"demand_hours":696,"capacity_hours":45900,"utilization":1.5,
 "rejected":1,"rejection_rate":5.3,"incidents":3,
 "maintenance_cost":1100000,"bottlenecks":1}
8 kategori
SK-LAB-LDR/2026/SMT-GANJIL
```

## Batasan

- Tidak ada generator RAB, estimasi harga, prioritas pengadaan, atau proyeksi
  mahasiswa. Semua butuh data harga dan aturan bisnis yang belum disetujui.
- Tidak ada ekspor PDF/XLS dan kirim ke dekanat. Hanya halaman HTML.
- Jam permintaan memakai asumsi 12 jam operasional per hari; peminjaman hanya
  menyimpan tanggal, bukan jam.
- Pemanfaatan total terlihat kecil karena kapasitas dihitung penuh sepanjang
  semester (unit × 12 jam × jumlah hari).
- Verifikasi visual browser belum dilakukan.
