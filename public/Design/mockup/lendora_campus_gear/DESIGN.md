---
name: Lendora Campus Gear
colors:
  surface: '#F5F5F5'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e4e2e1'
  on-surface: '#1b1c1c'
  on-surface-variant: '#584237'
  inverse-surface: '#303030'
  inverse-on-surface: '#f3f0ef'
  outline: '#8c7164'
  outline-variant: '#e0c0b1'
  surface-tint: '#9d4300'
  primary: '#9d4300'
  on-primary: '#ffffff'
  primary-container: '#f97316'
  on-primary-container: '#582200'
  inverse-primary: '#ffb690'
  secondary: '#a73a00'
  on-secondary: '#ffffff'
  secondary-container: '#fd651e'
  on-secondary-container: '#571a00'
  tertiary: '#625e56'
  on-tertiary: '#ffffff'
  tertiary-container: '#a09a91'
  on-tertiary-container: '#36322c'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdbca'
  primary-fixed-dim: '#ffb690'
  on-primary-fixed: '#341100'
  on-primary-fixed-variant: '#783200'
  secondary-fixed: '#ffdbce'
  secondary-fixed-dim: '#ffb599'
  on-secondary-fixed: '#370e00'
  on-secondary-fixed-variant: '#7f2b00'
  tertiary-fixed: '#e9e1d8'
  tertiary-fixed-dim: '#ccc5bc'
  on-tertiary-fixed: '#1e1b15'
  on-tertiary-fixed-variant: '#4a463f'
  background: '#fcf9f8'
  on-background: '#1b1c1c'
  surface-variant: '#e4e2e1'
  surface-white: '#FFFFFF'
  border: '#E5E7EB'
  text-muted: '#6B7280'
  success: '#16A34A'
  success-bg: '#F0FDF4'
  warning: '#D97706'
  warning-bg: '#FFFBEB'
  danger: '#DC2626'
  danger-bg: '#FEF2F2'
  info: '#0284C7'
  info-bg: '#F0F9FF'
  violet: '#7C3AED'
  violet-bg: '#F5F3FF'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '800'
    lineHeight: 36px
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 17px
    fontWeight: '600'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '600'
    lineHeight: 20px
  caption:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  code-sm:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  margin: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system embodies a clean, professional, and utilitarian aesthetic tailored for university media production units and student creators in Indonesia. The design philosophy centers on operational confidence, immediate clarity, and tactical ergonomics. It strikes a balance between academic rigor and creative energy: warm, inviting, and highly functional.

The visual tone draws from **Modern Clean / Functionalist Flat**:
- Unobtrusive, content-first layout structures where photography equipment assets take center stage.
- Zero decorative gradients, heavy glassmorphism, or skeumorphic textures.
- High operational feedback through high-contrast status pills and dedicated numeric data treatments.
- Distinct mobile-first ergonomics with minimum 44px tap targets, optimized for field handoffs and equipment check-ins.
- All microcopy, labels, system dialogs, and notifications are rendered exclusively in clear, direct Bahasa Indonesia.

## Colors

The color system operates on an active high-contrast brand signature anchored by vibrant orange (`#F97316`), paired with neutral grays and tinted functional surfaces. Dark mode is intentionally omitted to maintain consistent physical field legibility under varying lighting conditions.

### Brand Roles
- **Primary (`#F97316`)**: Reserved for primary CTAs, active mobile bottom navigation items, timeline step completions, and focus-visible indicators.
- **Secondary (`#EA580C`)**: Pressed, active, and focused states for primary buttons; used when high-contrast text rendering is required against warm tinted backgrounds.
- **Tertiary (`#FFF7ED`)**: Ambient highlight tint used for active filter chips, category pills, and highlighted booking container cards ("Peminjaman Aktif").
- **Neutral (`#262626`)**: High-contrast, near-black ink for headers, primary data cells, and critical labels.

### Semantic Status Mapping (Bahasa Indonesia)
Every status in the equipment lifecycle is mapped to a specific color pair consisting of a solid text/glyph tone and a 10% tinted background:
- **Tersedia / Selesai / Baik (`#16A34A` on `#F0FDF4`)**: Asset ready, booking concluded, or equipment in good operational order.
- **Menunggu / Perawatan (`#D97706` on `#FFFBEB`)**: Pending admin approval, submission under review, or approaching pickup deadline.
- **Disetujui / Dipinjam (`#0284C7` on `#F0F9FF`)**: Approved reservation or equipment actively in possession of the borrower.
- **Direservasi / Siap Diambil (`#7C3AED` on `#F5F3FF`)**: Asset staged and confirmed for handover.
- **Ditolak / Rusak / Hilang (`#DC2626` on `#FEF2F2`)**: Rejected request, asset malfunction, schedule clash, or missing kit.

## Typography

The typographic hierarchy combines structural authority with data precision:
- **Headings (Plus Jakarta Sans)**: Delivers a clean, contemporary appearance with open counters that maintain high legibility on mobile screens. Used for page titles, category headings, and asset names.
- **Body & Controls (Inter)**: Provides neutral, distortion-free legibility across paragraphs, form labels, and badge copy.
- **Identifiers & Codes (JetBrains Mono)**: Applied strictly to asset serial numbers (`CAM-SONY-A7M4-01`), booking transaction IDs (`LDR-BK-2026-0001`), timestamps, and currency values. Must always enforce `font-variant-numeric: tabular-nums` to prevent visual jitter. Identifiers must never truncate with ellipsis; wrap or scroll horizontally instead.

## Layout & Spacing

The layout is constructed around an 8-point base grid (with a 4-point micro step for compact alignments).

### Form Factors & Viewport Rules
- **Borrower Mobile Viewport (< 768px)**: Native container width stretches fluidly across mobile screens with a mandatory lateral canvas margin of `16px` (`margin`). When rendered on desktop screens for administrative preview, the mobile container is capped at `max-width: 480px` and centered with an ambient backdrop.
- **Asset Catalog Grid**: Fixed 2-column layout on mobile devices with a `12px` to `16px` column gap (`gutter`).
- **Touch Ergonomics**: All interactive elements (buttons, inputs, bottom navigation targets, chip selections) have a strict minimum physical tap height of `44px`. Adjacent interactive controls must have at least an `8px` (`space-sm`) safety clearance.
- **Vertical Rhythm**: Card inner padding is standardized at `16px` (`space-md`). Section containers maintain `24px` (`space-lg`) of vertical separation.

## Elevation & Depth

This system avoids heavy drop shadows, neon glows, and glassy backdrops. Depth is established through crisp tonal contrast and hairline outlines:

- **Flat Surface Tier**: The default canvas uses pure white (`#FFFFFF`) or warm utility gray (`#F5F5F5`). Content cards sit on white surfaces separated by a 1px solid border in `#E5E7EB`.
- **Level 1 (Cards, Floating Containers, Bottom Bar)**:
  `box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.06)` with a `1px solid #E5E7EB` border.
- **Level 2 (Modals, Action Sheets, Date Pickers)**:
  `box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.08)` paired with an overlay backdrop (`rgba(38, 38, 38, 0.4)`).
- **Surface Elevation via Tone**: Secondary table headers, metadata blocks, and disabled zones rely on `#F5F5F5` fills rather than shadow displacement.

## Shapes

The geometric framework is calibrated to roundedness level 2, providing friendly yet crisp bounds that suit technical equipment:

- **Inputs & Standard Buttons**: `10px` border radius (`--radius-input`).
- **Cards & Asset Containers**: `14px` border radius (`--radius-card`). When an asset card includes a hero image, the image is locked to a 4:3 aspect ratio with top corners rounded to `14px` and bottom corners straight (`0px`).
- **Modals & Bottom Action Drawers**: `16px` border radius (`--radius-modal`) on top corners.
- **Badges, Status Pills & Filter Chips**: Fully rounded pill shapes (`9999px`).
- **Focus Rings**: `2px solid #F97316` with a `2px` offset (`focus-visible`).

## Components

### Buttons
- **Primary Button**: Background `#F97316`, text `#FFFFFF`, height `44px`, border-radius `10px`, font weight 600. Active/Hover state transitions to `#EA580C`.
- **Secondary Button**: Background `#FFFFFF`, border `1px solid #E5E7EB`, text `#262626`, height `44px`, border-radius `10px`. Hover state applies `#F5F5F5`.
- **Destructive Button**: Background `#DC2626`, text `#FFFFFF`, height `44px`, border-radius `10px`.
- **Disabled State**: Opacity `0.4`, cursor `not-allowed`, pointer-events deactivated.

### Status Badges & Pills
- Rendered with height `24px`, horizontal padding `10px`, border-radius `9999px`, and `font-size: 13px` with font weight 600.
- Colors follow semantic status assignments (e.g., `#16A34A` text on `#F0FDF4` background for "Tersedia"). Every badge must display clear Indonesian text; color alone must never indicate status.

### Form Inputs & Selectors
- Height `44px`, horizontal padding `12px`, border `1px solid #E5E7EB`, border-radius `10px`, background `#FFFFFF`, font size `15px`.
- Placeholder text uses `#6B7280`. Focus state triggers border `#F97316` and a `2px` focus ring.
- Error state replaces border with `#DC2626` and renders an explanatory label in `#DC2626` beneath the field.

### Asset Cards
- White background (`#FFFFFF`), `1px solid #E5E7EB` outline, `14px` corner radius, subtle shadow (`0 1px 2px rgba(0,0,0,0.06)`).
- Top media element: 4:3 image ratio with top radius `14px`. If no image is provided, display a centered camera icon on `#F5F5F5`.
- Card body includes an asset category pill, heading `17px` weight 600, monospace gear code (`code-sm`), and real-time availability badge.

### Filter Chips
- Height `36px`, rounded pill (`9999px`), horizontal padding `14px`.
- Inactive: background `#FFFFFF`, border `1px solid #E5E7EB`, text `#6B7280`.
- Active: background `#FFF7ED`, border `1px solid #F97316`, text `#EA580C`, font weight 600.

### Bottom Navigation Bar (Mobile)
- Fixed bottom position, height `64px`, white background (`#FFFFFF`), border-top `1px solid #E5E7EB`.
- 4 to 5 navigation targets with Lucide icon (24px) stacked above caption typography (12px, weight 600).
- Active item uses `#F97316`; inactive items use `#6B7280`.

### Toast Notifications
- Floating bar with `10px` border radius, white background (`#FFFFFF`), shadow Level 2, and a solid `4px` left accent border matching status intent (`#16A34A` for success, `#DC2626` for error).
- Configured to dismiss automatically after 4000ms.