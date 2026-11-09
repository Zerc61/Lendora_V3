# Rencana Perubahan Area Borrower — Penyelarasan Penuh dengan Mockup Lendora V3

Tanggal: 2026-11-09
Status: RENCANA — belum ada kode yang diubah
Ruang lingkup: role `borrower` (M0–M10, B1–B9)
Sumber acuan:

- `public/Design/mockup/m0_onboarding` s.d. `m11_pembayaran_denda_keterlambatan`
- `public/Design/mockup/b1_beranda_portal_mahasiswa_desktop` s.d. `b9_pusat_bantuan_regulasi_sop_penyelesaian_denda_borrower_desktop`
- `public/Design/mockup/design.md`
- `public/Design/mockup/lendora_campus_gear/DESIGN.md`
- `public/Design/mockup/lendora_v3_blade_implementation_guide.md`
- `public/Design/mockup/lendora_v3_struktur_menu_resmi_per_role_rbac.md`
- `AGENTS.md`

---

## 1. Ringkasan Audit Proyek

Baseline yang diperiksa:

| Item | Jumlah / Kondisi |
|---|---|
| Framework | Laravel 12, Blade + CSS murni, tanpa Vite untuk halaman yang sudah di-restyle |
| Controller | 29 |
| Model | 13 |
| Migration | 19 (001–0015 + 3 migration tanggal) |
| View Blade | 61 |
| Service | 8 |
| Test | 38 file, **143 passed (525 assertions)** |
| Role | 5: borrower, operator, organization_admin, admin_prodi, technician |
| Git | Bersih, commit terakhir `3613289 feat(ui): align borrower and staff pages with mockups` |

Kondisi area borrower sekarang:

- Route `borrower.*` sudah ada untuk beranda, katalog, detail aset, daftar
  peminjaman, form pengajuan, detail peminjaman, notifikasi, dan profil.
- Layout borrower sudah responsif: mobile memakai appbar + bottom navigation;
  desktop memakai topbar portal.
- Belum ada route dan halaman Pusat Bantuan/Regulasi (B9).
- Belum ada halaman pembayaran denda (M11), tabel `penalties`/`payments`, dan
  alur suspend/restore akses.
- Banyak elemen mockup belum punya data/model: grade akses, bundle/komponen,
  riwayat QC, kalender ketersediaan nyata, kuota, skor kepatuhan, sertifikasi,
  pengumuman terstruktur, QR pengembalian, perpanjangan, dan dokumen PDF.
- Beberapa data masih ditampilkan sebagai placeholder atau nilai global, bukan
  dihitung dari transaksi nyata.

### 1.1 Gap utama yang ditemukan

| Area | Temuan | Dampak |
|---|---|---|
| Registrasi borrower | `RegisteredUserController` tidak mengisi `organization_id`, `nim`, `study_program`, `phone` | Borrower baru tidak melihat katalog karena query memakai organisasi `null` |
| Ketersediaan katalog | `CatalogController@show` menganggap semua 14 hari sama, hanya dari `assets.status` | Kalender ketersediaan tidak nyata; bertentangan dengan mockup B5/M5 |
| Katalog | Hanya filter `search` dan `category_id` | Filter tanggal, brand, lokasi, status, grade, dan sort belum ada |
| Kartu aset | Tidak ada jumlah unit tersedia, grade, status relatif, lokasi, dan strip ketersediaan | Tidak sesuai B2/M4 |
| Form pengajuan | Tidak ada mata kuliah, jenis kegiatan, loket, tambahan aksesori, persetujuan SOP, draft, dan cek kuota | Tidak sesuai B4/M6 |
| Detail peminjaman | Tidak ada QR, perpanjangan, PDF, catatan serah terima, metadata loket, dan aturan denda | Tidak sesuai B6/M8 |
| Notifikasi | Tidak ada kategori, pengumuman lab, aksi terkait, dan status loket | Tidak sesuai B7/M9 |
| Profil | Tidak ada angkatan, konsentrasi, DPA, skor kepatuhan, kuota, sertifikasi, 2FA, dan ringkasan sanksi | Tidak sesuai B8/M10 |
| Pusat bantuan | Belum ada route/controller/view | B9 tidak ada sama sekali |
| Denda & pembayaran | Belum ada tabel, service, route, view | M11 tidak ada sama sekali |
| Layout desktop | Belum ada footer, menu Bantuan & Regulasi, dan chip status pengguna | Tidak sesuai B1–B9 |
| Data master | Belum ada tabel courses, pickup locations, announcements, FAQs, documents, asset components, QC, dan blocks | Mockup tidak bisa diisi tanpa data baru |
| Status draft | Belum ada status `draft` | Tombol “Simpan Draft” tidak bisa berfungsi |
| Denah/status lab | Belum ada data operasional | B7/B9 hanya bisa statis atau dari setting |

---

## 2. Konflik dengan Aturan Proyek dan Keputusan yang Dibutuhkan

`AGENTS.md` §12 menyatakan beberapa hal di luar MVP. Mockup B1–B9 dan M11
meminta hal-hal tersebut. **Pemilik proyek sudah menyetujui perluasan MVP
borrower secara eksplisit** (lihat keputusan di bawah), sehingga rencana ini
membangun seluruh paritas mockup termasuk M11.

| Fitur mockup | Layar | Status aturan proyek | Keputusan final |
|---|---|---|---|
| Denda keterlambatan, klaim kerusakan, pembayaran | B1, B8, B9, M11 | Di luar MVP; tabel `payments` design-only | **Dibangun penuh.** Tabel `penalties` + `payments` + halaman M11. Tanpa gateway; pembayaran dicatat manual (VA statis, QRIS statis, sparepart, cicilan) |
| QR pengembalian | B6, M8 | Butuh generator | Token acak + QR sisi klien, tanpa dependency |
| PDF surat jalan/BAP | B1, B6, M8 | Butuh generator dokumen | Halaman cetak (`window.print()`) sebagai PDF, tanpa package baru |
| Perpanjangan pinjam +1 hari | B6, M8 | Butuh aturan baru | Tabel `booking_extensions` + persetujuan operator |
| 2FA dan OTP email | B8 | Tidak ada di MVP | 2FA TOTP murni PHP + QR; OTP email |
| Sinkronisasi SIAKAD | B4, B8 | Tidak ada integrasi | Data dari tabel `courses` dan profil; label “tersinkronisasi” informatif |
| Grade akses, QC, bundle, kalibrasi | B2, B5 | Tidak ada di schema | Tabel asset components, QC checks, documents, blocks |
| Kuota praktikum, skor kepatuhan | B1, B3, B8 | Tidak ada perhitungan | Service kuota dan skor; nilai awal dari seeder |
| Pengumuman lab terstruktur | B1, B7 | Hanya satu setting teks | Tabel `announcements` |
| Pusat bantuan, FAQ, unduhan, kalkulator denda | B9 | Belum ada | Route + tabel FAQs/documents + JS kalkulator |
| Rating aset, favorit | B5 | Belum ada | Favorit + tabel `asset_reviews` |

**Keputusan final (sudah disetujui pemilik proyek, 2026-11-09):**

1. **Paritas penuh termasuk M11.** Tabel `payments` dan `penalties` dibangun.
   Pembayaran denda dicatat sebagai transaksi manual; tidak ada gateway
   Midtrans/eksternal. Halaman M11 tampil dan berfungsi.
2. **Data master lewat seeder dulu.** CRUD admin untuk courses, loket,
   pengumuman, FAQ, dan dokumen masuk fase terpisah setelah area borrower stabil.
3. **PDF via print view, QR via token.** Tanpa dependency Composer/npm baru.
4. Semua pekerjaan mengikuti urutan backend → frontend per fase (`AGENTS.md`
   §0 dan §8.4).

---

## 3. Peta Mockup ke Layar dan Status

Legenda status: `Ada` = route/view ada; `Sebagian` = ada tetapi belum sesuai;
`Belum` = belum ada.

### 3.1 Mobile (M)

| Mockup | Layar | Route sekarang | View sekarang | Status | Target |
|---|---|---|---|---|---|
| M0 | Onboarding | `/` | `welcome.blade.php` | Ada | Selaraskan copy, badge, dan CTA |
| M1 | Login | `login` | `auth/login.blade.php` | Sebagian | Tambah footer bantuan, versi, state |
| M2 | Register | `register` | `auth/register.blade.php` | Sebagian | Tambah NIM, prodi, WhatsApp, terms, organisasi |
| M3 | Beranda | `borrower.home` | `borrower/home.blade.php` | Sebagian | Banner verifikasi, metrik, stepper, pengumuman, panduan |
| M4 | Katalog | `borrower.catalog.index` | `borrower/catalog/index.blade.php` | Sebagian | Filter, sort, kartu kaya, ketersediaan |
| M5 | Detail aset | `borrower.catalog.show` | `borrower/catalog/show.blade.php` | Sebagian | Galeri, bundle, QC, kalender 30 hari, sidebar |
| M6 | Form pengajuan | `borrower.bookings.create` | `borrower/bookings/create.blade.php` | Sebagian | Mata kuliah, jenis kegiatan, loket, add-on, SOP, draft |
| M7 | Peminjaman saya | `borrower.bookings.index` | `borrower/bookings/index.blade.php` | Sebagian | Kartu per status, sidebar metrik, aksi |
| M8 | Detail peminjaman | `borrower.bookings.show` | `borrower/bookings/show.blade.php` | Sebagian | Timeline lengkap, QR, PDF, perpanjangan |
| M9 | Notifikasi | `borrower.notifications.index` | `borrower/notifications/index.blade.php` | Sebagian | Kategori, pengumuman, aksi, sidebar |
| M10 | Profil | `borrower.profile.edit` | `borrower/profile/edit.blade.php` | Sebagian | Skor, kuota, sertifikasi, keamanan, sanksi |
| M11 | Pembayaran denda | `borrower.penalties.index`, `borrower.penalties.show` (baru) | `borrower/penalties/index|show` (baru) | Belum | Halaman penuh: BAP, rincian biaya, metode, tahapan pemulihan, unduh PDF |

### 3.2 Desktop (B)

| Mockup | Layar | Route sekarang | View sekarang | Status | Target |
|---|---|---|---|---|---|
| B1 | Beranda portal | `borrower.home` | `borrower/home.blade.php` | Sebagian | Layout desktop penuh + footer |
| B2 | Katalog | `borrower.catalog.index` | `borrower/catalog/index.blade.php` | Sebagian | Sidebar filter + kartu katalog |
| B3 | Peminjaman saya | `borrower.bookings.index` | `borrower/bookings/index.blade.php` | Sebagian | Panel peminjam + kartu transaksi |
| B4 | Form pengajuan | `borrower.bookings.create` | `borrower/bookings/create.blade.php` | Sebagian | Form 3 seksi + sidebar bantuan |
| B5 | Detail unit | `borrower.catalog.show` | `borrower/catalog/show.blade.php` | Sebagian | Tabs, bundle, QC, kalender, sidebar sticky |
| B6 | Detail booking | `borrower.bookings.show` | `borrower/bookings/show.blade.php` | Sebagian | Tracking, QR, metadata, regulasi |
| B7 | Notifikasi | `borrower.notifications.index` | `borrower/notifications/index.blade.php` | Sebagian | Dua kolom + pengumuman + sidebar |
| B8 | Profil | `borrower.profile.edit` | `borrower/profile/edit.blade.php` | Sebagian | Profil lengkap + sertifikasi + keamanan |
| B9 | Pusat bantuan | — | — | Belum | Route + view + data |

---

## 4. Perubahan Database

Aturan: migration lama tidak diedit. Semua perubahan dibuat sebagai migration
baru setelah `2026_11_08_000002_add_specifications_to_assets_table.php`.

### 4.1 Migration baru

| No | File | Isi |
|---|---|---|
| 1 | `2026_11_09_000001_add_borrower_fields_to_users_table.php` | Profil akademik, skor kepatuhan, 2FA, preferensi WhatsApp |
| 2 | `2026_11_09_000002_create_courses_table.php` | Mata kuliah dan dosen pengampu |
| 3 | `2026_11_09_000003_create_pickup_locations_table.php` | Loket pengambilan/pengembalian |
| 4 | `2026_11_09_000004_add_access_and_catalog_fields_to_assets_table.php` | Grade, akses, skor kondisi, favorit, rating, manual |
| 5 | `2026_11_09_000005_create_asset_components_table.php` | Bundle/kelengkapan fisik per unit |
| 6 | `2026_11_09_000006_create_asset_qc_checks_table.php` | Riwayat QC/kalibrasi |
| 7 | `2026_11_09_000007_create_asset_documents_table.php` | Manual, sertifikat, dokumen unit |
| 8 | `2026_11_09_000008_create_asset_blocks_table.php` | Blokir ketersediaan: QC, maintenance, tutup lab |
| 9 | `2026_11_09_000009_create_asset_favorites_table.php` | Favorit borrower |
| 10 | `2026_11_09_000010_add_booking_detail_fields_to_bookings_table.php` | Mata kuliah, kegiatan, loket, SOP, draft, token QR |
| 11 | `2026_11_09_000011_create_booking_extensions_table.php` | Pengajuan perpanjangan |
| 12 | `2026_11_09_000012_create_announcements_table.php` | Pengumuman/warta lab |
| 13 | `2026_11_09_000013_create_faqs_table.php` | FAQ pusat bantuan |
| 14 | `2026_11_09_000014_create_documents_table.php` | Dokumen unduhan pusat bantuan |
| 15 | `2026_11_09_000015_create_user_certifications_table.php` | Sertifikasi dan lisensi borrower |
| 16 | `2026_11_09_000016_create_asset_reviews_table.php` | Rating/ulasan aset |
| 17 | `2026_11_09_000017_create_penalties_table.php` | Denda/klaim kerusakan per BAP |
| 18 | `2026_11_09_000018_create_payments_table.php` | Pembayaran denda manual (M11) |

### 4.2 Kolom tabel `users`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `cohort_year` | unsignedSmallInteger | ya | — | Angkatan, untuk B8 |
| `study_concentration` | string(120) | ya | — | Konsentrasi, untuk B8 |
| `dpa_name` | string(150) | ya | — | Dosen Pembimbing Akademik |
| `dpa_nidn` | string(30) | ya | — | NIDN DPA |
| `compliance_score` | unsignedSmallInteger | tidak | 100 | Skor kepatuhan B1/B3/B8 |
| `two_factor_secret` | text | ya | — | 2FA TOTP |
| `two_factor_recovery_codes` | text | ya | — | Kode pemulihan |
| `two_factor_confirmed_at` | timestamp | ya | — | Status 2FA aktif |
| `email_otp_enabled` | boolean | tidak | false | OTP email |
| `whatsapp_opt_in` | boolean | tidak | true | Preferensi WhatsApp |

Kolom lama yang tetap dipakai: `nim`, `study_program`, `semester`, `lab_tier`,
`lab_member_id`, `academic_verified_at`, `phone`, `notification_preferences`.

### 4.3 Tabel `courses`

`id`, `organization_id` FK cascade, `code` string(30), `name` string(150),
`lecturer_name` string(150) nullable, `semester` unsignedTinyInteger nullable,
`credits` unsignedTinyInteger nullable, `is_active` boolean default true,
timestamps. Unique `organization_id + code`.

### 4.4 Tabel `pickup_locations`

`id`, `organization_id` FK cascade, `name` string(120), `address` string(255)
nullable, `operational_hours` string(120) nullable, `is_active` boolean default
true, `sort_order` unsignedSmallInteger default 0, timestamps.

### 4.5 Kolom tabel `assets`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `grade` | string(5) | ya | — | `A`, `B`, `C`, atau `-` |
| `access_level` | string(30) | tidak | `open` | `open`, `supervisor_letter`, `prodi_approval` |
| `condition_score` | unsignedTinyInteger | tidak | 100 | Skor kondisi/QC |
| `is_featured` | boolean | tidak | false | Rekomendasi/populer |
| `manual_path` | string(255) | ya | — | PDF manual unit |
| `rating_average` | decimal(3,2) | ya | — | Rating tampilan |
| `rating_count` | unsignedInteger | tidak | 0 | Jumlah ulasan |

### 4.6 Tabel `asset_components`

`id`, `asset_id` FK cascade, `name` string(150), `serial_number` string(100)
nullable, `quantity` unsignedSmallInteger default 1, `condition_status`
string(20) default `good`, `notes` string(255) nullable, `sort_order`
unsignedSmallInteger default 0, timestamps. Index `asset_id`.

### 4.7 Tabel `asset_qc_checks`

`id`, `asset_id` FK cascade, `technician_id` FK users nullOnDelete, `checked_at`
timestamp, `result` string(20), `sensor_cleanliness` string(120) nullable,
`shutter_accuracy` string(120) nullable, `firmware_version` string(50) nullable,
`notes` text nullable, `certificate_path` string(255) nullable, `next_due_at`
date nullable, timestamps. Index `asset_id + checked_at`.

### 4.8 Tabel `asset_documents`

`id`, `asset_id` FK cascade, `title` string(180), `type` string(30),
`path` string(255), `mime_type` string(100) nullable, `size` unsignedBigInteger
nullable, `is_public` boolean default true, timestamps.

### 4.9 Tabel `asset_blocks`

`id`, `organization_id` FK cascade, `asset_id` FK assets nullOnDelete (null =
blokir seluruh organisasi), `type` string(30) (`maintenance`, `calibration`,
`closure`, `practical`), `starts_at` timestamp, `ends_at` timestamp,
`reason` string(255) nullable, `created_by` FK users nullOnDelete, timestamps.
Index `asset_id + starts_at + ends_at`, `organization_id + starts_at`.

### 4.10 Tabel `asset_favorites`

`id`, `user_id` FK cascade, `asset_id` FK cascade, timestamps. Unique
`user_id + asset_id`.

### 4.11 Kolom tabel `bookings`

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `course_id` | FK courses | ya | — | Mata kuliah/kegiatan |
| `activity_type` | string(30) | ya | — | `regular`, `final_project`, `student_org`, `other` |
| `pickup_location_id` | FK pickup_locations | ya | — | Loket |
| `usage_location` | string(255) | ya | — | Lokasi penggunaan |
| `supervisor_name` | string(150) | ya | — | Dosen/pembimbing |
| `supervisor_letter_path` | string(255) | ya | — | Surat izin |
| `sop_agreed_at` | timestamp | ya | — | Persetujuan SOP |
| `submitted_at` | timestamp | ya | — | Null = draft |
| `return_token` | string(64) | ya | — | Token QR pengembalian, unique |

Status `draft` ditambahkan ke `BookingStatus`; booking draft tidak masuk antrean
operator dan tidak dihitung sebagai transaksi aktif.

### 4.12 Tabel `booking_extensions`

`id`, `booking_id` FK cascade, `requested_by` FK users restrict, `requested_days`
unsignedSmallInteger default 1, `requested_due_at` timestamp, `reason`
string(255), `status` string(20) default `pending`, `decided_by` FK users
nullOnDelete, `decided_at` timestamp nullable, `rejection_reason` string(255)
nullable, timestamps. Index `booking_id + status`.

### 4.13 Tabel `announcements`

`id`, `organization_id` FK cascade, `title` string(180), `slug` string(200),
`category` string(30), `excerpt` string(255) nullable, `body` text,
`publisher_name` string(150) nullable, `published_at` timestamp,
`is_pinned` boolean default false, `attachment_path` string(255) nullable,
`cta_label` string(80) nullable, `cta_url` string(255) nullable, timestamps,
softDeletes. Index `organization_id + published_at`.

### 4.14 Tabel `faqs`

`id`, `organization_id` FK cascade, `category` string(30) nullable, `question`
string(255), `answer` text, `sort_order` unsignedSmallInteger default 0,
`is_active` boolean default true, timestamps.

### 4.15 Tabel `documents`

`id`, `organization_id` FK cascade, `title` string(180), `type` string(30),
`path` string(255), `mime_type` string(100) nullable, `size` unsignedBigInteger
nullable, `is_public` boolean default true, `sort_order` unsignedSmallInteger
default 0, timestamps.

### 4.16 Tabel `user_certifications`

`id`, `user_id` FK cascade, `title` string(180), `issuer` string(150),
`certificate_number` string(100) nullable, `issued_at` date nullable,
`expires_at` date nullable, `file_path` string(255) nullable, `status`
string(20) default `valid`, timestamps.

### 4.17 Tabel `penalties`

Denda/klaim kerusakan yang lahir dari inspeksi (BAP resmi). Satu baris = satu
BAP untuk satu booking/aset.

`id`, `organization_id` FK cascade, `booking_id` FK bookings nullOnDelete,
`asset_id` FK assets nullOnDelete, `borrower_id` FK users restrict,
`bap_number` string(40) unique (`LDR-BAP-2026-0004`), `incident_at` timestamp,
`inspector_name` string(150), `damage_description` string(255),
`repair_subtotal` unsignedInteger, `subsidy_percent` unsignedTinyInteger
default 10, `subsidy_amount` unsignedInteger default 0,
`admin_fee` unsignedInteger default 0, `total_amount` unsignedInteger,
`status` string(20) default `waiting_payment` (`waiting_payment`, `partial`,
`paid`, `waived`, `overdue`), `due_at` timestamp, `paid_at` timestamp nullable,
`recovery_step` unsignedTinyInteger default 1, `notes` text nullable,
`created_by` FK users nullOnDelete, timestamps. Index
`borrower_id + status`, `booking_id`, `asset_id`.

Akibat sanksi: `users.status` menjadi `suspended` selama
`penalties.status != paid|waived`. `EnsureActiveStatus` memblokir area borrower;
halaman M11 tetap dapat diakses lewat middleware khusus (`auth` saja) agar
borrower bisa melunasi.

### 4.18 Tabel `payments`

Pembayaran manual tanpa gateway (M11). Satu baris = satu upaya pembayaran.

`id`, `penalty_id` FK penalties cascade, `borrower_id` FK users restrict,
`method` string(30) (`va_bni`, `qris`, `sparepart`, `installment`),
`amount` unsignedInteger, `status` string(20) default `pending`
(`pending`, `waiting_verification`, `verified`, `rejected`),
`reference_number` string(60) nullable (nomor VA / referensi QRIS / invoice
sparepart), `proof_path` string(255) nullable, `paid_at` timestamp nullable,
`verified_at` timestamp nullable, `verified_by` FK users nullOnDelete,
`rejection_reason` string(255) nullable, `notes` text nullable, timestamps.
Index `penalty_id + status`, `borrower_id + created_at`.

Nomor VA mahasiswa dibentuk dari NIM: `988-0012-{nim}` (tanpa spasi), disimpan
saat pembayaran `va_bni` dibuat. QRIS memakai barcode statis loket keuangan
kampus dan hanya menampilkan instruksi + unggah bukti.

### 4.19 Setting baru

Tambahan di `settings` (via `SettingSeeder`, bukan migration):

| Key | Default | Dipakai |
|---|---|---|
| `semester_name` | `Ganjil 2026/2027` | B1/B3/B4/B8 |
| `semester_start` | `2026-08-01` | Hitung kuota semester |
| `semester_end` | `2026-12-31` | Hitung kuota semester |
| `semester_quota_hours` | `25` | Kuota praktikum |
| `max_parallel_units` | `3` | Batas unit aktif |
| `max_addon_units` | `3` | Batas aksesori |
| `late_fee_per_hour` | `10000` | Denda keterlambatan |
| `late_grace_minutes` | `15` | Toleransi |
| `compliance_penalty_per_hour` | `5` | Penalti skor |
| `damage_subsidy_percent` | `10` | Subsidi prodi |
| `pickup_open_time` | `08:30` | Jam ambil |
| `return_close_time` | `16:00` | Jam kembali |
| `operator_contact_phone` | `Ext. 402` | B6/B9 |
| `operator_contact_whatsapp` | `+62 812-3456-7890` | B6/B7/B9 |
| `operational_hours` | `Senin–Jumat 08:00–17:00 WIB` | Footer/B7/B9 |
| `lab_status` | `open` | B7/B9 |
| `queue_average_minutes` | `2` | Estimasi antrean |
| `sop_version` | `V2.4` | B1/B9 |
| `bap_prefix` | `LDR-BAP` | Nomor BAP M11 |
| `va_bank_name` | `BNI Kampus` | M11 |
| `va_prefix` | `988-0012` | Nomor VA M11 |
| `finance_counter_name` | `Loket Keuangan Kampus` | M11 |
| `penalty_due_days` | `4` | Batas bayar M11 |
| `installment_max` | `3` | Cicilan M11 |
| `suspension_on_penalty` | `1` | Suspend akun saat denda belum lunas |
| `recovery_step_2_title` | `Workshop SOP Lab Outdoor (1 Hari)` | M11 |
| `recovery_step_3_title` | `Otorisasi Pemulihan oleh Kaprodi` | M11 |

---

## 5. Model, Enum, dan Relasi

### 5.1 Model baru

| Model | Tabel | Relasi utama |
|---|---|---|
| `Course` | `courses` | belongsTo Organization, hasMany Booking |
| `PickupLocation` | `pickup_locations` | belongsTo Organization, hasMany Booking |
| `AssetComponent` | `asset_components` | belongsTo Asset |
| `AssetQcCheck` | `asset_qc_checks` | belongsTo Asset, Technician (User) |
| `AssetDocument` | `asset_documents` | belongsTo Asset |
| `AssetBlock` | `asset_blocks` | belongsTo Organization, Asset |
| `AssetFavorite` | `asset_favorites` | belongsTo User, Asset |
| `BookingExtension` | `booking_extensions` | belongsTo Booking, requester, decider |
| `Announcement` | `announcements` | belongsTo Organization |
| `Faq` | `faqs` | belongsTo Organization |
| `Document` | `documents` | belongsTo Organization |
| `UserCertification` | `user_certifications` | belongsTo User |
| `AssetReview` | `asset_reviews` | belongsTo Asset, BookingItem, Borrower |
| `Penalty` | `penalties` | belongsTo Organization, Booking, Asset, Borrower; hasMany Payments |
| `Payment` | `payments` | belongsTo Penalty, Borrower, Verifier (User) |

### 5.2 Model yang diubah

| Model | Perubahan |
|---|---|
| `User` | `$fillable`, cast `two_factor_confirmed_at`, relasi favorites, certifications, reviews, penalties, payments; scope borrower |
| `Asset` | `$fillable`, cast `grade`, `access_level`, `condition_score`, relasi components, qcChecks, documents, blocks, favorites, reviews, penalties; scope featured/available |
| `Booking` | `$fillable`, cast `activity_type`, `sop_agreed_at`, `submitted_at`; relasi course, pickupLocation, extensions, penalties; scope excluding drafts |
| `BookingItem` | relasi review; helper checklist komponen |
| `Organization` | relasi courses, pickupLocations, announcements, faqs, documents, blocks |
| `Setting` | tidak berubah struktural; tambah key di seeder |

### 5.3 Enum baru

| Enum | Nilai |
|---|---|
| `AssetGrade` | `A`, `B`, `C` |
| `AssetAccessLevel` | `open`, `supervisor_letter`, `prodi_approval` |
| `BookingActivityType` | `regular`, `final_project`, `student_org`, `other` |
| `BookingExtensionStatus` | `pending`, `approved`, `rejected`, `cancelled` |
| `AnnouncementCategory` | `operational`, `maintenance`, `policy`, `procurement` |
| `AssetBlockType` | `maintenance`, `calibration`, `closure`, `practical` |
| `DocumentType` | `sop`, `form`, `manual`, `certificate`, `other` |
| `CertificationStatus` | `valid`, `expired`, `revoked` |

`BookingStatus` ditambah `Draft` dengan transisi:
`draft → pending`, `draft → cancelled`.

---

## 6. Service dan Aturan Bisnis

### 6.1 `AvailabilityService`

Tambahan method:

- `calendarFor(Asset $asset, CarbonInterface $start, int $days): array`
  Mengembalikan status per hari: `available`, `booked`, `maintenance`,
  `qc`, `closed`, `practical`.
- `availabilityMapFor(Collection $assets, CarbonInterface $start, int $days): array`
  Versi batch untuk katalog agar tidak N+1.
- `isAvailableForRange()` tetap dipakai, ditambah pengecekan
  `asset_blocks`.
- `conflictPayloadFor(iterable $assets, $start, $end): ?array`
  Mengembalikan unit, kode booking konflik, rentang, dan saran rentang bebas
  untuk banner B4/M6.
- `suggestFreeRange()` dipertahankan dan dipakai form.

Aturan:

- Booking `approved`/`active` menempati tanggal.
- `asset_blocks` tipe `maintenance`, `calibration`, `closure` menutup tanggal.
- `practical` hanya label tampilan, bukan konflik booking.
- Tanggal sentuh (`end == start`) bukan konflik.

### 6.2 `CatalogService`

Method `index(User $user, array $filters): array`:

- Query aset organisasi, kecuali `maintenance`/`retired` untuk katalog default.
- Filter: `search`, `category_id` (termasuk subkategori), `start_date`,
  `end_date`, `availability`, `brand`, `location`, `access_level`, `grade`,
  `sort`.
- Sort: `availability` (default), `name`, `popular`, `newest`.
- Facet: jumlah per kategori, brand, lokasi, status, grade.
- `availabilityByAsset` untuk strip 14 hari.
- Hitung `available_units` per model/nama.

### 6.3 `BookingService`

Perubahan:

- `createDraft()` dan `updateDraft()`: validasi ringan, status `draft`.
- `submitDraft()`: validasi penuh, status `draft → pending`, isi `submitted_at`.
- `createRequest()`: tetap ada untuk submit langsung.
- Validasi baru:
  - `course_id` milik organisasi.
  - `pickup_location_id` aktif.
  - `activity_type` valid.
  - `sop_agreed_at` wajib.
  - `max_addon_units`.
  - `max_parallel_units`.
  - `semester_quota_hours`.
  - grade/akses: `supervisor_letter` wajib unggah surat;
    `prodi_approval` masuk antrean khusus.
- `timelineFor()` diperkaya aktor dan waktu dari handover, inspeksi, dan
  extension; tetap tanpa tabel event baru.
- `requestExtension()`, `approveExtension()`, `rejectExtension()`.
- `generateReturnToken()` saat pickup/approval.
- `lateHours()` dan `estimatedPenalty()` untuk B1/B6/B8/B9.
- `assertTransition()` ditambah `draft`.

### 6.4 `BorrowerScoreService`

- `summary(User $user): array` berisi skor, tier, tepat waktu, kerusakan,
  pelanggaran, tren.
- Skor awal dari `users.compliance_score`.
- Pengurangan saat keterlambatan/kerusakan; penambahan saat pengembalian tepat
  waktu.
- Nilai ditulis kembali ke `compliance_score` oleh `InspectionService` dan
  `HandoverService`.

### 6.5 `QuotaService`

- `summary(User $user): array` berisi total, terpakai, sisa, persentase.
- Sumber pemakaian: booking `approved`/`active`/`returned` pada rentang
  semester.
- `assertCanBorrow(User $user, array $assetIds, $start, $end)`.

### 6.6 `AnnouncementService`

- `forBorrower(User $user)` mengambil pengumuman terbit, urut pinned lalu
  tanggal.
- Fallback ke setting `borrower_announcement` jika tabel kosong.

### 6.7 `TotpService`

- Generate secret, verifikasi kode 6 digit, recovery codes.
- Tidak memakai package; implementasi RFC 6238 dengan `hash_hmac`.
- QR otpauth dirender di klien.

### 6.8 `PenaltyService`

- `createFromInspection(Inspection $inspection): Penalty` — membentuk BAP
  (`LDR-BAP-YYYY-NNNN`), menghitung subtotal perbaikan, subsidi, biaya admin,
  total, dan jatuh tempo (`penalty_due_days`).
- `lateHours()`, `estimatedLateFee()` dari `late_fee_per_hour` dan
  `late_grace_minutes`.
- `summaryFor(User $user)`: daftar denda aktif, total, status, sisa hari.
- `applySuspension(Penalty $penalty)`: set `users.status = suspended` bila
  `suspension_on_penalty = 1` dan total belum lunas.
- `restoreAccess(Penalty $penalty)`: aktifkan kembali akun saat lunas/diwaive
  dan tidak ada denda lain yang terbuka.
- Tidak memproses pembayaran; itu tugas `PaymentService`.

### 6.9 `PaymentService`

- `start(Penalty $penalty, string $method): Payment` — membuat baris
  `payments` status `pending`; untuk `va_bni` membentuk nomor VA dari NIM.
- `submitProof(Payment $payment, UploadedFile $proof): Payment` — simpan bukti,
  status `waiting_verification`.
- `verify(Payment $payment, User $operator): Payment` — status `verified`,
  `paid_at`, `verified_by`, lalu panggil `PenaltyService::restoreAccess()` bila
  akumulasi pembayaran menutup total.
- `reject(Payment $payment, User $operator, string $reason): Payment`.
- `summaryFor(Penalty $penalty)`: total dibayar, sisa, status.
- Semua perubahan status ditulis ke `activity_logs`.

---

## 7. Request dan Validasi

### 7.1 Request baru

| File | Kegunaan |
|---|---|
| `SaveBookingDraftRequest.php` | Simpan/ubah draft |
| `SubmitBookingRequest.php` | Submit draft |
| `RequestBookingExtensionRequest.php` | Ajukan perpanjangan |
| `StoreBorrowerCertificationRequest.php` | Unggah sertifikat |
| `StoreAnnouncementRequest.php` | Admin pengumuman |
| `UpdateAnnouncementRequest.php` | Admin pengumuman |
| `StoreCourseRequest.php` | Admin mata kuliah |
| `UpdateCourseRequest.php` | Admin mata kuliah |
| `StorePickupLocationRequest.php` | Admin loket |
| `UpdatePickupLocationRequest.php` | Admin loket |
| `StoreFaqRequest.php` | Admin FAQ |
| `UpdateFaqRequest.php` | Admin FAQ |
| `StoreDocumentRequest.php` | Admin dokumen |
| `UpdateDocumentRequest.php` | Admin dokumen |
| `StorePenaltyPaymentRequest.php` | Mulai pembayaran denda (metode) |
| `SubmitPaymentProofRequest.php` | Unggah bukti bayar |
| `RequestInstallmentRequest.php` | Ajukan keringanan/cicilan |
| `VerifyPaymentRequest.php` | Admin verifikasi pembayaran |
| `RejectPaymentRequest.php` | Admin tolak pembayaran |

### 7.2 Request yang diubah

| File | Perubahan |
|---|---|
| `StoreBookingRequest.php` | Field baru + validasi kuota/grade/SOP/add-on |
| `UpdateBorrowerProfileRequest.php` | WhatsApp, preferensi, validasi sertifikat |
| `RegisteredUserController` (inline) | NIM, prodi, WhatsApp, terms, organisasi default |

### 7.3 Aturan validasi penting

- `requested_end_date >= requested_start_date`.
- Durasi <= `max_loan_days`.
- Jumlah unit + add-on <= `max_parallel_units`/`max_addon_units`.
- Tidak boleh bentrok dengan booking `approved`/`active`.
- Tidak boleh memilih aset yang `maintenance`, `retired`, atau diblokir.
- Aset `prodi_approval` wajib memilih `activity_type` yang sesuai.
- Surat pembimbing wajib untuk `access_level = supervisor_letter`.
- Pembayaran: `method` harus salah satu enum; `amount > 0`; total pembayaran
  tidak boleh melebihi sisa kewajiban; bukti wajib `image/*` atau `application/pdf`
  maks 5 MB; `installment` hanya boleh jika `installment_max > 0` dan belum ada
  pengajuan aktif.

---

## 8. Controller dan Route

### 8.1 Controller borrower

| Controller | Method | Perubahan |
|---|---|---|
| `Borrower\HomeController` | `__invoke` | Tambah `BorrowerScoreService`, `QuotaService`, `AnnouncementService`, `AvailabilityService` |
| `Borrower\CatalogController` | `index`, `show` | Filter baru, facet, availability map, favorite, add-on kompatibel |
| `Borrower\BookingController` | `index`, `create`, `store`, `show`, `cancel` | Draft, submit, extension, token, document |
| `Borrower\BookingExtensionController` | `store`, `cancel` | Baru |
| `Borrower\DocumentController` | `letter`, `bap` | View cetak, tanpa package PDF |
| `Borrower\NotificationController` | `index`, `markRead`, `markAllRead` | Filter kategori + pengumuman |
| `Borrower\ProfileController` | `edit`, `update` | Data lengkap, sertifikasi, 2FA, sanksi |
| `Borrower\FavoriteController` | `toggle` | Baru |
| `Borrower\HelpController` | `__invoke` | Baru |
| `Borrower\PenaltyController` | `index`, `show`, `document` | Baru — halaman M11 + BAP cetak |
| `Borrower\PaymentController` | `store`, `proof`, `installment` | Baru — pembayaran manual |
| `Borrower\TwoFactorController` | `enable`, `confirm`, `disable` | Baru |

### 8.2 Route borrower baru

```php
Route::get('help', BorrowerHelpController::class)->name('help');

Route::post('catalog/{asset}/favorite', [BorrowerFavoriteController::class, 'toggle'])
    ->name('catalog.favorite');

Route::post('bookings/draft', [BorrowerBookingController::class, 'saveDraft'])
    ->name('bookings.draft.store');
Route::put('bookings/{booking}/draft', [BorrowerBookingController::class, 'updateDraft'])
    ->name('bookings.draft.update');
Route::post('bookings/{booking}/submit', [BorrowerBookingController::class, 'submitDraft'])
    ->name('bookings.submit');

Route::post('bookings/{booking}/extensions', [BorrowerBookingExtensionController::class, 'store'])
    ->name('bookings.extensions.store');
Route::post('bookings/{booking}/extensions/{extension}/cancel', [BorrowerBookingExtensionController::class, 'cancel'])
    ->name('bookings.extensions.cancel');

Route::get('bookings/{booking}/document/{type}', [BorrowerDocumentController::class, 'show'])
    ->whereIn('type', ['letter', 'bap'])
    ->name('bookings.document');

Route::get('bookings/{booking}/return-pass', [BorrowerBookingController::class, 'returnPass'])
    ->name('bookings.return-pass');

Route::post('profile/certifications', [BorrowerProfileController::class, 'storeCertification'])
    ->name('profile.certifications.store');
Route::delete('profile/certifications/{certification}', [BorrowerProfileController::class, 'destroyCertification'])
    ->name('profile.certifications.destroy');

Route::post('profile/2fa/enable', [BorrowerTwoFactorController::class, 'enable'])
    ->name('profile.2fa.enable');
Route::post('profile/2fa/confirm', [BorrowerTwoFactorController::class, 'confirm'])
    ->name('profile.2fa.confirm');
Route::post('profile/2fa/disable', [BorrowerTwoFactorController::class, 'disable'])
    ->name('profile.2fa.disable');

// Denda & pembayaran (M11) — tetap dapat diakses saat akun suspended.
Route::get('penalties', [BorrowerPenaltyController::class, 'index'])
    ->name('penalties.index');
Route::get('penalties/{penalty}', [BorrowerPenaltyController::class, 'show'])
    ->name('penalties.show');
Route::get('penalties/{penalty}/document/{type}', [BorrowerPenaltyController::class, 'document'])
    ->whereIn('type', ['bap', 'receipt'])
    ->name('penalties.document');

Route::post('penalties/{penalty}/payments', [BorrowerPaymentController::class, 'store'])
    ->name('penalties.payments.store');
Route::post('penalties/{penalty}/installment', [BorrowerPaymentController::class, 'installment'])
    ->name('penalties.installment');
Route::post('payments/{payment}/proof', [BorrowerPaymentController::class, 'proof'])
    ->name('payments.proof');
```

**Urutan middleware penting:** grup borrower biasa memakai
`auth + active + role:borrower`. Route `borrower.penalties.*` dan
`borrower.payments.*` diletakkan di grup `auth + role:borrower` **tanpa**
`active`, supaya akun `suspended` tetap bisa membayar denda dan memulihkan
akses. Semua route lain tetap diblokir `EnsureActiveStatus`.

### 8.3 Route admin pendukung data

Hanya jika CRUD admin disetujui:

```php
Route::resource('announcements', AdminAnnouncementController::class)->except('show');
Route::resource('courses', AdminCourseController::class)->except('show');
Route::resource('pickup-locations', AdminPickupLocationController::class)->except('show');
```

Verifikasi pembayaran denda (operator / organization_admin):

```php
Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verify'])
    ->name('payments.verify');
Route::post('payments/{payment}/reject', [AdminPaymentController::class, 'reject'])
    ->name('payments.reject');
Route::get('penalties', [AdminPenaltyController::class, 'index'])->name('penalties.index');
```

Asset form admin (`W4`) perlu tambahan tab/section:

- Komponen/bundle.
- QC checks.
- Dokumen/manual.
- Grade dan access level.
- Skor kondisi.

### 8.4 Middleware dan policy

- Route borrower biasa tetap `auth`, `active`, `role:borrower`.
- Route denda/pembayaran: `auth`, `role:borrower` (tanpa `active`) agar akun
  suspended bisa melunasi.
- `BookingPolicy`:
  - `view` hanya pemilik.
  - `updateDraft`/`submitDraft` hanya pemilik dan status draft.
  - `cancel` hanya pemilik dan status draft/pending.
  - `requestExtension` hanya pemilik dan status active.
- `BookingExtensionPolicy` baru.
- `FavoritePolicy`/scope pemilik.
- `PenaltyPolicy`: `view`/`pay` hanya pemilik; `manage` untuk operator dan
  organization_admin.
- `PaymentPolicy`: `view` hanya pemilik atau staf; `verify`/`reject` hanya
  operator dan organization_admin.
- `AnnouncementPolicy`/`FaqPolicy`/`DocumentPolicy` untuk admin.

---

## 9. View, Komponen, dan View Contract

### 9.1 Layout

File: `resources/views/layouts/borrower.blade.php`

Yang diubah:

- Header desktop:
  - Logo + chip `Portal Mahasiswa`.
  - Menu: `Beranda`, `Katalog Peralatan`, `Peminjaman Saya`, `Notifikasi`,
    `Bantuan & Regulasi`.
  - Search box dengan placeholder `Cari kamera, audio...` dan shortcut `⌘K`
    (visual).
  - CTA `Ajukan Peminjaman`.
  - Bell dengan dot unread.
  - Chip pengguna: nama, badge kepatuhan, NIM/prodi, avatar.
- Mobile:
  - Appbar ringkas.
  - Bottom nav 5 tab: Beranda, Katalog, Peminjaman, Notifikasi, Profil.
  - Badge jumlah peminjaman aktif pada tab Peminjaman.
- Footer desktop 4 kolom:
  - Brand + deskripsi.
  - Jam operasional.
  - Afiliasi kampus.
  - Kontak & bantuan.
- `View::composer` di `AppServiceProvider` menambahkan:
  `$unreadNotifications`, `$activeBookingCount`, `$borrowerCompliance`.
- Tidak ada query di Blade.
- `aria-current`, skip link, dan fokus tetap.

### 9.2 Komponen baru

| File | Fungsi |
|---|---|
| `components/borrower/hero-status.blade.php` | Banner sapaan + status verifikasi |
| `components/borrower/stat-strip.blade.php` | Metrik unit, denda, kuota |
| `components/borrower/active-loan-card.blade.php` | Kartu peminjaman aktif + stepper |
| `components/borrower/booking-stepper.blade.php` | Stepper 5 langkah, compact/full |
| `components/borrower/announcement-list.blade.php` | Daftar warta lab |
| `components/borrower/quick-guide.blade.php` | Panduan 4 langkah |
| `components/borrower/catalog-filter-panel.blade.php` | Sidebar filter katalog |
| `components/borrower/asset-availability-strip.blade.php` | Strip 14 hari |
| `components/borrower/availability-calendar.blade.php` | Kalender 30 hari |
| `components/borrower/asset-bundle-list.blade.php` | Checklist kelengkapan |
| `components/borrower/asset-qc-card.blade.php` | Ringkasan QC |
| `components/borrower/booking-card.blade.php` | Kartu booking per status |
| `components/borrower/booking-timeline.blade.php` | Timeline lengkap |
| `components/borrower/booking-actions.blade.php` | Aksi kontekstual |
| `components/borrower/qr-pass.blade.php` | QR + token pengembalian |
| `components/borrower/extension-modal.blade.php` | Modal perpanjangan |
| `components/borrower/compliance-card.blade.php` | Skor dan tier |
| `components/borrower/quota-card.blade.php` | Kuota semester |
| `components/borrower/sanction-card.blade.php` | Ringkasan denda/klaim |
| `components/borrower/notification-item.blade.php` | Baris notifikasi kaya |
| `components/borrower/help-faq-accordion.blade.php` | FAQ |
| `components/borrower/penalty-calculator.blade.php` | Kalkulator denda |
| `components/borrower/bap-summary.blade.php` | Ringkasan BAP M11 |
| `components/borrower/penalty-breakdown.blade.php` | Rincian biaya + subsidi |
| `components/borrower/payment-method-list.blade.php` | Pilihan metode bayar |
| `components/borrower/recovery-steps.blade.php` | Tahapan pemulihan akses |
| `components/borrower/payment-upload.blade.php` | Form unggah bukti bayar |
| `components/borrower/footer.blade.php` | Footer portal |
| `components/ui/breadcrumbs.blade.php` | Breadcrumb |
| `components/ui/select.blade.php` | Select konsisten |
| `components/ui/textarea.blade.php` | Textarea konsisten |

### 9.3 Komponen yang diubah

| File | Perubahan |
|---|---|
| `components/asset-card.blade.php` | Tambah grade, jumlah tersedia, lokasi, strip ketersediaan, aksi |
| `components/status-badge.blade.php` | Tambah label `draft`, grade, akses |
| `components/timeline.blade.php` | Dukung aktor/waktu lengkap dan state extension |
| `components/icon.blade.php` | Tambah ikon Lucide yang dipakai mockup |
| `components/empty-state.blade.php` | Dukung CTA ganda |
| `components/modal.blade.php` | Dukung ukuran dan footer aksi |
| `components/confirm-modal.blade.php` | Dukung varian danger/primary |

### 9.4 View Contract per layar

| View | Route | Variabel |
|---|---|---|
| `borrower/home.blade.php` | `borrower.home` | `user`, `activeBooking`, `dueInfo`, `summary`, `availableAssets`, `recommendations`, `announcements`, `quota`, `compliance`, `sanctions`, `quickGuide` |
| `borrower/catalog/index.blade.php` | `borrower.catalog.index` | `assets`, `categories`, `filters`, `facets`, `statusCounts`, `availabilityByAsset`, `activeFilters` |
| `borrower/catalog/show.blade.php` | `borrower.catalog.show` | `asset`, `availabilityDays`, `isAvailable`, `availabilityReason`, `favorite`, `compatibleAddons`, `rating` |
| `borrower/bookings/index.blade.php` | `borrower.bookings.index` | `bookings`, `tab`, `counts`, `activeBooking`, `quota`, `compliance`, `penaltyRules` |
| `borrower/bookings/create.blade.php` | `borrower.bookings.create/store` | `assets`, `addons`, `maxLoanDays`, `maxAddonUnits`, `courses`, `pickupLocations`, `quota`, `conflict`, `suggestedRange`, `sopVersion` |
| `borrower/bookings/show.blade.php` | `borrower.bookings.show` | `booking`, `timeline`, `returnToken`, `canExtend`, `penaltyRules`, `operatorContact`, `documents` |
| `borrower/notifications/index.blade.php` | `borrower.notifications.index` | `groups`, `unreadCount`, `category`, `announcements`, `categories`, `compliance`, `operatorInfo` |
| `borrower/profile/edit.blade.php` | `borrower.profile.edit` | `user`, `counts`, `activeBooking`, `compliance`, `quota`, `sanctions`, `notificationTypes`, `twoFactorEnabled`, `certifications` |
| `borrower/help/index.blade.php` | `borrower.help` | `faqs`, `documents`, `sopRules`, `penaltySettings`, `organization`, `operatorContact`, `operationalStatus` |
| `borrower/penalties/index.blade.php` | `borrower.penalties.index` | `penalties`, `summary`, `compliance`, `operatorContact` |
| `borrower/penalties/show.blade.php` | `borrower.penalties.show` | `penalty`, `breakdown`, `payments`, `paymentSummary`, `methods`, `vaNumber`, `recoverySteps`, `paymentSettings`, `canPay` |
| `borrower/penalties/document.blade.php` | `borrower.penalties.document` | `penalty`, `booking`, `asset`, `breakdown`, `type` |
| `auth/register.blade.php` | `register` | `studyPrograms` (dari config/tabel), `organizations` (opsional) |

---

## 10. Rincian Perubahan per Layar

### 10.1 B1 / M3 — Beranda

Tambahan:

- Banner status akun: badge `Akun Aktif & Terverifikasi`, nama, prodi,
  NIM, status lab, skor kepatuhan.
- Strip metrik: unit dipinjam, denda, sisa kuota.
- Kartu peminjaman aktif:
  - Kode booking, status, badge peringatan H-1.
  - Foto, kategori, nama, kode aset, serial, jadwal, loket, kondisi.
  - Stepper 5 langkah dengan waktu/aktor.
  - Aksi: Lihat Detail, Surat Jalan (print), Minta Ekstensi.
- Seksi “Peralatan Populer & Siap Pinjam Hari Ini”:
  - 4 kartu aset dengan grade, jumlah unit, kode, deskripsi, CTA.
- Seksi “Warta Operasional Lab”:
  - Daftar pengumuman dari tabel `announcements`.
  - Kategori, penerbit, tanggal, CTA.
- Seksi “Panduan Cepat & Regulasi”:
  - 4 langkah statis + tautan B9.
- Empty state jika belum ada peminjaman aktif.
- Data harus dari service, bukan query di view.

### 10.2 B2 / M4 — Katalog

Tambahan:

- Header: `Inventaris Terbuka`, judul, subjudul, jumlah siap pakai/sedang
  dipinjam.
- Search + filter tanggal (rentang) + durasi.
- Sort: ketersediaan, abjad, terpopuler.
- Sidebar filter desktop / drawer mobile:
  - Kategori dengan jumlah.
  - Ketersediaan: siap sekarang, terjadwal minggu depan.
  - Grade akses: bebas, izin khusus kaprodi.
  - Merek.
  - Lokasi penyimpanan.
  - Info pengambilan.
- Chip filter aktif + tombol reset.
- Kartu aset:
  - Kategori, status, jumlah unit tersedia.
  - Grade/akses.
  - Kode, nama, deskripsi.
  - Strip ketersediaan 14 hari / label `100% Ready`, `Tersedia 16 Nov`.
  - Lokasi.
  - Aksi `Ajukan Peminjaman` atau `Booking Jadwal Mendatang`.
- Pagination dengan info `Menampilkan 1–6 dari 48`.
- Empty state per filter.
- Mobile tetap grid 2 kolom, desktop 3–4 kolom.

### 10.3 B5 / M5 — Detail Aset

Tambahan:

- Breadcrumb: Beranda / Katalog / Kategori / Nama.
- Galeri foto + thumbnail + indikator.
- Badge unit terverifikasi + serial.
- Tabs/section:
  - Spesifikasi.
  - Bundle/kelengkapan.
  - Riwayat QC.
  - Regulasi grade.
- Spesifikasi dinamis + highlight fitur.
- Bundle checklist dengan serial, kondisi, jumlah.
- Kartu QC: skor, kebersihan sensor, akurasi, firmware, teknisi, tanggal,
  sertifikat.
- Persyaratan grade A: otorisasi dosen/kaprodi, skor minimum, tanggung jawab.
- Kalender 30 hari:
  - `Tersedia`, `Praktikum/Sewa`, `QC`, `Tutup`.
  - Slot terpilih.
- Sidebar sticky:
  - Kode, lokasi, nama, grade, kategori, rating, sesi, utilisasi.
  - Lokasi penyimpanan.
  - Rentang jadwal + jam ambil/kembali.
  - Add-on lensa kompatibel.
  - Aksi: Ajukan, Tanya Operator, Simpan Favorit.
- Syarat pengambilan + unduh manual.
- CTA nonaktif dengan alasan jika unit tidak tersedia.
- Share link + copy feedback (JS).

### 10.4 B4 / M6 — Form Pengajuan

Tambahan:

- Breadcrumb.
- Status peminjam: kuota, jumlah unit dipilih, badge verifikasi.
- Kartu unit terpilih + tombol `Ganti Unit`.
- Aksesori pendukung opsional (maks 3), status siap.
- Seksi jadwal:
  - Tanggal mulai/selesai.
  - Durasi otomatis.
  - Batas maksimal.
  - Cek kuota.
  - Pilih loket.
  - Info KTM.
- Seksi akademik:
  - Mata kuliah/kegiatan (select dari `courses`).
  - Jenis kegiatan (regular/TA/ormawa/lainnya).
  - Tujuan + lokasi penggunaan (textarea 300 karakter).
  - Checkbox SOP.
- Banner konflik:
  - Unit, tanggal, kode booking konflik, saran rentang.
  - Tombol `Terapkan` saran.
- Ringkasan ketentuan: biaya, tanggung jawab, estimasi verifikasi.
- Aksi: `Kirim Pengajuan`, `Simpan Draft`.
- Sidebar bantuan operator.
- Validasi inline + error spesifik.

### 10.5 B3 / M7 — Peminjaman Saya

Tambahan:

- Banner peringatan pengembalian aktif + tombol fokus.
- Header + CTA `Pinjam Alat Baru`.
- Panel peminjam:
  - Peminjaman aktif + jatuh tempo.
  - Tier dan skor kepatuhan.
  - Tepat waktu, kerusakan, pelanggaran.
  - Kuota semester.
- Tab status: Semua, Menunggu, Aktif, Selesai, Dibatalkan/Ditolak.
- Kartu per status:
  - Aktif: countdown, loket, item, bundle checklist, stepper, QR, perpanjangan,
    kontak, dokumen.
  - Pending: item, tujuan, dosen, stepper, batal, ubah catatan.
  - Selesai: BAP, hasil inspeksi, poin kepatuhan, unduh BAP, pinjam lagi.
  - Dibatalkan/ditolak: alasan.
- Empty state per tab.
- Panduan keterlambatan di bawah.

### 10.6 B6 / M8 — Detail Peminjaman

Tambahan:

- Breadcrumb + header kode/status/peminjam/NIM/aset.
- Timer jatuh tempo.
- Aksi: QR pengembalian, perpanjangan, unduh bukti, hubungi petugas.
- Tracking 5 langkah dengan waktu/aktor.
- Daftar bundle dengan serial, kondisi keluar/masuk, QC.
- Catatan serah terima operator.
- QR check-in pass + titik pengembalian.
- Metadata: loket, petugas, tujuan, dosen, waktu pinjam, batas kembali,
  deposit/KTM.
- Regulasi dan sanksi.
- Modal perpanjangan.
- Toast sukses.
- Untuk status `rejected`: banner alasan danger.

### 10.7 B7 / M9 — Notifikasi

Tambahan:

- Header + subtitle.
- Filter kategori: Semua, Transaksi & Sirkulasi, Pengingat Tenggat, Warta Lab.
- Tombol `Tandai semua dibaca`.
- Dua kolom desktop:
  - Notifikasi akun/transaksi dengan ikon, judul, isi, waktu, aksi.
  - Pengumuman resmi dengan kategori, penerbit, tanggal, CTA.
- Grup `Hari Ini` / `Minggu Ini` / `Sebelumnya`.
- Sidebar:
  - Status loket.
  - Reputasi akun.
  - Dokumen/regulasi.
  - Kontak teknisi.
- Aksi notifikasi: buka detail, lihat surat, perpanjang.
- Data kategori dari `data.category`; tanpa migration.

### 10.8 B8 / M10 — Profil

Tambahan:

- Header `Profil & Keamanan Akun` + badge sinkronisasi.
- Kartu identitas:
  - UID lab, nama, NIM, angkatan, prodi, konsentrasi, status.
  - Tier, skor kepatuhan, tepat waktu, kerusakan, pelanggaran.
  - Kuota semester.
- Menu daftar: Biodata, Keamanan, Riwayat/Sertifikasi, Notifikasi/WhatsApp,
  Panduan, Kontak operator, Keluar.
- Form biodata:
  - Nama, NIM readonly, email readonly, WhatsApp.
  - DPA + NIDN.
  - Preferensi notifikasi.
- Sertifikasi:
  - Daftar sertifikat, nomor, penerbit, masa berlaku, PDF.
  - Unggah sertifikat baru.
- Keamanan:
  - Ubah password.
  - 2FA TOTP.
  - OTP email.
- Ringkasan sanksi: denda, klaim, status peminjaman.
- Callout reputasi.
- Logout dengan konfirmasi.
- Toast sukses.

### 10.9 B9 — Pusat Bantuan

Route baru `borrower.help`.

Tambahan:

- Header + search.
- SOP 01: alur 4 langkah.
- SOP 02: regulasi dan batas waktu.
- SOP 03: prosedur BAP/kerusakan.
- FAQ accordion dari tabel `faqs`.
- Kalkulator denda:
  - Input jam, jumlah unit.
  - Estimasi Rp dan penalti skor.
  - Tarif dari setting.
- Unduhan dokumen dari tabel `documents`.
- Kontak loket, WhatsApp, ekstensi.
- Denah fasilitas (link/visual statis).
- Status operasional (setting/statis).
- Footer.

### 10.10 M0 / M1 / M2 — Auth

- M0: copy onboarding, badge 120+ gear, SSO, CTA, tautan masuk.
- M1: login + remember + lupa sandi + callout verifikasi + tautan tata tertib
  dan bantuan + versi.
- M2: tambah field NIM/NIP, program studi/unit, WhatsApp, terms.
- `RegisteredUserController`:
  - Validasi field baru.
  - Assign `role_id = borrower`.
  - Assign `organization_id` default organisasi aktif pertama atau organisasi
    dari pilihan.
  - Simpan `nim`, `study_program`, `phone`.
  - Set `status = active`.
- Setelah register, redirect ke `borrower.home`.

### 10.11 M11 — Pembayaran Denda

Route baru:

- `borrower.penalties.index` — daftar denda aktif dan riwayat.
- `borrower.penalties.show` — halaman utama M11.
- `borrower.penalties.document` — cetak BAP/kuitansi.
- `borrower.penalties.payments.store` — pilih metode.
- `borrower.penalties.installment` — ajukan cicilan.
- `borrower.payments.proof` — unggah bukti.

Isi halaman:

- Header: back, judul, tombol `Bantuan CS`.
- Banner status akun:
  - `Akun Peminjaman Sementara Ditangguhkan` saat `penalties.status != paid`.
  - Subteks alasan dan janji pemulihan akses.
  - Varian sukses saat lunas: `Hak Akses Dipulihkan`.
- Kartu BAP:
  - Nomor BAP resmi (`LDR-BAP-2026-0004`).
  - Badge status + sisa hari (`Menunggu Pembayaran (Sisa 4 Hari)`).
  - Aset: kode, nama.
  - Deskripsi kerusakan.
  - Tanggal insiden + zona waktu WIB.
  - Petugas pemeriksa.
- Rincian biaya:
  - Subtotal perbaikan dan suku cadang.
  - Subsidi prodi (persen + nominal negatif).
  - Biaya administrasi/kalibrasi (nominal atau `Gratis (Tercakup)`).
  - Total kewajiban + jatuh tempo.
  - Versi regulasi (`sop_version`).
- Metode penyelesaian (radio/select):
  - `Virtual Account BNI Kampus` — instan 24 jam, nomor VA + tombol salin.
  - `QRIS / E-Wallet` — instruksi barcode statis mesin EDC.
  - `Penggantian Sparepart Mandiri` — instruksi invoice + part fisik.
  - `Keringanan / Cicilan` — form pengajuan, maks `installment_max`.
- Form bukti pembayaran:
  - Upload JPG/PNG/PDF maks 5 MB.
  - Catatan opsional.
  - Status `waiting_verification` setelah kirim.
- Tahapan pemulihan hak akses:
  - Langkah 1: verifikasi pembayaran.
  - Langkah 2: workshop SOP (judul dari setting).
  - Langkah 3: otorisasi kaprodi.
  - Accordion, langkah aktif ditandai.
- Aksi bawah:
  - `Cek Status Pembayaran` (refresh status).
  - `Unduh Salinan Dokumen BAP Resmi (PDF)` → print view.
  - Toast saat nomor VA disalin.
  - Tombol nonaktif + alasan saat sudah lunas atau menunggu verifikasi.
- Riwayat pembayaran: metode, nominal, tanggal, status, alasan penolakan.
- Setelah `paid`: banner hijau, akses kembali aktif, CTA ke beranda.

---

## 11. CSS dan JavaScript

### 11.1 CSS

File:

- `public/css/lendora.css`: tambah token yang belum ada:
  `--ldr-mono`, `--ldr-info-bg`, `--ldr-violet`, `--ldr-warning-bg`,
  `--ldr-success-bg`, `--ldr-danger-bg`, `--ldr-shadow-md`, `--ldr-radius-card`.
- `public/css/borrower.css`: tulis ulang/rapikan:
  - Container desktop `max-width: 1280px`.
  - Header 64px, nav horizontal, search, CTA, user chip.
  - Footer 4 kolom.
  - Grid katalog 2/3/4 kolom.
  - Sidebar filter 260px + sticky.
  - Kartu aset 4:3, badge, strip ketersediaan.
  - Detail 2 kolom, galeri sticky, sidebar sticky.
  - Kalender 7 kolom, status warna.
  - Kartu booking, stepper, timeline.
  - Panel profil 2 kolom.
  - Notifikasi 2 kolom.
  - Pusat bantuan 2 kolom.
  - Halaman denda M11: banner suspend, kartu BAP, rincian biaya, metode bayar,
    upload bukti, tahapan pemulihan, riwayat pembayaran.
  - Mobile: satu kolom, bottom nav, drawer filter.
  - `prefers-reduced-motion`.
- `public/css/ldr-login.css` dan `public/css/onboarding.css`: selaraskan token
  dan layout M0–M2.

Aturan: tanpa Tailwind, tanpa inline `<style>`, semua kelas kebab-case.

### 11.2 JavaScript

File: `public/js/borrower.js`

Modul:

- Modal/confirm (sudah ada).
- Date range + durasi otomatis.
- Terapkan saran rentang konflik.
- Drawer filter mobile.
- Toggle favorit (fetch POST + CSRF).
- Copy link/token.
- Print dokumen (`window.print()`).
- Accordion FAQ.
- Kalkulator denda.
- Kategori notifikasi (filter klien atau query).
- QR render (jika memakai library vendored).
- Copy nomor VA + toast (M11).
- Toggle panel metode pembayaran (M11).
- Preview nama file bukti bayar sebelum unggah (M11).
- Modal cicilan (M11).
- Muat ulang status pembayaran tanpa reload penuh (M11).
- Toast (sudah ada).

File baru:

- `public/js/borrower-help.js` jika kalkulator/accordion dipisah.
- `public/js/vendor/qr-code.js` hanya jika QR client-side disetujui.

Tanpa bundler, tanpa framework.

---

## 12. Bahasa dan Teks

File:

- `lang/id/ui.php`: tambah grup/key:
  - `borrower.*` (verified, quota, compliance, announcement, guide, metrics)
  - `catalog.*` (filters, sort, availability, grade, location, result)
  - `booking.*` (draft, course, activity, pickup, sop, extension, qr, document,
    penalty)
  - `penalty.*` (bap, breakdown, subsidy, due, status, method, va, qris,
    sparepart, installment, recovery, receipt)
  - `payment.*` (status, proof, verify, reject, history)
  - `profile.*` (cohort, concentration, dpa, certification, 2fa, sanctions)
  - `help.*` (sop, faq, calculator, documents, contact, status)
  - `notification.category.*`
  - `asset.grade.*`, `asset.access.*`
- `lang/id/messages.php`: pesan sukses/gagal baru:
  - draft tersimpan, draft dikirim, ekstensi diajukan/disetujui/ditolak,
    favorit, sertifikat, 2FA, kuota terlampaui, grade tidak memenuhi.
  - pembayaran dibuat, bukti terkirim, bukti ditolak, pembayaran terverifikasi,
    denda lunas, akses dipulihkan, cicilan diajukan.
- `lang/id/validation.php`: label atribut baru.
- Semua label mengikuti teks mockup; kode/identifier tetap Inggris.
- Nav desktop memakai `Katalog Peralatan`, mobile memakai `Katalog`.

---

## 13. Seeder dan Data Demo

File baru:

- `database/seeders/CourseSeeder.php`
- `database/seeders/PickupLocationSeeder.php`
- `database/seeders/AnnouncementSeeder.php`
- `database/seeders/FaqSeeder.php`
- `database/seeders/DocumentSeeder.php`
- `database/seeders/AssetComponentSeeder.php`
- `database/seeders/AssetQcSeeder.php`
- `database/seeders/AssetBlockSeeder.php`
- `database/seeders/BorrowerCertificationSeeder.php`
- `database/seeders/BorrowerFavoriteSeeder.php`
- `database/seeders/BookingExtensionSeeder.php`
- `database/seeders/PenaltySeeder.php` (BAP `LDR-BAP-2026-0004`, total
  Rp 3.105.000, status `waiting_payment`)
- `database/seeders/PaymentSeeder.php` (satu pembayaran pending + satu
  terverifikasi untuk riwayat)

File diubah:

- `database/seeders/SettingSeeder.php`: semua setting baru.
- `database/seeders/BorrowerShowcaseSeeder.php`:
  - Ani: `cohort_year = 2022`, `study_concentration = Sinematografi`,
    `dpa_name`, `dpa_nidn`, `compliance_score = 98`, `lab_tier = A+`.
  - Tambah 8 booking demo dengan status berbeda.
  - Tambah pengumuman, favorit, sertifikasi, ekstensi.
- `database/seeders/CategoryAndAssetSeeder.php`:
  - Tambah `grade`, `access_level`, `condition_score`, `is_featured`.
  - Tambah komponen, QC, dokumen.
- `database/seeders/DemoTransactionSeeder.php`: sesuaikan agar tidak bentrok.
- `database/seeders/DatabaseSeeder.php`: urutan pemanggilan baru.

Data demo minimum agar layar mirip mockup:

- 48 aset katalog (boleh terdiri dari unit nyata, bukan duplikat palsu).
- 2 pengumuman berjalan.
- 6 FAQ.
- 3 dokumen.
- 4 mata kuliah.
- 3 loket.
- 1 peminjaman aktif, 1 pending, 5 selesai, 1 batal/ditolak.
- 1 ekstensi pending.
- 1 denda `waiting_payment` dengan BAP dan rincian biaya persis mockup.
- 1 pembayaran terverifikasi untuk riwayat.

---

## 14. Test yang Wajib Ditambah

### 14.1 Feature tests

| File | Cakupan |
|---|---|
| `tests/Feature/Borrower/HomeTest.php` | Metrik, peminjaman aktif, pengumuman, kuota, skor |
| `tests/Feature/Borrower/CatalogFilterTest.php` | Search, kategori, brand, lokasi, grade, status, sort |
| `tests/Feature/Borrower/CatalogAvailabilityTest.php` | Filter tanggal, strip 14 hari, konflik |
| `tests/Feature/Borrower/AssetDetailTest.php` | Bundle, QC, kalender 30 hari, favorit, add-on |
| `tests/Feature/Borrower/BookingDraftTest.php` | Simpan/ubah/submit draft, isolasi pemilik |
| `tests/Feature/Borrower/BookingFlowTest.php` | Perluas: course, SOP, add-on, kuota, grade |
| `tests/Feature/Borrower/BookingExtensionTest.php` | Ajukan, setujui, tolak, akses pemilik |
| `tests/Feature/Borrower/BookingDocumentTest.php` | Halaman cetak surat/BAP, 403 pemilik lain |
| `tests/Feature/Borrower/NotificationCenterTest.php` | Kategori, mark read, pengumuman |
| `tests/Feature/Borrower/ProfileComplianceTest.php` | Skor, kuota, sanksi, update profil |
| `tests/Feature/Borrower/ProfileSecurityTest.php` | Password, 2FA, OTP |
| `tests/Feature/Borrower/CertificationTest.php` | Unggah/hapus sertifikat |
| `tests/Feature/Borrower/FavoriteTest.php` | Toggle, daftar, isolasi |
| `tests/Feature/Borrower/HelpCenterTest.php` | FAQ, dokumen, kalkulator, route |
| `tests/Feature/Borrower/RegistrationProfileTest.php` | Field baru, organisasi, redirect |
| `tests/Feature/Borrower/PenaltyPageTest.php` | Halaman M11, rincian biaya, status, akses pemilik, akses saat suspended |
| `tests/Feature/Borrower/PaymentFlowTest.php` | Mulai bayar, unggah bukti, verifikasi, tolak, denda lunas, akses pulih |
| `tests/Feature/Borrower/PenaltyDocumentTest.php` | Cetak BAP, 403 pemilik lain |
| `tests/Feature/Admin/PaymentVerificationTest.php` | Operator verify/reject, role lain 403, role technician 403 |

### 14.2 Unit tests

| File | Cakupan |
|---|---|
| `tests/Unit/AvailabilityCalendarTest.php` | Status per hari, blok, booking, sentuh tanggal |
| `tests/Unit/QuotaServiceTest.php` | Hitung kuota, batas unit paralel |
| `tests/Unit/BorrowerScoreServiceTest.php` | Pengurangan/penambahan skor |
| `tests/Unit/BookingExtensionServiceTest.php` | Transisi ekstensi |
| `tests/Unit/CatalogQueryTest.php` | Facet dan filter |
| `tests/Unit/PenaltyServiceTest.php` | BAP, subsidi, total, denda keterlambatan, suspend/restore |
| `tests/Unit/PaymentServiceTest.php` | Transisi status pembayaran, akumulasi, pelunasan |
| `tests/Unit/TotpServiceTest.php` | Kode TOTP |

### 14.3 Admin tests

| File | Cakupan |
|---|---|
| `tests/Feature/Admin/AnnouncementTest.php` | CRUD pengumuman, role |
| `tests/Feature/Admin/CourseTest.php` | CRUD mata kuliah |
| `tests/Feature/Admin/PickupLocationTest.php` | CRUD loket |
| `tests/Feature/Admin/AssetComponentTest.php` | Komponen di form aset |

Aturan: semua test lama wajib tetap lulus. Tidak boleh ada `markTestSkipped`
untuk menutupi kegagalan.

---

## 15. Urutan Eksekusi

Ikuti aturan `AGENTS.md`: backend dulu, frontend kemudian, satu fase satu
verifikasi.

### Fase 0 — Baseline dan keputusan

- Keputusan §2 sudah disetujui (paritas penuh, seeder dulu, print + token).
- Pastikan `php artisan test` tetap 143 passed.
- Catat baseline di history.

### Fase 1 — Backend fondasi

- Migration users, courses, pickup locations, assets, components, QC,
  documents, blocks, favorites, bookings, extensions, announcements, faqs,
  documents, certifications, reviews, penalties, payments.
- Model + enum + cast + relasi.
- Seeder setting dan data master.
- Service: Availability, Catalog, Booking, Quota, Score, Announcement,
  Penalty, Payment.
- Request + policy.
- Test unit dan feature backend.
- Verifikasi: `php artisan migrate`, `php artisan db:seed`, `php artisan test`.

### Fase 2 — Backend borrower

- Controller/route home, catalog, booking, extension, document, notification,
  profile, favorite, help, penalty, payment, auth register.
- View contract final.
- Test feature borrower.
- Verifikasi: `php artisan test`, `php artisan route:list --path=borrower`.

### Fase 3 — Layout dan komponen visual

- Layout borrower desktop/mobile + footer.
- Komponen baru dan ubah komponen lama.
- CSS `lendora.css`, `borrower.css`, `ldr-login.css`, `onboarding.css`.
- JS `borrower.js`.
- Verifikasi: `php artisan view:cache`, cek manual lebar 360/768/1024/1280.

### Fase 4 — Beranda, katalog, detail aset

- `home.blade.php`.
- `catalog/index.blade.php`.
- `catalog/show.blade.php`.
- Komponen kartu, filter, kalender, bundle, QC.
- Test visual dan feature.

### Fase 5 — Form dan detail peminjaman

- `bookings/create.blade.php`.
- `bookings/index.blade.php`.
- `bookings/show.blade.php`.
- Modal ekstensi, QR pass, dokumen cetak.
- Test alur penuh.

### Fase 6 — Notifikasi, profil, bantuan, denda, auth

- `notifications/index.blade.php`.
- `profile/edit.blade.php`.
- `help/index.blade.php`.
- `penalties/index.blade.php`, `penalties/show.blade.php`,
  `penalties/document.blade.php`.
- `auth/login.blade.php`, `auth/register.blade.php`, `welcome.blade.php`.
- Sertifikasi, 2FA, OTP, sanksi, pembayaran denda manual.
- Test.

### Fase 7 — Admin data pendukung

- Verifikasi pembayaran denda (operator/admin).
- CRUD pengumuman, courses, pickup locations, FAQ, dokumen.
- Form aset: komponen, QC, dokumen, grade.
- Test role dan validasi.

### Fase 8 — UAT dan penutupan

- Uji semua role.
- Uji lebar responsif.
- Uji empty/error/loading.
- Uji akses lintas borrower (403).
- Perbarui `docs/rencana/README.md` dan tulis history.

---

## 16. Acceptance Criteria per Layar

### Beranda

- [ ] Menampilkan nama, prodi, NIM, status verifikasi, skor kepatuhan.
- [ ] Metrik unit aktif, denda, sisa kuota.
- [ ] Kartu peminjaman aktif dengan stepper dan aksi.
- [ ] Rekomendasi 4+ aset dengan data nyata.
- [ ] Pengumuman dari tabel, bukan teks setting saja.
- [ ] Panduan 4 langkah + tautan B9.
- [ ] Empty state jika tidak ada pinjaman.

### Katalog

- [ ] Filter search, kategori, tanggal, brand, lokasi, status, grade.
- [ ] Sort berfungsi dan bertahan saat pagination.
- [ ] Jumlah hasil dan facet benar.
- [ ] Kartu menampilkan status, jumlah unit, grade, lokasi, ketersediaan.
- [ ] Mobile filter drawer dan desktop sidebar.
- [ ] Empty state + reset.

### Detail Aset

- [ ] Galeri, spesifikasi, bundle, QC, regulasi.
- [ ] Kalender 30 hari dengan status nyata.
- [ ] Sidebar sticky dengan lokasi, jadwal, add-on, aksi.
- [ ] Favorit dan share berfungsi.
- [ ] CTA nonaktif dengan alasan jika tidak tersedia.

### Form Pengajuan

- [ ] Unit, add-on, tanggal, durasi, loket, mata kuliah, jenis kegiatan,
      tujuan, SOP.
- [ ] Validasi durasi, kuota, bentrok, grade.
- [ ] Banner konflik menyebut unit/tanggal/kode + saran.
- [ ] Simpan draft dan kirim.
- [ ] Redirect ke detail setelah kirim.

### Peminjaman Saya

- [ ] Tab dan hitungan benar.
- [ ] Panel peminjam (aktif, tier, kuota).
- [ ] Kartu per status dengan aksi sesuai.
- [ ] QR, ekstensi, dokumen, kontak pada kartu aktif.
- [ ] Empty state per tab.

### Detail Peminjaman

- [ ] Header, status, peminjam, aset.
- [ ] Timeline 5 langkah dengan waktu/aktor.
- [ ] Bundle dan kondisi keluar/masuk.
- [ ] Catatan serah terima.
- [ ] QR/token, metadata, regulasi.
- [ ] Aksi batal/ekstensi/cetak sesuai status.

### Notifikasi

- [ ] Filter kategori.
- [ ] Mark all read.
- [ ] Grup hari ini/minggu/sebelumnya.
- [ ] Pengumuman resmi terpisah.
- [ ] Aksi membuka objek terkait.
- [ ] Sidebar status loket/reputasi/kontak.

### Profil

- [ ] Identitas lengkap + skor + kuota.
- [ ] Form biodata + WhatsApp + DPA.
- [ ] Sertifikasi tampil/unggah.
- [ ] Ubah password.
- [ ] 2FA/OTP.
- [ ] Ringkasan sanksi.
- [ ] Logout.

### Pembayaran Denda (M11)

- [ ] Banner suspend saat denda belum lunas.
- [ ] Nomor BAP, status, sisa hari.
- [ ] Data aset, kerusakan, tanggal insiden, petugas.
- [ ] Rincian biaya: subtotal, subsidi, admin, total, jatuh tempo.
- [ ] Empat metode: VA, QRIS, sparepart, cicilan.
- [ ] Nomor VA + tombol salin.
- [ ] Unggah bukti bayar.
- [ ] Tahapan pemulihan akses.
- [ ] Cetak BAP.
- [ ] Riwayat pembayaran.
- [ ] Setelah lunas: akses kembali aktif.
- [ ] Halaman tetap bisa dibuka saat akun suspended.
- [ ] Borrower lain tidak bisa membuka denda orang lain (403).

### Pusat Bantuan

- [ ] SOP 01–03.
- [ ] FAQ accordion.
- [ ] Kalkulator denda.
- [ ] Unduhan dokumen.
- [ ] Kontak dan status operasional.
- [ ] Route dapat diakses hanya borrower.

### Auth

- [ ] Register menyimpan NIM, prodi, WhatsApp, organisasi.
- [ ] Borrower baru langsung melihat katalog.
- [ ] Login/register sesuai M1/M2.
- [ ] Onboarding sesuai M0.

---

## 17. File Map

### 17.1 Dibuat

```
app/Enums/AnnouncementCategory.php
app/Enums/AssetAccessLevel.php
app/Enums/AssetBlockType.php
app/Enums/AssetGrade.php
app/Enums/BookingActivityType.php
app/Enums/BookingExtensionStatus.php
app/Enums/CertificationStatus.php
app/Enums/DocumentType.php
app/Enums/PenaltyStatus.php
app/Enums/PaymentStatus.php
app/Enums/PaymentMethod.php

app/Http/Controllers/Borrower/BookingExtensionController.php
app/Http/Controllers/Borrower/DocumentController.php
app/Http/Controllers/Borrower/FavoriteController.php
app/Http/Controllers/Borrower/HelpController.php
app/Http/Controllers/Borrower/PenaltyController.php
app/Http/Controllers/Borrower/PaymentController.php
app/Http/Controllers/Borrower/TwoFactorController.php
app/Http/Controllers/Admin/AnnouncementController.php
app/Http/Controllers/Admin/CourseController.php
app/Http/Controllers/Admin/PaymentController.php
app/Http/Controllers/Admin/PenaltyController.php
app/Http/Controllers/Admin/PickupLocationController.php

app/Http/Requests/SaveBookingDraftRequest.php
app/Http/Requests/SubmitBookingRequest.php
app/Http/Requests/RequestBookingExtensionRequest.php
app/Http/Requests/StoreBorrowerCertificationRequest.php
app/Http/Requests/StoreAnnouncementRequest.php
app/Http/Requests/UpdateAnnouncementRequest.php
app/Http/Requests/StoreCourseRequest.php
app/Http/Requests/UpdateCourseRequest.php
app/Http/Requests/StorePickupLocationRequest.php
app/Http/Requests/UpdatePickupLocationRequest.php
app/Http/Requests/StoreFaqRequest.php
app/Http/Requests/UpdateFaqRequest.php
app/Http/Requests/StoreDocumentRequest.php
app/Http/Requests/UpdateDocumentRequest.php
app/Http/Requests/StorePenaltyPaymentRequest.php
app/Http/Requests/SubmitPaymentProofRequest.php
app/Http/Requests/RequestInstallmentRequest.php
app/Http/Requests/VerifyPaymentRequest.php
app/Http/Requests/RejectPaymentRequest.php

app/Models/Announcement.php
app/Models/AssetBlock.php
app/Models/AssetComponent.php
app/Models/AssetDocument.php
app/Models/AssetFavorite.php
app/Models/AssetQcCheck.php
app/Models/AssetReview.php
app/Models/BookingExtension.php
app/Models/Course.php
app/Models/Document.php
app/Models/Faq.php
app/Models/Penalty.php
app/Models/Payment.php
app/Models/PickupLocation.php
app/Models/UserCertification.php

app/Policies/BookingExtensionPolicy.php
app/Policies/AnnouncementPolicy.php
app/Policies/FaqPolicy.php
app/Policies/DocumentPolicy.php
app/Policies/PenaltyPolicy.php
app/Policies/PaymentPolicy.php

app/Services/AnnouncementService.php
app/Services/BorrowerScoreService.php
app/Services/CatalogService.php
app/Services/PaymentService.php
app/Services/PenaltyService.php
app/Services/QuotaService.php
app/Services/TotpService.php

database/migrations/2026_11_09_000001_add_borrower_fields_to_users_table.php
database/migrations/2026_11_09_000002_create_courses_table.php
database/migrations/2026_11_09_000003_create_pickup_locations_table.php
database/migrations/2026_11_09_000004_add_access_and_catalog_fields_to_assets_table.php
database/migrations/2026_11_09_000005_create_asset_components_table.php
database/migrations/2026_11_09_000006_create_asset_qc_checks_table.php
database/migrations/2026_11_09_000007_create_asset_documents_table.php
database/migrations/2026_11_09_000008_create_asset_blocks_table.php
database/migrations/2026_11_09_000009_create_asset_favorites_table.php
database/migrations/2026_11_09_000010_add_booking_detail_fields_to_bookings_table.php
database/migrations/2026_11_09_000011_create_booking_extensions_table.php
database/migrations/2026_11_09_000012_create_announcements_table.php
database/migrations/2026_11_09_000013_create_faqs_table.php
database/migrations/2026_11_09_000014_create_documents_table.php
database/migrations/2026_11_09_000015_create_user_certifications_table.php
database/migrations/2026_11_09_000016_create_asset_reviews_table.php
database/migrations/2026_11_09_000017_create_penalties_table.php
database/migrations/2026_11_09_000018_create_payments_table.php

database/seeders/AnnouncementSeeder.php
database/seeders/AssetBlockSeeder.php
database/seeders/AssetComponentSeeder.php
database/seeders/AssetQcSeeder.php
database/seeders/BookingExtensionSeeder.php
database/seeders/BorrowerCertificationSeeder.php
database/seeders/BorrowerFavoriteSeeder.php
database/seeders/CourseSeeder.php
database/seeders/DocumentSeeder.php
database/seeders/FaqSeeder.php
database/seeders/PaymentSeeder.php
database/seeders/PenaltySeeder.php
database/seeders/PickupLocationSeeder.php

resources/views/borrower/help/index.blade.php
resources/views/borrower/penalties/index.blade.php
resources/views/borrower/penalties/show.blade.php
resources/views/borrower/penalties/document.blade.php
resources/views/borrower/bookings/document.blade.php
resources/views/borrower/bookings/return-pass.blade.php

resources/views/components/borrower/hero-status.blade.php
resources/views/components/borrower/stat-strip.blade.php
resources/views/components/borrower/active-loan-card.blade.php
resources/views/components/borrower/booking-stepper.blade.php
resources/views/components/borrower/announcement-list.blade.php
resources/views/components/borrower/quick-guide.blade.php
resources/views/components/borrower/catalog-filter-panel.blade.php
resources/views/components/borrower/asset-availability-strip.blade.php
resources/views/components/borrower/availability-calendar.blade.php
resources/views/components/borrower/asset-bundle-list.blade.php
resources/views/components/borrower/asset-qc-card.blade.php
resources/views/components/borrower/booking-card.blade.php
resources/views/components/borrower/booking-timeline.blade.php
resources/views/components/borrower/booking-actions.blade.php
resources/views/components/borrower/qr-pass.blade.php
resources/views/components/borrower/extension-modal.blade.php
resources/views/components/borrower/compliance-card.blade.php
resources/views/components/borrower/quota-card.blade.php
resources/views/components/borrower/sanction-card.blade.php
resources/views/components/borrower/notification-item.blade.php
resources/views/components/borrower/help-faq-accordion.blade.php
resources/views/components/borrower/penalty-calculator.blade.php
resources/views/components/borrower/bap-summary.blade.php
resources/views/components/borrower/penalty-breakdown.blade.php
resources/views/components/borrower/payment-method-list.blade.php
resources/views/components/borrower/recovery-steps.blade.php
resources/views/components/borrower/payment-upload.blade.php
resources/views/components/borrower/footer.blade.php
resources/views/components/ui/breadcrumbs.blade.php
resources/views/components/ui/select.blade.php
resources/views/components/ui/textarea.blade.php

public/js/borrower-help.js
public/js/vendor/qr-code.js

tests/Feature/Borrower/HomeTest.php
tests/Feature/Borrower/CatalogFilterTest.php
tests/Feature/Borrower/CatalogAvailabilityTest.php
tests/Feature/Borrower/AssetDetailTest.php
tests/Feature/Borrower/BookingDraftTest.php
tests/Feature/Borrower/BookingExtensionTest.php
tests/Feature/Borrower/BookingDocumentTest.php
tests/Feature/Borrower/NotificationCenterTest.php
tests/Feature/Borrower/ProfileComplianceTest.php
tests/Feature/Borrower/ProfileSecurityTest.php
tests/Feature/Borrower/CertificationTest.php
tests/Feature/Borrower/FavoriteTest.php
tests/Feature/Borrower/HelpCenterTest.php
tests/Feature/Borrower/RegistrationProfileTest.php
tests/Feature/Borrower/PenaltyPageTest.php
tests/Feature/Borrower/PaymentFlowTest.php
tests/Feature/Borrower/PenaltyDocumentTest.php
tests/Feature/Admin/PaymentVerificationTest.php
tests/Feature/Admin/AnnouncementTest.php
tests/Feature/Admin/CourseTest.php
tests/Feature/Admin/PickupLocationTest.php
tests/Feature/Admin/AssetComponentTest.php
tests/Unit/AvailabilityCalendarTest.php
tests/Unit/QuotaServiceTest.php
tests/Unit/BorrowerScoreServiceTest.php
tests/Unit/BookingExtensionServiceTest.php
tests/Unit/CatalogQueryTest.php
tests/Unit/PenaltyServiceTest.php
tests/Unit/PaymentServiceTest.php
tests/Unit/TotpServiceTest.php
```

### 17.2 Diubah

```
app/Http/Controllers/Auth/RegisteredUserController.php
app/Http/Controllers/Borrower/HomeController.php
app/Http/Controllers/Borrower/CatalogController.php
app/Http/Controllers/Borrower/BookingController.php
app/Http/Controllers/Borrower/NotificationController.php
app/Http/Controllers/Borrower/ProfileController.php
app/Http/Controllers/Admin/AssetController.php
app/Http/Requests/StoreBookingRequest.php
app/Http/Requests/UpdateBorrowerProfileRequest.php
app/Http/Requests/StoreAssetRequest.php
app/Http/Requests/UpdateAssetRequest.php
app/Models/User.php
app/Models/Asset.php
app/Models/Booking.php
app/Models/BookingItem.php
app/Models/Organization.php
app/Policies/BookingPolicy.php
app/Providers/AppServiceProvider.php
app/Services/AvailabilityService.php
app/Services/BookingService.php
app/Services/HandoverService.php
app/Services/InspectionService.php
app/Services/SettingService.php
app/Enums/BookingStatus.php
database/seeders/SettingSeeder.php
database/seeders/BorrowerShowcaseSeeder.php
database/seeders/CategoryAndAssetSeeder.php
database/seeders/DemoTransactionSeeder.php
database/seeders/DatabaseSeeder.php
routes/web.php
resources/views/layouts/admin.blade.php
public/css/admin.css
lang/id/ui.php
lang/id/messages.php
lang/id/validation.php
public/css/lendora.css
public/css/borrower.css
public/css/ldr-login.css
public/css/onboarding.css
public/js/borrower.js
resources/views/layouts/borrower.blade.php
resources/views/layouts/guest.blade.php
resources/views/borrower/home.blade.php
resources/views/borrower/catalog/index.blade.php
resources/views/borrower/catalog/show.blade.php
resources/views/borrower/bookings/index.blade.php
resources/views/borrower/bookings/create.blade.php
resources/views/borrower/bookings/show.blade.php
resources/views/borrower/notifications/index.blade.php
resources/views/borrower/profile/edit.blade.php
resources/views/auth/login.blade.php
resources/views/auth/register.blade.php
resources/views/welcome.blade.php
resources/views/components/asset-card.blade.php
resources/views/components/status-badge.blade.php
resources/views/components/timeline.blade.php
resources/views/components/icon.blade.php
resources/views/components/empty-state.blade.php
resources/views/components/modal.blade.php
resources/views/components/confirm-modal.blade.php
```

---

## 18. Verifikasi

Perintah wajib setelah setiap fase:

```bash
php artisan migrate
php artisan db:seed
php artisan route:list --path=borrower
php artisan view:cache
php artisan test --compact
```

Verifikasi manual:

- Lebar 360, 390, 768, 1024, 1280 px.
- Role borrower, operator, organization_admin, admin_prodi, technician.
- Akses langsung URL borrower oleh role lain → 403.
- Akses booking milik borrower lain → 403/404.
- Akses denda milik borrower lain → 403.
- Akun `suspended` bisa membuka `borrower.penalties.*`, tetapi route lain → 403/redirect.
- Alur bayar: pilih metode → unggah bukti → operator verifikasi → denda lunas →
  akun aktif kembali.
- Semua tautan nav mengarah ke route nyata, tidak ada `#`.
- Tidak ada query di Blade.
- Tidak ada inline `<style>`.
- Tidak ada dependency Composer/npm baru tanpa persetujuan.
- Semua teks UI Indonesia; kode tetap Inggris.

---

## 19. Risiko dan Catatan

| Risiko | Mitigasi |
|---|---|
| Penambahan tabel besar di luar MVP | Sudah disetujui §2; pisahkan per fase |
| Migration baru mengubah data lama | Backup database sebelum migrate; migration hanya additive |
| Skor kepatuhan/kuota tidak akurat | Hitung dari transaksi nyata, seed hanya nilai awal |
| QR/PDF butuh dependency | Pakai token + print view; tanpa package |
| 2FA mengubah alur login | Buat fase terpisah + test challenge |
| Data SIAKAD tidak tersedia | Pakai tabel courses + label informatif |
| Pembayaran tanpa gateway rawan salah catat | Bukti wajib, verifikasi operator, log aktivitas, status eksplisit |
| Akun suspended terkunci dari halaman bayar | Route denda dikecualikan dari `EnsureActiveStatus` + test regresi |
| Denda ganda untuk satu insiden | Unique BAP per inspeksi/booking di service |
| Admin CRUD menambah beban | Prioritaskan seeder; CRUD setelah borrower stabil |
| Layout desktop mengubah mobile | Satu basis CSS, uji dua breakpoint |
| Test lama pecah karena status `draft` | Update query/count/status machine + test regresi |

---

## 20. Checklist “Tidak Boleh Terlewat”

### Navigasi dan layout

- [ ] Menu desktop: Beranda, Katalog Peralatan, Peminjaman Saya, Notifikasi,
      Bantuan & Regulasi.
- [ ] Menu mobile: Beranda, Katalog, Peminjaman, Notifikasi, Profil.
- [ ] Search, CTA Ajukan, bell unread, chip pengguna.
- [ ] Footer desktop 4 kolom.
- [ ] Badge jumlah pinjaman aktif.
- [ ] Breadcrumb di detail/form/profil/bantuan.

### Beranda

- [ ] Banner verifikasi dan skor.
- [ ] Metrik unit/denda/kuota.
- [ ] Kartu pinjaman aktif + stepper.
- [ ] Rekomendasi/populer.
- [ ] Pengumuman.
- [ ] Panduan 4 langkah.

### Katalog

- [ ] Search.
- [ ] Filter tanggal.
- [ ] Filter kategori.
- [ ] Filter ketersediaan.
- [ ] Filter grade.
- [ ] Filter merek.
- [ ] Filter lokasi.
- [ ] Sort.
- [ ] Chip aktif + reset.
- [ ] Kartu kaya + strip 14 hari.
- [ ] Pagination info.

### Detail aset

- [ ] Galeri.
- [ ] Spesifikasi.
- [ ] Bundle.
- [ ] QC.
- [ ] Regulasi grade.
- [ ] Kalender 30 hari.
- [ ] Sidebar sticky.
- [ ] Add-on.
- [ ] Favorit.
- [ ] Share.
- [ ] Tanya operator.
- [ ] Unduh manual.
- [ ] CTA nonaktif beralasan.

### Form

- [ ] Unit terpilih.
- [ ] Ganti unit.
- [ ] Add-on maks 3.
- [ ] Tanggal + durasi.
- [ ] Loket.
- [ ] Mata kuliah.
- [ ] Jenis kegiatan.
- [ ] Tujuan + lokasi.
- [ ] SOP.
- [ ] Konflik + saran.
- [ ] Kuota.
- [ ] Draft.
- [ ] Submit.

### Peminjaman saya

- [ ] Banner perhatian.
- [ ] Panel tier/skor/kuota.
- [ ] Tab + hitungan.
- [ ] Kartu aktif.
- [ ] Kartu pending.
- [ ] Kartu selesai.
- [ ] Kartu batal/tolak.
- [ ] QR.
- [ ] Ekstensi.
- [ ] Dokumen.
- [ ] Kontak.
- [ ] Empty state.

### Detail booking

- [ ] Header + peminjam + aset.
- [ ] Timer.
- [ ] Timeline 5 langkah.
- [ ] Bundle + serial + kondisi.
- [ ] Catatan handover.
- [ ] QR pass.
- [ ] Metadata.
- [ ] Regulasi.
- [ ] Ekstensi modal.
- [ ] Cetak surat/BAP.
- [ ] Batal.

### Notifikasi

- [ ] Kategori.
- [ ] Mark all.
- [ ] Grup waktu.
- [ ] Pengumuman.
- [ ] Aksi.
- [ ] Sidebar loket/reputasi/dokumen/kontak.

### Profil

- [ ] Identitas + UID.
- [ ] Angkatan + konsentrasi.
- [ ] Skor + tier.
- [ ] Metrik tepat waktu/kerusakan/pelanggaran.
- [ ] Kuota.
- [ ] Biodata + WhatsApp.
- [ ] DPA.
- [ ] Sertifikasi + unggah.
- [ ] Password.
- [ ] 2FA.
- [ ] OTP email.
- [ ] Sanksi.
- [ ] Logout.

### Pembayaran denda (M11)

- [ ] Banner suspend.
- [ ] Nomor BAP.
- [ ] Status + sisa hari.
- [ ] Aset + kerusakan.
- [ ] Tanggal insiden + petugas.
- [ ] Rincian biaya + subsidi.
- [ ] Jatuh tempo.
- [ ] VA + salin nomor.
- [ ] QRIS.
- [ ] Sparepart.
- [ ] Cicilan.
- [ ] Unggah bukti.
- [ ] Tahapan pemulihan.
- [ ] Cek status.
- [ ] Cetak BAP.
- [ ] Riwayat pembayaran.
- [ ] Status lunas + akses pulih.
- [ ] Akses saat suspended.
- [ ] Verifikasi operator.

### Bantuan

- [ ] SOP 01.
- [ ] SOP 02.
- [ ] SOP 03.
- [ ] FAQ.
- [ ] Kalkulator.
- [ ] Dokumen.
- [ ] Kontak.
- [ ] Denah.
- [ ] Status.

### Auth

- [ ] Onboarding M0.
- [ ] Login M1.
- [ ] Register M2.
- [ ] NIM/NIP.
- [ ] Prodi.
- [ ] WhatsApp.
- [ ] Terms.
- [ ] Organisasi default.
- [ ] Redirect benar.

### Sistem

- [ ] Migration additive.
- [ ] Model + enum + cast.
- [ ] Service + policy.
- [ ] Request + validasi.
- [ ] Route + controller.
- [ ] Seeder.
- [ ] Test unit/feature/admin.
- [ ] Language.
- [ ] CSS/JS.
- [ ] Dokumentasi history.
- [ ] Tabel `penalties` + `payments`.
- [ ] Verifikasi pembayaran admin.
- [ ] Log aktivitas denda/pembayaran.
- [ ] Suspend/restore akses otomatis.

---

## 21. Keputusan dan Langkah Berikutnya

Semua keputusan sudah final (2026-11-09):

1. **Paritas penuh termasuk M11** — tabel `penalties` dan `payments` dibangun,
   pembayaran manual tanpa gateway.
2. **Estimasi denda dan halaman pembayaran** tampil di B1/B8/B9/M11.
3. **PDF via print view dan QR via token** tanpa dependency baru.
4. **Seeder dulu** untuk data master; CRUD admin menyusul.

Eksekusi dimulai dari Fase 1 (backend fondasi), bukan dari tampilan, agar view
tidak dibangun di atas data yang belum ada. Setiap fase: backend dulu, test
lulus, baru frontend.
