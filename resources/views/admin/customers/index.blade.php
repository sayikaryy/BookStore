@extends('layouts.admin')

@section('title', 'Customers')
@section('page-title', 'Customer Management')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Customer Accounts</h2>
            <p class="text-sm text-slate-500">View registered bookstore customers and their purchasing activity</p>
        </div>
    </div>

    <!-- Stats Cards (US-042) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Customers</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalCustomers) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold">
                👥
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Active Accounts</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($activeCustomers) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold">
                ✅
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Inactive Accounts</p>
                <h3 class="text-2xl font-bold text-slate-700 mt-1">{{ number_format($inactiveCustomers) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-500 flex items-center justify-center text-xl font-bold">
                🔒
            </div>
        </div>
    </div>

    <!-- US-043 Search & Filter Controls -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <!-- Search Input -->
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="🔍 Search customer by name, email, or phone number..."
                       class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="sm:col-span-1">
                <button type="submit" class="w-full h-full bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center justify-center transition-colors">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Customer Table Card (US-042) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6">Customer</th>
                        <th class="py-4 px-6">Phone & Address</th>
                        <th class="py-4 px-6 text-center">Orders</th>
                        <th class="py-4 px-6 text-right">Total Spent</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-center">Joined</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Name & Email -->
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 border border-indigo-200 text-indigo-700 font-bold flex items-center justify-center text-sm shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors block">
                                            {{ $customer->name }}
                                        </a>
                                        <span class="text-xs text-slate-500 block">{{ $customer->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Phone & Address -->
                            <td class="py-4 px-6 text-xs text-slate-600 max-w-xs truncate">
                                <div>📞 {{ $customer->phone ?? 'Not provided' }}</div>
                                <div class="text-slate-400 truncate">📍 {{ $customer->address ?? 'No address' }}</div>
                            </td>

                            <!-- Orders Count (US-042) -->
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                    {{ $customer->orders_count }} orders
                                </span>
                            </td>

                            <!-- Total Spent -->
                            <td class="py-4 px-6 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                ${{ number_format($customer->total_spent ?? 0, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-6 text-center">
                                @if($customer->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        ● Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        ○ Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Joined Date -->
                            <td class="py-4 px-6 text-center text-xs text-slate-500 whitespace-nowrap">
                                {{ $customer->created_at ? $customer->created_at->format('M d, Y') : '-' }}
                            </td>

                            <!-- Actions (US-044, US-045) -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors">
                                    View Profile & Orders →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-4xl block">👥</span>
                                    <p class="font-medium text-slate-600">No customers found</p>
                                    <p class="text-xs text-slate-400">Try adjusting your search criteria or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
