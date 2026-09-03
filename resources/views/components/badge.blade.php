@props(['color' => 'gray'])

@php
    $colors = [
        'gray' => 'bg-gray-700 text-gray-200',
        'green' => 'bg-emerald-500/15 text-emerald-400',
        'red' => 'bg-red-500/15 text-red-400',
        'orange' => 'bg-orange-500/15 text-orange-400',
        'blue' => 'bg-blue-500/15 text-blue-400',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
