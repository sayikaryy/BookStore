@extends('layouts.admin')

@section('title', 'Customer Details - ' . $user->name)
@section('page-title', 'Customer Profile & Order History')

@section('content')
<div class="space-y-6">

    <!-- Back Button & Page Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">
            ← Back to Customer List
        </a>
    </div>

    <!-- Customer Profile Header Card (US-044) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start md:items-center space-x-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-indigo-600/30 shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="space-y-1">
                    <div class="flex items-center space-x-3">
                        <h2 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h2>
                        @if($user->status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">● Active</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">○ Inactive</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 flex items-center gap-2">
                        ✉️ {{ $user->email }}
                        <span class="text-slate-300">|</span>
                        📞 {{ $user->phone ?? 'No phone provided' }}
                    </p>
                    <p class="text-xs text-slate-400">
                        📍 {{ $user->address ?? 'No physical address provided' }}
                    </p>
                </div>
            </div>

            <!-- Customer Summary Quick Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-8">
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Total Orders</span>
                    <span class="text-xl font-bold text-slate-900">{{ $user->orders_count }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Completed Orders</span>
                    <span class="text-xl font-bold text-emerald-600">{{ $completedOrdersCount }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Total Spent</span>
                    <span class="text-xl font-extrabold text-indigo-600">${{ number_format($totalSpent, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- US-045 Customer Order History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">🛍️ Order History</h3>
                <p class="text-xs text-slate-500">All book purchase orders placed by {{ $user->name }}</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-slate-100 rounded-full text-slate-700">
                {{ $orders->total() }} order(s) total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6">Order ID</th>
                        <th class="py-4 px-6">Date</th>
                        <th class="py-4 px-6">Purchased Items</th>
                        <th class="py-4 px-6 text-center">Payment Method</th>
                        <th class="py-4 px-6 text-right">Total Amount</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Order ID -->
                            <td class="py-4 px-6 font-mono font-bold text-indigo-600">
                                #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Date -->
                            <td class="py-4 px-6 text-xs text-slate-600 whitespace-nowrap">
                                {{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : '-' }}
                            </td>

                            <!-- Items Summary -->
                            <td class="py-4 px-6 text-xs text-slate-700 max-w-xs">
                                <ul class="space-y-1">
                                    @foreach($order->items as $item)
                                        <li class="truncate">
                                            • {{ $item->book->title ?? 'Book' }} <span class="text-slate-400 font-semibold">(x{{ $item->quantity }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            <!-- Payment Method -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    💳 {{ $order->payment->payment_method ?? 'ABA_KHQR' }}
                                </span>
                            </td>

                            <!-- Total Amount -->
                            <td class="py-4 px-6 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                @if($order->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        ✅ Completed
                                    </span>
                                @elseif($order->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        ⚙️ Processing
                                    </span>
                                @elseif($order->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        ⏳ Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        ❌ Cancelled
                                    </span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 transition-colors text-xs font-semibold">
                                    View Order →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-4xl block">🛍️</span>
                                    <p class="font-medium text-slate-600">No orders placed yet</p>
                                    <p class="text-xs text-slate-400">This customer has not placed any book orders.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
