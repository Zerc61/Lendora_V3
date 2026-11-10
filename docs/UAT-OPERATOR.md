# UAT Operator — Loket

Gunakan akun demo: `operator@lendora.test`, `admin@lendora.test`,
`technician@lendora.test`, `adminprodi@lendora.test`,
`borrower1@lendora.test` (sandi `password`).

## Persiapan

- [ ] `php artisan migrate`
- [ ] `php artisan db:seed`
- [ ] `php artisan test` hijau
- [ ] `php artisan queue:prune` berjalan tanpa error

## W1 Dashboard

- [ ] `/admin` menampilkan 4 KPI, antrean hari ini, jadwal, lembari, shift.
- [ ] Menu hanya 3 grup: Utama, Operasional, Inventaris & Logistik.
- [ ] Menu pengguna, pengaturan, log aktivitas tidak tampil untuk operator.

## W6 Antrean persetujuan

- [ ] `/admin/bookings/approval` menampilkan kartu pengajuan.
- [ ] Konflik jadwal tampil sebagai banner dan unit alternatif muncul.
- [ ] Tombol Setujui nonaktif saat pengajuan melebihi batas operator.
- [ ] Tolak mewajibkan alasan.
- [ ] Alihkan unit mengubah unit pada booking.

## W7 Detail booking

- [ ] `/admin/bookings/{booking}` menampilkan identitas, kontak, unit, timeline.
- [ ] Cetak bukti pinjam terbuka setelah serah terima pickup.
- [ ] Tautan WhatsApp hanya muncul bila nomor tersedia.
- [ ] Lapor kerusakan membuka form BAP dengan booking terpilih.

## W8 Serah terima

- [ ] Checklist, kondisi, foto, dan tanda tangan tersimpan.
- [ ] Pickup mengubah booking menjadi aktif dan aset menjadi dipinjam.
- [ ] Return mengubah status sesuai hasil inspeksi.

## W9 Inspeksi

- [ ] Inspeksi bisa memilih konteks dan hasil.
- [ ] Hasil rusak membuat tiket maintenance dan status aset maintenance.

## W10 Maintenance

- [ ] Tiket punya kode, prioritas, teknisi, tenggat.
- [ ] Suku cadang dan biaya bisa ditambahkan.
- [ ] Progres dan kondisi akhir tersimpan saat selesai.

## O1 Kiosk scanner

- [ ] `/admin/kiosk` bisa lookup kode booking, NIM, dan kode aset.
- [ ] Pickup/return memvalidasi status booking.
- [ ] Tanda tangan dan cetak surat jalan tersedia.
- [ ] Shortcut keyboard dan mode layar penuh bekerja.

## O2 Kalender Gantt

- [ ] `/admin/bookings/calendar` menampilkan unit × hari.
- [ ] Konflik ditandai dan banner muncul.
- [ ] Filter 14/30 hari, bulan, dan kategori bekerja.
- [ ] Ekspor CSV dan iCal terunduh.
- [ ] Klik blok mengisi panel detail.

## O3 BAP & pembayaran

- [ ] BAP baru mendapat nomor otomatis dan total benar.
- [ ] Foto temuan tersimpan dan tampil di halaman detail.
- [ ] Baris biaya tambahan memperbarui total.
- [ ] Terbitkan BAP menangguhkan akses peminjam.
- [ ] Halaman print rapi saat dicetak.
- [ ] Verifikasi pembayaran melunasi tagihan; tolak wajib alasan.

## W3 Daftar aset (operator)

- [ ] KPI total/tersedia/dipinjam/maintenance tampil.
- [ ] Filter kategori, status, kondisi, dan lemari bekerja.
- [ ] Ekspor CSV menghormati filter.
- [ ] Tombol tambah/edit/nonaktifkan tidak tampil.

## W12 Laporan

- [ ] KPI, batang utilisasi, tren, top borrower, dan kerusakan tampil.
- [ ] Filter periode dan kategori bekerja.
- [ ] Ekspor CSV dan halaman print tersedia.

## O4 Antrean mandiri

- [ ] `/kiosk` menerbitkan tiket sesuai layanan.
- [ ] Operator memanggil dan menutup antrean.
- [ ] Monitor loket menampilkan nomor yang dipanggil.

## Lintas role (wajib 403)

- [ ] Operator membuka `/admin/users` → 403.
- [ ] Operator membuka `/admin/settings` → 403.
- [ ] Technician membuka BAP → 403.
- [ ] Admin Prodi membuka kiosk → 403.
- [ ] Borrower membuka `/admin` → 403.

## Catatan temuan

| No | Halaman | Temuan | Tingkat | Status |
|---|---|---|---|---|
| | | | | |
