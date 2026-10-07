<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cartItems = Cart::with(['book' => function ($q) {
            $q->with('category');
        }])->where('user_id', $user->id)->get();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->book) {
                $subtotal += ($item->book->price * $item->quantity);
            }
        }
        $deliveryFee = $subtotal > 0 ? 1.50 : 0.00;
        $totalAmount = $subtotal + $deliveryFee;

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'cart' => $cartItems,
            'data' => $cartItems,
            'summary' => [
                'subtotal' => (float)$subtotal,
                'delivery_fee' => (float)$deliveryFee,
                'total_amount' => (float)$totalAmount,
                'items_count' => $cartItems->sum('quantity'),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $bookId = $request->book_id;
        $quantity = $request->input('quantity', 1);

        $book = Book::find($bookId);
        if ($book->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
                'errors' => ['stock' => ['Not enough stock available']],
            ], 422);
        }

        $cartItem = Cart::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'quantity' => $quantity,
            ]);
        }

        $cartItem->load(['book' => function ($q) {
            $q->with('category');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart_item' => $cartItem,
            'data' => $cartItem,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $cartItem = Cart::where('user_id', $user->id)->where('id', $id)->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
                'errors' => ['cart' => ['Item not found in cart']],
            ], 404);
        }

        if ($request->quantity <= 0) {
            $cartItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => null,
            ], 200);
        }

        $book = Book::find($cartItem->book_id);
        if ($book && $book->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
                'errors' => ['stock' => ['Not enough stock available']],
            ], 422);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $cartItem->load(['book' => function ($q) {
            $q->with('category');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_item' => $cartItem,
            'data' => $cartItem,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $cartItem = Cart::where('user_id', $user->id)->where('id', $id)->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
                'errors' => ['cart' => ['Item not found in cart']],
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart item removed successfully',
            'data' => null,
        ], 200);
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => null,
        ], 200);
    }
}
