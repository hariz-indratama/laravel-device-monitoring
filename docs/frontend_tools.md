# Frontend Tools: Tailwind CSS & shadcn/ui

## 1. Tailwind CSS

Pondasi styling untuk seluruh DeviceMonitor frontend.

- **Utility-First:** Gunakan utility classes langsung di komponen Vue
- **Responsive Design:** Prefix `sm:`, `md:`, `lg:` untuk responsive grid
- **Custom Theme:** Warna menggunakan CSS custom properties dari `resources/css/app.css`
  - `--color-accent` → Emerald-500 (online status)
  - `--color-danger` → Red-500 (offline, critical)
  - `--color-warning` → Amber-500 (warning)

## 2. shadcn/ui Components

Komponen UI kompleks. Di-copy ke `resources/js/Components/ui/`.

### Komponen yang Digunakan:
| Komponen | Fungsi |
|---|---|
| Button | Aksi CRUD, toggle |
| Input | Form input (name, serial, location) |
| Label | Label form field |
| Select | Dropdown outlet, status filter |
| Card | Container untuk device card, stats |
| Badge | Status badge (Online/Offline/Warning) |
| Dialog | Konfirmasi hapus |
| Table | Log history di device detail |
| Tabs | Tab di device detail page |
| Sheet | Slide-over panel |
| Separator | Section divider |
| Skeleton | Loading state |
| Sonner (Toast) | Success/error notification |

## 3. Pinia Stores

Dua store utama untuk state management:

```typescript
// deviceStore — semua state terkait device
- devices: Device[]
- stats: DeviceStats
- selectedDevice: Device | null
- updateDeviceFromBroadcast(payload) // update dari WS

// alertStore — state alert
- alerts: DeviceAlert[]
- resolveAlert(id)
- criticalAlerts (computed)
```

## 4. Laravel Echo / WebSocket

Real-time update menggunakan Laravel Echo:

```typescript
// Subscribe ke user channel untuk semua update
echo.private(`user.${userId}`)
  .listen('device.status.updated', (payload) => {
    deviceStore.updateDeviceFromBroadcast(payload)
  })

// Subscribe ke specific device
echo.private(`device.${deviceId}`)
  .listen('device.status.updated', ...)
```

## 5. TypeScript Types

Semua domain types di `resources/js/types/`:
- `models.ts` — Device, DeviceLog, DeviceAlert, Outlet interfaces
- `inertia.d.ts` — Page props type augmentation
