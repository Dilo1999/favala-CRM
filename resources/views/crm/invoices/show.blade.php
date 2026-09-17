<div>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-bold text-white">Invoice {{ $record->friendly_id }}</h1>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('crm.invoices') }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium flex items-center gap-1.5 hover:bg-zinc-700">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Back
            </a>
            <a href="{{ route('print.invoice', $record) }}" target="_blank" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium flex items-center gap-1.5 hover:bg-zinc-700">
                <x-heroicon-o-printer class="w-4 h-4" /> Print
            </a>
            <a href="{{ route('crm.deliveries.create', ['invoiceId' => $record->id]) }}"
               class="px-4 py-2 rounded-lg border border-white/10 text-sm font-medium flex items-center gap-1.5 {{ $record->isFullyDelivered() ? 'text-zinc-500 opacity-50 pointer-events-none' : 'text-zinc-200 hover:bg-zinc-700' }}">
                <x-heroicon-o-truck class="w-4 h-4" />
                {{ $record->isFullyDelivered() ? 'Fully Scheduled' : 'Create Delivery Note' }}
            </a>
            <a href="{{ route('crm.returns.create', ['invoiceId' => $record->id]) }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium flex items-center gap-1.5 hover:bg-zinc-700">
                <x-heroicon-o-reply class="w-4 h-4" /> Create Return
            </a>
            @if ($this->payableAmount > 0)
                <button wire:click="openPaymentForm" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold flex items-center gap-1.5">
                    <x-heroicon-o-cash class="w-4 h-4" /> Receive Payment
                </button>
            @else
                <button disabled class="px-4 py-2 rounded-lg bg-accent/50 text-white/70 text-sm font-semibold flex items-center gap-1.5 cursor-not-allowed">
                    <x-heroicon-o-cash class="w-4 h-4" /> Receive Payment
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Invoice Details</h3>
                <div class="flex flex-wrap justify-between gap-6 text-sm">
                    <div>
                        <p class="text-zinc-500">Billed To</p>
                        <p class="text-white font-bold">{{ $record->bill_to_name ?? $record->customer?->company_name }}</p>
                        <p class="text-zinc-500">{{ $record->bill_to_phone ?: '-' }}</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-zinc-400">Invoice #: <span class="text-white font-bold">{{ $record->friendly_id }}</span></p>
                        <p class="text-zinc-400">Date: <span class="text-white font-bold">{{ $record->invoice_date->format('F jS, Y') }}</span></p>
                        <p class="text-zinc-400">Expires: <span class="text-white font-bold">{{ optional($record->expiry_date)->format('F jS, Y') ?? '—' }}</span></p>
                        @if ($record->quotation)
                            <p class="text-zinc-400">Quotation #: <a href="{{ route('crm.quotations.show', $record->quotation) }}" class="text-white font-bold hover:text-accent-light">{{ $record->quotation->friendly_id }}</a></p>
                        @endif
                        @if ($record->reference_number)
                            <p class="text-zinc-400">Reference #: <span class="text-white font-bold">{{ $record->reference_number }}</span></p>
                        @endif
                        @if ($record->staff)
                            <p class="text-zinc-400">Sales Staff: <span class="text-white font-bold">{{ $record->staff->name }}</span></p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Items</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">#</th><th class="pb-2">Product</th><th class="pb-2 text-right">Qty</th><th class="pb-2 text-right">Rate</th><th class="pb-2 text-right">Discount</th><th class="pb-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($record->items as $i => $item)
                            @php($gross = round($item->qty * $item->rate, 2))
                            @php($discountAmount = $item->discount_type === 'percent' ? round($gross * ($item->discount_value / 100), 2) : min($item->discount_value, $gross))
                            <tr>
                                <td class="py-2 text-zinc-500">{{ $i + 1 }}</td>
                                <td class="py-2 text-white font-medium">{{ $item->product?->description }} <span class="block text-zinc-500 text-xs font-normal">{{ $item->product?->code }}</span></td>
                                <td class="py-2 text-right text-zinc-300">{{ $item->qty }} pcs</td>
                                <td class="py-2 text-right text-zinc-300">MVR {{ number_format($item->rate, 2) }}</td>
                                <td class="py-2 text-right text-red-400">-MVR {{ number_format($discountAmount, 2) }}</td>
                                <td class="py-2 text-right text-white">MVR {{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Payment History</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">Date</th><th class="pb-2">Method</th><th class="pb-2">Reference</th><th class="pb-2">Receipt</th><th class="pb-2">Received By</th><th class="pb-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($record->payments as $payment)
                            <tr>
                                <td class="py-2 text-zinc-300">{{ $payment->date->format('F jS, Y') }}</td>
                                <td class="py-2"><span class="inline-flex items-center px-2.5 py-1 rounded-md bg-zinc-700 text-white text-xs font-semibold">{{ $payment->method }}</span></td>
                                <td class="py-2 text-zinc-400">{{ $payment->reference ?? '—' }}</td>
                                <td class="py-2">
                                    @if ($payment->receipt_path)
                                        <a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="text-accent hover:underline">View</a>
                                    @else
                                        <span class="text-zinc-500">—</span>
                                    @endif
                                </td>
                                <td class="py-2 text-zinc-400">{{ $payment->receivedBy?->name }}</td>
                                <td class="py-2 text-right text-white">MVR {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-4 text-center text-zinc-500">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-zinc-400">Subtotal</span><span class="text-white font-semibold">MVR {{ number_format($record->subtotal, 2) }}</span></div>
                    @if ($record->discount_value > 0)
                        <div class="flex justify-between"><span class="text-zinc-400">Discount</span><span class="text-red-400 font-semibold">-MVR {{ number_format($record->subtotal - ($record->grand_total - $record->gst_amount), 2) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-zinc-400">GST ({{ $record->gst_percent }}%)</span><span class="text-white font-semibold">MVR {{ number_format($record->gst_amount, 2) }}</span></div>
                    <div class="flex justify-between pt-3 border-t border-white/10"><span class="text-white font-bold">Invoice Total</span><span class="text-white font-bold text-lg">MVR {{ number_format($record->grand_total, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-400">Total Paid</span><span class="text-green-400 font-semibold">MVR {{ number_format($record->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-white font-bold">Balance Due</span>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full font-bold text-sm {{ $record->balance_due > 0 ? 'bg-red-600 text-white' : 'bg-green-600 text-white' }}">MVR {{ number_format($record->balance_due, 2) }}</span>
                    </div>
                </div>
            </div>

            @if ($record->deliveries->isNotEmpty())
                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                    <h3 class="font-bold text-white mb-3">Deliveries</h3>
                    @foreach ($record->deliveries as $d)
                        <a href="{{ route('crm.deliveries.show', $d) }}" class="block text-sm text-accent hover:underline mb-1">{{ $d->friendly_id }} — {{ ucfirst($d->status) }}</a>
                    @endforeach
                </div>
            @endif
            @if ($record->returns->isNotEmpty())
                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                    <h3 class="font-bold text-white mb-3">Returns</h3>
                    @foreach ($record->returns as $r)
                        <p class="text-sm text-zinc-300 mb-1">{{ $r->friendly_id }} — MVR {{ number_format($r->value, 2) }} ({{ ucfirst($r->status) }})</p>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if ($showPaymentForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showPaymentForm', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">Receive Payment</h2>
                <form wire:submit.prevent="receivePayment" class="space-y-4">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Amount to Pay</label>
                        <p class="w-full rounded-lg bg-zinc-900 border border-white/10 px-3 py-2 text-white text-sm font-medium">MVR {{ number_format($this->payableAmount, 2) }}</p>
                        <p class="text-[11px] text-zinc-500 mt-1">Always the full balance due — not editable here.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            @foreach (\App\Models\Payment::METHODS as $method) <option value="{{ $method }}">{{ $method }}</option> @endforeach
                        </select>
                    </div>
                    <div @if ($paymentMethod === 'Cash') style="display:none" @endif>
                        <label class="block text-xs text-zinc-400 mb-1">Reference Number <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="paymentReference" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        @error('paymentReference') <span class="block text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">
                            Receipt
                            @if ($paymentMethod !== 'Cash') <span class="text-red-400">*</span> @else <span class="text-zinc-500">(optional)</span> @endif
                        </label>
                        <input type="file" wire:model="receiptFile" accept=".pdf,image/*" class="w-full text-sm text-zinc-300" />
                        <div wire:loading wire:target="receiptFile" class="text-xs text-zinc-500 mt-1">Uploading…</div>
                        @if ($receiptFile)
                            <p class="text-xs text-zinc-400 mt-1">{{ $receiptFile->getClientOriginalName() }}</p>
                        @endif
                        @error('receiptFile') <span class="block text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    @if ($referenceMismatchWarning)
                        <div class="rounded-lg border border-amber-400/30 bg-amber-400/10 px-3 py-2">
                            <p class="text-xs text-amber-300">{{ $referenceMismatchWarning }}</p>
                        </div>
                    @endif
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showPaymentForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        @if ($referenceMismatchWarning)
                            <button type="button" wire:click="receivePayment(true)" wire:loading.attr="disabled" wire:target="receivePayment" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">Receive Anyway</button>
                        @else
                            <button type="submit" wire:loading.attr="disabled" wire:target="receivePayment" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                                <span wire:loading.remove wire:target="receivePayment">Receive Payment</span>
                                <span wire:loading wire:target="receivePayment">Verifying…</span>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
