@props([
    'title',
    'value',
    'icon',
    'trend' => null,
    'trendColor' => 'success' // success, danger, warning, info
])

@php
    $iconColors = [
        'success' => 'text-emerald-500 bg-emerald-100',
        'danger' => 'text-red-500 bg-red-100',
        'warning' => 'text-yellow-500 bg-yellow-100',
        'info' => 'text-blue-500 bg-blue-100',
    ];

    $trendColors = [
        'success' => 'text-emerald-600',
        'danger' => 'text-red-600',
        'warning' => 'text-yellow-600',
        'info' => 'text-blue-600',
    ];

    $iconStyle = $iconColors[$trendColor] ?? $iconColors['info'];
    $trendStyle = $trendColors[$trendColor] ?? $trendColors['success'];
@endphp

<x-ui.card class="p-6">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
            <h4 class="text-3xl font-bold text-slate-900 mt-2 tracking-tight">{{ $value }}</h4>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $iconStyle }}">
            <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
        </div>
    </div>
    
    @if($trend)
        <div class="mt-4 flex items-center text-sm">
            <span class="flex items-center font-medium {{ $trendStyle }}">
                @if($trendColor === 'success' || str_starts_with($trend, '+'))
                    <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                @elseif($trendColor === 'danger' || str_starts_with($trend, '-'))
                    <i data-lucide="trending-down" class="w-4 h-4 mr-1"></i>
                @else
                    <i data-lucide="minus" class="w-4 h-4 mr-1"></i>
                @endif
                {{ $trend }}
            </span>
            <span class="text-slate-500 ml-2">dari bulan lalu</span>
        </div>
    @endif
</x-ui.card>
