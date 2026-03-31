# Color System — DeviceMonitor

## Brand Colors

| Name | Hex | Tailwind | Usage |
|---|---|---|---|
| Primary | `#0F172A` | slate-900 | Sidebar bg, primary text |
| Accent | `#10B981` | emerald-500 | Online status, active nav |
| Accent Dark | `#059669` | emerald-600 | Hover accent |
| Background | `#F8FAFC` | slate-50 | Page background |
| Card | `#FFFFFF` | white | Card backgrounds |
| Border | `#E2E8F0` | gray-200 | Card borders |

## Status Colors

| Status | Color | Tailwind Class | Usage |
|---|---|---|---|
| Online | Emerald | `bg-emerald-100 text-emerald-700` | Status badge |
| Offline | Red | `bg-red-100 text-red-700` | Status badge, critical |
| Warning | Amber | `bg-amber-100 text-amber-700` | Warning badge, caution |
| Maintenance | Slate | `bg-slate-100 text-slate-700` | Maintenance badge |
| Info | Blue | `bg-blue-100 text-blue-700` | Info badge |

## Alert Severity Colors

| Severity | Background | Border | Badge |
|---|---|---|---|
| Critical | Rose-50 | Red-500 | `bg-red-500 text-white` |
| Warning | Amber-50 | Amber-500 | `bg-amber-500 text-white` |
| Info | Blue-50 | Blue-500 | `bg-blue-500 text-white` |

## Tailwind Config

Custom colors di `resources/css/app.css` (`@theme` block):

```css
--color-accent: #10B981;     /* emerald-500 */
--color-success: #10B981;    /* emerald */
--color-warning: #F59E0B;   /* amber */
--color-danger: #EF4444;     /* red */
--color-ring: #10B981;       /* emerald */
```

## Semantic Usage

- **Sidebar:** Slate-900 (bg), Emerald-500 (active indicator)
- **Stats cards:** Emerald-50, Red-50, Amber-50, Rose-50 (perbedaan visual)
- **Status dot:** `w-1.5 h-1.5 rounded-full bg-current` (adaptive)
- **Chart:** Emerald-500 (online), Red-500 (offline), Amber-500 (warning)
