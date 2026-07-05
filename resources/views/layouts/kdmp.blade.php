<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'KDMP') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50" x-data="{ sidebarOpen: false }">
    
    <x-ui.sidebar />

    <!-- Main Content Wrapper (Memberikan ruang untuk Sidebar Fixed) -->
    <div class="flex flex-col min-h-screen md:pl-[260px] transition-all duration-300">
        
        <!-- Header -->
        <!-- Sticky, Glass Effect (bg-white/70 backdrop-blur-md), Tinggi 64px -->
        <header class="sticky top-0 z-40 flex items-center justify-between h-[64px] px-4 sm:px-6 lg:px-8 bg-white/70 backdrop-blur-md border-b border-slate-200/50">
            
            <!-- Kiri: Tombol Menu Mobile & Breadcrumb -->
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 rounded-md md:hidden text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <nav class="hidden sm:flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li>
                            <a href="#" class="text-slate-400 hover:text-slate-500">
                                <i data-lucide="home" class="w-4 h-4"></i>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                                <a href="#" class="ml-2 text-sm font-medium text-slate-500 hover:text-slate-700">KDMP v2</a>
                            </div>
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
                <!-- Search Box -->
                <div class="hidden lg:block relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" class="block w-64 py-2 pl-10 pr-3 text-sm border border-slate-200 rounded-md bg-white/50 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400 transition-shadow" placeholder="Cari data...">
                </div>

                <!-- Ikon Notifikasi (Accent Orange) -->
                <button type="button" class="relative p-2 text-slate-400 hover:text-slate-500 focus:outline-none rounded-full transition-colors hover:bg-slate-100">
                    <span class="absolute top-1.5 right-1.5 block w-2 h-2 rounded-full bg-orange-500 ring-2 ring-white"></span>
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>

                <!-- Profile Dropdown (Menggunakan Alpine.js) -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
                    <button @click="open = !open" type="button" class="flex items-center gap-2 focus:outline-none p-1 rounded-full hover:bg-slate-100 transition-colors">
                        <img class="w-8 h-8 rounded-full border border-slate-200 object-cover" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&color=2563EB&background=DBEAFE" alt="Profile">
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
                        <div class="py-1">
                            <a href="#" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2">
                                <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                            </a>
                            <a href="#" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2">
                                <i data-lucide="settings" class="w-4 h-4"></i> Pengaturan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <!-- Background slate-50, Padding 32px (p-8) -->
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

    <!-- Script inisialisasi Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
