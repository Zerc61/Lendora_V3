# Perbaikan Foto Produk Tidak Muncul

Tanggal: 2026-11-09
Jenis pekerjaan: konfigurasi storage
Status: selesai

## Masalah

Halaman katalog, daftar aset, dan detail booking memakai
`Storage::url($photo->path)` sehingga menghasilkan URL `/storage/...`.
Berkas foto ada di `storage/app/public/assets/...`, tetapi `public/storage`
belum ada, sehingga semua URL foto mengembalikan 404.

## Perbaikan

Menjalankan:

```
php artisan storage:link
```

Hasil:

```
INFO  The [public/storage] link has been connected to [storage/app/public].
```

Simlink: `public/storage -> storage/app/public`.

Tidak ada perubahan kode, data, atau seeder.

## Verifikasi

- `AssetPhoto` berisi 27 baris; contoh path `assets/LDR-CAM-0001/photo.jpg`.
- Server lokal dijalankan sementara di port 8123:

```
curl -s -o /dev/null -w "foto:%{http_code} type:%{content_type}\n" \
  http://127.0.0.1:8123/storage/assets/LDR-CAM-0001/photo.jpg
foto:200 type:image/jpeg
```

## Catatan

- Setelah deploy atau clone baru, jalankan `php artisan storage:link` sekali.
- Jika memakai `php artisan serve`, symlink langsung berlaku tanpa restart.
- Tes otomatis memakai `Storage::fake`, jadi tidak menangkap masalah symlink
  ini; verifikasi dilakukan lewat HTTP.
