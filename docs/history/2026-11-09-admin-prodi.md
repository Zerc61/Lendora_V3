# Admin Prodi: Dashboard Otorisasi Khusus dan Akses Baca-Saja

Tanggal: 2026-11-09
Jenis pekerjaan: frontend + perubahan otorisasi terbatas
Status: selesai

## Tujuan

Menyelaraskan area Admin Prodi dengan mockup P1/P2 memakai data yang sudah ada:

- Dashboard menampilkan KPI berikon dan antrean otorisasi khusus berbentuk
  kartu.
- Antrean khusus menampilkan identitas peminjam, tujuan, periode, durasi, dan
  jumlah unit.
- Aset dan kategori dapat dilihat tanpa tombol tambah/ubah/hapus.

Kuota prodi, rekomendasi alat, portofolio, dan skor kepatuhan (P3–P6) tidak
dibuat karena belum ada tabel atau aturannya.

## Perubahan

### Dashboard

`admin/dashboard.blade.php`:

- KPI Admin Prodi memakai ikon dan hint: menunggu, pengajuan khusus, sedang
  dipinjam, kembali bulan ini.
- Tabel antrean khusus diganti kartu berisi kode, nama, NIM/prodi, badge
  durasi, tujuan, periode, jumlah unit, dan tombol "Tinjau pengajuan".
- Daftar tetap memakai filter `isLongDuration()` dari controller; tidak ada
  aturan baru.

### Akses baca-saja aset dan kategori

Sesuai keputusan pengguna dan matriks izin (Admin Prodi = L):

`routes/web.php`:

- Grup baru `role:operator,organization_admin,admin_prodi` hanya untuk
  `GET admin/assets` dan `GET admin/categories`.
- Resource aset dan kategori tidak lagi menghasilkan route `index` di grup
  tulis; route `create/store/edit/update/destroy/retire` tetap
  operator/organization_admin.

`admin/categories/index.blade.php` memakai `$canManage` untuk menyembunyikan
tombol tambah, edit, hapus, dan modal. `admin/assets/index.blade.php` sudah
memakai guard yang sama.

Tidak ada perubahan pada `AssetPolicy`; `viewAny` sudah mengizinkan Admin Prodi.

### Gaya dan bahasa

`public/css/admin.css` menambah `.ldr-section-hint`, `.ldr-special-list`,
`.ldr-special-card`, `__head`, `__code`, `__purpose`, `__facts`, `__actions`.

`lang/id/ui.php` menambah `ui.dashboard.prodi.hint_*`, `queue_hint`, dan
`review`.

## File Tersentuh

Diubah:

- `routes/web.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/categories/index.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/ApprovalQueueTest.php`
- `tests/Feature/Admin/AssetCrudTest.php`
- `docs/history/README.md`
- `docs/rencana/menu-per-role.md`

Dibuat:

- `docs/history/2026-11-09-admin-prodi.md`

## Verifikasi

Blade cache:

```
INFO  Blade templates cached successfully.
```

Seluruh tes:

```
Tests:    141 passed (516 assertions)
Duration: 10.22s
```

Tes baru memastikan:

- Dashboard prodi merender "Pengajuan Khusus", kode booking durasi panjang, dan
  tombol tinjauan.
- Admin Prodi dapat membuka daftar aset dan kategori, tidak melihat tombol
  tambah, dan tetap mendapat 403 pada form edit aset.

## Risiko / Catatan Lanjutan

- Kuota, rekomendasi alat, portofolio, dan skor kepatuhan belum ada backend.
- Antrean khusus masih memakai definisi durasi > `max_loan_days`; belum ada
  nilai atau kategori khusus prodi.
- Notifikasi, profil, dan pencarian Admin Prodi belum disamakan.
- Verifikasi visual browser belum dilakukan; mesin tanpa Chromium/Firefox.
