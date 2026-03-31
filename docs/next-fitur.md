# DeviceMonitor — Roadmap Fitur Next

## FASE 1 — Core System (DONE)
- CRUD Devices & Outlets
- Device data ingestion via API
- Real-time status update via WebSocket (Reverb)
- Watchdog: offline detection
- Alert generation (temperature threshold)
- Dashboard dengan stats + recent alerts

---

## FASE 2 — Enhance Dashboard

### 2a. Temperature Chart
- Gunakan Chart.js atau ApexCharts di halaman device detail
- Tampilkan data 24 jam terakhir dari `device_logs`
- Endpoint: `GET /api/v1/devices/{serial}/logs?from=&to=`

### 2b. Alert Resolution
- Tombol "Resolve" di AlertItem.vue
- Endpoint: `PATCH /api/v1/alerts/{id}/resolve`
- Backend: DeviceAlertController + UpdateAlertRequest

### 2c. Alert History Page
- Halaman `/alerts` dengan filter (severity, type, resolved/unresolved)
- Paginated list semua alert

---

## FASE 3 — Mobile App (Flutter)

### 3a. Flutter Project Setup
- BLoC pattern untuk state management
- Repository pattern untuk API calls

### 3b. Flutter Screens
- Login screen (Sanctum token auth)
- Dashboard: device overview + active alerts
- Device list dengan pull-to-refresh
- Device detail dengan chart realtime

### 3c. Mobile API Endpoints
- `GET /api/v1/devices/{serial}` — device info
- `GET /api/v1/devices/{serial}/logs?hours=24` — historical data

---

## FASE 4 — Advanced Features

### 4a. Telegram Notification
- Config `TELEGRAM_BOT_TOKEN` dan `TELEGRAM_CHAT_ID`
- NotificationService sudah ada — tinggal test dan improve

### 4b. Data Retention Command
- `PruneOldDeviceLogs` command
- `Schedule::command('device:prune-logs')->daily()`

### 4c. Email Notification
- Kirim email ke owner saat critical alert
- Gunakan Laravel Mail + queue

### 4d. Multi-user dengan Outlet Assignment
- Staff bisa assign ke outlet tertentu
- `forUser` scope sudah support ini

### 4e. Historical Report Page
- Pilih device + date range → tampilkan chart
- Export ke PDF/CSV

### 4f. Mobile Widget (Android/iOS)
- Notifikasi push saat alert critical
- Quick view device status

---

## FASE 5 — Scaling

### 5a. PostgreSQL Migration
- Move dari SQLite ke PostgreSQL untuk production
- Gunakan UUID untuk device serial (atau keep serial number)

### 5b. Redis Caching
- Cache device stats per user
- Cache dashboard data (TTL: 60 detik)

### 5c. Queue Worker untuk Ingest
- `DeviceIngestController` → dispatch job → process async
- Mencegah blocking saat volume tinggi

### 5d. Rate Limiting
- Device ingestion: 60 req/minute per device
- Mobile API: 120 req/minute per user
