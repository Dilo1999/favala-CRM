<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.quotations') }}" class="text-zinc-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
            <h1 class="text-2xl font-bold text-white">{{ $record->friendly_id }}</h1>
            <x-badge :color="$record->status === 'sent' ? 'green' : 'gray'">{{ ucfirst($record->status) }}</x-badge>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('print.quotation', $record) }}" target="_blank" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm flex items-center gap-1">
                <x-heroicon-o-printer class="w-4 h-4" /> Print
            </a>
            @if ($record->status === 'draft')
                <button wire:click="markSent" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Mark as Sent</button>
            @endif
            @if ($record->invoices()->count() === 0)
                <button wire:click="convert" wire:confirm="Convert this quotation to an invoice?" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-semibold">Convert to Invoice</button>
            @endif
            <a href="{{ route('crm.quotations.edit', $record) }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-zinc-500">Customer</dt><dd class="text-white">{{ $record->customer?->company_name }}</dd></div>
                    <div><dt class="text-zinc-500">Deal</dt><dd class="text-white">{{ $record->deal?->friendly_id ?? '—' }}</dd></div>
                    <div><dt class="text-zinc-500">Date</dt><dd class="text-white">{{ $record->quotation_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-zinc-500">Expiry</dt><dd class="text-white">{{ optional($record->expiry_date)->format('d M Y') }}</dd></div>
                    <div><dt class="text-zinc-500">Staff</dt><dd class="text-white">{{ $record->staff?->name }}</dd></div>
                    <div><dt class="text-zinc-500">Bill To</dt><dd class="text-white">{{ $record->bill_to_name }} — {{ $record->bill_to_phone }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="font-bold text-white mb-3">Items</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2">Vendor</th><th class="pb-2 text-right">Qty</th><th class="pb-2 text-right">Rate</th><th class="pb-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($record->items as $item)
                            <tr>
                                <td class="py-2 text-white">{{ $item->product?->description }}</td>
                                <td class="py-2 text-zinc-400">{{ $item->vendor?->company_name }}</td>
                                <td class="py-2 text-right text-zinc-300">{{ $item->qty }}</td>
                                <td class="py-2 text-right text-zinc-300">MVR {{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-2 text-right text-white">MVR {{ number_format($item->line_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($record->terms_conditions)
                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                    <h3 class="font-bold text-white mb-3">Terms & Conditions</h3>
                    <p class="text-sm text-zinc-300 whitespace-pre-line">{{ $record->terms_conditions }}</p>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 text-sm space-y-2 h-fit lg:sticky lg:top-6">
            <h3 class="font-bold text-white mb-2">Summary</h3>
            <div class="flex justify-between text-zinc-400"><span>Subtotal</span><span class="text-white">MVR {{ number_format($record->subtotal, 2) }}</span></div>
            <div class="flex justify-between text-zinc-400"><span>GST ({{ $record->gst_percent }}%)</span><span class="text-white">MVR {{ number_format($record->gst_amount, 2) }}</span></div>
            <div class="flex justify-between text-white font-bold text-base border-t border-white/10 pt-2"><span>Grand Total</span><span>MVR {{ number_format($record->grand_total, 2) }}</span></div>
            <div class="flex justify-between text-zinc-400 pt-2"><span>Profit</span><span class="text-green-400">MVR {{ number_format($record->total_profit, 2) }} ({{ $record->profit_margin }}%)</span></div>
        </div>
    </div>
</div>
