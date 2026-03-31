<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceTesterController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $devices = Device::query()
            ->forUser($user)
            ->with('outlet')
            ->orderBy('name')
            ->get()
            ->map(fn ($d) => [
                'id'            => $d->id,
                'name'          => $d->name,
                'serial_number' => $d->serial_number,
                'type'          => $d->type,
                'status'        => $d->status->value,
                'outlet'        => $d->outlet?->name,
                'uptime'        => $d->uptime_started_at?->toIso8601String(),
            ]);

        return Inertia::render('DeviceTester/Index', [
            'devices' => $devices,
        ]);
    }
}
