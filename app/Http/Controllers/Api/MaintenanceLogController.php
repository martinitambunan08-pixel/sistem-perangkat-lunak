<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;

class MaintenanceLogController extends Controller
{
    public function index()
    {
        $maintenanceLogs = MaintenanceLog::with([
            'machine',
            'technician.user',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar maintenance log berhasil diambil.',
            'data' => $maintenanceLogs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'technician_id' => ['required', 'exists:technicians,id'],
            'type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'started_at' => ['required', 'date'],
            'completed_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $maintenanceLog = MaintenanceLog::create($validated);

        $maintenanceLog->load([
            'machine',
            'technician.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance log berhasil dibuat.',
            'data' => $maintenanceLog,
        ], 201);
    }

    public function show(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->load([
            'machine',
            'technician.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail maintenance log berhasil diambil.',
            'data' => $maintenanceLog,
        ]);
    }

    public function update(Request $request, MaintenanceLog $maintenanceLog)
    {
        $validated = $request->validate([
            'machine_id' => ['sometimes', 'exists:machines,id'],
            'technician_id' => ['sometimes', 'exists:technicians,id'],
            'type' => ['sometimes', 'string', 'max:50'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'max:50'],
            'started_at' => ['sometimes', 'date'],
            'completed_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $maintenanceLog->update($validated);

        $maintenanceLog->load([
            'machine',
            'technician.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance log berhasil diperbarui.',
            'data' => $maintenanceLog,
        ]);
    }

    public function destroy(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance log berhasil dihapus.',
        ]);
    }
}