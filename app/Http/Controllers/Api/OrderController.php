<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\MachineSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['sometimes', 'date_format:Y-m-d'],
            'end_date' => [
                'sometimes',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
        ]);

        $query = Order::with([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ]);

        if (isset($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar order berhasil diambil.',
            'data' => $orders,
        ]);
    }

   
public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id'  => ['nullable', 'exists:customers,id'],
        'machine_id'   => ['required', 'exists:machines,id'],
        'slot_code'    => ['required', 'string'],
        'quantity'     => ['required', 'integer', 'min:1'],
        'total_price'  => ['required', 'numeric', 'min:0'],
        'status'       => ['nullable', 'string'],
        'order_number' => ['nullable', 'string'],
    ]);

    return DB::transaction(function () use ($validated) {

        // 1. Cari slot mesin dan kunci agar tidak diproses
        // bersamaan oleh transaksi lain.
        $slot = MachineSlot::where(
            'machine_id',
            $validated['machine_id']
        )
            ->where(function ($query) use ($validated) {
                $query->where(
                    'slot_number',
                    $validated['slot_code']
                )->orWhere('id', $validated['slot_code']);
            })
            ->lockForUpdate()
            ->first();

        if (!$slot) {
            $slot = MachineSlot::where(
                'machine_id',
                $validated['machine_id']
            )
                ->where('status', 'active')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();
        }

        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'Slot mesin tidak ditemukan.',
            ], 404);
        }

        if ($slot->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Slot mesin tidak aktif.',
            ], 400);
        }

        // 2. Periksa produk.
        $product = Product::find($slot->product_id);

        if (!$product || !$product->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak tersedia.',
            ], 400);
        }

        // 3. Cari menu aktif terdekat yang berlaku hari ini
        // atau di masa mendatang.
        $dailyMenu = DailyMenu::where('status', 'active')
            ->whereDate('menu_date', '>=', today())
            ->orderBy('menu_date')
            ->first();

        if (!$dailyMenu) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada menu aktif untuk tanggal yang berlaku.',
            ], 422);
        }

        // 4. Kunci porsi menu agar dua pesanan tidak
        // menggunakan porsi yang sama.
        $menuItem = DailyMenuItem::where(
            'daily_menu_id',
            $dailyMenu->id
        )
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak tersedia pada menu harian aktif.',
            ], 422);
        }

        if ($menuItem->remaining_portions < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Porsi menu harian tidak mencukupi.',
                'remaining_portions' => (int) $menuItem->remaining_portions,
            ], 422);
        }

        // 5. Periksa stok produk pada mesin.
        $inventory = \App\Models\ProductInventory::where(
            'machine_id',
            $validated['machine_id']
        )
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        $availableQty = $inventory
            ? (int) $inventory->quantity
            : (int) ($slot->current_stock ?? 10);

        if ($availableQty < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Stok mesin tidak mencukupi.',
                'available_qty' => $availableQty,
            ], 422);
        }

        // 6. Pastikan harga sesuai dengan database.
        $expectedTotal = (float) $product->price
            * (int) $validated['quantity'];

        if (abs($expectedTotal - (float) $validated['total_price']) > 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Total harga tidak sesuai dengan harga produk.',
                'expected_total' => $expectedTotal,
            ], 422);
        }

        // 7. Cadangkan porsi menu.
        $menuItem->decrement(
            'remaining_portions',
            (int) $validated['quantity']
        );

        // 8. Buat order.
        $order = Order::create([
            'customer_id' => $validated['customer_id'] ?? 1,
            'machine_id' => $validated['machine_id'],
            'order_number' => $validated['order_number']
                ?? ('ORD-' . now()->format('YmdHis') . '-' . rand(100, 999)),
            'total_price' => $expectedTotal,
            'status' => $validated['status'] ?? 'pending',
        ]);

        // 9. Buat item pesanan.
        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price,
            'subtotal' => $expectedTotal,
        ]);

        $order->load([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat dan porsi menu berhasil dicadangkan.',
            'data' => $order,
            'menu_remaining_portions' => (int) $menuItem->fresh()->remaining_portions,
        ], 201);
    });
}

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status'      => ['required', 'string'],
            'total_price' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $order->update($validated);

        $order->load([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diperbarui.',
            'data'    => $order,
        ]);
    }

    public function addItem(Request $request, Order $order)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $subtotal = (float) $product->price * (int) $validated['quantity'];

        $item = $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity'   => $validated['quantity'],
            'price'      => $product->price,
            'subtotal'   => $subtotal,
        ]);

        $order->update([
            'total_price' => $order->orderItems()->sum('subtotal'),
        ]);

        $item->load('product');

        return response()->json([
            'success' => true,
            'message' => 'Item order berhasil ditambahkan.',
            'data'    => $item,
        ], 201);
    }

    public function payments(Order $order)
    {
        $payments = $order->payments()->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pembayaran berhasil diambil.',
            'data'    => $payments,
        ]);
    }

    public function addPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string'],
            'amount'         => ['required', 'numeric', 'min:0'],
        ]);

        // Pastikan jumlah pembayaran sesuai dengan total pesanan
        if ((float) $validated['amount'] !== (float) $order->total_price) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran tidak sesuai dengan total pesanan.',
                'expected_amount' => (float) $order->total_price,
            ], 422);
        }

        // Cegah pembayaran ulang untuk pesanan yang sudah dibayar
        $alreadyPaid = $order->payments()
            ->where('payment_status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah memiliki pembayaran berhasil.',
            ], 422);
        }

        return DB::transaction(function () use ($validated, $order) {
            $payment = $order->payments()->create([
                'payment_method' => $validated['payment_method'],
                'amount'         => $validated['amount'],
                'payment_status' => 'paid',
                'transaction_id' => 'PAY-' . str_pad(
                    (string) (\App\Models\Payment::max('id') + 1),
                    4,
                    '0',
                    STR_PAD_LEFT
                ),
                'paid_at' => now(),
            ]);

            // Perbarui status pesanan setelah pembayaran berhasil
            $order->update([
                'status' => 'paid',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dan status pesanan diperbarui.',
                'data' => [
                    'order' => $order->fresh(),
                    'payment' => $payment,
                ],
            ], 201);
        });
    }

    public function show($id)
    {
        $order = Order::with([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $order,
        ]);
    }
}