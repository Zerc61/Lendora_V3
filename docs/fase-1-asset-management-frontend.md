# Laporan Fase 1 Asset Management — FRONTEND

- **Yang dibangun:** komponen Blade Phase 1 (`status-badge`, `condition-badge`, `filter-bar`, `pagination`, `asset-card`, `confirm-modal`); UI inventaris admin (tabel aset + filter, form create/edit dua kolom, uploader multi-foto dengan preview/utama/hapus, tree kategori dengan toggle, confirm-modal retire/hapus, toast flash); katalog borrower (search sticky, chip kategori horizontal, grid 2 kolom, pagination) dan detail aset (galeri scroll-snap + dots, badge, spec list, strip ketersediaan 14 hari, CTA sticky); penyesuaian CSS/JS area.
- **File penting:**
  - Create: `resources/views/components/{filter-bar,pagination,asset-card,confirm-modal}.blade.php`, `docs/fase-1-asset-management-frontend.md`.
  - Modify: `resources/views/components/{status-badge,condition-badge}.blade.php`, `resources/views/admin/assets/{index,create,edit}.blade.php`, `resources/views/admin/categories/{index,create,edit}.blade.php`, `resources/views/borrower/catalog/{index,show}.blade.php`, `resources/views/layouts/{admin,borrower}.blade.php`, `public/css/{lendora,admin,borrower}.css`, `public/js/admin.js`, `lang/id/ui.php`, `app/Http/Controllers/Admin/CategoryController.php`, `app/Http/Controllers/Borrower/CatalogController.php`.
  - Delete: tidak ada.
- **Migration/seed dijalankan:** tidak (trek frontend; schema dan seeder tidak disentuh).
- **Test:** `php artisan test` → **59 passed (176 assertions)**, durasi 2.08s. Perubahan frontend tidak menambah/mengubah test.
- **Acceptance criteria fase:**
  - [x] Tabel aset: thumb 48px, kode mono, nama + merek/model, kategori, badge status, badge kondisi, lokasi, aksi edit/retire; filter search + kategori + status + kondisi; empty state; pagination.
  - [x] Form aset dua kolom (identitas | operasional) + kartu foto; error per field Bahasa Indonesia; status read-only dengan hint.
  - [x] Uploader: pilih multi-file → preview → radio "Foto utama" → hapus; file utama dikirim sebagai file pertama sehingga backend menetapkannya `is_primary`.
  - [x] Tree kategori dengan toggle chevron, jumlah aset per kategori, edit/hapus; hapus diblokir server-side tetap tampil sebagai toast lewat `session('error')`.
  - [x] Retire/hapus memakai `confirm-modal` (backdrop, Escape, fokus awal, restore fokus).
  - [x] Katalog: search sticky, chip horizontal, grid 2 kolom, pagination, empty state.
  - [x] Detail: galeri scroll-snap + dots 44px, nama/kode/badge, spec list, strip 14 hari, CTA sticky dengan alasan saat tidak tersedia.
  - [x] Hanya token `--ldr-*` di view; tidak ada class Tailwind; tanpa package baru; target sentuh borrower ≥44px.
  - [x] `php artisan test` lulus penuh; `node --check public/js/admin.js` lolos; semua Blade ter-compile (`php -l` pada hasil compile).
  - [ ] Visual QA 375/1280 px — **tidak dijalankan**: tidak ada browser (Chromium/Firefox/Playwright) di environment. Verifikasi pengganti: feature test render seluruh view kontrak (`assertOk`), cek sintaks JS, grep hex/Tailwind, dan audit ukuran target sentuh.
- **View Contract (perubahan presentasi saja):**

| View file | Route name | Variabel |
|---|---|---|
| `resources/views/admin/categories/index.blade.php` | `admin.categories.index` | tetap `$categories`, `$canDelete`; `$categories` (root + `children`) kini membawa `assets_count` hasil `withCount('assets')` |
| `resources/views/borrower/catalog/show.blade.php` | `borrower.catalog.show` | tetap `$asset`, `$isAvailable`; tambah `$availabilityDays` (14 item: `date` Carbon, `available` bool) |

- **Catatan deviasi:**
  - Toggle foto utama untuk foto lama tidak dibuat: tidak ada field/validasi backend (`UpdateAssetRequest` hanya menerima `photos[]`). Foto lama ditampilkan read-only dengan badge "Foto utama".
  - CTA "Ajukan Peminjaman" memakai notice `:target` (`#booking-notice`) karena route booking baru ada di Phase 2. Saat status bukan `available`, tombol disabled + teks alasan.
  - Label status mengikuti Design.md §3.2 (`Maintenance`, `Nonaktif`), bukan `Perawatan`/`Dihentikan` di prompt; Design.md adalah pemetaan resmi (§11).
  - `condition-badge` juga memetakan `minor_damage` dan `lost` karena kedua nilai ada di `AssetCondition`; prompt hanya menyebut good/fair/damaged.
  - Chip katalog hanya menampilkan kategori root; subkategori tidak dijadikan chip.
  - Toast di-inline di `layouts/admin.blade.php`, bukan komponen terpisah; hanya dipakai area admin pada fase ini.
  - Bug Blade ditemukan dan diperbaiki: `@php(...)` inline tidak didukung versi ini (menghasilkan `<?php(...)` tanpa penutup) pada `layouts/admin.blade.php`, `layouts/borrower.blade.php`, dan `admin/assets/index.blade.php`; diganti bentuk blok `@php ... @endphp`.
  - `status-badge`/`condition-badge` diubah dari sebelumnya agar memakai `__('ui.status.*')` dan `__('ui.condition.*')`.
- **Kontras terverifikasi:** badge memakai pasangan token ink/tint: `success` 6.81:1, `warning` 6.84:1, `danger` 5.91:1, `info` 7.09:1, `violet` 6.48:1, netral 6.93:1. Chip aktif `#9A3412` di `#FFF7ED` 6.88:1; tombol primary putih di `#C2410C` 5.18:1. Semua PASS AA.
- **Yang belum selesai / catatan:** visual QA browser; ketersediaan aktual per tanggal (Phase 2); route booking; modul transaksi/operasional.
