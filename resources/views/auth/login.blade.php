<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In - BookVerse</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased bg-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Title Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white text-3xl font-extrabold shadow-xl shadow-indigo-600/30 mb-3">
                📚
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">BookVerse</h1>
            <p class="text-slate-400 text-sm font-medium mt-1">Administrator Control Center</p>
        </div>

        <!-- Card Container -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-8 shadow-2xl backdrop-blur-sm">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white">Sign In to Admin Web</h2>
                <p class="text-slate-400 text-xs mt-1">Enter your credentials to access system management</p>
            </div>

            <!-- Demo Credentials Banner -->
            <div class="mb-6 bg-indigo-950/60 border border-indigo-500/30 rounded-xl p-3.5 text-xs text-indigo-200">
                <div class="flex items-center space-x-2 font-semibold text-indigo-300 mb-1">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Default Admin Credentials</span>
                </div>
                <div class="space-y-0.5 font-mono text-[11px] text-slate-300">
                    <p><span class="text-slate-400">Email:</span> admin@bookverse.com</p>
                    <p><span class="text-slate-400">Password:</span> password</p>
                </div>
            </div>

            <!-- Global Error / Flash Message -->
            @if(session('error'))
                <div class="mb-6 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm p-3.5 rounded-xl flex items-start space-x-2.5">
                    <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm p-3.5 rounded-xl flex items-start space-x-2.5">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email', 'admin@bookverse.com') }}" 
                               required 
                               autofocus
                               placeholder="admin@bookverse.com"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900/80 border {{ $errors->has('email') ? 'border-rose-500 focus:ring-rose-500' : 'border-slate-700 focus:border-indigo-500 focus:ring-indigo-500' }} rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition-all">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required
                               placeholder="••••••••"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900/80 border {{ $errors->has('password') ? 'border-rose-500 focus:ring-rose-500' : 'border-slate-700 focus:border-indigo-500 focus:ring-indigo-500' }} rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition-all">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span class="ml-2 text-xs text-slate-300">Remember me</span>
                    </label>
                    <!--  -->
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-500/40 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Sign In to Dashboard<span class="text-xs text-slate-400"></span>
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-700/60 pt-4">
                <p class="text-xs text-slate-400">
                    BookVerse System &bull; Admin Access Only
                </p>
            </div>
        </div>
    </div>

</body>
</html>
