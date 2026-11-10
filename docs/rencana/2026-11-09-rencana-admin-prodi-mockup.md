# Rencana Perombakan Area Admin Prodi — Penyelarasan dengan Mockup P1–P6

Tanggal: 2026-11-09
Status: RENCANA — belum ada kode, migration, atau route yang diubah
Ruang lingkup: role `admin_prodi` (P1, P2) + audit P3–P6
Branch: `feature/operator-operasional`
Commit terakhir: `9e8c734 feat(operator): build operator counter console and approval workflow`
Sumber acuan:

- `public/Design/mockup/p1_dashboard_admin_prodi_monitoring_kuota_rekomendasi_alat`
- `public/Design/mockup/p2_antrean_otorisasi_khusus_kaprodi_detail_verifikasi_portofolio`
- `public/Design/mockup/p3_kuota_utilisasi_mata_kuliah`
- `public/Design/mockup/p4_kalender_jadwal_praktikum_alokasi_studio_av`
- `public/Design/mockup/p5_mahasiswa_skor_kepatuhan_lab`
- `public/Design/mockup/p6_laporan_utilisasi_semesteran_rekomendasi_rab`
- `docs/rencana/menu-per-role.md`
- `docs/history/2026-11-09-admin-prodi.md`
- `AGENTS.md`

---

## 1. Tujuan

1. Menyelaraskan area Admin Prodi dengan mockup P1 dan P2 memakai data yang
   sudah ada.
2. Memberi Admin Prodi sidebar dan identitas area sendiri, bukan meminjam menu
   operator/organization admin.
3. Menjadikan antrean otorisasi khusus sebagai halaman kerja utama: pengajuan
   durasi panjang, nilai aset, identitas akademik pemohon, dan keputusan yang
   boleh diambil.
4. Memetakan P3–P6 secara jujur: mana yang bisa dibangun tanpa tabel baru dan
   mana yang harus ditunda.
5. Tetap patuh aturan proyek: Laravel 12, Blade + CSS murni, logic di
   `app/Services/`, status lewat enum, tes nyata, tanpa package baru.

---

## 2. Baseline Audit (kondisi saat ini)

| Item | Kondisi |
|---|---|
| Route dashboard | `GET /admin` → `AdminDashboardController` (satu view untuk 4 role) |
| Sidebar | Grup generik: Inventaris, Transaksi, Operasional, Administrasi |
| Menu terlihat prodi | Dashboard, Aset, Kategori, Antrean Booking, Semua Booking, Laporan |
| Menu khusus prodi | Tidak ada |
| Dashboard prodi | 4 KPI generik + kartu antrean khusus + 2 quick action |
| Antrean khusus | `admin.bookings.approval`, campur semua `pending`; prodi hanya bisa approve yang durasi > batas |
| Detail booking | `admin.bookings.show`, sudah ada identitas peminjam, unit, nilai aset, tombol setujui |
| Laporan | `admin.reports.index/print/export`, generik, bisa diakses prodi |
| Notifikasi/profil/pencarian | Shared admin, sudah jalan |
| Data akademik | `users`: `nim`, `study_program`, `semester`, `lab_tier`, `cohort_year`, `study_concentration`, `dpa_name`, `dpa_nidn`, `compliance_score`, `academic_verified_at` |
| Data nilai aset | `assets.purchase_price`, `Booking::totalAssetValue()` |
| Kuota semester | `QuotaService` + setting `semester_start`, `semester_end`, `semester_quota_hours` (dipakai borrower) |
| Skor kepatuhan | `BorrowerScoreService` (`compliance_score`, tier A+ s.d. D) |
| Mata kuliah | Tabel `courses` ada, `bookings.course_id` ada, tetapi belum dipakai di form/UI mana pun |
| Ruang/studio | Tidak ada tabel ruang, slot, atau jadwal praktikum |
| Portofolio/IPK/SKS | Tidak ada kolom atau tabel |
| RAB/rekomendasi alat | Tidak ada tabel atau aturan prediksi |
| Tes terkait prodi | `ApprovalQueueTest`, `AssetCrudTest`, `SearchTest`, `ShellTest`, `AuthorizationRegressionTest` |

### 2.1 Temuan yang perlu diperbaiki

1. **Sidebar prodi tidak punya menu "Otorisasi Khusus"**, padahal itu pekerjaan
   utama role ini. Pengguna harus mencari lewat "Antrean Booking".
2. **Dashboard prodi tidak selalu menampilkan antrean**: blok antrean hanya
   dirender bila `$specialQueue` tidak kosong, tanpa empty state.
3. **KPI `special` dan isi antrean memakai definisi berbeda**: KPI menghitung
   semua `pending` durasi panjang, antrean menyaring `requested_start_date >=
   hari ini`. Angka bisa tidak cocok.
4. **Kartu antrean prodi hanya bisa "Tinjau"**: tidak ada nilai aset, identitas
   akademik, atau tombol setujui langsung seperti P1/P2.
5. **Halaman antrean masih berjudul "Antrean Persetujuan" generik** dan
   menampilkan semua pengajuan, termasuk yang tidak boleh diputus prodi.
6. **Detail booking belum punya panel verifikasi prodi**: alasan eskalasi,
   skor kepatuhan, DPA, dan nilai aset belum diringkas untuk keputusan.
7. **Laporan prodi belum punya identitas prodi**; masih tampilan laporan
   organisasi.
8. **P3–P6 belum punya data pendukung** sehingga tidak boleh dibangun dengan
   mengarang tabel/kolom.

---

## 3. Inventaris Mockup vs Kesiapan Data

| Mockup | Isi utama | Data tersedia | Keputusan |
|---|---|---|---|
| **P1** Dashboard Prodi | KPI, alert otorisasi, kartu per mata kuliah, antrean khusus dengan nilai & aksi, rail rekomendasi/kepatuhan/agenda | KPI, antrean, nilai aset, skor kepatuhan, setting batas. Tidak ada kuota per MK, rekomendasi, agenda | **Fase A**: KPI + alert + antrean + rail aturan/kategori. Bagian MK/rekomendasi/agenda ditunda |
| **P2** Antrean Otorisasi Khusus | Alasan eskalasi, profil pemohon, portofolio, paket aset + reservasi, risiko, catatan pembimbing, otorisasi digital | Identitas akademik, skor, unit + nilai, jadwal. Tidak ada IPK/SKS/portofolio/risiko | **Fase A**: alasan, identitas, nilai, unit, keputusan approve. Portofolio/risiko/tanda tangan digital ditunda |
| **P3** Kuota & Utilisasi MK | Kuota per MK, dosen, transfer kuota | `courses`, `bookings.course_id`, kuota global per mahasiswa. Tidak ada kuota per MK, dosen pengampu per booking, semester aktif | **Fase B2** (butuh schema + aturan) |
| **P4** Kalender Praktikum | Slot studio, bentrok jadwal, dispensasi | Booking per tanggal, aset. Tidak ada ruang/studio, slot jam, jadwal kuliah | **Fase B3** (butuh schema) |
| **P5** Mahasiswa & Kepatuhan | Daftar mahasiswa, skor, BAP, status akses, pemulihan | `compliance_score`, tier, `penalties`, `payments`, status user | **Fase B1** (read-only, tanpa schema) |
| **P6** Laporan Semester & RAB | Utilisasi semester, bottleneck, rekomendasi RAB, tanda tangan | Laporan peminjaman periodik, nilai aset. Tidak ada RAB/prediksi | **Fase B4** (butuh agregasi + model RAB) |
| P1 rail AI/Rekomendasi | Prediksi kebutuhan alat | Tidak ada | Ditunda |
| P2 portofolio & IPK | Proposal, IPK, SKS, sertifikat | Tidak ada | Ditunda |

---

## 4. Keputusan Lingkup

### Fase A — sekarang (frontend + service, tanpa migration)

1. Sidebar Admin Prodi sendiri + badge antrean khusus.
2. Dashboard Prodi mengikuti struktur P1 dengan data nyata.
3. Halaman `admin.bookings.approval` punya varian prodi: hanya pengajuan
   otorisasi khusus.
4. Panel "Verifikasi Otorisasi Prodi" di `admin.bookings.show`.
5. Identitas prodi pada halaman laporan (judul/eyebrow saja).
6. Tes fitur + catatan `docs/history/`.

### Fase B — perlu persetujuan terpisah

- **B1** Daftar mahasiswa & kepatuhan read-only (P5) — data sudah ada.
- **B2** Kuota/utilisasi mata kuliah (P3) — butuh penugasan MK ke booking,
  kuota per MK, dan semester aktif.
- **B3** Kalender praktikum/studio (P4) — butuh tabel ruang dan slot.
- **B4** Laporan semester + RAB (P6) — butuh agregasi semester dan model RAB.

### Tidak dibuat

- Rekomendasi AI, prediksi alat, portofolio, IPK/SKS, tanda tangan digital,
  agenda rapat. Tidak ada data dan di luar MVP.

---

## 5. Rencana Fase A (detail per file)

### A1. Sidebar Admin Prodi

File: `resources/views/layouts/admin.blade.php`
File: `app/Providers/AppServiceProvider.php`

Struktur menu khusus `admin_prodi`:

| Grup | Menu | Route | Badge |
|---|---|---|---|
| Utama | Dashboard Prodi | `admin.dashboard` | — |
| Otorisasi | Otorisasi Khusus | `admin.bookings.approval` | jumlah antrean khusus |
| Monitoring | Aset (baca-saja) | `admin.assets.index` | — |
| Monitoring | Kategori (baca-saja) | `admin.categories.index` | — |
| Laporan & Dokumen | Laporan | `admin.reports.index` | — |

Perubahan:

- Tambah cabang `if ($role === 'admin_prodi')` yang mengganti grup generik,
  mengikuti pola cabang `operator` yang sudah ada.
- `AppServiceProvider`: pada composer `layouts.admin`, hitung
  `sidebarCounts['special_approvals']` untuk prodi:
  booking `pending` organisasi yang `isLongDuration(maxLoanDays)`.
- Badge memakai mekanisme `badge` + `sidebarCounts` yang sudah ada; jangan
  menambah JavaScript baru.
- Menu yang tidak relevan (user, setting, activity log, handover, inspeksi,
  maintenance, kalender, kiosk) tidak dirender untuk prodi.
- Item "Antrean Booking"/"Semua Booking" diganti satu item "Otorisasi Khusus"
  agar tidak ada dua pintu ke halaman yang sama.

### A2. Dashboard Prodi (P1)

File baru: `app/Services/ProdiDashboardService.php`
File: `app/Http/Controllers/Admin/DashboardController.php`
File: `resources/views/admin/dashboard.blade.php`
File: `public/css/admin.css`
File: `lang/id/ui.php`

Service `ProdiDashboardService::for(User $user): array` mengembalikan:

| Key | Isi | Sumber |
|---|---|---|
| `kpis.pending` | semua pengajuan menunggu | `bookings` |
| `kpis.special` | pengajuan durasi > `max_loan_days` | `bookings` + `isLongDuration()` |
| `kpis.active` | sedang dipinjam | `bookings` |
| `kpis.returned_this_month` | kembali bulan ini | `bookings.returned_at` |
| `kpis.students` | mahasiswa aktif organisasi | `users` role borrower |
| `kpis.low_compliance` | mahasiswa skor < 60 | `users.compliance_score` |
| `queue` | kartu pengajuan khusus | `ApprovalQueueService::card()` |
| `categories` | permintaan unit per kategori dari antrean | item antrean |
| `rules.max_days` | `max_loan_days` | `SettingService` |
| `rules.operator_limit` | `operator_approval_limit` | `SettingService` |
| `semester.label` | label dari `semester_start`/`semester_end` | `SettingService` |

Aturan:

- Definisi `special` untuk KPI dan antrean **harus sama**: `pending` +
  `isLongDuration(maxLoanDays)` + `requested_start_date >= hari ini`.
  Ini memperbaiki temuan §2.1 butir 3.
- `ApprovalQueueService::card()` dipakai ulang; tidak ada perhitungan baru di
  controller atau Blade.
- Controller hanya: `$prodiDashboard = app(ProdiDashboardService::class)->for($user)`.
  Variabel lama `$specialQueue` dihapus untuk role prodi agar tidak ada dua
  sumber data.

Tampilan dashboard prodi:

1. **Header**: eyebrow "Panel Akademik Prodi", judul "Dashboard Admin Prodi",
   chip semester (dari `semester.label`), quick action "Otorisasi Khusus" dan
   "Laporan".
2. **KPI**: 4 kartu utama (Menunggu, Otorisasi Khusus, Sedang Dipinjam, Kembali
   Bulan Ini) + 2 kartu ringkas (Mahasiswa Aktif, Kepatuhan Rendah) mengikuti
   grid yang ada.
3. **Alert otorisasi**: banner kuning bila ada antrean khusus, berisi jumlah dan
   tautan ke halaman antrean.
4. **Antrean Otorisasi Khusus**: selalu dirender.
   - Empty state bila kosong.
   - Kartu berisi: kode, nama, NIM, prodi, semester, tujuan, badge durasi,
     nilai aset (`Rp`), periode, jumlah unit + 2 nama unit teratas.
   - Aksi: "Tinjau" dan "Setujui" (POST `admin.bookings.approve`).
   - Tombol setujui `@disabled` bila `card.can_decide === false`, dengan alasan
     dari `card.block_reason` supaya tidak muncul 403.
5. **Rail kanan**: "Ketentuan Otorisasi Prodi" (batas hari operator, batas nilai
   operator, wewenang prodi) + "Permintaan per Kategori" (bar sederhana dari
   `categories`).
6. Gaya memakai token yang ada; kelas baru berawalan `.ldr-prodi-`.

### A3. Halaman Otorisasi Khusus (P2 daftar)

File: `app/Http/Controllers/Admin/BookingController.php`
File: `app/Services/ApprovalQueueService.php`
File: `resources/views/admin/bookings/approval.blade.php`
File: `lang/id/ui.php`

Perubahan:

- `BookingController::approval()`: untuk `admin_prodi`, set
  `$filters['scope'] = 'special'`.
- `ApprovalQueueService::for()`: dukung `scope=special` → filter
  `isLongDuration` setelah query (tetap memakai filter pencarian/kategori/sort).
- KPI untuk prodi: Menunggu, Konflik, Otorisasi Khusus, Tingkat Kelulusan.
  Angka "Menunggu" mengikuti scope (hanya pengajuan khusus).
- View:
  - Eyebrow "P2 · Otorisasi Khusus", judul "Otorisasi Khusus", subtitle khusus
    prodi.
  - Tombol walk-in disembunyikan untuk prodi (sudah lewat `$canRejectRole`).
  - Kartu: identitas akademik (`NIM`, prodi, semester, `lab_tier`), nilai aset,
    durasi, tujuan, periode, unit, dan status verifikasi akademik dari
    `academic_verified_at`.
  - Tombol Setujui langsung di kartu untuk prodi, dengan guard `can_decide`.
  - Tidak ada tombol Tolak (lihat §8).

### A4. Panel Verifikasi di Detail Booking (P2 detail)

File: `app/Http/Controllers/Admin/BookingController.php`
File: `resources/views/admin/bookings/show.blade.php`
File: `public/css/admin.css`
File: `lang/id/ui.php`

Perubahan:

- `BookingController::show()` menambah `$prodiReview` hanya bila role prodi:
  hasil `ApprovalQueueService::card($user, $booking)` (sudah berisi
  `is_special`, `value`, `can_decide`, `block_reason`) + ringkasan
  `BorrowerScoreService::summary($booking->borrower)`.
- View, khusus prodi dan `pending`:
  - Banner alasan eskalasi: durasi > batas dan/atau nilai > batas operator.
  - Kartu "Verifikasi Otorisasi Prodi" berisi: NIM, program studi, semester,
    angkatan, konsentrasi, DPA, skor kepatuhan + tier, status verifikasi
    akademik, nilai total aset, dan checklist aturan.
  - Label tombol utama menjadi "Setujui & Otorisasi" untuk prodi.
  - Tombol yang sama tetap memakai route `admin.bookings.approve`; tidak ada
    route baru.
- Tidak menduplikasi daftar unit/nilai aset yang sudah ada di halaman; panel
  hanya merujuk total.

### A5. Laporan Prodi

File: `resources/views/admin/reports/index.blade.php`
File: `lang/id/ui.php`

- Tambah eyebrow/identitas "Laporan Prodi" untuk role prodi (presentasi saja,
  memakai `auth()->user()->role->slug`).
- Filter periode, cetak, dan ekspor tetap seperti sekarang.
- Tidak ada agregasi semester baru; P6 ditunda.

### A6. Notifikasi, Profil, Pencarian

Tidak ada perubahan kode. Cukup verifikasi:

- `admin.notifications.index` menampilkan notifikasi milik prodi.
- `admin.profile.edit` menampilkan role dan organisasi.
- `admin.search` memberi tautan baca-saja untuk aset/kategori.

Bila ada bug, perbaikan dicatat terpisah, bukan bagian restyle.

### A7. CSS dan Bahasa

File: `public/css/admin.css`

Kelas baru (semua memakai token `--ldr-*`):

- `.ldr-prodi-head`, `.ldr-prodi-head__meta`
- `.ldr-prodi-grid`, `.ldr-prodi-rail`
- `.ldr-prodi-panel`, `.ldr-prodi-panel__head`
- `.ldr-prodi-identity`, `.ldr-prodi-identity__item`
- `.ldr-prodi-rule`, `.ldr-prodi-bar`
- `.ldr-prodi-escalation`

File: `lang/id/ui.php`

- Grup baru `ui.prodi.*`: `title`, `subtitle`, `queue`, `review`, `approve`,
  `escalation_duration`, `escalation_value`, `rule_*`, `identity_*`,
  `compliance_*`, `empty_*`.
- Update `ui.nav.otorisasi_khusus` bila belum ada; hapus pemakaian
  `ui.dashboard.prodi.*` yang tergantikan, jangan biarkan kunci mati.

### A8. Tes

| File | Skenario |
|---|---|
| `tests/Feature/Admin/ShellTest.php` | Sidebar prodi menampilkan "Otorisasi Khusus" + badge, laporan, aset; tidak menampilkan user/setting/handover |
| `tests/Feature/Admin/ProdiDashboardTest.php` (baru) | KPI dan antrean konsisten; kartu memuat nilai aset + identitas akademik; tombol setujui disabled untuk yang tidak berwenang; empty state |
| `tests/Feature/Admin/ApprovalQueueTest.php` | Prodi hanya melihat pengajuan durasi khusus; pengajuan normal tidak muncul; prodi tetap bisa approve durasi panjang |
| `tests/Feature/Admin/AuthorizationRegressionTest.php` | Prodi tidak bisa reject, tidak bisa akses kiosk/BAP/users/settings |
| `tests/Feature/Admin/ReportTest.php` | Halaman laporan dan ekspor tetap jalan untuk prodi |
| `tests/Unit/ProdiDashboardServiceTest.php` (baru) | Perhitungan KPI, filter antrean, kategori, definisi special |

Semua tes wajib dijalankan; angka lulus/gagal dilaporkan apa adanya.

---

## 6. Fase B — Rincian Singkat (belum dikerjakan)

### B1. P5 Daftar Mahasiswa & Kepatuhan (read-only)

- Halaman baru `admin/students/index` + pencarian dan filter tier.
- Data: `users` role borrower, `compliance_score`, `BorrowerScoreService`,
  jumlah pinjaman, denda aktif, status akun.
- Tanpa aksi sanksi; keputusan sanksi tetap di luar MVP.
- Butuh route, controller, view, tes. Tidak butuh migration.

### B2. P3 Kuota & Utilisasi Mata Kuliah

Butuh keputusan dan schema baru:

- Semester akademik aktif (tabel/setting).
- Penugasan `bookings.course_id` pada form peminjaman (saat ini tidak pernah
  diisi).
- Kuota jam per mata kuliah dan relasi dosen pengampu.
- Transfer kuota antar-MK.

### B3. P4 Kalender Praktikum & Studio

- Tabel ruang/studio, slot jam, jadwal kuliah, dan aturan bentrok.
- Bukan perluasan `bookings` biasa; butuh model jadwal terpisah.

### B4. P6 Laporan Semester & RAB

- Agregasi semester, analisis bottleneck, dan model rekomendasi pengadaan.
- Butuh tabel RAB/anggaran dan aturan prioritas.

---

## 7. Urutan Eksekusi dan Verifikasi

1. `ProdiDashboardService` + data contract + unit test.
2. Controller dashboard + sidebar + badge.
3. View dashboard prodi + CSS + lang.
4. Varian prodi pada halaman approval + filter scope.
5. Panel verifikasi di detail booking.
6. Identitas laporan prodi.
7. Tes fitur + regresi penuh.

Perintah verifikasi:

```
php artisan view:cache
php artisan test
```

Target: seluruh tes hijau, tanpa tes yang dilewati.

---

## 8. Risiko dan Keputusan yang Diminta

1. **Wewenang menolak.** Policy saat ini hanya mengizinkan Operator dan
   Organization Admin menolak; Admin Prodi hanya menyetujui. Mockup P2
   menampilkan "Tolak" dan "Minta Revisi". Rencana ini **tidak** menambah
   wewenang tolak. Perubahan itu mengubah aturan bisnis dan harus disetujui
   terpisah.
2. **Definisi otorisasi khusus.** Spesifikasi §6.2 memakai durasi >
   `max_loan_days`. Mockup P2 menambahkan nilai > Rp 5.000.000. Rencana ini
   mempertahankan definisi durasi saja; nilai hanya ditampilkan, bukan syarat
   baru.
3. **Semester.** Belum ada tabel semester aktif. Chip semester diturunkan dari
   setting `semester_start`/`semester_end`, tanpa pemilih semester.
4. **P3–P6.** Tidak dikerjakan pada Fase A karena butuh schema dan aturan baru.
   Membangunnya sekarang berarti mengarang data.
5. **Data akademik.** `compliance_score` bisa `null` untuk data lama; tampilkan
   `—`, jangan default 100 diam-diam di tampilan prodi.
6. **Beban query.** Badge sidebar menghitung durasi di PHP untuk semua
   `pending`. Jumlah `pending` per organisasi kecil; bila nanti besar, pindahkan
   ke query SQL atau cache.

---

## 9. Definisi Selesai Fase A

- Sidebar prodi punya "Otorisasi Khusus" dengan badge yang benar.
- Dashboard prodi memuat KPI, alert, antrean khusus lengkap dengan nilai aset
  dan aksi setujui yang aman, plus empty state.
- Halaman antrean prodi hanya menampilkan pengajuan yang boleh diputus prodi.
- Detail booking prodi menampilkan panel verifikasi akademik dan alasan
  eskalasi.
- Laporan tetap dapat diakses, dicetak, dan diekspor prodi.
- `php artisan test` hijau.
- Catatan pekerjaan ditulis di `docs/history/2026-11-09-admin-prodi-mockup.md`.
