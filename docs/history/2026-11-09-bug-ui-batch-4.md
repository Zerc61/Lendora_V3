# Perbaikan Alur Perpanjangan Peminjaman (Batch 4)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix alur perpanjangan (borrower + operator/admin)
Status: selesai

## Tujuan

Menutup B10 dan B12 pada
`docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`. Kedua bug berasal dari
satu akar: alur perpanjangan belum ada endpoint yang lengkap, sehingga tombol
perpanjangan tidak bisa dipakai dan submit form berakhir 500.

## Perubahan

| ID | Masalah | Perbaikan |
|---|---|---|
| B10 | `extension-form.blade.php` memakai `route('bookings.extension.request')` tanpa prefix `borrower.` dan service belum punya method penyimpanan | Route diperbaiki ke `borrower.bookings.extension.request`; `BookingService::requestExtension` dibuat (validasi status aktif, tanggal, duplikasi, konflik jadwal) |
| B12 | Link perpanjangan memakai `<a href>` GET ke route POST di 4 view | Link diarahkan ke route GET form `borrower.bookings.extension.form`; submit dilakukan lewat form POST |

Tambahan agar alur tidak berhenti di pengajuan:

- `BookingService::approveExtension` dan `rejectExtension` beserta
  `assertExtensionDecidable` (hanya operator/organization_admin, status
  pengajuan harus pending, booking harus aktif).
- Route admin `admin.bookings.extensions.approve` dan
  `admin.bookings.extensions.reject`.
- `BookingPolicy::requestExtension` dan `BookingExtensionPolicy::cancel`.
- `AvailabilityService::conflictsFor` menerima `?int $ignoreBookingId` agar
  booking yang sedang diperpanjang tidak dihitung sebagai konflik sendiri.
- Kartu pengajuan pending + tombol batal di detail booking borrower.
- Panel keputusan perpanjangan (setujui/tolak + alasan) di detail booking admin.

## File tersentuh

Dibuat:

- `app/Policies/BookingExtensionPolicy.php`

Diubah:

- `app/Services/AvailabilityService.php`
- `app/Services/BookingService.php`
- `app/Policies/BookingPolicy.php`
- `app/Http/Controllers/Borrower/BookingExtensionController.php`
- `app/Http/Controllers/Borrower/BookingController.php`
- `app/Http/Controllers/Admin/BookingController.php`
- `routes/web.php`
- `resources/views/borrower/bookings/extension-form.blade.php`
- `resources/views/borrower/bookings/show.blade.php`
- `resources/views/borrower/bookings/index.blade.php`
- `resources/views/components/borrower/booking-card.blade.php`
- `resources/views/components/borrower/active-loan-card.blade.php`
- `resources/views/admin/bookings/show.blade.php`
- `lang/id/ui.php`
- `lang/id/messages.php`
- `tests/Feature/Borrower/BookingFlowTest.php`
- `tests/Feature/Admin/ApprovalQueueTest.php`
- `docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`

## Verifikasi

`php artisan test tests/Feature/Borrower/BookingFlowTest.php`:

```
Tests:    10 passed (43 assertions)
```

`php artisan test tests/Feature/Admin/ApprovalQueueTest.php`:

```
Tests:    13 passed (50 assertions)
```

`php artisan test`:

```
Tests:    163 passed (617 assertions)
```

`php artisan view:cache`: `Blade templates cached successfully.`

Pemeriksa key statis (`__('ui.*')` + `__('messages.*')`):

```
0 missing of 749
```

Crawl HTTP (sesi `ani@lendora.test` dan `admin@lendora.test`,
booking 9 punya pengajuan perpanjangan pending id 1):

```
/borrower/bookings/9                   -> 200, 0 raw key
/borrower/bookings/9/extension-form    -> 200, 0 raw key
/admin/bookings/9                      -> 200, 0 raw key
```

Pemeriksaan isi:

- `/borrower/bookings/9`: 1 link ke `borrower.bookings.extension.form`.
- `/borrower/bookings/9/extension-form`: 1 form dengan action
  `borrower.bookings.extension.request`.
- `/admin/bookings/9`: form approve dan reject perpanjangan ada, alasan
  pengajuan `Penambahan sesi wawancara di luar jadwal awal.` tampil.

## Risiko / catatan lanjutan

- Perpanjangan hanya boleh diajukan saat booking berstatus `active`; booking
  `approved` yang belum diambil tidak dapat diperpanjang.
- Hanya operator dan organization_admin yang dapat memutuskan perpanjangan;
  admin_prodi belum terlibat.
- Approve perpanjangan mengubah `requested_end_date` dan `due_at` booking.
  Item dan status aset tidak berubah.
- Sisa backlog: B17/B18/B19 (pembayaran/cicilan) dan B23 (keputusan cakupan
  fitur pembayaran).
