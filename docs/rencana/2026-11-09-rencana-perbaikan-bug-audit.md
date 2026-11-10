# Rencana Perbaikan Bug — Hasil Audit UI & Fungsional

Tanggal: 2026-11-09
Status: `SELESAI` — batch 1 (B01–B05), batch 2 (B06–B09, B11, B13), batch 3 (B14–B16, B20–B22, B24), batch 4 (B10, B12), dan batch 5 (B17–B19, B23) selesai
Ruang lingkup: area borrower (katalog, kartu aset, peminjaman, notifikasi) dan
temuan lintas modul yang terbukti menghasilkan HTTP 500.

Sumber acuan:

- Audit live dengan login seeded user (`admin@lendora.test`, `borrower2@lendora.test`, `ani@lendora.test`)
- 4 screenshot: Beranda, Notifikasi, Peminjaman Saya, `/borrower/catalog/7`
- `AGENTS.md` §0 (alur kerja), §5 (status machine), §8 (placement), §9 (testing)
- `docs/rencana/README.md` (aturan kerja)

Status arti: `BELUM` · `DIKERJAKAN` · `SELESAI` · `BUTUH PERSETUJUAN`

---

## 1. Ringkasan Audit

Baseline saat audit:

| Item | Kondisi |
|---|---|
| `php artisan test` | 143 passed (526 assertions) |
| Halaman borrower yang di-crawl | `/borrower` 200, `/borrower/catalog` 200, `/borrower/catalog/7` **500**, `/borrower/bookings` 200, `/borrower/notifications` 200, `/borrower/penalties` **500** (sebagai pemilik penalti), `/borrower/two-factor` **500** |
| Halaman admin yang di-crawl | 23 halaman, semua 200 |
| Akar masalah berulang | (a) enum dianggap string di Blade/PHP, (b) view tidak sinkron dengan `lang/id/ui.php` dan `public/css/*` |

Catatan: test hijau tidak menangkap bug ini karena smoke test tidak membuka
aset yang punya komponen, halaman penalti, halaman 2FA, dan tidak memeriksa
atribut JS/CSS.

---

## 2. Peta Bug terhadap Screenshot

| Screenshot | Gejala | Bug ID |
|---|---|---|
| Beranda (lama) | Gambar aset rusak (alt text tampil), judul kartu menimpa badge, "0 unit tersedia" | B02; gambar sudah tidak reproducible (`/storage/...` = 200) |
| Notifikasi | Teks `ui.notification.sidebar_status` dkk tampil mentah; judul grup `today`/`earlier`; empty-state ganda | B04 |
| Peminjaman Saya | Filter berdempet; tombol Batalkan/Perpanjang tidak muncul; dot progres tidak kumulatif | B03, B05 |
| `/borrower/catalog/7` | `TypeError: strtolower(): Argument #1 ($string) must be of type string, App\Enums\AssetCondition given` | B01 |

---

## 3. Kandidat 5 Bug Pertama (batch 1)

Lima bug ini dipilih karena mewakili keempat screenshot, semuanya punya bukti
nyata, dan perbaikannya tidak menyentuh skema database.

### B01 — Detail katalog 500 saat aset punya komponen `SELESAI`
- Gejala: `/borrower/catalog/7` → HTTP 500 (screenshot 4).
- Lokasi: `resources/views/components/borrower/asset-bundle-list.blade.php:15-16`.
- Akar masalah: `condition_status` di-cast ke `App\Enums\AssetCondition`
  (`app/Models/AssetComponent.php:31`), lalu kode memanggil
  `strtolower($component->condition_status)`.
- Dampak: 27 aset memiliki komponen; semua halaman detailnya error.
- Perbaikan: pakai `$component->condition_status->value` untuk kelas badge dan
  key terjemahan.
- Verifikasi: `GET /borrower/catalog/7` harus 200 dan badge kondisi tampil.
- Risiko: rendah, hanya presentasi.

### B02 — Kartu aset salah status dan "0 unit tersedia" `SELESAI`
- Gejala: semua kartu di Beranda/Katalog menampilkan **0 unit tersedia** dan CTA
  "tidak tersedia" walau aset `available` (terverifikasi di HTML live).
- Lokasi: `resources/views/components/asset-card.blade.php:6,83,85`.
- Akar masalah: membandingkan instance enum dengan string:
  `$asset->status === AssetStatus::Available->value`.
- Perbaikan: bandingkan dengan enum (`$asset->status === AssetStatus::Available`)
  pada ketiga tempat.
- Verifikasi: render Beranda, cari `ldr-available-count`; nilai > 0 dan kelas
  `ldr-asset-card__cta--available` muncul untuk aset available.
- Risiko: rendah.

### B03 — Tombol Batalkan & Perpanjang tidak pernah muncul `SELESAI`
- Gejala: pada daftar peminjaman, booking `pending` tidak punya tombol
  "Batalkan", booking `active` tidak punya tombol perpanjangan (screenshot 3).
- Lokasi:
  - `resources/views/borrower/bookings/index.blade.php:77,83`
  - `resources/views/borrower/bookings/show.blade.php:40,66`
  - `resources/views/components/borrower/booking-card.blade.php:32,38`
- Akar masalah: `$booking->status === 'pending'` — `status` di-cast ke
  `BookingStatus` (`app/Models/Booking.php:55`), sehingga strict compare ke string
  selalu `false`. Alert alasan penolakan juga tidak pernah tampil.
- Perbaikan: bandingkan dengan `BookingStatus::Pending` / `BookingStatus::Active`
  / `BookingStatus::Rejected` (import enum di view atau pakai `->value`).
- Verifikasi: booking `pending` menampilkan tombol Batalkan; booking `approved`
  menampilkan aksi sesuai aturan; booking `rejected` menampilkan alasan.
- Catatan: tombol perpanjangan saat ini `<a href>` ke route POST (lihat B08,
  batch berikut). Untuk batch 1 cukup perbaiki visibilitas; link diperbaiki di B08.
- Risiko: rendah.

### B04 — Notifikasi menampilkan raw key & label Inggris `SELESAI`
- Gejala: sidebar kanan notifikasi menampilkan `ui.notification.sidebar_status`,
  `ui.notification.counter_active`, dst.; judul grup `today` dan `earlier`;
  empty-state muncul dua kali.
- Lokasi:
  - `resources/views/borrower/notifications/index.blade.php:29,61-80`
  - `app/Http/Controllers/Borrower/NotificationController.php:28-31`
  - `lang/id/ui.php` blok `notification` (baris 181-208)
- Akar masalah:
  1. Key `sidebar_status`, `counter_active`, `sidebar_reputation`,
     `sidebar_documents`, `sop_link`, `penalty_link`, `sidebar_contact` tidak ada.
     `account_good` hanya ada di namespace `profile`.
  2. View mencetak `$groupLabel` mentah; controller memakai key `today`/`earlier`.
  3. Semua grup dirender, termasuk grup kosong, sehingga empty-state tampil
     berulang.
- Perbaikan:
  1. Tambahkan key yang hilang di `lang/id/ui.php` (Bahasa Indonesia).
  2. Terjemahkan label grup: `__('ui.notification.' . $groupLabel)`.
  3. Render `x-empty-state` sekali saja bila total notifikasi kosong; grup kosong
     tidak dirender.
- Verifikasi: halaman notifikasi tidak mengandung string `ui.notification.` dan
  tidak menampilkan "today"/"earlier".
- Risiko: rendah. Data hardcoded kontak operator tetap ada; dipindah ke batch
  berikut (B17).

### B05 — Filter status peminjaman tidak ter-style `SELESAI`
- Gejala: judul filter berdesakan: "Semua 4 Menunggu Persetujuan 1 Disetujui 1 …"
  (screenshot 3).
- Lokasi: `resources/views/borrower/bookings/index.blade.php:22,25` vs
  `public/css/lendora.css:690-729`.
- Akar masalah: markup memakai `ldr-status-tab` / `ldr-status-tab__count`,
  sedangkan CSS mendefinisikan `ldr-status-tabs__tab` / `ldr-status-tabs__count`.
- Perbaikan: samakan nama kelas. Pilih markup mengikuti CSS
  (`ldr-status-tabs__tab`, `ldr-status-tabs__count`) agar tidak mengubah token
  desain.
- Verifikasi: chip filter punya border, padding, jarak, dan state aktif.
- Risiko: rendah. Cek juga pemakaian kelas yang sama di area admin.

---

## 4. Backlog Setelah Batch 1

### 4.1 Kritis — HTTP 500 lain
- [x] **B06** `resources/views/borrower/penalties/index.blade.php:44` dan
      `penalties/show.blade.php:19`: `strtolower($penalty->status)` pada enum
      `PenaltyStatus` → 500. Ganti ke `->value`.
- [x] **B07** `penalties/show.blade.php:77` (`$payment->status === 'verified'`,
      concat enum ke `__()`) dan `penalties/document.blade.php:49` (concat enum).
      Gunakan `->value`, bandingkan dengan enum `PaymentStatus`.
- [x] **B08** View `borrower/two-factor/edit` tidak ada, route
      `/borrower/two-factor` → 500. Putuskan: buat view atau hapus route/controller
      (butuh persetujuan karena fitur di luar MVP §12). Diputuskan: buat view.
- [x] **B09** View `borrower/payments/show` tidak ada
      (`PaymentController::show`). Putuskan bersama B13. Diputuskan: buat view.
- [x] **B10** `resources/views/borrower/bookings/extension-form.blade.php:17`
      memakai `route('bookings.extension.request')` tanpa prefix `borrower.` →
      500 saat submit. Sekarang memakai `borrower.bookings.extension.request`
      dan `BookingService::requestExtension` sudah diimplementasikan.
- [x] **B11** `PaymentController::store` tidak mengisi `borrower_id` (kolom NOT
      NULL) dan memakai `paid_amount` yang tidak ada di tabel `penalties`.

### 4.2 Tinggi — fitur salah
- [x] **B12** Link perpanjangan `<a href>` GET ke route POST di
      `bookings/index:84`, `bookings/show:71`, `booking-card:39`,
      `active-loan-card:81`. Diganti ke route GET form
      `borrower.bookings.extension.form`; submit lewat form POST.
- [x] **B13** Route ganda `POST penalties/{penalty}/payments` di
      `routes/web.php:71` dan `:73`; hapus yang stub setelah fitur pembayaran
      diputuskan.
- [x] **B14** Tombol favorit mati: tambahkan `data-favorite-toggle`, perbaiki
      `FavoriteController` agar menerima request JSON tanpa `asset_id`
      (asset sudah di route), dan pindahkan `<button>` keluar dari `<a>`.
- [x] **B15** Tombol "Cek ketersediaan" (`catalog/show.blade.php:92`,
      `data-toggle="calendar"`) tidak punya handler JS. Diganti anchor ke
      `#availability-calendar`.
- [x] **B16** Progres dot booking tidak kumulatif
      (`bookings/index.blade.php:71`): `approved` hanya 1 dot, seharusnya 2.
- [x] **B17** `PenaltyController::show:48` membandingkan `$penalty->status`
      (enum) dengan string sehingga `$canPay` selalu `false`. Sekarang memakai
      `PenaltyStatus` dan tombol bayar diganti form POST yang nyata.
- [x] **B18** `PaymentController::installment` memakai status
      `installment_requested` yang tidak ada dan kolom `installment_months`
      yang belum ada. Case enum + migration
      `2026_11_09_000019_add_installment_months_to_penalties_table.php`
      ditambahkan; batas bulan mengikuti setting `installment_max`.
- [x] **B19** `PenaltyController::storePayment/installment` adalah stub yang
      hanya redirect. Keduanya dihapus; route sudah memakai
      `PaymentController` yang menyimpan data. `submitProof` tetap dipakai dan
      benar-benar menyimpan bukti.

### 4.3 Sedang — konten & konsistensi
- [x] **B20** Key lang hilang di modul penalti dan borrower. Diperiksa ulang
      dengan skrip pemeriksa key: 0 key statis hilang.
- [x] **B21** Sidebar notifikasi memakai data hardcoded (`Ext. 402`,
      `+62 812-3456-7890`); `$operatorInfo` tidak dikirim controller. Sekarang
      controller mengirim nilai dari `SettingService`.
- [x] **B22** Sinkronkan semua key terjemahan statis yang tersisa agar tidak ada
      `__('ui.…')` mentah di HTML. Ditambah test regresi pemindai key.

### 4.4 Rendah / keputusan produk
- [x] **B23** Pemilik memilih menyelesaikan alur pembayaran/denda (bukan
      menyembunyikannya). Alur nyata sudah lengkap: buat pembayaran + unggah
      bukti, detail pembayaran, dan pengajuan cicilan. Route denda/pembayaran
      dipindah ke grup `auth` + `role:borrower` tanpa `active` agar borrower
      yang ditangguhkan tetap bisa melunasi denda.
- [x] **B24** Cek ulang screenshot Beranda lama: gambar rusak sudah tidak
      reproducible; semua `src` gambar di `/borrower` mengembalikan 200.

---

## 5. Rencana Eksekusi Batch 1

Urutan kerja (satu commit per bug agar mudah di-review):

1. [x] B01 — perbaiki `asset-bundle-list.blade.php`, uji `/borrower/catalog/7`.
2. [x] B02 — perbaiki `asset-card.blade.php`, uji render Beranda + Katalog.
3. [x] B03 — perbaiki tiga view peminjaman, uji daftar & detail.
4. [x] B04 — lengkapi lang + view notifikasi, uji halaman.
5. [x] B05 — samakan kelas CSS filter, uji visual dan cek kelas lain.
6. [x] Regression: `php artisan test`, `php artisan view:cache`, crawl HTTP ulang.

### 5.0 Hasil Batch 1 (2026-11-09)

- `php artisan test tests/Feature/Borrower/UiRegressionTest.php` →
  `5 passed (18 assertions)`.
- `php artisan test` → `148 passed (544 assertions)`.
- `php artisan view:cache` → sukses.
- Crawl: `/borrower/catalog/7` 200, raw key notifikasi 0, tombol Batalkan tampil,
  7 elemen `ldr-status-tabs__tab`, CTA available muncul, "0 unit tersedia" hilang.
- Catatan: `NotificationController.php` tidak perlu diubah; label grup
  diterjemahkan di view.
- Bukti lengkap: `docs/history/2026-11-09-bug-ui-batch-1.md`.

### 5.1 Perintah verifikasi batch 1

```bash
php artisan test
php artisan view:cache
php artisan view:clear

# login seeded user lalu cek status HTTP
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/borrower
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/borrower/catalog/7
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/borrower/bookings
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/borrower/notifications
```

Pemeriksaan isi (bukan hanya status):

- HTML `/borrower` tidak memuat `<strong>0</strong> unit tersedia` untuk aset
  `available` dan memuat `ldr-asset-card__cta--available`.
- HTML `/borrower/notifications` tidak memuat string `ui.notification.` dan tidak
  memuat `>today<` / `>earlier<`.
- HTML `/borrower/bookings` memuat tombol Batalkan untuk booking `pending`.
- HTML `/borrower/catalog/7` memuat badge kondisi dan tidak ada `TypeError`.

### 5.2 Definisi selesai batch 1

- [x] Kelima bug berstatus `SELESAI` di tabel todo.
- [x] `php artisan test` hijau, jumlah test tidak berkurang.
- [x] `php artisan view:cache` sukses.
- [x] Halaman yang disebut lolos status dan pemeriksaan isi di §5.1.
- [x] Laporan singkat berisi hasil nyata perintah test (bukan klaim).
- [x] Tidak ada perubahan skema database, route, atau business logic di luar
      cakupan lima bug.

### 5.3 Hasil Batch 2 (2026-11-09)

Batch 2 menutup B06, B07, B08, B09, B11, B13.

- `php artisan test tests/Feature/Borrower/UiRegressionTest.php` →
  `8 passed (42 assertions)`.
- `php artisan test` → `151 passed (568 assertions)`.
- `php artisan view:cache` → sukses.
- Crawl login `ani@lendora.test`:
  `/borrower/penalties` 200, `/borrower/penalties/2` 200,
  `/borrower/penalties/2/document/bap` 200,
  `/borrower/penalties/2/document/receipt` 200,
  `/borrower/two-factor` 200, `/borrower/payments/2` 200.
- Tidak ada raw key `ui.penalty.` / `ui.payment.` / `ui.profile.` pada halaman
  tersebut.
- `php artisan route:list --path=borrower` menunjukkan hanya satu route
  `POST borrower/penalties/{penalty}/payments`.
- Bukti lengkap: `docs/history/2026-11-09-bug-ui-batch-2.md`.

### 5.4 Hasil Batch 3 (2026-11-09)

Batch 3 menutup B14, B15, B16, B20, B21, B22, B24.

- `php artisan test tests/Feature/Borrower/UiRegressionTest.php` →
  `13 passed (62 assertions)`.
- `php artisan test` → `156 passed (588 assertions)`.
- `php artisan view:cache` → sukses.
- Pemeriksa key statis: `0 missing of 738`.
- Crawl borrower + admin: 23 halaman 200, 0 raw key `ui.*`.
- `/borrower`: favorite toggle 12, dot `is-done` 8, semua `src` gambar 200.
- Bukti lengkap: `docs/history/2026-11-09-bug-ui-batch-3.md`.

### 5.5 Hasil Batch 4 (2026-11-09)

Batch 4 menutup B10 dan B12 (alur perpanjangan peminjaman).

- `php artisan test tests/Feature/Borrower/BookingFlowTest.php` →
  `10 passed (43 assertions)`.
- `php artisan test` → `160 passed (606 assertions)`.
- `php artisan view:cache` → sukses. Pemeriksa key: `0 missing of 745`.
- Crawl `/borrower/bookings/9` dan `/borrower/bookings/9/extension-form` → 200,
  0 raw key.
- Bukti lengkap: `docs/history/2026-11-09-bug-ui-batch-4.md`.

### 5.6 Hasil Batch 5 (2026-11-09)

Batch 5 menutup B17–B19 dan B23 (pembayaran denda + cicilan).

- Migration baru:
  `2026_11_09_000019_add_installment_months_to_penalties_table.php` (dijalankan,
  `DONE`).
- `php artisan test tests/Feature/Borrower/PenaltyPaymentTest.php` →
  `7 passed (29 assertions)`.
- `php artisan test` → `170 passed (646 assertions)`.
- `php artisan view:cache` → sukses. Pemeriksa key: `0 missing of 758`.
- Crawl `/borrower/penalties`, `/borrower/penalties/1`,
  `/borrower/payments/1` → 200, 0 raw key. Form bayar dan form cicilan tampil.
- Bukti lengkap: `docs/history/2026-11-09-bug-ui-batch-5.md`.

---

## 6. Todo Tracker

| ID | Prioritas | Status | File utama | Uji |
|---|---|---|---|---|
| B01 | Kritis | `SELESAI` | `resources/views/components/borrower/asset-bundle-list.blade.php` | HTTP 200 `/borrower/catalog/7` |
| B02 | Tinggi | `SELESAI` | `resources/views/components/asset-card.blade.php` | unit tersedia > 0, CTA available |
| B03 | Tinggi | `SELESAI` | `resources/views/borrower/bookings/index.blade.php`, `show.blade.php`, `components/borrower/booking-card.blade.php` | tombol Batalkan tampil |
| B04 | Sedang | `SELESAI` | `resources/views/borrower/notifications/index.blade.php`, `app/Http/Controllers/Borrower/NotificationController.php`, `lang/id/ui.php` | tidak ada raw key |
| B05 | Sedang | `SELESAI` | `resources/views/borrower/bookings/index.blade.php`, `public/css/lendora.css` | chip filter ter-style |
| B06 | Kritis | `SELESAI` | `resources/views/borrower/penalties/index.blade.php`, `show.blade.php` | halaman penalti 200 |
| B07 | Kritis | `SELESAI` | `resources/views/borrower/penalties/show.blade.php`, `document.blade.php`, `app/Services/PaymentService.php` | label pembayaran valid |
| B08 | Kritis | `SELESAI` | `resources/views/borrower/two-factor/edit.blade.php` (baru) | `/borrower/two-factor` 200 |
| B09 | Kritis | `SELESAI` | `resources/views/borrower/payments/show.blade.php` (baru) | `/borrower/payments/{id}` 200 |
| B11 | Kritis | `SELESAI` | `app/Http/Controllers/Borrower/PaymentController.php` | payment tersimpan |
| B13 | Tinggi | `SELESAI` | `routes/web.php` | satu route payments |
| B14 | Tinggi | `SELESAI` | `components/asset-card.blade.php`, `catalog/show.blade.php`, `FavoriteController.php`, `public/js/borrower.js`, `public/css/borrower.css` | POST JSON toggle |
| B15 | Tinggi | `SELESAI` | `resources/views/borrower/catalog/show.blade.php` | anchor ke kalender |
| B16 | Sedang | `SELESAI` | `resources/views/borrower/bookings/index.blade.php` | 8 dot done |
| B20 | Sedang | `SELESAI` | `lang/id/ui.php`, `lang/id/messages.php` | 0 key hilang |
| B21 | Sedang | `SELESAI` | `app/Http/Controllers/Borrower/NotificationController.php`, `notifications/index.blade.php` | kontak dari settings |
| B22 | Sedang | `SELESAI` | `lang/id/ui.php` | test pemindai key |
| B24 | Rendah | `SELESAI` | data seed/asset | semua gambar 200 |
| B10 | Tinggi | `SELESAI` | `resources/views/borrower/bookings/extension-form.blade.php`, `BookingService.php` | submit perpanjangan tersimpan |
| B12 | Tinggi | `SELESAI` | 4 view tombol + `BookingExtensionController.php`, `routes/web.php` | GET form 200, submit POST |
| B17 | Kritis | `SELESAI` | `PenaltyController.php`, `penalties/show.blade.php` | form bayar tampil, POST simpan |
| B18 | Tinggi | `SELESAI` | `PenaltyStatus.php`, migration `000019`, `PaymentController.php`, `Penalty.php` | cicilan 3 bulan tersimpan, 5 ditolak |
| B19 | Tinggi | `SELESAI` | `PenaltyController.php`, `PaymentController.php` | stub dihapus, data tersimpan |
| B23 | Rendah | `SELESAI` | `routes/web.php`, `penalties/show.blade.php` | alur nyata, suspended tetap bisa bayar |

---

## 7. Risiko & Catatan

- B03 hanya memperbaiki visibilitas tombol; link perpanjangan yang salah method
  tetap rusak sampai B12 dikerjakan. Ini disengaja agar batch 1 tetap kecil.
- B04 menambah key terjemahan; tidak mengubah data notifikasi.
- B05 menyentuh CSS bersama (`lendora.css`). Periksa area admin yang memakai
  `ldr-status-tab*` sebelum commit.
- Bug borrower ringan (favorit, kalender, progres dot, key terjemahan, kontak
  operator) ditutup di batch 3; perpanjangan di batch 4; pembayaran/cicilan di
  batch 5. Tidak ada backlog bug tersisa dari audit.
- Setiap perbaikan wajib disertai bukti output test dan crawl; tidak boleh ada
  klaim "sudah beres" tanpa bukti.

---

## 8. Batch 6 — Kelengkapan CSS & Halaman Pengajuan

Status: `SELESAI`. Bukti lengkap di
`docs/history/2026-11-09-bug-ui-batch-6.md`.

| Item | Status | Bukti |
|---|---|---|
| CSS komponen borrower (denda, booking, bantuan, notifikasi, QR, cetak, bundel) | `SELESAI` | crawl 15 halaman 200, 0 raw key |
| CSS atom bersama & badge | `SELESAI` | pemeriksa kelas: 0 kelas statis hilang |
| `/borrower/bookings/create` HTTP 500 (`$conflict`) | `SELESAI` | 200, input `assets[]` terkirim |
| Durasi pengajuan tidak dihitung | `SELESAI` | `borrower.js` `data-duration-display` |
| Tombol "Simpan Draf" tanpa aksi | `SELESAI` | tombol dihapus |
| Regresi QR pass | `SELESAI` | `UiRegressionTest` 15 passed |
| Suite penuh | `SELESAI` | 172 passed (655 assertions) |
