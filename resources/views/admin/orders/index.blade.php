@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Order Management')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Bookstore Customer Orders</h2>
            <p class="text-sm text-slate-500">Track order processing, update order status, and view customer purchases</p>
        </div>
    </div>

    <!-- Summary Stats Cards (US-046) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Orders</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalOrders) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                🛍️
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Pending</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($pendingOrders) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                ⏳
            </div>
        </div>

        <!-- Processing -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Processing</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($processingOrders) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                ⚙️
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Completed</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($completedOrders) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                ✅
            </div>
        </div>

        <!-- Cancelled -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">Cancelled</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($cancelledOrders) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg">
                ❌
            </div>
        </div>
    </div>

    <!-- US-046 Filter & Search Controls -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <!-- Search input -->
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="🔍 Search order ID, customer name, or email..."
                       class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>⚙️ Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="sm:col-span-1">
                <button type="submit" class="w-full h-full bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center justify-center transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Order Table Card (US-046, US-048, US-049, US-050) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6">Order ID</th>
                        <th class="py-4 px-6">Customer</th>
                        <th class="py-4 px-6">Date</th>
                        <th class="py-4 px-6 text-center">Items</th>
                        <th class="py-4 px-6 text-right">Total</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Order ID -->
                            <td class="py-4 px-6 font-mono font-bold text-indigo-600 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">
                                    #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>

                            <!-- Customer Details -->
                            <td class="py-4 px-6">
                                @if($order->user)
                                    <a href="{{ route('admin.customers.show', $order->user) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors block">
                                        {{ $order->user->name }}
                                    </a>
                                    <span class="text-xs text-slate-500 block">{{ $order->user->email }}</span>
                                @else
                                    <span class="text-slate-400 italic">Guest Customer</span>
                                @endif
                            </td>

                            <!-- Order Date -->
                            <td class="py-4 px-6 text-xs text-slate-600 whitespace-nowrap">
                                {{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : '-' }}
                            </td>

                            <!-- Items Summary -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    📦 {{ $order->items->sum('quantity') }} books ({{ $order->items->count() }} titles)
                                </span>
                            </td>

                            <!-- Total -->
                            <td class="py-4 px-6 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>

                            <!-- Status Badge (US-050) -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                @if($order->status === 'completed')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✅ Completed
                                    </span>
                                @elseif($order->status === 'processing')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        ⚙️ Processing
                                    </span>
                                @elseif($order->status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        ⏳ Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        ❌ Cancelled
                                    </span>
                                @endif
                            </td>

                            <!-- Actions (US-047, US-048, US-049) -->
                            <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors">
                                    👁️ Details
                                </a>

                                <!-- Quick Change Status Modal Trigger -->
                                <button type="button" 
                                        data-id="{{ $order->id }}" 
                                        data-status="{{ $order->status }}"
                                        onclick="openChangeStatusModal(this)"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                                    ⚡ Status
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-4xl block">🛍️</span>
                                    <p class="font-medium text-slate-600">No orders found</p>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or search terms.</p>
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

<!-- ========================================== -->
<!-- US-048 QUICK CHANGE STATUS MODAL -->
<!-- ========================================== -->
<div id="change-status-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">⚡ Update Order Status</h3>
            <button type="button" onclick="closeChangeStatusModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form id="change-status-form" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">Order Reference:</p>
                    <p id="modal-order-ref" class="font-mono font-bold text-indigo-600 text-sm"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Select New Status</label>
                    <select id="modal-status-select" name="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="pending">⏳ Pending</option>
                        <option value="processing">⚙️ Processing</option>
                        <option value="completed">✅ Completed</option>
                        <option value="cancelled">❌ Cancelled (Restocks Items)</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-5">
                <button type="button" onclick="closeChangeStatusModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openChangeStatusModal(btn) {
        const id = btn.dataset.id;
        const status = btn.dataset.status;
        document.getElementById('modal-order-ref').innerText = '#ORD-' + String(id).padStart(5, '0');
        document.getElementById('modal-status-select').value = status;
        document.getElementById('change-status-form').action = "/admin/orders/" + id + "/status";
        document.getElementById('change-status-modal').classList.remove('hidden');
    }
    function closeChangeStatusModal() {
        document.getElementById('change-status-modal').classList.add('hidden');
    }
</script>
@endsection
