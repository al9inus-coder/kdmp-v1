@php
    $menuItems = config('navigation', []);
@endphp

<style>
    @keyframes berkibar {
        0%, 100% { transform: rotate(0deg) skewY(0deg) scale(1); }
        25% { transform: rotate(2deg) skewY(3deg) scale(1.02); }
        50% { transform: rotate(0deg) skewY(0deg) scale(1); }
        75% { transform: rotate(-1deg) skewY(-2deg) scale(0.98); }
    }
    .animate-berkibar {
        animation: berkibar 4s ease-in-out infinite;
        transform-origin: left center;
    }
</style>

<!-- Sidebar -->
<!-- Fixed width 260px, Background White, Solid (Bukan Glass), Border Kanan Tipis -->
<aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-slate-200 transition-all duration-300 transform overflow-hidden"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'md:translate-x-0': true, 'w-[260px]': !sidebarCollapsed, 'w-[72px]': sidebarCollapsed}">

    <!-- Logo Area -->
    <div class="flex items-center justify-between md:justify-center h-[64px] border-b border-slate-100 px-4 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden transition-all duration-300">
            <img src="{{ asset('images/bendera.svg') }}" alt="Bendera" class="w-12 h-8 rounded-sm shrink-0 shadow-md drop-shadow-sm border border-slate-200/50 object-cover animate-berkibar">
            <div class="flex items-center whitespace-nowrap pr-1" x-show="!sidebarCollapsed">
                <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">KDMP</span>
            </div>
        </a>
        <button type="button" @click="sidebarOpen = false" class="p-1 rounded-md text-slate-400 hover:bg-slate-100 md:hidden transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>



    <!-- Navigation -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        @foreach($menuItems as $item)
            @php
                $canAccessGroup = !isset($item['roles']) || (auth()->check() && auth()->user()->hasAnyRole($item['roles']));
            @endphp
            
            @if($canAccessGroup)
                @if(isset($item['type']) && $item['type'] === 'group')
                    <!-- Group Label -->
                    <div class="pt-4 mt-4 border-t border-slate-100 first:pt-0 first:mt-0 first:border-0">
                        <p class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase" x-show="!sidebarCollapsed">
                            {{ $item['title'] }}
                        </p>
                        @if(isset($item['children']))
                            @foreach($item['children'] as $child)
                                @php
                                    $canAccessChild = !isset($child['roles']) || (auth()->check() && auth()->user()->hasAnyRole($child['roles']));
                                @endphp
                                @if($canAccessChild)
                                    <x-ui.sidebar-item :item="$child" />
                                @endif
                            @endforeach
                        @endif
                    </div>
                @else
                    <!-- Top level item -->
                    <x-ui.sidebar-item :item="$item" />
                @endif
            @endif
        @endforeach
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-slate-100 shrink-0">
        <form method="POST" action="{{ route('logout') ?? '#' }}">
            @csrf
            <button type="submit" class="flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-medium text-red-600 transition-colors rounded-md bg-red-50 hover:bg-red-100" :title="sidebarCollapsed ? 'Keluar' : ''">
                <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i>
                <span x-show="!sidebarCollapsed">Keluar</span>
            </button>
        </form>
    </div>
</aside>
