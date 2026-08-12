@extends('layouts.admin')

@section('title', 'Order Details #ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT))
@section('page-title', 'Order Details')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">
            ← Back to All Orders
        </a>

        <!-- Order Action Buttons (US-048, US-049) -->
        <div class="flex items-center space-x-3">
            @if($order->status !== 'cancelled')
                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order? This will automatically restore the purchased items back into book inventory stock.')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition-colors shadow-sm">
                        🚫 Cancel Order & Restock
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- US-050 Visual Order Status Progress Tracker -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
            <div>
                <span class="text-xs uppercase font-semibold text-slate-400 tracking-wider">Order Reference</span>
                <h2 class="text-2xl font-extrabold text-slate-900 font-mono">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->created_at ? $order->created_at->format('F d, Y \a\t h:i A') : 'N/A' }}</p>
            </div>

            <!-- Current Status Badge -->
            <div>
                @if($order->status === 'completed')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        ✅ Status: Completed
                    </span>
                @elseif($order->status === 'processing')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        ⚙️ Status: Processing
                    </span>
                @elseif($order->status === 'pending')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        ⏳ Status: Pending
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        ❌ Status: Cancelled
                    </span>
                @endif
            </div>
        </div>

        <!-- Progress Timeline Steps -->
        <div class="grid grid-cols-4 gap-2 text-center text-xs">
            <!-- Step 1: Placed -->
            <div class="space-y-2">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-white bg-indigo-600">
                    1
                </div>
                <p class="font-bold text-slate-900">Order Placed</p>
                <p class="text-[11px] text-slate-400">Received</p>
            </div>

            <!-- Step 2: Pending -->
            <div class="space-y-2">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold {{ in_array($order->status, ['pending', 'processing', 'completed']) ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                    2
                </div>
                <p class="font-bold text-slate-900">Payment Verified</p>
                <p class="text-[11px] text-slate-400">Confirmed</p>
            </div>

            <!-- Step 3: Processing -->
            <div class="space-y-2">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold {{ in_array($order->status, ['processing', 'completed']) ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                    3
                </div>
                <p class="font-bold text-slate-900">Processing & Packing</p>
                <p class="text-[11px] text-slate-400">Preparing shipment</p>
            </div>

            <!-- Step 4: Completed -->
            <div class="space-y-2">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold {{ $order->status === 'completed' ? 'bg-emerald-600 text-white' : ($order->status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                    {{ $order->status === 'cancelled' ? '✕' : '4' }}
                </div>
                <p class="font-bold text-slate-900">{{ $order->status === 'cancelled' ? 'Cancelled' : 'Completed' }}</p>
                <p class="text-[11px] text-slate-400">{{ $order->status === 'cancelled' ? 'Order voided' : 'Delivered' }}</p>
            </div>
        </div>
    </div>

    <!-- Grid Layout: Items & Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left 8 Columns: US-050 Order Items Breakdown -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">📦 Order Items ({{ $order->items->count() }})</h3>
                    <span class="text-xs font-semibold text-slate-500">Total Quantity: {{ $order->items->sum('quantity') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="py-4 px-6 text-center">Cover</th>
                                <th class="py-4 px-6">Book Item</th>
                                <th class="py-4 px-6 text-right">Unit Price</th>
                                <th class="py-4 px-6 text-center">Qty</th>
                                <th class="py-4 px-6 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($order->items as $item)
                                <tr>
                                    <!-- Cover Thumbnail -->
                                    <td class="py-4 px-6 text-center">
                                        @if($item->book && $item->book->cover_image)
                                            <img src="{{ asset('storage/' . $item->book->cover_image) }}" alt="{{ $item->book->title }}" class="w-10 h-14 object-cover rounded shadow-sm mx-auto border border-slate-200">
                                        @else
                                            <div class="w-10 h-14 rounded bg-indigo-50 border border-indigo-100 text-indigo-500 flex items-center justify-center font-bold text-base mx-auto">
                                                📖
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Book Title & Category -->
                                    <td class="py-4 px-6 font-semibold text-slate-900">
                                        @if($item->book)
                                            <a href="{{ route('admin.books.show', $item->book) }}" class="hover:text-indigo-600 transition-colors block">
                                                {{ $item->book->title }}
                                            </a>
                                            <span class="text-xs text-slate-400 font-normal">
                                                By {{ $item->book->author ?? 'Unknown' }} • {{ $item->book->category->name ?? 'General' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Deleted Product</span>
                                        @endif
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="py-4 px-6 text-right text-slate-700 whitespace-nowrap">
                                        ${{ number_format($item->price, 2) }}
                                    </td>

                                    <!-- Quantity -->
                                    <td class="py-4 px-6 text-center font-bold text-slate-900 whitespace-nowrap">
                                        x{{ $item->quantity }}
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="py-4 px-6 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                        ${{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Order Financial Summary -->
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <div class="w-full sm:w-64 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal:</span>
                            <span class="font-semibold text-slate-800">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Shipping Fee:</span>
                            <span class="font-semibold text-emerald-600">FREE</span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between text-base font-extrabold text-slate-900">
                            <span>Grand Total:</span>
                            <span class="text-indigo-600">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 4 Columns: Customer & Status Management Card -->
        <div class="lg:col-span-4 space-y-6">

            <!-- US-048 Update Status Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-900">⚡ Update Order Status</h3>
                <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>⚙️ Processing</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled (Restocks Stock)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                        Save Status Change
                    </button>
                </form>
            </div>

            <!-- US-047 Customer Info Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">👤 Customer Information</h3>
                @if($order->user)
                    <div>
                        <span class="block text-xs text-slate-400">Name</span>
                        <a href="{{ route('admin.customers.show', $order->user) }}" class="font-bold text-indigo-600 hover:underline text-sm">
                            {{ $order->user->name }}
                        </a>
                    </div>
                    <div>
                        <span class="block text-xs text-slate-400">Email</span>
                        <span class="text-xs text-slate-700 font-medium">{{ $order->user->email }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-slate-400">Phone</span>
                        <span class="text-xs text-slate-700 font-medium">{{ $order->user->phone ?? 'N/A' }}</span>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Guest Customer</p>
                @endif

                <div class="pt-2 border-t border-slate-100">
                    <span class="block text-xs text-slate-400">Shipping Address</span>
                    <p class="text-xs text-slate-800 font-medium mt-0.5">{{ $order->shipping_address ?? 'No address recorded' }}</p>
                </div>
            </div>

            <!-- US-047 Payment Information Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">💳 Payment Details</h3>
                <div>
                    <span class="block text-xs text-slate-400">Payment Method</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mt-1">
                        {{ $order->payment->payment_method ?? 'ABA_KHQR' }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-slate-400">Transaction ID</span>
                    <span class="font-mono text-xs font-bold text-slate-800">{{ $order->payment->transaction_id ?? 'TXN-' . strtoupper(substr(md5($order->id), 0, 8)) }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-400">Payment Status</span>
                    @if(($order->payment->status ?? 'paid') === 'paid')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Paid
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            Pending Payment
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
