# Perbaikan Kartu Aset Katalog

Tanggal: 2026-11-09
Jenis pekerjaan: redesign komponen `asset-card` (borrower)
Status: selesai

## Tujuan

Kartu aset di `/borrower/catalog` berantakan: status unit tertutup tombol
favorit, badge grade menumpuk di atas foto, strip ketersediaan menempel di
bawah gambar seperti garis putus-putus, dan chip kategori melebar penuh.
Tinggi kartu juga tidak seragam.

## Perubahan

- Badge status dipindah ke kiri bawah foto; tombol favorit tetap kanan atas,
  jadi tidak lagi saling menutup.
- Badge grade dipindah dari atas foto ke baris tag di badan kartu, bersebelahan
  dengan chip kategori.
- Chip kategori tidak lagi melebar penuh.
- Strip ketersediaan 14 hari dipindah ke baris ketersediaan di badan kartu,
  tampil tipis di samping jumlah unit tersedia.
- Kartu memakai flex column; CTA selalu menempel di bawah sehingga tinggi
  kartu dalam satu baris seragam.
- Judul dan deskripsi diberi tinggi minimum dua baris agar isi tidak melompat.
- Badge status dibatasi lebarnya dan memakai font 11px.

## File tersentuh

Diubah:

- `resources/views/components/asset-card.blade.php`
- `public/css/borrower.css`
- `tests/Feature/Borrower/UiRegressionTest.php`

## Verifikasi

`php artisan test tests/Feature/Borrower/UiRegressionTest.php`:

```
Tests:    17 passed (84 assertions)
Duration: 5.13s
```

`php artisan test`:

```
Tests:    174 passed (668 assertions)
Duration: 17.28s
```

`php artisan view:cache`: berhasil.

Pemeriksa key terjemahan: `0 missing of 758`.

Crawl `GET /borrower/catalog` (cookie `ani@lendora.test`): HTTP 200, 12 kartu,
12 baris tag, 12 badge status di dalam media, 0 badge grade di atas foto,
tanpa key `ui.*` mentah.

## Catatan

- Tidak ada browser headless; verifikasi visual berbasis markup dan CSS.
- Perubahan belum di-commit.
