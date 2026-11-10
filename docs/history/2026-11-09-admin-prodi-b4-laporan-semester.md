# Admin Prodi B4: Laporan Semester & Rekomendasi RAB (P6)

Tanggal: 2026-11-09
Jenis pekerjaan: service + frontend (tanpa perubahan schema)
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Memberi Admin Prodi rekap semesteran: pemanfaatan jam alat per kategori,
tingkat penolakan permintaan, biaya pemeliharaan, usulan pengadaan (estimasi
RAB), ekspor CSV, dan blok pengesahan dokumen.

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

### Estimasi RAB

- Hanya kategori dengan permintaan ditolak dan permintaan diterima > 0.
- Unit tambahan = `ceil(unit eksisting × ditolak / diterima)`, minimal 1.
  Artinya kapasitas dinaikkan sampai rasio penolakan hilang.
- Harga satuan = rata-rata `assets.purchase_price` kategori, dibulatkan ke
  Rp 100.000 terdekat. Bila tidak ada harga, kolom harga dan subtotal kosong.
- Prioritas 1 bila status kategori `kritis`, selain itu Prioritas 2.
- Total RAB = jumlah subtotal yang punya harga.

## Halaman

`ReportController@semester` → `GET /admin/reports/semester` →
`admin.reports.semester`, middleware `role:admin_prodi`.

`resources/views/admin/reports/semester.blade.php` (baru):

- Header + chip semester.
- 4 KPI.
- Tabel analisis per kategori dengan bar pemanfaatan dan badge status.
- Blok pengesahan: prodi, organisasi, nomor dokumen, waktu pembuatan.
- Tombol "Ekspor CSV" → `GET /admin/reports/semester/export`.
- Tabel estimasi RAB dengan dasar usulan, unit tambahan, harga satuan, subtotal,
  prioritas, dan total.
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
Tests:    274 passed (1175 assertions)
Duration: 25.89s
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

- RAB adalah estimasi dari harga rata-rata unit eksisting, bukan penawaran
  vendor. Tidak ada proyeksi mahasiswa atau harga pengadaan nyata.
- Tidak ada ekspor PDF/XLS dan kirim ke dekanat. Hanya halaman HTML + CSV.
- Jam permintaan memakai asumsi 12 jam operasional per hari; peminjaman hanya
  menyimpan tanggal, bukan jam.
- Pemanfaatan total terlihat kecil karena kapasitas dihitung penuh sepanjang
  semester (unit × 12 jam × jumlah hari).
- Verifikasi visual browser belum dilakukan.
