# Laporan Fase 2 Discovery → Approval — BACKEND

- **Yang dibangun:** schema `bookings` + `booking_items`; model `Booking`/`BookingItem`; `AvailabilityService` lengkap (status + overlap + daftar konflik + saran rentang bebas); `BookingService` (create/approve/reject/cancel + timeline + otorisasi keputusan); `SettingService` (default `max_loan_days`/`operator_approval_limit`); `InvalidStatusTransitionException`; `BookingPolicy`; FormRequest `StoreBookingRequest`/`ApproveBookingRequest`/`RejectBookingRequest`; controller borrower & admin (index, create, store, show, cancel / index, show, approve, reject, calendar); notifikasi database `BookingReceivedNotification` & `BookingDecisionNotification`; `View::composer` unread count borrower shell; stub view sesuai View Contract; test unit + feature.
- **File penting:**
  - Create: `database/migrations/{0007_create_bookings_table,0008_create_booking_items_table,0012_create_notifications_table}.php`, `app/Models/{Booking,BookingItem}.php`, `app/Services/{BookingService,SettingService}.php`, `app/Exceptions/InvalidStatusTransitionException.php`, `app/Policies/BookingPolicy.php`, `app/Http/Controllers/Admin/BookingController.php`, `app/Http/Controllers/Borrower/BookingController.php`, `app/Http/Requests/{StoreBookingRequest,ApproveBookingRequest,RejectBookingRequest}.php`, `app/Notifications/{BookingReceivedNotification,BookingDecisionNotification}.php`, `resources/views/borrower/bookings/{create,show}.blade.php`, `resources/views/admin/bookings/{index,show,calendar}.blade.php`, `tests/Unit/{BookingServiceTest,BookingConcurrencyTest}.php`, `tests/Feature/Borrower/BookingFlowTest.php`, `tests/Feature/Admin/ApprovalQueueTest.php`, `docs/fase-2-discovery-approval-backend.md`.
  - Modify: `app/Services/AvailabilityService.php` (implementasi penuh), `app/Providers/AppServiceProvider.php` (composer unread), `routes/web.php`, `lang/id/{messages,ui}.php`, `resources/views/borrower/bookings/index.blade.php` (stub nyata), `tests/Unit/AvailabilityServiceTest.php` (diperluas).
  - Delete: tidak ada.
- **Migration/seed dijalankan:** ya.
  - `php artisan migrate` → `0007_create_bookings_table` DONE, `0008_create_booking_items_table` DONE, `0012_create_notifications_table` DONE.
- **Test:** `php artisan test` → **91 passed (271 assertions)**, durasi 2.54s.
- **Acceptance criteria fase:**
  - [x] Pengajuan menghasilkan booking `pending` + items dengan kode unik `{ORG}-BK-{YYYY}-{0001}`.
  - [x] Pengajuan bertabrakan ditolak dengan pesan unit + tanggal + kode booking konflik + saran rentang bebas.
  - [x] Approve mengubah aset menjadi `reserved`, mengirim notifikasi database, mencatat `approved_by`/`approved_at`/`due_at` 16:00.
  - [x] Reject wajib alasan (min 5 karakter), aset tetap `available`, borrower dinotifikasi.
  - [x] Cancel: borrower hanya booking `pending` miliknya; operator/org_admin untuk `approved` yang belum diambil; aset `reserved` dilepas kembali.
  - [x] Borrower hanya melihat/membatalkan transaksinya sendiri (403 teruji).
  - [x] Otorisasi: operator di bawah limit nilai & durasi; org_admin semua; admin_prodi hanya durasi > `max_loan_days` (403 pada yang tidak berhak).
  - [x] `php artisan test` lulus penuh.
- **View Contract:**

| View file (exact path) | Route name | Variables passed |
|---|---|---|
| `resources/views/borrower/bookings/index.blade.php` | `borrower.bookings.index` | `$bookings` (paginator), `$tab` (current status), `$counts` (per-status) |
| `resources/views/borrower/bookings/create.blade.php` | `borrower.bookings.create` | `$assets` (selected units with code/name/status), `$maxLoanDays` |
| `resources/views/borrower/bookings/show.blade.php` | `borrower.bookings.show` | `$booking` (with items.asset), `$timeline` (from BookingService::timelineFor) |
| `resources/views/admin/bookings/index.blade.php` | `admin.bookings.index` | `$bookings` (paginator), `$filters`, `$counts`, `$canDecide` (bool per authority), `$limitExceeded` (map booking_id → reason) |
| `resources/views/admin/bookings/show.blade.php` | `admin.bookings.show` | `$booking` (with items.asset, borrower), `$timeline`, `$canDecide` |
| `resources/views/admin/bookings/calendar.blade.php` | `admin.bookings.calendar` | `$month` (Carbon), `$units`, `$ranges` (per-unit booked ranges), `$filters` |

- **Route tambahan:** `borrower.bookings.index|create|store|show|cancel`, `admin.bookings.index|show|approve|reject|calendar`.
- **Catatan deviasi:**
  - **`0012_create_notifications_table` dibuat lebih awal** dari rencana fase (semula Phase 3) karena acceptance Fase 2 mewajibkan notifikasi database + baris notifikasi pada test; tanpa tabel, channel database gagal. Prompt Phase 3 harus melewati migration 0012 ini.
  - `SettingService` memakai default `max_loan_days=7` dan `operator_approval_limit=5000000` sampai tabel `settings` (0014) ada; ditandai `shortcut:`.
  - Pengajuan dengan durasi > `max_loan_days` diblokir di submit (sesuai prompt), sehingga booking durasi panjang hanya bisa dibuat lewat jalur non-borrower; jalur admin_prodi tetap diuji dengan booking yang dibuat langsung.
  - `activity_logs` masih menulis ke log Laravel (tabel menyusul Phase 3).
  - Stub view bersifat minimal (tanpa styling) sesuai §8.4; UI final di prompt frontend Phase 2.
- **Yang belum selesai / catatan:** handover/inspection/maintenance (Phase 3); UI final; menu sidebar admin belum diarahkan ke route bookings (frontend Phase 2).
