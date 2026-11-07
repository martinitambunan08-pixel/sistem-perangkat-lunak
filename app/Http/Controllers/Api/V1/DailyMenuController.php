<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class DailyMenuController extends Controller
{
    // GET /api/v1/daily-menus
    public function index()
    {
        $menus = DailyMenu::with('items.product')
             ->where('status', 'active')
             ->orderByDesc('menu_date')
             ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar menu aktif berhasil diambil.',
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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'menu_date' => [
            'required',
            'date',
            'date_format:Y-m-d',
            Rule::unique('daily_menus', 'menu_date')->ignore($id),
        ],
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

    $menu = DailyMenu::find($id);

    if (!$menu) {
        return response()->json([
            'success' => false,
            'message' => 'Menu harian tidak ditemukan.',
        ], 404);
    }

    $productIds = collect($validated['items'])->pluck('product_id');

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

    $menu = DB::transaction(function () use ($validated, $id) {
        $menu = DailyMenu::whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();

        $existingItems = DailyMenuItem::where('daily_menu_id', $menu->id)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        $newProductIds = collect($validated['items'])
            ->pluck('product_id')
            ->map(fn ($productId) => (int) $productId);

        // Jangan menghapus makanan yang sudah memiliki porsi terjual.
        foreach ($existingItems as $productId => $existingItem) {
            if (!$newProductIds->contains((int) $productId)) {
                $sold = $existingItem->initial_portions
                    - $existingItem->remaining_portions;

                if ($sold > 0) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Makanan yang sudah terjual tidak boleh dihapus dari menu.',
                        ],
                    ]);
                }

                $existingItem->delete();
            }
        }

        foreach ($validated['items'] as $item) {
            $productId = (int) $item['product_id'];
            $portions = (int) $item['portions'];

            if ($existingItems->has($productId)) {
                $existingItem = $existingItems->get($productId);

                $sold = $existingItem->initial_portions
                    - $existingItem->remaining_portions;

                if ($portions < $sold) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Jumlah porsi tidak boleh lebih sedikit daripada porsi yang sudah terjual.',
                        ],
                    ]);
                }

                $existingItem->update([
                    'initial_portions' => $portions,
                    'remaining_portions' => $portions - $sold,
                ]);
            } else {
                DailyMenuItem::create([
                    'daily_menu_id' => $menu->id,
                    'product_id' => $productId,
                    'initial_portions' => $portions,
                    'remaining_portions' => $portions,
                ]);
            }
        }

        $menu->update([
            'menu_date' => $validated['menu_date'],
        ]);

        return $menu->load('items.product');
    });

    return response()->json([
        'success' => true,
        'message' => 'Menu harian berhasil diperbarui.',
        'data' => $menu,
        ]);
    }
    
    public function activate($id)
    {
        $menu = DailyMenu::with('items.product')->find($id);

    if (!$menu) {
        return response()->json([
            'success' => false,
            'message' => 'Menu harian tidak ditemukan.',
        ], 404);
    }

    if ($menu->items->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Menu harian belum memiliki makanan.',
        ], 422);
    }

    foreach ($menu->items as $item) {
        if (!$item->product || !$item->product->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Ada makanan yang tidak tersedia. Periksa menu terlebih dahulu.',
            ], 422);
        }
    }

    $menu->update([
        'status' => 'active',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Menu harian berhasil diaktifkan.',
        'data' => $menu->fresh()->load('items.product'),
        ]);
    }
}