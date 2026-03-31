# Device Monitoring — Business Rules

## 1. Device Status

### Status Types
- **Online:** Device mengirim heartbeat & data dalam threshold waktu
- **Offline:** Device tidak mengirim data > N menit (configurable, default: 5 menit)
- **Warning:** Threshold warning tercapai (suhu mendekati batas)
- **Maintenance:** Device sengaja di-set offline oleh admin

### Status Transition
```
Device mengirim data → status = online
Watchdog deteksi stale heartbeat → status = offline
Admin klik toggle → status = maintenance
Admin klik toggle lagi → status = online
```

## 2. Alert Logic

### Alert Types
| Type | Severity | Trigger |
|---|---|---|
| temperature_high | Critical | Suhu > temperature_max |
| temperature_low | Warning | Suhu < temperature_min |
| battery_low | Warning | Battery < battery_threshold |
| heartbeat_missed | Critical | last_seen_at > threshold |
| sensor_malfunction | Critical | Hardware error |

### Auto-Resolution
- `temperature_high`, `temperature_low`, `battery_low` auto-resolved ketika data baru masuk dalam range
- `heartbeat_missed` auto-resolved ketika device kembali online
- `sensor_malfunction` harus di-resolve manual oleh user

## 3. Watchdog (Silent Failure Detection)

- **Schedule:** Setiap 5 menit via Laravel scheduler
- **Logic:** Cek semua `DeviceHeartbeat`, jika `last_seen_at + interval < now()` → increment `missed_count`
- **Action on stale (missed_count > 0):**
  1. Update device status → `offline`
  2. Buat alert type `heartbeat_missed`
  3. Dispatch `DeviceStatusUpdated` event
  4. Kirim notifikasi Telegram (jika configured)

## 4. Real-time Broadcast

- Setiap perubahan status device → dispatch `DeviceStatusUpdated` → Reverb broadcast
- Setiap alert baru → dispatch `DeviceAlertCreated` → broadcast ke user channel
- Frontend Pinia store update otomatis via Laravel Echo listener

## 5. Data Retention

- `device_logs` dipertahankan 30 hari (configurable)
- Older logs harus di-prune secara berkala (future: scheduled command)

## 6. User Roles

- **Owner:** Full access — CRUD device, outlet, lihat alert, ubah threshold
- **Staff:** View only — lihat dashboard dan device, tidak bisa edit/resolve alert
