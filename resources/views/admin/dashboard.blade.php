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
            <!-- <p class="mt-2 text-indigo-100/80 text-sm leading-relaxed">
                Here is a summary of your bookstore status. Manage your inventory, categories, and track performance in real-time.
            </p> -->
        </div>
    </div>

    <!-- Statistics Cards Grid (US-013 to US-017) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Stat Card 1: Total Books (US-014) -->
        <a href="{{ route('admin.books.index') }}" class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Books</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2 group-hover:text-indigo-600 transition-colors">
                        {{ number_format($totalBooks) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-indigo-600 mt-2">
                        View Inventory &rarr;
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Stat Card 2: Total Customers (US-015) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Customers</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2">
                        {{ number_format($totalCustomers) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-slate-400 mt-2">
                        Registered Accounts
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stat Card 3: Total Orders (US-016) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Orders</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2">
                        {{ number_format($totalOrders) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-slate-400 mt-2">
                        System Orders
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stat Card 4: Total Sales (US-017) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Revenue</span>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2">
                        ${{ number_format($totalSales, 2) }}
                    </p>
                    <span class="inline-flex items-center text-xs font-medium text-emerald-600 mt-2">
                        Completed Payments
                    </span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
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

        <!-- Quick Actions & Categories Overview -->
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

                    <a href="{{ route('admin.categories.create') }}" class="flex items-center p-4 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-3 group-hover:scale-105 transition-transform">
                            +
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-indigo-600">Add Category</span>
                            <span class="block text-xs text-slate-500">Organize catalog</span>
                        </div>
                    </a>
                </div>
            </div>



        </div>

    </div>

</div>
@endsection
