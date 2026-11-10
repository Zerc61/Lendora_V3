# Redesign Panel Samping Notifikasi

Tanggal: 2026-11-09
Jenis pekerjaan: redesign UI (borrower)
Status: selesai

## Tujuan

Panel kanan halaman `/borrower/notifications` terasa janggal: empat kartu
terpisah dengan isi statis, judul kartu tidak seragam, dan tautan dokumen
tampil sebagai daftar polos. Panel dirapikan menjadi tiga kartu ringkas dengan
data nyata.

## Perubahan

- Panel samping dibagi menjadi tiga kartu: **Ringkasan**, **Dokumen &
  Regulasi**, dan **Kontak Operator**.
- Kartu Ringkasan menampilkan jumlah peminjaman aktif dan skor kepatuhan nyata
  (sebelumnya teks statis "Tidak ada peminjaman aktif" / "Akun Baik").
- Tautan dokumen dan kontak memakai baris ikon + panah; nomor telepon memakai
  `tel:` dan WhatsApp memakai `https://wa.me/`.
- Judul grup notifikasi (`Hari ini`, `Sebelumnya`) memakai gaya label kecil.
- Chip kategori notifikasi memakai label terjemahan
  (`ui.notification.type.*`), bukan slug mentah. Kategori tak dikenal
  diubah menjadi teks biasa.
- Query `category` yang tidak valid diabaikan.
- Empty state tetap tunggal; tidak lagi muncul per grup.

## File tersentuh

Diubah:

- `app/Http/Controllers/Borrower/NotificationController.php`
- `resources/views/borrower/notifications/index.blade.php`
- `public/css/borrower.css`
- `lang/id/ui.php`
- `tests/Feature/Borrower/UiRegressionTest.php`

## Verifikasi

`php artisan test tests/Feature/Borrower/UiRegressionTest.php`:

```
Tests:    16 passed (79 assertions)
Duration: 4.68s
```

`php artisan test`:

```
Tests:    173 passed (663 assertions)
Duration: 17.54s
```

`php artisan view:cache`: berhasil (`exit=0`).

Pemeriksa key terjemahan: `0 missing of 758`.

Crawl `GET /borrower/notifications` (cookie `ani@lendora.test`):

- HTTP 200.
- Tidak ada key `ui.*` mentah.
- Panel memuat `Ringkasan`, `Peminjaman aktif`, `Skor Kepatuhan`,
  `Dokumen & Regulasi`, `Kontak Operator`.

## Catatan

- Tidak ada browser headless; verifikasi visual berbasis markup dan CSS.
- Perubahan belum di-commit.
