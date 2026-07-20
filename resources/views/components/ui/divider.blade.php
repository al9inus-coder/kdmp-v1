@props(['variant' => 'horizontal', 'margin' => 'my-6'])

@php
    $baseClass = 'bg-slate-200 block';
    $variantClass = $variant === 'vertical' ? 'w-px h-auto mx-4 self-stretch' : 'h-px w-full ' . $margin;
@endphp

<div role="separator" {{ $attributes->merge(['class' => $baseClass . ' ' . $variantClass]) }}></div>
