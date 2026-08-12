@extends('layouts.admin')

@section('title', 'Admin Profile')
@section('page-title', 'Admin Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header banner -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
        <div class="w-20 h-20 rounded-full bg-indigo-600 text-white font-extrabold text-3xl flex items-center justify-center shadow-lg shadow-indigo-600/30">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="text-center md:text-left flex-1">
            <h2 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <div class="mt-2 flex flex-wrap gap-2 justify-center md:justify-start">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 capitalize">
                    Role: {{ $user->role }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 capitalize">
                    Status: {{ $user->status }}
                </span>
            </div>
        </div>
        <a href="{{ route('admin.change-password') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-xl transition-colors border border-slate-200">
            Change Password &rarr;
        </a>
    </div>

    <!-- Update Form -->
    <div class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-4 mb-6">Personal & Contact Information</h3>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Email Address *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           required 
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Phone Number</label>
                    <input type="text" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $user->phone) }}" 
                           placeholder="+855 12 345 678"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <!-- Role (Disabled) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">System Role</label>
                    <input type="text" 
                           value="{{ ucfirst($user->role) }}" 
                           disabled 
                           class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-slate-500 text-sm cursor-not-allowed">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Address</label>
                <textarea id="address" 
                          name="address" 
                          rows="3" 
                          placeholder="Street address, city, region..."
                          class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">{{ old('address', $user->address) }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
