@props(['color' => 'gray'])

@php
    $colors = [
        'gray' => 'bg-zinc-700 text-white',
        'green' => 'bg-green-600 text-white',
        'red' => 'bg-red-600 text-white',
        'orange' => 'bg-accent text-white',
        'blue' => 'bg-blue-600 text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
