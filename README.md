# 🖥️ Device Monitoring System

Real-time IoT device monitoring platform built with **Laravel 13**, **Vue 3 (Inertia.js)**, and **Laravel Reverb** for WebSocket broadcasting. Designed for managing game consoles and arcade machines across multiple outlets.

## ✨ Features

- **Real-time Device Status** — Live online/offline monitoring via WebSocket (Laravel Reverb)
- **Multi-outlet Management** — Organize devices across locations with CSV bulk import
- **Alert System** — Automated critical/warning alerts with Telegram notification support
- **Heartbeat Watchdog** — Background job detects offline devices automatically
- **Role-based Access** — Owner and Staff roles with policy-based authorization
- **Mobile API** — RESTful API (v1) with Sanctum authentication for Flutter mobile app
- **Dashboard** — Real-time stats, device cards, recent alerts & activity logs

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 13, PHP 8.3+ |
| **Frontend** | Vue 3 (Script Setup), Inertia.js v2, TypeScript |
| **Styling** | Tailwind CSS 4, Shadcn-vue, Radix UI |
| **Real-time** | Laravel Reverb (WebSocket), Laravel Echo |
| **Authentication** | Laravel Sanctum (SPA + API token) |
| **Database** | PostgreSQL |
| **Queue** | Database queue driver |
| **Charts** | Chart.js + vue-chartjs |

## 📋 Prerequisites

- PHP ≥ 8.3
- Composer
- Node.js ≥ 18
- PostgreSQL ≥ 14
- npm or pnpm

## 🚀 Getting Started

### 1. Clone & Install

```bash
git clone <repository-url>
cd Laravel-Device-Monitoring

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=device_monitoring
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 3. Database

```bash
# Create database & run migrations
php artisan migrate

# Seed with sample data (optional)
php artisan db:seed
```

### 4. Run Development Server

```bash
# Quick start (all services)
composer dev

# Or run separately:
php artisan serve          # HTTP server
php artisan queue:listen   # Queue worker
php artisan reverb:start   # WebSocket server
npm run dev                # Vite dev server
```

The app will be available at `http://localhost:8000`.

### Default Login

| Field | Value |
|---|---|
| Email | `admin@devicemonitor.id` |
| Password | `password` |

## 📁 Project Structure

```
app/
├── Console/Commands/     # Artisan commands (ImportWilayah, TestDeviceBroadcast)
├── Enums/                # PHP 8.1 Enums (DeviceStatus, DeviceType, AlertType, etc.)
├── Events/               # Broadcast events (DeviceStatusUpdated, DeviceLogCreated, etc.)
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/       # REST API controllers (auth, device ingestion, wilayah)
│   │   └── Web/          # Inertia controllers (dashboard, devices, outlets, users)
│   ├── Middleware/        # Auth, Inertia, owner role check
│   ├── Requests/         # Form request validation
│   └── Resources/        # API resources (Device, Alert, Log, Outlet)
├── Jobs/                 # Background jobs (CheckOfflineDevices)
├── Models/               # Eloquent models
├── Policies/             # Authorization policies (Device, Outlet, Alert)
├── Providers/            # Service providers
└── Services/             # Business logic (Ingest, Report, Notification, Wilayah)

resources/js/
├── Components/           # Vue components (UI, device, layout, shared)
├── Pages/                # Inertia page components
├── composables/          # Vue composables (broadcast, websocket, wilayah)
├── layouts/              # App layout
├── stores/               # Pinia stores (device, alert)
└── types/                # TypeScript interfaces
```

## 🔌 API Endpoints

### Public

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/v1/login` | Login (rate limited: 10/min) |
| `POST` | `/api/v1/devices/{serial}/data` | Device data ingestion (rate limited: 120/min) |
| `GET` | `/api/v1/wilayah/provinces` | Get Indonesian provinces |

### Authenticated (Bearer Token)

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/v1/logout` | Logout |
| `GET` | `/api/v1/me` | Current user info |
| `GET` | `/api/v1/devices/{serial}` | Get device details |

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 📜 License

This project is proprietary software. All rights reserved.
