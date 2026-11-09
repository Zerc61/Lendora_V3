# Rencana Perbaikan & Penambahan Lendora V3

Dokumen ini adalah daftar kerja aktif hasil audit proyek terhadap:

- `Panduan_Arahan_AI_Lendora_V3.pdf`
- `docs/Design.md`
- kondisi nyata kode, route, view, dan tes

Status: `SELESAI` · `DIKERJAKAN` · `BELUM` · `BUTUH PERSETUJUAN`

Aturan kerja:

1. Satu pekerjaan = satu catatan di `docs/history/`.
2. Jangan menambah dependency besar, role, permission, menu, route, atau tabel tanpa persetujuan.
3. Pembayaran, deposit, dan Midtrans tidak dibuat tanpa persetujuan.
4. Setiap perubahan wajib melewati `php artisan test` dan `php artisan view:cache`.

---

## 1. Ringkasan Audit

Modul inti PDF sudah tersedia: aset, kategori, katalog, ketersediaan, pengajuan,
persetujuan, serah terima, inspeksi, maintenance, notifikasi borrower, laporan,
pengguna, pengaturan, dan log aktivitas.

Gap yang ditemukan:

| Area | Temuan | Dampak |
|---|---|---|
| Notifikasi staf | Notifikasi kerusakan dikirim ke operator/admin, tetapi tidak ada halaman untuk membacanya | Notifikasi tidak terpakai |
| Tampilan admin | Dashboard, Pengguna, Inspeksi, Maintenance, Laporan, Pengaturan masih HTML polos | Tidak sesuai arah Clean Professional |
| Log aktivitas | Hanya daftar + pagination, tanpa filter/pencarian/ekspor | Sulit dipakai saat data banyak |
| Serah terima | Daftar sudah ada, belum ada pencarian/filter | Sulit saat transaksi banyak |
| Navigasi admin | Belum ada breadcrumb | Tidak sesuai pola admin di PDF |
| Teks lama | Beberapa teks menyebut fitur "fase berikutnya" padahal sudah ada | Menyesatkan pengguna |
| Multi-organisasi | Scoping data ada, UI pengelolaan organisasi belum ada | Arah pengembangan, bukan MVP |
| Pembayaran | Tidak ada | Sengaja ditunda oleh PDF |
| Role Admin Prodi/Technician | Sudah berjalan dengan aturan proyek | Kewenangan final belum disetujui PDF |

---

## 2. P0 — Perbaikan Fungsional

### A1. Pusat Notifikasi Staf — `SELESAI`
- Route `GET /admin/notifications`.
- Tandai satu dibaca dan tandai semua dibaca.
- Bell + jumlah belum dibaca di topbar admin.
- Scope: notifikasi milik pengguna yang sedang login.
- Tes: akses per role, tandai dibaca, isolasi antar pengguna.

### A2. Filter & Ekspor Log Aktivitas — `SELESAI`
- Pencarian deskripsi/aksi/nama pengguna.
- Filter aksi dan rentang tanggal.
- Ekspor CSV mengikuti filter aktif.
- Scope organisasi tetap dijaga.
- Tes: filter, isolasi organisasi, isi CSV.

### A3. Pencarian & Filter Serah Terima — `SELESAI`
- Pencarian kode booking atau nama peminjam.
- Filter status: menunggu pengambilan / menunggu pengembalian.
- Tes: hasil filter dan scope organisasi.

### A4. Breadcrumb Admin — `SELESAI`
- Breadcrumb di bawah topbar: Dashboard / judul halaman.
- Tidak tampil di Dashboard.
- Tes: muncul di halaman admin selain Dashboard.

### A5. Bersihkan Teks Usang — `SELESAI`
- Hapus teks "menyusul di fase berikutnya" yang tidak lagi benar.
- Hapus translation key mati.
- Tes: tidak ada regresi teks.

---

## 3. P1 — Konsistensi UI Admin

Semua halaman memakai komponen yang sudah ada: `ldr-page-head`, `ldr-filter-bar`,
`ldr-table-card`, `ldr-table`, `ldr-card`, `ldr-field`, `ldr-input`, `ldr-btn`,
`ldr-badge`, `ldr-empty-state`, `x-pagination`.

### B1. Dashboard — `SELESAI`
- Kartu KPI per role.
- Aktivitas terbaru, antrean khusus Admin Prodi, tugas Technician.
- Tautan cepat ke antrean terkait.

### B2. Pengguna — `SELESAI`
- Index: pencarian, filter peran, tabel, aksi ubah/tangguhkan/aktifkan.
- Form tambah/ubah memakai layout form admin.

### B3. Inspeksi — `SELESAI`
- Index: filter pencarian/hasil, tabel, aksi catat inspeksi.
- Form catat inspeksi memakai layout form admin.

### B4. Maintenance — `SELESAI`
- Index: tabel status, penugasan, aksi mulai/selesai/ubah.
- Form buat/ubah/selesai memakai layout form admin.

### B5. Laporan — `SELESAI`
- Filter periode/kategori.
- Kartu KPI, tabel utilisasi, peminjam teratas, kerusakan terbaru.
- Tombol ekspor CSV.

### B6. Pengaturan — `SELESAI`
- Form organisasi dan kebijakan memakai layout form admin.

### B7. Serah Terima — `SELESAI`
- Form kondisi per unit dengan tampilan tabel/kartu yang konsisten.

### B8. Borrower: Notifikasi & Profil — `SELESAI`
- Notifikasi: grup hari ini/sebelumnya, status baca, aksi tandai.
- Profil: kartu identitas dan form ubah data.

---

## 4. P2 — Penambahan Opsional

| Item | Status | Catatan |
|---|---|---|
| Profil admin | `SELESAI` | Staf dapat ubah nama, telepon, dan kata sandi sendiri |
| Preferensi notifikasi | `SELESAI` | Tersedia di profil borrower dan staf |
| Pencarian global | `SELESAI` | Aset, booking, pengguna, kategori dengan scope organisasi |
| Analitik laporan | `SELESAI` | Tren bulanan, aset terpopuler, keterlambatan kembali |
| Dark mode | `TIDAK DIBUAT` | Di luar MVP menurut Design.md |
| UI multi-organisasi | `BUTUH PERSETUJUAN` | Menyentuh model provider |
| Pembayaran/deposit/Midtrans | `BUTUH PERSETUJUAN` | Dilarang PDF tanpa persetujuan |
| Finalisasi role Admin Prodi/Technician | `BUTUH PERSETUJUAN` | PDF menyebut belum final |

---

## 5. Urutan Eksekusi

1. P0 fungsional: A1, A2, A3, A4, A5.
2. P1 UI: B1–B8.
3. Verifikasi: `php artisan test`, `php artisan view:cache`, cek route baru.
4. Perbarui status di dokumen ini.
5. Tulis history per pekerjaan.

## 6. Definisi Selesai

- Route dan halaman dapat dibuka sesuai role.
- Tidak ada tautan `#` pada navigasi.
- Scope organisasi tidak bocor.
- Test lama tetap lulus; test baru untuk logika baru.
- Tampilan memakai token dan komponen desain yang sudah ada.
- Tidak ada dependency baru.

---

## 7. Status Eksekusi Terakhir

Tanggal: 2026-11-08

- P0 A1–A5: `SELESAI`
- P1 B1–B8: `SELESAI`
- P2: profil admin, preferensi notifikasi, pencarian global, analitik laporan `SELESAI`
- `php artisan test`: **129 passed (454 assertions)**
- `php artisan migrate`: `0015_add_notification_preferences_to_users_table` berhasil
- `php artisan view:cache`: berhasil
- Tautan `#`: tidak ada
- Dependency baru: tidak ada

Catatan lanjutan:

- Dark mode tetap tidak dibuat karena di luar MVP `Design.md`.
- UI multi-organisasi, pembayaran/deposit/Midtrans, dan finalisasi role tetap menunggu persetujuan.
- Riwayat pekerjaan ada di `docs/history/`.

