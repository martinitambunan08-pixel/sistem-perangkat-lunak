<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::with([
            'slots.product',
            'inventories.product',
            'statusLogs',
            'telemetryData',
            'maintenanceLogs',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar mesin berhasil diambil.',
            'data' => $machines,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $machine = Machine::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil dibuat.',
            'data' => $machine,
        ], 201);
    }

    public function show(Machine $machine)
    {
        $machine->load([
            'slots.product',
            'inventories.product',
            'statusLogs',
            'telemetryData',
            'maintenanceLogs',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail mesin berhasil diambil.',
            'data' => $machine,
        ]);
    }

    
public function monitoring(Machine $machine)
{
    $telemetry = $machine->telemetryData()
        ->orderByDesc('recorded_at')
        ->orderByDesc('id')
        ->first();

    $temperature = $telemetry !== null
        && $telemetry->temperature !== null
        ? (float) $telemetry->temperature
        : null;

    $temperatureStatus = 'no_data';

    if ($temperature !== null) {
        $temperatureStatus = $temperature > 70
            ? 'warning'
            : 'normal';
    }

    return response()->json([
        'success' => true,
        'message' => 'Data monitoring mesin berhasil diambil.',
        'data' => [
            'machine_id' => $machine->id,
            'machine_name' => $machine->name,
            'machine_status' => $machine->status,
            'location' => $machine->location,
            'temperature' => $temperature,
            'temperature_status' => $temperatureStatus,
            'humidity' => $telemetry
                ? $telemetry->humidity
                : null,
            'voltage' => $telemetry
                ? $telemetry->voltage
                : null,
            'sensor_status' => $telemetry
                ? $telemetry->sensor_status
                : 'no_data',
            'sensor_data' => $telemetry
                ? $telemetry->sensor_data
                : null,
            'recorded_at' => $telemetry
                ? $telemetry->recorded_at
                : null,
        ],
    ]);
}


    public function update(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]);

        $machine->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil diperbarui.',
            'data' => $machine,
        ]);
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil dihapus.',
        ]);
    }
}