<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\MachineSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // POST /api/v1/orders (Gabungan M4 + M5: Buat Order & Lock Stok hold_qty)
    
public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id' => ['nullable', 'exists:customers,id'],
        'machine_id'  => ['required', 'exists:machines,id'],
        'slot_code'   => ['required', 'string'],
        'quantity'    => ['required', 'integer', 'min:1'],
        'total_price' => ['required', 'numeric', 'min:0'],
        'status'      => ['nullable', 'string'],
        'order_number' => ['nullable', 'string'],
    ]);

    return DB::transaction(function () use ($validated) {
        // Cari slot berdasarkan mesin dan nomor slot
        $slot = MachineSlot::where('machine_id', $validated['machine_id'])
            ->where('slot_number', $validated['slot_code'])
            ->lockForUpdate()
            ->first();

        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'Slot mesin tidak ditemukan.'
            ], 404);
        }

        // Pastikan slot aktif
        if ($slot->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Slot mesin tidak aktif.'
            ], 400);
        }

        // Periksa ketersediaan stok
        $availableQty = (int) $slot->current_stock;

        if ($availableQty < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi.',
                'available_qty' => $availableQty,
            ], 400);
        }

        $product = Product::find($slot->product_id);

        if (!$product || !$product->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak tersedia.'
            ], 400);
        }

        // Validasi harga agar sesuai dengan harga produk
        $expectedTotal = (float) $product->price
            * $validated['quantity'];

        if (abs($expectedTotal - (float) $validated['total_price']) > 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Total harga tidak sesuai dengan harga produk.',
                'expected_total' => $expectedTotal,
            ], 422);
        }

        // Buat order
        $order = Order::create([
            'customer_id'  => $validated['customer_id'] ?? 1,
            'machine_id'   => $validated['machine_id'],
            'order_number' => $validated['order_number']
                ?? ('ORD-' . now()->format('YmdHis') . '-' . rand(100, 999)),
            'total_price'  => $expectedTotal,
            'status'       => $validated['status'] ?? 'pending',
        ]);

        // Buat item pesanan
        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity'   => $validated['quantity'],
            'price'      => $product->price,
            'subtotal'   => $expectedTotal,
        ]);

        $order->load([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat.',
            'data'    => $order,
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
        $subtotal = $product->price * $validated['quantity'];

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