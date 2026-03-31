# DeviceMonitor — UI/UX Guidelines

## 1. Design Principles

- **Clarity over aesthetics:** Data monitoring harus mudah dibaca. Angka adalah prioritas.
- **Real-time feedback:** Status device berubah → UI berubah seketika via WebSocket.
- **Alert prominence:** Critical alerts harus mencolok (merah, atas halaman).
- **Minimal clicks:** CRUD device/outlet selesai dalam seminimal mungkin langkah.

## 2. Visual Identity

### Color System
- **Primary:** `#0F172A` (Slate-900) — sidebar, header
- **Online / Success:** `#10B981` (Emerald-500) — badge online, tombol positive
- **Warning:** `#F59E0B` (Amber-500) — badge warning, alert caution
- **Critical / Danger:** `#EF4444` (Red-500) — offline badge, critical alert, hapus
- **Info:** `#3B82F6` (Blue-500) — informational elements
- **Background:** `#F8FAFC` (Slate-50) — page background
- **Card:** `#ffffff` — white cards for content areas

### Typography
- **Font:** Instrument Sans (via CDN) / system-ui fallback
- **Weights:** 400 (body), 500 (label), 600 (semibold), 700 (bold heading)
- **Sizes:** text-xs (12px, metadata), text-sm (14px, body), text-base (16px, headings), text-xl+ (large stats)

## 3. Layout Rules

### Web Layout
- Sidebar tetap di kiri (w-64), Slate-900 background, Emerald-500 active state
- Main content area: padding 24px (p-6), max-width tidak terbatas
- Cards: rounded-xl, border gray-200, shadow-sm

### Responsive
- sm: grid 1 kolom
- md: grid 2 kolom
- lg: grid 3-4 kolom untuk device cards
- Sidebar tersembunyi di mobile (hamburger menu — future enhancement)

## 4. Dashboard Layout

```
[Stats Bar: Total | Online | Offline | Warning | Alerts]
[Device Grid (2/3 width)] | [Alert Feed (1/3 width)]
```

## 5. Status Badge Design

- **Online:** Emerald background + dot indicator
- **Offline:** Red background + dot indicator
- **Warning:** Amber background + dot indicator
- **Maintenance:** Slate background + dot indicator

## 6. Component Patterns

- **DeviceCard:** rounded-xl, border, hover shadow, menampilkan temp + humidity + battery + location
- **AlertItem:** border-l-4 colored by severity, inline resolve button
- **Stats:** colored cards (emerald/red/amber/rose) untuk visual impact
- **DataTable:** gunakan shadcn DataTable untuk log history

## 7. Empty States

- Jangan biarkan layar kosong
- Jika tidak ada device: "Belum ada device. Tambahkan device pertama Anda." + tombol "Add Device"
- Jika tidak ada alert: "Tidak ada alert aktif. Semua device berjalan normal." + emoji checkmark

## 8. Animations

- Page transition: fade via Inertia
- Toast notifications: Sonner (shadcn toast)
- Real-time update: subtle opacity flash pada card yang di-update (CSS transition 300ms)
