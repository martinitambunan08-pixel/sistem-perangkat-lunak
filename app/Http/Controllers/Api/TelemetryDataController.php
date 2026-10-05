<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelemetryData;
use Illuminate\Http\Request;

class TelemetryDataController extends Controller
{
    public function index()
    {
        $telemetry = TelemetryData::with('machine')
            ->latest('recorded_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data telemetry berhasil diambil.',
            'data' => $telemetry,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'temperature' => ['nullable', 'numeric'],
            'humidity' => ['nullable', 'numeric'],
            'voltage' => ['nullable', 'numeric'],
            'sensor_status' => ['required', 'string', 'max:50'],
            'sensor_data' => ['nullable', 'array'],
            'recorded_at' => ['required', 'date'],
        ]);

        $telemetry = TelemetryData::create($validated);

        $telemetry->load('machine');

        return response()->json([
            'success' => true,
            'message' => 'Data telemetry berhasil disimpan.',
            'data' => $telemetry,
        ], 201);
    }

    public function show(TelemetryData $telemetryData)
    {
        $telemetryData->load('machine');

        return response()->json([
            'success' => true,
            'message' => 'Detail data telemetry berhasil diambil.',
            'data' => $telemetryData,
        ]);
    }

    public function update(Request $request, TelemetryData $telemetryData)
    {
        $validated = $request->validate([
            'machine_id' => ['sometimes', 'exists:machines,id'],
            'temperature' => ['nullable', 'numeric'],
            'humidity' => ['nullable', 'numeric'],
            'voltage' => ['nullable', 'numeric'],
            'sensor_status' => ['sometimes', 'string', 'max:50'],
            'sensor_data' => ['nullable', 'array'],
            'recorded_at' => ['sometimes', 'date'],
        ]);

        $telemetryData->update($validated);

        $telemetryData->load('machine');

        return response()->json([
            'success' => true,
            'message' => 'Data telemetry berhasil diperbarui.',
            'data' => $telemetryData,
        ]);
    }

    public function destroy(TelemetryData $telemetryData)
    {
        $telemetryData->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data telemetry berhasil dihapus.',
        ]);
    }
}