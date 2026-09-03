<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Quotations</h1>
        <a href="{{ route('crm.quotations.create') }}" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> New Quotation
        </a>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <div class="relative flex-1 min-w-[240px]">
            <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search by ID, customer or staff…" class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
        </div>
        <select wire:model="statusFilter" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option><option value="sent">Sent</option>
        </select>
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Quotation ID</th><th class="p-3">Deal Number</th><th class="p-3">Customer</th>
                    <th class="p-3">Date</th><th class="p-3">Staff</th><th class="p-3">Status</th><th class="p-3">Amount</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($quotations as $q)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white font-medium">{{ $q->friendly_id }}</td>
                        <td class="p-3 text-gray-400">{{ $q->deal?->friendly_id ?? '—' }}</td>
                        <td class="p-3 text-gray-300">{{ $q->customer?->company_name }}</td>
                        <td class="p-3 text-gray-400">{{ $q->quotation_date->format('d M Y') }}</td>
                        <td class="p-3 text-gray-400">{{ $q->staff?->name }}</td>
                        <td class="p-3"><x-badge :color="$q->status === 'sent' ? 'green' : 'gray'">{{ ucfirst($q->status) }}</x-badge></td>
                        <td class="p-3 text-white">MVR {{ number_format($q->grand_total, 2) }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <a href="{{ route('crm.quotations.show', $q) }}" class="block px-3 py-1.5 text-gray-200 hover:bg-gray-700">View</a>
                                <a href="{{ route('print.quotation', $q) }}" target="_blank" class="block px-3 py-1.5 text-gray-200 hover:bg-gray-700">Print</a>
                                <a href="{{ route('crm.quotations.edit', $q) }}" class="block px-3 py-1.5 text-gray-200 hover:bg-gray-700">Edit</a>
                                @if ($q->status === 'draft')
                                    <button wire:click="markSent({{ $q->id }})" class="block w-full text-left px-3 py-1.5 text-gray-200 hover:bg-gray-700">Mark as Sent</button>
                                @endif
                                @if ($q->invoices()->count() === 0)
                                    <button wire:click="convert({{ $q->id }})" wire:confirm="Convert this quotation to an invoice?" class="block w-full text-left px-3 py-1.5 text-emerald-400 hover:bg-gray-700">Convert to Invoice</button>
                                @endif
                                <button wire:click="delete({{ $q->id }})" wire:confirm="Delete this quotation?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-gray-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-gray-500">No quotations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $quotations->links() }}</div>
</div>
