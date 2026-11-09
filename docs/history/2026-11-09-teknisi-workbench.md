# Technician: Dashboard Workbench dan Tugas Maintenance

Tanggal: 2026-11-09
Jenis pekerjaan: frontend
Status: selesai

## Tujuan

Menyelaraskan area Technician dengan mockup dashboard teknisi dan work order
tanpa mengubah route, validasi, otorisasi, atau aturan bisnis:

- Dashboard menampilkan KPI tugas dan kartu tugas milik teknisi.
- Daftar maintenance menampilkan konteks tugas dan aksi mulai/selesai.
- Form selesai maintenance tampil sebagai lembar kerja dengan detail unit.

Fitur mockup yang di luar MVP tidak dibuat: suku cadang, kalibrasi, checklist
QC, dan tanda tangan digital.

## Perubahan

### Dashboard

`Admin/DashboardController` menambah eager load `asset.photos` dan `reportedBy`
pada tugas teknisi.

`admin/dashboard.blade.php`:

- KPI teknisi memakai ikon dan hint: terjadwal, dikerjakan, selesai bulan ini.
- Tabel "Tugas saya" diganti kartu work order: foto unit, kode, status,
  deskripsi, pelapor, waktu mulai, dan aksi mulai/selesai.
- `my_tasks` hanya tampil bila teknisi punya tugas.

### Daftar maintenance

`admin/maintenances/index.blade.php`:

- Subjudul khusus teknisi.
- Deskripsi tugas ditampilkan di bawah kode aset.
- KPI, thumbnail, filter, dan aksi role tetap.

### Form selesai maintenance

`Admin/MaintenanceController@finishForm` menambah eager load `asset.photos`.

`admin/maintenances/finish.blade.php`:

- Hero unit: kode, status, nama, merek/model, pelapor, teknisi, waktu mulai,
  dan foto unit.
- Kartu "Lembar kerja" berisi deskripsi, pelapor, dan waktu mulai.
- Form hasil tetap memakai field `result_notes`, `cost`, `condition_good`.
- Sidebar ringkasan status, teknisi, dan biaya.

### Gaya dan bahasa

`public/css/admin.css` menambah `.ldr-workorder-list`, `.ldr-workorder`,
`__media`, `__body`, `__head`, `__meta`, dan `__actions`.

`lang/id/ui.php` menambah `ui.maintenance.technician_description`,
`back_to_list`, `work_order`, `started_at`, `finish_hint`, `summary`, serta
`ui.dashboard.technician.hint_*`.

## File Tersentuh

Diubah:

- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/MaintenanceController.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/maintenances/index.blade.php`
- `resources/views/admin/maintenances/finish.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/MaintenanceTest.php`
- `docs/history/README.md`
- `docs/rencana/menu-per-role.md`

Dibuat:

- `docs/history/2026-11-09-teknisi-workbench.md`

## Verifikasi

Blade cache:

```
INFO  Blade templates cached successfully.
```

Seluruh tes:

```
Tests:    139 passed (506 assertions)
Duration: 10.02s
```

Tes baru memastikan dashboard teknisi merender "Tugas saya" dan deskripsi tugas,
serta form selesai merender "Lembar kerja".

## Risiko / Catatan Lanjutan

- Akses aset untuk teknisi masih 403; perlu keputusan membuka baca-saja atau
  menyembunyikan menu.
- Suku cadang, kalibrasi, checklist QC, dan lampiran foto kerja belum ada
  backend-nya.
- Verifikasi visual browser belum dilakukan; mesin tanpa Chromium/Firefox.
