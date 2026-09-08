@props(['index', 'label' => null, 'openRow' => null, 'searchTerm' => '', 'results' => null])

<div class="relative">
    @if ($openRow === $index)
        <input type="text" wire:model.debounce.250ms="productSearch" wire:keydown.escape="closeProductSearch"
            placeholder="Search by name or code…" autofocus
            class="w-full rounded-lg bg-zinc-900 border-accent text-white text-sm" />
        <div class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto rounded-lg bg-zinc-800 border border-white/10 shadow-lg">
            @forelse (($results ?? []) as $product)
                <button type="button" wire:click="pickProduct({{ $index }}, '{{ $product->key }}')"
                    class="block w-full text-left px-3 py-2 text-sm text-zinc-200 hover:bg-zinc-700 border-b border-white/5 last:border-b-0">
                    <span class="text-white">{{ $product->description }}</span>
                    <span class="block text-xs text-zinc-500">
                        {{ $product->code }}
                        @if ($product->origin)
                            &middot; <span class="text-accent">{{ $product->origin }}</span>
                        @endif
                    </span>
                </button>
            @empty
                <p class="px-3 py-2 text-xs text-zinc-500">
                    {{ trim($searchTerm) === '' ? 'Type to search products…' : 'No matching products.' }}
                </p>
            @endforelse
        </div>
        <button type="button" wire:click="closeProductSearch" tabindex="-1"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white">
            <x-heroicon-o-x class="w-4 h-4" />
        </button>
    @else
        <button type="button" wire:click="openProductSearch({{ $index }})"
            class="w-full flex items-center gap-2 rounded-lg bg-zinc-900 border border-white/10 text-sm px-3 py-2 text-left hover:border-accent">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 shrink-0" />
            <span class="{{ $label ? 'text-white' : 'text-zinc-500' }} truncate">{{ $label ?: 'Search products…' }}</span>
        </button>
    @endif
</div>
