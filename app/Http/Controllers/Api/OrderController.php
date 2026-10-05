<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'machine_id' => ['required', 'exists:machines,id'],
            'order_number' => ['required', 'string', 'unique:orders,order_number'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
        ]);

        $order = Order::create($validated);

        $order->load([
            'customer',
            'machine',
            'orderItems.product',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat.',
            'data' => $order,
        ], 201);
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
        'data' => $order,
    ]);
    }

    public function update(Request $request, Order $order)
    {
    $validated = $request->validate([
        'status' => ['required', 'string'],
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
        'data' => $order,
    ]);
    }

    public function addItem(Request $request, Order $order)
    {
    $validated = $request->validate([
        'product_id' => ['required', 'exists:products,id'],
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    $product = \App\Models\Product::findOrFail($validated['product_id']);

    $subtotal = $product->price * $validated['quantity'];

    $item = $order->orderItems()->create([
        'product_id' => $product->id,
        'quantity' => $validated['quantity'],
        'price' => $product->price,
        'subtotal' => $subtotal,
    ]);

    $order->update([
        'total_price' => $order->orderItems()->sum('subtotal'),
    ]);

    $item->load('product');

    return response()->json([
        'success' => true,
        'message' => 'Item order berhasil ditambahkan.',
        'data' => $item,
    ], 201);
    }

    public function payments(Order $order)
    {
    $payments = $order->payments()->latest()->get();

    return response()->json([
        'success' => true,
        'message' => 'Daftar pembayaran berhasil diambil.',
        'data' => $payments,
    ]);
    }

    public function addPayment(Request $request, Order $order)
    {
    $validated = $request->validate([
        'payment_method' => ['required', 'string'],
        'amount' => ['required', 'numeric', 'min:0'],
    ]);

    $payment = $order->payments()->create([
        'payment_method' => $validated['payment_method'],
        'amount' => $validated['amount'],
        'payment_status' => 'paid',
        'transaction_id' => 'PAY-' . str_pad(
            (string) (\App\Models\Payment::max('id') + 1),
            4,
            '0',
            STR_PAD_LEFT
        ),
        'paid_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Pembayaran berhasil dibuat.',
        'data' => $payment,
    ], 201);
    }
}