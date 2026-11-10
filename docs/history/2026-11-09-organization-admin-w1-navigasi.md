# Organization Admin W1: Perombakan Navigasi Sidebar

Tanggal: 2026-11-09
Jenis pekerjaan: frontend (navigasi role-aware)
Status: selesai
Branch: `feature/operator-operasional`

## Tujuan

Menyusun ulang sidebar `organization_admin` mengikuti fokus pemilik organisasi
(W2): Inventaris, Peminjaman, Operasional, Organisasi. Tanpa route baru, tanpa
perubahan otorisasi, tanpa schema.

## Perubahan

`resources/views/layouts/admin.blade.php`:

- Blok `$groups` khusus `organization_admin`:
  - Inventaris: Aset, Kategori.
  - Peminjaman: Antrean Persetujuan, Semua Transaksi, Kalender Ketersediaan.
  - Operasional: Serah Terima, Pemeriksaan, Maintenance.
  - Organisasi: Pengguna & Role, Laporan & Analitik, Pengaturan, Log Aktivitas.
- Tautan Dashboard tetap memakai tautan mandiri di atas grup (tidak
  diduplikasi di dalam grup Utama).
- Role operator, admin prodi, dan technician tidak berubah.

`lang/id/ui.php`:

- `ui.sections.borrowings`, `ui.sections.organization`.
- `ui.nav.users_roles`, `ui.nav.reports_analytics`.

`tests/Feature/Admin/ShellTest.php`:

- Urutan grup organisasi diperbarui; label baru ikut diuji.

## File Tersentuh

- `resources/views/layouts/admin.blade.php`
- `lang/id/ui.php`
- `tests/Feature/Admin/ShellTest.php`
- `docs/history/2026-11-09-organization-admin-w1-navigasi.md`

## Verifikasi

```
php artisan test tests/Feature/Admin/ShellTest.php
Tests: 4 passed (41 assertions)
Duration: 0.95s
```

## Batasan

- Hanya grup dan label. Isi layar W2–W13 belum dirombak.
- Isu otorisasi dari audit (serah terima hanya lihat untuk Organization Admin,
  cakupan route inspeksi, pengaturan operator) belum dikoreksi karena butuh
  keputusan backend terpisah.
- Tidak dibuat: PDF, pilihan semester, logo, prefiks kode, jam layanan,
  backup, dan toggle automasi tambahan.
- Verifikasi visual browser belum dilakukan.
