# 2026-11-08 — Filter Serah Terima

## Tujuan
Memudahkan operator menemukan transaksi yang menunggu pengambilan atau pengembalian.

## Perubahan
- Pencarian kode booking atau nama peminjam.
- Filter jenis: pengambilan, pengembalian, atau semua.
- Query tetap dibatasi organisasi pengguna.

## File tersentuh
- `app/app/Http/Controllers/Admin/HandoverController.php`
- `app/resources/views/admin/handovers/index.blade.php`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/HandoverTest.php`

## Verifikasi
- Test filter pickup/return dan pencarian kode.
- Test akses borrower tetap `403`.
- `php artisan test` lulus.

## Risiko
Tidak ada perubahan aturan bisnis serah terima.
