# Kelengkapan CSS dan Perbaikan Halaman Pengajuan (Batch 6)

Tanggal: 2026-11-09
Jenis pekerjaan: bug fix tampilan (CSS) + halaman `/borrower/bookings/create`
Status: selesai

## Tujuan

Pemilik melaporkan desain masih terlihat berantakan setelah Batch 1–5. Audit
menunjukkan penyebabnya bukan markup salah, melainkan banyak kelas `ldr-*` di
Blade yang belum punya aturan CSS sama sekali. Batch ini melengkapi CSS tanpa
mendesain ulang halaman.

## Perubahan

| Area | Masalah | Perbaikan |
|---|---|---|
| Denda & pembayaran | `ldr-penalty-*`, `ldr-payment-table` tanpa CSS | Grid ringkasan, kartu denda, rincian, tabel pembayaran, aksi |
| Form peminjaman | `ldr-booking-create`, `ldr-date-range`, `ldr-extension-form` tanpa CSS | Grid form, rentang tanggal, form ekstensi |
| Detail & daftar booking | Kelas `ldr-booking-detail__*`, `ldr-booking-list-page` tanpa CSS | Header, meta, item, badge |
| Kartu aset | Badge grade, strip ketersediaan, hitungan unit, CTA tanpa CSS | Posisi badge, dot ketersediaan, CTA tersedia/tidak |
| Filter katalog | Panel filter, opsi, chip aktif, pengurutan tanpa CSS | Sidebar desktop, state aktif, chip filter |
| Pass pengembalian | `ldr-return-pass`, `ldr-qr-code` tanpa CSS; QR tidak dirender | CSS pass + QR; `#qr-svg` diberi `data-qr-token` dan `data-size="200"` |
| Pusat bantuan | `ldr-help-*` tanpa CSS | SOP, dokumen, kontak |
| Notifikasi | `ldr-notification-*` tanpa CSS | Kategori, daftar, item belum dibaca |
| Bundel & QC | `ldr-bundle-list`, `ldr-qc-list` tanpa CSS | Daftar komponen dan riwayat QC |
| Dokumen cetak | `ldr-print-*` tanpa CSS | Layout dokumen + aturan `@media print` |
| Atom bersama | Checkbox, input group, badge grade/condition, teks sukses | Ditambahkan ke `lendora.css` |
| Tombol inline | Tiga form memakai `style="display:inline"` | Diganti kelas `.ldr-form-inline` |
| Halaman pengajuan | `$conflict` tidak dikirim → HTTP 500; unit pilihan dari katalog tidak ikut terkirim | Controller mengirim `conflict = null`; view menampilkan unit terpilih, input `assets[]`, pesan validasi |
| Durasi pengajuan | Teks durasi tetap `— hari` | `borrower.js` menghitung durasi dan batas `maxLoanDays` dari `data-*` |
| Tombol draf | Tombol "Simpan Draf" tanpa aksi | Dihapus |

## File tersentuh

Diubah:

- `public/css/lendora.css`
- `public/css/borrower.css`
- `public/css/admin.css`
- `public/js/borrower.js`
- `app/Http/Controllers/Borrower/BookingController.php`
- `resources/views/borrower/bookings/create.blade.php`
- `resources/views/borrower/bookings/return-pass.blade.php`
- `resources/views/borrower/bookings/index.blade.php`
- `resources/views/components/borrower/booking-card.blade.php`
- `lang/id/ui.php`
- `tests/Feature/Borrower/UiRegressionTest.php`
- `docs/rencana/2026-11-09-rencana-perbaikan-bug-audit.md`

## Verifikasi

`php artisan test tests/Feature/Borrower/UiRegressionTest.php`:

```
Tests:    15 passed (71 assertions)
Duration: 4.52s
```

`php artisan test`:

```
Tests:    172 passed (655 assertions)
Duration: 16.22s
```

`php artisan view:cache`:

```
INFO  Blade templates cached successfully.
```

Pemeriksa key terjemahan (`/tmp/check_keys.php`):

```
0 missing of 760
```

Crawl halaman borrower (cookie `ani@lendora.test`):

| URL | Status | Raw key |
|---|---|---|
| `/borrower` | 200 | tidak ada |
| `/borrower/catalog` | 200 | tidak ada |
| `/borrower/catalog/1` | 200 | tidak ada |
| `/borrower/bookings` | 200 | tidak ada |
| `/borrower/bookings/9` | 200 | tidak ada |
| `/borrower/bookings/create?assets[]=1` | 200 | tidak ada |
| `/borrower/bookings/9/extension-form` | 200 | tidak ada |
| `/borrower/bookings/9/return-pass` | 200 | tidak ada |
| `/borrower/bookings/9/document/letter` | 200 | tidak ada |
| `/borrower/help` | 200 | tidak ada |
| `/borrower/notifications` | 200 | tidak ada |

Halaman denda (cookie `borrower1@lendora.test`, akun suspended):

| URL | Status | Raw key |
|---|---|---|
| `/borrower/penalties` | 200 | tidak ada |
| `/borrower/penalties/1` | 200 | tidak ada |
| `/borrower/payments/1` | 200 | tidak ada |
| `/borrower/penalties/1/document/bap` | 200 | tidak ada |

Pemeriksa kelas CSS: 20 nama tersisa, semuanya potongan dinamis
(`ldr-badge--grade-{a,b,c}`, `ldr-badge--condition-{...}`, `ldr-kpi--`,
`ldr-calendar__chip--`) atau komponen yang tidak dipakai
(`ldr-booking-timeline`). Tidak ada kelas statis yang hilang.

## Catatan

- Visual QA hanya berbasis markup dan CSS; tidak ada browser headless di
  lingkungan ini, jadi tangkapan layar tidak tersedia.
- `.ldr-footer__brand` dan `.ldr-footer__column` sengaja tanpa aturan khusus;
  grid footer sudah diatur `.ldr-footer__inner`.
- Semua perubahan belum di-commit sesuai instruksi.
