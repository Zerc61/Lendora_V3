# Laporan Fase 2 Discovery → Approval — FRONTEND

- **Yang dibangun:** komponen `timeline` (5 langkah, status selesai/aktif/menunggu/gagal), `status-tabs` (pill + jumlah per status, mempertahankan filter), `conflict-banner` (danger, pesan dari backend), `modal` generik (dipakai ulang oleh `confirm-modal`), toast di layout borrower. UI nyata untuk 6 halaman kontrak: form pengajuan (3 field + ringkasan unit read-only), daftar & detail peminjaman borrower, antrean persetujuan admin, detail booking admin, kalender ketersediaan bulanan. JS vanilla: modal, batas tanggal mulai ≤ selesai, auto-submit filter. Navigasi: bottom nav "Peminjaman Saya" + dot notifikasi, sidebar admin ke antrean/semua transaksi/kalender.
- **File penting:**
  - Create: `resources/views/components/{timeline,status-tabs,conflict-banner,modal}.blade.php`, `docs/fase-2-discovery-approval-frontend.md`.
  - Modify: `resources/views/borrower/bookings/{create,index,show}.blade.php`, `resources/views/admin/bookings/{index,show,calendar}.blade.php`, `resources/views/components/{status-badge,confirm-modal,icon}.blade.php`, `resources/views/layouts/{borrower,admin}.blade.php`, `resources/views/borrower/catalog/show.blade.php`, `public/css/{lendora,borrower,admin}.css`, `public/js/{borrower,admin}.js`, `lang/id/ui.php`, `app/Http/Controllers/Borrower/BookingController.php` (eager-load foto), `app/Http/Controllers/Admin/BookingController.php` (variabel presentasi).
  - Delete: tidak ada.
- **Migration/seed dijalankan:** tidak (frontend track).
- **Test:** `php artisan test` → **91 passed (271 assertions)**, durasi 2.67s.
- **Acceptance criteria fase:**
  - [x] Form pengajuan ≤ 4 field (mulai, selesai, tujuan, submit); ringkasan unit read-only dengan badge status.
  - [x] Hint "Maksimal :days hari" tampil; error konflik server tampil sebagai `conflict-banner`.
  - [x] `status-tabs` di borrower & admin; default antrean admin = Pending; filter status/tanggal/pencarian.
  - [x] Kartu booking borrower: kode mono, rentang tanggal, jumlah unit, badge status, mini progress 5 titik, aksi Batalkan (modal) + Detail.
  - [x] Detail borrower: header + badge, timeline, daftar item (foto/nama/kode/status item), kartu jadwal, banner alasan penolakan, batalkan via modal.
  - [x] Antrean admin: tabel lengkap (kode, peminjam, unit, periode, durasi, nilai, status, aksi); Setujui disabled + alasan kewenangan; Tolak via modal alasan wajib; baris `conflict-banner` untuk bentrok.
  - [x] Detail admin: identitas peminjam, tabel item + kondisi keluar/masuk, timeline, tombol keputusan dengan aturan kewenangan yang sama.
  - [x] Kalender: grid 7 kolom, chip berwarna per rentang, navigasi bulan sebelumnya/berikutnya mempertahankan `category_id`, legenda, filter kategori/bulan.
  - [x] Dot unread notifikasi di bottom nav borrower dari `$unreadNotifications`.
  - [x] `php artisan test` tetap hijau.
- **View Contract (perubahan):**

| View | Variabel baru | Sifat |
|---|---|---|
| `admin/bookings/index` | `$conflicts` (map `booking_id` → pesan konflik, hanya booking pending) | presentasi, memakai `BookingService::conflictMessageFor` |
| `admin/bookings/show` | `$blockReason` (string|null) | presentasi, memakai `BookingService::decisionBlockReason` |
| `admin/bookings/calendar` | `$categories` (daftar kategori organisasi untuk filter) | presentasi |
| `borrower/bookings/index`, `borrower/bookings/show`, `admin/bookings/index`, `admin/bookings/show` | eager-load `items.asset.photos` | performa tampilan, nama variabel tidak berubah |

- **Visual QA checklist:**
  - Browser tidak tersedia di lingkungan ini (tidak ada Chromium/Firefox/Playwright), jadi QA visual di browser **tidak dijalankan**. Verifikasi dilakukan lewat inspeksi CSS + render Blade di feature test.
  - 375px: input `font-size: 16px` (mencegah zoom iOS), `.ldr-borrower` max 480px, tab status `overflow-x: auto`, tombol borrower ≥ 44px, modal dapat di-scroll (`max-height: 100dvh - 32px`).
  - 1280px: tabel antrean dalam `.ldr-table-wrap` (scroll horizontal bila sempit), panel modal max 440px, kalender `grid-template-columns: repeat(7, minmax(0, 1fr))` dengan header bulan.
  - Conflict banner menampilkan pesan backend apa adanya (unit + tanggal + kode booking + saran rentang).
- **Catatan deviasi:**
  - Kalender diimplementasikan sebagai grid bulan 7 kolom (baris = minggu), setiap sel berisi chip unit + kode booking berwarna; opsi "baris = unit" tidak kompatibel dengan grid 7 kolom dan akan membuat 20+ tabel bulan bersarang.
  - Timeline hanya menampilkan event yang tersedia di backend Phase 2 (Diajukan, Disetujui, Ditolak/Dibatalkan). Langkah Diambil, Dikembalikan, Selesai/Inspeksi tampil sebagai menunggu sampai event Phase 3 ada.
  - Riwayat serah terima di detail admin masih placeholder (data handover Phase 3).
  - CTA "Ajukan Peminjaman" di detail aset kini menuju form nyata; notice `#booking-notice` dihapus.
  - Label sidebar "Peminjaman" dipecah menjadi "Antrean Persetujuan" dan "Semua Transaksi" sesuai Design.md.
- **Yang belum selesai:** halaman notifikasi/profil tetap "menyusul" (Phase 3), aksi handover/inspection/maintenance, QA visual browser.
- **Commit:** `feat(phase2-frontend): request form, status tracking, approval queue, availability calendar` (hash final ada di `git log -1`).
