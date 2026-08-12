@extends('layouts.admin')

@section('title', 'Inventory & Stock Management')
@section('page-title', 'Inventory Management')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Book Inventory & Stock</h2>
            <p class="text-sm text-slate-500">Monitor stock levels, receive low stock alerts, and adjust quantities</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.books.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                + Add New Book
            </a>
        </div>
    </div>

    <!-- US-041 Low Stock Notification Alert Banner -->
    @if($lowStockCount > 0 || $outOfStockCount > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl p-4 md:p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-amber-100 rounded-xl text-amber-700 text-xl font-bold">
                        ⚠️
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">Low Inventory Alert</h4>
                        <p class="text-xs text-amber-900 mt-0.5">
                            You have <strong class="text-amber-800 font-extrabold">{{ $lowStockCount }}</strong> low stock item(s) (≤ 5 left) 
                            and <strong class="text-rose-700 font-extrabold">{{ $outOfStockCount }}</strong> out of stock item(s).
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'low_stock']) }}" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                        View Low Stock
                    </a>
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'out_of_stock']) }}" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                        View Out of Stock
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Stats Cards (US-040) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Products -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Books</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalProducts) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold">
                📚
            </div>
        </div>

        <!-- Card 2: Total Units in Stock -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Units in Stock</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalStockItems) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold">
                📦
            </div>
        </div>

        <!-- Card 3: Low Stock Count -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Low Stock Alert (≤ 5)</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($lowStockCount) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-xl font-bold">
                ⚡
            </div>
        </div>

        <!-- Card 4: Out of Stock -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">Out of Stock (0)</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($outOfStockCount) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold">
                🚫
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <!-- Search input -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="🔍 Search title, author, or ISBN..."
                       class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- Category Filter Dropdown -->
            <div class="sm:col-span-3">
                <select name="category_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <option value="all">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Level Status Filter (US-040, US-041) -->
            <div class="sm:col-span-3">
                <select name="stock_status" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <option value="" {{ !request('stock_status') ? 'selected' : '' }}>All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock (> 5)</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock (1 - 5)</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>🚫 Out of Stock (0)</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="sm:col-span-1">
                <button type="submit" class="w-full h-full bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center justify-center transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table Card (US-040) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6 text-center">Cover</th>
                        <th class="py-4 px-6">Book Title</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6 text-right">Price</th>
                        <th class="py-4 px-6 text-center">Stock Level</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Stock Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($books as $book)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Cover -->
                            <td class="py-3 px-6 text-center">
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-10 h-14 object-cover rounded shadow-sm mx-auto border border-slate-200">
                                @else
                                    <div class="w-10 h-14 rounded bg-indigo-50 border border-indigo-100 text-indigo-500 flex items-center justify-center font-bold text-base mx-auto">
                                        📖
                                    </div>
                                @endif
                            </td>

                            <!-- Book Title & Author -->
                            <td class="py-3 px-6 font-semibold text-slate-900 max-w-xs">
                                <a href="{{ route('admin.books.show', $book) }}" class="hover:text-indigo-600 transition-colors block truncate">
                                    {{ $book->title }}
                                </a>
                                <span class="block text-xs text-slate-400 font-normal">By {{ $book->author ?? 'Unknown' }}</span>
                            </td>

                            <!-- Category -->
                            <td class="py-3 px-6 text-slate-600 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $book->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            <!-- Price -->
                            <td class="py-3 px-6 text-right font-bold text-slate-900 whitespace-nowrap">
                                ${{ number_format($book->price, 2) }}
                            </td>

                            <!-- Stock Level (US-040) -->
                            <td class="py-3 px-6 text-center whitespace-nowrap">
                                @if($book->stock == 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        🚫 0 (Out of Stock)
                                    </span>
                                @elseif($book->stock <= 5)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        ⚠️ {{ $book->stock }} left (Low Stock)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ✅ {{ $book->stock }} units
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-6 text-center whitespace-nowrap">
                                @if($book->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Stock Actions (US-037, US-038, US-039) -->
                            <td class="py-3 px-6 text-right space-x-1 whitespace-nowrap">
                                <!-- US-037 Add Stock Button -->
                                <button type="button" 
                                        data-id="{{ $book->id }}" 
                                        data-title="{{ $book->title }}" 
                                        data-stock="{{ $book->stock }}"
                                        onclick="openAddStockModal(this)" 
                                        class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200 transition-colors"
                                        title="Add Stock (+)">
                                    + Add
                                </button>

                                <!-- US-038 Update Stock Button -->
                                <button type="button" 
                                        data-id="{{ $book->id }}" 
                                        data-title="{{ $book->title }}" 
                                        data-stock="{{ $book->stock }}"
                                        onclick="openUpdateStockModal(this)" 
                                        class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors"
                                        title="Set Exact Stock">
                                    ✏️ Edit
                                </button>

                                <!-- US-039 Remove Stock Button -->
                                <button type="button" 
                                        data-id="{{ $book->id }}" 
                                        data-title="{{ $book->title }}" 
                                        data-stock="{{ $book->stock }}"
                                        onclick="openRemoveStockModal(this)" 
                                        class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg border border-rose-200 transition-colors"
                                        title="Remove Stock (-)">
                                    - Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-4xl block">📦</span>
                                    <p class="font-medium text-slate-600">No inventory records found</p>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($books->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- US-037 ADD STOCK MODAL -->
<!-- ========================================== -->
<div id="add-stock-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">📦 Add Stock Quantity</h3>
            <button type="button" onclick="closeAddStockModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form id="add-stock-form" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">Book Title:</p>
                    <p id="add-modal-book-title" class="font-semibold text-slate-800 text-sm"></p>
                    <p class="text-xs text-slate-500 mt-1">Current Stock: <strong id="add-modal-current-stock" class="text-indigo-600"></strong></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Quantity to Add</label>
                    <input type="number" name="quantity" min="1" value="10" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-5">
                <button type="button" onclick="closeAddStockModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-600/20 transition-all">
                    + Confirm Add Stock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- US-038 UPDATE STOCK MODAL -->
<!-- ========================================== -->
<div id="update-stock-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">✏️ Update Exact Stock Level</h3>
            <button type="button" onclick="closeUpdateStockModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form id="update-stock-form" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">Book Title:</p>
                    <p id="update-modal-book-title" class="font-semibold text-slate-800 text-sm"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">New Total Stock Quantity</label>
                    <input type="number" id="update-modal-stock-input" name="stock" min="0" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-5">
                <button type="button" onclick="closeUpdateStockModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                    Update Stock Level
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- US-039 REMOVE STOCK MODAL -->
<!-- ========================================== -->
<div id="remove-stock-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">➖ Remove Stock Quantity</h3>
            <button type="button" onclick="closeRemoveStockModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form id="remove-stock-form" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">Book Title:</p>
                    <p id="remove-modal-book-title" class="font-semibold text-slate-800 text-sm"></p>
                    <p class="text-xs text-slate-500 mt-1">Available Stock: <strong id="remove-modal-available-stock" class="text-rose-600"></strong></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Quantity to Remove</label>
                    <input type="number" id="remove-modal-qty-input" name="quantity" min="1" value="1" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-5">
                <button type="button" onclick="closeRemoveStockModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-600/20 transition-all">
                    - Confirm Remove Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // US-037 Add Stock Modal Logic
    function openAddStockModal(btn) {
        const id = btn.dataset.id;
        const title = btn.dataset.title;
        const stock = btn.dataset.stock;
        document.getElementById('add-modal-book-title').innerText = title;
        document.getElementById('add-modal-current-stock').innerText = stock + ' units';
        document.getElementById('add-stock-form').action = "/admin/inventory/" + id + "/add-stock";
        document.getElementById('add-stock-modal').classList.remove('hidden');
    }
    function closeAddStockModal() {
        document.getElementById('add-stock-modal').classList.add('hidden');
    }

    // US-038 Update Stock Modal Logic
    function openUpdateStockModal(btn) {
        const id = btn.dataset.id;
        const title = btn.dataset.title;
        const stock = btn.dataset.stock;
        document.getElementById('update-modal-book-title').innerText = title;
        document.getElementById('update-modal-stock-input').value = stock;
        document.getElementById('update-stock-form').action = "/admin/inventory/" + id + "/update-stock";
        document.getElementById('update-stock-modal').classList.remove('hidden');
    }
    function closeUpdateStockModal() {
        document.getElementById('update-stock-modal').classList.add('hidden');
    }

    // US-039 Remove Stock Modal Logic
    function openRemoveStockModal(btn) {
        const id = btn.dataset.id;
        const title = btn.dataset.title;
        const stock = parseInt(btn.dataset.stock);
        document.getElementById('remove-modal-book-title').innerText = title;
        document.getElementById('remove-modal-available-stock').innerText = stock + ' units';
        document.getElementById('remove-modal-qty-input').max = stock;
        document.getElementById('remove-stock-form').action = "/admin/inventory/" + id + "/remove-stock";
        document.getElementById('remove-stock-modal').classList.remove('hidden');
    }
    function closeRemoveStockModal() {
        document.getElementById('remove-stock-modal').classList.add('hidden');
    }
</script>
@endsection
