@extends('layouts.admin')

@section('title', 'Add New Book')
@section('page-title', 'Add New Book')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                📚
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Add New Book</h2>
                <p class="text-xs text-slate-500">Fill in the details below to add a new book to your store</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Book Title (Required) -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Book Title *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}" 
                           required 
                           placeholder="e.g. Atomic Habits"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Category Dropdown (Required - US-030) -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Category *</label>
                    <select id="category_id" 
                            name="category_id" 
                            required 
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Select Category ▼</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', request('category_id')) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Author (US-031) -->
                <div>
                    <label for="author" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Author</label>
                    <input type="text" 
                           id="author" 
                           name="author" 
                           value="{{ old('author') }}" 
                           placeholder="e.g. James Clear"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- ISBN (US-032) -->
                <div>
                    <label for="isbn" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">ISBN</label>
                    <input type="text" 
                           id="isbn" 
                           name="isbn" 
                           value="{{ old('isbn') }}" 
                           placeholder="e.g. 9780735211292"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Publisher (US-033) -->
                <div>
                    <label for="publisher" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Publisher</label>
                    <input type="text" 
                           id="publisher" 
                           name="publisher" 
                           value="{{ old('publisher') }}" 
                           placeholder="e.g. Avery / Penguin Random House"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Publication Year (US-034) -->
                <div>
                    <label for="publication_year" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Publication Year</label>
                    <input type="number" 
                           id="publication_year" 
                           name="publication_year" 
                           value="{{ old('publication_year') }}" 
                           placeholder="e.g. 2018"
                           min="1000" 
                           max="{{ date('Y') + 1 }}"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Selling Price (Required - US-036) -->
                <div>
                    <label for="price" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Selling Price ($) *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 font-bold text-sm">$</span>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               id="price" 
                               name="price" 
                               value="{{ old('price') }}" 
                               required 
                               placeholder="15.00"
                               class="w-full pl-8 pr-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Stock (Required) -->
                <div>
                    <label for="stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Stock Quantity *</label>
                    <input type="number" 
                           min="0" 
                           id="stock" 
                           name="stock" 
                           value="{{ old('stock', 10) }}" 
                           required 
                           placeholder="10"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Status *</label>
                    <select id="status" 
                            name="status" 
                            required 
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Book Cover Upload (US-029) -->
                <div class="md:col-span-2">
                    <label for="cover_image" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Book Cover Image</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-indigo-500 transition-colors bg-slate-50/50">
                        <input type="file" 
                               id="cover_image" 
                               name="cover_image" 
                               accept="image/jpeg,image/jpg,image/png,image/webp" 
                               onchange="previewImage(this)"
                               class="hidden">
                        <label for="cover_image" class="cursor-pointer space-y-2 block">
                            <div id="image-preview-container" class="hidden mb-3">
                                <img id="image-preview" class="h-36 mx-auto object-cover rounded-lg shadow-md border border-slate-200">
                            </div>
                            <div id="upload-placeholder">
                                <span class="text-3xl block">🖼️</span>
                                <span class="text-sm font-semibold text-indigo-600">Click to upload cover image</span>
                                <p class="text-xs text-slate-400 mt-1">Supported formats: JPG, JPEG, PNG, WEBP (Max: 2MB)</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Description (US-035) -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Book Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="4" 
                              placeholder="Write a compelling summary or synopsis of the book..."
                              class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">{{ old('description') }}</textarea>
                </div>

            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                <a href="{{ route('admin.books.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    [Cancel]
                </a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                    [Add Book]
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-preview-container').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
