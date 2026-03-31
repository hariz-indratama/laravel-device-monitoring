<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = Device::query()
            ->forUser($request->user())
            ->with('outlet')
            ->when($request->search, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('name', 'like', "%{$s}%")
                    ->orWhere('serial_number', 'like', "%{$s}%");
            }))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        return Inertia::render('Devices/Index', [
            'devices' => DeviceResource::collection($devices),
            'filters' => $request->only(['search', 'status']),
            'stats' => [
                'total'   => Device::forUser($request->user())->count(),
                'online'  => Device::forUser($request->user())->where('status', 'online')->count(),
                'offline' => Device::forUser($request->user())->where('status', 'offline')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Devices/Create', [
            'outlets' => \App\Models\Outlet::forUser($request->user())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreDeviceRequest $request)
    {
        $device = Device::create([
            'name'          => $request->validated('name'),
            'serial_number' => $request->validated('serial_number'),
            'type'          => $request->validated('type'),
            'outlet_id'     => $request->validated('outlet_id'),
            'user_id'       => $request->user()->id,
            'status'        => 'offline',
            'assigned_at'   => now(),
        ]);
        return redirect()->route('devices.show', $device->id)->with('success', 'Device created.');
    }

    public function show(Request $request, Device $device)
    {
        $this->authorize('view', $device);

        $device->load(['outlet', 'alerts' => fn($q) => $q->latest()->limit(20)]);

        $logsPaginated = $device->logs()
            ->orderByDesc('logged_at')
            ->paginate(5)
            ->withQueryString();

        return Inertia::render('Devices/Show', [
            'device' => (new \App\Http\Resources\DeviceResource($device))->jsonSerialize(),
            'uptime_seconds'    => $device->getUptimeInSeconds(),
            'formatted_uptime'  => $device->getFormattedUptime(),
            'logs'      => collect($logsPaginated->items())->map(fn($log) => (new \App\Http\Resources\DeviceLogResource($log))->jsonSerialize())->all(),
            'logs_meta' => [
                'current_page' => $logsPaginated->currentPage(),
                'last_page'    => $logsPaginated->lastPage(),
                'per_page'     => $logsPaginated->perPage(),
                'total'        => $logsPaginated->total(),
                'from'         => $logsPaginated->firstItem(),
                'to'           => $logsPaginated->lastItem(),
            ],
            'alerts'    => collect($device->alerts)->map(fn($alert) => (new \App\Http\Resources\DeviceAlertResource($alert))->jsonSerialize())->all(),
            'active_alerts_count' => $device->alerts->whereNull('resolved_at')->count(),
        ]);
    }

    public function edit(Request $request, Device $device)
    {
        $this->authorize('update', $device);

        return Inertia::render('Devices/Edit', [
            'device' => (new DeviceResource($device))->jsonSerialize(),
        ]);
    }

    public function update(UpdateDeviceRequest $request, Device $device)
    {
        $this->authorize('update', $device);

        $device->update($request->validated());
        return redirect()->route('devices.show', $device->id)->with('success', 'Device updated.');
    }

    public function destroy(Request $request, Device $device)
    {
        $this->authorize('delete', $device);

        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted.');
    }

    public function toggle(Request $request, Device $device)
    {
        $this->authorize('toggle', $device);

        if ($device->isOn()) {
            $device->markOffline();
        } else {
            $device->markOnline();
        }
        return back()->with('success', 'Device status updated.');
    }
}
