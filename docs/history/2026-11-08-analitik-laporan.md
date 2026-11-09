# 2026-11-08 — Analitik Laporan

## Tujuan
Menambah kedalaman laporan tanpa dependency baru: tren bulanan, aset paling sering dipinjam, dan keterlambatan pengembalian.

## Perubahan
- KPI baru `late_returns`: booking kembali dengan `returned_at` melewati `due_at` pada periode terpilih.
- Tabel tren peminjaman per bulan (jumlah transaksi dan total hari pinjam).
- Tabel aset paling sering dipinjam (maksimal 5 unit).
- Semua query mengikuti filter periode dan kategori serta scope organisasi.
- Kartu KPI dan tabel baru memakai komponen yang sudah ada.

## File tersentuh
- `app/app/Http/Controllers/Admin/ReportController.php`
- `app/resources/views/admin/reports/index.blade.php`
- `app/lang/id/ui.php`
- `app/tests/Feature/Admin/ReportTest.php`

## Verifikasi
- Test: total tren bulanan sama dengan total peminjaman, aset teratas tidak kosong, `late_returns` tersedia.
- `php artisan test`: 129 passed (454 assertions).

## Risiko
Utilisasi masih dihitung dari durasi pengajuan, bukan jam pemakaian sebenarnya. Perhitungan ini konsisten dengan laporan sebelumnya.
