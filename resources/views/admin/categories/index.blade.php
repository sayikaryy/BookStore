@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Category Management')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Book Categories</h2>
            <p class="text-sm text-slate-500">Organize and manage your bookstore catalog categories</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            + Add Category
        </a>
    </div>

    <!-- Search & Filter Card (US-022) -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
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
                       placeholder="🔍 Search categories by name..."
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

            <!-- Filter Buttons -->
            <div class="sm:col-span-1 flex items-center space-x-2">
                <button type="submit" class="w-full h-full bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center justify-center transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Category Table Card (US-021) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-4 px-6">#</th>
                        <th class="py-4 px-6">Category Name</th>
                        <th class="py-4 px-6">Description</th>
                        <th class="py-4 px-6 text-center">Books</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($categories as $index => $category)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 font-mono text-slate-400 text-xs">
                                {{ $categories->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-900">
                                <a href="{{ route('admin.categories.show', $category) }}" class="hover:text-indigo-600 transition-colors">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td class="py-4 px-6 text-slate-600 max-w-xs truncate">
                                {{ $category->description ?? 'No description provided.' }}
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-slate-800">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-700">
                                    {{ $category->books_count }} books
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($category->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        ● Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        ○ Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.categories.show', $category) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 transition-colors" title="View Details">
                                    👁️
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 transition-colors" title="Edit Category">
                                    ✏️
                                </a>
                                <button type="button" 
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}"
                                        data-books-count="{{ $category->books_count }}"
                                        onclick="openDeleteModal(this)" 
                                        class="p-1.5 text-slate-500 hover:text-rose-600 transition-colors" 
                                        title="Delete Category">
                                    🗑️
                                </button>

                                <form id="delete-form-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-4xl block">📂</span>
                                    <p class="font-medium text-slate-600">No categories found</p>
                                    <p class="text-xs text-slate-400">Try adjusting your search criteria or create a new category.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>

<!-- US-020 Safe Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold mx-auto">
            ⚠️
        </div>
        <div class="text-center space-y-2">
            <h3 class="text-lg font-bold text-slate-900">Delete Category?</h3>
            <p class="text-sm text-slate-600">
                Are you sure you want to delete <strong id="delete-category-name" class="text-slate-900"></strong>?
            </p>
            <div id="delete-warning-books" class="hidden bg-amber-50 border border-amber-200 text-amber-800 text-xs p-3 rounded-xl text-left">
                ⚠️ <strong>Note:</strong> This category has assigned books. The system will prevent deletion to preserve database integrity.
            </div>
        </div>
        <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                Cancel
            </button>
            <button type="button" onclick="confirmDelete()" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-600/20 transition-all">
                Delete Category
            </button>
        </div>
    </div>
</div>

<script>
    let activeDeleteId = null;

    function openDeleteModal(btn) {
        activeDeleteId = btn.dataset.id;
        const name = btn.dataset.name;
        const booksCount = parseInt(btn.dataset.booksCount || 0);

        document.getElementById('delete-category-name').innerText = name;
        const warning = document.getElementById('delete-warning-books');
        if (booksCount > 0) {
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
        }
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        activeDeleteId = null;
        document.getElementById('delete-modal').classList.add('hidden');
    }

    function confirmDelete() {
        if (activeDeleteId) {
            document.getElementById('delete-form-' + activeDeleteId).submit();
        }
    }
</script>
@endsection
