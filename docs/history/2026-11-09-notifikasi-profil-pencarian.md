# Notifikasi, Profil, Pencarian Staf dan Akses Aset Teknisi

Tanggal: 2026-11-09
Jenis pekerjaan: frontend + perubahan otorisasi terbatas
Status: selesai

## Tujuan

Menyelesaikan sisa menu bersama area admin:

- Notifikasi staf tampil sebagai kartu dengan penanda belum dibaca.
- Profil staf menampilkan informasi akun dan organisasi.
- Hasil pencarian mengarah ke halaman yang boleh dibuka tiap role.
- Technician dapat melihat aset tanpa tombol kelola.

## Perubahan

### Notifikasi staf

`admin/notifications/index.blade.php`:

- Tabel diganti daftar kartu.
- Kartu belum dibaca memakai garis kiri orange dan ikon lonceng.
- Aksi "Tandai dibaca" per kartu dan "Tandai semua" tetap.
- Pengelompokan hari ini / sebelumnya tetap.

### Profil staf

`admin/profile/edit.blade.php` menambah kartu "Informasi akun": organisasi,
telepon, dan status akun. Form nama, telepon, preferensi notifikasi, dan ubah
sandi tidak berubah.

### Pencarian

`admin/search/index.blade.php`:

- Hasil aset membuka daftar aset dengan filter `search=<kode>`, bukan form edit.
- Hasil kategori membuka daftar kategori, bukan form edit.
- Hasil pengguna dan booking tetap.
- Alasan: Admin Prodi dan Technician kini hanya punya akses baca.

`Admin/SearchController` menambahkan Technician ke pencarian aset. Pencarian
booking dan pengguna tetap dibatasi role sebelumnya.

### Akses aset teknisi

Sesuai keputusan pengguna dan matriks izin:

- `routes/web.php`: `GET admin/assets` dibuka untuk
  `operator,organization_admin,admin_prodi,technician`.
- Route tulis aset tetap `operator`/`organization_admin`.
- `layouts/admin.blade.php`: menu Aset ditampilkan untuk Technician.
- `admin/assets/index.blade.php` sudah memakai `$canManage`, sehingga tombol
  tambah/edit/nonaktifkan tidak muncul.

Kategori tetap tidak dibuka untuk Technician.

### Gaya

`public/css/admin.css` menambah `.ldr-notice-list`, `.ldr-notice`,
`.ldr-notice--unread`, `__icon`, `__body`, dan `__time`.

## File Tersentuh

Diubah:

- `routes/web.php`
- `app/Http/Controllers/Admin/SearchController.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/notifications/index.blade.php`
- `resources/views/admin/profile/edit.blade.php`
- `resources/views/admin/search/index.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/NotificationTest.php`
- `tests/Feature/Admin/ProfileTest.php`
- `tests/Feature/Admin/SearchTest.php`
- `tests/Feature/Admin/AssetCrudTest.php`
- `tests/Feature/Admin/AssetPolicyTest.php`
- `docs/history/README.md`
- `docs/rencana/menu-per-role.md`

Dibuat:

- `docs/history/2026-11-09-notifikasi-profil-pencarian.md`

## Verifikasi

Blade cache:

```
INFO  Blade templates cached successfully.
```

Seluruh tes:

```
Tests:    143 passed (525 assertions)
Duration: 10.41s
```

Tes baru/ diubah memastikan:

- Notifikasi staf merender kartu belum dibaca.
- Profil staf merender "Informasi akun".
- Admin Prodi mendapat tautan aset baca-saja di pencarian.
- Technician dapat membuka daftar aset, tetapi tetap 403 pada form tambah dan
  ubah.

## Risiko / Catatan Lanjutan

- Technician belum punya aksi ubah kondisi aset; matriks menyebut U (condition
  only), tetapi belum ada route/UI-nya.
- Notifikasi hanya menampilkan pesan; tautan ke subjek belum ada di data.
- Verifikasi visual browser belum dilakukan; mesin tanpa Chromium/Firefox.
