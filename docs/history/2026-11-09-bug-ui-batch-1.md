# Perbaikan 5 Bug UI Borrower (Batch 1)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix UI/render
Status: selesai

## Tujuan

Menutup lima bug hasil audit pada rencana
`docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md` (B01–B05).

## Perubahan

| ID | Masalah | Perbaikan |
|---|---|---|
| B01 | Detail katalog 500: `strtolower()` pada enum `AssetCondition` | Pakai `$component->condition_status->value` |
| B02 | Kartu aset selalu "0 unit tersedia" dan CTA unavailable | Bandingkan `$asset->status` dengan enum `AssetStatus::Available`, bukan `->value` |
| B03 | Tombol Batalkan/Perpanjang dan alert penolakan tidak muncul | Bandingkan `$booking->status` dengan enum `BookingStatus` |
| B04 | Notifikasi menampilkan raw key, label `today`/`earlier`, empty-state ganda | Tambah 8 key terjemahan, terjemahkan label grup, render empty-state sekali |
| B05 | Filter status peminjaman tidak ter-style | Samakan kelas markup ke `ldr-status-tabs__tab` / `ldr-status-tabs__count` |

## File tersentuh

- `resources/views/components/borrower/asset-bundle-list.blade.php`
- `resources/views/components/asset-card.blade.php`
- `resources/views/borrower/bookings/index.blade.php`
- `resources/views/borrower/bookings/show.blade.php`
- `resources/views/components/borrower/booking-card.blade.php`
- `resources/views/borrower/notifications/index.blade.php`
- `lang/id/ui.php`
- `tests/Feature/Borrower/UiRegressionTest.php` (baru, 5 test)

## Verifikasi

`php artisan test tests/Feature/Borrower/UiRegressionTest.php`:

```
Tests:    5 passed (18 assertions)
```

`php artisan test`:

```
Tests:    148 passed (544 assertions)
```

`php artisan view:cache`: `Blade templates cached successfully.`

Crawl HTTP dengan login `borrower2@lendora.test`:

```
/borrower                 200
/borrower/catalog/7       200
/home  cta--available     5
/home  "0 unit tersedia"  0
/borrower/notifications  raw key "ui.notification." 0
/borrower/bookings       tombol "Batalkan Pengajuan" 1
/borrower/bookings       kelas ldr-status-tabs__tab 7
```

## Risiko / catatan lanjutan

- B03 hanya memperbaiki visibilitas. Link perpanjangan masih `<a href>` GET ke
  route POST (405) sampai B12 dikerjakan.
- Halaman penalti, 2FA, pembayaran, dan detail aset berkomponen lain sudah
  beres untuk komponen; modul penalti/2FA/pembayaran masih 500 (B06–B11).
- Data kontak operator pada sidebar notifikasi masih hardcoded (B21).
