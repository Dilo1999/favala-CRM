<div>
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Shops</h1>
            <p class="text-sm text-slate-500 mt-1">The shops in the Shop Catalog support system.</p>
        </div>
        <button wire:click="create" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" /> Add Shop
        </button>
    </div>

    <div class="relative max-w-sm mb-5">
        <x-heroicon-o-search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by name, contact, phone…"
            class="w-full pl-10 rounded-lg bg-white border-slate-300 text-slate-900 text-sm placeholder:text-slate-400 focus:border-accent" />
    </div>

    @if ($shops->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 py-20">
            <div class="flex flex-col items-center text-center">
                <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                    <x-heroicon-o-shopping-bag class="w-5 h-5 text-slate-400" />
                </div>
                <p class="text-sm text-slate-500">No shops yet.</p>
                <button wire:click="create" class="text-sm text-accent hover:text-accent-hover font-medium mt-1">Add your first shop</button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($shops as $shop)
                <div wire:dblclick="edit({{ $shop->id }})" title="Double-click to edit"
                    class="group relative rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-md transition-all overflow-hidden cursor-pointer">
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <x-row-menu>
                            <button wire:click="edit({{ $shop->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                            <button wire:click="delete({{ $shop->id }})" wire:confirm="Delete this shop?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                        </x-row-menu>
                    </div>

                    <div class="h-16 bg-gradient-to-r from-accent-light to-accent"></div>

                    <div class="px-5 pb-5">
                        <div class="h-14 w-14 rounded-xl bg-white shadow ring-4 ring-white -mt-7 flex items-center justify-center text-accent font-bold text-lg">
                            {{ mb_substr($shop->name, 0, 1) }}
                        </div>

                        <h3 class="text-slate-900 font-semibold truncate pr-6 mt-3">{{ $shop->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $shop->contact_person ?: 'No contact set' }}</p>

                        <div class="text-xs text-slate-500 mt-3 space-y-1">
                            @if ($shop->phone)
                                <p class="flex items-center gap-1.5"><x-heroicon-o-phone class="w-3.5 h-3.5 text-slate-400" /> {{ $shop->phone }}</p>
                            @endif
                            @if ($shop->location)
                                <p class="flex items-center gap-1.5"><x-heroicon-o-location-marker class="w-3.5 h-3.5 text-slate-400" /> {{ $shop->location }}</p>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Products</p>
                            @forelse ($shop->prices as $price)
                                <div class="flex items-center justify-between text-sm py-1">
                                    <span class="text-slate-600 truncate pr-2">{{ $price->product->description }}</span>
                                    <span class="text-accent font-bold shrink-0">MVR {{ number_format($price->price, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">No products assigned yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6">{{ $shops->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900 mb-5">{{ $editingId ? 'Edit Shop' : 'Add Shop' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Name</label>
                        <input type="text" wire:model="form.name" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                        @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Contact Person</label>
                        <input type="text" wire:model="form.contact_person" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Phone</label>
                        <input type="text" wire:model="form.phone" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Location</label>
                        <input type="text" wire:model="form.location" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">Save Shop</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
