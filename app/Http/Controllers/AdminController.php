<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalBooks = Book::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalOrders = Order::count();
        $totalSales = Payment::where('status', 'paid')->sum('amount');
        if ($totalSales == 0) {
            $totalSales = Order::where('status', 'completed')->sum('total_amount');
        }

        $totalCategories = Category::count();
        $recentBooks = Book::with('category')->latest()->take(5)->get();
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalBooks',
            'totalCustomers',
            'totalOrders',
            'totalSales',
            'totalCategories',
            'recentBooks',
            'recentOrders'
        ));
    }
}
