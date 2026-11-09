# Lendora V3 — Pemetaan Resmi Menu & Arsitektur Navigasi Berbasis Role (Role-Based Access Control)

Dokumen ini adalah standarisasi navigasi arsitektur **Lendora V3** untuk memisahkan menu yang sebelumnya tercampur. Setiap role memiliki struktur navigasi (sidebar/tab bar), batasan hak akses (RBAC), serta daftar layar yang independen.

---

## 1. Ikhtisar Role & Pola Antarmuka (UX Paradigm)

Lendora V3 memiliki **5 Role Pengguna** dengan 2 pola tata letak utama:

| No | Role Pengguna | Tipe Layout | Pola Navigasi | Target Pengguna |
|:---|:---|:---|:---|:---|
| **1** | **Borrower (Peminjam)** | **Mobile-First** (Max-width 480px di desktop) | **Bottom Tab Bar (5 Tab)** | Mahasiswa, Dosen Pengampu |
| **2** | **Operator Loket (Sirkulasi)** | **Desktop Console** (Sidebar 256px + Topbar) | **Sidebar Kiri Operasional** | Staf Loket Sirkulasi, Asisten Lab Jaga |
| **3** | **Technician (Teknisi Lab)** | **Desktop Console** (Sidebar 256px + Topbar) | **Sidebar Kiri Workbench** | Teknisi Perbaikan & Kalibrasi Alat |
| **4** | **Admin Prodi (Kaprodi)** | **Desktop Console** (Sidebar 256px + Topbar) | **Sidebar Kiri Akademik** | Kaprodi, Koordinator Lab Akademik |
| **5** | **Organization Admin (Superadmin)**| **Desktop Console** (Sidebar 256px + Topbar) | **Sidebar Kiri Organisasi** | Kepala UPT Lab / Penanggung Jawab Kampus |

---

## 2. Rincian Struktur Menu per Role

---

### ROLE 1: BORROWER (MAHASISWA & PEMINJAM)
> **Pola:** Mobile App dengan **Bottom Navigation Bar (5 Tab)** tetap di bawah layar. Tidak menggunakan sidebar kiri agar bersih dan fokus pada transaksi mandiri.

* **Tab 1: Beranda** (`/borrower`) — `M3`
  * Ringkasan status peminjaman aktif & sisa waktu (countdown H-1).
  * Pengumuman operasional loket.
  * Rekomendasi unit alat populer.
* **Tab 2: Katalog** (`/borrower/catalog`) — `M4` & `M5`
  * Pencarian dan filter kategori kamera, lensa, audio, lighting.
  * Strip ketersediaan 14 hari ke depan.
  * Formulir Pengajuan Pinjam mandiri (`M6`).
* **Tab 3: Peminjaman Saya** (`/borrower/bookings`) — `M7` & `M8`
  * Filter status: *Semua*, *Menunggu*, *Aktif*, *Selesai*.
  * Timeline progres 5 langkah (*Diajukan → Disetujui → Diambil → Dikembalikan → Selesai*).
  * Opsi batalkan pengajuan mandiri (sebelum disetujui).
* **Tab 4: Notifikasi** (`/borrower/notifications`) — `M9`
  * Pengingat H-1 pengembalian loket.
  * Notifikasi persetujuan / penolakan beserta alasan.
  * Hasil inspeksi pasca-pengembalian.
* **Tab 5: Profil** (`/borrower/profile`) — `M10`
  * Informasi data diri, NIM/NIP, kontak WhatsApp.
  * Riwayat ringkas transaksi & status kepatuhan lab.
  * Tombol Keluar (Logout).

*(Layar Auth Khusus Borrower: `M0` Onboarding, `M1` Login, `M2` Register).*

---

### ROLE 2: OPERATOR LOKET (SIRKULASI & OPERASIONAL HARIAN)
> **Pola:** Web Dashboard Desktop (Sidebar 256px). Fokus utama: Kecepatan transaksi fisik di loket, serah-terima alat, inspeksi fisik, dan mitigasi benturan jadwal.

```text
┌────────────────────────────────────────────────────────┐
│ SIDEBAR OPERATOR LOKET                                 │
├────────────────────────────────────────────────────────┤
│ [UTAMA]                                                │
│  ├── 📊 Dashboard Loket (`W1`)                          │
│                                                        │
│ [OPERASIONAL LOKET]                                    │
│  ├── ⚡ Kiosk Scanner Cepat (`O1`)                      │
│  ├── 🔄 Serah Terima (Handover Pickup/Return) (`W8`)   │
│  ├── 🔍 Pemeriksaan & Inspeksi Fisik (`W9`)            │
│  ├── 📋 Antrean Persetujuan Standar Loket (`W6`)       │
│  └── ⚠️ Berita Acara & Denda Kerusakan (`O3`)          │
│                                                        │
│ [INVENTARIS & JADWAL]                                  │
│  ├── 📦 Daftar Aset (Mode Read-Only Loket) (`W3`)      │
│  └── 📅 Kalender Ketersediaan (Gantt Chart) (`O2`)     │
└────────────────────────────────────────────────────────┘
```

* **Hak Akses:**
  * Menyetujui pinjaman dalam batas standar (durasi ≤ 7 hari & nilai unit ≤ Rp 5.000.000).
  * Melakukan checkout QR/Barcode serah terima barang.
  * Mencatat BAP insiden kerusakan & menerbitkan invoice denda.
  * **Dilarang:** Mengedit konfigurasi sistem universitas atau menghapus master data aset.

---

### ROLE 3: TECHNICIAN (TEKNISI LABORATORIUM AV)
> **Pola:** Web Dashboard Desktop (Sidebar 256px). Fokus utama: Reparasi fisik, jadwal pemeliharaan berkala, manajemen stok sparepart, dan kalibrasi alat.

```text
┌────────────────────────────────────────────────────────┐
│ SIDEBAR TEKNISI LAB                                    │
├────────────────────────────────────────────────────────┤
│ [WORKBENCH TEKNISI]                                    │
│  ├── 🛠️ Dashboard Teknisi & Antrean Servis (`W18`)     │
│  ├── 📋 Work Order & Lembar Kerja Reparasi (`W20`)     │
│                                                        │
│ [LOGISTIK & PEMELIHARAAN]                              │
│  ├── ⚙️ Inventaris Suku Cadang & Logistik (`T3`)       │
│  ├── 🔬 Kalibrasi Preventif & Sertifikasi (`T4`)       │
│  └── 🔍 Riwayat Hasil Inspeksi Loket (`W9`)            │
└────────────────────────────────────────────────────────┘
```

* **Hak Akses:**
  * Mengubah status aset: `maintenance` ↔ `available`.
  * Memperbarui status Work Order: *Terjadwal* → *Sedang Dikerjakan* → *Selesai*.
  * Mengurangi/menambah stok suku cadang & mencatat biaya perbaikan riil.
  * Mengunggah sertifikat kalibrasi sensor dan lensa.

---

### ROLE 4: ADMIN PROGRAM STUDI (KAPRODI / KOORDINATOR AKADEMIK)
> **Pola:** Web Dashboard Desktop (Sidebar 256px). Fokus utama: Kebijakan kurikulum, alokasi kuota jam praktikum per mata kuliah, dan otorisasi dispensasi khusus.

```text
┌────────────────────────────────────────────────────────┐
│ SIDEBAR ADMIN PRODI / KAPRODI                          │
├────────────────────────────────────────────────────────┤
│ [PANEL AKADEMIK]                                       │
│  ├── 🎓 Dashboard Admin Prodi (`P1`)                   │
│  ├── ✍️ Otorisasi Khusus & Dispensasi Kaprodi (`P2`)    │
│                                                        │
│ [MONITORING & EVALUASI]                                │
│  ├── ⏱️ Kuota & Utilisasi Mata Kuliah                   │
│  ├── 👥 Mahasiswa & Skor Kepatuhan Lab                 │
│  ├── 📈 Rekomendasi Cerdas Pengadaan Alat (RAB)        │
│  └── 📑 Laporan Utilisasi Semesteran                   │
└────────────────────────────────────────────────────────┘
```

* **Hak Akses:**
  * Memberikan dispensasi khusus untuk peminjaman berisiko tinggi (nilai > Rp 15.000.000 atau durasi > 7 hari seperti Tugas Akhir / Produksi Luar Kota).
  * Memantau kuota jam alat yang dikonsumsi per dosen pengampu mata kuliah.
  * Meninjau usulan pengadaan alat baru semester berikutnya berdasarkan data penolakan jadwal.

---

### ROLE 5: ORGANIZATION ADMIN (SUPERADMIN / KEPALA UPT LAB)
> **Pola:** Web Dashboard Desktop (Sidebar 256px). Fokus utama: Pengelolaan aset master, manajemen role pengguna, konfigurasi kebijakan global, dan audit analitik.

```text
┌────────────────────────────────────────────────────────┐
│ SIDEBAR ORGANIZATION ADMIN                             │
├────────────────────────────────────────────────────────┤
│ [UTAMA]                                                │
│  └── 🏢 Dashboard Organisasi Eksekutif (`W2`)          │
│                                                        │
│ [MANAJEMEN INVENTARIS]                                 │
│  ├── 📦 Daftar Aset (CRUD Lengkap) (`W3`)              │
│  ├── ➕ Form Tambah / Edit Aset (`W4`)                 │
│  └── 🗂️ Kategori & Klasifikasi Unit (`W5`)             │
│                                                        │
│ [MONITORING & KEUANGAN]                                │
│  ├── 📋 Seluruh Antrean & Audit Transaksi (`W6`/`W7`)  │
│  ├── 🛠️ Monitoring Maintenance & Biaya Bengkel (`W10`)│
│  └── 📊 Laporan & Analitik Menyeluruh (`W12`)          │
│                                                        │
│ [PENGATURAN SISTEM]                                    │
│  ├── 👥 Manajemen Pengguna & Role (`W11`)              │
│  └── ⚙️ Pengaturan Kebijakan & Organisasi (`W13`)      │
└────────────────────────────────────────────────────────┘
```

* **Hak Akses:**
  * Kontrol penuh (*Super Admin*) atas seluruh modul sistem.
  * Mengatur batas wewenang persetujuan operator loket (`W13`).
  * Menetapkan role akun (`W11`) dan menangguhkan pengguna bermasalah.
  * Mengunduh rekap laporan analitik & ekspor CSV/XLS untuk dekanat (`W12`).

---

## 3. Matriks Matriks Hak Akses Antar-Layar (Matrix Screen by Role)

| Kode Layar | Nama Layar | Borrower | Operator | Teknisi | Kaprodi | Org Admin |
|:---|:---|:---:|:---:|:---:|:---:|:---:|
| `M0–M2` | Auth (Login, Register, Onboard) | ✅ | — | — | — | — |
| `M3–M10` | Borrower Mobile Flow | ✅ | — | — | — | — |
| `W1` | Dashboard Operator Loket | — | ✅ | — | — | View |
| `O1` | Kiosk Scanner Loket Cepat | — | ✅ | — | — | — |
| `O2` | Kalender Gantt Chart Loket | — | ✅ | — | View | ✅ |
| `O3` | Berita Acara & Denda Kerusakan | — | ✅ | Ref | Ref | ✅ |
| `W8` | Form Serah Terima (Handover) | — | ✅ | — | — | View |
| `W9` | Pemeriksaan & Inspeksi Fisik | — | ✅ | ✅ | — | View |
| `W18` | Dashboard Teknisi (Workbench) | — | — | ✅ | — | View |
| `W20` | Work Order & Reparasi Alat | — | — | ✅ | — | View |
| `T3` | Inventaris Sparepart Logistik | — | — | ✅ | — | View |
| `T4` | Kalibrasi Preventif Alat | — | — | ✅ | — | View |
| `P1` | Dashboard Admin Prodi (Kaprodi) | — | — | — | ✅ | View |
| `P2` | Otorisasi Khusus & Portofolio | — | Eskalasi | — | ✅ | View |
| `W2` | Dashboard Eksekutif Organisasi | — | — | — | — | ✅ |
| `W3–W5` | Master Aset & Kategori CRUD | — | View | View | View | ✅ (Edit) |
| `W6–W7` | Antrean Persetujuan & Booking | View | ✅ (Reguler) | — | ✅ (Dispensasi) | ✅ (Penuh) |
| `W10` | Manajemen Maintenance Terjadwal | — | View | Eksekusi | — | ✅ (Biaya) |
| `W11` | Manajemen Pengguna & Role | — | — | — | — | ✅ |
| `W12` | Laporan & Analitik Global | — | — | — | View Prodi | ✅ |
| `W13` | Pengaturan Kebijakan Sistem | — | — | — | — | ✅ |
