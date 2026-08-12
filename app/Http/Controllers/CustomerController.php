<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * View customer list with search and filtering (US-042, US-043).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('status', 'completed');
            }], 'total_amount');

        // Search by name, email, or phone (US-043)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        $totalCustomers = User::where('role', 'customer')->count();
        $activeCustomers = User::where('role', 'customer')->where('status', 'active')->count();
        $inactiveCustomers = User::where('role', 'customer')->where('status', 'inactive')->count();

        return view('admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers'
        ));
    }

    /**
     * View customer details and order history (US-044, US-045).
     */
    public function show(User $user)
    {
        // Ensure only customer profiles are viewed here
        if ($user->role !== 'customer') {
            abort(404, 'Customer not found');
        }

        $user->loadCount('orders');

        $orders = Order::where('user_id', $user->id)
            ->with(['items.book', 'payment'])
            ->latest()
            ->paginate(10);

        $totalSpent = Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $completedOrdersCount = Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return view('admin.customers.show', compact('user', 'orders', 'totalSpent', 'completedOrdersCount'));
    }
}
