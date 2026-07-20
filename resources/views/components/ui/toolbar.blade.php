@props(['search' => false, 'searchPlaceholder' => 'Cari data...'])

<div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row lg:items-center justify-between gap-4']) }}>
    
    <!-- Area Kiri: Search & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
        
        <!-- Auto Search Input jika prop search=true -->
        @if($search)
            <div class="flex items-center gap-2 w-full sm:max-w-sm shrink-0">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <x-ui.input type="text" name="search" class="pl-9" placeholder="{{ $searchPlaceholder }}" value="{{ request('search') }}" />
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-white bg-slate-800 rounded-md hover:bg-slate-900 transition-colors shrink-0">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ url()->current().'?'.http_build_query(request()->except(['search', 'page'])) }}"
                        class="inline-flex items-center p-2 text-slate-400 hover:text-rose-500 transition-colors shrink-0" title="Hapus pencarian">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        @endif

        <!-- Slot khusus Filter (Dropdown, Datepicker, dll) -->
        @if(isset($filters))
            <div class="flex flex-wrap items-center gap-2">
                {{ $filters }}
            </div>
        @endif
    </div>

    <!-- Area Kanan: Export & Action Buttons (Default Slot) -->
    <div class="flex flex-wrap items-center gap-3 shrink-0">
        {{ $slot }}
    </div>
</div>
