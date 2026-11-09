# Redesign Autentikasi dan Onboarding

Tanggal: 2026-11-08
Jenis pekerjaan: frontend
Status: selesai

## Tujuan

Menyatukan seluruh halaman autentikasi dan onboarding dengan arah desain
orange + putih, tanpa gradient, glow, atau glassmorphism.

Ruang lingkup: login, register, lupa sandi, atur ulang sandi, konfirmasi sandi,
verifikasi surel, dan onboarding.

## Perubahan

### Layout auth

- `layouts/guest.blade.php`
  - Satu layout untuk semua halaman auth; prop `split` dihapus.
  - Brand memakai ikon aperture dalam kotak orange, nama aplikasi, dan tagline.
  - Kartu putih dengan border, radius, dan shadow tipis.
  - Footer berisi tautan Tata Tertib Lab, Bantuan Admin, dan versi.
  - Skrip toggle kata sandi dipertahankan.

### Halaman

- `auth/login.blade.php`: struktur baru tanpa panel foto gelap. Intro, form,
  meta "ingat saya"/lupa sandi, tombol, tautan daftar, dan callout verifikasi.
- `auth/register.blade.php`: struktur yang sama; field, validasi, dan toggle
  kata sandi tidak berubah.
- `auth/forgot-password.blade.php`, `auth/reset-password.blade.php`,
  `auth/confirm-password.blade.php`: form memakai kelas
  `ldr-auth__form` agar jarak field konsisten.
- `auth/verify-email.blade.php`: tidak diubah; otomatis mengikuti layout baru.

### CSS

- `public/css/ldr-login.css`: ditulis ulang.
  - Menghapus seluruh gradient, palet gelap, dan bayangan besar.
  - Menambah komponen `ldr-auth__shell`, brand, panel, intro, form, callout,
    switch, dan footer.
  - Input 48px, tombol 48px, fokus orange, dan tampilan mobile di bawah 480px.
- `public/css/onboarding.css`
  - Overlay foto menjadi warna solid, bukan gradient.
  - `backdrop-filter: blur` dihapus dari pill dan label.
  - Kelas `ldr-onboarding__slides` yang sebelumnya belum didefinisikan kini
    memakai grid.
- `public/css/borrower.css`
  - Placeholder kartu aset memakai warna solid, bukan pola gradient.

## File Tersentuh

Diubah:

- `resources/views/layouts/guest.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/auth/confirm-password.blade.php`
- `public/css/ldr-login.css`
- `public/css/onboarding.css`
- `public/css/borrower.css`

Dibuat:

- `docs/history/2026-11-08-redesign-auth.md`

Tidak ada route, controller, validasi, atau tes yang diubah.

## Verifikasi

```
php artisan view:cache
INFO  Blade templates cached successfully.

php artisan test --compact
Tests:    129 passed (454 assertions)
Duration: 9.11s
```

Pemeriksaan tambahan: tidak ada lagi `linear-gradient`, `radial-gradient`, atau
`backdrop-filter` di `public/css/` selain teks komentar.

## Risiko / Catatan Lanjutan

- Verifikasi visual browser belum dilakukan; mesin tidak punya browser headless.
- Halaman verifikasi surel masih memakai dua tombol berdampingan; perlu UAT
  pada layar sempit.
- Logo file di `public/images/` tidak lagi dipakai halaman auth. File tidak
  dihapus karena keputusan varian logo masih terbuka.
- Register belum punya field NIM/prodi/WhatsApp seperti mockup M2 karena
  backend belum menyediakan kolomnya.
