<!-- Bottom Navigation Bar Ultra-Clean Gemini Style Khusus Mobile PWA -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#0E1117]/95 backdrop-blur-xl border-t border-slate-800/80 text-slate-400 px-4 py-2 shadow-2xl">
    <div class="flex items-center justify-between max-w-sm mx-auto">
        
        <!-- 1. Home / Beranda -->
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex flex-col items-center gap-1 group py-1">
            <div class="p-1 rounded-xl transition-colors group-hover:text-cyan-400">
                <i data-lucide="home" class="w-5 h-5 text-cyan-400"></i>
            </div>
            <span class="text-[10px] font-semibold text-cyan-400">Beranda</span>
        </a>

        <!-- 2. Gemini AI Chat Launcher -->
        <button @click="$dispatch('open-ai-drawer')" class="flex flex-col items-center gap-1 group py-1 relative">
            <div class="p-1 rounded-xl text-purple-400 group-hover:text-purple-300 relative">
                <svg class="w-5 h-5 text-purple-400 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-purple-400">Gemini AI</span>
        </button>

        <!-- 3. Voice Command Central Mic (Gemini Floating Pill) -->
        <button @click="$dispatch('open-ai-drawer', { voice: true })" class="flex flex-col items-center -mt-6">
            <div class="w-13 h-13 rounded-full bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-400 p-0.5 shadow-xl shadow-indigo-600/30 ring-4 ring-[#0E1117] active:scale-95 transition-transform">
                <div class="w-full h-full bg-[#0E1117] rounded-full flex items-center justify-center text-cyan-400">
                    <i data-lucide="mic" class="w-6 h-6 animate-pulse"></i>
                </div>
            </div>
            <span class="text-[10px] font-bold text-white mt-0.5">Voice AI</span>
        </button>

        <!-- 4. Camera OCR Disposisi -->
        <button @click="$dispatch('open-camera-ocr')" class="flex flex-col items-center gap-1 group py-1">
            <div class="p-1 rounded-xl text-emerald-400 group-hover:text-emerald-300">
                <i data-lucide="camera" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-semibold text-emerald-400">Scan OCR</span>
        </button>

        <!-- 5. Profil -->
        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : url('/profile') }}" class="flex flex-col items-center gap-1 group py-1">
            <div class="p-1 rounded-xl group-hover:text-slate-200">
                <i data-lucide="user" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-medium text-slate-400">Profil</span>
        </a>

    </div>
</nav>
