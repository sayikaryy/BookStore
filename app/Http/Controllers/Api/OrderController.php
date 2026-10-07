<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Book;
use App\Models\Cart;
use App\Models\InventoryLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['items.book.category', 'payment'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'orders' => $orders,
            'data' => $orders,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::with(['items.book.category', 'payment', 'user'])
            ->where(function ($q) use ($user) {
                if (!$user->isAdmin()) {
                    $q->where('user_id', $user->id);
                }
            })
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'errors' => ['order' => ['Order not found']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully',
            'order' => $order,
            'data' => $order,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string', // ABA_KHQR, ACLEDA_KHQR, BAKONG_KHQR, CARD, CASH_ON_DELIVERY
            'delivery_method' => 'nullable|string',
            'phone' => 'nullable|string',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $orderItemsData = [];
            $purchasedBookIds = [];

            // 1. Verify stock and calculate subtotal
            foreach ($request->items as $item) {
                $book = Book::lockForUpdate()->find($item['book_id']);

                if (!$book || $book->stock < $item['quantity']) {
                    DB::rollBack();
                    $avail = $book ? $book->stock : 0;
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for '{$book->title}'. Only {$avail} units left.",
                        'errors' => ['stock' => ["'{$book->title}' has only {$avail} units available."]],
                    ], 422);
                }

                $itemSubtotal = $book->price * $item['quantity'];
                $subtotal += $itemSubtotal;
                $purchasedBookIds[] = $book->id;

                $orderItemsData[] = [
                    'book' => $book,
                    'quantity' => $item['quantity'],
                    'price' => $book->price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $deliveryFee = 1.50; // standard delivery fee
            $discount = 0.00;
            $totalAmount = $subtotal + $deliveryFee - $discount;

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // 2. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'delivery_method' => $request->input('delivery_method', 'Standard Courier (1-2 days)'),
                'phone' => $request->input('phone', $user->phone ?? ''),
                'note' => $request->input('note', null),
            ]);

            // 3. Create Order Items & Deduct Stock with Inventory Logging
            foreach ($orderItemsData as $itemData) {
                $book = $itemData['book'];
                $prevStock = $book->stock;
                $newStock = $prevStock - $itemData['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                // Update Book Stock
                $book->update(['stock' => $newStock]);

                // Record Inventory Log
                InventoryLog::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'type' => 'order_sale',
                    'quantity' => -$itemData['quantity'],
                    'previous_stock' => $prevStock,
                    'new_stock' => $newStock,
                    'unit_cost' => $book->price,
                    'reference_number' => $orderNumber,
                    'notes' => "Sold via customer checkout for Order #{$orderNumber}",
                ]);
            }

            // 4. Create Initial Payment Record
            $paymentMethod = strtoupper(str_replace(' ', '_', $request->payment_method));
            $txnId = 'TXN-' . str_replace('_', '-', $paymentMethod) . '-' . strtoupper(Str::random(6));

            $bankProvider = 'CASH';
            $qrString = null;
            if (str_contains($paymentMethod, 'ABA')) {
                $bankProvider = 'ABA';
                $qrString = "00020101021229370016bakong@abaa00010108abaa_usd520459995303840540" . number_format($totalAmount, 2, '.', '') . "5802KH5912BookVerseKH6010Phnom Penh6304" . strtoupper(Str::random(4));
            } elseif (str_contains($paymentMethod, 'ACLEDA')) {
                $bankProvider = 'ACLEDA';
                $qrString = "00020101021229370016bakong@aclb00010108aclb_usd520459995303840540" . number_format($totalAmount, 2, '.', '') . "5802KH5912BookVerseKH6010Phnom Penh6304" . strtoupper(Str::random(4));
            } elseif (str_contains($paymentMethod, 'BAKONG') || str_contains($paymentMethod, 'KHQR')) {
                $bankProvider = 'BAKONG';
                $qrString = "00020101021229370016bakong@nbckh0010108nbc_khqr520459995303840540" . number_format($totalAmount, 2, '.', '') . "5802KH5912BookVerseKH6010Phnom Penh6304" . strtoupper(Str::random(4));
            } elseif (str_contains($paymentMethod, 'CARD')) {
                $bankProvider = 'CARD';
            }

            $paymentStatus = ($paymentMethod === 'CASH_ON_DELIVERY') ? 'pending' : 'pending';

            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $txnId,
                'payment_method' => $paymentMethod,
                'bank_provider' => $bankProvider,
                'amount' => $totalAmount,
                'currency' => 'USD',
                'status' => $paymentStatus,
                'qr_string' => $qrString,
                'paid_at' => null,
            ]);

            // 5. Remove items from Cart
            Cart::where('user_id', $user->id)
                ->whereIn('book_id', $purchasedBookIds)
                ->delete();

            // 6. Record Activity Log
            ActivityLog::record(
                'create_order',
                'orders',
                "Order {$orderNumber} created by {$user->name} totaling \${$totalAmount}",
                ['order_id' => $order->id, 'amount' => $totalAmount, 'payment_method' => $paymentMethod],
                $user->id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order' => $order->load(['items.book.category', 'payment']),
                'data' => $order->load(['items.book.category', 'payment']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Order failed: ' . $e->getMessage(),
                'errors' => ['order' => [$e->getMessage()]],
            ], 500);
        }
    }

    public function receipt(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::with(['items.book.category', 'payment', 'user'])
            ->where(function ($q) use ($user) {
                if (!$user->isAdmin()) {
                    $q->where('user_id', $user->id);
                }
            })
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $receipt = [
            'invoice_number' => $order->invoice_number,
            'order_date' => $order->created_at->format('M d, Y h:i A'),
            'store_info' => [
                'name' => 'BookVerse Cambodia',
                'address' => 'Building 123, Monivong Blvd, Phnom Penh',
                'phone' => '+855 23 888 999',
                'email' => 'support@bookverse.com',
                'website' => 'https://bookverse.com.kh',
            ],
            'customer' => [
                'name' => $order->user->name ?? 'Customer',
                'phone' => $order->phone ?: ($order->user->phone ?? 'N/A'),
                'shipping_address' => $order->shipping_address,
                'delivery_method' => $order->delivery_method,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'book_id' => $item->book_id,
                    'title' => $item->book->title ?? 'Unknown Book',
                    'author' => $item->book->author ?? '',
                    'quantity' => $item->quantity,
                    'unit_price' => (float)$item->price,
                    'subtotal' => (float)$item->subtotal,
                ];
            }),
            'financials' => [
                'subtotal' => (float)$order->subtotal,
                'delivery_fee' => (float)$order->delivery_fee,
                'discount' => (float)$order->discount,
                'total_amount' => (float)$order->total_amount,
                'currency' => $order->payment->currency ?? 'USD',
            ],
            'payment' => [
                'transaction_id' => $order->payment->transaction_id ?? 'N/A',
                'payment_method' => $order->payment->payment_method ?? 'N/A',
                'bank_provider' => $order->payment->bank_provider ?? 'N/A',
                'payment_status' => $order->payment->status ?? 'pending',
                'paid_at' => $order->payment->paid_at ? $order->payment->paid_at->format('M d, Y h:i A') : 'Pending',
            ],
            'order_status' => $order->status,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Digital receipt generated successfully',
            'data' => $receipt,
        ]);
    }
}
