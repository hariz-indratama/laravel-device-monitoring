# Root Directory Structure

## Frontend (resources/js/)

```
resources/js/
├── Components/
│   ├── device/                     # Device Monitoring UI
│   │   ├── DeviceCard.vue         # Card: status, temp, humidity, battery
│   │   ├── DeviceStatusBadge.vue  # Online/Offline/Warning badge
│   │   ├── DeviceStats.vue        # Stats: total, online, offline, warning
│   │   └── AlertItem.vue          # Alert row with severity + resolve
│   ├── layout/
│   │   ├── Sidebar.vue            # Nav sidebar (DeviceMonitor branding)
│   │   ├── AuthenticatedLayout.vue # Wrapper layout
│   │   └── index.js
│   └── ui/                        # Shadcn Vue components
│       ├── badge/
│       ├── button/
│       ├── card/
│       ├── collapsible/
│       ├── command/
│       ├── data-table/
│       ├── dialog/
│       ├── dropdown-menu/
│       ├── input/
│       ├── label/
│       ├── scroll-area/
│       ├── select/
│       ├── separator/
│       ├── sheet/
│       ├── skeleton/
│       ├── table/
│       ├── tabs/
│       └── toast/                 # Sonner toast notifications
├── composables/
│   └── useWebsocket.ts           # Laravel Echo connection composable
├── Layouts/
│   └── AppLayout.vue             # Root layout wrapper
├── Pages/
│   ├── Auth/
│   │   └── Login.vue            # Login page (DeviceMonitor branding)
│   ├── Dashboard/
│   │   └── Index.vue            # Dashboard: stats, device grid, alerts
│   ├── Devices/
│   │   ├── Index.vue            # Device list with search + filter
│   │   ├── Create.vue           # Add device form
│   │   ├── Edit.vue            # Edit device form
│   │   └── Show.vue             # Device detail + logs table + alerts
│   └── Outlets/
│       ├── Index.vue            # Outlet list
│       ├── Create.vue           # Add outlet form
│       ├── Edit.vue            # Edit outlet form
│       └── Show.vue            # Outlet detail + devices
├── stores/
│   ├── deviceStore.ts           # Pinia: devices state + WS update
│   └── alertStore.ts           # Pinia: alerts state
├── types/
│   ├── index.d.ts             # Barrel export
│   ├── models.ts              # Device, DeviceLog, DeviceAlert, Outlet interfaces
│   └── inertia.d.ts           # Page props type augmentation
├── lib/
│   └── utils.js               # cn() helper (shadcn)
├── app.js                      # Entry point + Pinia setup
└── bootstrap.js               # Axios + Laravel Echo setup
```

## Backend (app/)

```
app/
├── Enums/
│   ├── UserRole.php            # Owner, Staff
│   ├── DeviceStatus.php        # Online, Offline, Warning, Maintenance
│   ├── AlertSeverity.php       # Info, Warning, Critical
│   └── AlertType.php          # TemperatureHigh, DeviceOffline, BatteryLow, etc.
├── Events/
│   └── DeviceStatusUpdated.php # Broadcast via Reverb channel
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/
│   │   │   ├── AuthController.php        # Login/logout (Sanctum token)
│   │   │   └── DeviceApiController.php   # Ingest data dari IoT device
│   │   ├── Web/
│   │   │   ├── DashboardController.php   # Dashboard page
│   │   │   ├── DeviceController.php      # CRUD device
│   │   │   └── OutletController.php     # CRUD outlet/site
│   │   └── Controller.php
│   ├── Middleware/
│   │   └── HandleInertiaRequests.php   # Share auth + csrf props
│   ├── Requests/
│   │   ├── StoreDeviceRequest.php
│   │   ├── UpdateDeviceRequest.php
│   │   └── IngestDeviceDataRequest.php
│   └── Resources/
│       ├── DeviceResource.php
│       ├── DeviceLogResource.php
│       ├── DeviceAlertResource.php
│       └── OutletResource.php
├── Jobs/
│   └── CheckOfflineDevices.php  # Watchdog: deteksi device offline
├── Models/
│   ├── Device.php               # Device IoT + forUser scope
│   ├── DeviceLog.php           # Time-series sensor log
│   ├── DeviceAlert.php         # Alert dengan forUser scope
│   ├── DeviceHeartbeat.php     # Heartbeat tracking
│   ├── Outlet.php              # Site/location + forUser scope
│   └── User.php                # User + devices relation
├── Providers/
│   └── AppServiceProvider.php  # Service binding
└── Services/
    ├── DeviceIngestService.php  # Proses data masuk + threshold check
    ├── NotificationService.php   # Telegram notification
    └── DeviceReportService.php   # Laporan device
```

## Routes

```
routes/
├── api.php     # API v1 (device ingest + mobile auth)
├── web.php     # Web: Inertia pages
├── console.php # Scheduler: CheckOfflineDevices job
└── channels.php # Broadcast channels: device.{id}, user.{id}
```

## Database (database/)

```
database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2024_01_01_000001_add_role_to_users_table.php
│   ├── 2024_01_01_000002_create_outlets_table.php
│   ├── 2026_03_27_000001_create_devices_table.php
│   ├── 2026_03_27_000002_create_device_logs_table.php
│   ├── 2026_03_27_000003_create_device_alerts_table.php
│   └── 2026_03_27_000004_create_device_heartbeats_table.php
└── seeders/
    └── DatabaseSeeder.php  # Creates owner user + 2 outlets + 4 devices
```

## Config

```
config/
├── app.php
├── auth.php
├── broadcasting.php     # Reverb driver configuration
├── cache.php
├── database.php
├── device-monitoring.php # Custom: offline_threshold_minutes, defaults
├── filesystems.php
├── logging.php
├── mail.php
├── queue.php
├── services.php         # Telegram bot token/chat_id
├── session.php
└── ziggy.php
```

## Mobile (mobile/)

```
mobile/
└── README.md          # Flutter app docs (future)
```
