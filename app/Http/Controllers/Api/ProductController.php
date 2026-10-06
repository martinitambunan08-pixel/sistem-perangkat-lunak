<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MachineSlot;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/v1/products (Katalog Produk)
    public function index()
    {
        $products = Product::all();

        return response()->json([
            'success' => true,
            'message' => 'Daftar katalog produk',
            'data' => $products
        ]);
    }

    // GET /api/v1/inventory (Stok Slot per Mesin)
    public function inventory(Request $request)
    {
        $query = MachineSlot::with(['product', 'machine']);

        if ($request->has('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }

        $slots = $query->get()->map(function ($slot) {
            return [
                'id' => $slot->id,
                'machine_id' => $slot->machine_id,
                'slot_code' => $slot->slot_code,
                'product' => $slot->product,
                'current_qty' => $slot->current_qty,
                'hold_qty' => $slot->hold_qty ?? 0,
                'available_qty' => max(0, $slot->current_qty - ($slot->hold_qty ?? 0)),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar stok slot mesin',
            'data' => $slots
        ]);
    }
}