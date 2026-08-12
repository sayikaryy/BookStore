@extends('layouts.admin')

@section('title', $book->title . ' - Book Details')
@section('page-title', 'Book Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.books.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            &larr; Back to Books List
        </a>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.books.edit', $book) }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                ✏️ Edit Book
            </a>
            <button type="button" onclick="document.getElementById('delete-book-modal').classList.remove('hidden')" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-sm rounded-xl border border-rose-200 transition-all">
                🗑️ Delete Book
            </button>

            <form id="delete-book-form" action="{{ route('admin.books.destroy', $book) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <!-- Details Card (US-028) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8">
        <div class="flex flex-col md:flex-row gap-8">
            
            <!-- Book Cover -->
            <div class="w-full md:w-56 shrink-0 text-center">
                @if($book->cover_image)
                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full max-w-[220px] h-auto object-cover rounded-2xl shadow-lg border border-slate-200 mx-auto">
                @else
                    <div class="w-full max-w-[220px] h-72 rounded-2xl bg-indigo-50 border-2 border-dashed border-indigo-200 text-indigo-500 flex flex-col items-center justify-center mx-auto">
                        <span class="text-5xl mb-2">📖</span>
                        <span class="text-xs font-semibold text-indigo-600">No Cover Uploaded</span>
                    </div>
                @endif

                <div class="mt-4 space-y-2">
                    <span class="block text-2xl font-extrabold text-slate-900">${{ number_format($book->price, 2) }}</span>
                    <div>
                        @if($book->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                ● Active Product
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                ○ Inactive Product
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Metadata Details -->
            <div class="flex-1 space-y-6">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 mb-2">
                        Category: {{ $book->category->name ?? 'Uncategorized' }}
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $book->title }}</h1>
                    <p class="text-base text-slate-600 font-medium mt-1">By <span class="text-slate-900">{{ $book->author ?? 'Unknown Author' }}</span></p>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold">ISBN</span>
                        <span class="font-mono text-slate-900 font-bold mt-1 block">{{ $book->isbn ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold">Publisher</span>
                        <span class="text-slate-900 font-semibold mt-1 block">{{ $book->publisher ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold">Publication Year</span>
                        <span class="text-slate-900 font-semibold mt-1 block">{{ $book->publication_year ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold">In Stock</span>
                        <span class="text-slate-900 font-semibold mt-1 block">{{ number_format($book->stock) }} units</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold">Created Date</span>
                        <span class="text-slate-900 font-semibold mt-1 block">{{ $book->created_at ? $book->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold">Last Updated</span>
                        <span class="text-slate-900 font-semibold mt-1 block">{{ $book->updated_at ? $book->updated_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>

                <!-- Description Section -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-2">Book Description</h3>
                    <div class="text-sm text-slate-700 leading-relaxed bg-white border border-slate-200 p-4 rounded-xl">
                        {{ $book->description ?? 'No description has been provided for this book yet.' }}
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Delete Book Confirmation Modal -->
<div id="delete-book-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold mx-auto">
            ⚠️
        </div>
        <div class="text-center space-y-2">
            <h3 class="text-lg font-bold text-slate-900">Delete Book?</h3>
            <p class="text-sm text-slate-600">
                Are you sure you want to delete <strong>{{ $book->title }}</strong>? This action cannot be undone.
            </p>
        </div>
        <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" onclick="document.getElementById('delete-book-modal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                Cancel
            </button>
            <button type="button" onclick="document.getElementById('delete-book-form').submit()" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-600/20 transition-all">
                Delete Book
            </button>
        </div>
    </div>
</div>
@endsection
