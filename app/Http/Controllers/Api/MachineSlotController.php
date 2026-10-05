<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineSlot;
use Illuminate\Http\Request;

class MachineSlotController extends Controller
{
    public function index()
    {
        $slots = MachineSlot::with([
            'machine',
            'product',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar slot mesin berhasil diambil.',
            'data' => $slots,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'product_id' => ['required', 'exists:products,id'],
            'slot_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $slot = MachineSlot::create($validated);

        $slot->load([
            'machine',
            'product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slot mesin berhasil dibuat.',
            'data' => $slot,
        ], 201);
    }

    public function show(MachineSlot $machineSlot)
    {
        $machineSlot->load([
            'machine',
            'product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail slot mesin berhasil diambil.',
            'data' => $machineSlot,
        ]);
    }

    public function update(Request $request, MachineSlot $machineSlot)
    {
        $validated = $request->validate([
            'machine_id' => ['sometimes', 'exists:machines,id'],
            'product_id' => ['sometimes', 'exists:products,id'],
            'slot_number' => ['sometimes', 'string', 'max:50'],
            'capacity' => ['sometimes', 'integer', 'min:0'],
            'current_stock' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]);

        $machineSlot->update($validated);

        $machineSlot->load([
            'machine',
            'product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slot mesin berhasil diperbarui.',
            'data' => $machineSlot,
        ]);
    }

    public function destroy(MachineSlot $machineSlot)
    {
        $machineSlot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slot mesin berhasil dihapus.',
        ]);
    }
}