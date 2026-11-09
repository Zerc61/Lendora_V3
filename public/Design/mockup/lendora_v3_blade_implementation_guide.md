# Lendora V3 — Panduan Spesifikasi Desain & Implementasi Blade (Laravel 12)

Dokumen ini adalah rujukan teknis definitif untuk mengimplementasikan mockup UI **Lendora V3** ke dalam arsitektur **Laravel 12 Blade + Pure CSS** (tanpa dependensi JS framework berat seperti Vue/React/Inertia/Livewire), sesuai dengan token dan aturan pada `Design.md`.

---

## 1. Arsitektur Layout Blade (Dual-World Layout)

Lendora membagi pengalaman pengguna ke dalam dua dunia yang berbeda:
1. **Borrower Experience (`mobile-first`)**: Ditargetkan untuk smartphone civitas kampus (mahasiswa/dosen), navigasi berbasis bottom-navigation 5 tab. Jika dibuka di desktop, dibatasi dengan kontainer responsif `max-w-md mx-auto` / `max-width: 480px`.
2. **Admin Console (`desktop-first`)**: Ditargetkan untuk operator lab, teknisi, dan pimpinan organisasi dengan sidebar kiri fixed 256px dan topbar persisten.

### 1.1 Struktur Folder Template Blade Rekomendasi
```text
resources/views/
├── layouts/
│   ├── base.blade.php           # Setup HTML5, Google Fonts, meta CSRF, CSS root tokens
│   ├── borrower.blade.php       # Layout mobile borrower (max-w-md, bottom-nav)
│   ├── admin.blade.php          # Layout desktop console (sidebar 256px + topbar)
│   └── auth.blade.php           # Clean blank frame untuk login/register/onboarding
├── components/
│   ├── brand/
│   │   └── logo.blade.php       # SVG / Logo siklus oranye resmi
│   ├── ui/
│   │   ├── button.blade.php     # Tombol primary, secondary, ghost, danger
│   │   ├── badge.blade.php      # Pill badge status dengan latar tint
│   │   ├── input.blade.php      # Form input dengan state error & helper
│   │   ├── card.blade.php       # Kontainer kartu standar (radius 14px)
│   │   └── stat-card.blade.php  # Kartu metrik KPI
│   ├── borrower/
│   │   ├── bottom-nav.blade.php # 5 menu navigasi borrower
│   │   ├── asset-card.blade.php # Kartu unit rasio 4:3
│   │   └── loan-card.blade.php  # Kartu peminjaman aktif & sisa waktu
│   └── admin/
│       ├── sidebar.blade.php    # Navigasi bertingkat peran
│       ├── topbar.blade.php     # Breadcrumb, search, shift status, avatar
│       └── conflict-banner.blade.php # Deteksi tumpang-tindih tanggal
└── pages/
    ├── borrower/ ...            # M1 s.d. M10
    └── admin/ ...               # W1 s.d. W13
```

---

## 2. Design Tokens CSS Murni (`public/css/lendora.css`)

Semua komponen dan halaman harus menggunakan token CSS kustom di bawah ini:

```css
:root {
  /* --- Warna Brand & Inti [FINAL] --- */
  --color-primary: #F97316;          /* Oranye primer untuk CTA & item aktif */
  --color-primary-dark: #EA580C;     /* Hover & active state tombol utama */
  --color-orange-tint: #FFF7ED;      /* Latar lembut banner & highlight */
  --color-orange-border: #FFEDD5;    /* Border penegas elemen oranye */
  --color-white: #FFFFFF;            /* Latar kartu & permukaan utama */
  --color-surface: #F5F5F5;          /* Latar belakang halaman & baris zebra */
  --color-surface-dim: #E5E7EB;      /* Pemisah & border sekunder */
  --color-text: #262626;             /* Teks utama (high contrast) */
  --color-text-muted: #6B7280;       /* Teks sekunder, label, placeholder */
  --color-border: #E5E7EB;          /* Garis pemisah komponen */

  /* --- Warna Semantik Status [FINAL] --- */
  --color-success: #16A34A;
  --color-success-bg: #F0FDF4;      /* Tersedia, Disetujui/Active, Selesai */
  --color-warning: #D97706;
  --color-warning-bg: #FFFBEB;      /* Pending, Maintenance berjalan */
  --color-danger: #DC2626;
  --color-danger-bg: #FEF2F2;        /* Ditolak, Rusak, Terlambat */
  --color-info: #0284C7;
  --color-info-bg: #F0F9FF;          /* Disetujui, Terjadwal, Sedang dipinjam */
  --color-violet: #7C3AED;
  --color-violet-bg: #F5F3FF;        /* Direservasi (booking approved belum diambil) */

  /* --- Tipografi --- */
  --font-heading: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
  --font-body: 'Inter', system-ui, -apple-system, sans-serif;
  --font-mono: ui-monospace, 'JetBrains Mono', 'SFMono-Regular', monospace;

  /* --- Radius & Dimensi --- */
  --radius-input: 10px;
  --radius-card: 14px;
  --radius-modal: 16px;
  --radius-pill: 9999px;

  /* --- Touch Targets --- */
  --btn-height-mobile: 44px;         /* Ramah sentuhan jempol */
  --btn-height-desktop: 38px;        /* Kepadatan data console */

  /* --- Bayangan Bersih (Subtle Shadows) --- */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
}
```

---

## 3. Komponen Utama Blade

### 3.1 Komponen Pill Badge Status (`components/ui/badge.blade.php`)
Badge status wajib selalu memiliki teks eksplisit (tidak hanya warna):

```blade
@props([
    'variant' => 'success', // success, warning, danger, info, violet, neutral
    'size' => 'md',         // sm (12px), md (13px)
])

@php
$classes = [
    'success' => 'bg-[var(--color-success-bg)] text-[var(--color-success)] border-[#BBF7D0]',
    'warning' => 'bg-[var(--color-warning-bg)] text-[var(--color-warning)] border-[#FDE68A]',
    'danger'  => 'bg-[var(--color-danger-bg)] text-[var(--color-danger)] border-[#FECACA]',
    'info'    => 'bg-[var(--color-info-bg)] text-[var(--color-info)] border-[#BAE6FD]',
    'violet'  => 'bg-[var(--color-violet-bg)] text-[var(--color-violet)] border-[#DDD6FE]',
    'neutral' => 'bg-gray-100 text-gray-700 border-gray-200',
][$variant] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border $classes"]) }}>
    {{ $slot }}
</span>
```

### 3.2 Pemetaan Status Database ke Badge UI
| Tabel & Kolom | Nilai Database | Label UI | Variant Badge |
|---|---|---|---|
| `bookings.status` | `pending` | Menunggu Persetujuan | `warning` |
| | `approved` | Disetujui | `info` |
| | `active` | Dipinjam | `success` |
| | `returned` | Dikembalikan | `neutral` |
| | `rejected` | Ditolak | `danger` |
| | `cancelled` | Dibatalkan | `neutral` |
| `assets.status` | `available` | Tersedia | `success` |
| | `reserved` | Direservasi | `violet` |
| | `borrowed` | Dipinjam | `info` |
| | `maintenance` | Maintenance | `warning` |
| | `retired` | Nonaktif | `neutral` |
| `inspections.result` | `ok` | Baik | `success` |
| | `needs_repair` | Perlu Perbaikan | `warning` |
| | `damaged` | Rusak | `danger` |

### 3.3 Komponen Tombol (`components/ui/button.blade.php`)
```blade
@props([
    'variant' => 'primary', // primary, secondary, ghost, danger
    'size' => 'md',
    'type' => 'button'
])

@php
$base = "inline-flex items-center justify-center font-semibold rounded-[var(--radius-input)] transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2";
$variants = [
    'primary' => 'bg-[var(--color-primary)] hover:bg-[var(--color-primary-dark)] text-white shadow-sm',
    'secondary' => 'bg-white hover:bg-gray-50 text-[var(--color-text)] border border-[var(--color-border)] shadow-sm',
    'ghost' => 'bg-transparent text-[var(--color-primary)] hover:bg-[var(--color-orange-tint)]',
    'danger' => 'bg-[var(--color-danger)] hover:bg-red-700 text-white shadow-sm',
][$variant];
$sizes = [
    'sm' => 'h-8 px-3 text-xs',
    'md' => 'h-10 px-4 text-sm',
    'lg' => 'h-11 px-5 text-base', // 44px untuk borrower mobile
][$size];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base $variants $sizes"]) }}>
    {{ $slot }}
</button>
```

### 3.4 Komponen Banner Peringatan Konflik Tanggal (`components/admin/conflict-banner.blade.php`)
```blade
@props([
    'unitCode',
    'conflictingBookingCode',
    'conflictingBorrower',
    'dateRange'
])

<div class="p-3.5 bg-red-50 border border-red-200 rounded-[var(--radius-input)] flex items-start gap-3 text-red-800 text-xs leading-relaxed">
    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <strong class="font-bold text-red-900 block mb-0.5">Peringatan Benturan Jadwal!</strong>
        Unit <code class="font-mono bg-red-100 text-red-900 px-1 py-0.5 rounded">{{ $unitCode }}</code> sudah terikat peminjaman pada rentang tanggal <strong>{{ $dateRange }}</strong> oleh <span class="font-medium">{{ $conflictingBorrower }}</span> (<code class="font-mono">{{ $conflictingBookingCode }}</code>).
    </div>
</div>
```

---

## 4. Format Data & Konvensi Tampilan

1. **Kode Monospace**: Semua kode unit (`LDR-CAM-0001`) dan transaksi (`LDR-BK-2026-0007`) **wajib** menggunakan tag `<code class="font-mono">` atau font tabular. Jangan pernah biarkan terpotong (ellipsis).
2. **Format Mata Uang Rupiah**: Format bilangan bulat tanpa desimal:
   ```blade
   Rp {{ number_format($amount, 0, ',', '.') }}
   {{-- Hasil: Rp 12.500.000 --}}
   ```
3. **Format Tanggal Indonesia**:
   ```blade
   {{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y') }}
   {{-- Rentang: 12 – 14 Nov 2026 --}}
   ```
4. **Chip H-1 Due Date**:
   ```blade
   @if($isDueTomorrow)
       <span class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-semibold">
           Kembalikan besok!
       </span>
   @endif
   ```

---

## 5. Hubungan Route Laravel ke View Mockup

| Layar Mockup | View Blade | Route URL | Controller & Method |
|---|---|---|---|
| `M0` Onboarding | `pages.borrower.onboarding` | `/onboarding` | `OnboardingController@index` |
| `M1` Login | `pages.auth.login` | `/login` | `AuthenticatedSessionController@create` |
| `M2` Register | `pages.auth.register` | `/register` | `RegisteredUserController@create` |
| `M3` Beranda | `pages.borrower.home` | `/borrower` | `BorrowerHomeController@index` |
| `M4` Katalog | `pages.borrower.catalog.index` | `/borrower/catalog` | `CatalogController@index` |
| `M5` Detail Aset | `pages.borrower.catalog.show` | `/borrower/catalog/{asset:code}` | `CatalogController@show` |
| `M6` Ajukan Pinjam | `pages.borrower.bookings.create` | `/borrower/catalog/{asset}/book` | `BookingController@create` |
| `M7` Peminjaman Saya| `pages.borrower.bookings.index` | `/borrower/bookings` | `BookingController@index` |
| `M8` Detail Booking | `pages.borrower.bookings.show` | `/borrower/bookings/{booking:code}` | `BookingController@show` |
| `M9` Notifikasi | `pages.borrower.notifications` | `/borrower/notifications` | `NotificationController@index` |
| `M10` Profil | `pages.borrower.profile` | `/borrower/profile` | `ProfileController@show` |
| `W1` Dash Operator | `pages.admin.dashboard.operator`| `/admin/operator` | `OperatorDashboardController@index` |
| `W2` Dash Org Admin | `pages.admin.dashboard.org` | `/admin` | `AdminDashboardController@index` |
| `W3` Daftar Aset | `pages.admin.assets.index` | `/admin/assets` | `AssetController@index` |
| `W4` Form Aset | `pages.admin.assets.form` | `/admin/assets/create` | `AssetController@create` |
| `W5` Kategori | `pages.admin.categories.index`| `/admin/categories` | `CategoryController@index` |
| `W6` Antrean Otorisasi| `pages.admin.bookings.queue` | `/admin/bookings/approval` | `ApprovalQueueController@index` |
| `W7` Detail Booking | `pages.admin.bookings.show` | `/admin/bookings/{booking:code}` | `AdminBookingController@show` |
| `W8` Serah Terima | `pages.admin.handovers.create`| `/admin/handovers` | `HandoverController@create` |
| `W9` Pemeriksaan | `pages.admin.inspections.index`| `/admin/inspections` | `InspectionController@index` |
| `W10` Maintenance | `pages.admin.maintenances.index`| `/admin/maintenances` | `MaintenanceController@index` |
| `W11` Pengguna & Role| `pages.admin.users.index` | `/admin/users` | `UserController@index` |
| `W12` Laporan & Analitik| `pages.admin.reports.index` | `/admin/reports` | `ReportController@index` |
| `W13` Pengaturan | `pages.admin.settings.index` | `/admin/settings` | `SettingController@index` |

Dokumen ini siap dijadikan pedoman langsung oleh pengembang backend & frontend Laravel untuk menghasilkan implementasi yang 100% presisi dengan desain Lendora V3.
