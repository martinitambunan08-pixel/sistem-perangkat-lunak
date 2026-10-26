<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $validated = $request->validate([
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        $payments = Payment::where('payment_status', 'paid')
            ->whereDate('paid_at', $date);

        $totalTransactions = (clone $payments)->count();
        $totalRevenue = (clone $payments)->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan transaksi harian berhasil diambil.',
            'data' => [
                'date' => $date,
                'total_transactions' => $totalTransactions,
                'total_revenue' => (float) $totalRevenue,
            ],
        ]);
    }
}
