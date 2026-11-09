# Redesign Dashboard Operator

Tanggal: 2026-11-08
Jenis pekerjaan: frontend
Status: selesai (dashboard); halaman operator lain menyusul

## Tujuan

Mengganti dashboard operator generik (KPI + tombol cepat) dengan tampilan
operasional sesuai mockup W1: antrean persetujuan dan daftar jatuh tempo
hari ini.

## Perubahan

### Controller

`app/Http/Controllers/Admin/DashboardController.php`

- Menambah `operatorQueue`: booking `pending` organisasi operator, terurut
  tanggal mulai, maksimal 6 baris, eager load `borrower` dan `items.asset`.
- Menambah `operatorDueToday`: booking `active` dengan `due_at` hari ini,
  terurut jam jatuh tempo, maksimal 6 baris.
- Kedua query hanya dijalankan untuk role `operator`. Tidak ada perubahan
  aturan bisnis, route, validasi, atau otorisasi.

### View

`resources/views/admin/dashboard.blade.php`

- Cabang `operator` memakai layout khusus:
  - Judul "Dashboard Operator" dan subjudul operasional.
  - Empat kartu KPI dengan ikon dan keterangan singkat.
  - Panel "Antrean Persetujuan" berisi kode, peminjam, ringkasan unit,
    periode, dan tautan detail.
  - Panel "Jatuh Tempo Hari Ini" berisi kode, unit, peminjam, jam jatuh
    tempo, dan tautan proses serah terima.
  - Keadaan kosong ditampilkan sebagai teks, bukan tabel kosong.
- Role lain (`organization_admin`, `admin_prodi`, `technician`) tetap memakai
  tampilan lama.

### Bahasa

`lang/id/ui.php`

- Menambah kunci `ui.dashboard.operator`: judul, subjudul, judul panel,
  keterangan KPI, keadaan kosong, "Lihat semua", dan "Proses".

### CSS

`public/css/admin.css`

- Menambah `.ldr-kpi__head`, `.ldr-kpi__icon`, `.ldr-dash-grid`,
  `.ldr-card-head`, `.ldr-dash-empty`.
- Grid dua kolom aktif pada layar ≥1200px; di bawahnya panel bertumpuk.
- Warna tetap memakai token orange + putih yang ada.
- Menambah `.ldr-form-grid--spaced`, `.ldr-section-title--flush`, dan
  `.ldr-table__cell--wide` untuk mengganti inline style di halaman operator.

## File Tersentuh

Diubah:

- `app/Http/Controllers/Admin/DashboardController.php`
- `resources/views/admin/dashboard.blade.php`
- `lang/id/ui.php`
- `public/css/admin.css`
- `resources/views/admin/bookings/handover.blade.php`
- `resources/views/admin/reports/index.blade.php`
- `docs/rencana/menu-per-role.md`

Dibuat:

- `docs/history/2026-11-08-redesign-dashboard-operator.md`

## Verifikasi

```
php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test --compact
Tests:    129 passed (454 assertions)
Duration: 8.81s
```

`tests/Feature/Admin/PageSmokeTest.php` dan `ShellTest.php` ikut memuat
dashboard operator, sehingga cabang view baru sudah tersentuh tes.

## Risiko / Catatan Lanjutan

- Verifikasi visual browser belum dilakukan; mesin tidak punya browser.
- Antrean dibatasi 6 baris tanpa paginasi; tautan "Lihat semua" menuju
  halaman antrean penuh.
- Halaman operator lain (antrean, detail, kalender, serah terima, inspeksi,
  maintenance, aset, laporan) belum diselaraskan pada pekerjaan ini.
