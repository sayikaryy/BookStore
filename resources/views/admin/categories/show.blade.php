@extends('layouts.admin')

@section('title', $category->name . ' - Category Details')
@section('page-title', 'Category Details')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center space-x-3">
                <h2 class="text-2xl font-bold text-slate-900">{{ $category->name }}</h2>
                @if($category->status === 'active')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Active
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        Inactive
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-600 max-w-xl">{{ $category->description ?? 'No description available for this category.' }}</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.categories.edit', $category) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-xl transition-colors">
                ✏️ Edit Category
            </a>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                &larr; Back to Categories
            </a>
        </div>
    </div>

    <!-- Books under this category -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-bold text-slate-900">Books in this Category ({{ $category->books->count() }})</h3>
            <a href="{{ route('admin.books.create') }}?category_id={{ $category->id }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                + Add Book to Category
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($category->books as $book)
                <div class="border border-slate-200 rounded-xl p-4 flex flex-col justify-between hover:shadow-md transition-shadow bg-slate-50/50">
                    <div class="space-y-3">
                        <div class="w-full h-44 bg-slate-200 rounded-lg overflow-hidden flex items-center justify-center">
                            @if($book->cover_image)
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-4xl">📖</span>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('admin.books.show', $book) }}" class="font-bold text-sm text-slate-900 hover:text-indigo-600 line-clamp-2">
                                {{ $book->title }}
                            </a>
                            <p class="text-xs text-slate-500 mt-1">By {{ $book->author ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-between">
                        <span class="font-bold text-indigo-600 text-sm">${{ number_format($book->price, 2) }}</span>
                        <span class="text-xs text-slate-500">Stock: {{ $book->stock }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-sm">
                    No books have been assigned to this category yet.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
