<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.invoices') }}" class="text-zinc-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
        <h1 class="text-2xl font-bold text-white">New Manual Invoice</h1>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Customer</label>
                <select wire:model="customer_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                    <option value="">Select customer…</option>
                    @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                </select>
                @error('customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
            <div></div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Invoice Date</label>
                <input type="date" wire:model="invoice_date" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Expiry Date</label>
                <input type="date" wire:model="expiry_date" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Bill To (Name)</label>
                <input type="text" wire:model="bill_to_name" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Bill To (Phone)</label>
                <input type="text" wire:model="bill_to_phone" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 overflow-x-auto">
            <h2 class="font-bold text-white mb-4">Items</h2>
            <table class="w-full text-sm min-w-[700px]">
                <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2">Markup %</th><th class="pb-2 text-right">Amount</th><th></th></tr></thead>
                <tbody class="divide-y divide-white/10">
                    @foreach ($items as $i => $item)
                        @php($line = $this->lines[$i] ?? ['line_amount' => 0])
                        <tr>
                            <td class="py-2 pr-2 min-w-[240px]">
                                <x-product-search :index="$i" :label="$item['product_label'] ?? null"
                                    :open-row="$productSearchRow" :search-term="$productSearch" :results="$this->productSearchResults" />
                            </td>
                            <td class="py-2 pr-2 w-20">
                                <input type="number" step="1" min="1" @if($item['max_qty'] !== null) max="{{ $item['max_qty'] }}" @endif
                                    wire:model.lazy="items.{{ $i }}.qty" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                                @if ($item['max_qty'] !== null)
                                    <p class="text-[11px] text-zinc-500 mt-1">Max: {{ $item['max_qty'] }}</p>
                                @endif
                                @error("items.{$i}.qty") <span class="block text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </td>
                            <td class="py-2 pr-2 w-24"><input type="number" step="0.01" wire:model.lazy="items.{{ $i }}.markup_percent" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" /></td>
                            <td class="py-2 text-right text-white font-medium">MVR {{ number_format($line['line_amount'], 2) }}</td>
                            <td class="py-2 pl-2">@if (count($items) > 1)<button type="button" wire:click="removeItem({{ $i }})" class="text-red-400"><x-heroicon-o-trash class="w-4 h-4" /></button>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" wire:click="addItem" class="mt-3 text-sm text-accent font-medium">+ Add Product</button>
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 max-w-sm ml-auto text-sm space-y-2">
            <div class="flex justify-between text-zinc-400"><span>Subtotal</span><span class="text-white">MVR {{ number_format($this->summary['subtotal'], 2) }}</span></div>
            <div class="flex justify-between text-zinc-400"><span>GST ({{ $gst_percent }}%)</span><span class="text-white">MVR {{ number_format($this->summary['gst_amount'], 2) }}</span></div>
            <div class="flex justify-between text-white font-bold text-base border-t border-white/10 pt-2"><span>Grand Total</span><span>MVR {{ number_format($this->summary['grand_total'], 2) }}</span></div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('crm.invoices') }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Create Invoice</button>
        </div>
    </form>
</div>
