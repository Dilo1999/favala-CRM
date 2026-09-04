<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Price List</h1>
            @if ($filteredProduct)
                <p class="text-sm text-zinc-400 mt-1">
                    Showing prices for <span class="text-white font-medium">{{ $filteredProduct->description }}</span>
                    <button wire:click="$set('productFilter', null)" class="text-accent ml-2">Clear</button>
                </p>
            @else
                <p class="text-sm text-zinc-500 mt-1">View and manage prices from all vendors. Click a row to edit a price.</p>
            @endif
        </div>
        <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> Add Price
        </button>
    </div>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by product or vendor…"
            class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Date Added</th>
                    <th class="p-3">Product</th>
                    <th class="p-3">Vendor</th>
                    <th class="p-3">Price (without GST)</th>
                    <th class="p-3">Staff Added</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($prices as $price)
                    <tr class="hover:bg-zinc-700/40 cursor-pointer" wire:click="edit({{ $price->id }})">
                        <td class="p-3 text-zinc-400">{{ $price->created_at->format('d M Y') }}</td>
                        <td class="p-3 text-white">
                            {{ $price->product?->description }}
                            <span class="text-zinc-500 text-xs block">{{ $price->product?->code }}</span>
                        </td>
                        <td class="p-3 text-zinc-300">{{ $price->vendor?->company_name }}</td>
                        <td class="p-3 text-white font-medium">MVR {{ number_format($price->price, 2) }}</td>
                        <td class="p-3 text-zinc-400">{{ $price->addedBy?->name }}</td>
                        <td class="p-3 text-right" wire:click.stop>
                            <x-row-menu>
                                <button wire:click="edit({{ $price->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                <button wire:click="delete({{ $price->id }})" wire:confirm="Delete this price entry?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-zinc-500">No price entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $prices->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">{{ $editingId ? 'Edit Price Entry' : 'Add Price' }}</h2>
                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Product</label>
                        <select wire:model="form.product_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($products as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('form.product_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Vendor</label>
                        <select wire:model="form.vendor_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($vendors as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('form.vendor_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Price (without GST)</label>
                        <input type="number" step="0.01" wire:model="form.price" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        @error('form.price') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save Price</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
