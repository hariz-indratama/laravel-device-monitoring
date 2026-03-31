# Database Schema

## Tables

### 1. outlets
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK, auto increment |
| name | string | Nama lokasi/site |
| address | text | Nullable |
| phone | string(20) | Nullable |
| user_id | bigint FK | Owner (nullable) |
| created_at | timestamp | |
| updated_at | timestamp | |

### 2. devices
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| outlet_id | bigint FK | Outlet pemilik |
| user_id | bigint FK | Nullable |
| name | string | Nama device |
| serial_number | string | Unique |
| type | string | Default: temperature_sensor |
| location | string | Lokasi spesifik |
| status | string | online/offline/warning/maintenance |
| temperature_min | float | Default: 0°C |
| temperature_max | float | Default: 40°C |
| battery_threshold | int | Default: 20% |
| heartbeat_interval | int | Default: 300s |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3. device_logs (time-series)
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| device_id | bigint FK | Indexed (device_id, logged_at) |
| temperature | float | Nullable |
| humidity | float | Nullable |
| battery | int | Nullable (0-100) |
| signal_strength | int | Nullable (dBm) |
| logged_at | timestamp | Indexed |

### 4. device_alerts
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| device_id | bigint FK | |
| type | string | Enum: temperature_high, device_offline, battery_low... |
| message | text | |
| severity | string | info/warning/critical |
| data | json | Nullable, data tambahan |
| resolved_at | timestamp | Nullable |
| resolved_by | bigint FK | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |
| Index: (device_id, resolved_at) | | |

### 5. device_heartbeats
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| device_id | bigint FK | Unique |
| last_seen_at | timestamp | |
| missed_count | int | Default: 0 |
| interval_seconds | int | Default: 300 |

### 6. users (existing — unchanged)
### 7. outlets → devices → device_logs (relasi cascade)
