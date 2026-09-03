<div>
    <h1 class="text-2xl font-bold text-white mb-6">All Receipts</h1>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by customer, invoice or reference…" class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Payment ID</th><th class="p-3">Date</th><th class="p-3">Invoice</th><th class="p-3">Customer</th><th class="p-3">Method</th><th class="p-3">Amount</th><th class="p-3">Invoice Total</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white font-medium">{{ $payment->friendly_id }}</td>
                        <td class="p-3 text-gray-400">{{ $payment->date->format('d M Y') }}</td>
                        <td class="p-3 text-gray-300">{{ $payment->invoice?->friendly_id }}</td>
                        <td class="p-3 text-gray-300">{{ $payment->invoice?->customer?->company_name }}</td>
                        <td class="p-3 text-gray-400">{{ $payment->method }}</td>
                        <td class="p-3 text-white">MVR {{ number_format($payment->amount, 2) }}</td>
                        <td class="p-3 text-gray-500">MVR {{ number_format($payment->invoice?->grand_total, 2) }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('crm.invoices.show', $payment->invoice) }}" class="text-orange-400 text-sm hover:underline">View Invoice</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-gray-500">No payments recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
