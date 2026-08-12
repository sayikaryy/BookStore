@extends('layouts.admin')

@section('title', 'Change Password')
@section('page-title', 'Security Settings')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                🔑
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Change Admin Password</h2>
                <p class="text-xs text-slate-500">Ensure your account uses a strong, secure password</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.change-password.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Current Password *</label>
                <input type="password" 
                       id="current_password" 
                       name="current_password" 
                       required 
                       placeholder="Enter your current password"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">New Password *</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       placeholder="Minimum 6 characters"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Confirm New Password *</label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       required 
                       placeholder="Re-enter new password"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <a href="{{ route('admin.profile') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to Profile
                </a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
