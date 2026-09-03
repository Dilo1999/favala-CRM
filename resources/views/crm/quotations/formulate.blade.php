<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.quotations') }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
        <h1 class="text-2xl font-bold text-white">{{ $recordId ? 'Edit Quotation' : 'New Quotation' }}</h1>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
            <h2 class="font-bold text-white mb-4">Customer Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Customer</label>
                    <select wire:model="customer_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" {{ $dealId ? 'disabled' : '' }}>
                        <option value="">Select customer…</option>
                        @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                    @error('customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div></div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Quotation Date</label>
                    <input type="date" wire:model="quotation_date" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Expiry Date</label>
                    <input type="date" wire:model="expiry_date" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Bill To (Name)</label>
                    <input type="text" wire:model="bill_to_name" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Bill To (Phone)</label>
                    <input type="text" wire:model="bill_to_phone" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-400 mb-1">Bill To (Address)</label>
                    <input type="text" wire:model="bill_to_address" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-gray-900 border border-gray-800 p-5 overflow-x-auto">
            <h2 class="font-bold text-white mb-4">Products</h2>
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase">
                        <th class="pb-2">Product</th><th class="pb-2">Vendor</th><th class="pb-2">Qty</th>
                        <th class="pb-2">Markup %</th><th class="pb-2">Discount</th><th class="pb-2 text-right">Line Total</th><th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach ($items as $i => $item)
                        @php($line = $this->lines[$i] ?? ['line_amount' => 0])
                        <tr>
                            <td class="py-2 pr-2 min-w-[220px]">
                                <select wire:change="updateItemProduct({{ $i }}, $event.target.value)" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                                    <option value="">Select…</option>
                                    @foreach ($productOptions as $id => $name)
                                        <option value="{{ $id }}" @selected($item['product_id'] == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 pr-2 min-w-[160px]">
                                <select wire:change="updateItemVendor({{ $i }}, $event.target.value)" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                                    <option value="">—</option>
                                    @foreach (($this->vendorOptions[$i] ?? []) as $vid => $vname)
                                        <option value="{{ $vid }}" @selected($item['vendor_id'] == $vid)>{{ $vname }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 pr-2 w-20">
                                <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.qty" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                            </td>
                            <td class="py-2 pr-2 w-24">
                                <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.markup_percent" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                            </td>
                            <td class="py-2 pr-2 w-36">
                                <div class="flex gap-1">
                                    <select wire:model.lazy="items.{{ $i }}.discount_type" class="w-20 rounded-lg bg-gray-800 border-gray-700 text-white text-xs">
                                        <option value="flat">MVR</option><option value="percent">%</option>
                                    </select>
                                    <input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.discount_value" class="w-16 rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                                </div>
                            </td>
                            <td class="py-2 text-right text-white font-medium whitespace-nowrap">MVR {{ number_format($line['line_amount'], 2) }}</td>
                            <td class="py-2 pl-2">
                                @if (count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $i }})" class="text-red-400"><x-heroicon-o-trash class="w-4 h-4" /></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" wire:click="addItem" class="mt-3 text-sm text-orange-400 font-medium">+ Add Product</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5 lg:col-span-2">
                <h2 class="font-bold text-white mb-4">Order Adjustments</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Discount Type</label>
                        <select wire:model="discount_type" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="flat">Flat (MVR)</option><option value="percent">Percent (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Discount Value</label>
                        <input type="number" step="0.01" wire:model="discount_value" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">GST %</label>
                        <input type="number" step="0.01" wire:model="gst_percent" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5 text-sm space-y-2">
                <h2 class="font-bold text-white mb-2">Summary</h2>
                <div class="flex justify-between text-gray-400"><span>Subtotal</span><span class="text-white">MVR {{ number_format($this->summary['subtotal'], 2) }}</span></div>
                <div class="flex justify-between text-gray-400"><span>Discount</span><span class="text-red-400">- MVR {{ number_format($this->summary['order_discount_amount'], 2) }}</span></div>
                <div class="flex justify-between text-gray-400"><span>GST ({{ $gst_percent }}%)</span><span class="text-white">MVR {{ number_format($this->summary['gst_amount'], 2) }}</span></div>
                <div class="flex justify-between text-white font-bold text-base border-t border-gray-800 pt-2"><span>Grand Total</span><span>MVR {{ number_format($this->summary['grand_total'], 2) }}</span></div>
                <div class="flex justify-between text-gray-400 pt-2"><span>Profit</span><span class="text-emerald-400">MVR {{ number_format($this->summary['total_profit'], 2) }} ({{ $this->summary['profit_margin'] }}%)</span></div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('crm.quotations') }}" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">{{ $recordId ? 'Save Changes' : 'Create Quotation' }}</button>
        </div>
    </form>
</div>
