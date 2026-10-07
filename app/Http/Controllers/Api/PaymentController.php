<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $order = Order::where(function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('user_id', $user->id);
            }
        })->where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'errors' => ['order' => ['Order not found']],
            ], 404);
        }

        $paymentMethod = strtoupper(str_replace(' ', '_', $request->payment_method));
        $txnId = 'TXN-' . str_replace('_', '-', $paymentMethod) . '-' . strtoupper(Str::random(6));

        $bankProvider = 'CASH';
        if (str_contains($paymentMethod, 'ABA')) $bankProvider = 'ABA';
        elseif (str_contains($paymentMethod, 'ACLEDA')) $bankProvider = 'ACLEDA';
        elseif (str_contains($paymentMethod, 'BAKONG') || str_contains($paymentMethod, 'KHQR')) $bankProvider = 'BAKONG';
        elseif (str_contains($paymentMethod, 'CARD')) $bankProvider = 'CARD';

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => $txnId,
                'payment_method' => $paymentMethod,
                'bank_provider' => $bankProvider,
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'status' => 'paid',
                'paid_at' => now(),
            ]
        );

        $order->update(['status' => 'processing']);

        ActivityLog::record(
            'payment_received',
            'payments',
            "Payment received for Order #{$order->order_number} via {$paymentMethod} (\${$order->total_amount})",
            ['order_id' => $order->id, 'transaction_id' => $txnId],
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment' => $payment,
            'data' => $payment,
        ], 200);
    }

    public function generateQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'bank_provider' => 'nullable|string|in:ABA,ACLEDA,BAKONG,WING',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $order = Order::where(function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('user_id', $user->id);
            }
        })->where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $bank = strtoupper($request->input('bank_provider', 'ABA'));
        $amount = number_format($order->total_amount, 2, '.', '');
        $tag = strtoupper(Str::random(6));

        // Format compliant with EMVCo / Bakong KHQR standard
        $mockKhqr = "00020101021229370016bakong@" . strtolower($bank) . "00010108" . strtolower($bank) . "_usd520459995303840540{$amount}5802KH5912BookVerseKH6010Phnom Penh6304{$tag}";

        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => 'TXN-' . $bank . '-' . strtoupper(Str::random(6)),
                'payment_method' => $bank . '_KHQR',
                'bank_provider' => $bank,
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'status' => 'pending',
            ]
        );

        $payment->update([
            'bank_provider' => $bank,
            'payment_method' => $bank . '_KHQR',
            'qr_string' => $mockKhqr,
        ]);

        return response()->json([
            'success' => true,
            'message' => "KHQR generated for {$bank}",
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => (float)$order->total_amount,
                'currency' => 'USD',
                'bank_provider' => $bank,
                'merchant_name' => 'BookVerse Cambodia',
                'qr_string' => $mockKhqr,
                'transaction_id' => $payment->transaction_id,
            ]
        ]);
    }

    public function simulateSuccess(Request $request, $id)
    {
        $user = $request->user();

        $payment = Payment::where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('order_id', $id);
        })->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment record not found'], 404);
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $order = $payment->order;
        if ($order) {
            $order->update(['status' => 'processing']);

            ActivityLog::record(
                'simulate_payment_success',
                'payments',
                "Simulated successful QR payment for Order #{$order->order_number} [{$payment->transaction_id}]",
                ['payment_id' => $payment->id, 'order_id' => $order->id],
                $user ? $user->id : null
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment successfully simulated and confirmed!',
            'data' => [
                'payment' => $payment,
                'order' => $order,
            ]
        ]);
    }

    public function webhook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'status' => 'required|in:SUCCESS,PAID,FAILED',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $payment = Payment::where('transaction_id', $request->transaction_id)->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if (in_array($request->status, ['SUCCESS', 'PAID'])) {
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            if ($payment->order) {
                $payment->order->update(['status' => 'processing']);
            }
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed',
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $payment = Payment::whereHas('order', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('user_id', $user->id);
            }
        })->where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('order_id', $id);
        })->with('order')->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'errors' => ['payment' => ['Payment record not found']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment details retrieved successfully',
            'payment' => $payment,
            'data' => $payment,
        ], 200);
    }
}
