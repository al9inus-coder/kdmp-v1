@props(['variant' => 'draft'])

@php
    $baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize tracking-wide';
    
    $variants = [
        'draft' => 'bg-slate-100 text-slate-700 border border-slate-200',
        'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'success' => 'bg-green-100 text-green-800 border border-green-200',
        'danger' => 'bg-red-100 text-red-800 border border-red-200',
        'info' => 'bg-blue-100 text-blue-800 border border-blue-200',
    ];

    $variantClass = $variants[$variant] ?? $variants['draft'];
@endphp

<span {{ $attributes->merge(['class' => $baseClasses . ' ' . $variantClass]) }}>
    {{ $slot }}
</span>
