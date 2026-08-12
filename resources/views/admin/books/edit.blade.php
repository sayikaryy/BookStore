@extends('layouts.admin')

@section('title', 'Edit Book')
@section('page-title', 'Edit Book Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                ✏️
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Edit Book: {{ $book->title }}</h2>
                <p class="text-xs text-slate-500">Update book information, inventory, and cover image</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Book Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Book Title *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $book->title) }}" 
                           required 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Category *</label>
                    <select id="category_id" 
                            name="category_id" 
                            required 
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Select Category ▼</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Author -->
                <div>
                    <label for="author" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Author</label>
                    <input type="text" 
                           id="author" 
                           name="author" 
                           value="{{ old('author', $book->author) }}" 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- ISBN -->
                <div>
                    <label for="isbn" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">ISBN</label>
                    <input type="text" 
                           id="isbn" 
                           name="isbn" 
                           value="{{ old('isbn', $book->isbn) }}" 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Publisher -->
                <div>
                    <label for="publisher" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Publisher</label>
                    <input type="text" 
                           id="publisher" 
                           name="publisher" 
                           value="{{ old('publisher', $book->publisher) }}" 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Publication Year -->
                <div>
                    <label for="publication_year" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Publication Year</label>
                    <input type="number" 
                           id="publication_year" 
                           name="publication_year" 
                           value="{{ old('publication_year', $book->publication_year) }}" 
                           min="1000" 
                           max="{{ date('Y') + 1 }}"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Selling Price ($) -->
                <div>
                    <label for="price" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Selling Price ($) *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 font-bold text-sm">$</span>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               id="price" 
                               name="price" 
                               value="{{ old('price', $book->price) }}" 
                               required 
                               class="w-full pl-8 pr-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Stock Quantity *</label>
                    <input type="number" 
                           min="0" 
                           id="stock" 
                           name="stock" 
                           value="{{ old('stock', $book->stock) }}" 
                           required 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Status *</label>
                    <select id="status" 
                            name="status" 
                            required 
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="active" {{ old('status', $book->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $book->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Book Cover Upload (US-024, US-029) -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Book Cover Image</label>
                    <div class="flex flex-col sm:flex-row items-center gap-6 p-4 border border-slate-200 rounded-2xl bg-slate-50/50">
                        <!-- Current Cover -->
                        <div class="text-center shrink-0">
                            <span class="text-xs text-slate-500 block mb-1">Current Cover</span>
                            @if($book->cover_image)
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="h-32 w-24 object-cover rounded-lg shadow-md border border-slate-200 mx-auto">
                            @else
                                <div class="h-32 w-24 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-500 flex items-center justify-center font-bold text-2xl mx-auto">
                                    📖
                                </div>
                            @endif
                        </div>

                        <!-- Upload New Cover -->
                        <div class="flex-1 w-full text-center sm:text-left border-2 border-dashed border-slate-300 rounded-xl p-4 hover:border-indigo-500 transition-colors bg-white">
                            <input type="file" 
                                   id="cover_image" 
                                   name="cover_image" 
                                   accept="image/jpeg,image/jpg,image/png,image/webp" 
                                   onchange="previewEditImage(this)"
                                   class="hidden">
                            <label for="cover_image" class="cursor-pointer space-y-2 block">
                                <div id="edit-preview-container" class="hidden mb-2">
                                    <span class="text-xs text-indigo-600 font-semibold block">New Selected Cover Preview:</span>
                                    <img id="edit-image-preview" class="h-28 mx-auto sm:mx-0 object-cover rounded-lg shadow border border-indigo-200">
                                </div>
                                <div id="edit-upload-placeholder">
                                    <span class="text-2xl block">📷</span>
                                    <span class="text-xs font-semibold text-indigo-600">Click to replace cover image</span>
                                    <p class="text-[11px] text-slate-400 mt-1">Leave empty to keep current cover</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Book Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="4" 
                              class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">{{ old('description', $book->description) }}</textarea>
                </div>

            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                <a href="{{ route('admin.books.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                    Update Book
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function previewEditImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('edit-image-preview').src = e.target.result;
                document.getElementById('edit-preview-container').classList.remove('hidden');
                document.getElementById('edit-upload-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
