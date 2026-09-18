<div>
    <h1 class="text-2xl font-bold text-white mb-6">All Receipts</h1>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by customer, invoice or reference…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Payment ID</th><th class="p-3">Date</th><th class="p-3">Invoice</th><th class="p-3">Customer</th><th class="p-3">Method</th><th class="p-3">Amount</th><th class="p-3">Invoice Total</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($payments as $payment)
                    @php($invoiceTotals = $payment->invoice?->adjustedTotals())
                    @php($refundReturn = $payment->method === \App\Models\Payment::METHOD_REFUND ? $payment->invoice?->returns->keyBy('friendly_id')->get(str_replace('Return #', '', $payment->reference ?? '')) : null)
                    <tr class="hover:bg-zinc-700/40">
                        <td class="p-3 text-white font-medium">{{ $payment->friendly_id }}</td>
                        <td class="p-3 text-zinc-400">{{ $payment->date->format('d M Y') }}</td>
                        <td class="p-3 text-zinc-300">{{ $payment->invoice?->friendly_id }}</td>
                        <td class="p-3 text-zinc-300">{{ $payment->invoice?->customer?->company_name }}</td>
                        <td class="p-3 text-zinc-400">{{ $payment->method }}</td>
                        <td class="p-3 text-white">MVR {{ number_format($payment->amount, 2) }}</td>
                        <td class="p-3 text-zinc-500">
                            MVR {{ number_format($invoiceTotals['grand_total'] ?? 0, 2) }}
                            @if (($invoiceTotals['refunded_value'] ?? 0) > 0)
                                <span class="block text-[11px] text-zinc-600 line-through">MVR {{ number_format($payment->invoice?->grand_total, 2) }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            @if ($payment->receipt_path)
                                <a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="text-accent text-sm hover:underline mr-3">Receipt</a>
                            @elseif ($refundReturn)
                                <a href="{{ route('print.credit-note', $refundReturn) }}" target="_blank" class="text-accent text-sm hover:underline mr-3">Credit Note</a>
                            @endif
                            <a href="{{ route('crm.invoices.show', $payment->invoice) }}" class="text-accent text-sm hover:underline">View Invoice</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-zinc-500">No payments recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
