<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'KDMP') }} - @yield('title', 'Dashboard')</title>

    <!-- PWA Web Manifest & Mobile Theme -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 pb-16 md:pb-0"
      x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
      x-init="sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true'; $watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">
    <!-- Backdrop untuk Mobile -->
    <div x-show="sidebarOpen" 
         x-transition.opacity
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden"
         style="display: none;">
    </div>
    
    <x-ui.sidebar />

    <!-- Main Content Wrapper (Memberikan ruang untuk Sidebar Fixed) -->
    <div class="flex flex-col min-h-screen transition-all duration-300"
         :class="{ 'md:pl-[260px]': !sidebarCollapsed, 'md:pl-[72px]': sidebarCollapsed }">
        
        <!-- Header -->
        <!-- Sticky, Glass Effect (bg-white/70 backdrop-blur-md), Tinggi 64px -->
        <header class="sticky top-0 z-40 flex items-center justify-between h-[64px] px-4 sm:px-6 lg:px-8 bg-white/70 backdrop-blur-md border-b border-slate-200/50">
            
            <!-- Kiri: Tombol Menu Mobile & Breadcrumb -->
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 rounded-md md:hidden text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>

                <!-- Tombol Perkecil/Perbesar Sidebar (Desktop) -->
                <button @click="sidebarCollapsed = !sidebarCollapsed" type="button"
                        class="hidden md:inline-flex p-2 -ml-2 rounded-md text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500 transition-colors"
                        :title="sidebarCollapsed ? 'Perbesar menu' : 'Perkecil menu'">
                    <i data-lucide="panel-left-close" class="w-6 h-6" x-show="!sidebarCollapsed"></i>
                    <i data-lucide="panel-left-open" class="w-6 h-6" x-show="sidebarCollapsed" style="display:none;"></i>
                </button>
                
                <nav class="hidden sm:flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li>
                            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center text-slate-400 hover:text-slate-600 transition-colors" title="Beranda">
                                <i data-lucide="home" class="w-4 h-4"></i>
                                <span class="ml-2 text-sm font-medium">Beranda</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                                <span class="ml-2 text-sm font-medium text-slate-800" aria-current="page">@yield('title', 'Halaman')</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Kanan: Search, Notifikasi, Dropdown Profil -->
            <div class="flex items-center gap-4">
                <!-- Search Box (Live Search Paket) -->
                <div class="hidden lg:block relative"
                     x-data="{
                        q: '',
                        results: [],
                        loading: false,
                        open: false,
                        async search() {
                            if (this.q.trim().length < 2) { this.results = []; this.open = false; return; }
                            this.loading = true; this.open = true;
                            try {
                                const res = await fetch(`{{ route('search') }}?q=${encodeURIComponent(this.q)}`, { headers: { 'Accept': 'application/json' } });
                                const data = await res.json();
                                this.results = data.results || [];
                            } catch (e) { this.results = []; }
                            this.loading = false;
                        },
                        close() { this.open = false; }
                     }"
                     @keydown.escape="close()"
                     @click.away="close()">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" x-model="q" @input.debounce.300ms="search()" @focus="if (results.length) open = true"
                           class="block w-64 py-2 pl-10 pr-3 text-sm border border-slate-200 rounded-md bg-white/50 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400 transition-shadow"
                           placeholder="Cari ID RUP / nama paket..." autocomplete="off">

                    <!-- Dropdown Hasil -->
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 z-50 mt-2 w-96 max-h-96 overflow-y-auto bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5"
                         style="display:none;">
                        <!-- Loading -->
                        <div x-show="loading" class="px-4 py-3 text-sm text-slate-500">Mencari…</div>

                        <!-- Kosong -->
                        <div x-show="!loading && results.length === 0 && q.trim().length >= 2" class="px-4 py-3 text-sm text-slate-500">
                            Tidak ada paket yang cocok.
                        </div>

                        <!-- Hasil -->
                        <template x-for="item in results" :key="item.id_rup">
                            <a :href="item.url"
                               class="flex items-start gap-3 px-4 py-2.5 hover:bg-slate-50 border-b border-slate-100 last:border-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate" x-text="item.nama_paket || '(Tanpa nama)'"></p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        ID RUP: <span class="font-mono text-slate-600" x-text="item.id_rup || '-'"></span>
                                        <span x-show="item.status" class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 uppercase" x-text="item.status"></span>
                                    </p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Profile Dropdown (Menggunakan Alpine.js) -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
                    <button @click="open = !open" type="button" class="flex items-center gap-2 focus:outline-none p-1 rounded-full hover:bg-slate-100 transition-colors">
                        <img class="w-8 h-8 rounded-full border border-slate-200 object-cover bg-slate-50" src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('storage/avatars/avatar.png') }}" alt="Profile">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 hidden sm:block"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-75" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95" 
                         class="absolute right-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2">
                                <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-4 bg-slate-50">
            @if(isset($header))
                <div class="mb-6">
                    {{ $header }}
                </div>
            @endif

            <!-- Slot Konten Utama -->
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="py-4 text-center border-t border-slate-200/60 bg-slate-50 mt-auto">
            <p class="text-xs text-slate-500">
                &copy; {{ date('Y') }} KDMP - Kendali Digital Manajemen Pengadaan. All rights reserved.
            </p>
        </footer>
    </div>

    <!-- Mobile PWA Bottom Navigation Bar (Otomatis Tampil di Ponsel) -->
    <x-mobile-pwa-bottom-nav />

    <!-- AI Assistant Interactive Widget & Slide-over Drawer (Touch-friendly & Desktop FAB) -->
    <x-ai-assistant-widget />

    @stack('scripts')

    <!-- PWA Service Worker Registration & Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // PWA Service Worker Registration
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('KDMP PWA Service Worker Registered:', reg.scope))
                    .catch(err => console.error('KDMP PWA Service Worker Failed:', err));
            }
        });
    </script>
</body>
</html>
