@props(['variant' => 'default', 'padding' => 'md'])

@php
    $baseClasses = 'rounded-2xl overflow-hidden';
    
    $paddings = [
        'none' => 'p-0',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
    ];

    $variants = [
        'default' => 'bg-white border border-slate-200 shadow-md',
        'glass' => 'bg-white/70 backdrop-blur-md border border-white/20 shadow-[0_8px_32px_0_rgba(31,38,135,0.07)]',
        'flat' => 'bg-white border border-slate-200 shadow-none',
    ];

    $paddingClass = $paddings[$padding] ?? $paddings['md'];
    $variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => $baseClasses . ' ' . $variantClass . ' ' . $paddingClass]) }}>
    {{ $slot }}
</div>
