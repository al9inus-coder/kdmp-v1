<!-- Bottom Navigation Bar Khusus Mobile PWA -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 text-slate-400 px-3 py-2 shadow-2xl">
    <div class="flex items-center justify-around max-w-md mx-auto">
        
        <!-- 1. Home / Beranda -->
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex flex-col items-center gap-1 group py-1">
            <div class="p-1 rounded-xl transition-colors group-hover:text-emerald-400 font-medium">
                <i data-lucide="home" class="w-5 h-5 text-emerald-400"></i>
            </div>
            <span class="text-[10px] font-semibold text-emerald-400">Beranda</span>
        </a>

        <!-- 2. AI Assistant Chat Mobile -->
        <button @click="$dispatch('open-ai-drawer')" class="flex flex-col items-center gap-1 group py-1 relative">
            <div class="p-1 rounded-xl text-indigo-400 group-hover:text-indigo-300 relative">
                <i data-lucide="bot" class="w-5 h-5"></i>
                <span class="absolute -top-1 -right-1 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
            </div>
            <span class="text-[10px] font-semibold text-indigo-400">AI Assistant</span>
        </button>

        <!-- 3. Voice Command Fast Button (CENTER FLOATING MIC) -->
        <button @click="$dispatch('open-ai-drawer', { voice: true })" class="flex flex-col items-center -mt-6">
            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-emerald-600 via-teal-500 to-cyan-400 flex items-center justify-center text-white shadow-lg shadow-emerald-600/40 ring-4 ring-slate-950 active:scale-95 transition-transform">
                <i data-lucide="mic" class="w-6 h-6 animate-pulse"></i>
            </div>
            <span class="text-[10px] font-bold text-white mt-0.5">Voice AI</span>
        </button>

        <!-- 4. Camera OCR Disposisi -->
        <button @click="$dispatch('open-camera-ocr')" class="flex flex-col items-center gap-1 group py-1">
            <div class="p-1 rounded-xl text-cyan-400 group-hover:text-cyan-300">
                <i data-lucide="camera" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-semibold text-cyan-400">Scan OCR</span>
        </button>

        <!-- 5. Profile / Profil -->
        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : url('/profile') }}" class="flex flex-col items-center gap-1 group py-1">
            <div class="p-1 rounded-xl group-hover:text-slate-200">
                <i data-lucide="user" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-medium text-slate-400">Profil</span>
        </a>

    </div>
</nav>
