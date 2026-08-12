@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Overview Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Welcome Greeting Header -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-2xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-8 pointer-events-none">
            <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
        </div>
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center space-x-2 bg-indigo-500/30 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold text-indigo-200 border border-indigo-400/30 mb-4">
                <span>✨ BookVerse Admin Center</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Welcome back, {{ Auth::user()->name }} 👋</h2>
            <p class="mt-2 text-indigo-100/80 text-sm leading-relaxed">
                Manage your bookstore catalog, inspect customer orders, monitor inventory stock levels, and review registered customers.
            </p>
        </div>
    </div>

    <!-- Statistics Cards Grid (US-013 to US-017, Sprint 3) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Stat Card 1: Total Books -->
        <a href="{{ route('admin.books.index') }}" class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Books</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2 group-hover:text-indigo-600 transition-colors">
                        {{ number_format($totalBooks) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-indigo-600 mt-2">
                        Manage Catalog &rarr;
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    📖
                </div>
            </div>
        </a>

        <!-- Stat Card 2: Total Customers (US-042) -->
        <a href="{{ route('admin.customers.index') }}" class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Customers</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2 group-hover:text-blue-600 transition-colors">
                        {{ number_format($totalCustomers) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-blue-600 mt-2">
                        View Customers &rarr;
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    👥
                </div>
            </div>
        </a>

        <!-- Stat Card 3: Total Orders (US-046) -->
        <a href="{{ route('admin.orders.index') }}" class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Orders</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2 group-hover:text-amber-600 transition-colors">
                        {{ number_format($totalOrders) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-amber-600 mt-2">
                        View Orders &rarr;
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    🛍️
                </div>
            </div>
        </a>

        <!-- Stat Card 4: Total Revenue -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Revenue</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2">
                        ${{ number_format($totalSales, 2) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-emerald-600 mt-2">
                        Paid Sales
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    💰
                </div>
            </div>
        </div>

    </div>

    <!-- Overview Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Recent Books Overview -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Recently Added Books</h3>
                    <p class="text-xs text-slate-500">Latest titles in your bookstore catalog</p>
                </div>
                <a href="{{ route('admin.books.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                    View All &rarr;
                </a>
            </div>
            <div class="divide-y divide-slate-100 overflow-x-auto flex-1">
                @forelse($recentBooks as $book)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            @if($book->cover_image)
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-10 h-13 object-cover rounded shadow-sm shrink-0">
                            @else
                                <div class="w-10 h-12 rounded bg-indigo-50 text-indigo-500 border border-indigo-100 flex items-center justify-center font-bold text-xs shrink-0">
                                    📖
                                </div>
                            @endif
                            <div class="truncate">
                                <a href="{{ route('admin.books.show', $book) }}" class="text-sm font-semibold text-slate-800 hover:text-indigo-600 truncate block">
                                    {{ $book->title }}
                                </a>
                                <p class="text-xs text-slate-500 truncate">
                                    By {{ $book->author ?? 'Unknown' }} &bull; <span class="text-indigo-600 font-medium">{{ $book->category->name ?? 'Uncategorized' }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-4">
                            <span class="text-sm font-bold text-slate-900">${{ number_format($book->price, 2) }}</span>
                            <span class="block text-[11px] text-slate-500">Stock: {{ $book->stock }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-sm">
                        No books available yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions Overview -->
        <div class="space-y-6">

            <!-- Quick Action Shortcuts -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Quick Management Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.books.create') }}" class="flex items-center p-4 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-3 group-hover:scale-105 transition-transform">
                            +
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-indigo-600">Add New Book</span>
                            <span class="block text-xs text-slate-500">Upload cover & price</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.inventory.index') }}" class="flex items-center p-4 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold mr-3 group-hover:scale-105 transition-transform">
                            📦
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-600">Stock & Inventory</span>
                            <span class="block text-xs text-slate-500">Add or edit stock</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center p-4 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center font-bold mr-3 group-hover:scale-105 transition-transform">
                            🛍️
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-amber-600">Customer Orders</span>
                            <span class="block text-xs text-slate-500">Process & ship orders</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="flex items-center p-4 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3 group-hover:scale-105 transition-transform">
                            👥
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-blue-600">Customer Accounts</span>
                            <span class="block text-xs text-slate-500">View customer profiles</span>
                        </div>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
