@props(['disabled' => false, 'readonly' => false, 'invalid' => false, 'required' => false, 'rows' => 4])

@php
    $baseClass = 'block w-full rounded-md shadow-sm sm:text-sm transition-colors duration-200 ease-in-out px-3 py-2 ';
    $stateClass = $invalid 
        ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-offset-0 ' 
        : 'border-slate-300 text-slate-900 placeholder-slate-400 focus:ring-blue-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-0 bg-white ';
    $disabledClass = 'disabled:bg-slate-100 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none disabled:cursor-not-allowed ';
    $readonlyClass = 'read-only:bg-slate-50 read-only:text-slate-500 read-only:focus:ring-0 read-only:focus:border-slate-300 read-only:shadow-none ';
    
    $finalClass = trim($baseClass . $stateClass . $disabledClass . $readonlyClass);
@endphp

<textarea 
    rows="{{ $rows }}"
    {{ $disabled ? 'disabled' : '' }} 
    {{ $readonly ? 'readonly' : '' }} 
    {{ $required ? 'required' : '' }} 
    {!! $attributes->merge(['class' => $finalClass]) !!}
>{{ $slot }}</textarea>
