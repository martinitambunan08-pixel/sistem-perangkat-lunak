<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineStatusLog;
use Illuminate\Http\Request;

class MachineStatusLogController extends Controller
{
    public function index()
    {
        $logs = MachineStatusLog::with('machine')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar log status mesin berhasil diambil.',
            'data' => $logs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'status' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $log = MachineStatusLog::create($validated);

        $log->load('machine');

        return response()->json([
            'success' => true,
            'message' => 'Log status mesin berhasil dibuat.',
            'data' => $log,
        ], 201);
    }

    public function show(MachineStatusLog $machineStatusLog)
    {
        $machineStatusLog->load('machine');

        return response()->json([
            'success' => true,
            'message' => 'Detail log status mesin berhasil diambil.',
            'data' => $machineStatusLog,
        ]);
    }

    public function update(
        Request $request,
        MachineStatusLog $machineStatusLog
    ) {
        $validated = $request->validate([
            'machine_id' => ['sometimes', 'exists:machines,id'],
            'status' => ['sometimes', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'started_at' => ['sometimes', 'date'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $machineStatusLog->update($validated);

        $machineStatusLog->load('machine');

        return response()->json([
            'success' => true,
            'message' => 'Log status mesin berhasil diperbarui.',
            'data' => $machineStatusLog,
        ]);
    }

    public function destroy(MachineStatusLog $machineStatusLog)
    {
        $machineStatusLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log status mesin berhasil dihapus.',
        ]);
    }
}