<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category')->where('status', 'active');

        // Search
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

        // Featured Filter
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // In Stock Filter
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
            case 'rating_desc':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('rating_count', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $perPage = $request->input('per_page', 20);
        $books = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Books retrieved successfully',
            'data' => $books->items(),
            'books' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'total' => $books->total(),
                'per_page' => $books->perPage(),
            ],
        ], 200);
    }

    public function featured()
    {
        $featured = Book::with('category')
            ->where('status', 'active')
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        // If not enough featured, fallback to top rated
        if ($featured->isEmpty()) {
            $featured = Book::with('category')
                ->where('status', 'active')
                ->orderBy('rating', 'desc')
                ->take(8)
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Featured books retrieved successfully',
            'data' => $featured,
        ]);
    }

    public function show($id)
    {
        $book = Book::with('category')->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'errors' => ['book' => ['Book not found']],
            ], 404);
        }

        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('status', 'active')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Book details retrieved successfully',
            'data' => $book,
            'book' => $book,
            'related' => $relatedBooks,
        ], 200);
    }

    public function search(Request $request)
    {
        $search = $request->query('q', $request->query('search', ''));

        $query = Book::with('category')->where('status', 'active');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $books = $query->latest()->take(20)->get();

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'books' => $books,
            'data' => $books,
        ], 200);
    }
}
