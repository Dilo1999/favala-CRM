<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.quotations') }}" class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $recordId ? 'Edit Quotation' : 'Create Quotation' }}</h1>
            <p class="text-sm text-zinc-500">Build a priced quote for a customer, product by product.</p>
        </div>
    </div>

    <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-1">Customer Information</h2>
                <p class="text-xs text-zinc-500 mb-4">Who this quotation is for.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if (! $recordId)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Populate from Deal</label>
                            <select wire:model="dealId" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                                <option value="">None (Manual Entry)</option>
                                @foreach ($deals as $deal)
                                    <option value="{{ $deal->id }}">{{ $deal->friendly_id }} — {{ $deal->customer?->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Customer <span class="text-red-400">*</span></label>
                        <select wire:model="customer_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" {{ $dealId ? 'disabled' : '' }}>
                            <option value="">Select customer…</option>
                            @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Quotation Date</label>
                        <div class="relative" x-data="datePicker('quotation_date', '{{ $quotation_date }}')" wire:ignore>
                            <x-heroicon-o-calendar class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input type="text" x-ref="input" readonly class="w-full pl-9 rounded-lg bg-zinc-700 border-white/10 text-white text-sm cursor-pointer" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Expiry Date</label>
                        <div class="relative" x-data="datePicker('expiry_date', '{{ $expiry_date }}')" wire:ignore>
                            <x-heroicon-o-calendar class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input type="text" x-ref="input" readonly class="w-full pl-9 rounded-lg bg-zinc-700 border-white/10 text-white text-sm cursor-pointer" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 overflow-x-auto">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-bold text-white">Products</h2>
                </div>
                <p class="text-xs text-zinc-500 mb-4">The line items being quoted.</p>

                <table class="w-full text-sm min-w-[960px]">
                    <thead>
                        <tr class="text-left text-zinc-500 text-xs uppercase">
                            <th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2">Vendor</th>
                            <th class="pb-2">Price</th><th class="pb-2">Markup %</th><th class="pb-2">Discount</th><th class="pb-2 text-right">Total</th><th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($items as $i => $item)
                            @php($line = $this->lines[$i] ?? ['line_amount' => 0])
                            <tr>
                                <td class="py-2 pr-2 min-w-[220px]">
                                    <select wire:change="updateItemProduct({{ $i }}, $event.target.value)" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                                        <option value="">Select…</option>
                                        @foreach ($productOptions as $id => $name)
                                            <option value="{{ $id }}" @selected($item['product_id'] == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2 pr-2 w-20">
                                    <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.qty" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                                </td>
                                <td class="py-2 pr-2 min-w-[160px]">
                                    <select wire:change="updateItemVendor({{ $i }}, $event.target.value)" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                                        <option value="">—</option>
                                        @foreach (($this->vendorOptions[$i] ?? []) as $vid => $vname)
                                            <option value="{{ $vid }}" @selected($item['vendor_id'] == $vid)>{{ $vname }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2 pr-2 w-24">
                                    <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.cost" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                                </td>
                                <td class="py-2 pr-2 w-24">
                                    <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.markup_percent" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                                </td>
                                <td class="py-2 pr-2 w-36">
                                    <div class="flex gap-1">
                                        <select wire:model.lazy="items.{{ $i }}.discount_type" class="w-20 rounded-lg bg-zinc-700 border-white/10 text-white text-xs">
                                            <option value="flat">MVR</option><option value="percent">%</option>
                                        </select>
                                        <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.discount_value" class="w-16 rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                                    </div>
                                </td>
                                <td class="py-2 text-right text-white font-medium whitespace-nowrap">MVR {{ number_format($line['line_amount'], 2) }}</td>
                                <td class="py-2 pl-2">
                                    @if (count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $i }})" title="Remove product"
                                            class="h-8 w-8 flex items-center justify-center rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="button" wire:click="addItem"
                    class="mt-3 w-full flex items-center justify-center gap-2 rounded-lg border border-dashed border-white/15 py-2.5 text-sm font-medium text-zinc-400 hover:text-accent hover:border-accent/60 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" /> Add Product
                </button>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-1">Quotation Details</h2>
                <p class="text-xs text-zinc-500 mb-4">Terms, payment conditions, and delivery notes for this quote.</p>
                <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Terms & Conditions</label>
                <textarea wire:model="terms_conditions" rows="5" placeholder="e.g. 50% advance payment required. Delivery within 7 working days of order confirmation…"
                    class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm"></textarea>
            </div>
        </div>

        {{-- Sticky summary column --}}
        <div class="lg:sticky lg:top-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 text-sm space-y-3">
                <h2 class="font-bold text-white mb-1">Summary</h2>

                <div class="flex justify-between items-center text-zinc-400"><span>Subtotal</span><span class="text-white font-medium">MVR {{ number_format($this->summary['subtotal'], 2) }}</span></div>

                <div class="border-t border-white/10"></div>

                <div class="flex justify-between items-center gap-3">
                    <span class="text-zinc-400 shrink-0">Discount</span>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-xs text-zinc-300 cursor-pointer">
                            <input type="radio" wire:model="discount_type" value="flat" /> Flat
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-zinc-300 cursor-pointer">
                            <input type="radio" wire:model="discount_type" value="percent" /> %
                        </label>
                        <input type="number" step="0.01" wire:model="discount_value" class="w-20 rounded-lg bg-zinc-700 border-white/10 text-white text-sm text-right" />
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-zinc-400">GST (%)</span>
                    <input type="number" step="0.01" wire:model="gst_percent" class="w-20 rounded-lg bg-zinc-700 border-white/10 text-white text-sm text-right" />
                </div>

                <div class="flex justify-between items-center text-zinc-400"><span>GST Amount</span><span class="text-white font-medium">MVR {{ number_format($this->summary['gst_amount'], 2) }}</span></div>

                <div class="flex justify-between items-center text-white font-bold text-base border-t border-white/10 pt-3"><span>Grand Total</span><span>MVR {{ number_format($this->summary['grand_total'], 2) }}</span></div>

                <div class="flex justify-between items-center pt-1">
                    <span class="text-zinc-400">Total Profit</span>
                    <span class="text-accent font-semibold">MVR {{ number_format($this->summary['total_profit'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-zinc-400">Profit Margin</span>
                    <span class="text-accent font-semibold">{{ $this->summary['profit_margin'] }}%</span>
                </div>

                <div class="flex flex-col gap-2 pt-3">
                    <button type="submit" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                        <x-heroicon-o-check class="w-4 h-4" /> {{ $recordId ? 'Save Changes' : 'Create Quotation' }}
                    </button>
                    <a href="{{ route('crm.quotations') }}" class="text-center px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700 transition-colors">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
