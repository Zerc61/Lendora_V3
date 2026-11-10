# Laporan Fase Operator — BACKEND

Tanggal: 2026-11-09
Rencana: `docs/rencana/2026-11-09-rencana-operator-mockup.md`

## Ringkasan

Backend operator dirombak agar seluruh alur loket memakai satu pintu Service:
kiosk, antrean, serah terima, inspeksi, maintenance, BAP/denda, verifikasi
pembayaran, kalender Gantt, antrean persetujuan, dan laporan analitik.

## Skema database

Migration baru (di luar peta MVP awal, sesuai rencana §5):

| Migration | Tabel |
|---|---|
| `000020` | `handover_items` |
| `000021` | `maintenance_parts` |
| `000022` | `penalty_items` |
| `000023` | `queue_tickets` |
| `000024` | `shift_notes` |
| `000025` | `asset_blocks` |
| `000026` | `booking_extensions` |
| `000027` | `inspections` perluasan konteks/follow-up |
| `000028` | `penalties` perluasan BAP |
| `000029` | `maintenances` perluasan tiket/parts |

Enum baru: `InspectionContext`, `InspectionFollowUp`, `MaintenancePriority`,
`QueueServiceType`, `QueueTicketStatus`.

Model baru: `HandoverItem`, `MaintenancePart`, `PenaltyItem`, `QueueTicket`,
`ShiftNote`, `AssetBlock`, `BookingExtension`.

## Service

| Service | Tanggung jawab |
|---|---|
| `HandoverService` | checklist per item, foto, tanda tangan, kode bukti, transisi booking/aset |
| `InspectionService` | konteks, checklist, foto, hasil, pembuatan maintenance otomatis |
| `MaintenanceService` | kode tiket, prioritas, parts, progres, kondisi akhir |
| `BapService` | nomor BAP, item biaya, subsidi, total, issue, foto, auto-suspend |
| `PenaltyService` / `PaymentService` | tagihan dan verifikasi pembayaran |
| `KioskService` | lookup booking/return token/NIM/kode aset, preflight, pickup/return |
| `QueueService` | tiket harian, panggil, selesai, papan monitor |
| `ShiftService` | shift aktif dari jam + settings, catatan serah shift |
| `ApprovalQueueService` | kartu antrean, konflik, alternatif, batas operator |
| `GanttService` | baris unit, bar reservasi, blok maintenance, deteksi konflik |
| `ReportAnalyticsService` | KPI, utilisasi, tren, top borrower, kerusakan |
| `OperatorDashboardService` | KPI dan antrean hari ini |

Aturan status tetap satu pintu: controller hanya memanggil Service; transisi
ilegal ditolak enum/guard.

## Route penting

```
GET  /kiosk                              (publik, token perangkat)
GET  /admin/kiosk                        scanner loket
POST /admin/kiosk/lookup                 cari booking
POST /admin/kiosk/process                pickup/return
GET  /admin/kiosk/queue                  monitor antrean
POST /admin/kiosk/queue/call-next
POST /admin/kiosk/queue/{ticket}/serve
POST /admin/shifts/notes
GET  /admin/bookings/approval
GET  /admin/bookings/calendar
GET  /admin/bookings/calendar/export     CSV / iCal
POST /admin/bookings/{booking}/reassign
GET  /admin/assets/export
GET  /admin/penalties                    BAP
POST /admin/penalties/{penalty}/items
GET  /admin/penalties/{penalty}/print
GET  /admin/payments                     verifikasi
GET  /admin/reports/print
```

## Seeder

`OperatorDemoSeeder` menambahkan data demo operasional: serah terima
pickup/return, parts maintenance, item BAP, tiket antrean hari ini, dan catatan
shift. Seeder sudah terdaftar di `DatabaseSeeder`.

## Verifikasi

```
php artisan test
Tests: 242 passed (997 assertions)
Duration: ~22s
```

Cakupan baru: `KioskTest`, `HandoverTest`, `InspectionTest`, `MaintenanceTest`,
`PenaltyBapTest`, `PaymentVerificationTest`, `ApprovalQueueTest`,
`GanttCalendarTest`, `OperatorDashboardTest`, `OperatorDemoSeederTest`,
`AuthorizationRegressionTest`.

## Catatan

- Sesi kiosk (`kiosk.booking_id`, `kiosk.type`) belum dibersihkan setelah
  proses; pemindaian berikutnya menimpa nilai lama sehingga tidak memblokir,
  tetapi perlu dibersihkan pada iterasi berikut.
- iCal dan CSV ditulis manual tanpa package tambahan.
- Print memakai CSS `@media print`, bukan generator PDF.
