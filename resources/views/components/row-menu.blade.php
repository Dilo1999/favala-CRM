@props(['align' => 'right'])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
    <button @click="open = !open" type="button" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-700">
        <x-heroicon-o-dots-vertical class="w-5 h-5" />
    </button>
    <div x-show="open" x-cloak x-transition
         class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} z-20 mt-1 w-44 rounded-lg bg-zinc-800 border border-white/10 shadow-lg py-1 text-sm">
        {{ $slot }}
    </div>
</div>
