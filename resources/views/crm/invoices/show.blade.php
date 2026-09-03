<div>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.invoices') }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
            <h1 class="text-2xl font-bold text-white">{{ $record->friendly_id }}</h1>
            <x-badge :color="$record->payment_status === 'paid' ? 'green' : ($record->payment_status === 'partial' ? 'orange' : 'gray')">{{ ucfirst($record->payment_status) }}</x-badge>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('print.invoice', $record) }}" target="_blank" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm flex items-center gap-1">
                <x-heroicon-o-printer class="w-4 h-4" /> Print
            </a>
            <a href="{{ route('crm.deliveries.create', ['invoiceId' => $record->id]) }}"
               class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm {{ $record->isFullyDelivered() ? 'opacity-50 pointer-events-none' : '' }}">
                {{ $record->isFullyDelivered() ? 'Fully Scheduled' : 'Create Delivery Note' }}
            </a>
            <a href="{{ route('crm.returns.create', ['invoiceId' => $record->id]) }}" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">
                Create Return
            </a>
            @if ($record->balance_due > 0)
                <button wire:click="openPaymentForm" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">
                    Receive Payment
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Billed To</dt><dd class="text-white">{{ $record->bill_to_name }} — {{ $record->bill_to_phone }}</dd></div>
                    <div><dt class="text-gray-500">Date</dt><dd class="text-white">{{ $record->invoice_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500">Expiry</dt><dd class="text-white">{{ optional($record->expiry_date)->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500">Source Quotation</dt><dd class="text-white">
                        @if ($record->quotation)<a href="{{ route('crm.quotations.show', $record->quotation) }}" class="text-orange-400 hover:underline">{{ $record->quotation->friendly_id }}</a>@else — @endif
                    </dd></div>
                </dl>
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <h3 class="font-bold text-white mb-3">Items</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">#</th><th class="pb-2">Product</th><th class="pb-2 text-right">Qty</th><th class="pb-2 text-right">Rate</th><th class="pb-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach ($record->items as $i => $item)
                            <tr>
                                <td class="py-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="py-2 text-white">{{ $item->product?->description }} <span class="text-gray-500 text-xs">{{ $item->product?->code }}</span></td>
                                <td class="py-2 text-right text-gray-300">{{ $item->qty }}</td>
                                <td class="py-2 text-right text-gray-300">MVR {{ number_format($item->rate, 2) }}</td>
                                <td class="py-2 text-right text-white">MVR {{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="border-t border-gray-800 mt-3 pt-3 text-sm space-y-1 max-w-xs ml-auto">
                    <div class="flex justify-between text-gray-400"><span>Subtotal</span><span class="text-white">MVR {{ number_format($record->subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-gray-400"><span>GST ({{ $record->gst_percent }}%)</span><span class="text-white">MVR {{ number_format($record->gst_amount, 2) }}</span></div>
                    <div class="flex justify-between text-white font-bold"><span>Invoice Total</span><span>MVR {{ number_format($record->grand_total, 2) }}</span></div>
                    <div class="flex justify-between text-gray-400"><span>Total Paid</span><span class="text-emerald-400">MVR {{ number_format($record->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between text-gray-400"><span>Balance Due</span><span class="text-white">MVR {{ number_format($record->balance_due, 2) }}</span></div>
                </div>
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <h3 class="font-bold text-white mb-3">Payment History</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">Date</th><th class="pb-2">Method</th><th class="pb-2">Reference</th><th class="pb-2">Received By</th><th class="pb-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse ($record->payments as $payment)
                            <tr>
                                <td class="py-2 text-gray-300">{{ $payment->date->format('d M Y') }}</td>
                                <td class="py-2 text-gray-300">{{ $payment->method }}</td>
                                <td class="py-2 text-gray-400">{{ $payment->reference ?? '—' }}</td>
                                <td class="py-2 text-gray-400">{{ $payment->receivedBy?->name }}</td>
                                <td class="py-2 text-right text-white">MVR {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-500">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            @if ($record->deliveries->isNotEmpty())
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                    <h3 class="font-bold text-white mb-3">Deliveries</h3>
                    @foreach ($record->deliveries as $d)
                        <a href="{{ route('crm.deliveries.show', $d) }}" class="block text-sm text-orange-400 hover:underline mb-1">{{ $d->friendly_id }} — {{ ucfirst($d->status) }}</a>
                    @endforeach
                </div>
            @endif
            @if ($record->returns->isNotEmpty())
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                    <h3 class="font-bold text-white mb-3">Returns</h3>
                    @foreach ($record->returns as $r)
                        <p class="text-sm text-gray-300 mb-1">{{ $r->friendly_id }} — MVR {{ number_format($r->value, 2) }} ({{ ucfirst($r->status) }})</p>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if ($showPaymentForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showPaymentForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">Receive Payment</h2>
                <form wire:submit.prevent="receivePayment" class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Amount to Pay</label>
                        <input type="number" step="0.01" wire:model="paymentAmount" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        @error('paymentAmount') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            @foreach (\App\Models\Payment::METHODS as $method) <option value="{{ $method }}">{{ $method }}</option> @endforeach
                        </select>
                    </div>
                    @if ($paymentMethod !== 'Cash')
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Reference Number</label>
                            <input type="text" wire:model="paymentReference" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        </div>
                    @endif
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showPaymentForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Receive Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
