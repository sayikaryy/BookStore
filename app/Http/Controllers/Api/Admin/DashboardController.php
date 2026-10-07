<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Book;
use App\Models\User;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. KPI Metrics
        $totalRevenue = (float)Payment::where('status', 'paid')->sum('amount');
        $totalOrders = Order::count();
        $activeCustomers = User::where('role', 'customer')->where('status', 'active')->count();
        $totalBooks = Book::count();
        $lowStockBooksCount = Book::lowStock()->count();

        // 2. Orders by Status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 3. Payment Method Breakdown
        $paymentsByMethod = Payment::where('status', 'paid')
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        // 4. Low Stock Alerts
        $lowStockBooks = Book::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        // 5. Recent Orders
        $recentOrders = Order::with(['user', 'payment', 'items.book'])
            ->latest()
            ->take(5)
            ->get();

        // 6. Top Selling Books
        $topBooks = Book::with('category')
            ->withCount(['orderItems as total_sold' => function ($q) {
                $q->select(DB::raw('coalesce(sum(quantity),0)'));
            }])
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard metrics retrieved successfully',
            'data' => [
                'kpis' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'active_customers' => $activeCustomers,
                    'total_books' => $totalBooks,
                    'low_stock_count' => $lowStockBooksCount,
                ],
                'orders_by_status' => $ordersByStatus,
                'payments_by_method' => $paymentsByMethod,
                'low_stock_books' => $lowStockBooks,
                'recent_orders' => $recentOrders,
                'top_selling_books' => $topBooks,
            ]
        ]);
    }

    public function reports()
    {
        $salesReport = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as orders_count'),
            DB::raw('sum(total_amount) as daily_revenue')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->take(14)
        ->get();

        $categorySales = Category::withCount(['books' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'daily_sales' => $salesReport,
                'categories_breakdown' => $categorySales,
            ]
        ]);
    }
}
