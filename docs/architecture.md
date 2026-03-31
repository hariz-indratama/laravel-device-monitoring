# Device Monitoring System Architecture

## Core Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Database:** PostgreSQL (atau SQLite untuk development)
- **Type Management:** TypeScript 5.x
- **Real-time Engine:** Laravel Reverb (WebSocket Server) + Laravel Echo
- **In-Memory Store:** Redis (caching & broadcast queue)
- **Web Frontend:** Vue.js 3 (Composition API) via Inertia.js
- **Mobile Frontend:** Flutter (State Management: BLoC)
- **Styling:** Tailwind CSS + Shadcn Vue
- **State Management:** Pinia

## Authentication Strategy

- **Web:** Laravel Sanctum (Stateful/Cookie-based) via Inertia
- **Mobile:** Laravel Sanctum (Token-based/Bearer)
- **Roles:** Owner (full access), Staff (view only)

## Integration Points

| Source | Target | Protocol | Purpose |
|---|---|---|---|
| IoT Device | API | HTTPS POST | Ingest suhu, humidity, battery, signal |
| Server | Dashboard | WebSocket (Reverb) | Real-time UI update on status/data change |
| Watchdog | Database | Cron Job | Deteksi "silent failure" (device mati tiba-tiba) |
| Inertia | Vue | JSON Props | Initial data load on page open |

## Data Flow

```
[Device IoT] --HTTPS POST--> [DeviceApiController]
                                      |
                                      v
                            [DeviceIngestService]
                                      |
                       +--------------+---------------+
                       |                              |
                       v                              v
                [DeviceLog]                  [DeviceAlert] (if threshold exceeded)
                       |                              |
                       v                              v
               [Dashboard via WS]            [NotificationService]
```

## Real-time Broadcasting

- Channel: `device.{id}` (private) — broadcast status update ke subscriber
- Channel: `user.{id}` (private) — broadcast alert creation
- Event: `DeviceStatusUpdated` — dispatched saat device berubah status
- Event: `DeviceAlertCreated` — dispatched saat alert baru dibuat

## Watchdog

- Job: `CheckOfflineDevices` dijadwalkan setiap 5 menit
- Cek `DeviceHeartbeat.last_seen_at` terhadap threshold (default: 5 menit)
- Jika stale → update status ke `offline`, buat alert, kirim notifikasi
