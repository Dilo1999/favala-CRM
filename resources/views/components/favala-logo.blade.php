@props([
    'variant' => 'full',
])

<img
    src="{{ asset('images/logo/Favala-1.png') }}"
    alt="Favala"
    {{ $attributes->class([
        'object-left shrink-0',
        'h-8 w-auto max-w-[9rem] object-contain' => $variant === 'full',
        'h-8 w-8 rounded object-cover' => $variant === 'mark',
        'h-9 w-auto max-w-[10rem] object-contain object-center' => $variant === 'auth',
        'h-10 w-auto max-w-[11rem] object-contain' => $variant === 'hero',
        'h-10 w-auto max-w-[12rem] object-contain' => $variant === 'print',
    ]) }}
/>
