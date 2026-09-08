<div>
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                @if ($filteredProduct)
                    {{ $filteredProduct->description }}
                @elseif ($filteredShop)
                    {{ $filteredShop->name }}
                @else
                    Shop Prices
                @endif
            </h1>
            <p class="text-sm text-slate-500 mt-1">Which shop carries which product, and at what price.</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($filteredProduct || $filteredShop)
                <a href="{{ route('shop-catalog.prices') }}" class="px-3 py-2 rounded-lg text-sm text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors">Clear filter</a>
            @endif
            <button wire:click="create" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" /> Add Price
            </button>
        </div>
    </div>

    <div class="relative max-w-sm mb-5">
        <x-heroicon-o-search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by product or shop…"
            class="w-full pl-10 rounded-lg bg-white border-slate-300 text-slate-900 text-sm placeholder:text-slate-400 focus:border-accent" />
    </div>

    @if ($prices->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 py-20">
            <div class="flex flex-col items-center text-center">
                <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                    <x-heroicon-o-tag class="w-5 h-5 text-slate-400" />
                </div>
                <p class="text-sm text-slate-500">No prices yet.</p>
                <button wire:click="create" class="text-sm text-accent hover:text-accent-hover font-medium mt-1">Add a price</button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($prices as $price)
                <div class="group relative rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-md transition-all p-5">
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <x-row-menu>
                            <button wire:click="edit({{ $price->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                            <button wire:click="delete({{ $price->id }})" wire:confirm="Delete this price entry?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                        </x-row-menu>
                    </div>

                    <h3 class="text-slate-900 font-semibold truncate pr-6">{{ $price->product->description }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                        <x-heroicon-o-shopping-bag class="w-3.5 h-3.5 text-slate-400" /> {{ $price->shop->name }}
                    </p>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xl font-bold text-accent">MVR {{ number_format($price->price, 2) }}</span>
                        <span class="text-xs text-slate-400">{{ $price->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6">{{ $prices->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900 mb-5">{{ $editingId ? 'Edit Price' : 'Add Price' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Product</label>
                        <select wire:model="form.product_id" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent">
                            <option value="">Select product…</option>
                            @foreach ($products as $id => $description)
                                <option value="{{ $id }}">{{ $description }}</option>
                            @endforeach
                        </select>
                        @error('form.product_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Shop</label>
                        <select wire:model="form.shop_id" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent">
                            <option value="">Select shop…</option>
                            @foreach ($shops as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('form.shop_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Price</label>
                        <input type="number" step="0.01" min="0" wire:model="form.price" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                        @error('form.price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">Save Price</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
