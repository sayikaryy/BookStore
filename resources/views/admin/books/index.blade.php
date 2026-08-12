@extends('layouts.admin')

@section('title', 'Books')
@section('page-title', 'Book Management')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Book Bookstore Products</h2>
            <p class="text-sm text-slate-500">Manage your bookstore inventory, pricing, and book covers</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            + Add Book
        </a>
    </div>

    <!-- Search & Filter Controls (US-026, US-027) -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.books.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <!-- Search input: title, author, ISBN -->
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
            <div class="sm:col-span-4">
                <select name="category_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <option value="all">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <option value="all">All Statuses</option>
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

    <!-- Book Table Card (US-026) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6 text-center">Cover</th>
                        <th class="py-4 px-6">Book Title</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6">Author</th>
                        <th class="py-4 px-6 text-right">Price</th>
                        <th class="py-4 px-6 text-center">Stock</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($books as $book)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Cover Thumbnail (US-029) -->
                            <td class="py-3 px-6 text-center">
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-10 h-14 object-cover rounded shadow-sm mx-auto border border-slate-200">
                                @else
                                    <div class="w-10 h-14 rounded bg-indigo-50 border border-indigo-100 text-indigo-500 flex items-center justify-center font-bold text-base mx-auto">
                                        📖
                                    </div>
                                @endif
                            </td>

                            <!-- Book Title & ISBN -->
                            <td class="py-3 px-6 font-semibold text-slate-900 max-w-xs">
                                <a href="{{ route('admin.books.show', $book) }}" class="hover:text-indigo-600 transition-colors block truncate">
                                    {{ $book->title }}
                                </a>
                                @if($book->isbn)
                                    <span class="block text-[11px] text-slate-400 font-mono font-normal">ISBN: {{ $book->isbn }}</span>
                                @endif
                            </td>

                            <!-- Category -->
                            <td class="py-3 px-6 text-slate-600 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $book->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            <!-- Author -->
                            <td class="py-3 px-6 text-slate-700 whitespace-nowrap">
                                {{ $book->author ?? '-' }}
                            </td>

                            <!-- Price (US-036) -->
                            <td class="py-3 px-6 text-right font-bold text-slate-900 whitespace-nowrap">
                                ${{ number_format($book->price, 2) }}
                            </td>

                            <!-- Stock -->
                            <td class="py-3 px-6 text-center whitespace-nowrap">
                                @if($book->stock <= 5)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        {{ $book->stock }} left
                                    </span>
                                @else
                                    <span class="text-slate-700 font-semibold text-xs">
                                        {{ $book->stock }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-6 text-center whitespace-nowrap">
                                @if($book->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        ● Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        ○ Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-6 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.books.show', $book) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 transition-colors" title="View Details">
                                    👁️
                                </a>
                                <a href="{{ route('admin.books.edit', $book) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 transition-colors" title="Edit Book">
                                    ✏️
                                </a>
                                <button type="button" 
                                        onclick="openDeleteModal('{{ $book->id }}', '{{ addslashes($book->title) }}')" 
                                        class="p-1.5 text-slate-500 hover:text-rose-600 transition-colors" 
                                        title="Delete Book">
                                    🗑️
                                </button>

                                <form id="delete-book-form-{{ $book->id }}" action="{{ route('admin.books.destroy', $book) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-4xl block">📚</span>
                                    <p class="font-medium text-slate-600">No books found</p>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or click "+ Add Book" to add a new product.</p>
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

<!-- Delete Book Confirmation Modal (US-025) -->
<div id="delete-book-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold mx-auto">
            ⚠️
        </div>
        <div class="text-center space-y-2">
            <h3 class="text-lg font-bold text-slate-900">Delete Book?</h3>
            <p class="text-sm text-slate-600">
                Are you sure you want to delete <strong id="delete-book-title" class="text-slate-900"></strong>?
            </p>
        </div>
        <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" onclick="closeDeleteBookModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                Cancel
            </button>
            <button type="button" onclick="confirmDeleteBook()" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-600/20 transition-all">
                Delete Book
            </button>
        </div>
    </div>
</div>

<script>
    let activeDeleteBookId = null;

    function openDeleteModal(id, title) {
        activeDeleteBookId = id;
        document.getElementById('delete-book-title').innerText = title;
        document.getElementById('delete-book-modal').classList.remove('hidden');
    }

    function closeDeleteBookModal() {
        activeDeleteBookId = null;
        document.getElementById('delete-book-modal').classList.add('hidden');
    }

    function confirmDeleteBook() {
        if (activeDeleteBookId) {
            document.getElementById('delete-book-form-' + activeDeleteBookId).submit();
        }
    }
</script>
@endsection
