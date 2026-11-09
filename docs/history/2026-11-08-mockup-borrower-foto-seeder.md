# Mockup Borrower, Foto Produk, dan Seeder Demo

Tanggal: 2026-11-08
Jenis pekerjaan: frontend, data demo
Status: selesai

## Tujuan

Menyelaraskan area borrower dengan mockup Stitch dan menyiapkan data demo yang
layak untuk pengujian:

- Beranda, katalog, detail aset, dan profil mengikuti struktur mockup M3, M4,
  M5, dan M10/B8.
- Setiap produk memiliki foto nyata, bukan placeholder.
- Footer portal borrower dihapus.
- Profil memiliki ringkasan pinjaman, identitas akademik, dan tombol keluar.
- Seeder demo berisi pengguna, produk baru, deskripsi panjang, spesifikasi, dan
  riwayat peminjaman.

Ruang lingkup: view dan CSS borrower, controller borrower, kolom akademik
pengguna, kolom spesifikasi aset, serta seeder demo. Route, validasi, otorisasi,
dan alur bisnis tidak berubah.

## Perubahan

### Layout dan navigasi

- `layouts/borrower.blade.php`
  - Footer portal dihapus dari markup desktop.
  - Meta pengguna di topbar menampilkan NIM dan program studi jika tersedia.
- `public/css/borrower.css`
  - Semua aturan footer portal dihapus.

### Beranda

- `borrower/home.blade.php`: sapaan, tanggal, jumlah unit siap pinjam, empat
  kartu ringkasan status nyata, kartu peminjaman aktif dengan foto unit dan jatuh
  tempo, rekomendasi aset, dan pengumuman lab.
- `Borrower/HomeController.php`: menambahkan hitungan status peminjaman dan
  jumlah aset berstatus tersedia.

### Katalog dan detail aset

- `borrower/catalog/index.blade.php`: ringkasan ketersediaan (siap dipinjam dan
  sedang dipakai) di atas daftar aset.
- `components/asset-card.blade.php`: menampilkan merek/model dan potongan
  deskripsi.
- `borrower/catalog/show.blade.php`: bagian "Tentang peralatan ini" dan daftar
  spesifikasi dinamis dari kolom `specifications`.
- `Borrower/CatalogController.php`: menghitung jumlah aset per status untuk
  ringkasan katalog.

### Profil

- `borrower/profile/edit.blade.php`: kartu identitas dengan avatar, badge
  verifikasi, ID lab dan tier, ringkasan pinjaman, teaser peminjaman aktif, form
  kontak, preferensi notifikasi, form kata sandi, dan tombol "Keluar".
- `Borrower/ProfileController.php`: menyediakan hitungan status, total transaksi,
  dan peminjaman aktif.

### Data dan skema

- Migration `2026_11_08_000001_add_academic_profile_fields_to_users_table.php`:
  kolom `nim`, `study_program`, `semester`, `lab_tier`, `lab_member_id`, dan
  `academic_verified_at`. Migration memakai guard `Schema::hasColumn` karena
  database lokal sudah memiliki sebagian kolom tersebut.
- Migration `2026_11_08_000002_add_specifications_to_assets_table.php`: kolom
  JSON `specifications` pada tabel aset.
- `Models/User.php` dan `Models/Asset.php`: field baru masuk `fillable` dan
  `casts`.
- `CategoryAndAssetSeeder.php`: 18 aset dengan deskripsi panjang, spesifikasi,
  dan foto nyata. Foto disalin dari `public/images/products/` ke disk `public`.
- `BorrowerShowcaseSeeder.php`: pengguna `ani@lendora.test`, sembilan produk
  baru (Sony FX3, Blackmagic 6K Pro, Sony A7 IV, Sony 85mm GM, Amaran 200D,
  Manfrotto Befree, Rode Wireless GO II, DJI Mic, Zoom H4n Pro), kategori
  Kamera Cinema dan Audio & Mic, serta tiga peminjaman.
- `DatabaseSeeder.php`: mendaftarkan `BorrowerShowcaseSeeder`.
- `public/images/products/`: 20 foto produk dari Wikimedia Commons, diperkecil
  dengan ImageMagick.

### Label

- `lang/id/ui.php`: label baru untuk beranda, katalog, dan profil.

## File Tersentuh

Diubah:

- `app/Http/Controllers/Borrower/HomeController.php`
- `app/Http/Controllers/Borrower/CatalogController.php`
- `app/Http/Controllers/Borrower/ProfileController.php`
- `app/Models/Asset.php`
- `app/Models/User.php`
- `database/seeders/CategoryAndAssetSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/borrower/home.blade.php`
- `resources/views/borrower/catalog/index.blade.php`
- `resources/views/borrower/catalog/show.blade.php`
- `resources/views/borrower/profile/edit.blade.php`
- `resources/views/components/asset-card.blade.php`
- `resources/views/layouts/borrower.blade.php`
- `public/css/borrower.css`
- `lang/id/ui.php`

Dibuat:

- `database/migrations/2026_11_08_000001_add_academic_profile_fields_to_users_table.php`
- `database/migrations/2026_11_08_000002_add_specifications_to_assets_table.php`
- `database/seeders/BorrowerShowcaseSeeder.php`
- `public/images/products/*.jpg`
- `tests/Feature/Borrower/ShowcaseSeedTest.php`
- `docs/history/2026-11-08-mockup-borrower-foto-seeder.md`

## Verifikasi

Migrasi dan seeder dijalankan pada database MariaDB lokal:

```
INFO  Running migrations.
2026_11_08_000001_add_academic_profile_fields_to_users_table .. DONE
2026_11_08_000002_add_specifications_to_assets_table .......... DONE
```

Pemeriksaan data demo:

```
LDR-AUD-0001 | 352 chars | specs=7 | photo=assets/LDR-AUD-0001/photo.jpg
LDR-CAM-0001 | 336 chars | specs=6 | photo=assets/LDR-CAM-0001/photo.jpg
LDR-CAM-0007 | 450 chars | specs=7 | photo=assets/LDR-CAM-0007/photo.jpg
USER Ani Rahmawati | 2207412089 | Desain Komunikasi Visual | 5 | A
BOOKINGS 3
```

Blade cache dan seluruh tes:

```
INFO  Blade templates cached successfully.

Tests:    132 passed (474 assertions)
Duration: 9.39s
```

Tes baru `ShowcaseSeedTest` memastikan profil akademik, deskripsi panjang,
spesifikasi, foto tersimpan, dan halaman beranda, katalog, detail, serta profil
merender data demo.

## Risiko / Catatan Lanjutan

- Foto berasal dari Wikimedia Commons dan dipakai untuk demo internal. Periksa
  lisensi per file sebelum publikasi.
- Tombol simpan pada form identitas menyimpan seluruh form (termasuk preferensi
  notifikasi), sama seperti perilaku sebelumnya.
- Verifikasi visual dengan browser belum dilakukan karena Chromium/Firefox tidak
  tersedia di mesin ini. Verifikasi saat ini memakai tes HTTP dan cache Blade.
- Migration akademik memakai guard kolom; saat deploy ke database baru, kolom
  tetap dibuat lengkap.
