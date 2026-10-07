<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Book;
use App\Models\InventoryLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'payment', 'items.book']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'payment', 'items.book.category'])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::with('items.book')->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            $order->update(['status' => $newStatus]);

            // If order was cancelled, restore inventory
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->book) {
                        $prev = $item->book->stock;
                        $restored = $prev + $item->quantity;
                        $item->book->update(['stock' => $restored]);

                        InventoryLog::create([
                            'book_id' => $item->book->id,
                            'user_id' => $request->user()->id,
                            'type' => 'order_cancel',
                            'quantity' => $item->quantity,
                            'previous_stock' => $prev,
                            'new_stock' => $restored,
                            'reference_number' => $order->order_number,
                            'notes' => "Restocked because Order #{$order->order_number} was cancelled",
                        ]);
                    }
                }
            }

            ActivityLog::record(
                'update_order_status',
                'orders',
                "Order #{$order->order_number} status updated from '{$oldStatus}' to '{$newStatus}'",
                ['order_id' => $order->id, 'old' => $oldStatus, 'new' => $newStatus],
                $request->user()->id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order status updated to '{$newStatus}'",
                'data' => $order->fresh()->load(['user', 'payment', 'items.book']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
