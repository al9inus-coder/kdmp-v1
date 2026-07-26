<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Asisten KDMP</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

{{-- Halaman sengaja polos: tanpa topbar KDMP, tanpa kartu, tanpa menu bawah.
     Yang terlihat hanya percakapan dan kolom ketik. --}}
<body class="font-sans antialiased h-full bg-white text-slate-900">

<div x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="h-[100dvh] flex flex-col overflow-hidden">

    {{-- Kepala halaman --}}
    <header class="h-14 shrink-0 flex items-center justify-between px-2 border-b border-slate-100">
        <button type="button" @click="sidebarOpen = true"
                class="w-10 h-10 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-colors"
                title="Menu">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <span class="text-sm font-semibold text-slate-700">Asisten KDMP</span>

        <a href="{{ route('asisten') }}"
           class="w-10 h-10 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-colors"
           title="Percakapan baru">
            <i data-lucide="square-pen" class="w-5 h-5"></i>
        </a>
    </header>

    {{-- Percakapan --}}
    <main class="flex-1 min-h-0">
        <div class="h-full mx-auto w-full max-w-3xl">
            <x-ai.chat mode="penuh" />
        </div>
    </main>

    {{-- Latar gelap saat menu terbuka --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/40" style="display:none"></div>

    {{-- Menu geser --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-[280px] bg-white border-r border-slate-200 flex flex-col transition-transform duration-300"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="h-14 shrink-0 flex items-center justify-between px-4 border-b border-slate-100">
            <span class="text-lg font-black tracking-tight text-slate-900">KDMP</span>
            <button type="button" @click="sidebarOpen = false"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            @foreach(config('navigation', []) as $item)
                @php
                    $bolehGrup = ! isset($item['roles'])
                        || (auth()->check() && auth()->user()->hasAnyRole($item['roles']));
                @endphp

                @if($bolehGrup)
                    @if(($item['type'] ?? null) === 'group')
                        <div class="pt-4 mt-4 border-t border-slate-100 first:pt-0 first:mt-0 first:border-0">
                            <p class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">{{ $item['title'] }}</p>
                            @foreach($item['children'] ?? [] as $child)
                                @if(! isset($child['roles']) || (auth()->check() && auth()->user()->hasAnyRole($child['roles'])))
                                    <x-ui.sidebar-item :item="$child" />
                                @endif
                            @endforeach
                        </div>
                    @else
                        <x-ui.sidebar-item :item="$item" />
                    @endif
                @endif
            @endforeach
        </nav>

        <div class="p-3 border-t border-slate-100 shrink-0">
            <p class="px-3 pb-2 text-xs text-slate-400 truncate">{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-medium text-red-600 rounded-md bg-red-50 hover:bg-red-100 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                </button>
            </form>
        </div>
    </aside>
</div>

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());
</script>
</body>
</html>
