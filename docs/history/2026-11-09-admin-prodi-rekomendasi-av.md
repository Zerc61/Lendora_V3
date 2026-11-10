# Admin Prodi: Menu Rekomendasi AV

Tanggal: 2026-11-09
Jenis pekerjaan: frontend + route baca-saja
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Melengkapi menu terakhir Admin Prodi dari mockup P6: halaman Rekomendasi AV
terpisah dari Laporan Semester.

## Perubahan

- `app/Http/Controllers/Admin/RecommendationController.php` (baru):
  memakai ulang `SemesterReportService`, hanya membaca.
- `resources/views/admin/recommendations/index.blade.php` (baru):
  KPI kategori kritis, permintaan ditolak, usulan unit, estimasi anggaran;
  tabel usulan dengan unit saat ini, diterima/total pengajuan, penolakan,
  unit tambahan, harga satuan, subtotal, prioritas, dan total RAB.
- `routes/web.php`: `GET /admin/recommendations` → `admin.recommendations.index`,
  middleware `role:admin_prodi`.
- `resources/views/layouts/admin.blade.php`: item "Rekomendasi AV" di grup
  Laporan & Dokumen.
- `lang/id/ui.php`: `ui.nav.recommendations`, grup `ui.recommendation.*`.
- `tests/Feature/Admin/RecommendationTest.php` (baru): render, empty state,
  403 untuk role lain.
- `tests/Feature/Admin/ShellTest.php`: tautan menu baru.

## Verifikasi

```
php artisan test
Tests:    282 passed (1199 assertions)
Duration: 26.77s
```

## Batasan

- Data sama dengan estimasi RAB pada laporan semester.
- Tidak ada aksi tulis, persetujuan, atau kirim ke dekanat.
- Verifikasi visual browser belum dilakukan.
