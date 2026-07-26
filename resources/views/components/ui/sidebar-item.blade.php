@props(['item'])

@php
    $hasChildren = isset($item['children']) && count($item['children']) > 0;
    
    // Safety check route exists, fallback to '#' if not
    $href = '#';
    $routeParams = $item['params'] ?? [];
    if(isset($item['route']) && Route::has($item['route'])) {
        $href = route($item['route'], $routeParams);
    }

    $isActive = false;
    if (isset($item['route']) && Route::has($item['route'])) {
        $isActive = request()->routeIs($item['route'] . '*');

        if ($isActive && !empty($routeParams)) {
            foreach ($routeParams as $key => $value) {
                if ((string) request()->query($key) !== (string) $value) {
                    $isActive = false;
                    break;
                }
            }
        }

        if ($isActive && ($item['exact_query'] ?? false) && request()->query()) {
            $isActive = false;
        }
    }

    // Optional permission check
    if (isset($item['permission']) && Auth::check() && !Auth::user()->can($item['permission'])) {
        return; // Don't render if unauthorized
    }

    // Badge angka "butuh tindakan" — kunci badge didefinisikan di config/navigation.php.
    // Hitungan SPD diambil dari AntreanKerja agar sama persis dengan pil asisten.
    $badgeCount = null;
    if (isset($item['badge'])) {
        $badgeCount = match ($item['badge']) {
            'kabid_paket_pending' => \App\Models\Package::where('status', 'submitted')->count(),
            'kabid_sppd_pending'  => app(\App\Services\AntreanKerja::class)->jumlahSpd(auth()->user()),
            default => null,
        };
    }
@endphp

@if($hasChildren)
    <!-- Collapsible Menu -->
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="mb-1">
        <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true } else { open = !open }"
            class="flex items-center justify-between w-full gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors {{ $isActive ? 'text-emerald-700 bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
            :class="{ 'justify-center': sidebarCollapsed }"
            :title="sidebarCollapsed ? '{{ $item['title'] }}' : ''">
            <div class="flex items-center gap-3">
                @if(isset($item['icon']))
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
                @endif
                <span x-show="!sidebarCollapsed">{{ $item['title'] }}</span>
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" x-show="!sidebarCollapsed"></i>
        </button>

        <!-- Children (Recursive) -->
        <div x-show="open && !sidebarCollapsed"
             x-transition:enter="transition-all ease-in-out duration-200"
             x-transition:enter-start="opacity-0 max-h-0"
             x-transition:enter-end="opacity-100 max-h-screen"
             x-transition:leave="transition-all ease-in-out duration-200"
             x-transition:leave-start="opacity-100 max-h-screen"
             x-transition:leave-end="opacity-0 max-h-0"
             class="pl-9 pr-2 mt-1 space-y-1 overflow-hidden">
            @foreach($item['children'] as $child)
                <x-ui.sidebar-item :item="$child" />
            @endforeach
        </div>
    </div>
@else
    <!-- Single Menu Item -->
    <a href="{{ $href }}"
       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md transition-colors mb-1 {{ $isActive ? 'text-emerald-700 bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
       :class="{ 'justify-center': sidebarCollapsed }"
       :title="sidebarCollapsed ? '{{ $item['title'] }}' : ''">
        @if(isset($item['icon']))
            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
        @endif
        <span x-show="!sidebarCollapsed">{{ $item['title'] }}</span>
        @if(($badgeCount ?? 0) > 0)
            <span class="ml-auto inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-[10px] font-bold bg-rose-500 text-white shadow-sm" x-show="!sidebarCollapsed">
                {{ $badgeCount > 99 ? '99+' : $badgeCount }}
            </span>
        @endif
    </a>
@endif
