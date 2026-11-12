# Rencana Menu per Role

Tanggal: 2026-11-08
Status: rencana, belum dikerjakan
Dasar: audit Fase 1 (`docs/history/2026-11-08-audit-proyek-fase-1.md`)

## Tujuan

Daftar menu yang akan dikerjakan pada redesign, beserta role yang berhak
melihat dan memakainya. Dokumen ini menjadi acuan urutan kerja frontend dan
batas pekerjaan backend.

## Aturan

1. Slug role tidak berubah: `borrower`, `operator`, `organization_admin`,
   `admin_prodi`, `technician`.
2. Menu hanya boleh memakai route yang sudah ada, kecuali backend baru
   disetujui lebih dulu.
3. Role tanpa izin tidak boleh melihat menu, dan akses URL langsung harus 403.
4. Desain: orange `#F97316` + putih, tanpa gradient/glow/glassmorphism.
5. Setiap menu selesai wajib punya catatan di `docs/history/` dan tes nyata.
6. Menu di luar MVP ditunda sampai ada persetujuan eksplisit.

## Matriks Menu Area Borrower

| Menu | Route | Borrower |
|---|---|---|
| Onboarding | `/` | ya |
| Login | `login` | publik |
| Register | `register` | publik |
| Beranda | `borrower.home` | ya |
| Katalog | `borrower.catalog.index` | ya |
| Detail aset | `borrower.catalog.show` | ya |
| Ajukan peminjaman | `borrower.bookings.create` | ya |
| Peminjaman saya | `borrower.bookings.index` | ya |
| Detail peminjaman | `borrower.bookings.show` | ya |
| Notifikasi | `borrower.notifications.index` | ya |
| Profil | `borrower.profile.edit` | ya |

## Matriks Menu Area Staf

Keterangan: `L` = lihat saja, `CRUD` = kelola penuh, `—` = tidak tampil.

| Menu | Route | Operator | Org Admin | Admin Prodi | Technician |
|---|---|---|---|---|---|
| Dashboard | `admin.dashboard` | ya | ya | ya | ya |
| Pencarian global | `admin.search` | ya | ya | ya | ya |
| Aset | `admin.assets.*` | L | CRUD | L* | L* |
| Kategori | `admin.categories.*` | — | CRUD | L* | — |
| Antrean persetujuan | `admin.bookings.index` | ya | ya | ya | — |
| Semua peminjaman | `admin.bookings.index?status=all` | ya | ya | ya | — |
| Detail booking | `admin.bookings.show` | ya | ya | ya | — |
| Kalender ketersediaan | `admin.bookings.calendar` | ya | ya | — | — |
| Serah terima | `admin.bookings.handover`, `admin.handovers.index` | ya | L | — | — |
| Inspeksi | `admin.inspections.*` | ya | L | — | ya |
| Maintenance | `admin.maintenances.*` | buat | CRUD | — | kerjakan |
| Pengguna & role | `admin.users.*` | — | CRUD | — | — |
| Laporan | `admin.reports.*` | L | L | L | — |
| Pengaturan organisasi | `admin.settings.*` | — | CRUD | — | — |
| Log aktivitas | `admin.activity-logs.*` | — | ya | — | — |
| Notifikasi staf | `admin.notifications.index` | ya | ya | ya | ya |
| Profil staf | `admin.profile.edit` | ya | ya | ya | ya |

`*` Butuh keputusan: menu tampil tetapi route saat ini menolak role tersebut
(403). Pilihan: hapus menu, atau ubah route/policy menjadi baca-saja.

## Rencana Kerja per Role

### 1. Borrower

| Urutan | Menu | Status | Catatan |
|---|---|---|---|
| 1 | Onboarding | SELESAI | Overlay solid, tanpa blur/gradient, layout desktop sudah ada |
| 2 | Login/Register | SELESAI | Satu layout auth, orange + putih, tanpa gradient |
| 3 | Beranda | SELESAI | Sapaan, empat ringkasan status nyata, peminjaman aktif, rekomendasi |
| 4 | Katalog | SELESAI | Ringkasan ketersediaan, pencarian, chip kategori, kartu berfoto |
| 5 | Detail aset | SELESAI | Deskripsi panjang dan daftar spesifikasi dinamis |
| 6 | Ajukan peminjaman | SELESAI | Ringkasan dan form dua kolom pada desktop |
| 7 | Peminjaman saya | SELESAI | Daftar dua kolom pada desktop |
| 8 | Detail peminjaman | SELESAI | Grid jadwal, timeline, dan unit |
| 9 | Notifikasi | SELESAI | Daftar dua kolom pada desktop |
| 10 | Profil | SELESAI | Kartu identitas, ringkasan pinjaman, form, dan tombol keluar |

Layout borrower responsif: mobile memakai appbar + bottom nav, desktop memakai
topbar portal tanpa footer. Foto produk nyata dan data demo disediakan oleh
`CategoryAndAssetSeeder` dan `BorrowerShowcaseSeeder`. Detail:
`docs/history/2026-11-08-redesign-borrower.md`,
`docs/history/2026-11-08-redesign-auth.md`, dan
`docs/history/2026-11-08-mockup-borrower-foto-seeder.md`.

### 2. Operator

| Urutan | Menu | Status | Catatan |
|---|---|---|---|
| 1 | Dashboard | SELESAI | Layout khusus operator (W1), antrean + jatuh tempo hari ini |
| 2 | Antrean persetujuan | SELESAI | Kartu antrean + ringkasan status (W6); aksi setujui/tolak tetap |
| 3 | Detail booking | SELESAI | Hero peminjam, kartu unit berfoto, timeline (W7) |
| 4 | Kalender ketersediaan | SELESAI | Gantt unit × hari, KPI, konflik, ekspor CSV/iCal (O2) |
| 5 | Serah terima | SELESAI | Checklist, foto, tanda tangan, kode bukti (W8) |
| 6 | Inspeksi | SELESAI | Konteks, checklist, foto, tiket maintenance otomatis (W9) |
| 7 | Maintenance | SELESAI | Kode tiket, prioritas, parts, progres, kondisi akhir (W10) |
| 8 | Aset (lihat) | SELESAI | KPI, filter kategori/status/kondisi/lemari, ekspor CSV (W3) |
| 9 | Laporan | SELESAI | KPI, grafik utilisasi, tren, top borrower, kerusakan (W12) |
| 10 | BAP & denda | SELESAI | Nomor BAP, item biaya, foto, cetak, verifikasi pembayaran (O3) |
| 11 | Kiosk scanner | SELESAI | Lookup booking/NIM/aset, pickup/return, tanda tangan (O1a/O1b) |
| 12 | Antrean mandiri | SELESAI | Tiket, panggil, selesai, monitor loket (O4) |
| 13 | Shift & catatan | SELESAI | Shift aktif dari jam + catatan serah shift |
| 14 | Notifikasi, profil, pencarian | SELESAI | Kartu notifikasi, info akun, tautan aman |

Catatan: pekerjaan 2, 3, 5, 6, 7 tercatat di
`docs/history/2026-11-08-operasional-operator.md`. Overhaul operator lengkap
tercatat di `docs/history/2026-11-09-overhaul-operator.md`.

### 3. Organization Admin

| Urutan | Menu | Status | Catatan |
|---|---|---|---|
| 1 | Dashboard | SELESAI | KPI berikon, aksi cepat, umpan aktivitas (W2) |
| 2 | Aset | SELESAI | Ringkasan status + tabel berfoto (W3) |
| 3 | Kategori | SELESAI | Ringkasan induk/sub/unit + pohon (W5) |
| 4 | Antrean persetujuan | SELESAI | Kartu antrean + persetujuan di atas limit |
| 5 | Detail booking | SELESAI | Hero peminjam, kartu unit, timeline |
| 6 | Serah terima | SELESAI | Daftar kartu + form kondisi per unit |
| 7 | Inspeksi | SELESAI | KPI hasil + tabel inspeksi |
| 8 | Maintenance | SELESAI | KPI status/biaya, CRUD, penugasan |
| 9 | Pengguna & role | SELESAI | KPI akun + avatar; tambah/ubah/tangguhkan (W11) |
| 10 | Laporan | SELESAI | KPI berikon, filter, ekspor (W12) |
| 11 | Pengaturan organisasi | SELESAI | Banner aktif + kebijakan pinjam (W13) |
| 12 | Log aktivitas | SELESAI | Tabel dan filter sudah berfungsi |
| 13 | Notifikasi, profil, pencarian | SELESAI | Kartu notifikasi, info akun, tautan aman |

Catatan: pekerjaan 1–3 dan 9–11 tercatat di
`docs/history/2026-11-09-organisasi-admin.md`. Pekerjaan 4–8 tercatat di
`docs/history/2026-11-08-operasional-operator.md`.

### 4. Admin Prodi

| Urutan | Menu | Status | Catatan |
|---|---|---|---|
| 1 | Dashboard prodi | SELESAI | KPI berikon + kartu antrean khusus (P1); kuota/rekomendasi ditunda |
| 2 | Antrean otorisasi khusus | SELESAI | Kartu pengajuan durasi panjang + tinjauan (P2); portofolio ditunda |
| 3 | Laporan | SELESAI | KPI berikon, filter, ekspor |
| 4 | Aset/Kategori baca-saja | SELESAI | Route baca dibuka; tombol kelola disembunyikan |
| 5 | Notifikasi, profil, pencarian | SELESAI | Kartu notifikasi, info akun, tautan aman |

Menu P3–P6 ditunda: lihat bagian Menu Ditunda.

### 5. Technician

| Urutan | Menu | Status | Catatan |
|---|---|---|---|
| 1 | Dashboard workbench | SELESAI | KPI tugas + kartu work order milik teknisi |
| 2 | Daftar tugas maintenance | SELESAI | KPI, thumbnail, deskripsi, aksi mulai/selesai |
| 3 | Detail tugas + selesai | SELESAI | Lembar kerja + form hasil; checklist/part di luar MVP |
| 4 | Inspeksi | SELESAI | KPI hasil + form inspeksi |
| 5 | Aset (kondisi) | SELESAI | Baca-saja; ubah kondisi belum ada route/UI |
| 6 | Notifikasi, profil, pencarian | SELESAI | Kartu notifikasi, info akun, tautan aman |

Catatan: pekerjaan 1–3 tercatat di
`docs/history/2026-11-09-teknisi-workbench.md`.

## Menu Ditunda

| Menu | Role | Alasan |
|---|---|---|
| Pembayaran denda (M11) | borrower | Pembayaran di luar MVP |
| Pusat bantuan/regulasi (B9) | borrower | Bisa statis, tetapi belum disetujui |
| QR/PDF/perpanjangan (B6) | borrower | Butuh generator dokumen dan aturan baru |
| Kuota/utilisasi mata kuliah (P3) | admin_prodi | Butuh model prodi dan mata kuliah |
| Kalender praktikum/studio (P4) | admin_prodi | Butuh model jadwal dan studio |
| Skor kepatuhan (P5) | admin_prodi | Butuh aturan perhitungan |
| Laporan semesteran/RAB (P6) | admin_prodi | Butuh agregasi dan data RAB |
| Inventaris suku cadang (T3) | technician | Butuh tabel stok |
| Kalibrasi preventif (T4) | technician | Butuh tabel jadwal dan sertifikat |

## Urutan Pengerjaan Global

1. **Perbaikan cepat** — hapus menu 403, lengkapi kelas CSS hilang, pindahkan
   inline style, samakan sistem auth.
2. **Fondasi visual** — token, komponen bersama, layout staf per role, layout
   borrower final.
3. **Borrower** — 10 menu.
4. **Operator** — 10 menu.
5. **Organization Admin** — 13 menu.
6. **Technician** — 6 menu.
7. **Admin Prodi** — dashboard, antrean khusus, laporan setelah backend siap.
8. **Menu ditunda** — hanya setelah persetujuan terpisah.

## Definisi Selesai per Menu

- Route dapat dibuka role yang berhak; role lain mendapat 403.
- Menu tidak tampil untuk role tanpa izin.
- Tidak ada tautan mati atau `#`.
- Tampilan memakai token dan komponen bersama.
- Tidak ada inline style atau kelas CSS tanpa definisi.
- Tes lama tetap lulus; tes baru ditambah bila ada logika baru.
- Catatan history dibuat.

## File Terkait

- `docs/history/2026-11-08-audit-proyek-fase-1.md`
- `docs/history/2026-11-08-inventaris-mockup.md`
- `docs/history/2026-11-08-pemetaan-mockup-implementasi.md`
- `docs/Design.md`
- `public/Design/mockup/lendora_v3_struktur_menu_resmi_per_role_rbac.md`
