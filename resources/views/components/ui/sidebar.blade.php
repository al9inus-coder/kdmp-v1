@php
    $menuItems = config('navigation', []);
@endphp

<!-- Sidebar -->
<!-- Fixed width 260px, Background White, Solid (Bukan Glass), Border Kanan Tipis -->
<aside class="fixed inset-y-0 left-0 z-50 flex flex-col w-[260px] bg-white border-r border-slate-200 transition-transform duration-300 transform" 
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'md:translate-x-0': true}">
    
    <!-- Logo Area -->
    <div class="flex items-center justify-center h-[64px] border-b border-slate-100 px-6 shrink-0">
        <a href="/" class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500 text-white shadow-sm">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-slate-800">KDMP <span class="text-emerald-500">v2</span></span>
        </a>
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
                        <p class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">
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
            <button type="submit" class="flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-medium text-red-600 transition-colors rounded-md bg-red-50 hover:bg-red-100">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Keluar
            </button>
        </form>
    </div>
</aside>
