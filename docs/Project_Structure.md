project-root/
├── app/
│ ├── Actions/ # Logika bisnis mandiri (Single Responsibility)
│ │ ├── ProcessDeviceHeartbeat.ts # Memproses data suhu & uptime masuk
│ │ └── CalculateDeviceUptime.ts # Menghitung durasi aktif perangkat
│ ├── Console/Commands/ # Background Tasks (Watchdogs)
│ │ └── CheckOfflineDevices.php # Mengecek device yang "mati" tiba-tiba
│ ├── Events/ # Event untuk Real-time Broadcasting
│ │ └── DeviceStatusUpdated.php # Dikirim via Reverb saat data berubah
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Api/ # Endpoint untuk IoT/Device (Ingest Data)
│ │ │ │ └── DeviceApiController.php
│ │ │ └── Web/ # Controller untuk Inertia/Dashboard
│ │ │ ├── DashboardController.php
│ │ │ └── OutletController.php
│ │ └── Resources/ # API Resources (Data Transformation)
│ │ └── DeviceResource.php
│ ├── Models/ # Eloquent Models (Typed with PHPDoc)
│ └── Services/ # Integrasi pihak ketiga (misal: Notif Telegram)
├── config/ # Konfigurasi Laravel & Reverb
├── database/
│ ├── migrations/ # Skema tabel (UUID, Time-series)
│ └── seeders/ # Data awal outlet & device untuk testing
├── resources/
│ ├── js/
│ │ ├── Components/ # UI Components (Atomic Design)
│ │ │ ├── Base/ # Button, Input, Modal (Reusable)
│ │ │ ├── Device/ # DeviceCard, TemperatureChart, StatusBadge
│ │ │ └── Layouts/ # AppLayout, Sidebar, Navbar
│ │ ├── Pages/ # Inertia Page Components
│ │ │ ├── Dashboard/Index.vue
│ │ │ └── Outlets/Show.vue
│ │ ├── Stores/ # Pinia Stores (Global State)
│ │ │ └── deviceStore.ts # Mengelola state real-time device
│ │ ├── types/ # TypeScript Definitions (.d.ts / .ts)
│ │ │ ├── models.ts # Interface untuk Database Models
│ │ │ └── inertia.d.ts # Type untuk Page Props
│ │ ├── composables/ # Vue Composables (Reusable logic)
│ │ │ └── useWebsocket.ts # Hook untuk handle Laravel Echo
│ │ ├── app.ts # Entry point Vue + Inertia
│ │ └── bootstrap.ts # Konfigurasi Axios & Echo
│ └── css/
│ └── app.css # Tailwind Directives
├── routes/
│ ├── api.php # Route untuk Ingest Data (Device -> Server)
│ ├── web.php # Route Dashboard (User -> Server)
│ └── channels.php # Auth untuk WebSocket Private Channels
├── tsconfig.json # Konfigurasi TypeScript
├── vite.config.ts # Konfigurasi Vite + Vue + Laravel
└── .env # Environment variables (Reverb, DB, etc.)
