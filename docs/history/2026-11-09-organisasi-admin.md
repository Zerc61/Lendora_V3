# Organisasi Admin: Dashboard, Aset, Kategori, Pengguna, Laporan, Pengaturan

Tanggal: 2026-11-09
Jenis pekerjaan: frontend
Status: selesai

## Tujuan

Menyelaraskan halaman Organization Admin dengan mockup Stitch W2, W3, W5, W11,
W12, dan W13 tanpa mengubah route, validasi, otorisasi, atau aturan bisnis:

- Dashboard menampilkan KPI berikon, aksi cepat, dan umpan aktivitas.
- Daftar aset menampilkan ringkasan status inventaris di atas tabel.
- Kategori menampilkan jumlah induk, sub-kategori, dan unit terkategori.
- Pengguna menampilkan ringkasan akun dan avatar inisial.
- Laporan memakai kartu KPI berikon dengan keterangan.
- Pengaturan memakai banner status aktif.

Form tambah/edit aset (W4) tidak diubah karena sudah memakai kartu formulir
yang berfungsi.

## Perubahan

### Dashboard

`admin/dashboard.blade.php`:

- KPI Organization Admin memakai ikon dan hint per metrik.
- Umpan "Log aktivitas terbaru" diubah dari tabel menjadi daftar kartu dengan
  tautan "Lihat semua" ke halaman log aktivitas.
- KPI operator, prodi, dan teknisi tidak diubah.

Tidak ada query baru; `recentActivity` sudah dikirim controller.

### Aset

`Admin/AssetController@index` menambah `$summary` dari hitungan status aset
organisasi (total, tersedia, dipinjam, maintenance).

`admin/assets/index.blade.php` menambah empat kartu KPI di atas filter. Tabel,
filter, paginasi, tombol tambah/edit/nonaktifkan, dan modal tetap.

### Kategori

`Admin/CategoryController@index` menambah `$summary`: jumlah kategori induk,
sub-kategori, dan unit terkategori.

`admin/categories/index.blade.php` menambah tiga kartu KPI di atas pohon
kategori. Struktur pohon, tombol toggle, edit, dan hapus tidak berubah.

### Pengguna

`Admin/UserController@index` menambah `$summary`: total akun, aktif,
ditangguhkan, dan jumlah peminjam. Metode `summary()` private di controller.

`admin/users/index.blade.php` menambah empat kartu KPI dan avatar inisial pada
kolom nama. Filter peran, status, ubah, tangguhkan, dan aktifkan tetap.

### Laporan

`admin/reports/index.blade.php` menambah ikon dan hint pada lima kartu KPI.
Filter periode/kategori, ekspor CSV, tabel utilisasi, tren bulanan, aset
teratas, peminjam teratas, dan kerusakan terbaru tidak berubah.

### Pengaturan

`admin/settings/edit.blade.php` menambah banner "Pengaturan aktif" di atas
formulir. Field identitas organisasi, kebijakan pinjam, pengingat H-1, dan
pengumuman tidak berubah.

### Gaya dan bahasa

`public/css/admin.css` menambah:

- `.ldr-user-cell` dan `.ldr-avatar` untuk sel pengguna.
- `.ldr-activity-list` dan `.ldr-activity-item` untuk umpan aktivitas.
- `.ldr-settings-banner` untuk banner pengaturan.

`lang/id/ui.php` menambah kunci `ui.asset.index_description`/`kpi_*`,
`ui.category.kpi_*`, `ui.user.kpi_*`, `ui.report.hint_*`,
`ui.setting.active_*`, serta `ui.dashboard.recent_activity_hint` dan
`ui.dashboard.view_all`, dan hint `ui.dashboard.admin.hint_*`.

Semua memakai token orange + putih yang sudah ada.

## File Tersentuh

Diubah:

- `app/Http/Controllers/Admin/AssetController.php`
- `app/Http/Controllers/Admin/CategoryController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/assets/index.blade.php`
- `resources/views/admin/categories/index.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/reports/index.blade.php`
- `resources/views/admin/settings/edit.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/AssetCrudTest.php`
- `tests/Feature/Admin/UserManagementTest.php`
- `tests/Feature/Admin/ReportTest.php`
- `tests/Feature/Admin/PageSmokeTest.php`
- `docs/history/README.md`
- `docs/rencana/menu-per-role.md`

Dibuat:

- `docs/history/2026-11-09-organisasi-admin.md`

## Verifikasi

Blade cache:

```
INFO  Blade templates cached successfully.
```

Seluruh tes:

```
Tests:    138 passed (500 assertions)
Duration: 9.87s
```

Tes baru memastikan:

- Halaman aset merender KPI "Total unit aset" dan kode aset.
- Halaman pengguna merender KPI "Total terdaftar" dan nama operator.
- Halaman laporan merender hint KPI.
- Smoke test kini memuat halaman aset dan kategori.

## Risiko / Catatan Lanjutan

- Verifikasi visual browser belum dilakukan; mesin tidak memiliki Chromium atau
  Firefox. Verifikasi memakai tes HTTP dan Blade cache.
- Form tambah/edit aset dan halaman log aktivitas belum diubah.
- Tombol dan aksi tetap mengikuti otorisasi yang sudah ada; tidak ada aturan
  bisnis baru.
- Ringkasan memakai query agregat tambahan; jumlah query bertambah satu per
  halaman aset, kategori, dan pengguna.
