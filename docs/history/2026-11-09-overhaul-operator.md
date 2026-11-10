# Overhaul Operator — 2026-11-09

## Tujuan

Menyesuaikan area operator (loket) dengan mockup Lendora: dashboard, antrean
persetujuan, detail booking, serah terima, inspeksi, maintenance, BAP/denda,
kiosk scanner, antrean mandiri, kalender Gantt, daftar aset, laporan, shift.

## Perubahan

Backend:

- 10 migration baru dan 5 enum baru; 7 model baru.
- Service baru: `KioskService`, `QueueService`, `ShiftService`, `BapService`,
  `GanttService`, `OperatorDashboardService`, `ApprovalQueueService`,
  `ReportAnalyticsService`.
- Perluasan `HandoverService`, `InspectionService`, `MaintenanceService`,
  `PenaltyService`, `PaymentService`.
- Route kiosk publik `/kiosk`, antrean, shift, BAP, pembayaran, Gantt, ekspor,
  walk-in, reassign.
- `OperatorDemoSeeder` mengisi data demo operasional.

Frontend:

- Shell operator, W1, W3, W6, W7, W8, W9, W10, W12, O1a/O1b, O2, O3, O4.
- CSS di `public/css/admin.css` dan `public/css/kiosk.css`; JS vanilla di
  `public/js/admin.js`.
- Terjemahan baru di `lang/id/ui.php` dan `lang/id/messages.php`.

## File Tersentuh

Daftar lengkap ada di `docs/fase-operator-backend.md` dan
`docs/fase-operator-frontend.md`.

## Verifikasi

```
php artisan test
Tests: 242 passed (997 assertions)
```

Pemeriksaan 403 lintas role:

- Borrower tidak bisa membuka `/admin` dan `/admin/kiosk`.
- Technician tidak bisa menyetujui booking atau membuka BAP.
- Operator tidak bisa mengelola pengguna dan pengaturan organisasi.
- Admin Prodi tidak bisa membuka BAP dan kiosk.

## Risiko / Catatan Lanjutan

- Sesi kiosk tidak dibersihkan setelah proses pickup/return; nilai lama
  tertimpa pada pemindaian berikutnya.
- Polling antrean masih berupa reload halaman penuh.
- Halaman print perlu diuji pada printer fisik dan ekspor PDF browser.
- Verifikasi pembayaran langsung mengubah status tanpa dialog konfirmasi dan
  belum ada route pembatalan.
- Belum ada commit/push; seluruh perubahan ada di working tree
  `feature/borrower-portal`.
