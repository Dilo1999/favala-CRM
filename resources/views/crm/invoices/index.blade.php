<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Invoices</h1>
    </div>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by invoice ID or customer…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Invoice ID</th><th class="p-3">Customer</th><th class="p-3">Last Payment</th>
                    <th class="p-3">Payment Status</th><th class="p-3">Amount</th><th class="p-3">Balance Due</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($invoices as $invoice)
                    @php($last = $invoice->payments->sortByDesc('date')->first())
                    <tr onclick="window.location='{{ route('crm.invoices.show', $invoice) }}'" class="hover:bg-zinc-700/40 cursor-pointer">
                        <td class="p-3 text-white font-medium">{{ $invoice->friendly_id }}</td>
                        <td class="p-3 text-zinc-300">{{ $invoice->customer?->company_name }}</td>
                        <td class="p-3 text-zinc-400">{{ $last ? $last->date->format('d M Y') : 'N/A' }}</td>
                        <td class="p-3">
                            <x-badge :color="match($invoice->payment_status) { 'paid' => 'green', 'partial' => 'orange', 'refunded' => 'blue', default => 'gray' }">
                                {{ ucfirst($invoice->payment_status) }}
                            </x-badge>
                        </td>
                        <td class="p-3 text-white">MVR {{ number_format($invoice->grand_total, 2) }}</td>
                        <td class="p-3 text-zinc-300">MVR {{ number_format($invoice->balance_due, 2) }}</td>
                        <td class="p-3 text-right" onclick="event.stopPropagation()">
                            <x-row-menu>
                                <a href="{{ route('crm.invoices.show', $invoice) }}" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">View Details</a>
                                <a href="{{ route('print.invoice', $invoice) }}" target="_blank" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Print</a>
                                @if (auth()->user()->canManageAllRecords())
                                    <button wire:click="delete({{ $invoice->id }})" wire:confirm="Delete this invoice?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                @endif
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-zinc-500">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
