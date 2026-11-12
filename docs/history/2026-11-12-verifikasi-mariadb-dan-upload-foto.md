# Verifikasi MariaDB Nyata dan Upload Foto — 11–12 November 2026

## Tujuan

Membuktikan tiga hal yang sebelumnya hanya diuji di SQLite atau dengan
`Storage::fake()`:

1. Migration dan foreign key berjalan di MariaDB 11.8.6.
2. Kunci baris `lockForUpdate()` benar-benar mencegah dua persetujuan ganda.
3. Upload foto tersimpan di disk `public`, tercatat di database, dan dapat
   diakses lewat `/storage`.

Database uji: `lendora_test` (dibuat khusus, terpisah dari `lendora`).

## Temuan dan perbaikan

### 1. Migration gagal total di instalasi baru MariaDB

`migrate:fresh` berhenti di `0009_create_handovers_table`:

```
SQLSTATE[HY000]: General error: 1005 Can't create table `lendora_test`.`handovers`
(errno: 121 "Duplicate key on write or update")
alter table `handovers` add constraint `1` foreign key (`booking_id`) ...
```

Penyebab: `->index()` setelah `->constrained(...)` membuat nama constraint
menjadi `1`. Tabel kedua yang memakai FK bernama `1` lalu bentrok.

| Rantai | Constraint yang terbentuk |
|---|---|
| `constrained('parents')->restrictOnDelete()->index()` | `CONSTRAINT \`1\`` |
| `constrained('parents')->restrictOnDelete()` | `CONSTRAINT \`children_parent_id_foreign\`` |

Perbaikan: `->index()` dihapus dari lima migration (0008, 0009, 0010, 0011,
0013). MariaDB otomatis membuat index untuk foreign key. Database `lendora`
yang sudah berjalan tidak terpengaruh karena migration tersebut tidak
dijalankan ulang.

### 2. Approve ganda lolos ke pemeriksaan konflik

Uji dua proses bersamaan pada booking yang sama menghasilkan satu sukses dan
satu penolakan, tetapi penolakan kedua berupa pesan konflik yang menyebut
booking itu sendiri.

Penyebab: `assertTransition()` mengembalikan `return` diam-diam ketika status
tujuan sama dengan status sekarang.

Perbaikan:

- `assertTransition()` tidak lagi mengizinkan transisi ke status yang sama.
- `approve()` membaca ulang dan memeriksa status setelah kunci aset diperoleh.

Hasil setelah perbaikan:

```
{"label":"A","outcome":"approved","finished":0.0903}
{"label":"B","outcome":"InvalidStatusTransitionException: Status peminjaman LDR-BK-CONC-0001 tidak dapat diubah dari approved ke approved."}
bookings.status=approved, activity_logs booking.approved=1, assets.status=reserved
```

### 3. Kolom `penalties.status` terlalu pendek

Permintaan cicilan denda menghasilkan HTTP 500 di MariaDB:

```
SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'status'
```

`PenaltyStatus::InstallmentRequested` bernilai `installment_requested` (21
karakter) sedangkan kolom `varchar(20)`. SQLite tidak menegakkan panjang
kolom, sehingga bug tidak terlihat. Database `lendora` juga masih
`varchar(20)`.

Perbaikan: migration baru `2026_11_11_000001_widen_penalties_status.php`
melebarkan kolom menjadi `varchar(30)`.

### 4. Tes registrasi bergantung pada id hasil seeder

`RegistrationTest` mengirim `organization_id => 1`. Di MariaDB,
auto-increment tidak kembali ke 1 setelah rollback, sehingga tes kedua dalam
file yang sama mendapat organisasi ber-id lain dan gagal. Perbaikan: tes
mengambil id organisasi dari database.

## Verifikasi

| Pemeriksaan | Hasil |
|---|---|
| `migrate:fresh --seed` di `lendora_test` | 44 tabel, 70 foreign key, 0 constraint bernama angka |
| PHPUnit dengan `DB_CONNECTION=mysql DB_DATABASE=lendora_test` | 315 passed (1359 assertions) |
| PHPUnit SQLite (default) | 315 passed (1359 assertions) |
| Dua proses approve bersamaan | 1 sukses, 1 ditolak, 1 log, aset `reserved` sekali |
| FK `restrict`: hapus aset/kategori/pengguna yang dipakai | Ditolak, SQLSTATE 23000 / error 1451 |
| FK `cascade`: hapus booking | `booking_items` ikut terhapus, sesuai rancangan |
| Upload foto lewat HTTP (multipart) | Redirect sukses, baris `asset_photos` dibuat |
| Berkas di disk | `storage/app/public/assets/LDR-CAM-0010/...jpg` ada, 738 byte |
| Akses `/storage/...` | HTTP 200, `image/jpeg`, 738 byte, isi identik |
| Symlink `public/storage` | Ada dan menunjuk `storage/app/public` |

Bukti tes dijalankan ke MariaDB (bukan SQLite) diverifikasi lewat general log
server: 379 pernyataan menyentuh `lendora_test`.

## Catatan lanjutan

- Migration baru belum dijalankan di database `lendora`. Selama belum
  dijalankan, permintaan cicilan denda di lingkungan nyata tetap gagal 500.
- Database uji `lendora_test` disimpan untuk verifikasi berikutnya. Hapus
  dengan `DROP DATABASE lendora_test` bila tidak diperlukan.
- Uji beban/N+1 dan perangkat mobile nyata masih di luar cakupan.
