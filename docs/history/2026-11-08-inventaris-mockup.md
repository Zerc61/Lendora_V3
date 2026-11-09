# Inventaris Mockup Lendora V3

Tanggal: 2026-11-08
Jenis pekerjaan: audit (tanpa perubahan kode)
Status: selesai

## Tujuan

Mendata seluruh mockup di `public/Design/mockup/` beserta ketersediaan
screenshot, pengelompokan role, dan catatan desain. Dokumen ini menjadi dasar
pemetaan ke implementasi di
`docs/history/2026-11-08-pemetaan-mockup-implementasi.md`.

## Perubahan

Tidak ada perubahan file mockup atau aset. Hanya dokumen ini yang dibuat.

## Ringkasan Angka

- 49 layar dengan `code.html`.
- 46 file `screen.png`.
- 4 layar tanpa screenshot: B1, B2, B3, B4.
- 1 folder tambahan berisi `screen.png` saja:
  `lendora_favicon_180_siklus.png/` (bukan layar aplikasi).
- 1 folder referensi desain: `lendora_campus_gear/` (berisi `DESIGN.md`).

## Grup Mockup

| Grup | Jumlah | Role utama | Rentang |
|---|---|---|---|
| Borrower mobile | 12 | borrower | M0–M11 |
| Borrower desktop | 9 | borrower | B1–B9 |
| Operator | 5 | operator | O1 (2 varian), O2–O4 |
| Konsol admin bersama | 13 | operator, organization_admin | W1–W13 |
| Admin Prodi | 6 | admin_prodi | P1–P6 |
| Technician | 4 | technician | dashboard, workbench, T3, T4 |
| **Total** | **49** | | |

## Inventaris Lengkap

Kolom screenshot: `ya` = ada `screen.png`, `tidak` = hanya `code.html`.

### Borrower Mobile (12)

| Kode | Folder | Layar | Screenshot |
|---|---|---|---|
| M0 | `m0_onboarding` | Onboarding | ya |
| M1 | `m1_login` | Login | ya |
| M2 | `m2_register` | Register | ya |
| M3 | `m3_beranda` | Beranda | ya |
| M4 | `m4_katalog` | Katalog | ya |
| M5 | `m5_detail_aset` | Detail aset | ya |
| M6 | `m6_form_ajukan_peminjaman` | Form ajukan peminjaman | ya |
| M7 | `m7_peminjaman_saya` | Peminjaman saya | ya |
| M8 | `m8_detail_peminjaman` | Detail peminjaman | ya |
| M9 | `m9_notifikasi` | Notifikasi | ya |
| M10 | `m10_profil` | Profil | ya |
| M11 | `m11_pembayaran_denda_keterlambatan` | Pembayaran denda | ya |

Ciri desain: layout ponsel, bottom navigation 5 tab (Beranda, Katalog,
Peminjaman Saya, Notifikasi, Profil), kartu aset dengan foto, badge status,
form singkat, timeline status.

### Borrower Desktop (9)

| Kode | Folder | Layar | Screenshot |
|---|---|---|---|
| B1 | `b1_beranda_portal_mahasiswa_desktop` | Beranda portal mahasiswa | tidak |
| B2 | `b2_katalog_peralatan_borrower_desktop` | Katalog peralatan | tidak |
| B3 | `b3_peminjaman_saya_borrower_desktop` | Peminjaman saya | tidak |
| B4 | `b4_form_ajukan_peminjaman_borrower_desktop` | Form ajukan peminjaman | tidak |
| B5 | `b5_detail_unit_peralatan_borrower_desktop` | Detail unit | ya |
| B6 | `b6_detail_booking_tracking_sirkulasi_borrower_desktop` | Detail booking + tracking | ya |
| B7 | `b7_notifikasi_pengumuman_lab_borrower_desktop` | Notifikasi + pengumuman lab | ya |
| B8 | `b8_profil_mahasiswa_pengaturan_akun_borrower_desktop` | Profil + pengaturan akun | ya |
| B9 | `b9_pusat_bantuan_regulasi_sop_penyelesaian_denda_borrower_desktop` | Pusat bantuan, regulasi, SOP, denda | ya |

Ciri desain: portal desktop dengan sidebar/topbar, tabel dan panel lebar,
katalog grid multi-kolom. Berbeda dari M-series yang mobile-first.

### Operator (5)

| Kode | Folder | Layar | Screenshot |
|---|---|---|---|
| O1a | `o1_kiosk_scanner_loket_cepat_1` | Kiosk scanner loket (varian 1) | ya |
| O1b | `o1_kiosk_scanner_loket_cepat_2` | Kiosk scanner loket (varian 2) | ya |
| O2 | `o2_kalender_ketersediaan_unit_gantt_chart_jadwal_pinjam` | Kalender ketersediaan (Gantt) | ya |
| O3 | `o3_berita_acara_kerusakan_klaim_denda_loket` | BAP kerusakan + klaim denda | ya |
| O4 | `o4_kiosk_antrean_mandiri_mahasiswa` | Kiosk antrean mandiri | ya |

Ciri desain: tampilan loket/kiosk layar besar, alur cepat scan dan serah
terima, Gantt chart ketersediaan, formulir BAP.

### Konsol Admin Bersama (13)

| Kode | Folder | Layar | Screenshot |
|---|---|---|---|
| W1 | `w1_dashboard_operator` | Dashboard operator | ya |
| W2 | `w2_dashboard_organization_admin` | Dashboard organization admin | ya |
| W3 | `w3_daftar_aset_inventaris` | Daftar aset/inventaris | ya |
| W4 | `w4_form_tambah_edit_aset` | Form tambah/edit aset | ya |
| W5 | `w5_kategori_inventaris` | Kategori inventaris | ya |
| W6 | `w6_antrean_persetujuan_approval_queue` | Antrean persetujuan | ya |
| W7 | `w7_detail_booking_admin` | Detail booking admin | ya |
| W8 | `w8_form_serah_terima_handover` | Form serah terima | ya |
| W9 | `w9_pemeriksaan_inspection` | Pemeriksaan | ya |
| W10 | `w10_maintenance_daftar_tugas_detail_drawer` | Maintenance + drawer detail | ya |
| W11 | `w11_pengguna_role` | Pengguna & role | ya |
| W12 | `w12_laporan_analitik` | Laporan & analitik | ya |
| W13 | `w13_pengaturan_organisasi` | Pengaturan organisasi | ya |

Ciri desain: sidebar 256px, topbar dengan pencarian dan notifikasi, tabel data
padat, filter, badge status, KPI card, drawer detail.

### Admin Prodi (6)

| Kode | Folder | Layar | Screenshot |
|---|---|---|---|
| P1 | `p1_dashboard_admin_prodi_monitoring_kuota_rekomendasi_alat` | Dashboard prodi: kuota + rekomendasi alat | ya |
| P2 | `p2_antrean_otorisasi_khusus_kaprodi_detail_verifikasi_portofolio` | Antrean otorisasi khusus + verifikasi portofolio | ya |
| P3 | `p3_kuota_utilisasi_mata_kuliah` | Kuota & utilisasi mata kuliah | ya |
| P4 | `p4_kalender_jadwal_praktikum_alokasi_studio_av` | Kalender praktikum + alokasi studio AV | ya |
| P5 | `p5_mahasiswa_skor_kepatuhan_lab` | Skor kepatuhan mahasiswa | ya |
| P6 | `p6_laporan_utilisasi_semesteran_rekomendasi_rab` | Laporan semesteran + rekomendasi RAB | ya |

Ciri desain: dashboard analitik, grafik, tabel agregat, alur verifikasi
portofolio, kalender praktikum.

### Technician (4)

| Kode | Folder | Layar | Screenshot |
|---|---|---|---|
| T-Dash | `dashboard_teknisi_workbench_status_pemeliharaan` | Dashboard teknisi: workbench + status pemeliharaan | ya |
| T-WO | `workbench_reparasi_work_order_teknisi` | Workbench reparasi / work order | ya |
| T3 | `t3_inventaris_suku_cadang_logistik` | Inventaris suku cadang | ya |
| T4 | `t4_kalibrasi_preventif_sertifikasi_alat` | Kalibrasi preventif + sertifikasi | ya |

Ciri desain: papan kerja (kanban/daftar tugas), status pekerjaan, checklist,
detail alat dan riwayat.

## Catatan Desain Lintas Mockup

- Palet konsisten: orange `#F97316`, hover `#EA580C`, tint `#FFF7ED`, latar
  putih, surface `#F5F5F5`, teks `#262626`, border `#E5E7EB`.
- Tipografi: Plus Jakarta Sans untuk heading, Inter untuk body.
- Badge status konsisten: hijau (tersedia/selesai), kuning (menunggu), biru
  (aktif), merah (ditolak/rusak), abu (dibatalkan/arsip).
- Komponen berulang: KPI card, tabel, filter bar, tab status, timeline, drawer,
  modal konfirmasi, empty state.
- Tidak ada gradient, glow, atau glassmorphism pada mockup konsol. Efek
  transparan hanya pada overlay modal/drawer.
- Mockup memakai ikon garis keluarga Lucide.
- Beberapa mockup memperlihatkan data fiktif (nama mahasiswa, kode alat, angka
  kuota); data ini tidak boleh dianggap sebagai seed nyata.

## Verifikasi

Dihitung langsung dari filesystem:

```
find public/Design/mockup -name code.html | wc -l   -> 49
find public/Design/mockup -name screen.png | wc -l  -> 46
```

Folder tanpa `screen.png`: B1–B4. Folder `lendora_favicon_180_siklus.png`
berisi `screen.png` saja.

## File Tersentuh

Dibuat:

- `docs/history/2026-11-08-inventaris-mockup.md`

Tidak ada aset mockup yang diubah.

## Risiko / Catatan Lanjutan

- Empat mockup borrower desktop tidak punya screenshot; keputusan visual harus
  mengacu `code.html`.
- Folder `lendora_favicon_180_siklus.png/` kemungkinan artefak ekspor; jangan
  dihapus tanpa izin.
- Mockup bukan kontrak backend. Layar tanpa route harus diperlakukan sebagai
  usulan, bukan fitur yang sudah disetujui.
