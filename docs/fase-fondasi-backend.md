# Laporan Fase Fondasi — BACKEND

- **Yang dibangun:** Laravel 12 + Breeze (Blade), schema `roles`/`organizations`/`users`, enum status, middleware role & status, redirector dashboard, area stub borrower/admin, seeder role/org/akun demo, halaman 403 Bahasa Indonesia, test auth.
- **File penting:**
  - Create: `app/Enums/{BookingStatus,AssetStatus,BookingItemStatus,MaintenanceStatus,InspectionResult,AssetCondition}.php`, `app/Models/{Role,Organization,User}.php`, `app/Http/Middleware/{EnsureRole,EnsureActiveStatus}.php`, `app/Http/Controllers/Borrower/HomeController.php`, `app/Http/Controllers/Admin/DashboardController.php`, `database/migrations/0001_create_roles_table.php`, `0002_create_organizations_table.php`, `0003_create_users_table.php`, `database/seeders/{RoleSeeder,OrganizationSeeder,AdminSeeder}.php`, `lang/id/{validation,messages}.php`, `public/css/lendora.css`, `resources/views/{layouts/borrower,layouts/admin,borrower/home,admin/dashboard,errors/403}.blade.php`, `tests/Feature/Auth/{RoleRedirectTest,CrossRoleAccessTest,SuspendedUserTest,GuestAccessTest}.php`, `docs/fase-fondasi-backend.md`.
  - Modify: `bootstrap/app.php`, `routes/web.php`, `app/Models/User.php`, `app/Http/Controllers/Auth/RegisteredUserController.php`, `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php`, `config/app.php`, `.env.example`, `.env` (lokal).
  - Delete: `database/migrations/0001_01_01_000000_create_users_table.php` (digantikan `0003`), scaffolding profil Breeze yang tidak ada di route map (`ProfileController`, `views/profile`, `ProfileTest`, `views/dashboard`, layout `app`/`navigation`).
- **Migration/seed dijalankan:** ya. `php artisan migrate:fresh --seed` (lokal, disetujui): 5 migration + 3 seeder sukses.
- **Test:** `php artisan test` → **35 passed (81 assertions)**, durasi 2.23s.
- **Acceptance criteria:**
  - [x] `php artisan migrate` bersih pada database kosong.
  - [x] Login/register/reset berfungsi; tiap role mendarat di area benar.
  - [x] Akses lintas role → 403 dengan halaman Indonesia.
  - [x] Kedua stub view render.
  - [x] `php artisan test` lulus penuh.
  - [x] Commit.
- **Catatan deviasi:**
  - `0003_create_users_table.php` juga membuat `password_reset_tokens` dan `sessions` karena auth/reset dan `SESSION_DRIVER=database` membutuhkannya. Ini pengganti langsung migration users bawaan Laravel.
  - Registrasi publik memberi role `borrower` (keputusan user).
  - Technician tidak boleh membuka `/admin` (sesuai acceptance criteria fase), meski nanti punya route operasional sendiri.
  - `/profile` bawaan Breeze dihapus karena tidak ada di route map; profil borrower dibuat di fase frontend/berikutnya.
  - Migration default `cache` dan `jobs` tetap dipakai framework.
- **View Contract:**

| View file (exact path) | Route name | Variables passed |
|---|---|---|
| `resources/views/layouts/borrower.blade.php` | — | layout for all borrower pages |
| `resources/views/layouts/admin.blade.php` | — | layout for all admin pages |
| `resources/views/borrower/home.blade.php` | `borrower.home` | `$user` (authenticated) |
| `resources/views/admin/dashboard.blade.php` | `admin.dashboard` | `$user` (with `$user->role`) |
| `resources/views/errors/403.blade.php` | — | Laravel default error data |

- **Akun demo (password `password`):** `admin@lendora.test`, `operator@lendora.test`, `technician@lendora.test`, `adminprodi@lendora.test`, `borrower1@lendora.test`, `borrower2@lendora.test`.
- **Yang belum selesai:** UI final, seluruh fitur Phase 1–3, `docs/README` kredensial demo.
