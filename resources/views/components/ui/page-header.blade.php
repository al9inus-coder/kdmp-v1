@props(['title', 'description' => null])

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        @if($description)
            <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="flex items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
