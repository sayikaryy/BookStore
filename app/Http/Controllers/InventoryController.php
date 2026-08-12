<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display inventory levels and stock management dashboard.
     */
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Search by Title, Author, or ISBN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Stock Level Filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->where('stock', '>', 0)->where('stock', '<=', 5);
                    break;
                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;
                case 'in_stock':
                    $query->where('stock', '>', 5);
                    break;
            }
        }

        $books = $query->orderBy('stock', 'asc')->paginate(10)->withQueryString();

        // Calculate summary statistics
        $totalProducts = Book::count();
        $totalStockItems = Book::sum('stock');
        $lowStockCount = Book::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $outOfStockCount = Book::where('stock', 0)->count();

        $categories = Category::where('status', 'active')->get();

        return view('admin.inventory.index', compact(
            'books',
            'categories',
            'totalProducts',
            'totalStockItems',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Add stock quantity to a book (US-037).
     */
    public function addStock(Request $request, Book $book)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $addedQty = (int) $request->quantity;
        $book->stock += $addedQty;
        $book->save();

        return redirect()->back()->with('success', "Successfully added {$addedQty} units to \"{$book->title}\". New stock: {$book->stock}.");
    }

    /**
     * Update stock level to an exact number (US-038).
     */
    public function updateStock(Request $request, Book $book)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $newStock = (int) $request->stock;
        $oldStock = $book->stock;
        $book->stock = $newStock;
        $book->save();

        return redirect()->back()->with('success', "Stock for \"{$book->title}\" updated from {$oldStock} to {$newStock}.");
    }

    /**
     * Remove stock quantity from a book (US-039).
     */
    public function removeStock(Request $request, Book $book)
    {
        $request->validate([
            'quantity' => "required|integer|min:1|max:{$book->stock}",
        ], [
            'quantity.max' => "Cannot remove more than the current stock level ({$book->stock}).",
        ]);

        $removedQty = (int) $request->quantity;
        $book->stock -= $removedQty;
        $book->save();

        return redirect()->back()->with('success', "Successfully removed {$removedQty} units from \"{$book->title}\". Remaining stock: {$book->stock}.");
    }
}
