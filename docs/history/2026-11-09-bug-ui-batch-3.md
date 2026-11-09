# Perbaikan Bug UI Borrower Lanjutan (Batch 3)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix UI, terjemahan, dan kontak operator
Status: selesai

## Tujuan

Menutup bug batch 3 pada
`docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`: B14, B15, B16, B20,
B21, B22, dan B24.

## Perubahan

| ID | Masalah | Perbaikan |
|---|---|---|
| B14 | Tombol favorit tidak punya handler; `FavoriteController` menuntut `asset_id`; tombol berada di dalam `<a>` | Tambah `data-favorite-toggle`, controller toggle + balasan JSON, tombol dipindah keluar anchor, CSS tombol ditambahkan |
| B15 | Tombol "Cek Ketersediaan" tidak punya handler | Diganti anchor `href="#availability-calendar"` |
| B16 | Dot progres hanya menandai status saat ini | Indeks status kumulatif: pending 1, approved 2, active 3, returned 4 |
| B20 | Key terjemahan hilang | 36 key ditambahkan ke `ui.php` + `messages.php`; blok `common` duplikat dihapus |
| B21 | Sidebar notifikasi memakai `$operatorInfo` yang tidak dikirim | Controller mengirim `operatorPhone`/`operatorWhatsApp` dari `SettingService` |
| B22 | Raw key tersisa di HTML | Test pemindai key statis ditambahkan; `ui.asset.condition_status.*` dikoreksi ke `ui.condition_status.*` |
| B24 | Verifikasi gambar Beranda | Semua `src` gambar di `/borrower` mengembalikan 200 |

## File tersentuh

- `resources/views/components/asset-card.blade.php`
- `resources/views/borrower/catalog/show.blade.php`
- `resources/views/borrower/bookings/index.blade.php`
- `resources/views/borrower/notifications/index.blade.php`
- `resources/views/components/borrower/asset-bundle-list.blade.php`
- `app/Http/Controllers/Borrower/FavoriteController.php`
- `app/Http/Controllers/Borrower/NotificationController.php`
- `public/css/borrower.css`
- `public/js/borrower.js` (tidak diubah; handler sudah ada)
- `lang/id/ui.php`
- `lang/id/messages.php`
- `tests/Feature/Borrower/UiRegressionTest.php`

## Verifikasi

`php artisan test tests/Feature/Borrower/UiRegressionTest.php`:

```
Tests:    13 passed (62 assertions)
```

`php artisan test`:

```
Tests:    156 passed (588 assertions)
```

`php artisan view:cache`: `Blade templates cached successfully.`

Pemeriksa key statis (`__('ui.*')` + `__('messages.*')`):

```
0 missing of 738
```

Crawl HTTP:

```
borrower: /borrower /borrower/catalog /borrower/catalog/7 /borrower/bookings
          /borrower/notifications /borrower/profile /borrower/help
          /borrower/penalties /borrower/penalties/2 /borrower/two-factor
          /borrower/payments/2                     -> 200, 0 raw key
admin:    /admin /admin/bookings /admin/bookings/calendar /admin/assets
          /admin/categories /admin/handovers /admin/inspections
          /admin/maintenances /admin/users /admin/reports /admin/settings
                                                   -> 200, 0 raw key
```

Pemeriksaan isi:

- `/borrower/catalog`: 12 elemen `data-favorite-toggle`.
- `/borrower/catalog/7`: 1 link `#availability-calendar`.
- `/borrower/bookings`: 8 dot `is-done` (pending 1 + active 3 + returned 4).
- `/borrower/notifications`: kontak dari setting (`Ext. 402`,
  `+62 812-3456-7890`) tanpa `$operatorInfo`.
- `POST /borrower/catalog/{asset}/favorite` JSON → `{ "favorited": true }`,
  panggilan kedua → `{ "favorited": false }`.
- Semua `src` gambar di `/borrower` → 200.

## Risiko / catatan lanjutan

- `FavoriteController::store` kini bersifat toggle untuk request JSON. Route
  `destroy` tetap ada untuk pemakaian non-JS.
- Tombol favorit di halaman detail hanya muncul saat aset belum difavoritkan;
  perilaku ini tidak diubah.
- `ui.borrower.extension*` masih ada meski view memakai `ui.booking.*`; tidak
  dihapus agar tidak memutus referensi lain di masa depan.
- Sisa backlog: B10/B12 (alur perpanjangan), B17/B18/B19 (pembayaran/cicilan),
  B23 (keputusan cakupan fitur pembayaran).
