<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Book;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', 'active')
            ->withCount(['books' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'categories' => $categories,
            'data' => $categories,
        ], 200);
    }

    public function show($id)
    {
        $category = Category::where('status', 'active')
            ->withCount(['books' => function ($query) {
                $query->where('status', 'active');
            }])
            ->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'errors' => ['category' => ['Category not found']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category details retrieved successfully',
            'category' => $category,
            'data' => $category,
        ], 200);
    }

    public function books($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'errors' => ['category' => ['Category not found']],
            ], 404);
        }

        $books = Book::with('category')
            ->where('category_id', $id)
            ->where('status', 'active')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Books for category {$category->name} retrieved successfully",
            'category' => $category,
            'books' => $books,
            'data' => $books,
        ], 200);
    }
}
