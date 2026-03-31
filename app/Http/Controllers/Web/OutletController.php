<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutletResource;
use App\Http\Resources\DeviceResource;
use App\Models\Outlet;
use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $outlets = Outlet::query()
            ->forUser($request->user())
            ->withCount('devices')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->filled('sort'), function ($q) use ($request) {
                match ($request->sort) {
                    'name_asc'  => $q->orderBy('name'),
                    'name_desc' => $q->orderBy('name', 'desc'),
                    'devices'   => $q->orderBy('devices_count', 'desc'),
                    default     => $q->latest(),
                };
            }, fn($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Outlets/Index', [
            'outlets' => (fn($p) => [
                'data' => $p->items()
                    ? collect($p->items())->map(fn($m) => (new OutletResource($m))->jsonSerialize())->all()
                    : [],
                'total'       => $p->total(),
                'per_page'    => $p->perPage(),
                'current_page'=> $p->currentPage(),
                'last_page'   => $p->lastPage(),
                'from'        => $p->firstItem(),
                'to'          => $p->lastItem(),
            ])($outlets),
            'filters' => $request->only(['search', 'sort']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Outlets/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'kota'        => 'nullable|string|max:100',
            'kecamatan'   => 'nullable|string|max:100',
            'desa'        => 'nullable|string|max:100',
            'provinsi'    => 'nullable|string|max:100',
            'kode_pos'    => 'nullable|string|max:10',
        ]);

        $validated['user_id'] = $request->user()->id;
        $outlet = Outlet::create($validated);

        return redirect()->route('outlets.show', $outlet->id)->with('success', 'Outlet created.');
    }

    public function show(Request $request, Outlet $outlet)
    {
        $this->authorize('view', $outlet);

        $outlet->load(['devices' => fn($q) => $q->orderByDesc('assigned_at')]);

        // Devices available to assign (not yet assigned to any outlet)
        $availableDevices = Device::query()
            ->forUser($request->user())
            ->whereNull('outlet_id')
            ->orderBy('name')
            ->get()
            ->map(fn($d) => (new DeviceResource($d))->jsonSerialize());

        return Inertia::render('Outlets/Show', [
            'outlet'            => (new OutletResource($outlet))->jsonSerialize(),
            'availableDevices'  => $availableDevices,
        ]);
    }

    /**
     * Assign an existing device to this outlet.
     */
    public function assignDevice(Request $request, Outlet $outlet)
    {
        $this->authorize('update', $outlet);

        $validated = $request->validate([
            'device_id'    => 'required|exists:devices,id',
            'assigned_at'  => 'nullable|date',
        ]);

        $device = Device::query()
            ->forUser($request->user())
            ->whereNull('outlet_id')
            ->findOrFail($validated['device_id']);

        $device->update([
            'outlet_id'   => $outlet->id,
            'assigned_at' => $validated['assigned_at'] ?? now(),
        ]);

        return back()->with('success', "Device {$device->name} added to {$outlet->name}.");
    }

    /**
     * Remove a device from this outlet (unassign).
     */
    public function unassignDevice(Request $request, Outlet $outlet, Device $device)
    {
        $this->authorize('update', $outlet);

        if ($device->outlet_id !== $outlet->id) {
            abort(403, 'Device does not belong to this outlet.');
        }

        $device->update([
            'outlet_id'   => null,
            'assigned_at' => null,
        ]);

        return back()->with('success', "Device {$device->name} removed from {$outlet->name}.");
    }

    public function edit(Request $request, Outlet $outlet)
    {
        $this->authorize('update', $outlet);

        return Inertia::render('Outlets/Edit', [
            'outlet' => (new OutletResource($outlet))->jsonSerialize(),
        ]);
    }

    public function update(Request $request, Outlet $outlet)
    {
        $this->authorize('update', $outlet);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'kota'        => 'nullable|string|max:100',
            'kecamatan'   => 'nullable|string|max:100',
            'desa'        => 'nullable|string|max:100',
            'provinsi'    => 'nullable|string|max:100',
            'kode_pos'    => 'nullable|string|max:10',
        ]);

        $outlet->update($validated);

        return redirect()->back()->with('success', 'Outlet updated.');
    }

    public function destroy(Request $request, Outlet $outlet)
    {
        $this->authorize('delete', $outlet);

        // Unassign all devices from this outlet before deleting
        Device::query()->where('outlet_id', $outlet->id)->update([
            'outlet_id'   => null,
            'assigned_at' => null,
        ]);

        $outlet->delete();

        return redirect()->route('outlets.index')->with('success', 'Outlet deleted.');
    }

    /**
     * Download CSV template for bulk import.
     */
    public function importTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="outlets_template.csv"',
        ];

        $columns = ['name', 'address', 'phone', 'email', 'kota', 'provinsi', 'kode_pos'];

        $rows = [
            ['Warehouse Pusat Jakarta', 'Jl. Industri No.1, Jakarta', '0211234567', 'warehouse@example.com', 'Jakarta', 'DKI Jakarta', '12345'],
            ['Toko Bandung', 'Jl. Braga No.10, Bandung', '0221234567', 'bandung@example.com', 'Bandung', 'Jawa Barat', '40111'],
            ['Gudang Surabaya', 'Jl. Rungkut No.5, Surabaya', '0311234567', 'surabaya@example.com', 'Surabaya', 'Jawa Timur', '60293'],
        ];

        $callback = function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle CSV import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'Could not read CSV file.');
        }

        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);
        $allowedKeys = ['name', 'address', 'phone', 'email', 'kota', 'provinsi', 'kode_pos'];
        $headers = array_intersect($headers, $allowedKeys);

        if (!in_array('name', $headers)) {
            fclose($handle);
            return back()->with('error', 'CSV must have a "name" column.');
        }

        $rows = [];
        $errors = [];
        $line = 2;

        while (($data = fgetcsv($handle)) !== false) {
            // Guard against column count mismatch
            if (count($data) !== count($headers)) {
                $errors[] = "Row {$line}: column count mismatch (expected " . count($headers) . ", got " . count($data) . ").";
                $line++;
                continue;
            }

            $row = array_combine($headers, $data);

            if (empty(array_filter($row))) {
                continue; // skip empty rows
            }

            $name = trim($row['name'] ?? '');
            if (empty($name)) {
                $errors[] = "Row {$line}: name is required.";
                $line++;
                continue;
            }

            if (strlen($name) > 255) {
                $errors[] = "Row {$line}: name exceeds 255 characters.";
                $line++;
                continue;
            }

            $rows[] = [
                'user_id'  => $request->user()->id,
                'name'     => $name,
                'address'  => trim($row['address'] ?? '') ?: null,
                'phone'    => trim($row['phone'] ?? '') ?: null,
                'email'    => trim($row['email'] ?? '') ?: null,
                'kota'     => trim($row['kota'] ?? '') ?: null,
                'provinsi' => trim($row['provinsi'] ?? '') ?: null,
                'kode_pos' => trim($row['kode_pos'] ?? '') ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $line++;
        }
        fclose($handle);

        if (!empty($errors)) {
            return back()->with('error', implode(' ', $errors));
        }

        if (empty($rows)) {
            return back()->with('error', 'No valid data found in the file.');
        }

        $created = 0;
        foreach ($rows as $row) {
            Outlet::create($row);
            $created++;
        }

        return redirect()->route('outlets.index')->with(
            'success',
            "{$created} outlet(s) imported successfully."
        );
    }
}
