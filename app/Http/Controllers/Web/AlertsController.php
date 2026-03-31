<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DeviceAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlertsController extends Controller
{
    public function index(Request $request)
    {
        $query = DeviceAlert::query()
            ->forUser($request->user())
            ->with('device', 'resolver')
            ->when($request->filled('search'), fn($q) => $q->where('message', 'like', "%{$request->search}%"))
            ->when($request->filled('severity'), fn($q, $s) => $q->where('severity', $s))
            ->when($request->filled('status'), fn($q, $s) => $q->when(
                $s === 'unresolved',
                fn($qq) => $qq->whereNull('resolved_at'),
                fn($qq) => $qq->whereNotNull('resolved_at')
            ))
            ->latest();

        $alerts = $query->paginate(20)->withQueryString();

        $stats = [
            'total'      => DeviceAlert::query()->forUser($request->user())->count(),
            'critical'   => DeviceAlert::query()->forUser($request->user())->where('severity', 'critical')->whereNull('resolved_at')->count(),
            'warning'    => DeviceAlert::query()->forUser($request->user())->where('severity', 'warning')->whereNull('resolved_at')->count(),
            'unresolved' => DeviceAlert::query()->forUser($request->user())->whereNull('resolved_at')->count(),
            'resolved'   => DeviceAlert::query()->forUser($request->user())->whereNotNull('resolved_at')->count(),
        ];

        return Inertia::render('Alerts/Index', [
            'alerts'  => $alerts,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'severity', 'status']),
        ]);
    }

    public function resolve(Request $request, DeviceAlert $alert)
    {
        abort_unless($request->user()->can('resolve', $alert), 403);

        $alert->update([
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Alert resolved.');
    }

    public function bulkResolve(Request $request)
    {
        $ids = $request->input('ids', []);
        $resolved = DeviceAlert::query()
            ->forUser($request->user())
            ->whereIn('id', $ids)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => now(),
                'resolved_by' => $request->user()->id,
            ]);

        return back()->with('success', "{$resolved} alert(s) resolved.");
    }
}
