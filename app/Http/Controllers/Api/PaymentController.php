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
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|string|in:success,failed',
        ]);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan'], 404);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'idempotency_key' => (string) Str::uuid(),
        ]);

        if ($request->status === 'success') {
            $order->status = 'paid';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulasi pembayaran diproses',
            'data' => $payment
        ]);
    }

    // POST /api/v1/payments/{id}/confirm-dispense (Konfirmasi Dispense & Potong Stok)
    public function confirmDispense(Request $request, $id)
    {
        $request->validate([
            'machine_id' => 'required|integer',
            'slot_code' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $payment = Payment::find($id);
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Data pembayaran tidak ditemukan'], 404);
            }

            $slot = MachineSlot::where('machine_id', $request->machine_id)
                ->where('slot_code', $request->slot_code)
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                return response()->json(['success' => false, 'message' => 'Slot tidak ditemukan'], 404);
            }

            // Kurangi stok riil dan kurangi hold_qty
            $slot->current_qty = max(0, $slot->current_qty - $request->quantity);
            $slot->hold_qty = max(0, ($slot->hold_qty ?? 0) - $request->quantity);
            $slot->save();

            // Update status order menjadi completed
            if ($payment->order) {
                $payment->order->status = 'completed';
                $payment->order->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Dispense dikonfirmasi, stok fisik berhasil dipotong',
                'data' => [
                    'payment_id' => $payment->id,
                    'remaining_current_qty' => $slot->current_qty,
                    'remaining_hold_qty' => $slot->hold_qty
                ]
            ]);
        });
    }
}