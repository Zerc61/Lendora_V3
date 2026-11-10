# Admin Prodi: Dashboard P1, Otorisasi Khusus P2, Panel Verifikasi

Tanggal: 2026-11-09
Jenis pekerjaan: frontend + service baca-saja
Status: selesai (Fase A)
Branch: `feature/operator-operasional`

## Tujuan

Menyelaraskan area Admin Prodi dengan mockup P1 dan P2 memakai data yang sudah
ada, tanpa migration dan tanpa menambah wewenang baru.

Rencana lengkap: `docs/rencana/2026-11-09-rencana-admin-prodi-mockup.md`.

## Perubahan

### Sidebar

`resources/views/layouts/admin.blade.php`:

- Cabang `admin_prodi` mengganti grup generik:
  - Otorisasi → Otorisasi Khusus (badge antrean).
  - Monitoring Prodi → Aset, Kategori (baca-saja).
  - Laporan & Dokumen → Laporan.
- Label dashboard menjadi "Dashboard Prodi".
- Menu operator/organization admin (pengguna, pengaturan, log, serah terima,
  inspeksi, perawatan, kalender, kiosk) tidak dirender untuk prodi.

`app/Providers/AppServiceProvider.php`:

- `sidebarCounts['special_approvals']` dihitung dari booking `pending` yang
  `isLongDuration(maxLoanDays)` dan belum lewat tanggal mulai.

### Dashboard

`app/Services/ProdiDashboardService.php` (baru):

- KPI: pending, special, active, returned_this_month, students,
  low_compliance.
- Antrean khusus memakai `ApprovalQueueService::card()`.
- Definisi `special` untuk KPI dan antrean sekarang sama.
- `categories`: permintaan unit per kategori dari antrean.
- `rules`: `max_loan_days` dan `operator_approval_limit`.
- `semester`: label dari setting `semester_start`/`semester_end`.

`app/Http/Controllers/Admin/DashboardController.php`:

- `prodiKpis()` dihapus; data datang dari service.
- `$specialQueue` diganti `$prodiDashboard`.

`resources/views/admin/dashboard.blade.php`:

- Header prodi + chip semester.
- 6 KPI berikon.
- Banner jumlah antrean.
- Kartu antrean: kode, nama, NIM, prodi, semester, tujuan, durasi, nilai aset,
  periode, jumlah unit, 2 nama unit, tombol Tinjau dan Setujui.
- Tombol Setujui disabled bila `can_decide` false, dengan alasan.
- Empty state.
- Rail: ketentuan otorisasi dan permintaan per kategori.

### Antrean Otorisasi Khusus

`app/Services/ApprovalQueueService.php`:

- Filter baru `scope=special` (durasi > `max_loan_days`).

`app/Http/Controllers/Admin/BookingController.php`:

- Role `admin_prodi` otomatis memakai `scope=special`.

`resources/views/admin/bookings/approval.blade.php`:

- Judul/eyebrow/subtitle khusus prodi.
- KPI "Menunggu Otorisasi".
- `lab_tier` dan status verifikasi akademik pada kartu.
- Label aksi "Setujui & Otorisasi" untuk prodi.

### Detail Booking

`app/Http/Controllers/Admin/BookingController.php`:

- `$prodiReview` berisi kartu antrean, skor kepatuhan, nilai aset, alasan
  eskalasi, dan batas otorisasi.

`resources/views/admin/bookings/show.blade.php`:

- Panel "Verifikasi Otorisasi Prodi" untuk pengajuan durasi khusus:
  - alasan eskalasi durasi/nilai,
  - identitas akademik (NIM, prodi, semester, angkatan, konsentrasi, DPA),
  - skor kepatuhan + tier,
  - status verifikasi akademik,
  - nilai aset,
  - checklist aturan.
- Tombol "Setujui & Otorisasi" hanya untuk pengajuan yang boleh diputus.

Wewenang tidak berubah: Admin Prodi tetap hanya menyetujui pengajuan durasi
khusus, tidak menolak.

### Laporan

`resources/views/admin/reports/index.blade.php`:

- Eyebrow "Laporan & Dokumen Prodi" dan judul "Laporan Prodi" untuk role prodi.
- Filter, cetak, dan ekspor tetap.

### CSS dan bahasa

- `public/css/admin.css`: blok `/* Admin Prodi */` (header meta, alert, grid,
  rail, panel, bar kategori, kartu, review, escalation, identity, responsif).
- `lang/id/ui.php`: kunci `ui.nav.dashboard_prodi`, `ui.nav.otorisasi_khusus`,
  `ui.sections.prodi_*`, `ui.sections.reports_documents`,
  `ui.dashboard.prodi.students|low_compliance`, `ui.approval.prodi_*`,
  `ui.approval.academic_*`, dan grup `ui.prodi.*`.

## File Tersentuh

Dibuat:

- `app/Services/ProdiDashboardService.php`
- `tests/Feature/Admin/ProdiDashboardTest.php`
- `tests/Unit/ProdiDashboardServiceTest.php`
- `docs/rencana/2026-11-09-rencana-admin-prodi-mockup.md`
- `docs/history/2026-11-09-admin-prodi-mockup.md`

Diubah:

- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/BookingController.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/ApprovalQueueService.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/bookings/approval.blade.php`
- `resources/views/admin/bookings/show.blade.php`
- `resources/views/admin/reports/index.blade.php`
- `resources/views/layouts/admin.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/ApprovalQueueTest.php`
- `tests/Feature/Admin/ShellTest.php`

## Verifikasi

```
php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test
Tests:    250 passed (1052 assertions)
Duration: 24.63s
```

## Catatan Lanjutan

- P3 kuota/utilisasi MK, P4 kalender praktikum, P5 daftar mahasiswa, dan P6
  laporan semester/RAB ditunda: butuh schema dan aturan baru.
- Chip semester memakai setting, belum ada pemilih semester.
- Definisi khusus tetap durasi > `max_loan_days`; nilai aset hanya informasi,
  bukan syarat otorisasi baru.
- Verifikasi visual browser belum dilakukan.
