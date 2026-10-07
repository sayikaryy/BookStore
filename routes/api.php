<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\BookManagementController as AdminBookController;
use App\Http\Controllers\Api\Admin\CategoryManagementController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\InventoryManagementController as AdminInventoryController;
use App\Http\Controllers\Api\Admin\OrderManagementController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ActivityLogController as AdminActivityLogController;

/*
|--------------------------------------------------------------------------
| BookVerse API Routes (v1)
|--------------------------------------------------------------------------
*/

$defineRoutes = function () {
    // -------------------------------------------------------------
    // Public Authentication
    // -------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Fallback direct auth routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // -------------------------------------------------------------
    // Public Catalog (Books & Categories)
    // -------------------------------------------------------------
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/featured', [BookController::class, 'featured']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('/books/{id}', [BookController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories/{id}/books', [CategoryController::class, 'books']);

    // Public payment webhook simulation
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // -------------------------------------------------------------
    // Protected Routes (Requires Sanctum Bearer Token)
    // -------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile & Authentication
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::get('/profile', [AuthController::class, 'user']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
        });
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/profile', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);

        // Shopping Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/', [CartController::class, 'store']);
            Route::put('/{id}', [CartController::class, 'update']);
            Route::delete('/{id}', [CartController::class, 'destroy']);
            Route::delete('/', [CartController::class, 'clear']);
        });

        // Saved Shipping Addresses
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
        });

        // Customer Orders & Digital Receipts
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::get('/{id}/receipt', [OrderController::class, 'receipt']);
        });

        // Payments & Cambodian QR Gateways Simulation
        Route::prefix('payments')->group(function () {
            Route::post('/', [PaymentController::class, 'store']);
            Route::get('/{id}', [PaymentController::class, 'show']);
            Route::post('/generate-qr', [PaymentController::class, 'generateQr']);
            Route::post('/{id}/simulate-success', [PaymentController::class, 'simulateSuccess']);
        });

        // ---------------------------------------------------------
        // Admin Protected Routes (Requires 'admin' role)
        // ---------------------------------------------------------
        Route::prefix('admin')->middleware('admin')->group(function () {
            // Dashboard & Analytics
            Route::get('/dashboard', [AdminDashboardController::class, 'index']);
            Route::get('/reports', [AdminDashboardController::class, 'reports']);

            // Book Management CRUD
            Route::get('/books', [AdminBookController::class, 'index']);
            Route::post('/books', [AdminBookController::class, 'store']);
            Route::get('/books/{id}', [AdminBookController::class, 'show']);
            Route::put('/books/{id}', [AdminBookController::class, 'update']);
            Route::delete('/books/{id}', [AdminBookController::class, 'destroy']);

            // Category Management CRUD
            Route::get('/categories', [AdminCategoryController::class, 'index']);
            Route::post('/categories', [AdminCategoryController::class, 'store']);
            Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
            Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);

            // Inventory & Supplier Management
            Route::get('/inventory', [AdminInventoryController::class, 'index']);
            Route::post('/inventory/adjust', [AdminInventoryController::class, 'adjustStock']);
            Route::get('/inventory/logs', [AdminInventoryController::class, 'logs']);
            Route::get('/suppliers', [AdminInventoryController::class, 'suppliers']);
            Route::post('/suppliers', [AdminInventoryController::class, 'storeSupplier']);
            Route::put('/suppliers/{id}', [AdminInventoryController::class, 'updateSupplier']);

            // Order Fulfillment Management
            Route::get('/orders', [AdminOrderController::class, 'index']);
            Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
            Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

            // Security & Activity Audit
            Route::get('/activity-logs', [AdminActivityLogController::class, 'index']);
        });
    });
};

// Define routes under /api/v1/ prefix
Route::prefix('v1')->group($defineRoutes);

// Also define routes directly under /api/ for backward compatibility
$defineRoutes();
