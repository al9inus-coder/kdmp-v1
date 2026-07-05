@props([
    'icon' => 'inbox',
    'title' => 'Tidak Ada Data',
    'description' => 'Belum ada data yang dapat ditampilkan.'
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50']) }}>
    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
        <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
    </div>
    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
    <p class="text-sm text-slate-500 mt-2 max-w-sm">{{ $description }}</p>
    
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
