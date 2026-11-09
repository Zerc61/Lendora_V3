# Operasional Operator: Antrean, Detail, Serah Terima, Inspeksi, Maintenance

Tanggal: 2026-11-08
Jenis pekerjaan: frontend
Status: selesai

## Tujuan

Menyelaraskan halaman operasional operator dengan mockup Stitch W6–W10 tanpa
mengubah aturan bisnis, route, validasi, atau otorisasi:

- Antrean persetujuan menampilkan ringkasan status dan kartu pengajuan.
- Detail booking menampilkan identitas peminjam, unit berfoto, dan aksi cepat.
- Form serah terima menampilkan mode pickup/return dan kondisi per unit.
- Daftar serah terima memakai kartu dengan aksi langsung.
- Inspeksi dan maintenance menampilkan ringkasan angka nyata.

## Perubahan

### Antrean persetujuan

`Borrower` tidak berubah. `Admin/BookingController@index` menambah
`queueStats` dari hitungan status yang sudah ada.

`admin/bookings/index.blade.php`:

- Empat kartu ringkasan: menunggu, disetujui, dipinjam, dikembalikan.
- Filter status, pencarian, rentang tanggal, dan paginasi tetap.
- Tabel diganti kartu antrean berisi kode, status, waktu pengajuan, identitas
  peminjam, tujuan, periode, daftar unit berfoto, nilai aset, konflik jadwal,
  serta aksi detail/setujui/tolak.
- Tombol setujui/tolak tetap memakai `canDecide`, `limitExceeded`, dan
  `conflicts`; modal penolakan tidak berubah.

### Detail booking

`admin/bookings/show.blade.php`:

- Header booking berisi kode, status, nama peminjam, NIM/prodi/semester,
  periode, jumlah unit, tujuan, dan aksi setujui/tolak/serah terima.
- Data peminjam dan tujuan tetap ditampilkan lengkap.
- Tabel unit diganti kartu unit berfoto dengan kondisi keluar/masuk, status
  item, dan nilai unit.
- Ringkasan periode, jatuh tempo, dan total nilai aset.
- Timeline, riwayat serah terima, kartu keputusan, dan modal penolakan tetap.

### Serah terima

`Admin/HandoverController` menambah eager load `asset.photos` pada daftar dan
form.

`admin/bookings/handover.blade.php`:

- Banner mode pickup/return menjelaskan dampak status transaksi.
- Unit ditampilkan sebagai kartu berfoto; setiap unit tetap memakai select
  `conditions[item_id]`.
- Catatan dan tombol simpan tetap memakai field dan route lama.
- Sidebar ringkasan transaksi.

`admin/handovers/index.blade.php`:

- Tabel diganti kartu berisi kode, peminjam, jenis serah terima, periode,
  daftar unit berfoto, dan aksi detail/proses.
- Filter pencarian dan jenis tetap.

### Inspeksi

`Admin/InspectionController@index` menambah ringkasan total, baik, perlu
perbaikan, rusak, dan inspeksi hari ini.

`admin/inspections/index.blade.php` menambah empat kartu ringkasan di atas
filter. Tabel, filter hasil, dan paginasi tetap.

### Maintenance

`Admin/MaintenanceController@index` menambah ringkasan terjadwal, dikerjakan,
selesai, dan total biaya. Query teknisi tetap dibatasi ke tugas miliknya.

`admin/maintenances/index.blade.php` menambah empat kartu ringkasan dan
thumbnail aset pada tabel. Aksi mulai/selesai/edit tetap sesuai role.

### Gaya

`public/css/admin.css` menambah komponen:

- Varian warna kartu KPI.
- Kartu antrean, item antrean, dan total nilai.
- Header detail booking, kartu unit, dan ringkasan biaya.
- Banner dan kartu form serah terima.
- Kartu daftar serah terima.
- Thumbnail kecil tabel.

Semua memakai token orange + putih yang sudah ada; tanpa gradient, glow, atau
glassmorphism.

### Bahasa

`lang/id/ui.php` menambah grup `ui.queue` dan kunci
`ui.handover.mode_*`, `ui.handover.summary`, `ui.inspection.kpi_*`, serta
`ui.maintenance.kpi_*`.

## File Tersentuh

Diubah:

- `app/Http/Controllers/Admin/BookingController.php`
- `app/Http/Controllers/Admin/HandoverController.php`
- `app/Http/Controllers/Admin/InspectionController.php`
- `app/Http/Controllers/Admin/MaintenanceController.php`
- `resources/views/admin/bookings/index.blade.php`
- `resources/views/admin/bookings/show.blade.php`
- `resources/views/admin/bookings/handover.blade.php`
- `resources/views/admin/handovers/index.blade.php`
- `resources/views/admin/inspections/index.blade.php`
- `resources/views/admin/maintenances/index.blade.php`
- `public/css/admin.css`
- `lang/id/ui.php`
- `tests/Feature/Admin/ApprovalQueueTest.php`
- `tests/Feature/Admin/HandoverTest.php`
- `tests/Feature/Admin/InspectionTest.php`
- `tests/Feature/Admin/MaintenanceTest.php`
- `docs/rencana/menu-per-role.md`

Dibuat:

- `docs/history/2026-11-08-operasional-operator.md`

## Verifikasi

Blade cache:

```
INFO  Blade templates cached successfully.
```

Seluruh tes:

```
Tests:    136 passed (491 assertions)
Duration: 10.36s
```

Tes baru/ tambahan memastikan:

- Antrean dan detail booking merender kode, unit, dan tujuan.
- Form serah terima merender unit dan pilihan kondisi.
- Inspeksi dan maintenance merender ringkasan beserta data nyata.

## Risiko / Catatan Lanjutan

- Kalender masih tampilan bulanan; mockup O2 memakai Gantt yang butuh data dan
  backend baru, jadi belum diubah.
- Daftar aset untuk operator dan halaman laporan belum diubah pada pekerjaan
  ini karena sudah memakai tabel/filter yang berfungsi.
- Verifikasi visual browser belum dilakukan; mesin tidak memiliki Chromium atau
  Firefox. Verifikasi memakai tes HTTP dan Blade cache.
- Tombol aksi tetap mengikuti otorisasi dan batas nilai yang sudah ada; tidak
  ada aturan bisnis baru.
