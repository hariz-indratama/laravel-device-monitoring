# dailyPOS — Flutter Mobile

## Tech Stack
- **Framework:** Flutter (BLoC state management)
- **API Base:** `https://api.dailypos.test/api/v1`
- **Auth:** Laravel Sanctum Bearer Token

## Getting Started

```bash
# Clone & setup
cd mobile
flutter pub get

# Run
flutter run
```

## API Endpoints (from `docs/api_manifest.md`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Returns Sanctum Token |
| GET | `/products` | List with variants and stock |
| POST | `/sales/checkout` | Submit transaction |
| GET | `/reports/today` | Staff dashboard summary |
| GET | `/qris/generate/{sale_id}` | Returns QRIS image URL |

## Authentication Flow

```dart
// Login → receive Bearer token
// Store token securely (flutter_secure_storage)
// Attach to all requests: Authorization: Bearer <token>
```

## Key Screens
1. **Login** — Phone number or email login
2. **POS Screen** — Product grid + cart (touch-optimized)
3. **Customer Lookup** — Loyalty points display
4. **QRIS Payment** — Dynamic QRIS display
5. **Today's Summary** — Quick sales overview

## State Management (BLoC)
- `AuthBloc` — login/logout/token management
- `ProductBloc` — product list, search, barcode scan
- `CartBloc` — cart items, totals
- `CheckoutBloc` — payment flow, QRIS
- `ReportBloc` — today's summary
