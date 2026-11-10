# Laporan Fase Operator — FRONTEND

Tanggal: 2026-11-09
Rencana: `docs/rencana/2026-11-09-rencana-operator-mockup.md`

## Peta mockup → halaman

| Mockup | Halaman | Status |
|---|---|---|
| W1 Dashboard operator | `admin/dashboard` varian operator | SELESAI |
| W3 Daftar aset | `admin/assets/index` | SELESAI |
| W6 Antrean persetujuan | `admin/bookings/approval` | SELESAI |
| W7 Detail booking | `admin/bookings/show` | SELESAI |
| W8 Serah terima | `admin/bookings/handover`, `admin/handovers/index` | SELESAI |
| W9 Inspeksi | `admin/inspections/index|create|show` | SELESAI |
| W10 Maintenance | `admin/maintenances/index|create|show` | SELESAI |
| W12 Laporan | `admin/reports/index`, `admin/reports/print` | SELESAI |
| O1a/O1b Kiosk scanner | `admin/kiosk/index` | SELESAI |
| O2 Kalender Gantt | `admin/bookings/calendar` | SELESAI |
| O3 BAP & denda | `admin/penalties/index|create|show|print`, `admin/payments/index` | SELESAI |
| O4 Kiosk antrean | `kiosk/*`, `admin/kiosk/queue` | SELESAI |

## Perubahan UI utama

- Shell operator: sidebar 3 grup sesuai mockup, menu di luar izin tidak tampil.
- Dashboard W1: KPI, antrean hari ini, jadwal, lemari, shift aktif.
- Antrean persetujuan W6: kartu berjenjang urgensi, avatar peminjam, daftar
  unit berfoto + status ketersediaan, banner konflik, unit alternatif dengan
  aksi alihkan, panel kebijakan lab dan unit cadangan.
- Detail booking W7: pil periode, tombol cetak bukti pinjam dan WhatsApp,
  kutipan tujuan, tindakan cepat (lapor kerusakan, kembali, antrean).
- Gantt O2: KPI okupansi/aktif/menunggu serah/konflik, banner konflik, filter
  14 hari/30 hari/bulan, legenda, timeline per kategori, panel detail jadwal,
  slot menganggur, ekspor CSV/iCal.
- Daftar aset W3: KPI, filter kategori/status/kondisi/lemari, chip filter
  aktif, ekspor CSV, tabel berfoto, tombol kelola hanya Organization Admin.
- Laporan W12: KPI, batang utilisasi kategori, sparkline tren, rekap kategori,
  top borrower, kerusakan, ekspor CSV, halaman print.
- BAP O3: index/create/show/print, unggah foto temuan, baris biaya dinamis,
  verifikasi pembayaran dengan KPI.

## File penting

Create:

```
resources/views/kiosk/*
resources/views/admin/kiosk/index.blade.php
resources/views/admin/kiosk/queue.blade.php
resources/views/admin/penalties/{index,create,show,print}.blade.php
resources/views/admin/payments/index.blade.php
resources/views/admin/maintenances/show.blade.php
resources/views/admin/inspections/show.blade.php
resources/views/admin/bookings/receipt.blade.php
public/css/kiosk.css
```

Modify:

```
resources/views/layouts/admin.blade.php
resources/views/admin/dashboard.blade.php
resources/views/admin/bookings/{index,approval,show,calendar,handover}.blade.php
resources/views/admin/assets/index.blade.php
resources/views/admin/reports/{index,print}.blade.php
resources/views/admin/inspections/{index,create}.blade.php
resources/views/admin/maintenances/{index,create}.blade.php
resources/views/components/icon.blade.php
public/css/admin.css
public/js/admin.js
lang/id/ui.php
lang/id/messages.php
```

## JavaScript

Vanilla, tanpa build step: tab, modal, konfirmasi, signature pad, jam kiosk,
shortcut keyboard, polling antrean, baris biaya dinamis, panel detail Gantt.

## Verifikasi

```
php artisan view:cache        -> Blade cached
node -e "..." public/js/admin.js -> sintaks OK
php artisan test              -> 242 passed (997 assertions)
```

## Deviations

- Sidebar/aktivitas terakhir pada W6 tidak memakai feed real-time; panel
  menampilkan kebijakan dan unit cadangan dari data antrean.
- Detail jadwal Gantt memakai pemilihan blok via JS, bukan drawer server-side.
- Tidak ada generator PDF; print memakai CSS.
- Tombol "Riwayat Keputusan" dan "Pemeriksaan Otomatis" pada mockup W6 belum
  dibuat karena route-nya belum ada.
