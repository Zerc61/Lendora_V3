# Audit dan Rencana Perombakan Menu Organization Admin

Tanggal audit: 2026-11-09  
Diperbarui: 2026-11-11  
Area: konsol admin Lendora V3, fokus role `organization_admin`  
Status: **selesai diimplementasikan** pada commit `9a9ee6d`; dokumen ini
dipertahankan sebagai catatan audit dan acuan verifikasi.

## Tujuan dan batas

Dokumen ini mencatat kondisi navigasi Organization Admin, hasil telaah mockup yang berkaitan, dan urutan kerja yang disarankan. Perombakan berikutnya harus tetap memakai Blade dan CSS yang sudah ada, route yang tersedia, serta aturan akses yang berlaku. Dokumen ini tidak menyetujui penambahan fitur, route, kolom, atau perubahan otorisasi.

Mockup dikumpulkan sebagai indeks dan analisis di bawah. File visual asli tetap berada di `public/Design/mockup/`; tidak ada aset yang disalin atau dipindahkan.

## Ringkasan audit repo

- Saat audit, navigasi staf dirender dari array menu di `resources/views/layouts/admin.blade.php`. Role `organization_admin` saat itu memakai grup umum: Inventaris, Transaksi, Operasional, dan Administrasi. Kondisi sekarang (setelah `9a9ee6d`): Inventaris, Peminjaman, Operasional, dan Organisasi. Role operator dan admin prodi memiliki susunan khusus; technician memakai filter grup umum.
- Route organisasi yang tersedia mencakup dashboard, aset, kategori, booking dan kalender, serah terima, inspeksi, maintenance, pengguna, laporan, pengaturan, pencarian, notifikasi, profil, dan log aktivitas. Route berada di `routes/web.php`; visibilitas menu harus mengikuti izin di sana.
- Dashboard Organization Admin sudah menerima KPI dan `recentActivity` dari `DashboardController`. Layar aset, kategori, pengguna, laporan, dan pengaturan juga sudah memiliki controller dan view masing-masing.
- Tes shell saat ini memeriksa urutan grup menu, tautan admin, dan sebagian menu tersembunyi per role. `PageSmokeTest` memeriksa beberapa halaman admin dapat dirender. Tes-tes ini menjadi acuan saat perubahan navigasi kelak dikerjakan; audit ini tidak menjalankan tes.
- Ada perubahan lokal yang belum di-commit sebelum audit, termasuk perubahan pada layout admin, dashboard, booking, CSS, bahasa, serta implementasi Admin Prodi. Perubahan tersebut tidak disentuh. Review dan implementasi lanjutan perlu membedakan perubahan yang sudah ada dari perubahan khusus rencana ini.

### Temuan audit dan statusnya

1. **Kelompok menu belum menonjolkan fokus pemilik organisasi.** Organization Admin mendapat struktur umum yang berorientasi transaksi dan operasional, lalu menu Pengguna, Laporan, Pengaturan, dan Log Aktivitas disatukan di Administrasi. Rencana menyarankan pengelompokan lebih dekat pada tugas pemilik: Ringkasan, Inventaris, Peminjaman, Operasional, dan Organisasi.

   **Resolusi:** sudah dikerjakan pada `9a9ee6d`. Dashboard tetap berupa
   tautan terpisah di atas grup, bukan grup "Ringkasan".
2. **Wewenang route dan dokumen rencana lama tidak seluruhnya selaras.** `routes/web.php` membatasi pengaturan ke `organization_admin`, sementara `docs/rencana/menu-per-role.md` menyebut operator dapat melihatnya. Route serah terima saat ini menerima operator dan Organization Admin, meski matriks otorisasi master menetapkan Organization Admin hanya melihat. Route inspeksi juga perlu dicocokkan dengan batas lihat-saja sebelum menu atau aksi diubah. Ini dicatat sebagai isu audit; rencana UI tidak membuka atau menutup wewenang sendiri.

   **Resolusi:** master spec (`AGENTS.md` §7) menang. Route aktual sudah benar
   (pengaturan hanya `organization_admin`; serah terima dan inspeksi
   `organization_admin` lihat saja). Yang perlu diperbaiki adalah
   `docs/rencana/menu-per-role.md` yang menulis operator boleh melihat
   pengaturan. Tidak ada perubahan route atau policy.
3. **Menu dan label harus mengikuti kontrak route nyata.** Antrean dan semua transaksi memakai `admin.bookings.index` dengan query status, kalender memakai `admin.bookings.calendar`, sedangkan log aktivitas punya route tersendiri. Jangan menganggap item mockup sebagai endpoint baru.
4. **Mockup memuat data/fungsi di luar dukungan yang dapat dipastikan.** Pengelolaan logo, prefiks, jam layanan, backup, dan pengaturan notifikasi tambahan di W13; prefiks kode dan kebijakan durasi tiap kategori di W5; serta atribut akademik NIM/NIP/departemen di W11 masih di luar dukungan. Jangan masukkan fungsi atau field ini ke implementasi menu tanpa persetujuan dan audit backend.

   **Koreksi 2026-11-11:** unduh PDF dan pilihan semester di W2/W12 **sudah
   didukung** sejak commit `964b22f` (`admin.reports.pdf`,
   `admin.reports.semester.pdf`, laporan semester Admin Prodi). Keduanya tidak
   lagi termasuk fitur yang tertunda.
5. **Perlu menjaga pemisahan role.** Sidebar berulang pada mockup bersama bukan bukti bahwa semua role boleh melihat atau menjalankan seluruh item. Tautan, aksi, dan akses URL harus tetap dibatasi route/policy yang ada.

## Rencana struktur menu Organization Admin

Susunan berikut adalah label dan kelompok navigasi yang **sudah
diimplementasikan** pada `9a9ee6d`; tidak ada route baru:

| Grup | Item | Route yang tersedia | Catatan akses |
|---|---|---|---|
| — (tautan terpisah) | Dashboard | `admin.dashboard` | Dashboard varian Organization Admin; dirender di atas grup, bukan grup "Utama" |
| Inventaris | Aset | `admin.assets.index` | Kelola sesuai policy Organization Admin |
| Inventaris | Kategori | `admin.categories.index` | Kelola kategori organisasi |
| Peminjaman | Antrean Persetujuan | `admin.bookings.index` | Gunakan filter status antrean yang sudah ada |
| Peminjaman | Semua Peminjaman | `admin.bookings.index?status=all` | Gunakan query status yang sudah ada; label mengikuti `ui.nav.all_bookings` |
| Peminjaman | Kalender Ketersediaan | `admin.bookings.calendar` | Route tersedia untuk operator dan Organization Admin |
| Operasional | Serah Terima | `admin.handovers.index` | Tampilkan sesuai keputusan/otorisasi route; master spec menyebut lihat saja |
| Operasional | Pemeriksaan | `admin.inspections.index` | Tampilkan sesuai izin yang sudah berlaku; pastikan aksi sesuai role |
| Operasional | Maintenance | `admin.maintenances.index` | Organization Admin mengelola tugas; operator/teknisi punya izin berbeda |
| Organisasi | Pengguna & Role | `admin.users.index` | Khusus Organization Admin |
| Organisasi | Laporan & Analitik | `admin.reports.index` | Gunakan filter dan ekspor yang tersedia |
| Organisasi | Log Aktivitas | `admin.activity-logs.index` | Khusus Organization Admin |
| Organisasi | Pengaturan Organisasi | `admin.settings.edit` | Khusus Organization Admin |

Pencarian global, notifikasi, profil, dan keluar tetap berada di area utilitas topbar/footer; item tersebut bukan menu modul organisasi. Urutan dapat disesuaikan setelah telaah visual, tetapi jangan menghapus akses yang sudah dipakai tanpa keputusan eksplisit.

## Indeks mockup Organization Admin

Semua layar W2–W13 berikut punya `code.html` dan `screen.png` di folder yang dicantumkan. Mockup konsol menampilkan sidebar yang sama atau serupa; sidebar menjadi referensi pengelompokan visual, sementara hak akses ditentukan aplikasi.

| Mockup | File visual | Keterkaitan dan hasil analisis |
|---|---|---|
| W2 Dashboard Organization Admin | `public/Design/mockup/w2_dashboard_organization_admin/` | Referensi dashboard pemilik: KPI utilisasi/transaksi/isu/maintenance, utilisasi kategori, tren transaksi, aktivitas audit, dan otorisasi tertunda. Fokus dashboard lebih tajam daripada menu administrasi generik. Beberapa metrik dan alur khusus di screenshot perlu dicocokkan dengan data nyata sebelum dipakai. |
| W3 Daftar Aset | `public/Design/mockup/w3_daftar_aset_inventaris/` | Mendukung kelompok Inventaris. KPI status, pencarian, filter, tabel unit, dan aksi tambah aset relevan; aksi tulis tetap mengikuti policy. |
| W4 Form Tambah/Edit Aset | `public/Design/mockup/w4_form_tambah_edit_aset/` | Layar turunan menu Aset, bukan item sidebar baru. Analisis hanya sebagai konteks alur inventaris; jangan menambah field yang tidak didukung form/skema sekarang. |
| W5 Kategori Inventaris | `public/Design/mockup/w5_kategori_inventaris/` | Mendukung kelompok Inventaris. Hierarki kategori dan ringkasan unit dapat menjadi referensi; prefiks otomatis, aturan durasi kategori, dan panel konfigurasi tambahan tidak diasumsikan sudah didukung. |
| W6 Antrean Persetujuan | `public/Design/mockup/w6_antrean_persetujuan_approval_queue/` | Mendukung Peminjaman. Tampilkan antrean dan peringatan sesuai data konflik/otorisasi yang sudah disediakan; keputusan tetap mengikuti service dan kewenangan role. |
| W7 Detail Booking Admin | `public/Design/mockup/w7_detail_booking_admin/` | Detail transaksi yang dicapai dari daftar/antrean, bukan item menu utama tersendiri. Timeline dan riwayat berguna untuk konsistensi konteks. |
| W8 Form Serah Terima | `public/Design/mockup/w8_form_serah_terima_handover/` | Referensi alur Operasional. Organization Admin dalam matriks master hanya melihat handover; form tindakan tidak boleh dibuka kepadanya tanpa perubahan otorisasi yang disetujui. |
| W9 Pemeriksaan | `public/Design/mockup/w9_pemeriksaan_inspection/` | Referensi modul Operasional. Pastikan menu dan aksi Organization Admin hanya sesuai permission inspeksi aktual; jangan menyamakan akses lihat dengan pencatatan hasil. |
| W10 Maintenance | `public/Design/mockup/w10_maintenance_daftar_tugas_detail_drawer/` | Mendukung menu Maintenance. Daftar dan detail relevan; aksi kelola admin, buat tugas operator, dan pengerjaan teknisi harus dibedakan. |
| W11 Pengguna & Role | `public/Design/mockup/w11_pengguna_role/` | Mendukung kelompok Organisasi dan batas akun. Tabel, filter role/status, tambah, ubah, dan penangguhan selaras sebagai referensi. Ekspor dan atribut NIM/NIP/departemen di mockup perlu verifikasi dukungan. |
| W12 Laporan & Analitik | `public/Design/mockup/w12_laporan_analitik/` | Mendukung kelompok Organisasi. KPI, utilisasi kategori, tren, rekap, dan kerusakan membantu menemukan laporan. Gunakan route/filter yang ada; CSV, XLSX, dan PDF sudah tersedia. Laporan semester khusus Admin Prodi berada di luar menu ini. |
| W13 Pengaturan Organisasi | `public/Design/mockup/w13_pengaturan_organisasi/` | Mendukung kelompok Organisasi. Bagian yang jelas sesuai saat ini: identitas dasar organisasi, kebijakan pinjam, pengingat H-1, dan pengumuman borrower. Logo, prefiks, jam layanan, backup, dan toggle automasi tambahan perlu dukungan terpisah sebelum direncanakan sebagai fitur. |

Tidak dimasukkan: P1–P6 (Admin Prodi), O1–O4 (alur operator/kiosk), T-series (teknisi), dan mockup borrower. Layar-layar itu memiliki konteks role/produk berbeda, walaupun beberapa proses operasional dapat ditautkan oleh pengguna Organization Admin sesuai izin.

## Tahapan implementasi

Tahap 1–3 dan 5 sudah dilaksanakan pada `9a9ee6d`; tahap 4 diperluas setelah
audit browser 2026-11-10.

1. **Konfirmasi batas akses:** cocokkan matriks otorisasi master, middleware route aktual, policy, dan catatan `docs/rencana/menu-per-role.md`, terutama handover, inspeksi, maintenance, dan pengaturan.
2. **Rapikan kontrak navigasi:** sepakati label, urutan, grup, active state, badge, serta menu utilitas. Pakai nama route yang sudah ada; jangan ubah route atau data bisnis sebagai bagian pekerjaan frontend.
3. **Perbarui sidebar role-aware:** implementasikan tampilan Organization Admin di `resources/views/layouts/admin.blade.php` dan gaya di `public/css/admin.css` mengikuti token desain dan pola sidebar konsol. Pastikan role lain tidak berubah kecuali ada scope terpisah yang disetujui.
4. **Verifikasi navigasi dan aksi:** sesuaikan tes shell untuk urutan dan route
   menu Organization Admin, lalu pastikan menu role lain tetap tersembunyi dan
   URL lintas role tetap ditolak. Jangan hanya memeriksa sidebar: jelajahi
   setiap tautan dan tombol yang tampil per role, karena tautan ke route
   terlarang tetap bisa muncul di dalam halaman yang boleh dibuka (contoh nyata:
   Admin Prodi pernah melihat tombol ekspor aset, kalender, dan lapor kerusakan
   yang berujung 403). Jalankan tes relevan dan laporkan output aktual.
5. **Telaah visual:** bandingkan sidebar dan dashboard dengan W2 serta pola konsol W3–W13 di browser pada lebar desktop dan tablet; periksa fokus keyboard, label aktif, collapse, dan overflow.

### File yang tersentuh implementasi

- `resources/views/layouts/admin.blade.php` — grup menu Organization Admin
  (commit `9a9ee6d`)
- `public/css/admin.css` — gaya sidebar/grup
- `tests/Feature/Admin/ShellTest.php` — tes urutan grup dan menu tersembunyi
- `tests/Feature/Admin/AuthorizationRegressionTest.php` — tes aksi 403 tidak
  tampil (commit `71c6b3a`)
- `resources/views/admin/assets/index.blade.php`,
  `resources/views/admin/bookings/index.blade.php`,
  `resources/views/admin/bookings/show.blade.php` — sembunyikan tautan/aksi
- `docs/history/2026-11-10-audit-browser-borrower-operator-prodi.md` — laporan
  hasil

Tidak ada migration atau perubahan database yang dibutuhkan untuk perombakan
navigasi yang hanya memakai route dan data saat ini. Bila audit akses
menemukan koreksi route/policy perlu dilakukan, pisahkan menjadi scope backend
dan persetujuan tersendiri.

## Verifikasi audit

Berlaku untuk tanggal audit 2026-11-09. Status implementasi ada di bagian
terakhir dokumen.

- Status git, peta route, controller/view admin, tes shell/smoke, dokumen desain dan riwayat proyek, serta screenshot/code mockup W2–W13 telah ditelaah.
- Tidak ada tes atau migration yang dijalankan karena pekerjaan ini berupa audit dan penyusunan rencana.
- Tidak ada kode, route, database, CSS, atau mockup asli yang diubah.
- Satu file rencana ini dibuat pada `docs/rencana/` sesuai ruang lingkup.

## Status implementasi per 2026-11-11

- Sidebar Organization Admin sudah dirombak menjadi grup Inventaris,
  Peminjaman, Operasional, dan Organisasi pada commit `9a9ee6d`.
- Dashboard tetap berupa tautan terpisah di atas grup, bukan grup "Utama".
- Label aktual untuk transaksi adalah "Semua Peminjaman", bukan "Semua
  Transaksi".
- Ekspor PDF dan laporan semester sudah tersedia sejak commit `964b22f`.
- Audit browser 2026-11-10 menemukan dan memperbaiki tautan/aksi 403 yang
  tampil ke role tanpa izin (commit `71c6b3a`).
- Tindak lanjut dokumen: perbaiki baris "Pengaturan organisasi" pada
  `docs/rencana/menu-per-role.md` (operator `L` → `—`). Tidak ada perubahan
  route yang diperlukan.
