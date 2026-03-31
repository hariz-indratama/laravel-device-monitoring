# Sidebar Navigation

## Structure

```
DeviceMonitor
├── MAIN
│   └── Dashboard                   /dashboard
├── DEVICES
│   ├── All Devices                 /devices
│   ├── Add Device                  /devices/create
│   └── Alerts                     /alerts
└── SITES
    ├── All Outlets                 /outlets
    └── Add Outlet                  /outlets/create
```

## Implementation

Sidebar component: `resources/js/Components/layout/Sidebar.vue`

### Design
- Width: `w-64` (256px)
- Background: `bg-slate-900` (Slate-900)
- Text: `text-slate-300` default, `text-white` on hover
- Active item: `bg-emerald-600 text-white`
- Logo: `DeviceMonitor` dengan accent emerald-400 pada kata "Device"

### Navigation Items

| Label | Href | Icon | Active Condition |
|---|---|---|---|
| Dashboard | /dashboard | Grid icon | URL starts with /dashboard |
| Devices | /devices | Monitor icon | URL starts with /devices |
| Outlets | /outlets | Building icon | URL starts with /outlets |
| Alerts | /alerts | Bell icon | URL starts with /alerts |

### User Section (Bottom)
- Avatar circle dengan initial huruf nama user
- Nama + role user
- Logout button (icon only)
