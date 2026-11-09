# Lendora V3 — Kumpulan Prompt Google Stitch

> Panduan + 24 prompt siap-tempel untuk membuat seluruh mockup UI Lendora V3 di
> **Google Stitch** (https://stitch.withgoogle.com). Prompt ditulis dalam bahasa
> Inggris karena Stitch memberikan hasil paling akurat dengan instruksi Inggris;
> label teks di dalam desain tetap bahasa Indonesia agar mockup terasa nyata.
> Pasangkan dengan `Design.md` sebagai acuan token (warna, tipografi, status).

---

## 0. Cara Pakai di Stitch

1. Buka **stitch.withgoogle.com** dan login dengan akun Google.
2. Buat project baru. Pilih mode **Mobile** untuk seri `M*` (borrower) dan mode
   **Web** untuk seri `W*` (admin console) — buat dua project terpisah agar
   tema tidak tercampur.
3. Tempel **Prompt Tema (T0)** di awal sesi / kolom theme, lalu tempel prompt
   layar **satu per satu** (satu prompt = satu layar).
4. Iterasi dengan instruksi kecil ("make the header sticky", "change grid to 3
   columns") — jangan mengulang seluruh prompt.
5. Hasil akhir: **Export to Figma** atau copy code untuk referensi implementasi
   Blade/CSS.

**Tips umum**

- Jangan meminta logika/data dinamis di Stitch ("klik tombol lalu menyimpan") —
  Stitch hanya membuat tampilan statis.
- Selalu sebut warna hex utama dan gaya di setiap prompt layar; sesi baru tidak
  selalu mengingat konteks.
- Kalau hasil terlalu ramai, tambahkan kalimat: *"plenty of whitespace, flat
  design, no gradients, no shadows heavier than subtle"*.
- Satu layar rumit lebih baik dipecah dua kali generate (versi daftar, lalu
  versi detail) daripada satu prompt panjang.

---

## 1. Prompt Tema — T0 (tempel lebih dulu di setiap project)

```
Design system for "Lendora", a clean professional equipment-lending platform
(photography gear rental for an Indonesian campus unit).

Style rules:
- White (#FFFFFF) background, light warm gray surfaces (#F5F5F5), borders #E5E7EB.
- Vibrant orange #F97316 used ONLY for primary actions, active nav items, and
  brand identity. Hover state: #EA580C. Soft orange tint #FFF7ED for highlights.
- Dark neutral text #262626, secondary text #6B7280.
- Semantic colors: green #16A34A (available/done), amber #D97706 (pending),
  sky #0284C7 (approved/active), violet #7C3AED (reserved), red #DC2626
  (rejected/damaged), all shown as small pill badges with light tinted backgrounds.
- Typography: Plus Jakarta Sans for headings, Inter for body. Clean, flat,
  professional. Rounded corners 12-14px, subtle shadows only.
- No gradients, no glassmorphism, no decorative illustrations, generous whitespace.
- All UI text in Bahasa Indonesia (e.g. "Ajukan Peminjaman", "Tersedia",
  "Menunggu Persetujuan").
```

---

## 2. Prompt Borrower — Mobile (project Mobile)

### M1 — Login
```
Mobile app login screen for Lendora, an equipment lending app for Indonesian
campus photography gear. Centered orange wordmark logo "Lendora", friendly
headline "Selamat datang kembali", subtitle "Masuk untuk mengajukan peminjaman
peralatan". Email field labeled "Email", password field with show/hide toggle
labeled "Kata Sandi". Large orange primary button "Masuk" (44px tall, rounded
10px). Below: link "Lupa kata sandi?" and secondary line "Belum punya akun?
Daftar". White background, clean professional, generous whitespace.
```

### M2 — Register
```
Mobile app registration screen for Lendora. Headline "Buat Akun", subtitle
"Mendaftar sebagai peminjam peralatan". Form fields stacked: "Nama Lengkap",
"Email", "Nomor Telepon/WhatsApp", "Kata Sandi" with helper text "Minimal 8
karakter". Orange primary button "Buat Akun". Small footnote: "Dengan mendaftar
kamu menyetujui ketentuan peminjaman organisasi." Back arrow top-left. Clean
white background, orange accents only on button and links.
```

### M3 — Beranda (Home)
```
Mobile home screen for Lendora borrower app. Top greeting header: "Halo, Ani"
(Plus Jakarta Sans bold 28px) with small subtext "Unit Kampus — Selamat
beraktivitas". Below: an active loan card with soft orange tint background
(#FFF7ED), label "PEMINJAMAN AKTIF", camera photo thumbnail, "Sony A7 III",
mono code "LDR-BK-2026-0007", amber chip "Kembalikan besok!", orange button
"Lihat Detail". Section "Rekomendasi untuk kamu": horizontal scroll of asset
cards (4:3 photo, name, category chip, green "Tersedia" badge). Section
"Pengumuman": simple white card with short text. Fixed bottom navigation with
5 items: Beranda (active, orange), Katalog, Peminjaman Saya, Notifikasi,
Profil — icons only 24px with tiny labels.
```

### M4 — Katalog
```
Mobile catalog screen for Lendora borrower app. Sticky search bar "Cari kamera,
lensa..." with search icon. Horizontal scrollable filter chips: "Semua" (active,
orange tint), "Kamera Mirrorless", "Lensa", "Tripod", "Lampu". Result count
text "24 unit tersedia". 2-column grid of asset cards: 4:3 camera photo,
status badge overlaid top-right (green "Tersedia" or gray "Dipinjam"), asset
name like "Sony A7 III", small mono code "LDR-CAM-0001", category chip.
Clean white cards with subtle border #E5E7EB, 14px rounded corners. Fixed
bottom navigation (5 items, Katalog active in orange).
```

### M5 — Detail Aset
```
Mobile asset detail screen for Lendora. Full-width photo gallery at top
(4:3, swipe dots indicator), back arrow overlaid. Content: asset name "Sony
A7 III" (bold), mono code "LDR-CAM-0001", category chip "Kamera Mirrorless",
green badge "Tersedia" and green outline badge "Kondisi: Baik". Specs list
in white card: Merek "Sony", Model "A7 III", Deskripsi short text. A 14-day
mini availability strip: 14 small rounded squares, green = free, gray =
booked, label "Ketersediaan 14 hari ke depan". Location row with pin icon
"Lemari A1 — Ruang Operator". Sticky bottom bar with full-width orange button
"Ajukan Peminjaman". No bottom nav on this screen.
```

### M6 — Form Ajukan Peminjaman
```
Mobile booking request form for Lendora, presented as a page titled "Ajukan
Peminjaman". Top: small unit summary row (thumbnail, "Sony A7 III", mono
"LDR-CAM-0001"). Form: two date fields side by side "Tanggal Mulai" and
"Tanggal Selesai" showing "12 Nov 2026" and "14 Nov 2026", helper text
"Maksimal 7 hari"; required textarea "Tujuan peminjaman" with placeholder
"Contoh: tugas dokumentasi seni budaya". Info card (orange tint): "Ketersediaan
dicek otomatis saat pengajuan dikirim". Orange primary button "Kirim Pengajuan".
Show a validation error example below dates in red: "Unit sudah dibooking
12–14 Nov — pilih tanggal lain". Clean, minimal, white background.
```

### M7 — Peminjaman Saya (list)
```
Mobile screen "Peminjaman Saya" for Lendora borrower. Top segmented tabs:
"Semua" (active), "Menunggu", "Aktif", "Selesai". List of booking cards, each
with: mono booking code "LDR-BK-2026-0007" + status pill badge (amber
"Menunggu Persetujuan", sky "Disetujui", green "Dipinjam", gray "Dikembalikan"),
date range "12–14 Nov 2026", small row of item thumbnails with names, and a
5-dot mini progress timeline (Diajukan → Disetujui → Diambil → Dikembalikan →
Selesai). One card shows ghost button "Batalkan". Fixed bottom nav with
"Peminjaman Saya" active. Clean cards, generous spacing.
```

### M8 — Detail Peminjaman
```
Mobile booking detail screen for Lendora. Header: mono code "LDR-BK-2026-0007"
large + green status badge "Dipinjam". Vertical status timeline with 5 steps
(Diajukan, Disetujui, Diambil, Dikembalikan, Selesai): completed steps filled
orange with timestamps "12 Nov 09:14", current step pulsing orange, future
steps gray. Card "Jadwal": Mulai 12 Nov 2026, Selesai 14 Nov 2026, Batas
kembali 14 Nov 2026 16:00. Card "Item": camera thumbnail, "Sony A7 III",
condition text "Saat keluar: baik, lengkap 2 baterai". Card "Catatan Serah
Terima" with short operator note. Ghost red-tinted button example "Batalkan
Pengajuan" (show it as if status were pending). Clean white layout.
```

### M9 — Notifikasi
```
Mobile notifications screen for Lendora. Header "Notifikasi" with right-aligned
text button "Tandai semua dibaca". Grouped list: section label "Hari ini" then
rows; each row has a leading circular icon (orange check for approval, red x
for rejection, amber bell for reminder, gray wrench for inspection), title
like "Pengajuan disetujui", one-line body "Kamera Sony A7 III siap diambil
12 Nov", time "09:14", and a small blue unread dot. Section "Sebelumnya" with
2 more rows (read state, no dot). Empty state variant at bottom as annotation:
bell illustration + "Belum ada notifikasi". Clean list, dividers #E5E7EB.
```

### M10 — Profil
```
Mobile profile screen for Lendora borrower. Top: circular avatar with initials
"AN" on orange tint background, name "Ani Rahmawati", chip "Borrower" (orange
tint). Menu list in white rounded card: "Data Diri" (user icon), "Ubah Kata
Sandi" (lock icon), "Riwayat Ringkas" (clock icon, value "12 transaksi"),
"Keluar" (red text, logout icon). Each row: icon left, label, chevron right,
dividers. Small version footer text "Lendora V3 · v1.0". Fixed bottom nav with
Profil active. Clean, professional, white background.
```

---

## 3. Prompt Admin — Web Desktop (project Web)

### W1 — Dashboard Operator
```
Web dashboard for Lendora admin area (1440px wide). Left sidebar 256px, white,
border right: orange logo "Lendora", grouped menu (Utama: Dashboard active
with orange left indicator; Inventaris: Aset, Kategori; Peminjaman: Antrean
Persetujuan, Semua Transaksi; Operasional: Serah Terima, Pemeriksaan,
Maintenance), footer with user "Budi — Operator". Topbar: page title
"Dashboard Operator", search field, bell icon with red dot, avatar. Content:
4 stat cards in a row (Pengajuan Baru 5, Pickup Hari Ini 3, Jatuh Tempo 2,
Terlambat 1) each with small icon and orange number. Below: table "Antrean
Hari Ini" columns Kode (mono), Peminjam, Unit, Jadwal, Status (pill badges),
Aksi (small orange "Setujui" button + red outline "Tolak"). Clean professional,
flat, white background, subtle borders.
```

### W2 — Dashboard Organization Admin
```
Web dashboard variant for Lendora Organization Admin (1440px). Same sidebar
style but menu: Dashboard active, Inventaris (Aset, Kategori), Peminjaman
(Antrean Persetujuan, Semua Transaksi, Kalender), Operasional, Organisasi
(Pengguna & Role, Laporan & Analitik, Pengaturan). Topbar title "Dashboard
Organisasi". Content row 1: 4 KPI cards — "Utilisasi Aset 68%", "Transaksi
Aktif 12", "Keterlambatan 3", "Maintenance Berjalan 2". Row 2: bar chart
"Utilisasi per Kategori" (5 categories, orange bars) and line chart "Transaksi
30 Hari" (orange line). Row 3: activity list card "Aktivitas Terbaru" with
mono action codes like booking.approved + timestamps. Flat, clean, no gradients.
```

### W3 — Daftar Aset
```
Web admin table page for Lendora "Inventaris — Aset" (1440px). Sidebar same
as before with Aset active. Topbar: breadcrumb "Inventaris / Aset", orange
primary button "+ Tambah Aset". Filter bar: search input "Cari nama, kode,
serial...", select dropdowns "Kategori" and "Status", active filter chip
"Kamera Mirrorless ×". Data table columns: Foto (40px thumb), Kode (mono),
Nama, Kategori, Status (pill badges: green Tersedia, violet Direservasi,
sky Dipinjam, amber Maintenance, gray Nonaktif), Kondisi (badge), Lokasi,
Aksi (eye/pencil icons). 8 realistic rows (Sony A7 III, Canon EOS R6, Lensa
24-70mm, Tripod Manfrotto, etc). Pagination footer "Menampilkan 1–8 dari 24"
with page numbers.
```

### W4 — Form Tambah/Edit Aset
```
Web admin form page for Lendora "Tambah Aset" (1440px). Sidebar same, breadcrumb
"Inventaris / Aset / Tambah". Two-column form in white cards. Left card
"Identitas": Nama Peralatan*, Kategori* (cascading select "Kamera > Kamera
Mirrorless"), Merek, Model, Serial Number, Deskripsi textarea. Right card
"Operasional": Status (select, default Tersedia), Kondisi (select Baik),
Lokasi, Tanggal Perolehan (date), Harga Perolehan (Rp, integer format
"12.500.000"), Catatan. Full-width card "Foto": drag-and-drop upload zone
with dashed border, 4 photo thumbnails grid, primary radio marker on first,
drag handle icons. Footer: secondary button "Batal" + orange "Simpan Aset".
Show one inline validation error: "Serial number sudah terdaftar".
```

### W5 — Kategori
```
Web admin page for Lendora "Inventaris — Kategori". Sidebar with Kategori
active. Breadcrumb "Inventaris / Kategori", orange button "+ Tambah Kategori".
Content: tree list card with expand/collapse arrows — root "Kamera Mirrorless"
(badge "8 aset") with children "Full-frame" and "APS-C"; "Lensa" (6 aset) with
children "Zoom" and "Prime"; "Tripod" (4 aset); "Lampu" (3 aset). Each row:
name (bold for parents, indented muted for children), slug in mono, asset
count badge, edit + delete icons. One row shows disabled delete with tooltip
"Tidak bisa dihapus: masih dipakai 8 aset". Clean flat table-like tree.
```

### W6 — Antrean Persetujuan
```
Web admin approval queue for Lendora "Peminjaman — Antrean Persetujuan".
Sidebar with Antrean Persetujuan active. Content: stack of pending request
cards. Each card: header mono code "LDR-BK-2026-0009" + amber badge "Menunggu
Persetujuan"; body rows — peminjam "Rara (Semester 3)", rentang "13–15 Nov
2026", tujuan "Dokumentasi acara seni", item list with thumbnails and mono
codes. One card shows a red danger banner: "Konflik jadwal: LDR-CAM-0001 sudah
dibooking 13–14 Nov oleh LDR-BK-2026-0007 (Ani)". Card actions right-aligned:
red outline button "Tolak" (with reason modal icon) + orange button "Setujui".
One card shows disabled approve with muted text "Menunggu persetujuan
Organization Admin". Flat clean cards, white background.
```

### W7 — Detail Booking (admin)
```
Web admin booking detail page for Lendora (1440px). Breadcrumb "Peminjaman /
LDR-BK-2026-0007". Header: large mono code + green badge "Dipinjam" + date
range "12–14 Nov 2026" + borrower chip "Ani Rahmawati — Borrower". Layout two
columns: left (60%) — card "Item" table with columns Unit (thumb + name +
mono code), Kondisi Keluar, Kondisi Masuk, Status item badge; card "Tujuan &
Catatan" with purpose text. Right (40%) — card "Timeline" vertical steps with
timestamps and actor names (Diajukan 11 Nov 20:05 oleh Ani, Disetujui 11 Nov
21:30 oleh Budi, Diambil 12 Nov 09:14 oleh Budi, upcoming steps gray); card
"Riwayat Serah Terima" with 2 log rows (pickup, return pending); action buttons
contextual: orange "Catat Return". Flat professional dashboard style.
```

### W8 — Form Serah Terima
```
Web admin handover form for Lendora "Operasional — Serah Terima". Sidebar with
Serah Terima active. Top: booking picker search "Cari kode booking..." with
selected result "LDR-BK-2026-0007 — Ani Rahmawati"; segmented toggle "Pickup"
(active orange) / "Return". Card "Checklist Item" — one row per unit:
checkbox, thumbnail, name + mono code, required input "Kondisi keluar"
(prefilled "Baik, lengkap 2 baterai, 1 kartu memori"), optional notes field.
Card "Catatan Umum": textarea. Footer: secondary "Batal" + orange "Simpan Serah
Terima". Helper text: "Menyimpan akan mengubah status transaksi menjadi aktif
dan aset menjadi dipinjam." Clean single-column form, 900px content width.
```

### W9 — Pemeriksaan (Inspection)
```
Web admin inspection page for Lendora "Operasional — Pemeriksaan". Sidebar with
Pemeriksaan active. Top card "Rekam Pemeriksaan": asset search field "Cari
kode/nama unit..." with selected "Sony A7 III — LDR-CAM-0001", optional select
"Konteks booking", radio group "Hasil": "Baik" (green), "Perlu Perbaikan"
(amber), "Rusak" (red), textarea "Catatan temuan", orange button "Simpan
Pemeriksaan". Below: table "Riwayat Pemeriksaan" columns Tanggal, Unit, Pemeriksa,
Hasil (badge), Catatan — 6 rows with mixed results. Info note card: "Hasil
'Perlu Perbaikan' atau 'Rusak' otomatis membuat tugas maintenance dan mengubah
status aset menjadi maintenance." Flat, clean.
```

### W10 — Maintenance
```
Web admin maintenance page for Lendora "Operasional — Maintenance". Sidebar
with Maintenance active. Filter chips: Semua (active), Terjadwal, Berjalan,
Selesai, Dibatalkan. Table columns: Aset (thumb + name + mono code),
Deskripsi (truncated), Teknisi, Status (pill: sky Terjadwal, amber Berjalan,
green Selesai, gray Dibatalkan), Biaya (Rp right-aligned), Mulai, Selesai,
Aksi. Two detail drawer examples side annotation: drawer titled "LDR-CAM-0002
— Ganti layar" with fields Deskripsi, Teknisi "Pak Andi", timeline, buttons
"Mulai" and "Selesaikan" (orange), cost input "Rp 850.000". Flat professional
data table, white cards.
```

### W11 — Pengguna & Role
```
Web admin user management for Lendora "Organisasi — Pengguna & Role". Sidebar
with Pengguna & Role active. Topbar: orange button "+ Tambah Pengguna". Filter:
search + role dropdown. Table columns: Nama (avatar initial + name), Email,
Telepon, Role (pill badges: violet Organization Admin, sky Operator, green
Borrower, amber Admin Prodi, gray Technician), Status (green Aktif / red
Ditangguhkan), Aksi (suspend toggle, edit role). 8 rows realistic Indonesian
names. Modal example "Tambah Pengguna": fields Nama, Email, Telepon, Role
(select of 5 roles), Organisasi (shown only for non-borrower roles), buttons
Batal / Simpan. Note text: "Penangguhan memblokir login tanpa menghapus data."
```

### W12 — Laporan & Analitik
```
Web admin reports page for Lendora "Organisasi — Laporan & Analitik". Sidebar
with Laporan active. Filter bar: date range "1–30 Nov 2026", category select,
outline button "Ekspor CSV" with download icon. KPI row: "Total Peminjaman 42",
"Hari Dipinjam 168", "Utilisasi Rata-rata 64%", "Kerusakan 5". Bar chart
"Utilisasi per Kategori" (orange bars with values). Table "Rekap per Kategori":
Kategori, Total Unit, Hari Dipinjam, Utilisasi % (with tiny inline progress
bar). Two list cards side by side: "Aktivitas Borrower Teratas" (name + count)
and "Kerusakan Terbaru" (unit + date + result badge). Flat, clean, professional.
```

### W13 — Pengaturan Organisasi
```
Web admin settings page for Lendora "Organisasi — Pengaturan". Sidebar with
Pengaturan active. Three stacked white cards. Card 1 "Data Organisasi": Nama
"Unit Kampus SMK Negeri 1", Kode "LDR" (mono, helper "Dipakai sebagai prefiks
kode transaksi"), Alamat, Telepon, Email. Card 2 "Kebijakan Peminjaman":
integer field "Durasi maksimum pinjam (hari)" value 7, rupiah field "Batas
kewenangan operator (Rp)" value 5.000.000, textarea "Pengumuman beranda
borrower". Card 3 "Notifikasi": toggle "Pengingat pengembalian H-1" (on),
toggle "Notifikasi hasil inspeksi" (on). Each card has its own small orange
"Simpan" button bottom-right. Flat clean form layout, 900px content width.
```

---

## 4. Prompt Iterasi Cepat (cheat sheet)

Tempel sesudah layar dasar terbentuk, satu per satu:

```
Make the bottom navigation fixed and always visible.
```
```
Use #F97316 only for the primary button and active item; everything else neutral.
```
```
Show an example of the empty state for this screen: icon, title, one sentence,
and an orange call-to-action button.
```
```
Change the asset grid to 2 columns on mobile with 12px gaps.
```
```
Add a skeleton loading state for this list.
```
```
Make all status indicators small pill badges with light tinted backgrounds,
never just colored text.
```
```
Show the error state where a date range conflicts with an existing booking:
red banner mentioning the unit code and the conflicting booking code.
```
```
Increase touch targets: buttons 44px tall, comfortable spacing for mobile.
```
```
Reduce decoration: no gradients, no heavy shadows, flat clean professional.
```

---

## 5. Batasan Stitch & Lanjutan ke Implementasi

- Stitch menghasilkan **tampilan statis** — badge status, angka, dan baris tabel
  adalah data contoh. Logika nyata (cek konflik jadwal, transisi status, otoritas
  role) dibangun di Laravel sesuai `Design.md` dan paket prompt AI
  (`laravel-ai-prompts/`).
- Setelah mockup disetujui, ekspor ke Figma untuk review pemangku kepentingan,
  lalu terjemahkan ke Blade + CSS dengan token Bagian 10 `Design.md`.
- Jika Stitch menghasilkan gaya yang menyimpang (mis. terlalu gelap atau
  gradient), tempel ulang ringkasan T0 sebagai koreksi sebelum lanjut.
- Konsistensi antar layar lebih penting daripada kecantikan satu layar:
  warna badge status harus identik di mockup borrower dan admin.
