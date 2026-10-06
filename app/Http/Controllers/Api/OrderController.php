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
    public function index()
    {
        $orders = Order::with([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ])->latest()->get();

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
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Cek slot & kunci baris slot untuk hindari race condition
            $slot = MachineSlot::where('machine_id', $validated['machine_id'])
                ->where('slot_code', $validated['slot_code'])
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slot mesin tidak ditemukan.'
                ], 404);
            }

            // 2. Hitung stok yang tersedia (current_qty - hold_qty)
            $availableQty = $slot->current_qty - ($slot->hold_qty ?? 0);

            if ($availableQty < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi (Overselling prevented).',
                    'available_qty' => max(0, $availableQty)
                ], 400);
            }

            // 3. Tambahkan hold_qty
            $slot->hold_qty = ($slot->hold_qty ?? 0) + $validated['quantity'];
            $slot->save();

            // 4. Buat Order
            $orderNumber = $request->order_number ?? ('ORD-' . time() . '-' . rand(100, 999));

            $order = Order::create([
                'customer_id'  => $validated['customer_id'] ?? 1,
                'machine_id'   => $validated['machine_id'],
                'order_number' => $orderNumber,
                'total_price'  => $validated['total_price'],
                'status'       => $validated['status'] ?? 'pending',
            ]);

            // Jika ada product_id di slot, otomatis buat orderItem
            if ($slot->product_id) {
                $product = Product::find($slot->product_id);
                $order->orderItems()->create([
                    'product_id' => $slot->product_id,
                    'quantity'   => $validated['quantity'],
                    'price'      => $product ? $product->price : 0,
                    'subtotal'   => $validated['total_price'],
                ]);
            }

            $order->load([
                'customer',
                'machine',
                'orderItems.product',
                'payments',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat dan stok telah di-hold.',
                'data'    => $order,
            ], 201);
        });
    }

    public function show(Order $order)
    {
        $order->load([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail order berhasil diambil.',
            'data'    => $order,
        ]);
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
            'paid_at'        => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dibuat.',
            'data'    => $payment,
        ], 201);
    }
}