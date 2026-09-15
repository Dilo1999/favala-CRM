<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Create Deal</h1>
    </div>

    <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-1">Deal Details</h2>
                <p class="text-xs text-zinc-500 mb-4">Enter the details for the new deal.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Customer <span class="text-red-400">*</span></label>
                        <select wire:model="customer_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select customer…</option>
                            @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Deal Date</label>
                        <div class="relative" x-data="datePicker('deal_date', '{{ $deal_date }}')" wire:ignore>
                            <x-heroicon-o-calendar class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input type="text" x-ref="input" readonly class="w-full pl-9 rounded-lg bg-zinc-700 border-white/10 text-white text-sm cursor-pointer" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Request Source</label>
                        <select wire:model="request_source" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select a source</option>
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
                        <textarea wire:model="additional_details" rows="3" placeholder="Any other notes or details…" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-1">Products Requested</h2>
                <p class="text-xs text-zinc-500 mb-4">What the customer is asking to be quoted for.</p>

                <div class="grid grid-cols-[1fr_6rem_auto] gap-x-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2 px-1">
                    <span>Product</span>
                    <span>Quantity</span>
                    <span></span>
                </div>

                <div class="space-y-2">
                    @foreach ($products as $i => $row)
                        <div class="grid grid-cols-[1fr_6rem_auto] gap-3 items-center border-t border-white/10 pt-2">
                            <div>
                                <x-product-search :index="$i" :label="$row['product_label'] ?? null"
                                    :open-row="$productSearchRow" :search-term="$productSearch" :results="$this->productSearchResults" />
                            </div>
                            <div>
                                <input type="number" step="1" min="1" @if($row['max_qty'] !== null) max="{{ $row['max_qty'] }}" @endif
                                    wire:model="products.{{ $i }}.qty" placeholder="Qty" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                                @if ($row['max_qty'] !== null)
                                    <p class="text-[11px] text-zinc-500 mt-1">Max: {{ $row['max_qty'] }}</p>
                                @endif
                                @error("products.{$i}.qty") <span class="block text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                @if (count($products) > 1)
                                    <button type="button" wire:click="removeProductRow({{ $i }})" title="Remove product"
                                        class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" wire:click="addProductRow"
                    class="mt-3 flex items-center gap-2 text-sm font-medium text-zinc-400 hover:text-accent transition-colors">
                    <x-heroicon-o-plus-circle class="w-4 h-4" /> Add Product
                </button>
            </div>
        </div>

        {{-- Sticky submit column --}}
        <div class="lg:sticky lg:top-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-1">Submit</h2>
                <p class="text-xs text-zinc-500 mb-4">Once you've filled in all the details, you can submit the deal.</p>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                        Create Deal
                    </button>
                    <a href="{{ route('crm.deals') }}" class="text-center px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700 transition-colors">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
