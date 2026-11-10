# Perbaikan Bug Penalti, Pembayaran, dan 2FA (Batch 2)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix HTTP 500 dan rute ganda
Status: selesai

## Tujuan

Menutup bug batch 2 pada
`docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`: B06, B07, B08, B09,
B11, dan B13.

## Perubahan

| ID | Masalah | Perbaikan |
|---|---|---|
| B06 | `strtolower($penalty->status)` pada enum `PenaltyStatus` → 500 | Pakai `$penalty->status->value` di index dan detail |
| B07 | Status/metode pembayaran enum dibandingkan dengan string dan di-concat ke `__()` | Pakai enum `PaymentStatus`, `$payment->method->value`, `$payment->status->value`; `PaymentService` membandingkan enum |
| B08 | View `borrower/two-factor/edit` tidak ada | Dibuat view minimal: toggle OTP surel + form update |
| B09 | View `borrower/payments/show` tidak ada | Dibuat halaman detail pembayaran: metode, jumlah, status, bukti, unggah ulang |
| B11 | `PaymentController::store` tidak mengisi `borrower_id`, memakai `paid_amount` yang tidak ada | Isi `borrower_id`, hitung sisa dengan `$penalty->remainingAmount()` |
| B13 | Dua route `POST penalties/{penalty}/payments` | Hapus route stub, sisakan `PaymentController` |

Tambahan dalam batch yang sama:

- `PenaltyController::submitProof` menyimpan `proof_path` dan mengubah status ke
  `PaymentStatus::WaitingVerification` (sebelumnya hanya redirect).
- `status-badge` mendukung prefix + varian `ui.penalty.status.`,
  `ui.payment.status.`, dan `ui.inspection_result.`.
- `lang/id/ui.php`: key `penalty.*`, `payment.*`, `profile.security_*`; judul
  tabel memakai `status_label`/`method_label` karena `status`/`method` berupa
  array.

## File tersentuh

- `resources/views/borrower/penalties/index.blade.php`
- `resources/views/borrower/penalties/show.blade.php`
- `resources/views/borrower/penalties/document.blade.php`
- `resources/views/borrower/two-factor/edit.blade.php` (baru)
- `resources/views/borrower/payments/show.blade.php` (baru)
- `resources/views/components/status-badge.blade.php`
- `app/Services/PaymentService.php`
- `app/Http/Controllers/Borrower/PaymentController.php`
- `app/Http/Controllers/Borrower/PenaltyController.php`
- `routes/web.php`
- `lang/id/ui.php`
- `tests/Feature/Borrower/UiRegressionTest.php`

## Verifikasi

`php artisan test tests/Feature/Borrower/UiRegressionTest.php`:

```
Tests:    8 passed (42 assertions)
```

`php artisan test`:

```
Tests:    151 passed (568 assertions)
```

`php artisan view:cache`: `Blade templates cached successfully.`

Crawl HTTP login `ani@lendora.test`:

```
/borrower/penalties                     200
/borrower/penalties/2                   200
/borrower/penalties/2/document/bap      200
/borrower/penalties/2/document/receipt  200
/borrower/two-factor                    200
/borrower/payments/2                    200
```

Pemeriksaan isi: tidak ada raw key `ui.penalty.`, `ui.payment.`, atau
`ui.profile.`; label tampil dalam Bahasa Indonesia (`Menunggu Pembayaran`,
`Terverifikasi`, `Keamanan Akun`).

`php artisan route:list --path=borrower`: hanya satu
`POST borrower/penalties/{penalty}/payments`.

## Risiko / catatan lanjutan

- Tombol bayar tetap tidak muncul: `$canPay` masih membandingkan enum dengan
  string (B17). Perbaikan sengaja ditunda karena belum ada form pembuatan
  pembayaran yang aman; halaman unggah bukti baru ada setelah payment dibuat.
- Route `installment-request` masih memanggil logika cicilan yang belum lengkap
  (B18/B19).
- Link perpanjangan (B10/B12) belum diperbaiki.
