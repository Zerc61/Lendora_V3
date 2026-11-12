# Status Pekerjaan dan Rekomendasi Keamanan

Tanggal: 2026-11-12
Branch: `feature/operator-operasional`
Commit terakhir: `0ea71a6`

Dokumen ini adalah ringkasan kondisi setelah audit UI Borrower/Operator/Admin
Prodi, perbaikan menu Organization Admin, dan verifikasi MariaDB. Isinya: yang
sudah selesai, yang belum, dan rekomendasi keamanan berdasarkan kondisi kode
saat ini.

## 1. Sudah selesai

| # | Pekerjaan | Bukti |
|---|---|---|
| 1 | Audit browser 199 halaman (Borrower, Operator, Admin Prodi; 1440 & 390 px) | 0 error console, 0 error jaringan, 0 overflow |
| 2 | Perbaikan tautan/aksi 403 yang tampil ke role tanpa izin | commit `71c6b3a`, tes regresi di `AuthorizationRegressionTest` |
| 3 | Perbaikan semantik kartu booking borrower, `h1` halaman detail, label kalkulator denda | commit `71c6b3a` |
| 4 | Rencana audit menu Organization Admin disinkronkan dengan kode | commit `e91e01f` |
| 5 | Urutan grup Organisasi sesuai rencana + tes urutan | commit `0df4621` |
| 6 | Bug migration: FK bernama `1`, instalasi baru MariaDB gagal total | commit `0ea71a6`, `migrate:fresh` lolos 44 tabel / 70 FK |
| 7 | Bug approve ganda: transisi ke status yang sama tidak ditolak | commit `0ea71a6`, uji dua proses bersamaan |
| 8 | Bug `penalties.status` terlalu pendek (cicilan denda 500) | commit `0ea71a6`, kolom `varchar(30)` |
| 9 | Tes registrasi bergantung id seeder | commit `0ea71a6` |
| 10 | Verifikasi upload foto nyata (disk, DB, `/storage`, symlink) | `docs/history/2026-11-12-verifikasi-mariadb-dan-upload-foto.md` |
| 11 | Tes penuh dua mesin DB | MariaDB 315 passed, SQLite 315 passed |
| 12 | Matriks menu per role diperbaiki (operator tidak melihat Pengaturan) | commit `e91e01f` |

## 2. Belum selesai

### 2.1 Butuh keputusan atau tindakan Anda

| # | Item | Alasan belum dikerjakan |
|---|---|---|
| 1 | `php artisan migrate` di database `lendora` | Migration pelebaran `penalties.status` sudah ada, tapi database nyata belum diubah. Selama belum, ajukan cicilan denda tetap HTTP 500 |
| 2 | Uji jalur POST sukses lewat browser (approve, handover, inspeksi, maintenance) | Audit baru menguji GET, validasi gagal, dan service. Perlu keputusan apakah data demo boleh berubah |
| 3 | Bukti pengingat H-1 (`loans:remind-due`) | Butuh cron server atau persetujuan menjalankan manual di DB nyata |
| 4 | CI otomatis (GitHub Actions) | Butuh file workflow baru di luar peta direktori proyek |
| 5 | Pindahkan skrip verifikasi dari `/tmp/qa` ke repo | Aturan penempatan file belum mengizinkan folder skrip |

### 2.2 Butuh spesifikasi produk

| # | Fitur | Yang dibutuhkan |
|---|---|---|
| 6 | T3 suku cadang (spare parts) | Skema tabel + aturan bisnis |
| 7 | T4 kalibrasi | Skema + alur |
| 8 | Prefiks kode aset per kategori | Format pasti + migrasi data lama |
| 9 | Logo instansi | Berkas logo + ukuran |
| 10 | Field NIM/NIP | Sumber data, wajib/opsional |

### 2.3 Tidak ada endpoint/aturan, jadi belum bisa

- Sinkronisasi SIAKAD.
- Kirim dokumen ke dekanat.
- Dispensasi dekan.

### 2.4 Batas pengujian yang masih terbuka

| Item | Kondisi sekarang |
|---|---|
| Kunci baris MariaDB | Terbukti manual; belum masuk CI |
| FK `restrict` MariaDB | Terbukti manual; belum masuk CI |
| Perangkat Android/iOS nyata | Hanya browser headless |
| Beban / N+1 | Data demo kecil (27 aset, 12 booking) |
| Notifikasi email/push nyata | Tes memakai `Notification::fake()` dan mail `array` |
| Backup/restore database | Tidak ada (backup UI ditolak) |

## 3. Rekomendasi keamanan

Diurutkan berdasarkan risiko. Setiap temuan menyebut bukti di kode.

### 3.1 Prioritas tinggi

| # | Temuan | Bukti | Dampak | Tindakan |
|---|---|---|---|---|
| H1 | Bukti pembayaran denda disimpan di disk **public** | `PenaltyController.php:118`, `PaymentController.php:30` → `store('penalties/proofs', 'public')` | Bukti transfer berisi nama, rekening, nominal. Siapa pun yang mendapat URL dapat membukanya | Simpan di disk privat (`local`), sajikan lewat route ber-otorisasi atau `Storage::download` dengan policy |
| H2 | Token kiosk punya nilai default yang tertanam di kode | `EnsureKioskToken` → `config('lendora.kiosk_token', 'lendora-kiosk')` | Kiosk dapat dibuka siapa pun yang tahu nilai default; sesi kiosk tetap tersimpan | Wajib set `KIOSK_TOKEN` acak di `.env`, hapus fallback, rotasi berkala |
| H3 | `APP_DEBUG=true` dan `APP_ENV=local` | `.env:2,4` | Stack trace, query, dan path bocor bila `.env` ini ikut dipakai di server | Pastikan produksi `APP_ENV=production`, `APP_DEBUG=false`; tambahkan pemeriksaan saat deploy |
| H4 | Cookie sesi tidak dipaksa HTTPS | `config/session.php:172` → `SESSION_SECURE_COOKIE` kosong; `.env` `SESSION_ENCRYPT=false` | Cookie sesi bisa terkirim lewat HTTP polos | Set `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, dan paksa HTTPS |
| H5 | Belum ada security header | Tidak ditemukan middleware `X-Frame-Options`, CSP, HSTS, `Referrer-Policy` | Rentan clickjacking dan sniffing tipe konten | Tambah middleware header global; CSP ketat karena aset CSS/JS lokal |
| H6 | Kredensial database memakai `root` dengan password lemah | `.env:28-29` | Satu kebocoran = kendali penuh database | Buat user MariaDB khusus aplikasi, hak minimum (`SELECT/INSERT/UPDATE/DELETE` + migration), ganti password |

### 3.2 Prioritas sedang

| # | Temuan | Bukti | Dampak | Tindakan |
|---|---|---|---|---|
| M1 | Endpoint kiosk tanpa pembatasan laju | `routes/web.php:100-106` hanya login dan satu route memakai `throttle` | Percobaan tebak token dan penyalahgunaan lookup tak dibatasi | Tambah `throttle` pada grup kiosk dan endpoint lookup/process |
| M2 | Kebijakan password hanya `Password::defaults()` | `RegisteredUserController.php:37`, `NewPasswordController.php:37` | Password lemah diterima; tidak ada cek kebocoran | Naikkan `min(12)`, aktifkan `uncompromised()`, pertimbangkan 2FA wajib untuk admin |
| M3 | Tidak ada peringatan login gagal berulang | `LoginRequest.php` hanya rate limit 5/menit per email+IP | Serangan bertahap tidak terdeteksi | Catat login gagal ke `activity_logs`, kirim notifikasi ke Org Admin |
| M4 | Upload foto hanya `image|max:2048` | `StoreAssetRequest.php:38`, `UpdateAssetRequest.php:50` | Gambar besar/aneh diterima; EXIF dapat memuat lokasi | Batasi dimensi, strip metadata, pertimbangkan re-encode |
| M5 | Bukti pembayaran hanya `mimes:jpg,jpeg,png,pdf` | `PenaltyController.php:114` | PDF dapat memuat konten aktif | Simpan privat (lihat H1), pindai atau batasi tipe lebih ketat |
| M6 | Ekspor laporan/aset tidak dicatat | `ActivityLogger` dipanggil untuk aksi bisnis, bukan ekspor | Data keluar tidak dapat diaudit | Catat `report.exported` beserta filter dan role |
| M7 | `return_token` disimpan apa adanya | `BookingService::newReturnToken()` → 10 karakter acak | Bocor database = token pengembalian valid | Pertimbangkan hash token, atau turunkan masa berlaku setelah barang kembali |
| M8 | Penangguhan akun bergantung pada perubahan yang belum di-commit | `LoginRequest.php` (perubahan lokal) menolak user non-`active` | Bila perubahan tidak ikut dirilis, user `suspended` masih bisa login sampai middleware menolak | Selesaikan, tambahkan tes, lalu commit |

### 3.3 Prioritas rendah

| # | Temuan | Tindakan |
|---|---|---|
| L1 | Log aktivitas tidak tamper-evident | Pertimbangkan hash berantai atau kirim ke penyimpanan terpisah |
| L2 | Tidak ada retensi log/notifikasi | Tetapkan masa simpan dan pembersihan terjadwal |
| L3 | Belum ada `composer audit` berkala | Jalankan di CI dan tinjau temuan |
| L4 | Backup database belum diuji restore | Buat backup server + uji restore, terpisah dari fitur UI yang Anda tolak |
| L5 | Data demo memakai password `password` | Pastikan seeder demo tidak dipakai di produksi |
| L6 | `APP_URL=http://localhost` | Set domain HTTPS produksi agar URL bertanda tangan dan email benar |

## 4. Urutan yang disarankan

1. Jalankan migration `penalties.status` di database `lendora`.
2. Perbaiki H1 (bukti pembayaran ke disk privat) dan H2 (token kiosk).
3. Siapkan profil produksi: H3, H4, H5, H6.
4. Selesaikan M8 (penangguhan akun) lalu commit.
5. Baru lanjut uji POST browser, scheduler, dan CI.

## 5. Catatan

- Dokumen ini tidak mengubah kode, route, maupun database.
- Temuan H1–H6 berasal dari pembacaan kode, bukan pengujian penetrasi.
- Item yang sudah tidak berlaku (misalnya 403 Admin Prodi, migration gagal) tidak diulang di sini.
