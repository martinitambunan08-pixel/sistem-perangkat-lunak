<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\MachineSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
   
    // POST /api/v1/payments/simulate (Simulasi Pembayaran)
    public function simulate(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'status' => 'required|string|in:success,failed',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah selesai dan tidak dapat dibayar ulang.',
            ], 422);
        }

        $payment = DB::transaction(function () use ($order, $validated) {
            $isSuccessful = $validated['status'] === 'success';

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'QRIS',
                'amount' => $order->total_price,
                'payment_status' => $isSuccessful ? 'paid' : 'failed',
                'transaction_id' => (string) Str::uuid(),
                'paid_at' => $isSuccessful ? now() : null,
            ]);

            if ($isSuccessful) {
                $order->status = 'paid';
                $order->save();
            }

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'success'
                ? 'Simulasi pembayaran berhasil.'
                : 'Simulasi pembayaran gagal.',
            'data' => $payment,
        ], 201);
    }

    
   
    // POST /api/v1/payments/{id}/confirm-dispense
    public function confirmDispense(Request $request, $id)
    {
        $validated = $request->validate([
            'machine_id' => 'required|integer',
            'slot_number' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $payment = Payment::with('order')->find($id);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan.',
                ], 404);
            }

            if ($payment->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran belum berhasil.',
                ], 422);
            }

            if (!$payment->order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            if ($payment->order->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan sudah selesai diproses.',
                ], 422);
            }

            $slot = MachineSlot::where(
                'machine_id',
                $validated['machine_id']
            )
                ->where('slot_number', $validated['slot_number'])
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slot tidak ditemukan.',
                ], 404);
            }

            if ($slot->current_stock < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok makanan tidak mencukupi.',
                ], 422);
            }

            $slot->current_stock -= $validated['quantity'];
            $slot->save();

            $payment->order->status = 'completed';
            $payment->order->save();

            return response()->json([
                'success' => true,
                'message' => 'Konfirmasi pengeluaran makanan berhasil.',
                'data' => [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order->id,
                    'slot_number' => $slot->slot_number,
                    'remaining_current_stock' => $slot->current_stock,
                    'order_status' => $payment->order->status,
                ],
            ]);
        });
    }
}