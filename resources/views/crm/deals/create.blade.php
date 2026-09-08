<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.deals') }}" class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">New Deal</h1>
            <p class="text-sm text-zinc-500">Log a new customer request for a quotation.</p>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
            <div class="flex items-center gap-2 mb-1">
                <x-heroicon-o-briefcase class="w-5 h-5 text-accent" />
                <h2 class="font-bold text-white">Deal Details</h2>
            </div>
            <p class="text-xs text-zinc-500 mb-4">Who the request is from and how it came in.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="col-span-1 sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Customer <span class="text-red-400">*</span></label>
                    <select wire:model="customer_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                        <option value="">Select customer…</option>
                        @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                    @error('customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Deal Date</label>
                    <input type="date" wire:model="deal_date" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Request Source</label>
                    <select wire:model="request_source" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                        <option value="">Select…</option>
                        @foreach ($requestSources as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Assigned Staff</label>
                    <select wire:model="assigned_staff_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Deal Stage</label>
                    <select wire:model="stage" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                        <option value="potential">Potential</option>
                        <option value="hot">🔥 Hot Deal</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
                <div class="col-span-1 sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Additional Details</label>
                    <textarea wire:model="additional_details" rows="3" placeholder="Anything the team should know about this request…" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm"></textarea>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
            <div class="flex items-center gap-2 mb-1">
                <x-heroicon-o-cube class="w-5 h-5 text-accent" />
                <h2 class="font-bold text-white">Products Requested</h2>
            </div>
            <p class="text-xs text-zinc-500 mb-4">What the customer is asking to be quoted for.</p>

            <div class="space-y-2">
                @foreach ($products as $i => $row)
                    <div class="flex gap-3 items-center rounded-lg border border-white/10 bg-zinc-900/40 p-3">
                        <div class="flex-1">
                            <x-product-search :index="$i" :label="$row['product_label'] ?? null"
                                :open-row="$productSearchRow" :search-term="$productSearch" :results="$this->productSearchResults" />
                        </div>
                        <div class="w-24 shrink-0">
                            <input type="number" step="1" min="1" wire:model="products.{{ $i }}.qty" placeholder="Qty" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        </div>
                        @if (count($products) > 1)
                            <button type="button" wire:click="removeProductRow({{ $i }})" title="Remove product"
                                class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="addProductRow"
                class="mt-3 w-full flex items-center justify-center gap-2 rounded-lg border border-dashed border-white/15 py-2.5 text-sm font-medium text-zinc-400 hover:text-accent hover:border-accent/60 transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" /> Add Product
            </button>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('crm.deals') }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-800 transition-colors">Cancel</a>
            <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                <x-heroicon-o-check class="w-4 h-4" /> Create Deal
            </button>
        </div>
    </form>
</div>
