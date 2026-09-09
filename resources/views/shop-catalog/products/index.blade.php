<div>
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Products</h1>
            <p class="text-sm text-slate-500 mt-1">Quantity isn't tracked — just whether a shop carries a product, and at what price.</p>
        </div>
        <button wire:click="create" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" /> Add Product
        </button>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="relative max-w-sm flex-1 min-w-[220px]">
            <x-heroicon-o-search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search by name, code, brand…"
                class="w-full pl-10 rounded-lg bg-white border-slate-300 text-slate-900 text-sm placeholder:text-slate-400 focus:border-accent" />
        </div>
        <select wire:model="categoryFilter" class="rounded-lg bg-white border-slate-300 text-slate-700 text-sm focus:border-accent">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
        </select>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 py-20">
            <div class="flex flex-col items-center text-center">
                <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                    <x-heroicon-o-cube class="w-5 h-5 text-slate-400" />
                </div>
                <p class="text-sm text-slate-500">No products found.</p>
                <button wire:click="create" class="text-sm text-accent hover:text-accent-hover font-medium mt-1">Add your first product</button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($products as $product)
                <div wire:dblclick="edit({{ $product->id }})" title="Double-click to edit"
                    class="group relative rounded-2xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-lg transition-all overflow-hidden cursor-pointer">
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <x-row-menu>
                            <button wire:click="edit({{ $product->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                            <button wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                        </x-row-menu>
                    </div>

                    {{-- Image area: real photo if uploaded, otherwise a warm decorative
                         gradient with a code chip, echoing a "featured product" tile. --}}
                    <div class="relative h-44 overflow-hidden bg-gradient-to-br from-orange-100 via-orange-50 to-white">
                        @if ($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" class="h-full w-full object-cover" alt="{{ $product->description }}" />
                        @else
                            <div class="absolute -right-6 top-1/2 -translate-y-1/2 h-28 w-28 rounded-full border border-accent/20"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-heroicon-o-cube class="w-10 h-10 text-accent/30" />
                            </div>
                        @endif
                        <span class="absolute left-3 bottom-3 bg-white/95 text-slate-900 text-xs font-semibold px-2.5 py-1 rounded-md shadow-sm font-mono">
                            {{ $product->code }}
                        </span>
                    </div>

                    <div class="p-5">
                        @if ($product->category)
                            <p class="text-xs font-semibold text-accent uppercase tracking-wide">{{ $product->category }}</p>
                        @endif
                        <h3 class="text-slate-900 font-bold truncate mt-0.5">{{ $product->description }}</h3>
                        <p class="text-sm text-slate-400 mt-0.5">{{ $product->brand ?: '—' }}</p>

                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                            @forelse ($product->prices as $price)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500 truncate pr-2">{{ $price->shop->name }}</span>
                                    <div class="text-right shrink-0">
                                        <div class="text-accent font-bold">MVR {{ number_format($price->price, 2) }}</div>
                                        <div class="text-[11px] text-slate-400">Updated {{ $price->updated_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Not assigned to a shop yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6">{{ $products->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900 mb-5">{{ $editingId ? 'Edit Product' : 'Add Product' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Product Image</label>
                        <div class="flex items-center gap-4">
                            <div class="h-16 w-16 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                @if ($image)
                                    <img src="{{ $image->temporaryUrl() }}" class="h-full w-full object-cover" />
                                @elseif ($existingImagePath)
                                    <img src="{{ asset('storage/'.$existingImagePath) }}" class="h-full w-full object-cover" />
                                @else
                                    <x-heroicon-o-cube class="w-6 h-6 text-slate-300" />
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" wire:model="image" accept="image/*" class="w-full text-sm text-slate-600" />
                                <div wire:loading wire:target="image" class="text-xs text-slate-400 mt-1">Uploading…</div>
                                @if ($image || $existingImagePath)
                                    <button type="button" wire:click="removeImage" class="text-xs text-red-500 hover:text-red-600 mt-1">Remove image</button>
                                @endif
                            </div>
                        </div>
                        @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Code</label>
                        <input type="text" wire:model="form.code" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                        @error('form.code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Description</label>
                        <input type="text" wire:model="form.description" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                        @error('form.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Category</label>
                        <select wire:model="form.category" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent">
                            <option value="">Select category…</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('form.category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Brand</label>
                        <input type="text" wire:model="form.brand" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                    </div>

                    <div class="col-span-2 border-t border-slate-200 pt-5 mt-1">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide">Shop Prices</label>
                            <button type="button" wire:click="addPriceRow" class="text-xs text-accent hover:text-accent-hover font-medium flex items-center gap-1">
                                <x-heroicon-o-plus class="w-3.5 h-3.5" /> Add shop
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Assign this product to a shop and its price — no separate step needed.</p>

                        <div class="space-y-2">
                            @foreach ($priceRows as $index => $row)
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <select wire:model="priceRows.{{ $index }}.shop_id" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent">
                                            <option value="">Select shop…</option>
                                            @foreach ($shops as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error("priceRows.{$index}.shop_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="w-32">
                                        <input type="number" step="0.01" min="0" wire:model="priceRows.{{ $index }}.price"
                                            placeholder="Price" class="w-full rounded-lg bg-white border-slate-300 text-slate-900 text-sm focus:border-accent" />
                                        @error("priceRows.{$index}.price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="button" wire:click="removePriceRow({{ $index }})"
                                        class="mt-2.5 text-slate-400 hover:text-red-500 shrink-0 transition-colors" title="Remove">
                                        <x-heroicon-o-x class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
