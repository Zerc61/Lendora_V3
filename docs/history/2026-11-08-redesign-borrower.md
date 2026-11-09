# Redesign Area Borrower — Mobile dan Desktop

Tanggal: 2026-11-08
Jenis pekerjaan: frontend
Status: selesai

## Tujuan

Mengerjakan ulang area borrower agar responsif dalam satu basis kode:

- Mobile: appbar + bottom navigation 5 tab.
- Desktop: portal dengan topbar, navigasi horizontal, pencarian, CTA, dan footer.
- Semua halaman borrower memakai token orange + putih, tanpa gradient.

Ruang lingkup: layout borrower, beranda, katalog, detail aset, daftar
peminjaman, form peminjaman, detail peminjaman, notifikasi, dan profil.

Di luar lingkup: login/register/onboarding, fitur denda, QR/PDF, perpanjangan,
pusat bantuan, dan backend baru.

## Perubahan

### Layout

- `layouts/borrower.blade.php`
  - Topbar desktop: brand, chip "Portal Mahasiswa", 5 menu, pencarian katalog,
    CTA "Ajukan Peminjaman", bell notifikasi, dan chip pengguna.
  - Appbar mobile dan bottom navigation tetap dipertahankan.
  - Footer desktop dengan navigasi dan tahun berjalan.
  - Navigasi disusun dari satu array `$navItems`; status aktif tetap memakai
    section `nav` dari tiap view.
  - Tidak ada route atau otorisasi yang diubah.

### Halaman

- `borrower/home.blade.php`: hero sapaan, kartu peminjaman aktif dengan foto
  unit dan jatuh tempo, grid rekomendasi, serta kartu pengumuman.
- `borrower/catalog/index.blade.php`: judul halaman dan subjudul; pencarian dan
  chip kategori tetap memakai filter yang ada.
- `borrower/catalog/show.blade.php`: layout dua kolom desktop (galeri sticky di
  kiri, informasi dan CTA di kanan); mobile tetap satu kolom dengan CTA bawah.
- `borrower/bookings/create.blade.php`: ringkasan unit dan form menjadi dua
  kolom pada desktop.
- `borrower/bookings/show.blade.php`: header, jadwal, timeline, dan daftar unit
  menjadi grid dua kolom pada desktop.
- `borrower/profile/edit.blade.php`: kartu identitas sticky dan form di sebelah
  kanan pada desktop.
- `borrower/bookings/index.blade.php` dan
  `borrower/notifications/index.blade.php`: tanpa perubahan markup; tampilan
  desktop diatur lewat CSS (daftar dua kolom).

### Pendukung

- `public/css/borrower.css`: breakpoint `1024px` dan `1280px`, komponen topbar,
  footer, kartu peminjaman aktif, pengumuman, grid katalog, dan layout detail.
- `components/icon.blade.php`: tambah ikon `plus`.
- `lang/id/ui.php`: tambah label `portal_label`, `portal_subtitle`,
  `active_loan`, `due`, `overdue`, `days_left`, `recommendations`,
  `announcement`, `footer_navigation`, dan `catalog.subtitle`.

## File Tersentuh

Diubah:

- `resources/views/layouts/borrower.blade.php`
- `resources/views/borrower/home.blade.php`
- `resources/views/borrower/catalog/index.blade.php`
- `resources/views/borrower/catalog/show.blade.php`
- `resources/views/borrower/bookings/create.blade.php`
- `resources/views/borrower/bookings/show.blade.php`
- `resources/views/borrower/profile/edit.blade.php`
- `resources/views/components/icon.blade.php`
- `public/css/borrower.css`
- `lang/id/ui.php`

Dibuat:

- `docs/history/2026-11-08-redesign-borrower.md`

Tidak ada controller, route, model, migration, policy, atau tes yang diubah.

## Verifikasi

```
php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test --compact
Tests:    129 passed (454 assertions)
Duration: 8.83s
```

Catatan: verifikasi visual di browser belum dilakukan karena tidak ada browser
headless terpasang di mesin ini. Pemeriksaan tampilan dilakukan lewat markup,
CSS, dan screenshot mockup.

## Risiko / Catatan Lanjutan

- Login, register, forgot/reset password, dan onboarding belum diselaraskan
  dengan sistem visual baru; masih memakai `ldr-login.css` bergradient.
- Layout desktop memakai topbar, bukan sidebar, mengikuti mockup B1/B5.
- Background dan kartu memakai token yang sudah ada. Tidak ada dependency baru.
- Perlu UAT visual pada lebar 360, 768, 1024, dan 1280 px sebelum dianggap
  final.
- Fitur mockup B6/B9 dan M11 tetap ditunda karena butuh backend atau keputusan
  produk.
