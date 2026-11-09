# Laporan Fase 1 — Asset Management · BACKEND

- **Yang dibangun:** schema kategori/aset/foto, model + enum cast, `AssetService` (kode otomatis, CRUD, foto, retire), `AvailabilityService` (status + skeleton overlap), `ActivityLogger`, `AssetPolicy`, CRUD admin aset & kategori dengan filter/pagination server-side, katalog borrower, FormRequest Indonesia, seeder 8 kategori + 18 unit demo + placeholder foto, stub view sesuai View Contract, dan test fitur/unit.
- **File penting:**
  - Create: `database/migrations/{0004_create_categories_table,0005_create_assets_table,0006_create_asset_photos_table}.php`, `app/Models/{Category,Asset,AssetPhoto}.php`, `app/Services/{AssetService,AvailabilityService,ActivityLogger}.php`, `app/Policies/AssetPolicy.php`, `app/Http/Requests/{StoreAssetRequest,UpdateAssetRequest,StoreCategoryRequest,UpdateCategoryRequest}.php`, `app/Http/Controllers/Admin/{AssetController,CategoryController}.php`, `app/Http/Controllers/Borrower/CatalogController.php`, `database/seeders/CategoryAndAssetSeeder.php`, `resources/views/admin/assets/{index,create,edit}.blade.php`, `resources/views/admin/categories/{index,create,edit}.blade.php`, `resources/views/borrower/catalog/{index,show}.blade.php`, `tests/Feature/Admin/{AssetCrudTest,AssetPolicyTest}.php`, `tests/Feature/Borrower/CatalogTest.php`, `tests/Unit/{AssetServiceTest,AssetEnumsTest,AvailabilityServiceTest}.php`.
  - Modify: `routes/web.php`, `app/Http/Controllers/Controller.php` (trait `AuthorizesRequests`), `database/seeders/DatabaseSeeder.php`, `lang/id/{messages,validation}.php`, `resources/views/layouts/admin.blade.php` (fallback `$user`), `resources/views/borrower/catalog/index.blade.php` (stub baru).
  - Delete: tidak ada.
- **Migration/seed dijalankan:** ya.
  - `php artisan migrate` → `0004_create_categories_table` DONE, `0005_create_assets_table` DONE, `0006_create_asset_photos_table` DONE.
  - `php artisan db:seed --class=CategoryAndAssetSeeder` → 8 kategori, 18 aset, 18 foto.
  - `php artisan storage:link` → `public/storage` terhubung.
- **Test:** `php artisan test` → **59 passed (176 assertions)**, durasi 1.86s.
- **Acceptance criteria:**
  - [x] CRUD aset & kategori via endpoint; kode unik otomatis `LDR-{CAT}-{0001}`.
  - [x] Foto tersimpan di `storage/app/public/assets/{code}/` dan tercatat di `asset_photos`.
  - [x] Filter (search, kategori, status, kondisi) dan pagination 15/halaman server-side; katalog 12/halaman.
  - [x] Soft delete; restore diuji lewat `withTrashed()->restore()`.
  - [x] Katalog borrower hanya menampilkan `available`/`reserved`/`borrowed`; status lain 404.
  - [x] Guard hapus kategori (punya anak/aset) dengan pesan Indonesia.
  - [x] `php artisan test` lulus penuh.
- **View Contract:**

| View file (exact path) | Route name | Variables passed |
|---|---|---|
| `resources/views/admin/assets/index.blade.php` | `admin.assets.index` | `$assets` (paginator), `$categories`, `$filters` (search, category_id, status, condition_status) |
| `resources/views/admin/assets/create.blade.php` | `admin.assets.create` | `$categories` (flat with depth for select) |
| `resources/views/admin/assets/edit.blade.php` | `admin.assets.edit` | `$asset` (with `$asset->photos`), `$categories` |
| `resources/views/admin/categories/index.blade.php` | `admin.categories.index` | `$categories` (tree: each with `children`), `$canDelete` map |
| `resources/views/admin/categories/create.blade.php` | `admin.categories.create` | `$categories` (potential parents) |
| `resources/views/admin/categories/edit.blade.php` | `admin.categories.edit` | `$category`, `$categories` |
| `resources/views/borrower/catalog/index.blade.php` | `borrower.catalog.index` | `$assets` (paginator), `$categories`, `$filters` |
| `resources/views/borrower/catalog/show.blade.php` | `borrower.catalog.show` | `$asset` (with photos, category), `$isAvailable` (bool) |

- **Route tambahan:** `admin.assets.index|create|store|edit|update|destroy`, `admin.assets.retire` (POST), `admin.categories.index|create|store|edit|update|destroy`, `borrower.catalog.index|show`.
- **Catatan deviasi:**
  - `activity_logs` baru dibuat di Phase 3. `ActivityLogger` menulis ke log Laravel dengan action `asset.status_changed`; upgrade ke tabel dilakukan saat migration 0013 ada.
  - `AvailabilityService` memakai guard `Schema::hasTable()`; nama kolom rentang booking (`start_date`/`end_date`) diasumsikan dan ditandai `shortcut:` untuk Phase 2.
  - Form aset menerima `code` opsional untuk menguji validasi duplikat; bila kosong, kode digenerate otomatis.
  - Kode kategori turunan memakai root (mis. Full-frame → `CAM`); kategori tak dikenal memakai 3 huruf pertama nama.
  - `AssetPolicy::update` hanya org_admin; ability `updateCondition` disediakan untuk technician sesuai matrix, tetapi route admin aset belum membuka akses technician (sesuai middleware fase).
  - Menambahkan `AvailabilityServiceTest` dan pengujian guard kategori di luar daftar test minimum prompt.
- **Yang belum selesai:** UI final (trek frontend `05`), booking/availability aktual (Phase 2), `activity_logs` DB (Phase 3).
