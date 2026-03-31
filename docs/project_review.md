# 🔍 Professional Code Review — Laravel Device Monitoring

> Hasil audit menyeluruh terhadap struktur, arsitektur, keamanan, dan kualitas kode project ini.
> Disusun berdasarkan standar industri software development profesional.

---

## 📊 Ringkasan Skor

| Aspek | Skor | Keterangan |
|---|---|---|
| **Struktur Project** | ⭐⭐⭐⭐ | Baik, mengikuti konvensi Laravel |
| **Arsitektur & Separation of Concerns** | ⭐⭐⭐☆ | Ada service layer tapi belum konsisten |
| **Keamanan** | ⭐⭐☆☆ | Ada celah kritis |
| **Testing** | ⭐☆☆☆ | Hampir tidak ada test |
| **Data Integrity** | ⭐⭐⭐☆ | Ada validasi tapi inkonsisten |
| **API Design** | ⭐⭐⭐☆ | Versioned, tapi missing rate-limiting & auth |
| **Frontend (Vue/TS)** | ⭐⭐⭐☆ | Typed, tapi ada inkonsistensi |
| **DevOps & CI/CD** | ⭐☆☆☆ | Tidak ada |
| **Dokumentasi** | ⭐⭐⭐☆ | Ada docs/ tapi README masih default |

---

## 🚨 KRITIS — Harus Diperbaiki Segera

### 1. File `.env` di-commit ke repository

> [!CAUTION]
> File `.env` mengandung **APP_KEY**, **database credentials**, dan **Reverb secrets** yang seharusnya TIDAK PERNAH di-commit ke Git.

**Masalah di** [.env](file:///c:/Laravel-Device-Monitoring/.env):
- Baris 3: `APP_KEY=base64:z7DP3TYb...` — secret key terekspos
- Baris 24-25: `DB_USERNAME=admin`, `DB_PASSWORD=admin` — hardcoded credentials
- Baris 65-67: Reverb App ID/Key/Secret terekspos

**Perbaikan:**
```bash
# Hapus dari tracking Git (JANGAN hapus filenya)
git rm --cached .env

# Regenerate semua secrets
php artisan key:generate
# Ganti semua password DB dan Reverb secrets
```

### 2. API Ingestion Endpoint Tanpa Autentikasi

> [!CAUTION]
> `POST /api/v1/devices/{serial_number}/data` **terbuka tanpa autentikasi apapun**. 
> Siapapun yang tau serial number bisa mengirim data palsu.

**Masalah di** [api.php](file:///c:/Laravel-Device-Monitoring/routes/api.php#L26-L27):
```php
// Public: Device ingestion (IoT device → server)
Route::post('/devices/{serial_number}/data', [DeviceApiController::class, 'ingest']);
```

**Perbaikan:**
- Tambahkan **API key per-device** atau gunakan **HMAC signature**
- Minimal: header `X-Device-Token` yang di-generate per device
- Terapkan **rate limiting** (`throttle:60,1`) untuk prevent abuse

### 3. SQL Injection via Search Filters

> [!WARNING]
> Beberapa controller menggunakan `LIKE "%{$s}%"` tanpa parameter binding yang aman.

**Masalah di** [DeviceController.php](file:///c:/Laravel-Device-Monitoring/app/Http/Controllers/Web/DeviceController.php#L20-L21):
```php
->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
    ->orWhere('serial_number', 'like', "%{$s}%"))
```

Meskipun Eloquent secara default menggunakan parameter binding, pattern `orWhere` tanpa grouped closure bisa menghasilkan result yang tak terduga (leak data user lain):

```php
// BURUK: orWhere tanpa group bisa bypass forUser scope
->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
    ->orWhere('serial_number', 'like', "%{$s}%"))

// BAIK: group the OR conditions  
->when($request->search, fn($q, $s) => $q->where(function($sub) use ($s) {
    $sub->where('name', 'like', "%{$s}%")
        ->orWhere('serial_number', 'like', "%{$s}%");
}))
```

Masalah yang sama ada di:
- [AlertsController.php](file:///c:/Laravel-Device-Monitoring/app/Http/Controllers/Web/AlertsController.php#L17)
- [UserController.php](file:///c:/Laravel-Device-Monitoring/app/Http/Controllers/Web/UserController.php#L18)

### 4. Tidak Ada Authorization/Policy pada Resource Controllers

> [!WARNING]
> Controller tidak memverifikasi bahwa user yang login adalah **pemilik** resource yang diakses.

**Masalah di** [DeviceController.php](file:///c:/Laravel-Device-Monitoring/app/Http/Controllers/Web/DeviceController.php#L57):
```php
public function show(Request $request, Device $device)
{
    // ❌ Tidak ada pengecekan apakah device ini milik user
    $device->load(['outlet', 'alerts' => ...]);
}
```

Juga di `edit()`, `update()`, `destroy()`, `toggle()` — semua tanpa authorization check.

**Perbaikan:**
```php
// Buat Policy
php artisan make:policy DevicePolicy --model=Device

// Di DevicePolicy.php:
public function view(User $user, Device $device): bool
{
    return $user->id === $device->user_id 
        || $user->id === $device->outlet?->user_id;
}

// Di controller:
$this->authorize('view', $device);
```

---

## ⚠️ PENTING — Harus Diperbaiki Sebelum Production

### 5. Testing Hampir Tidak Ada

> [!IMPORTANT]
> Project hanya memiliki **2 test boilerplate** dari scaffolding Laravel. Tidak ada satupun test yang menguji business logic.

**File test saat ini:**
- [tests/Feature/ExampleTest.php](file:///c:/Laravel-Device-Monitoring/tests/Feature/ExampleTest.php) — test `GET /` returns 200 (akan FAIL karena redirect ke login)
- [tests/Unit/ExampleTest.php](file:///c:/Laravel-Device-Monitoring/tests/Unit/ExampleTest.php) — `true is true`

**Yang harus dibuat (minimum):**

| Area | Test yang Dibutuhkan |
|---|---|
| **Auth** | Login, logout, middleware redirect |
| **Device CRUD** | Create, read, update, delete + ownership check |
| **API Ingest** | Valid payload, invalid payload, unknown serial |
| **Alerts** | Create, resolve, bulk resolve |
| **Authorization** | User tidak bisa akses device milik user lain |
| **Broadcast** | Event dispatched saat status berubah |
| **Job** | `CheckOfflineDevices` marks stale devices offline |

### 6. `.env.example` Tidak Sinkron dengan `.env`

**Masalah:** `.env.example` masih menggunakan default Laravel, tidak mencantumkan:
- `SANCTUM_STATEFUL_DOMAINS`
- Variabel Reverb (`REVERB_*`, `VITE_REVERB_*`)
- `VITE_USE_REVERB`
- `INERTIA_SSR_ENABLED`
- `DEVICE_OFFLINE_THRESHOLD_MINUTES`

Developer baru yang clone repo tidak akan tau variabel apa saja yang dibutuhkan.

### 7. `DatabaseSeeder` Menggunakan Kolom yang Sudah Tidak Ada

> [!WARNING]
> Seeder mereferensikan kolom `temperature`, `humidity`, `battery`, `signal_strength` pada `DeviceLog`, tapi migration terbaru (`2026_03_29_000000_update_device_logs_for_status.php`) kemungkinan sudah mengubah schema.

**Masalah di** [DatabaseSeeder.php](file:///c:/Laravel-Device-Monitoring/database/seeders/DatabaseSeeder.php#L57-L64):
```php
DeviceLog::create([
    'device_id'       => $device->id,
    'temperature'     => ...,  // ❌ Kolom ini mungkin sudah dihapus
    'humidity'        => ...,  // ❌ 
    'battery'         => ...,  // ❌
    'signal_strength' => ...,  // ❌
]);
```

Sementara model `DeviceLog` hanya memiliki `fillable`: `device_id`, `status`, `start_uptime`, `end_uptime`, `logged_at`.

### 8. `DeviceReportService` Mereferensikan Kolom Lama

**Masalah di** [DeviceReportService.php](file:///c:/Laravel-Device-Monitoring/app/Services/DeviceReportService.php#L28-L31):
```php
'avg_temp'      => $todayLogs->avg('temperature'),  // ❌ kolom sudah tidak ada
'min_temp'      => $todayLogs->min('temperature'),  // ❌
'max_temp'      => $todayLogs->max('temperature'),  // ❌
'avg_humidity'  => $todayLogs->avg('humidity'),      // ❌
```

### 9. Model `CashierShift` Orphan — Tidak Ada Migration

**Masalah:** [CashierShift.php](file:///c:/Laravel-Device-Monitoring/app/Models/CashierShift.php) ada sebagai model, tapi:
- Tidak ada migration untuk table `cashier_shifts`
- Tidak digunakan di controller, route, atau view manapun
- Tidak relevan dengan domain "Device Monitoring"

**Perbaikan:** Hapus model ini, atau buat migration + integrasinya jika memang dibutuhkan.

### 10. NotificationService Crash Jika Device Tidak Punya Outlet

**Masalah di** [NotificationService.php](file:///c:/Laravel-Device-Monitoring/app/Services/NotificationService.php#L14-L16):
```php
$device = $alert->device;
$outlet = $device->outlet;        // ❌ bisa null (outlet_id nullable)
$user   = $outlet->user;          // ❌ NPE jika outlet null
```

Karena `devices.outlet_id` sudah diubah menjadi **nullable** (migration `2026_03_29_000001`), kode ini akan crash.

### 11. Tidak Ada Rate Limiting pada API

Tidak ada `throttle` middleware pada API routes. Ini sangat berbahaya terutama pada:
- `POST /api/v1/login` — bisa di-brute-force
- `POST /api/v1/devices/{serial}/data` — bisa di-flood

---

## 📋 MEDIUM — Peningkatan Kualitas Kode

### 12. Duplikasi Logic — `isOnline()`/`isOn()` dan `isOffline()`/`isOff()`

**Masalah di** [Device.php](file:///c:/Laravel-Device-Monitoring/app/Http/Controllers/Web/DeviceController.php):
```php
public function isOnline(): bool { return $this->status === DeviceStatus::Online; }
public function isOn(): bool     { return $this->status === DeviceStatus::Online; }  // Duplikat!

public function isOffline(): bool { return $this->status === DeviceStatus::Offline; }
public function isOff(): bool     { return $this->status === DeviceStatus::Offline; }  // Duplikat!
```

**Perbaikan:** Pilih satu naming convention dan alias yang lain, atau hapus duplikat. 

### 13. Tidak Ada Middleware Authorization untuk User Management

**Masalah di** [web.php](file:///c:/Laravel-Device-Monitoring/routes/web.php#L50):
```php
Route::resource('users', UserController::class)->middleware('auth');
```

Comment bilang "Owner only" tapi middleware hanya `auth`. Authorization `abort_unless($request->user()->isOwner(), 403)` dilakukan di setiap method controller — ini seharusnya menjadi middleware terpisah.

### 14. Logout Logic Inline di Route File

**Masalah di** [web.php](file:///c:/Laravel-Device-Monitoring/routes/web.php#L53-L60):
```php
Route::post('/logout', function () {
    auth()->guard()->logout();
    // ... 5 baris logic
})->name('logout');
```

**Perbaikan:** Pindahkan ke `AuthController::logout()` method.

### 15. `HandleInertiaRequests` Tidak Extend Inertia Middleware

**Masalah di** [HandleInertiaRequests.php](file:///c:/Laravel-Device-Monitoring/app/Http/Middleware/HandleInertiaRequests.php):
Middleware ini dibuat custom tanpa extend `Inertia\Middleware`. Ini berarti:
- Asset versioning tidak berfungsi (no cache-busting)
- `rootView` harus di-set manual

### 16. Frontend Router Directory Kosong

[resources/js/router/](file:///c:/Laravel-Device-Monitoring/resources/js/router) — direktori kosong. Karena project menggunakan Inertia, `vue-router` tidak dibutuhkan. Tapi `vue-router` ada di `package.json` dependencies → unnecessary dependency.

### 17. Mixed Language in Code Comments

Comments menggunakan campuran Bahasa Indonesia dan Inggris. Untuk project profesional, pilih **satu bahasa** (idealnya Inggris untuk kolaborasi internasional).

```php
// Cek semua device yang punya heartbeat    ← Bahasa Indonesia
// Watchdog: cek device offline setiap 5 menit

// Skip kalau sudah offline atau maintenance
```

### 18. `DeviceIngestService::checkThresholds()` Dead Code

```php
public function checkThresholds(Device $device, array $data): ?DeviceAlert
{
    return null; // No sensor-based thresholds anymore
}
```

Method ini tidak digunakan dan hanya return null. Hapus dead code.

### 19. `WilayahService::clearCache()` Terlalu Agresif

```php
public function clearCache(): void
{
    Cache::flush();  // ❌ Menghapus SEMUA cache, bukan hanya wilayah
}
```

**Perbaikan:** Gunakan `Cache::forget()` dengan key spesifik, atau gunakan cache tags.

---

## 📝 MINOR — Polish untuk Production-Ready

### 20. README Masih Default Laravel

[README.md](file:///c:/Laravel-Device-Monitoring/README.md) masih menggunakan template default Laravel. Untuk project profesional, README harus berisi:
- Deskripsi project
- Prerequisites & system requirements
- Setup instructions step-by-step
- Environment variables yang dibutuhkan
- Arsitektur overview
- API documentation summary
- Kontributor & license

### 21. Tidak Ada CI/CD Pipeline

Project tidak memiliki:
- `.github/workflows/` untuk GitHub Actions
- `Dockerfile` / `docker-compose.yml`
- Config deployment apapun

Minimum yang dibutuhkan:
```yaml
# .github/workflows/ci.yml
- Run PHPUnit/Pest tests
- Run PHP Pint (linter)
- Run TypeScript type-check
- Build Vite assets
```

### 22. Dependency Placement Salah di `package.json`

**Masalah di** [package.json](file:///c:/Laravel-Device-Monitoring/package.json):

Packages ini ada di `devDependencies` tapi seharusnya di `dependencies`:
- `vue` — runtime framework
- `@inertiajs/vue3` — runtime dependency
- `axios` — runtime HTTP client
- `laravel-echo` — runtime WebSocket

Packages ini ada di `dependencies` tapi seharusnya di `devDependencies`:
- `tailwindcss-animate` — build-time only

### 23. Database SQLite File Di-commit

[database/database.sqlite](file:///c:/Laravel-Device-Monitoring/database/database.sqlite) (139KB) — file database binary di-commit. Tambahkan ke `.gitignore`.

### 24. `StoreDeviceRequest` Hardcodes Device Types

```php
'type' => 'required|string|in:game_console,arcade',
```

Tapi di seeder, type yang digunakan adalah `temperature_sensor`. Ini **inkonsisten** — device types harus centralized (gunakan Enum atau config).

### 25. Factory tidak match dengan schema saat ini

Factory files (`DeviceLogFactory`, `DeviceFactory`, dll.) kemungkinan masih mereferensikan kolom lama (`temperature`, `humidity`, dll.), sama seperti seeder.

### 26. Missing Error Handling di OutletController CSV Import

[OutletController.php](file:///c:/Laravel-Device-Monitoring/app/Http/Controllers/Web/OutletController.php#L240):
```php
$row = array_combine($headers, $data);
```

Jika jumlah kolom data tidak cocok dengan jumlah headers, `array_combine()` bisa return `false` → crash.

### 27. Tidak Ada Soft Deletes

Model `Device`, `Outlet`, `User` menggunakan hard delete. Untuk production, gunakan `SoftDeletes` agar data bisa di-restore dan ada audit trail.

---

## 🏗️ Rekomendasi Arsitektur untuk Level Profesional

### A. Yang Sudah Baik ✅
1. **Service Layer** — `DeviceIngestService`, `NotificationService` memisahkan business logic dari controller
2. **API Resources** — Konsisten menggunakan `JsonResource` untuk response transformation  
3. **Enum Usage** — `DeviceStatus`, `AlertType`, `AlertSeverity`, `UserRole` menggunakan PHP 8.1 Enums
4. **Broadcasting** — Real-time updates via Laravel Reverb + Events
5. **API Versioning** — `/api/v1/` prefix sudah diterapkan
6. **Typed Frontend** — TypeScript interfaces untuk domain models
7. **Scheduled Jobs** — `CheckOfflineDevices` untuk health monitoring
8. **Form Requests** — Validasi terpisah di `StoreDeviceRequest`, `IngestDeviceDataRequest`

### B. Yang Harus Ditambahkan 🔧

| Komponen | Status | Prioritas |
|---|---|---|
| **Laravel Policies** | ❌ Tidak ada | 🔴 Tinggi |
| **API Rate Limiting** | ❌ Tidak ada | 🔴 Tinggi |
| **Feature & Unit Tests** | ❌ Boilerplate only | 🔴 Tinggi |
| **CI/CD Pipeline** | ❌ Tidak ada | 🟡 Sedang |
| **Docker Setup** | ❌ Tidak ada | 🟡 Sedang |
| **API Documentation** (Swagger/Scribe) | ❌ Tidak ada | 🟡 Sedang |
| **Logging Strategy** | ⚠️ Minimal | 🟡 Sedang |
| **Error Monitoring** (Sentry/Bugsnag) | ❌ Tidak ada | 🟡 Sedang |
| **Database Indexes** | ⚠️ Belum diaudit | 🟡 Sedang |
| **Soft Deletes** | ❌ Tidak ada | 🟢 Rendah |
| **Activity Log / Audit Trail** | ❌ Tidak ada | 🟢 Rendah |
| **Health Check Endpoint** | ❌ Tidak ada | 🟢 Rendah |

---

## 📌 Prioritas Perbaikan (Urutan Kerja)

### Phase 1 — Security Fix (Hari 1-2)
1. Hapus `.env` dari Git tracking + regenerate secrets
2. Tambahkan authorization (Policies) pada semua resource controllers
3. Secure API ingestion endpoint (API key / device token)
4. Tambahkan rate limiting pada API routes
5. Fix `orWhere` scope leak pada search filters

### Phase 2 — Data Integrity (Hari 3-4)
6. Sinkronkan seeder & factory dengan schema terbaru
7. Hapus dead code (`checkThresholds`, `CashierShift`, router dir kosong)
8. Fix `NotificationService` null safety
9. Fix `DeviceReportService` kolom lama
10. Sinkronkan `.env.example`

### Phase 3 — Testing (Hari 5-7)
11. Tulis test untuk semua Auth flows
12. Tulis test untuk Device CRUD + authorization
13. Tulis test untuk API ingestion
14. Tulis test untuk `CheckOfflineDevices` job
15. Enable `RefreshDatabase` di Pest.php

### Phase 4 — DevOps & Polish (Hari 8-10)
16. Setup CI/CD (GitHub Actions)
17. Tuliskan README yang proper
18. Tambahkan Docker setup
19. Tambahkan API documentation
20. Setup error monitoring

---

> [!TIP]
> Project ini punya **fondasi arsitektur yang baik** (service layer, enums, events, API versioning, typed frontend). Masalah utamanya ada di **keamanan** dan **testing**. Dengan perbaikan Phase 1-3, project ini bisa mencapai standar profesional.
