<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductInventory;
use Illuminate\Http\Request;

class ProductInventoryController extends Controller
{
    public function index()
    {
        $inventories = ProductInventory::with([
            'machine',
            'product',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar inventory berhasil diambil.',
            'data' => $inventories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'maximum_stock' => ['required', 'integer', 'min:0'],
            'last_restocked_at' => ['nullable', 'date'],
        ]);

        $inventory = ProductInventory::create($validated);

        $inventory->load([
            'machine',
            'product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory berhasil dibuat.',
            'data' => $inventory,
        ], 201);
    }

    public function show(ProductInventory $productInventory)
    {
        $productInventory->load([
            'machine',
            'product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail inventory berhasil diambil.',
            'data' => $productInventory,
        ]);
    }

    public function update(Request $request, ProductInventory $productInventory)
    {
        $validated = $request->validate([
            'machine_id' => ['sometimes', 'exists:machines,id'],
            'product_id' => ['sometimes', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'minimum_stock' => ['sometimes', 'integer', 'min:0'],
            'maximum_stock' => ['sometimes', 'integer', 'min:0'],
            'last_restocked_at' => ['nullable', 'date'],
        ]);

        $productInventory->update($validated);

        $productInventory->load([
            'machine',
            'product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory berhasil diperbarui.',
            'data' => $productInventory,
        ]);
    }

    public function destroy(ProductInventory $productInventory)
    {
        $productInventory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory berhasil dihapus.',
        ]);
    }
}