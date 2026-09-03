<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Products</h1>
        <div class="flex gap-2">
            <button wire:click="$set('showImport', true)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm font-medium hover:bg-gray-800">
                Import Products
            </button>
            <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">
                <x-heroicon-o-plus class="w-4 h-4" /> Add Product
            </button>
        </div>
    </div>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by description, code or brand…"
            class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Product Code</th>
                    <th class="p-3">Description</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Brand</th>
                    <th class="p-3">Vendors</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($products as $product)
                    @php($vendorNames = $product->currentPrices()->pluck('vendor.company_name')->filter()->implode(', '))
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white font-medium">{{ $product->code }}</td>
                        <td class="p-3 text-gray-300">{{ $product->description }}</td>
                        <td class="p-3"><x-badge>{{ $product->category }}</x-badge></td>
                        <td class="p-3 text-gray-400">{{ $product->brand }}</td>
                        <td class="p-3 text-gray-400 max-w-[220px] truncate" title="{{ $vendorNames }}">{{ $vendorNames ?: '—' }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <a href="{{ route('crm.prices', ['product' => $product->id]) }}" class="block w-full text-left px-3 py-1.5 text-gray-200 hover:bg-gray-700">Update Prices</a>
                                <button wire:click="edit({{ $product->id }})" class="block w-full text-left px-3 py-1.5 text-gray-200 hover:bg-gray-700">Edit</button>
                                <button wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-gray-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-gray-500">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">{{ $editingId ? 'Edit Product' : 'Add Product' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Product Code</label>
                        <input type="text" wire:model="form.code" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        @error('form.code') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Legacy / Vendor Code</label>
                        <input type="text" wire:model="form.legacy_code" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Description</label>
                        <input type="text" wire:model="form.description" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        @error('form.description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Category</label>
                        <select wire:model="form.category" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($categories as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Brand</label>
                        <input type="text" wire:model="form.brand" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showImport)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showImport', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">Import Products</h2>
                <p class="text-xs text-gray-500 mb-3">CSV columns: code, description, category, brand</p>
                <form wire:submit.prevent="importProducts">
                    <input type="file" wire:model="importFile" class="w-full text-sm text-gray-300 mb-3" />
                    @error('importFile') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showImport', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
