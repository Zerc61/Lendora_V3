# LENDORA V3 — MASTER SPECIFICATION FOR AI CODING AGENTS

> Paste this file once at the start of the project (or store it as the project's
> agent rules file, e.g. `CLAUDE.md`). It is the authoritative context for every
> phase prompt that follows. When a phase prompt and this file conflict, this
> file wins unless the user explicitly decides otherwise.

---

## 0. WORKING AGREEMENT (mandatory for every task)

You are a senior Laravel engineer working on **Lendora V3**. Follow this workflow
for every task, without exception:

1. **Inspect** — examine the current project state: git status, routes, models,
   migrations, tests, and any design documents before writing code.
2. **Understand** — restate the request and which parts of the system it touches.
3. **Plan** — list files to create/modify **with their exact paths (per §8)**,
   database changes, and risks.
4. **Approval** — wait for the user's go-ahead unless the user explicitly said
   "execute directly" ("langsung kerjakan").
5. **Implement** — do ONLY the approved scope. No unrequested refactors.
6. **Verify** — run the relevant checks (`php artisan migrate`, `php artisan
   test`, targeted feature tests) and report the REAL output. Never claim tests
   pass when they were not run or when they fail.
7. **Report** — summarize: what changed, files touched (full paths), verification
   results (verbatim command output), and what remains unfinished.

**Communication rule:** always reply to the user in **Bahasa Indonesia**. Keep
all code, identifiers, commit messages, and technical terms in English.

**Track rule:** every phase prompt is either a **BACKEND** prompt or a
**FRONTEND** prompt (§8.4). Stay inside your track's scope. Backend comes first
within a phase; frontend consumes the View Contract that backend produced.

**Safety rules (violations are serious errors):**
- Never delete or overwrite data, files, migrations, config, or design assets
  without explicit permission.
- Never run destructive commands (`migrate:fresh`, `db:wipe`, `git push --force`,
  mass deletions) outside local development without explicit approval.
- Never touch auth, authorization, or business logic when the task is UI-only.
- Never invent features, roles, routes, tables, or columns that are not in this
  spec; if something is ambiguous, ask ONE most-important question first.
- Never install a new Composer/npm package without checking Laravel 12 / PHP
  8.2 compatibility and getting approval.
- Secrets live only in `.env`. Never commit credentials.

**Incremental rule:** work happens in approved phases only (Fondasi → Phase 1 →
Phase 2 → Phase 3 → UAT), and within a phase: backend before frontend. Do not
jump ahead into features of a later phase.

---

## 1. PROJECT OVERVIEW

**Lendora V3** is an equipment lending & inventory platform (initial focus:
photography equipment such as cameras, lenses, tripods) built for an Indonesian
campus/unit context. One row in `assets` = one physical unit with a serial
number. Equipment categories ("Kamera Mirrorless", "Lensa", …) are **data**, not
hard-coded code, so the platform can serve other asset types and organizations.

Four product goals:
1. Make it easy for **borrowers** to discover equipment and submit loan requests
   online (searchable catalog, clear availability, short request form).
2. Help the **provider** manage inventory, availability, transactions, and daily
   operations (clean asset data, approval queue, recorded handovers).
3. Track the **asset lifecycle** — condition, returns, maintenance — so every
   unit's history is auditable.
4. Keep **data, permissions, and activity logs per role** consistent: every
   action is recorded; every role sees and does only what is its responsibility.

Four design principles (apply to every decision):
- **Asset-first** — asset data and its actual condition are the foundation.
- **Availability-aware** — availability is computed from status + schedule
  overlap checks, never guessed from a single column.
- **Role-aware** — borrower and admin areas never share layout or navigation.
- **Incremental** — build per phase, verify, report, then continue.

---

## 2. TECH STACK (locked decisions)

| Area | Decision |
|---|---|
| Framework | **Laravel 12** (mandatory; no major upgrade without approval) |
| Database | **MariaDB** (no PostgreSQL/MySQL switch) |
| Frontend | **Blade templates + plain CSS** (design tokens via CSS custom properties). No Vue/React/Inertia/Livewire unless approved. Minimal vanilla JS only |
| CSS/JS assets | **Static files in `public/css/` and `public/js/`** loaded via `asset()` — **no Vite/npm build step** for our styles. Breeze's Tailwind/Vite scaffolding may be stripped from pages we restyle |
| Auth starter | **laravel/breeze (Blade stack)**, then customized |
| Money | Unsigned integer, whole Rupiah (no decimals) |
| Statuses | Short string columns; value sets enforced by PHP enum classes + FormRequest validation (NOT MariaDB ENUM) |
| Dependencies | Minimum: laravel/framework 12, laravel/breeze, laravel/tinker, fakerphp/faker (dev). Anything else requires approval |
| Design tokens | `#F97316` primary (orange), `#EA580C` hover, `#FFF7ED` tint, `#FFFFFF` background, `#F5F5F5` surface, `#262626` text, `#E5E7EB` border. Headings: Plus Jakarta Sans; body: Inter. UI language: Indonesian |

---

## 3. ROLES & AUTHORIZATION MODEL

Five roles, fixed in `roles` table (seeded, no CRUD):

| slug | Name | Summary |
|---|---|---|
| `borrower` | Borrower | Browse catalog, check availability, submit loans, track own transactions only |
| `operator` | Operator | Daily operations: approve/reject within limits, handovers, inspections, create maintenance tasks |
| `organization_admin` | Organization Admin | Inventory, users & roles, org policy, reports; highest authority inside one organization |
| `admin_prodi` | Admin Prodi (usulan) | Second-level approval for long-duration/high-value loans; monitors prodi usage reports; does NOT manage assets or users |
| `technician` | Technician (usulan) | Executes maintenance tasks, records inspections, toggles asset maintenance status; no loan approvals, no user data |

Authorization model:
- **One role per user** via `users.role_id`.
- **Middleware `EnsureRole`** per route group (compare user's role slug against
  group parameter); **middleware `EnsureActiveStatus`** blocks `status =
  suspended` users everywhere.
- **Policies** for data ownership/sensitivity: `BookingPolicy` (a borrower may
  only view/modify their own bookings), `AssetPolicy` (asset status changes only
  via Services), `MaintenancePolicy`.
- Cross-role URL access must return **403 with a friendly Indonesian error page**.

Permission matrix (module × role; L=view, C=create, U=update, D=delete,
A=decide/approve; `-` = none):

| Capability | BOR | OPR | ADM | PRD | TEK |
|---|---|---|---|---|---|
| View catalog | L | L | L | L | L |
| Manage assets & categories (C/U/D) | - | L | C,U,D | L | U (condition only) |
| Submit loan requests | C | - | - | - | - |
| Approve/reject routine requests | - | A | A | - | - |
| Second-level approval (special) | - | - | A | A | - |
| Handovers (pickup/return) | - | C,U | L | - | - |
| Inspections | - | C,U | L | - | C,U |
| Manage maintenance tasks | - | C | C,U,D | - | U |
| View all org transactions | - | L | L | L | - |
| View reports & analytics | - | L | L | L | - |
| Manage users & roles | - | - | C,U,D | - | - |
| Manage org settings | - | L | U | - | - |

---

## 4. DATABASE PLAN

14 tables in MVP (table `payments` is design-only, NOT built until the finance
feature is approved). Migration order is fixed (FK-safe); use it as filename
prefix, e.g. `0001_create_roles_table.php`:

```
0001 roles            0008 booking_items     Phase map:
0002 organizations    0009 handovers         Fondasi  : 0001–0003
0003 users            0010 inspections       Phase 1  : 0004–0006
0004 categories       0011 maintenances      Phase 2  : 0007–0008
0005 assets           0012 notifications     Phase 3  : 0009–0014
0006 asset_photos     0013 activity_logs
0007 bookings         0014 settings
```

Schema principles:
- Every table: `created_at`/`updated_at` (timestamps); soft deletes
  (`deleted_at`) where data must survive (organizations, categories, assets,
  bookings).
- Foreign keys with disciplined `onDelete`: `restrict` for business relations
  (asset→category, booking_item→asset), `cascade` only for pure children
  (asset_photos, booking_items), `nullOnDelete` for
  users.organization_id / activity_logs.user_id.
- Full column-by-column schemas are provided inside each backend phase prompt —
  follow them exactly (names, types, indexes). Do not add, rename, or drop
  columns without approval.
- One migration file per table. Seeders (not migrations) insert reference/demo
  data.

---

## 5. STATUS MACHINES (authoritative — implement as PHP enums + Service guards)

**Booking status** (`bookings.status`):
`pending` → `approved` → `active` → `returned`; plus `pending` → `rejected` /
`cancelled`; `approved` → `cancelled` (never picked up).

**Asset status** (`assets.status`):
`available`, `reserved`, `borrowed`, `maintenance`, `retired`.

**Booking item status** (`booking_items.status`):
`pending`, `approved`, `picked_up`, `returned`, `damaged`, `lost`.

**Maintenance status** (`maintenances.status`):
`scheduled`, `in_progress`, `done`, `cancelled`.

**Inspection result** (`inspections.result`): `ok`, `needs_repair`, `damaged`.

**Allowed transitions** (any transition outside this table must throw):

| From | To | Trigger / business rule |
|---|---|---|
| booking pending | approved | conflict-free schedule slot exists for ALL items (re-checked under lock) |
| booking pending | rejected | reason REQUIRED; release all items |
| booking pending | cancelled | borrower cancels before approval |
| booking approved | active | pickup handover recorded; items' assets become `borrowed` |
| booking approved | cancelled | borrower never picked up (operator action) |
| booking active | returned | return handover recorded for ALL items |
| asset available | reserved | booking approved |
| asset reserved | borrowed | pickup handover |
| asset borrowed | available | return + inspection result = ok |
| asset borrowed | maintenance | return + inspection result = needs_repair / damaged |
| asset maintenance | available | maintenance task done AND condition good |
| asset (any) | retired | Organization Admin only |

**Key rule:** an asset does NOT automatically become `available` just because
the booking is `returned` — the inspection result decides. Transaction status
and asset status are updated by Services, never by controllers directly.

---

## 6. CORE BUSINESS RULES

### 6.1 Availability & schedule conflict
An asset is available for range [start, end] iff BOTH:
1. `assets.status` = `available`, AND
2. no `booking_items` row on a booking with status `approved` or `active` for
   the same asset whose range overlaps: `existing_start < new_end AND
   existing_end > new_start` (touching ranges are NOT a conflict).

Triple-layer protection against double booking (race condition):
- **Layer 1:** validation in the FormRequest at submission (reject overlapping
  requests with a message naming the unit, dates, and the conflicting booking
  code; suggest the nearest free range).
- **Layer 2:** re-check at approval inside a DB transaction with
  `lockForUpdate()` on the asset rows.
- **Layer 3:** unit tests including a concurrency test (two simultaneous
  approvals on the same unit → exactly one succeeds).

### 6.2 Approval authority (default policy, values via `settings`)
- `max_loan_days` (default 7): max request duration a borrower may submit.
- `operator_approval_limit` (default Rp 5,000,000): max total purchase value of
  items in one booking that an **Operator** may approve.
- Requests exceeding operator limits are approved by **Organization Admin**;
  requests with duration > `max_loan_days` also appear in **Admin Prodi**'s
  special approval queue (second-level approval). Re-check availability at
  approval time regardless of approver.

### 6.3 Human-readable codes
- Asset code: `{ORG}-{CAT}-{0001}` e.g. `LDR-CAM-0001` (org code + short
  category code + running number, unique).
- Booking code: `{ORG}-BK-{YYYY}-{0001}` e.g. `LDR-BK-2026-0001` (running per
  year). Generate inside the creation transaction; retry-safe.

### 6.4 Notifications (Laravel database notifications; minimal, by design)
- Decision results (approved / rejected with reason) → borrower.
- Due-date reminder H-1 → borrower (scheduled command, respects the H-1 toggle
  in settings).
- Inspection result (needs_repair/damaged) → operator + organization admin.
No other notification types in MVP. Unread count appears in borrower bottom nav.

### 6.5 Activity logging
Service layer writes one `activity_logs` row per significant action with a
machine action code (`booking.created`, `booking.approved`, `booking.rejected`,
`handover.pickup`, `handover.return`, `inspection.recorded`,
`maintenance.created`, `maintenance.done`, `asset.status_changed`,
`user.role_changed`, …), subject type/id, human-readable Indonesian description,
and optional before/after properties JSON.

### 6.6 Announcements (MVP)
The borrower home "Pengumuman" section reads a text setting
(`borrower_announcement`) editable by Organization Admin in Settings. No
announcements table in MVP.

---

## 7. ROUTE MAP

```
Auth (Breeze, public)          /login /register /forgot-password ...
Redirect /dashboard            borrower -> /borrower, others -> /admin

Borrower area — prefix /borrower, middleware: auth + role:borrower
  GET    /borrower                     home (active loan, recommendations, announcements)
  GET    /borrower/catalog             catalog + search + category/status filters
  GET    /borrower/catalog/{asset}     detail: photos, specs, 14-day availability
  GET    /borrower/bookings            my bookings (tabs by status)
  POST   /borrower/bookings            submit request (validated, conflict-checked)
  GET    /borrower/bookings/{booking}  detail + status timeline
  POST   /borrower/bookings/{booking}/cancel   cancel own pending booking
  GET    /borrower/notifications       list + mark as read
  GET    /borrower/profile             profile + change password

Admin area — prefix /admin, middleware: auth + EnsureRole per group
  GET    /admin                        dashboard (per-role variant)
  resources /admin/assets              org_admin CRUD (operator: read-only)
  resources /admin/categories          org_admin
  GET    /admin/bookings               queue + all transactions (filters)
  GET    /admin/bookings/{booking}     admin detail + timeline + handover history
  POST   /admin/bookings/{booking}/approve   operator*, org_admin, admin_prodi(special)
  POST   /admin/bookings/{booking}/reject    operator*, org_admin (reason required)
  GET    /admin/bookings/calendar            availability calendar (operator, org_admin)
  POST   /admin/bookings/{booking}/handover  operator: record pickup/return + conditions
  resources /admin/inspections        operator, technician
  resources /admin/maintenances       org_admin manage; technician update own tasks
  resources /admin/users              org_admin (users & roles, suspend)
  GET    /admin/reports                org_admin, admin_prodi (+ CSV export)
  GET/POST /admin/settings             org_admin

* operator approve is enabled only within settings limits; otherwise the UI
  disables the button and explains who must approve.
```

---

## 8. DIRECTORY & FILE PLACEMENT CONTRACT (authoritative — AI must follow exactly)

This section tells you exactly **which file goes into which directory**. Before
creating ANY file, locate its place in §8.1. If a file's intended location is
not in this map, STOP and ask.

### 8.1 Complete file tree

```
lendora-v3/
├── app/
│   ├── Enums/                      BookingStatus, AssetStatus, BookingItemStatus,
│   │                               MaintenanceStatus, InspectionResult, AssetCondition
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Borrower/           HomeController, CatalogController, BookingController,
│   │   │   │                       NotificationController, ProfileController
│   │   │   └── Admin/              DashboardController, AssetController, CategoryController,
│   │   │                           BookingController, HandoverController, InspectionController,
│   │   │                           MaintenanceController, UserController, ReportController,
│   │   │                           SettingController
│   │   ├── Middleware/             EnsureRole.php, EnsureActiveStatus.php
│   │   └── Requests/               one FormRequest per form: StoreBookingRequest,
│   │                               StoreAssetRequest, UpdateAssetRequest, RejectBookingRequest, …
│   ├── Models/                     ONE model per table, singular: Role, Organization, User,
│   │                               Category, Asset, AssetPhoto, Booking, BookingItem,
│   │                               Handover, Inspection, Maintenance, ActivityLog, Setting
│   ├── Policies/                   AssetPolicy, BookingPolicy, MaintenancePolicy
│   └── Services/                   ALL business logic: AvailabilityService, BookingService,
│                                   HandoverService, InspectionService, MaintenanceService,
│                                   AssetService, SettingService, ActivityLogger
├── database/
│   ├── migrations/                 0001_create_roles_table.php … 0014_create_settings_table.php
│   │                               (numeric prefixes FIXED per §4 — never renumber)
│   └── seeders/                    RoleSeeder, OrganizationSeeder, AdminSeeder,
│                                   CategoryAndAssetSeeder, SettingSeeder, DemoTransactionSeeder
├── docs/                           Design.md (copied here by the human),
│                                   fase-*.md (end-of-phase reports, written by AI),
│                                   UAT-CHECKLIST.md, UAT-ISSUES.md (written by AI),
│                                   mockups/ (optional Stitch HTML/PNG exports)
├── lang/id/                        validation.php, messages.php (all Indonesian UI strings)
├── public/
│   ├── css/                        lendora.css (tokens + reset + shared atoms),
│   │                               borrower.css (borrower area only),
│   │                               admin.css (admin area only)
│   └── js/                         borrower.js, admin.js (vanilla only, no build step)
├── resources/views/
│   ├── layouts/                    borrower.blade.php, admin.blade.php (+ auth wiring from Breeze)
│   ├── borrower/                   home.blade.php, catalog/index|show, bookings/index|create|show,
│   │                               notifications/index, profile/edit
│   ├── admin/                      dashboard (+ per-role variants), assets/, categories/,
│   │                               bookings/ (index, show, calendar, handover), inspections/,
│   │                               maintenances/, users/, reports/, settings/
│   ├── components/                 status-badge, condition-badge, button, input, card,
│   │                               empty-state, modal, toast, timeline, kpi-card,
│   │                               pagination, icon (inline Lucide SVG map)
│   └── errors/                     403.blade.php (friendly Indonesian) + light 404/500
├── routes/                         web.php (three labeled blocks: public / borrower / admin),
│                                   console.php (scheduler: loans:remind-due)
└── tests/
    ├── Unit/                       one test class per Service/enum: AvailabilityServiceTest, …
    └── Feature/
        ├── Auth/                   RoleRedirectTest, SuspendedUserTest, …
        ├── Borrower/               CatalogTest, BookingFlowTest, …
        └── Admin/                  ApprovalQueueTest, HandoverTest, ReportTest, …
```

Standard Laravel directories not listed (config/, bootstrap/, app/Providers/,
storage/) stay at framework defaults.

### 8.2 Placement rules (what goes where — and what NEVER goes somewhere)

| # | Rule |
|---|---|
| P1 | ALL business logic (status transitions, availability checks, code generation, notifications triggering) lives in `app/Services/`. Controllers only: validate → call Service → redirect/JSON |
| P2 | Exactly ONE migration per table, at the fixed path & numeric prefix of §4. Never edit an already-merged migration; new migrations only with approval |
| P3 | Status values are never written as raw strings outside their enum class (PHP enums in `app/Enums/`, cast on models) |
| P4 | No DB queries and no business conditions inside Blade views — views only present the variables the controller passed |
| P5 | CSS: shared tokens & atoms → `public/css/lendora.css`; borrower-only rules → `borrower.css`; admin-only rules → `admin.css`. No inline `<style>` blocks, no Tailwind classes in files we own |
| P6 | JS goes to `public/js/{area}.js`, vanilla, progressive enhancement only (toggle, modal, preview). No JS framework, no bundler |
| P7 | User-facing Indonesian strings come from `lang/id/` (validation + messages); codes/identifiers stay English |
| P8 | Uploaded photos → Storage public disk (`storage/app/public/assets/{assetCode}/…`), served via `/storage`; never under `resources/` or `public/img` |
| P9 | Documents the AI writes (phase reports, UAT checklist/issues) → `docs/`, never the repo root |
| P10 | Tests mirror the code they test and live under `tests/Unit` or `tests/Feature/{Auth,Borrower,Admin}` per §8.1 |
| P11 | Never create files outside this map (no scripts in the repo root, no new top-level directories) without asking first |

### 8.3 Naming rules

| Artifact | Pattern | Example |
|---|---|---|
| Migration | `NNNN_create_{plural_snake}_table.php` (NNNN fixed) | `0007_create_bookings_table.php` |
| Model | singular StudlyCase | `BookingItem` |
| Controller | `{Area}\{Subject}Controller` | `Admin\BookingController` |
| FormRequest | `{Verb}{Subject}Request` | `StoreBookingRequest` |
| Service | `{Subject}Service` | `AvailabilityService` |
| Policy | `{Subject}Policy` | `BookingPolicy` |
| Enum | `{Subject}Status` / `{Subject}Result` / `{Subject}Condition` | `BookingStatus` |
| Test | `{ClassUnderTest}Test` in the matching folder | `tests/Unit/AvailabilityServiceTest.php` |
| View | kebab-case under its area folder | `admin/bookings/calendar.blade.php` |
| Route name | `borrower.*` / `admin.*` prefix | `admin.bookings.approve` |
| CSS | kebab-case classes; tokens `--ldr-*` | `.ldr-status-badge` |

### 8.4 Backend vs frontend track scope

| Aspect | BACKEND prompt | FRONTEND prompt |
|---|---|---|
| Owns | migrations, seeders, models, enums, middleware, policies, FormRequests, Services, controllers + routes, stub views, ALL PHPUnit tests | layouts, full views (per `docs/Design.md`), Blade components, `public/css/*`, `public/js/*`, visual QA |
| DB schema | may change (per phase schema) | must NOT touch |
| Routes | defines them | consumes them; never renames or re-points |
| Business logic | implements in Services | must not change |
| Tests | writes/extends; must pass at end of prompt | must stay green; may add view-level assertions only |

- **Stub-view mechanism:** a BACKEND prompt may create minimal placeholder Blade
  files at the View Contract paths (e.g. a bare `<main>` with the view name) so
  feature tests pass. The FRONTEND prompt then replaces their content with the
  real design. Frontend never has to invent missing controllers/routes — if
  something from the contract is missing, STOP and report.
- Within one phase: run backend first, verify, commit; then frontend, verify,
  commit.

### 8.5 View Contract (the backend → frontend hand-off)

Every BACKEND phase prompt ends with a **View Contract** table:

```
| View file (exact path per §8.1) | Route name | Variables passed |
```

Rules:
- Variable names in the contract are **binding** — the frontend prompt builds
  views against exactly those variables.
- The backend end-of-phase report must include the contract table verbatim.
- If the frontend work needs an extra variable (presentation-only, e.g. a
  formatted date or badge class), the frontend prompt may add it to the
  controller **only as presentation logic**, business logic untouched, tests
  still green — and it must report the contract change.

---

## 9. TESTING REQUIREMENTS

Framework: PHPUnit (Breeze default). Placement per P10. Priority targets
(in order):

| Object | Type | Key scenarios |
|---|---|---|
| AvailabilityService | Unit | free range = available; partial/full overlap = conflict; touching ranges (end == start) NOT a conflict; two concurrent submissions on same unit → exactly one wins (transaction + lock) |
| BookingService | Unit | transitions per §5; illegal transition throws |
| Booking API | Feature | borrower creates request → pending; borrower cannot access another's booking (403) |
| Approval API | Feature | operator approve → booking approved + assets reserved; reject without reason fails validation |
| Handover API | Feature | pickup → active + borrowed; return of all items → returned |
| Inspection API | Feature | needs_repair → maintenance task created + asset maintenance; ok → asset available |
| Role middleware | Feature | technician hits approval route = 403; borrower hits /admin = 403; suspended user blocked |

Rules: tests must actually run; report real output; a phase is not done while
its tests fail. Use RefreshDatabase. Seeders, never migrations, provide data.
Feature test classes go in the area folder matching the routes they exercise
(Auth → `tests/Feature/Auth`, borrower routes → `tests/Feature/Borrower`,
admin routes → `tests/Feature/Admin`).

---

## 10. SEEDERS

- **RoleSeeder** — the 5 fixed roles (Tabel 3.1 values).
- **OrganizationSeeder** — one demo org: name "Unit Kampus SMK Negeri 1", code `LDR`.
- **AdminSeeder** — 1 organization admin, 1 operator, 1 technician, 1 admin
  prodi, 2 borrowers (documented demo passwords, e.g. `password`).
- **CategoryAndAssetSeeder** — categories Kamera Mirrorless, Lensa, Tripod,
  Lampu (with subcategories) + 15–20 demo units with placeholder photos,
  realistic codes `LDR-CAM-0001` …
- **SettingSeeder** — `max_loan_days=7`, `operator_approval_limit=5000000`,
  `return_reminder_enabled=1`, `borrower_announcement=""`.
- **DemoTransactionSeeder** (Phase 3) — completed + active bookings and one
  maintenance task so dashboards/reports show realistic numbers.

---

## 11. DESIGN REFERENCE

UI implementation follows **`docs/Design.md`** (the human copies it from the
deliverable folder into the repo): color tokens, typography, badge/status
mapping, component specs, borrower bottom nav (5 items: Beranda, Katalog,
Peminjaman Saya, Notifikasi, Profil), collapsible admin sidebar, per-screen
layouts. Optional Stitch mockup exports live in `docs/mockups/`. The tokens in
Design.md win over mockup pixel details.

---

## 12. EXPLICIT OUT OF SCOPE (MVP) — do not build without approval

- Payments, deposits, fines, Midtrans/any gateway (table `payments` is design-only).
- Multi-organization management UI.
- Mobile native app / public REST API.
- Dark mode, i18n beyond Indonesian, third-party packages (spatie/*, etc.).
- Any table, column, route, role, or menu not present in this spec.

---

## 13. END-OF-PHASE REPORT FORMAT (the AI must output this)

```
## Laporan Fase <nama> — <BACKEND|FRONTEND>
- Yang dibangun: ...
- File penting: (full paths, grouped create/modify — must match §8 map)
- Migration/seed dijalankan: <ya/tidak + output ringkas>
- Test: <perintah + hasil nyata, angka lulus/gagal>
- Acceptance criteria fase: <checklist lolos/belum>
- View Contract: <table verbatim — BACKEND only>
- Yang belum selesai / catatan: ...
- Commit: <hash + pesan>
```
