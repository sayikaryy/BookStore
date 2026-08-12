<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * View list of all orders with filters (US-046).
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'payment', 'items']);

        // Search by Order ID or Customer Name/Email
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        // Calculate order stats
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'cancelledOrders'
        ));
    }

    /**
     * View single order details with item breakdown (US-047, US-050).
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.book.category', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status (US-048).
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return redirect()->back()->with('info', 'Order status was not changed.');
        }

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            // If cancelling an active order, restock items back to inventory (US-049)
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->book) {
                        $item->book->increment('stock', $item->quantity);
                    }
                }
            }

            // If un-cancelling an order back to active, deduct stock if available
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->book) {
                        $item->book->decrement('stock', min($item->quantity, $item->book->stock));
                    }
                }
            }

            $order->status = $newStatus;
            $order->save();
        });

        return redirect()->back()->with('success', "Order #{$order->id} status updated from '{$oldStatus}' to '{$newStatus}'.");
    }

    /**
     * Cancel an order directly (US-049).
     */
    public function cancel(Order $order)
    {
        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', "Order #{$order->id} is already cancelled.");
        }

        DB::transaction(function () use ($order) {
            // Restock items to inventory
            foreach ($order->items as $item) {
                if ($item->book) {
                    $item->book->increment('stock', $item->quantity);
                }
            }

            $order->status = 'cancelled';
            $order->save();
        });

        return redirect()->back()->with('success', "Order #{$order->id} has been cancelled and items restored to inventory.");
    }
}
