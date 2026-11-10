# Rencana Perombakan Area Operator — Penyelarasan Penuh dengan Mockup Lendora V3

Tanggal: 2026-11-09
Status: RENCANA — belum ada kode, migration, atau route yang diubah
Ruang lingkup: role `operator` (W1, W3, W6, W7, W8, W9, W10, W12, O1a, O1b, O2, O3, O4)
Sumber acuan:

- `public/Design/mockup/w1_dashboard_operator`
- `public/Design/mockup/w3_daftar_aset_inventaris`
- `public/Design/mockup/w6_antrean_persetujuan_approval_queue`
- `public/Design/mockup/w7_detail_booking_admin`
- `public/Design/mockup/w8_form_serah_terima_handover`
- `public/Design/mockup/w9_pemeriksaan_inspection`
- `public/Design/mockup/w10_maintenance_daftar_tugas_detail_drawer`
- `public/Design/mockup/w12_laporan_analitik`
- `public/Design/mockup/o1_kiosk_scanner_loket_cepat_1`
- `public/Design/mockup/o1_kiosk_scanner_loket_cepat_2`
- `public/Design/mockup/o2_kalender_ketersediaan_unit_gantt_chart_jadwal_pinjam`
- `public/Design/mockup/o3_berita_acara_kerusakan_klaim_denda_loket`
- `public/Design/mockup/o4_kiosk_antrean_mandiri_mahasiswa`
- `public/Design/mockup/lendora_v3_struktur_menu_resmi_per_role_rbac.md`
- `public/Design/mockup/lendora_v3_blade_implementation_guide.md`
- `AGENTS.md`

---

## 1. Tujuan

1. Menjadikan area operator sebagai **konsol loket sirkulasi** yang cepat dipakai
   saat jam layanan, bukan sekadar kumpulan halaman CRUD.
2. Menyelaraskan tampilan operator dengan mockup W1/W3/W6/W7/W8/W9/W10/W12 dan
   O1–O4, tanpa merusak alur role lain.
3. Melengkapi backend yang belum ada: kiosk scanner, antrean mandiri, Gantt
   ketersediaan, BAP kerusakan, verifikasi pembayaran denda, checklist dan foto
   serah-terima, checklist inspeksi, komponen biaya maintenance, catatan shift.
4. Menjaga kepatuhan pada aturan proyek: Laravel 12, MariaDB, Blade + CSS murni,
   tanpa Vite untuk halaman kita, tanpa package baru tanpa persetujuan,
   business logic di `app/Services/`, status lewat enum, dan tes nyata.

---

## 2. Baseline Audit (kondisi saat ini)

| Item | Jumlah / Kondisi |
|---|---|
| Framework | Laravel 12, Blade + CSS murni |
| Controller | 35 |
| Model | 28 |
| Service | 13 |
| View Blade | 93 |
| Migration | 38 (terakhir `2026_11_09_000019_add_installment_months_to_penalties_table`) |
| Test | 40 kelas, **174 passed (668 assertions)** |
| Branch | `feature/borrower-portal`, working tree bersih |
| Commit terakhir | `102dce5 feat(borrower): complete borrower portal flows and UI polish` |

Route operator yang sudah ada (`/admin`):

| Kapabilitas | Route sekarang | Kondisi |
|---|---|---|
| Dashboard | `admin.dashboard` (`GET /admin`) | Satu view untuk 4 role, varian operator masih ringkas |
| Antrean + semua transaksi | `admin.bookings.index` | Satu halaman, filter status, belum berbentuk kartu keputusan |
| Detail booking | `admin.bookings.show` | Ada, belum selaras W7 |
| Approve / reject | `admin.bookings.approve` / `reject` | Ada, batas operator sudah dicek Service |
| Perpanjangan | `admin.bookings.extensions.approve` / `reject` | Ada |
| Serah terima | `admin.handovers.index`, `admin.bookings.handover[.store]` | Form generik, belum ada checklist/foto/ttd |
| Inspeksi | `admin.inspections.index/create/store` | Form generik, belum ada checklist/foto/konteks |
| Maintenance | `admin.maintenances.index/create/store` | Belum ada tiket, parts, progres, drawer |
| Aset | `admin.assets.index` (read-only operator) | Ada, belum selaras W3 |
| Kalender | `admin.bookings.calendar` | Masih tampilan sederhana, belum Gantt |
| Laporan | `admin.reports.index` + `export` CSV | Belum ada grafik/utilisasi/top borrower |
| Kiosk scanner | — | Belum ada |
| Antrean mandiri | — | Belum ada |
| BAP / denda | — | Tabel `penalties`, `payments`, `PenaltyService`, `PaymentService` ada, UI operator belum ada |
| Catatan shift | — | Belum ada |
| Verifikasi pembayaran | — | Belum ada route operator |

Skema yang sudah tersedia dan bisa dipakai ulang:

- `bookings`: `code`, `approved_by`, `approved_at`, `pickup_at`, `due_at`,
  `returned_at`, `return_token`, `pickup_location_id`, `supervisor_*`.
- `booking_items`: `condition_out`, `condition_in`, `status`.
- `handovers`: booking, type, performed_by, happened_at, notes.
- `inspections`: booking_item_id, asset_id, inspector_id, result, notes,
  checked_at.
- `maintenances`: asset_id, reported_by, assigned_to, status, description,
  cost, result_notes.
- `penalties`: `bap_number`, `incident_at`, `inspector_name`,
  `damage_description`, `repair_subtotal`, `subsidy_percent`, `subsidy_amount`,
  `admin_fee`, `total_amount`, `status`, `due_at`, `recovery_step`, `notes`.
- `payments`: penalty_id, borrower_id, method, amount, status, reference_number,
  proof_path, paid_at, verified_at, verified_by, rejection_reason.
- `assets`: `serial_number`, `grade`, `condition_score`, `purchase_price`,
  `location`, `status`, `condition_status`.
- `asset_components`: kelengkapan fisik per unit.
- `asset_blocks`: blok jadwal maintenance/other.
- `activity_logs`, `notifications`, `settings`.

Kesimpulan audit: **alur inti sudah ada, tetapi belum ada lapisan operasional
loket**. Pekerjaan terbesar bukan menulis ulang transaksi, melainkan menambah
data operasional (checklist, foto, ttd, tiket, antrean, BAP) dan membangun UI
loket di atas Service yang sudah ada.

---

## 3. Peta Mockup ke Target Implementasi

| Mockup | Layar | Route target | View target | Status akhir |
|---|---|---|---|---|
| W1 | Dashboard Operator | `GET /admin` (varian operator) | `admin/dashboard.blade.php` | Rombak total |
| W3 | Daftar Aset (read-only loket) | `GET /admin/assets` | `admin/assets/index.blade.php` | Rombak + export |
| W6 | Antrean Persetujuan | `GET /admin/bookings/approval` | `admin/bookings/approval.blade.php` | Baru |
| W7 | Detail Booking Admin | `GET /admin/bookings/{booking}` | `admin/bookings/show.blade.php` | Rombak total |
| W8 | Serah Terima | `GET/POST /admin/bookings/{booking}/handover` | `admin/bookings/handover.blade.php` | Rombak + data baru |
| W9 | Pemeriksaan / Inspeksi | `GET /admin/inspections`, `create`, `show` | `admin/inspections/*` | Rombak + data baru |
| W10 | Maintenance + Drawer | `GET /admin/maintenances`, `{maintenance}` | `admin/maintenances/index|show` | Rombak + data baru |
| W12 | Laporan & Analitik | `GET /admin/reports` | `admin/reports/index.blade.php` | Rombak total |
| O1a/O1b | Kiosk Scanner Loket Cepat | `GET /admin/kiosk` + lookup/process | `admin/kiosk/index.blade.php` | Baru |
| O2 | Kalender Gantt | `GET /admin/bookings/calendar` | `admin/bookings/calendar.blade.php` | Rombak total |
| O3 | BAP Kerusakan & Denda | `GET/POST /admin/penalties*` | `admin/penalties/*` | Baru |
| O4 | Kiosk Antrean Mandiri | `GET /kiosk` (keputusan terbuka) | `kiosk/queue.blade.php` | Baru, butuh persetujuan lokasi file |

---

## 4. Gap Analysis per Modul

### 4.1 Shell, sidebar, dan topbar

Kebutuhan mockup:

- Sidebar operator dikelompokkan: **Utama**, **Operasional Loket**,
  **Inventaris & Jadwal**.
- Item: Dashboard Loket, Kiosk Scanner Cepat, Serah Terima, Pemeriksaan &
  Inspeksi, Antrean Persetujuan, Berita Acara & Denda, Daftar Aset,
  Kalender Ketersediaan.
- Topbar menampilkan shift aktif, pencarian, notifikasi, identitas operator.
- Badge jumlah antrean pada menu.

Kondisi sekarang:

- Sidebar generik 4 role, grup `inventory/transactions/operations/administration`.
- Operator melihat menu yang bukan fokusnya (Kategori read-only, Laporan, dll.)
  dan tidak melihat Kiosk/BAP.
- Tidak ada indikator shift.

Tindakan:

- Ubah `resources/views/layouts/admin.blade.php`: definisi menu per role,
  operator memakai struktur mockup; role lain tetap.
- Tambah partial `resources/views/layouts/partials/admin-shift-badge.blade.php`
  (atau variabel layout) untuk shift aktif dari `ShiftService`.
- Badge antrean memakai data ringan (query count) yang di-cache per request.

### 4.2 Dashboard Operator (W1)

Kebutuhan mockup:

- Salam + tanggal + status "Live Operasional".
- Kartu shift aktif: label shift, jam, lokasi lemari, tombol tukar shift.
- KPI: Pengajuan Baru, Pickup Hari Ini, Jatuh Tempo Hari Ini, Terlambat.
- Tabel "Antrean Operasional Hari Ini" dengan tab Semua/Persetujuan/Pickup/
  Pengembalian dan aksi cepat Setujui / Catat Pickup / Detail.
- Panel "Jadwal Serah Terima Hari Ini" (jam, aktivitas, peminjam).
- Panel "Ketersediaan Lemari Lab".
- Catatan shift kemarin.

Kondisi sekarang:

- KPI operator: pending, approved, active, due_today (belum ada terlambat,
  pickup hari ini, lemari, shift, catatan shift).
- Tidak ada tabel antrean operasional gabungan.
- Tidak ada aksi cepat di dashboard.

Tindakan:

- Service baru `OperatorDashboardService` mengembalikan:
  `kpis`, `todayQueue`, `handoverSchedule`, `shelfSummary`, `shift`,
  `lastShiftNote`.
- Aksi cepat memakai route approve dan handover yang sudah ada. Tidak ada
  logika baru di controller.
- Tabel memakai komponen kartu/table yang sama dengan W6.

### 4.3 Antrean Persetujuan (W6)

Kebutuhan mockup:

- KPI: Menunggu Verifikasi, Potensi Konflik, Otorisasi Khusus, Tingkat
  Kelulusan.
- Filter prodi, urgensi, urutan (terdekat/terbaru/nilai tertinggi).
- Kartu permohonan berisi identitas peminjam, tujuan, rentang, unit, nilai,
  status ketersediaan, catatan, lampiran.
- Kartu konflik menampilkan unit bentrok, booking lawan, dan saran unit
  alternatif.
- Kartu otorisasi khusus menampilkan alasan > Rp 5.000.000 / durasi panjang.
- Aksi: Setujui, Tolak (alasan wajib), Sarankan Unit Alternatif, Teruskan ke
  Organization Admin.

Kondisi sekarang:

- `admin.bookings.index` menampilkan tabel, bukan kartu keputusan.
- Konflik hanya muncul sebagai pesan validasi saat approve.
- Tidak ada saran unit alternatif atau tombol teruskan.
- Batas operator sudah dihitung `BookingService::operatorBlockReason()`.

Tindakan:

- Route baru `GET /admin/bookings/approval` → `Admin\BookingController@approval`.
- Service `ApprovalQueueService` (atau metode di `BookingService`) mengembalikan
  kartu terurut + status konflik + alternatif + alasan blokir.
- Endpoint baru `POST /admin/bookings/{booking}/suggest-alternative` untuk
  mencatat unit pengganti yang disarankan (activity log + notifikasi borrower).
- "Teruskan ke Organization Admin" cukup mengubah penanda antrean
  (`bookings.notes` atau activity log) tanpa tabel baru; keputusan tetap lewat
  tombol approve milik org_admin. **Butuh keputusan** apakah perlu kolom
  `escalated_at`/`escalated_by`.

### 4.4 Detail Booking (W7)

Kebutuhan mockup:

- Header kode booking + status + aksi kontekstual.
- Kartu peminjam: prodi, NIM, kontak, skor kepatuhan, status akun.
- Daftar unit + nilai + kondisi keluar/masuk.
- Timeline status.
- Riwayat serah terima.
- Panel keputusan (approve/reject) dengan alasan blokir.
- Tautan ke BAP/penalti bila ada.

Kondisi sekarang:

- Sudah ada data booking, item, timeline, handover.
- Belum ada skor kepatuhan peminjam, status penalti, dan tautan BAP.
- Belum ada panel konflik yang eksplisit.

Tindakan:

- Tambah variabel presentasi di controller: `borrowerScore`, `activePenalties`,
  `handoverHistory`, `conflict`, `alternatives`.
- Rombak view; tidak mengubah transisi status.

### 4.5 Serah Terima (W8)

Kebutuhan mockup:

- Pilih mode Pickup / Return.
- Identifikasi transaksi via kode booking atau Ctrl+K.
- Checklist per unit: kondisi keluar/masuk, kelengkapan paket, foto.
- Catatan umum dan pernyataan tanggung jawab.
- Ringkasan peminjam, nilai tanggungan, status jaminan.
- Tanda tangan peminjam + operator.
- Simpan → status transaksi dan aset berubah, cetak bukti.

Kondisi sekarang:

- Form hanya mengirim kondisi per item + catatan.
- Tidak ada checklist, foto, tanda tangan, kode bukti, atau ringkasan jaminan.
- Service sudah memakai transaksi + lock dan transisi status yang benar.

Tindakan:

- Tabel `handover_items` + kolom operasional `handovers` (lihat §5).
- `HandoverService` diperluas dengan parameter opsional agar tes lama tetap
  hijau: `checklists`, `photos`, `signatures`, `shiftLabel`.
- Simpan file ke disk publik: `storage/app/public/handovers/{bookingCode}/`.
- Cetak bukti memakai halaman print (bukan PDF library).

### 4.6 Pemeriksaan / Inspeksi (W9)

Kebutuhan mockup:

- KPI: Inspeksi Hari Ini, Perlu Tindakan, Karantina/Rusak, Indeks Kelayakan.
- Form: unit target, konteks (pasca-pengembalian, rutin, laporan kerusakan,
  pengadaan), hasil (Baik / Perlu Perbaikan / Rusak), checklist 4 poin,
  catatan, foto.
- Riwayat inspeksi 30 hari per unit dengan filter hasil.
- Otomatisasi: hasil rusak → tiket maintenance + status aset maintenance.

Kondisi sekarang:

- Form hanya memilih item, hasil, catatan.
- Belum ada checklist, foto, konteks, riwayat, atau skor.
- `InspectionService` sudah membuat maintenance dan mengubah status aset.

Tindakan:

- Kolom baru `inspections`: `code`, `context`, `checklist`, `photo_paths`,
  `condition_score`, `follow_up`, `maintenance_id`.
- `InspectionService` diperluas; pemanggilan lama tetap valid dengan default.
- Halaman `admin/inspections/show` untuk riwayat per unit.

### 4.7 Maintenance (W10)

Kebutuhan mockup:

- KPI: Tugas Berjalan, Menunggu Suku Cadang, Total Biaya Bulan Ini, Aset
  Selesai Servis.
- Tabel tiket: kode tiket, aset, keluhan, teknisi, tanggal, status, biaya.
- Filter kategori, teknisi, status.
- Drawer detail: ringkasan kerusakan, asal laporan, penugasan, progres,
  rincian biaya/suku cadang, aksi teknisi.

Kondisi sekarang:

- Sudah ada create/edit/finish dan status maintenance.
- Belum ada kode tiket, prioritas, vendor, tanggal mulai/tenggat, parts,
  progres, final condition.
- Drawer belum ada.

Tindakan:

- Kolom baru `maintenances` + tabel `maintenance_parts`.
- `MaintenanceService`: `generateCode()`, `addPart()`, `recordProgress()`,
  `finish()` diperluas.
- Route `GET /admin/maintenances/{maintenance}` untuk halaman detail (drawer
  didekati sebagai panel di halaman index + halaman detail mandiri agar tetap
  bisa di-bookmark).
- Operator hanya membuat dan melihat; update status tetap technician (sesuai
  matriks izin).

### 4.8 BAP Kerusakan & Denda (O3)

Kebutuhan mockup:

- Header BAP: nomor registrasi, status tiket maintenance.
- Data peminjam + status akun (suspended) + kuota.
- Identitas unit dan temuan kerusakan (ringan/sedang/berat, foto, kronologi).
- Rencana reparasi + work order + rincian suku cadang + biaya.
- Perhitungan denda: subtotal, subsidi, biaya admin, total.
- Riwayat BAP, cetak BAP, sahkan & terbitkan tagihan.
- Status pembayaran dan tempo.

Kondisi sekarang:

- Tabel `penalties` dan `payments` ada; `PenaltyService` dan `PaymentService`
  hanya untuk sisi borrower (summary).
- Belum ada controller, route, view, item biaya, foto, atau nomor BAP otomatis.
- Belum ada mekanisme suspend otomatis dari BAP.

Tindakan:

- Tabel `penalty_items` + kolom BAP pada `penalties`.
- Service baru `BapService`: `create()`, `generateNumber()`, `recalculate()`,
  `issue()`, `recordPayment()`.
- `BapService::issue()` memanggil `PenaltyService`/`User` untuk menurunkan
  `compliance_score` dan menonaktifkan peminjaman mandiri sesuai keputusan
  policy (lihat §15).
- Halaman index/create/show/print.
- Verifikasi pembayaran di `admin/payments` memakai `PaymentService`.

### 4.9 Kalender Ketersediaan / Gantt (O2)

Kebutuhan mockup:

- KPI: okupansi pekan, unit aktif di luar, reservasi menunggu serah, konflik.
- Filter periode 14/30 hari/bulan/semester, kategori, status.
- Baris per unit, kolom per hari, bar booking berwarna status.
- Blok maintenance, slot kosong, konflik ditandai.
- Panel detail jadwal + aksi alihkan unit / sesuaikan tanggal / hubungi.
- Ekspor CSV/iCal.

Kondisi sekarang:

- `calendar` menampilkan daftar/agenda sederhana.
- `AvailabilityService::calendarFor()` sudah menyediakan data harian.

Tindakan:

- Service baru `GanttService` membangun baris per kategori/unit dan bar
  reservasi dari `booking_items` + `bookings` + `asset_blocks`.
- Deteksi konflik memakai `AvailabilityService::conflictsFor()`.
- Ekspor CSV dan iCal ditulis manual (tanpa package).
- Aksi "alihkan unit" memakai `BookingService::reassignItem()` baru dengan
  cek ketersediaan + lock.

### 4.10 Daftar Aset (W3)

Kebutuhan mockup:

- KPI total, siap digunakan, dipinjam, maintenance.
- Filter kategori, status, kondisi, lemari/lokasi.
- Tabel dengan foto, kode, nama, kategori, status, kondisi, lokasi, aksi lihat.
- Ekspor CSV, tombol tambah aset **tidak muncul untuk operator**.

Kondisi sekarang:

- Tabel aset sudah ada; filter dasar ada.
- Belum ada KPI, filter lokasi, ekspor CSV operator, dan tampilan read-only yang
  tegas.

Tindakan:

- Rombak view; tambah `Admin\AssetController@export` (CSV) dengan otorisasi
  `organization_admin` + `operator` read-only.
- Tombol tambah/edit tetap hanya org_admin.

### 4.11 Laporan & Analitik (W12)

Kebutuhan mockup:

- Filter periode + kategori.
- KPI: total peminjaman, durasi akumulasi, utilisasi, insiden.
- Grafik utilisasi per kategori, tren transaksi 30 hari.
- Tabel rekap kategori, top borrower, daftar kerusakan.
- Ekspor CSV/PDF, buat laporan khusus.

Kondisi sekarang:

- Laporan dasar + CSV.
- Belum ada grafik, top borrower, daftar kerusakan, filter periode/kategori.

Tindakan:

- Perluas `ReportController` + service baru `ReportAnalyticsService`.
- Grafik memakai HTML/CSS bar dan SVG sparkline (tanpa library chart).
- "PDF" memakai halaman print dengan CSS `@media print` (tidak menambah
  dependency).

### 4.12 Kiosk Scanner (O1a/O1b)

Kebutuhan mockup:

- Mode pickup/return, input scan barcode booking/KTM/unit atau input manual.
- Validasi peminjam + booking + unit.
- Checklist fisik + inspeksi cepat + foto.
- Tanda tangan digital + simpan → status berubah + cetak surat jalan.
- Antrean berikutnya, shortcut keyboard, mode kiosk fullscreen, beep.

Kondisi sekarang:

- Belum ada.
- `return_token` dan kode booking sudah tersedia; QR borrower dibuat di
  `public/js/borrower.js` (encoder QR internal).

Tindakan:

- `KioskService`: `lookup()`, `preflight()`, `pickup()`, `return()`.
- Lookup menerima kode booking, `return_token`, NIM, atau kode aset.
- Proses pickup/return memanggil `HandoverService` + `InspectionService` agar
  transisi status tetap satu pintu.
- `resources/views/admin/kiosk/index.blade.php` + `public/js/admin.js`.
- Setiap pemindaian dan pemrosesan dicatat ke `activity_logs`
  (`kiosk.scanned`, `kiosk.pickup`, `kiosk.return`).
- Cetak surat jalan memakai view print.

### 4.13 Kiosk Antrean Mandiri (O4)

Kebutuhan mockup:

- Layar sentuh: scan KTM / input NIM, pilih layanan (pickup, return,
  konsultasi), cetak atau kirim tiket WhatsApp.
- Monitor loket real-time: loket A/B/T, nomor dipanggil, sisa antrean.
- Tiket berhasil + estimasi panggil.

Kondisi sekarang:

- Belum ada.

Tindakan:

- Tabel `queue_tickets`.
- `QueueService`: `issue()`, `callNext()`, `markServed()`, `statusBoard()`.
- Halaman kiosk publik `/kiosk` dengan middleware token perangkat.
- Halaman operator `admin/kiosk/queue` untuk memanggil/menutup antrean.
- **Butuh keputusan lokasi file** karena di luar peta `resources/views/admin`
  (lihat §15).

### 4.14 Shift & catatan

Kebutuhan mockup:

- Shift aktif + jam + lokasi, tombol tukar shift.
- Catatan shift sebelumnya.
- Catatan shift baru.

Kondisi sekarang:

- Belum ada.

Tindakan:

- Tabel `shift_notes`.
- `ShiftService`: `current()` (dari settings + jam), `notes()`, `store()`.
- Operator dapat menulis catatan; org_admin dapat melihat semua.
- "Tukar shift" untuk MVP hanya form serah catatan + konfirmasi; bukan
  penjadwalan penuh (lihat §15).

### 4.15 Walk-in & reservasi manual (dari W1/O2)

Kebutuhan mockup: tombol "+ Peminjaman Baru (Walk-in)" di W1 dan
"+ Reservasi Manual Loket" di O2.

Kondisi sekarang: pembuatan booking hanya oleh borrower.

Tindakan jika disetujui:

- `BookingService::createWalkIn()` dengan pemilihan borrower oleh operator,
  validasi ketersediaan, dan activity log `booking.walk_in`.
- Route `GET/POST /admin/bookings/walk-in`.
- **Butuh keputusan** karena menambah jalur pembuatan transaksi baru (lihat §15).

---

## 5. Perubahan Database

Semua migration baru memakai prefix tanggal `2026_11_09_` dan nomor lanjut dari
`000019`. Tidak ada migration lama yang diedit. Perubahan dijalankan dengan
`php artisan migrate`, bukan `migrate:fresh`.

### 5.1 Migration baru

| No | File | Isi |
|---|---|---|
| 000020 | `create_handover_items_table.php` | checklist kondisi per unit |
| 000021 | `add_operational_fields_to_handovers_table.php` | kode bukti, shift, ttd, foto, checklist global |
| 000022 | `add_operational_fields_to_inspections_table.php` | kode, konteks, checklist, foto, skor, follow-up |
| 000023 | `add_operational_fields_to_maintenances_table.php` | kode tiket, prioritas, vendor, tanggal, biaya jasa, kondisi akhir |
| 000024 | `create_maintenance_parts_table.php` | rincian suku cadang |
| 000025 | `create_penalty_items_table.php` | rincian biaya BAP |
| 000026 | `add_bap_fields_to_penalties_table.php` | kategori kasus, kronologi, work order, tempo, foto |
| 000027 | `create_queue_tickets_table.php` | antrean mandiri |
| 000028 | `create_shift_notes_table.php` | catatan shift |

### 5.2 Detail kolom

`handovers` (tambah):

```text
code                      string(40) unique nullable
shift_label               string(60) nullable
borrower_signature_path   string(255) nullable
operator_signature_path   string(255) nullable
photo_paths               json nullable
checklist                 json nullable
due_at                    timestamp nullable
```

`handover_items` (baru):

```text
id
handover_id        FK handovers, cascadeOnDelete
booking_item_id    FK booking_items, restrictOnDelete
condition          string(20)          # enum AssetCondition
accessories        json nullable
notes              string(255) nullable
timestamps
unique(handover_id, booking_item_id)
```

`inspections` (tambah):

```text
code              string(40) unique nullable
context           string(30) default 'post_return'
checklist         json nullable
photo_paths       json nullable
condition_score   unsignedTinyInteger nullable
follow_up         string(30) nullable
maintenance_id    FK maintenances nullable nullOnDelete
```

`maintenances` (tambah):

```text
code              string(40) unique nullable
priority          string(20) default 'normal'
vendor            string(120) nullable
started_at        timestamp nullable
due_at            timestamp nullable
finished_at       timestamp nullable
labor_cost        unsignedInteger default 0
final_condition   string(20) nullable
progress          json nullable
```

`maintenance_parts` (baru):

```text
id
maintenance_id    FK maintenances, cascadeOnDelete
name              string(150)
part_code         string(60) nullable
quantity          unsignedSmallInteger default 1
unit_cost         unsignedInteger default 0
timestamps
```

`penalty_items` (baru):

```text
id
penalty_id        FK penalties, cascadeOnDelete
description       string(200)
part_code         string(60) nullable
quantity          unsignedSmallInteger default 1
unit_cost         unsignedInteger
subtotal          unsignedInteger
timestamps
```

`penalties` (tambah):

```text
case_category     string(60) nullable
chronology        text nullable
maintenance_id    FK maintenances nullable nullOnDelete
payment_terms_days unsignedTinyInteger default 7
photo_paths       json nullable
```

`queue_tickets` (baru):

```text
id
organization_id   FK organizations, cascadeOnDelete
number            string(10)
service_type      string(20)          # pickup|return|consultation
status            string(20) default 'waiting'
counter           string(10) nullable
user_id           FK users nullable nullOnDelete
booking_id        FK bookings nullable nullOnDelete
nim               string(30) nullable
issued_at         timestamp
called_at         timestamp nullable
served_at         timestamp nullable
service_date      date index
timestamps
unique(organization_id, service_date, number)
index(organization_id, service_date, status)
```

`shift_notes` (baru):

```text
id
organization_id   FK organizations, cascadeOnDelete
user_id           FK users nullable nullOnDelete
shift             string(20)
note              text
timestamps
index(organization_id, created_at)
```

### 5.3 Enum baru

| Enum | Nilai |
|---|---|
| `InspectionContext` | `post_return`, `routine`, `damage_report`, `procurement` |
| `InspectionFollowUp` | `none`, `maintenance`, `bap` |
| `MaintenancePriority` | `low`, `normal`, `high`, `critical` |
| `QueueServiceType` | `pickup`, `return`, `consultation` |
| `QueueTicketStatus` | `waiting`, `called`, `serving`, `done`, `skipped` |

Enum lama tetap: `AssetCondition`, `InspectionResult`, `MaintenanceStatus`,
`BookingStatus`, `BookingItemStatus`, `HandoverType`, `PenaltyStatus`.

### 5.4 Model baru / perubahan

Model baru:

- `HandoverItem`, `MaintenancePart`, `PenaltyItem`, `QueueTicket`, `ShiftNote`.

Model diubah (casts + relasi):

- `Handover`: `items()`, `photo_paths` array, `checklist` array.
- `Inspection`: `maintenance()`, array casts.
- `Maintenance`: `parts()`, `inspections()`, `penalties()`, array casts.
- `Penalty`: `items()`, `maintenance()`, array casts.
- `Asset`: tidak berubah struktur; tambah scope filter lokasi bila perlu.
- `Booking`: `queueTickets()` opsional.

---

## 6. Service dan Aturan Bisnis

### 6.1 Service baru

| Service | Tanggung jawab |
|---|---|
| `OperatorDashboardService` | KPI, antrean hari ini, jadwal serah terima, ringkasan lemari, shift |
| `ApprovalQueueService` | kartu antrean, urutan, konflik, alternatif, alasan blokir |
| `KioskService` | lookup kode/token/NIM/aset, preflight, pickup, return |
| `QueueService` | tiket antrean, panggil, selesai, papan monitor |
| `GanttService` | baris unit, bar reservasi, blok maintenance, konflik, ekspor |
| `BapService` | nomor BAP, item biaya, total, terbitkan tagihan, suspend |
| `ShiftService` | shift aktif, catatan shift |
| `ReportAnalyticsService` | utilisasi, tren, top borrower, daftar kerusakan |

### 6.2 Service yang diperluas

| Service | Perubahan |
|---|---|
| `HandoverService` | parameter opsional checklist/foto/ttd/kode bukti; tulis `handover_items`; tetap transaksi + lock |
| `InspectionService` | konteks, checklist, foto, skor; tautkan `maintenance_id`; tetap buat tiket maintenance |
| `MaintenanceService` | kode tiket, parts, progres, biaya, kondisi akhir |
| `BookingService` | `reassignItem()`; `createWalkIn()` hanya jika disetujui; `suggestAlternative()` |
| `PenaltyService` | perhitungan total dari item, status, jatuh tempo |
| `PaymentService` | verifikasi/tolak pembayaran operator/org_admin |
| `AvailabilityService` | tidak berubah; dipakai Gantt dan reassign |
| `ActivityLogger` | action code baru |

### 6.3 Aturan yang tidak boleh berubah

- Transisi status tetap mengikuti mesin status di `AGENTS.md` §5.
- Approve tetap mengecek ulang ketersediaan di dalam transaksi + `lockForUpdate`.
- Operator tetap dibatasi `operator_approval_limit` dan `max_loan_days`.
- Inspeksi `ok` → aset `available`; `needs_repair`/`damaged` → aset
  `maintenance` + tiket maintenance.
- Booking tidak otomatis mengembalikan aset ke `available`; hasil inspeksi yang
  menentukan.
- Semua aksi signifikan menulis `activity_logs`.

### 6.4 Activity action baru

```text
kiosk.scanned
kiosk.pickup
kiosk.return
queue.issued
queue.called
queue.served
booking.suggested_alternative
booking.reassigned
booking.walk_in
inspection.recorded
maintenance.part_added
maintenance.progress_updated
bap.created
bap.issued
payment.verified
payment.rejected
shift.note_added
```

---

## 7. Route dan Izin

Semua route di bawah grup `auth + active + role:...` yang sudah ada. Tidak ada
route yang dihapus atau diganti nama; route baru memakai prefix `admin.` dan
tidak menabrak nama lama.

### 7.1 Route baru

| Method | URI | Name | Role |
|---|---|---|---|
| GET | `/admin/bookings/approval` | `admin.bookings.approval` | operator, org_admin, admin_prodi |
| POST | `/admin/bookings/{booking}/suggest-alternative` | `admin.bookings.suggest` | operator, org_admin |
| POST | `/admin/bookings/{booking}/reassign` | `admin.bookings.reassign` | operator, org_admin |
| GET | `/admin/bookings/calendar/export` | `admin.bookings.calendar.export` | operator, org_admin |
| GET | `/admin/kiosk` | `admin.kiosk.index` | operator, org_admin |
| POST | `/admin/kiosk/lookup` | `admin.kiosk.lookup` | operator, org_admin |
| POST | `/admin/kiosk/process` | `admin.kiosk.process` | operator, org_admin |
| GET | `/admin/kiosk/queue` | `admin.kiosk.queue` | operator, org_admin |
| POST | `/admin/kiosk/queue/call-next` | `admin.kiosk.queue.call` | operator, org_admin |
| POST | `/admin/kiosk/queue/{ticket}/serve` | `admin.kiosk.queue.serve` | operator, org_admin |
| GET | `/admin/handovers/{booking}/receipt` | `admin.handovers.receipt` | operator, org_admin |
| GET | `/admin/assets/export` | `admin.assets.export` | operator, org_admin |
| GET | `/admin/inspections/{inspection}` | `admin.inspections.show` | operator, org_admin, technician |
| GET | `/admin/maintenances/{maintenance}` | `admin.maintenances.show` | operator, org_admin, technician |
| POST | `/admin/maintenances/{maintenance}/parts` | `admin.maintenances.parts.store` | operator, org_admin, technician |
| GET | `/admin/penalties` | `admin.penalties.index` | operator, org_admin |
| GET | `/admin/penalties/create` | `admin.penalties.create` | operator, org_admin |
| POST | `/admin/penalties` | `admin.penalties.store` | operator, org_admin |
| GET | `/admin/penalties/{penalty}` | `admin.penalties.show` | operator, org_admin |
| GET | `/admin/penalties/{penalty}/print` | `admin.penalties.print` | operator, org_admin |
| POST | `/admin/penalties/{penalty}/issue` | `admin.penalties.issue` | operator, org_admin |
| GET | `/admin/payments` | `admin.payments.index` | operator, org_admin |
| POST | `/admin/payments/{payment}/verify` | `admin.payments.verify` | operator, org_admin |
| POST | `/admin/payments/{payment}/reject` | `admin.payments.reject` | operator, org_admin |
| POST | `/admin/shifts/notes` | `admin.shifts.notes.store` | operator, org_admin |
| GET | `/admin/reports/print` | `admin.reports.print` | operator, org_admin, admin_prodi |
| GET | `/kiosk` | `kiosk.queue` | publik + token perangkat |

Route opsional (butuh persetujuan):

| Method | URI | Name | Role |
|---|---|---|---|
| GET/POST | `/admin/bookings/walk-in` | `admin.bookings.walk-in` | operator, org_admin |
| GET/POST | `/admin/penalties/{penalty}/photos` | — | operator, org_admin |

### 7.2 Matriks izin area operator

| Kapabilitas | Operator | Org Admin | Admin Prodi | Technician |
|---|---|---|---|---|
| Dashboard loket | L | L | L | - |
| Antrean approval standar | L, A | L, A | L | - |
| Otorisasi khusus | - | A | A | - |
| Kiosk scanner | C,U | L | - | - |
| Antrean mandiri (kelola) | C,U | L | - | - |
| Serah terima | C,U | L | - | - |
| Inspeksi | C,U | L | - | C,U |
| Maintenance (buat) | C | C,U,D | - | U (status) |
| BAP & denda | C,U | C,U | - | - |
| Verifikasi pembayaran | U | U | - | - |
| Aset | L | C,U,D | L | L |
| Kalender Gantt | L | L | L | - |
| Laporan | L | L | L | - |
| Pengguna, settings, kategori | - | C,U,D | L | - |
| Activity log | - | L | - | - |

Catatan: akses URL langsung di luar izin tetap 403 lewat middleware role dan
policy yang ada.

---

## 8. Rencana UI

### 8.1 Layout dan navigasi

File diubah:

- `resources/views/layouts/admin.blade.php`

Perubahan:

- Definisi menu per role dipisah; operator memakai 3 grup mockup.
- Item operator: Dashboard, Kiosk Scanner, Serah Terima, Pemeriksaan, Antrean
  Persetujuan, Berita Acara & Denda, Daftar Aset, Kalender.
- Badge antrean dari `OperatorDashboardService` (di-share lewat view composer
  atau variabel layout).
- Topbar: shift badge, pencarian, notifikasi, user menu.
- Halaman `/admin/kiosk` memakai shell admin (sidebar operator tetap tampil);
  `layouts/kiosk.blade.php` dipakai hanya oleh antrean mandiri publik `/kiosk`.

File baru:

- `resources/views/layouts/kiosk.blade.php`
- `resources/views/layouts/print.blade.php`

### 8.2 Halaman operator

| File | Aksi |
|---|---|
| `admin/dashboard.blade.php` | ditulis ulang untuk varian operator |
| `admin/bookings/approval.blade.php` | baru |
| `admin/bookings/show.blade.php` | ditulis ulang |
| `admin/bookings/calendar.blade.php` | ditulis ulang (Gantt) |
| `admin/bookings/handover.blade.php` | ditulis ulang |
| `admin/handovers/index.blade.php` | ditulis ulang (worklist pickup/return) |
| `admin/handovers/receipt.blade.php` | baru (print) |
| `admin/assets/index.blade.php` | ditulis ulang |
| `admin/inspections/index.blade.php` | ditulis ulang |
| `admin/inspections/create.blade.php` | ditulis ulang |
| `admin/inspections/show.blade.php` | baru |
| `admin/maintenances/index.blade.php` | ditulis ulang |
| `admin/maintenances/show.blade.php` | baru |
| `admin/penalties/index.blade.php` | baru |
| `admin/penalties/create.blade.php` | baru |
| `admin/penalties/show.blade.php` | baru |
| `admin/penalties/print.blade.php` | baru |
| `admin/payments/index.blade.php` | baru |
| `admin/kiosk/index.blade.php` | baru |
| `admin/kiosk/queue.blade.php` | baru |
| `admin/reports/index.blade.php` | ditulis ulang |
| `kiosk/queue.blade.php` | baru, butuh persetujuan lokasi |

### 8.3 Komponen Blade

Komponen baru di `resources/views/components/admin/`:

- `stat-tile.blade.php` — KPI dengan ikon, nilai, hint, tone.
- `segmented-tabs.blade.php` — tab Semua/Persetujuan/Pickup/Pengembalian.
- `scanner-panel.blade.php` — input scan, status sensor, shortcut.
- `checklist-group.blade.php` — checklist kondisi/kelengkapan.
- `signature-pad.blade.php` — canvas tanda tangan.
- `photo-upload.blade.php` — upload + preview + hapus.
- `gantt-chart.blade.php` — grid unit × hari.
- `queue-ticket.blade.php` — kartu tiket antrean.
- `drawer.blade.php` — panel detail maintenance/booking.
- `approval-card.blade.php` — kartu W6.
- `bap-summary.blade.php` — ringkasan biaya BAP.

Komponen lama dipakai ulang: `status-badge`, `condition-badge`, `kpi-card`,
`empty-state`, `timeline`, `modal`, `pagination`, `icon`.

### 8.4 CSS

File: `public/css/admin.css` (tidak membuat file CSS baru).

Bagian baru:

- `.ldr-operator-*` untuk shell operator.
- `.ldr-kiosk-*` untuk layar kiosk (fullscreen, font besar, kontras tinggi).
- `.ldr-gantt-*` untuk grid Gantt.
- `.ldr-approval-card`.
- `.ldr-checklist`.
- `.ldr-signature`.
- `.ldr-drawer`.
- `.ldr-queue-*`.
- `.ldr-bap-*`.
- `.ldr-print-*` + `@media print`.

Aturan: token `--ldr-*`, tanpa gradient/glassmorphism, kontras WCAG AA,
target sentuh minimal 44px untuk kiosk.

### 8.5 JavaScript

File: `public/js/admin.js` (vanilla, progressive enhancement).

Modul:

- Scanner: auto-focus, Enter submit, shortcut F1–F4, beep dengan
  `AudioContext` (opsional, fallback diam).
- Kiosk queue: polling status board tiap 10 detik, hanya saat tab aktif.
- Signature: canvas pointer events, simpan sebagai PNG data URL ke input hidden.
- Photo preview: object URL, batas jumlah/ukuran.
- Checklist: toggle "perlu catatan" memunculkan textarea.
- Gantt: scroll horizontal, tooltip bar, filter.
- Drawer: buka/tutup dengan fokus terkunci, Escape.
- Tabs: tanpa reload.
- Konfirmasi aksi kritikal (approve/reject/issue BAP) memakai modal yang ada.

Tanpa framework, tanpa bundler, tanpa file JS baru di luar `public/js/`.

---

## 9. Seeder dan Data Demo

Yang diubah:

- `database/seeders/SettingSeeder.php`: tambah
  `maintenance_monthly_budget=10000000`,
  `operator_shift_label=Pagi (08:00–16:00)`,
  `bap_subsidy_percent=10`,
  `queue_prefix=A`.
- `database/seeders/DemoTransactionSeeder.php`: tambah
  handover items + checklist, inspeksi dengan checklist, tiket maintenance +
  parts, satu BAP + penalty items + payment pending, beberapa queue ticket,
  catatan shift.

Yang baru (butuh persetujuan karena di luar daftar seeder di `AGENTS.md`):

- `database/seeders/OperatorDemoSeeder.php` bila data operator dipisah agar
  `DemoTransactionSeeder` tidak membengkak.

Aturan: seeder hanya data demo; tidak ada logika bisnis di seeder.

---

## 10. Test Matrix

### 10.1 Unit

| Test | Fokus |
|---|---|
| `KioskServiceTest` | lookup kode/token/NIM/aset; preflight menolak booking salah status; pickup/return memanggil transisi benar |
| `QueueServiceTest` | nomor urut harian, panggil berikutnya, selesai, papan monitor |
| `GanttServiceTest` | baris unit, bar reservasi, blok maintenance, deteksi konflik |
| `BapServiceTest` | nomor BAP, total item, subsidi, status, issue |
| `HandoverChecklistTest` | checklist tersimpan per item, kondisi masuk/keluar |
| `InspectionChecklistTest` | hasil rusak membuat maintenance + status aset |
| `MaintenancePartsTest` | subtotal parts, biaya total, kondisi akhir |
| `ShiftServiceTest` | shift aktif dari jam + settings, catatan tersimpan |
| `ApprovalQueueServiceTest` | urutan, konflik, alternatif, batas operator |
| `ReportAnalyticsServiceTest` | utilisasi, tren, top borrower |

### 10.2 Feature

| Test | Fokus |
|---|---|
| `OperatorDashboardTest` | KPI dan antrean hari ini tampil, aksi cepat mengarah ke route benar |
| `ApprovalQueueUiTest` | kartu konflik/otorisasi khusus, tombol sesuai role |
| `KioskTest` | lookup + proses pickup/return, activity log, role 403 |
| `QueueKioskTest` | tiket terbit, panggil, selesai, monitor |
| `HandoverTest` (perluas) | checklist/foto/ttd tersimpan, transisi tetap benar |
| `InspectionTest` (perluas) | konteks/checklist/foto, maintenance otomatis |
| `MaintenanceTest` (perluas) | tiket, parts, progres, selesai |
| `PenaltyBapTest` | buat BAP dari inspeksi, issue, suspend, print |
| `PaymentVerificationTest` | verifikasi/tolak pembayaran, status penalti berubah |
| `CalendarGanttTest` | halaman Gantt, filter, ekspor CSV/iCal |
| `OperatorShellTest` | menu operator sesuai mockup, menu terlarang tidak tampil |
| `ReportAnalyticsTest` | filter, KPI, tabel, print |
| `CrossRoleAccessTest` (perluas) | borrower/technician tidak bisa akses kiosk/BAP |

### 10.3 Regression yang wajib tetap hijau

- `tests/Feature/Borrower/*` seluruhnya.
- `tests/Feature/Admin/*` yang ada.
- `tests/Feature/Auth/*`.
- `tests/Unit/*` yang ada, termasuk `AvailabilityServiceTest` dan
  `BookingServiceTest`.

Target akhir: **seluruh suite hijau**, bukan hanya tes baru.

---

## 11. Tahapan Pengerjaan

Setiap tahap berakhir dengan verifikasi nyata dan laporan. Backend dulu, UI
kemudian, sesuai `AGENTS.md` §8.4.

### Fase 0 — Keputusan dan pengamanan

- Konfirmasi §15.
- Buat backup database lokal.
- Catat baseline test (174 passed).

### Fase 1 — Skema dan domain

- 9 migration baru.
- 5 enum baru.
- 5 model baru + relasi/casts.
- Factory untuk model baru.
- Seeder setting + demo.
- Tes unit model/enum.
- Verifikasi: `php artisan migrate`, `php artisan test`.

### Fase 2 — Backend serah terima, inspeksi, maintenance

- Perluas `HandoverService`, `InspectionService`, `MaintenanceService`.
- FormRequest baru: `StoreHandoverRequest` (perluas), `StoreInspectionRequest`
  (perluas), `StoreMaintenancePartRequest`.
- Route receipt, inspection show, maintenance show, parts.
- Tes unit + feature.
- Verifikasi: `php artisan test --filter=Handover`, `Inspection`, `Maintenance`.

### Fase 3 — Backend kiosk dan antrean

- `KioskService`, `QueueService`, `ShiftService`.
- Route kiosk + queue.
- FormRequest kiosk/queue.
- Event/activity log.
- Tes unit + feature.
- Verifikasi: `php artisan test --filter=Kiosk`, `Queue`.

### Fase 4 — Backend BAP, denda, pembayaran

- `BapService`, perluas `PenaltyService`/`PaymentService`.
- Route penalties/payments.
- FormRequest BAP + verifikasi pembayaran.
- Tes unit + feature.
- Verifikasi: `php artisan test --filter=Bap`, `Payment`.

### Fase 5 — Backend dashboard, approval, Gantt, laporan

- `OperatorDashboardService`, `ApprovalQueueService`, `GanttService`,
  `ReportAnalyticsService`.
- Route approval, calendar export, asset export, report print, reassign,
  suggest-alternative.
- Tes unit + feature.
- Verifikasi: `php artisan test --filter=Operator`, `Gantt`, `Report`.

### Fase 6 — Frontend shell + dashboard (W1)

- Layout operator, shift badge, sidebar.
- Dashboard W1.
- Komponen `stat-tile`, `segmented-tabs`.
- Verifikasi: `view:cache`, smoke test role, tes UI.

### Fase 7 — Frontend kiosk + antrean (O1/O4)

- Layout kiosk.
- Halaman scanner dan queue.
- JS scanner, signature, queue polling.
- Verifikasi: smoke test, tes UI, cek keyboard/shortcut.

### Fase 8 — Frontend serah terima + inspeksi + maintenance (W8/W9/W10)

- Tiga halaman utama + print/drawer.
- Komponen checklist/photo/signature.
- Verifikasi: tes UI + review markup.

### Fase 9 — Frontend BAP + pembayaran (O3)

- Halaman index/create/show/print + verifikasi pembayaran.
- Verifikasi: tes UI, print CSS.

### Fase 10 — Frontend approval + Gantt + aset + laporan (W6/W7/W3/O2/W12)

- Kartu approval, detail booking, Gantt, aset, laporan.
- Verifikasi: tes UI, ekspor, print.

### Fase 11 — QA dan penutupan

- Seed demo lengkap.
- Full test suite.
- Cek semua route dengan akun demo (operator, org_admin, technician,
  admin_prodi, borrower).
- Cek 403 lintas role.
- Tulis `docs/fase-operator-backend.md`, `docs/fase-operator-frontend.md`,
  dan catatan `docs/history/`.
- Perbarui `docs/rencana/menu-per-role.md`.
- Belum commit/push sampai ada instruksi.

---

## 12. Kriteria Penerimaan

### Wajib

1. Sidebar operator persis 3 grup mockup; menu di luar izin tidak tampil.
2. Dashboard menampilkan 4 KPI + antrean hari ini + jadwal + lemari + shift.
3. Approval queue berbentuk kartu, mendeteksi konflik, menampilkan batas
   otorisasi, dan menyetujui/menolak lewat Service.
4. Serah terima menyimpan checklist, foto, tanda tangan, kode bukti; status
   booking dan aset berubah benar.
5. Inspeksi menyimpan checklist/foto/konteks; hasil rusak membuat tiket
   maintenance dan mengubah status aset.
6. Maintenance punya kode tiket, parts, biaya, progres, kondisi akhir.
7. BAP bisa dibuat dari inspeksi, punya nomor, item biaya, total, cetak, dan
   tagihan; pembayaran bisa diverifikasi.
8. Kalender Gantt menampilkan unit × hari, blok maintenance, konflik, filter,
   dan ekspor CSV.
9. Kiosk scanner bisa lookup dan memproses pickup/return dengan validasi.
10. Antrean mandiri bisa menerbitkan tiket, memanggil, dan menutup antrean.
11. Daftar aset operator read-only, dengan KPI/filter/ekspor.
12. Laporan menampilkan KPI, grafik, top borrower, kerusakan, filter, ekspor.
13. Semua tes lama tetap hijau; tes baru untuk setiap service/route utama.
14. Tidak ada akses lintas role yang bocor (403).
15. Tidak ada package baru tanpa persetujuan.

### Opsional (menunggu keputusan)

- Walk-in booking.
- Reservasi manual loket.
- Alihkan unit pengganti dari Gantt.
- Eskalasi eksplisit ke Organization Admin.
- Tukar shift terjadwal.

---

## 13. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Migration baru mengubah data lama | Data rusak | Hanya tambah kolom/table nullable; `migrate` bukan `fresh`; backup dulu |
| Signature/foto memperbesar storage | Disk penuh | Batas ukuran file, kompresi sisi klien, path per booking |
| Gantt N+1 / lambat | Halaman lambat | Eager load, batasi 30 hari, cache ringkasan |
| Kiosk publik menyimpan data pribadi | Privasi | Token perangkat, retensi tiket 7 hari, tanpa data sensitif di layar monitor |
| Auto-suspend dari BAP | Borrower tidak bisa pinjam | Keputusan eksplisit + activity log + jalur banding yang sudah ada |
| Perluasan Service merusak tes lama | Regresi | Parameter opsional + default; jalankan suite tiap fase |
| Print PDF tanpa library | Hasil cetak beda | CSS `@media print` + uji manual; PDF library hanya jika disetujui |
| Shortcut keyboard bentrok | Operator salah input | Shortcut hanya aktif di halaman kiosk; input manual selalu tersedia |
| Perubahan layout admin memengaruhi role lain | UI role lain rusak | Menu per role dites di `OperatorShellTest` + smoke semua role |

---

## 14. Estimasi Urutan Kerja (kasar)

| Kelompok | Isi | Perkiraan |
|---|---|---|
| Backend inti | Fase 1–2 | 2 sesi |
| Backend kiosk/antrean | Fase 3 | 1 sesi |
| Backend BAP/pembayaran | Fase 4 | 1 sesi |
| Backend dashboard/approval/Gantt/laporan | Fase 5 | 1–2 sesi |
| Frontend dashboard + kiosk | Fase 6–7 | 2 sesi |
| Frontend operasional | Fase 8–10 | 3 sesi |
| QA + dokumentasi | Fase 11 | 1 sesi |

Estimasi ini bukan janji; fokus pada penyelesaian per fase dengan tes hijau.

---

## 15. Keputusan yang Butuh Persetujuan

1. **Walk-in booking**: apakah operator boleh membuat peminjaman atas nama
   borrower? Mockup menampilkan tombolnya. Jika ya, perlu method Service baru
   dan alur validasi sendiri. Rekomendasi: **ya, fase terakhir**, setelah alur
   inti stabil.
2. **Kiosk antrean mandiri (O4)**: apakah termasuk lingkup sekarang? Jika ya,
   perlu route publik `/kiosk`, layout baru di luar `resources/views/admin`,
   dan mekanisme token perangkat. Rekomendasi: **ya**, tetapi dikerjakan
   setelah O1.
3. **Tanda tangan digital**: canvas (PNG) atau cukup nama terketik? Rekomendasi:
   **canvas**, disimpan sebagai PNG, dengan fallback nama terketik.
4. **Auto-suspend borrower saat BAP terbit**: aktif otomatis atau perlu
   konfirmasi org_admin? Rekomendasi: **otomatis** untuk kerusakan berat,
   dengan catatan activity log dan jalur pembayaran yang sudah ada.
5. **Verifikasi pembayaran oleh operator**: operator boleh verifikasi, atau
   hanya org_admin? Rekomendasi: **operator boleh**, org_admin tetap bisa.
6. **Tukar shift**: sekadar catatan serah terima atau penjadwalan shift penuh?
   Rekomendasi: **catatan serah terima saja** untuk MVP.
7. **Eskalasi ke Organization Admin**: perlu kolom `escalated_at`/`escalated_by`
   atau cukup activity log? Rekomendasi: **kolom**, agar antrean bisa difilter.
8. **PDF**: memakai print CSS atau menambah package PDF? Rekomendasi: **print
   CSS**, tanpa package baru.
9. **Seeder baru**: boleh menambah `OperatorDemoSeeder`, atau cukup memperluas
   `DemoTransactionSeeder`? Rekomendasi: **perluas seeder yang ada** agar tetap
   dalam peta file.
10. **Lokasi view kiosk publik**: boleh membuat folder
    `resources/views/kiosk/`? Rekomendasi: **ya**, dengan layout terpisah.
11. **Dashboard route**: tetap `/admin` dengan varian operator, atau tambah
    `/admin/operator` seperti implementation guide? Rekomendasi: **tetap
    `/admin`** agar tidak ada route ganda; `/admin/operator` hanya redirect.
12. **Batas retensi tiket antrean**: 7 hari lalu dihapus otomatis? Rekomendasi:
    **ya**, lewat scheduled command kecil.

Jika tidak ada instruksi lain, saya memakai rekomendasi di atas.

---

## 16. Di Luar Lingkup (tidak dikerjakan tanpa persetujuan baru)

- Pembayaran online / payment gateway.
- Penjadwalan shift penuh dengan rotasi otomatis.
- Multi-organisasi.
- Aplikasi mobile native / REST API publik.
- Dark mode.
- Library chart/PDF/QR baru.
- Perubahan pada modul borrower di luar penyesuaian tautan notifikasi dan
  status BAP.
- Perombakan area technician/admin prodi/organization admin di luar dampak
  langsung dari perubahan Service dan layout.

---

## 17. Keputusan Final (disetujui 2026-11-09)

| # | Topik | Keputusan |
|---|---|---|
| 1 | Walk-in booking | Ya, operator boleh membuat peminjaman walk-in |
| 2 | Kiosk antrean mandiri O4 | Ya, termasuk route publik `/kiosk` |
| 3 | Tanda tangan | Canvas, disimpan sebagai PNG |
| 4 | BAP terbit | Auto-suspend borrower, wajib lewat validasi aturan dan status BAP |
| 5 | Verifikasi pembayaran | Operator boleh verifikasi |
| 6 | Tukar shift | Catatan serah terima saja |
| 7 | Eskalasi org_admin | Ya, tambah `escalated_at` dan `escalated_by` |
| 8 | PDF | Print CSS dulu, tanpa package tambahan |
| 9 | Seeder | File baru `OperatorDemoSeeder` agar tanggung jawab data terpisah |
| 10 | Folder kiosk | `resources/views/kiosk/` boleh dibuat |
| 11 | Dashboard | Tetap `/admin`, varian operator berdasarkan role |
| 12 | Retensi tiket | 7 hari, dihapus otomatis lewat scheduled cleanup |

Dampak pada rencana:

- Migration `000026_add_bap_fields_to_penalties_table` juga menambah
  `escalated_at` dan `escalated_by`.
- Route `/admin/bookings/walk-in` masuk lingkup wajib.
- Route `/kiosk` dan `resources/views/kiosk/` masuk lingkup wajib.
- `BapService::issue()` wajib memanggil aturan suspend yang sudah ada di
  `SettingService`/`PenaltyService`, bukan menulis status user langsung.
- Seeder baru: `database/seeders/OperatorDemoSeeder.php`.
- Command pembersih tiket antrean didaftarkan di `routes/console.php`.

---

## 18. Ringkasan Satu Paragraf

Perombakan operator adalah penambahan **lapisan operasional loket** di atas
transaksi yang sudah benar: data checklist/foto/ttd untuk serah terima dan
inspeksi, tiket maintenance dengan parts, BAP dengan item biaya dan verifikasi
pembayaran, kiosk scanner dan antrean mandiri, Gantt ketersediaan, serta
dashboard/approval/laporan yang mengikuti mockup. Backend dikerjakan lebih dulu
per domain dengan tes, lalu frontend per layar, dan setiap fase diverifikasi
dengan perintah nyata. Tidak ada perubahan pada mesin status, batas wewenang
operator, atau alur borrower.
