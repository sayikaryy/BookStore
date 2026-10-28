<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\ActivityLog;
use App\Services\BakongService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Record a Cash on Delivery payment. Bakong KHQR payments must go through
     * generate-qr + check-status so they are verified by the Bakong Open API.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|in:CASH_ON_DELIVERY',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $order = $this->findOrderForUser($user, $request->order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'errors' => ['order' => ['Order not found']],
            ], 404);
        }

        $txnId = 'TXN-COD-' . strtoupper(Str::random(6));

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => $txnId,
                'payment_method' => 'CASH_ON_DELIVERY',
                'bank_provider' => 'CASH',
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'status' => 'pending',
                'paid_at' => null,
            ]
        );

        $order->update(['status' => 'processing']);

        ActivityLog::record(
            'payment_cod_selected',
            'payments',
            "Cash on Delivery selected for Order #{$order->order_number} (\${$order->total_amount})",
            ['order_id' => $order->id, 'transaction_id' => $txnId],
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Cash on Delivery recorded',
            'payment' => $payment,
            'data' => $payment,
        ], 200);
    }

    /**
     * Generate a Bakong KHQR for an order.
     */
    public function generateQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $order = $this->findOrderForUser($request->user(), $request->order_id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if ($order->payment && $order->payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'status_code' => 'ALREADY_PAID',
                'message' => 'This order has already been paid.',
            ], 409);
        }

        try {
            $khqr = app(BakongService::class)->generateKhqr($order);
        } catch (\Throwable $e) {
            Log::error('Bakong KHQR generation failed: ' . $e->getMessage(), ['order_id' => $order->id]);

            return response()->json([
                'success' => false,
                'status_code' => 'KHQR_ERROR',
                'message' => 'Could not generate Bakong KHQR: ' . $e->getMessage(),
            ], 400);
        }

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => 'TXN-BAKONG-' . strtoupper(Str::random(6)),
                'payment_method' => 'BAKONG_KHQR',
                'bank_provider' => 'BAKONG',
                'amount' => $order->total_amount,
                'currency' => $khqr['currency'],
                'status' => 'pending',
                'qr_string' => $khqr['qr_string'],
                'md5_hash' => $khqr['md5'],
                'deeplink' => $khqr['deeplink'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Bakong KHQR generated successfully',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => (float) $order->total_amount,
                'currency' => $khqr['currency'],
                'bank_provider' => 'BAKONG',
                'merchant_name' => $khqr['merchant_name'],
                'bakong_account_id' => $khqr['account_id'],
                'qr_string' => $khqr['qr_string'],
                'md5' => $khqr['md5'],
                'deeplink' => $khqr['deeplink'],
                'transaction_id' => $payment->transaction_id,
                'payment_id' => $payment->id,
                'lifetime_minutes' => $khqr['lifetime_minutes'],
                'expired_at' => $khqr['expired_at'],
                'can_simulate' => $this->simulationAllowed(),
            ]
        ]);
    }

    /**
     * Ask the Bakong Open API whether the order's KHQR has been paid.
     */
    public function checkStatus(Request $request, $id = null)
    {
        $id = $id ?? $request->input('order_id') ?? $request->input('payment_id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Order or payment ID is required',
            ], 422);
        }

        $user = $request->user();
        $payment = $this->findPaymentForUser($user, $id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found',
            ], 404);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => 'paid',
                'message' => 'Payment has been completed',
                'data' => [
                    'payment' => $payment,
                    'order' => $payment->order,
                ]
            ]);
        }

        if (empty($payment->md5_hash)) {
            return response()->json([
                'success' => true,
                'is_paid' => false,
                'status' => $payment->status,
                'message' => 'Payment is pending',
                'data' => [
                    'payment' => $payment,
                    'order' => $payment->order,
                ]
            ]);
        }

        $bakong = app(BakongService::class);
        $check = $bakong->checkTransactionByMd5($payment->md5_hash);

        if (!empty($check['is_paid'])) {
            if (!$this->transactionMatches($payment, $check['data'] ?? [], $bakong->getAccountId())) {
                return response()->json([
                    'success' => false,
                    'is_paid' => false,
                    'status' => 'verification_failed',
                    'message' => 'Payment amount or recipient verification failed.',
                ], 400);
            }

            $this->markPaid($payment, 'bakong_payment_verified', $check['data'] ?? [], $user ? $user->id : null);

            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => 'paid',
                'message' => 'Payment confirmed by Bakong!',
                'data' => [
                    'payment' => $payment->fresh(),
                    'order' => $payment->order ? $payment->order->fresh() : null,
                    'gateway_details' => $check['data'] ?? null,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'is_paid' => false,
            'status' => $check['status'] ?? 'pending',
            'message' => $check['message'] ?? 'Waiting for customer payment scan...',
            'data' => [
                'payment' => $payment,
                'order' => $payment->order,
            ]
        ]);
    }

    /**
     * Testing helper: mark the caller's own pending payment as paid.
     * Disabled unless BAKONG_ALLOW_SIMULATION=true.
     */
    public function simulateSuccess(Request $request, $id)
    {
        if (!$this->simulationAllowed()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment simulation is disabled in this environment.',
            ], 403);
        }

        $user = $request->user();
        $payment = $this->findPaymentForUser($user, $id);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment record not found'], 404);
        }

        if ($payment->status !== 'paid') {
            $this->markPaid($payment, 'simulate_payment_success', ['simulated' => true], $user ? $user->id : null);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment successfully simulated and confirmed!',
            'data' => [
                'payment' => $payment->fresh(),
                'order' => $payment->order ? $payment->order->fresh() : null,
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $payment = $this->findPaymentForUser($request->user(), $id);

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

    private function findOrderForUser($user, $orderId): ?Order
    {
        return Order::where(function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('user_id', $user->id);
            }
        })->where('id', $orderId)->first();
    }

    /**
     * Look up a payment by payment id or order id, scoped to the user's own orders.
     */
    private function findPaymentForUser($user, $id): ?Payment
    {
        return Payment::whereHas('order', function ($q) use ($user) {
            if (!$user->isAdmin()) {
                $q->where('user_id', $user->id);
            }
        })->where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('order_id', $id);
        })->with('order')->first();
    }

    /**
     * Bakong returns the settled transaction; make sure it paid the right
     * amount, in the right currency, to our account.
     */
    private function transactionMatches(Payment $payment, array $data, string $accountId): bool
    {
        $amountOk = !isset($data['amount']) || abs((float) $data['amount'] - (float) $payment->amount) <= 0.01;
        $currencyOk = empty($data['currency']) || strtoupper($data['currency']) === strtoupper($payment->currency ?? 'USD');
        $accountOk = empty($data['toAccountId']) || $data['toAccountId'] === $accountId;

        if (!($amountOk && $currencyOk && $accountOk)) {
            Log::warning('Bakong transaction verification failed', [
                'payment_id' => $payment->id,
                'expected_amount' => $payment->amount,
                'received_amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? null,
                'to_account' => $data['toAccountId'] ?? null,
            ]);

            return false;
        }

        return true;
    }

    private function markPaid(Payment $payment, string $action, array $details, ?int $userId = null): void
    {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $order = $payment->order;
        if ($order) {
            $order->update(['status' => 'processing']);
        }

        ActivityLog::record(
            $action,
            'payments',
            "Bakong KHQR payment confirmed for Order #" . ($order->order_number ?? $payment->order_id) . " [{$payment->transaction_id}]",
            [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'md5' => $payment->md5_hash,
                'details' => $details,
            ],
            $userId
        );
    }

    private function simulationAllowed(): bool
    {
        return (bool) config('bakong.allow_simulation', false);
    }
}
