@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-colors duration-150 ease-in-out rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm';
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $variants = [
        'primary' => 'bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-emerald-500 border border-transparent',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-500 border border-slate-200 shadow-none',
        'outline' => 'bg-transparent text-emerald-600 border border-emerald-500 hover:bg-emerald-50 focus:ring-emerald-500 shadow-none',
        'ghost' => 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:ring-slate-500 shadow-none border border-transparent',
        'danger' => 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500 border border-transparent',
        'success' => 'bg-green-500 text-white hover:bg-green-600 focus:ring-green-500 border border-transparent',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $variantClass = $variants[$variant] ?? $variants['primary'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $sizeClass . ' ' . $variantClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $sizeClass . ' ' . $variantClass]) }}>
        {{ $slot }}
    </button>
@endif
