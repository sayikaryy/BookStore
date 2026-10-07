<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Supplier;
use App\Models\InventoryLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        if ($request->boolean('low_stock_only')) {
            $query->lowStock();
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%");
            });
        }

        $books = $query->orderBy('stock', 'asc')->paginate(20);

        $totalUnits = Book::sum('stock');
        $totalInventoryValue = Book::select(DB::raw('sum(stock * price) as val'))->value('val');
        $lowStockCount = Book::lowStock()->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_units' => (int)$totalUnits,
                'total_inventory_value' => (float)$totalInventoryValue,
                'low_stock_count' => (int)$lowStockCount,
            ],
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'total' => $books->total(),
            ]
        ]);
    }

    public function adjustStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
            'type' => 'required|in:stock_in,stock_out,adjustment,purchase',
            'quantity' => 'required|integer',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $book = Book::findOrFail($request->book_id);
        $prevStock = $book->stock;
        $qty = $request->quantity;

        // Calculate new stock based on operation
        if ($request->type === 'stock_in' || $request->type === 'purchase') {
            $newStock = $prevStock + abs($qty);
            $qty = abs($qty);
        } elseif ($request->type === 'stock_out') {
            if ($prevStock < abs($qty)) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot remove {$qty} units. Current stock is only {$prevStock}.",
                ], 422);
            }
            $newStock = $prevStock - abs($qty);
            $qty = -abs($qty);
        } else {
            // direct adjustment
            $newStock = max(0, $qty);
            $qty = $newStock - $prevStock;
        }

        $book->update(['stock' => $newStock]);

        $log = InventoryLog::create([
            'book_id' => $book->id,
            'supplier_id' => $request->supplier_id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'quantity' => $qty,
            'previous_stock' => $prevStock,
            'new_stock' => $newStock,
            'unit_cost' => $request->unit_cost,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
        ]);

        ActivityLog::record(
            'adjust_stock',
            'inventory',
            "Stock for '{$book->title}' changed from {$prevStock} to {$newStock} ({$request->type})",
            ['book_id' => $book->id, 'type' => $request->type, 'delta' => $qty],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => "Stock successfully updated. Current stock: {$newStock}",
            'data' => [
                'book' => $book,
                'inventory_log' => $log->load('supplier'),
            ]
        ]);
    }

    public function suppliers()
    {
        $suppliers = Supplier::withCount('inventoryLogs')->get();
        return response()->json(['success' => true, 'data' => $suppliers]);
    }

    public function storeSupplier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $supplier = Supplier::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Supplier added successfully',
            'data' => $supplier,
        ], 201);
    }

    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'data' => $supplier,
        ]);
    }

    public function logs(Request $request)
    {
        $query = InventoryLog::with(['book', 'supplier', 'user']);

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $logs = $query->latest()->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ]
        ]);
    }
}
