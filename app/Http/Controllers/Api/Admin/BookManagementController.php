<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BookManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('author', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->latest()->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'total' => $books->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $book = Book::with(['category', 'inventoryLogs.supplier', 'inventoryLogs.user'])->find($id);

        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $book]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:50|unique:books,isbn',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] ?? 'active';
        $data['rating'] = 4.50;
        $data['rating_count'] = 1;

        $book = Book::create($data);

        ActivityLog::record(
            'create_book',
            'books',
            "Admin created book '{$book->title}' with initial stock of {$book->stock}",
            ['book_id' => $book->id],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book->load('category'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'nullable|string|max:50|unique:books,isbn,' . $id,
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $book->update($request->all());

        ActivityLog::record(
            'update_book',
            'books',
            "Admin updated book '{$book->title}'",
            ['book_id' => $book->id],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book->load('category'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        $title = $book->title;
        $book->delete();

        ActivityLog::record(
            'delete_book',
            'books',
            "Admin deleted book '{$title}' [ID: {$id}]",
            ['book_id' => $id],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully',
        ]);
    }
}
