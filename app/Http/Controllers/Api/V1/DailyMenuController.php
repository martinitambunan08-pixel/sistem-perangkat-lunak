<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyMenuController extends Controller
{
    // GET /api/v1/daily-menus
    public function index()
    {
        $menus = DailyMenu::with('items.product')
            ->orderByDesc('menu_date')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar menu harian berhasil diambil.',
            'data' => $menus,
        ]);
    }

    // GET /api/v1/daily-menus/{id}
    public function show($id)
    {
        $menu = DailyMenu::with('items.product')->find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu harian tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail menu harian berhasil diambil.',
            'data' => $menu,
        ]);
    }

    // POST /api/v1/daily-menus
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_date' => ['required', 'date', 'date_format:Y-m-d'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.portions' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        // Hanya produk yang tersedia yang dapat dipilih.
        $productIds = collect($validated['items'])
            ->pluck('product_id');

        $availableCount = Product::whereIn('id', $productIds)
            ->where('is_available', true)
            ->count();

        if ($availableCount !== $productIds->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Satu atau lebih produk tidak tersedia untuk dipilih.',
                ],
            ]);
        }

        // Simpan menu dan seluruh item dalam satu transaksi database.
        $menu = DB::transaction(function () use ($validated) {
            $menu = DailyMenu::create([
                'menu_date' => $validated['menu_date'],
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                DailyMenuItem::create([
                    'daily_menu_id' => $menu->id,
                    'product_id' => $item['product_id'],
                    'initial_portions' => $item['portions'],
                    'remaining_portions' => $item['portions'],
                ]);
            }

            return $menu;
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu harian berhasil disimpan.',
            'data' => $menu->load('items.product'),
        ], 201);
    }
}