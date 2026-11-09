# Perbaikan Pembayaran Denda dan Cicilan (Batch 5)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix alur pembayaran denda (borrower)
Status: selesai

## Tujuan

Menutup B17, B18, B19, dan B23 pada
`docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`. Pemilik memilih
menyelesaikan alur pembayaran/denda, bukan menyembunyikannya.

## Perubahan

| ID | Masalah | Perbaikan |
|---|---|---|
| B17 | `$canPay` membandingkan enum `PenaltyStatus` dengan string → selalu `false`; tombol bayar juga anchor GET ke route POST | Perbandingan memakai enum; tombol diganti form POST berisi metode + unggah bukti |
| B18 | Status `installment_requested` tidak ada di enum; kolom `installment_months` tidak ada | Case `InstallmentRequested` ditambahkan; migration `000019` menambah kolom `installment_months`; batas bulan dari setting `installment_max` |
| B19 | `PenaltyController::storePayment/installment` stub tanpa menyimpan data | Kedua method dihapus; route memakai `PaymentController` yang menyimpan payment/cicilan |
| B23 | Cakupan fitur pembayaran belum diputuskan | Alur nyata dilengkapi: buat pembayaran, detail pembayaran, pengajuan cicilan, status badge, dan ringkasan denda |

Perubahan pendukung:

- Payment `va_bni` menyimpan `reference_number` dari setting `va_prefix` + NIM.
- Pembayaran ditolak bila denda sudah lunas/tidak dapat dibayar atau sisa 0.
- Metode `installment` dikeluarkan dari form pembayaran biasa (punya form
  sendiri).
- Route denda/pembayaran dipindah ke grup `auth` + `role:borrower` tanpa
  middleware `active`, sehingga borrower berstatus `suspended` tetap dapat
  melihat dan melunasi denda.
- Riwayat pembayaran di halaman denda menaut ke detail pembayaran.
- `PenaltyService::summaryFor` memakai `paidAmount()`/`remainingAmount()`
  (sebelumnya membaca kolom `paid_amount` yang tidak ada) dan menghitung status
  `installment_requested` sebagai denda aktif.

## File tersentuh

Dibuat:

- `database/migrations/2026_11_09_000019_add_installment_months_to_penalties_table.php`
- `tests/Feature/Borrower/PenaltyPaymentTest.php`

Diubah:

- `app/Enums/PenaltyStatus.php`
- `app/Models/Penalty.php`
- `app/Services/PenaltyService.php`
- `app/Http/Controllers/Borrower/PenaltyController.php`
- `app/Http/Controllers/Borrower/PaymentController.php`
- `routes/web.php`
- `resources/views/borrower/penalties/show.blade.php`
- `resources/views/components/status-badge.blade.php`
- `lang/id/ui.php`
- `lang/id/messages.php`
- `docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`

## Verifikasi

`php artisan migrate`:

```
2026_11_09_000019_add_installment_months_to_penalties_table ... DONE
```

`php artisan test tests/Feature/Borrower/PenaltyPaymentTest.php`:

```
Tests:    7 passed (29 assertions)
```

Cakupan test: pembayaran tersimpan + bukti, metode cicilan ditolak di form
pembayaran, cicilan dalam batas, cicilan di atas batas ditolak, akses denda
milik orang lain 403, form tampil di halaman denda, borrower suspended tetap
bisa membuka dan mengajukan cicilan.

`php artisan test`:

```
Tests:    170 passed (646 assertions)
```

`php artisan view:cache`: `Blade templates cached successfully.`

Pemeriksa key statis:

```
0 missing of 758
```

Crawl HTTP (sesi `borrower1@lendora.test`, akun suspended):

```
/borrower/penalties      -> 200, 0 raw key
/borrower/penalties/1    -> 200, 0 raw key
/borrower/payments/1     -> 200, 0 raw key
```

Halaman denda menampilkan form pembayaran dan form cicilan (2 kemunculan route
`payments.store` + `installment-request`).

## Risiko / catatan lanjutan

- Migration baru menambah kolom nullable; tidak ada backfill data.
- Cicilan hanya mencatat pengajuan (`installment_requested`) dan jumlah bulan.
  Verifikasi/approval cicilan oleh operator belum dibangun dan tidak diminta.
- Pembayaran tetap manual: bukti diunggah, status `waiting_verification`, dan
  verifikasi operator di luar cakupan batch ini.
- `PenaltyService::summaryFor` memanggil `paidAmount()` per baris (N+1). Jumlah
  denda per borrower kecil; optimasi belum diperlukan.
