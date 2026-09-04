<div>
    @if ($showForm)
        {{-- Full-page Create / Edit form --}}
        <div class="flex items-center gap-3 mb-6">
            <button wire:click="$set('showForm', false)" class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $editingId ? 'Edit Product' : 'Create Product' }}</h1>
                <p class="text-sm text-zinc-500">Add a new item to the product catalog.</p>
            </div>
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
            <h2 class="font-bold text-white mb-1">Product Details</h2>
            <p class="text-xs text-zinc-500 mb-4">Core attributes used to identify this item across quotations, invoices, and stock.</p>

            <form wire:submit.prevent="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Product Code <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="form.code" placeholder="e.g., CEM-01" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    @error('form.code') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Brand</label>
                    <input type="text" wire:model="form.brand" placeholder="e.g., Tokyo Cement" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                </div>
                <div class="col-span-1 sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Description <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="form.description" placeholder="e.g., Portland Cement (50kg Bag)" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    @error('form.description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Category</label>
                    <select wire:model="form.category" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                        <option value="">Select…</option>
                        @foreach ($categories as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Unit of Measure (UoM)</label>
                    <input type="text" wire:model="form.unit_of_measure" placeholder="e.g., Bag, Pcs, Mtr" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                </div>
                <div class="col-span-1 sm:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700 transition-colors">Cancel</button>
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                        <x-heroicon-o-check class="w-4 h-4" /> {{ $editingId ? 'Save Changes' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </div>
    @else
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Products</h1>
        <div class="flex gap-2">
            <button wire:click="$set('showImport', true)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700">
                Import Products
            </button>
            <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                <x-heroicon-o-plus class="w-4 h-4" /> Add Product
            </button>
        </div>
    </div>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by description, code or brand…"
            class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Product Code</th>
                    <th class="p-3">Description</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Brand</th>
                    <th class="p-3">Vendors</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($products as $product)
                    @php($vendorNames = $product->currentPrices()->pluck('vendor.company_name')->filter()->implode(', '))
                    <tr class="hover:bg-zinc-700/40">
                        <td class="p-3 text-white font-medium">{{ $product->code }}</td>
                        <td class="p-3 text-zinc-300">{{ $product->description }}</td>
                        <td class="p-3"><x-badge>{{ $product->category }}</x-badge></td>
                        <td class="p-3 text-zinc-400">{{ $product->brand }}</td>
                        <td class="p-3 text-zinc-400 max-w-[220px] truncate" title="{{ $vendorNames }}">{{ $vendorNames ?: '—' }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <a href="{{ route('crm.prices', ['product' => $product->id]) }}" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Update Prices</a>
                                <button wire:click="edit({{ $product->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                <button wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-zinc-500">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
    @endif

    @if ($showImport)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showImport', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">Import Products</h2>
                <p class="text-xs text-zinc-500 mb-3">CSV columns: code, description, category, brand</p>
                <form wire:submit.prevent="importProducts">
                    <input type="file" wire:model="importFile" class="w-full text-sm text-zinc-300 mb-3" />
                    @error('importFile') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showImport', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
