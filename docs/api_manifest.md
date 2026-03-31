# API Manifest — Device Monitoring

## Base URL
`http://localhost/api/v1`

---

## Public Endpoints (Device Ingestion)

### `POST /devices/{serial_number}/data`
Ingest data dari device IoT. Tidak memerlukan auth.

**Payload:**
```json
{
  "temperature": 24.5,
  "humidity": 65.2,
  "battery": 87,
  "signal_strength": -55,
  "logged_at": "2026-03-27T10:00:00Z"
}
```

**Response (200):**
```json
{
  "success": true,
  "log": {
    "id": 1,
    "temperature": 24.5,
    "humidity": 65.2,
    "battery": 87,
    "logged_at": "2026-03-27T10:00:00Z"
  },
  "alert": null
}
```

**Jika alert triggered:**
```json
{
  "success": true,
  "log": { ... },
  "alert": {
    "id": 5,
    "type": "temperature_high",
    "message": "Suhu 42.5°C melebihi batas max 40°C"
  }
}
```

---

## Public: Authentication

### `POST /login`
Login untuk mobile app (Sanctum token).

**Payload:**
```json
{ "email": "admin@devicemonitor.id", "password": "password" }
```

**Response (200):**
```json
{
  "user": { "id": 1, "name": "Admin", "email": "...", "role": "owner" },
  "token": "1|abc..."
}
```

---

## Protected (Sanctum Bearer Token)

### `POST /logout`
Hapus token aktif.

### `GET /me`
Get user profile.

### `GET /devices/{serial_number}`
Get device info untuk mobile app.

**Response:**
```json
{
  "device": {
    "id": 1,
    "name": "Sensor Server Rack 1",
    "serial_number": "DEV-001",
    "status": "online",
    "latest_log": { "temperature": 23.5, "humidity": 60 }
  }
}
```

---

## Web Routes (Inertia — Cookie Auth)

| Method | Path | Controller |
|---|---|---|
| GET | /dashboard | DashboardController |
| GET | /devices | DeviceController@index |
| GET | /devices/create | DeviceController@create |
| POST | /devices | DeviceController@store |
| GET | /devices/{id} | DeviceController@show |
| GET | /devices/{id}/edit | DeviceController@edit |
| PATCH | /devices/{id} | DeviceController@update |
| DELETE | /devices/{id} | DeviceController@destroy |
| PATCH | /devices/{id}/toggle | DeviceController@toggle |
| GET | /outlets | OutletController@index |
| POST | /outlets | OutletController@store |
| GET | /outlets/{id} | OutletController@show |
| PATCH | /outlets/{id} | OutletController@update |
| DELETE | /outlets/{id} | OutletController@destroy |
