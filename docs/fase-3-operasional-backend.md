## Laporan Fase 3 — Operasional & Pelaporan — BACKEND

- **Yang dibangun:** siklus fisik penuh (pickup → aktif → kembali → inspeksi →
  maintenance → available) lewat service; manajemen pengguna & peran,
  laporan + ekspor CSV, pengaturan organisasi, log aktivitas DB, halaman
  notifikasi & profil peminjam, dashboard per peran (operator, org admin,
  admin prodi, teknisi), command pengingat H-1, seeder pengaturan dan demo
  transaksi, serta stub view sesuai View Contract.
- **File penting (create):**
  - `database/migrations/0009_create_handovers_table.php`
  - `database/migrations/0010_create_inspections_table.php`
  - `database/migrations/0011_create_maintenances_table.php`
  - `database/migrations/0013_create_activity_logs_table.php`
  - `database/migrations/0014_create_settings_table.php`
  - `app/Enums/HandoverType.php`
  - `app/Models/Handover.php`, `app/Models/Inspection.php`,
    `app/Models/Maintenance.php`, `app/Models/ActivityLog.php`,
    `app/Models/Setting.php`
  - `app/Services/HandoverService.php`, `app/Services/InspectionService.php`,
    `app/Services/MaintenanceService.php`, `app/Services/SettingService.php`
  - `app/Exceptions/InvalidMaintenanceTransitionException.php`
  - `app/Notifications/LoanPickupNotification.php`,
    `app/Notifications/LoanDueReminderNotification.php`,
    `app/Notifications/AssetDamageNotification.php`
  - `app/Http/Requests/StoreHandoverRequest.php`,
    `StoreInspectionRequest.php`, `StoreMaintenanceRequest.php`,
    `UpdateMaintenanceRequest.php`, `FinishMaintenanceRequest.php`,
    `StoreUserRequest.php`, `UpdateUserRequest.php`,
    `UpdateBorrowerProfileRequest.php`, `UpdateSettingsRequest.php`
  - `app/Http/Controllers/Admin/HandoverController.php`,
    `InspectionController.php`, `MaintenanceController.php`,
    `UserController.php`, `ReportController.php`, `SettingController.php`
  - `app/Http/Controllers/Borrower/NotificationController.php`,
    `ProfileController.php`
  - `app/Console/Commands/RemindDueLoans.php`
  - `database/seeders/SettingSeeder.php`, `DemoTransactionSeeder.php`
  - `tests/Feature/Admin/HandoverTest.php`, `InspectionTest.php`,
    `MaintenanceTest.php`, `UserManagementTest.php`, `ReportTest.php`,
    `AuthorizationRegressionTest.php`
  - `tests/Unit/RemindDueCommandTest.php`
  - 16 stub view sesuai View Contract di bawah.
- **File penting (modify):**
  - `app/Services/ActivityLogger.php` (tulis ke `activity_logs`)
  - `app/Services/BookingService.php` (timeline pickup/return)
  - `app/Http/Controllers/Admin/DashboardController.php` (KPI per peran)
  - `app/Http/Controllers/Borrower/HomeController.php` (data live)
  - `app/Providers/AppServiceProvider.php` (log `auth.login`)
  - `routes/web.php`, `routes/console.php` (jadwal `loans:remind-due` 08:00)
  - `database/seeders/DatabaseSeeder.php`
  - `lang/id/messages.php`
  - `tests/Feature/Auth/CrossRoleAccessTest.php` (teknisi kini boleh dashboard)
- **Migration/seed dijalankan:** Tidak di lokal. `php artisan migrate` gagal:
  `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)`.
  Skema diverifikasi lewat test SQLite in-memory (semua migrasi 0001–0014 jalan).
- **Test:** `php artisan test --compact` → **108 passed (349 assertions)**.
- **Acceptance criteria fase:**
  - [x] Siklus pinjam–kembali–inspeksi–maintenance terekam lengkap.
  - [x] Inspeksi `needs_repair` membuat maintenance + aset `maintenance`;
        selesai + kondisi baik → aset `available`.
  - [x] Laporan cocok dengan data demo; ekspor CSV valid.
  - [x] Activity log terekam (termasuk `auth.login` dan `user.role_changed`).
  - [x] Pengingat H-1 berjalan dan menghormati toggle.
  - [x] `php artisan test` lulus.
- **View Contract:** (verbatim)

| View file (exact path) | Route name | Variables passed |
|---|---|---|
| `resources/views/admin/bookings/handover.blade.php` | `admin.bookings.handover` (GET form) | `$booking`, `$items` (each with asset + condition fields), `$type` (pickup/return) |
| `resources/views/admin/inspections/index.blade.php` | `admin.inspections.index` | `$inspections` (paginator), `$filters` |
| `resources/views/admin/inspections/create.blade.php` | `admin.inspections.create` | `$assets` (picker data), `$bookingItem` (nullable preselect) |
| `resources/views/admin/maintenances/index.blade.php` | `admin.maintenances.index` | `$maintenances` (paginator; scoped per role), `$role` |
| `resources/views/admin/maintenances/create.blade.php` | `admin.maintenances.create` | `$assets`, `$technicians` |
| `resources/views/admin/maintenances/edit.blade.php` | `admin.maintenances.edit` | `$maintenance` (with asset, assigned technician), `$technicians` |
| `resources/views/admin/maintenances/finish.blade.php` | `admin.maintenances.finish` (GET form) | `$maintenance` |
| `resources/views/admin/users/index.blade.php` | `admin.users.index` | `$users` (paginator), `$filters`, `$roles` |
| `resources/views/admin/users/create.blade.php` | `admin.users.create` | `$roles`, `$organization` |
| `resources/views/admin/users/edit.blade.php` | `admin.users.edit` | `$user`, `$roles` |
| `resources/views/admin/reports/index.blade.php` | `admin.reports.index` | `$kpis`, `$utilization` (per-category rows), `$topBorrowers`, `$damages`, `$filters`, `$categories` |
| `resources/views/admin/settings/edit.blade.php` | `admin.settings.edit` | `$organization`, `$settings` (typed map) |
| `resources/views/admin/dashboard.blade.php` | `admin.dashboard` | `$role`, `$kpis`, `$recentActivity` (org_admin), `$specialQueue` (admin_prodi), `$tasks` (technician) |
| `resources/views/borrower/home.blade.php` | `borrower.home` | `$user`, `$activeBooking` (nullable, with due info), `$recommendations`, `$announcement`, `$unreadNotifications` |
| `resources/views/borrower/notifications/index.blade.php` | `borrower.notifications.index` | `$groups` (today/earlier), `$unreadCount` |
| `resources/views/borrower/profile/edit.blade.php` | `borrower.profile.edit` | `$user` |

  Catatan kontrak: `admin.dashboard` juga menerima `$user` (kebutuhan layout);
  `borrower.home` juga menerima `$dueInfo` (nullable: `due_at`, `days_left`,
  `is_overdue`) untuk tampilan hitung mundur.
- **Yang belum selesai / catatan:**
  - Migrasi + seed pada MariaDB lokal belum dijalankan (kredensial DB ditolak).
  - Repositori pernah ter-reset; seluruh pekerjaan Fase 1–3 berada pada satu
    commit pemulihan sehingga hash commit tidak memisahkan tiap fase.
  - UI final menyusul di prompt frontend Fase 3 (`09`).
  - Verifikasi visual browser belum dijalankan (tidak ada browser terpasang).
- **Commit:** `feat(phase3-backend): handovers, inspections, maintenance, users, reports, settings, activity log, reminder command`
